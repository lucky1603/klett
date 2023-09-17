<template>
    <div class="container">
        <b-row>
            <b-col cols="8">
                <div class="d-flex align-items-center justify-content-center flex-column w-100">
                    <h3>Korisnici u privremenoj tabeli uvoza</h3>
                    <b-table
                        ref="myTable"
                        responsive selectable
                        small striped bordered hover
                        select-mode="single"
                        :items="getItems"
                        :fields="fields"
                        :current-page="currentPage"
                        head-variant="dark"
                        class="w-100 h-100"
                        :per-page="pageSize"
                        @context-changed="pageChanged" @row-clicked="tableClicked">
                    </b-table>
                    <b-pagination
                        v-model="currentPage"
                        :total-rows="totalCount"
                        :per-page="pageSize"
                        aria-controls="profileTable"
                        align="right"
                        ></b-pagination>
                    <b-button variant="primary">{{ _('gui.Add')}}</b-button>
                </div>
            </b-col>
            <b-col>
                <div class="d-flex align-items-center justify-content-center">
                    <h3>NASTAVNICI U CRM-u</h3>
                </div>
                <div class="shadow">
                    <table class="w-100 bordered">
                        <thead>
                            <tr class="bg-dark text-light text-center">
                                <th colspan="2" style="font-size: 14px; padding: 7px">NASTAVNIK - CRM</th>
                            </tr>
                        </thead>
                        <tbody style="font-size: 11px">
                            <tr>
                                <td><strong>ID:</strong></td>
                                <td class="px-3">{{ crm.id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Ime:</strong></td>
                                <td class="px-3">{{ crm.ime }}</td>
                            </tr>
                            <tr>
                                <td><strong>Prezime:</strong></td>
                                <td class="px-3">{{ crm.prezime }}</td>
                            </tr>
                            <tr>
                                <td><strong>EMail:</strong></td>
                                <td class="px-3">{{ crm.email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Adresa:</strong></td>
                                <td class="px-3">{{ crm.adresa }}</td>
                            </tr>
                            <tr>
                                <td><strong>PB::</strong></td>
                                <td class="px-3">{{ crm.pb }}</td>
                            </tr>
                            <tr>
                                <td><strong>Mesto:</strong></td>
                                <td class="px-3">{{ crm.mesto }}</td>
                            </tr>
                            <tr>
                                <td><strong>Telefon1:</strong></td>
                                <td class="px-3">{{ crm.telefon1 }}</td>
                            </tr>
                            <tr>
                                <td><strong>Telefon2:</strong></td>
                                <td class="px-3">{{ crm.telefon2 }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-if="crm.predmeti.length > 0" >
                        <table class="w-100">
                            <thead class="bg-light">
                                <tr>
                                    <th colspan="2" class="text-center">PREDMETI</th>
                                </tr>
                            </thead>
                            <tbody style="font-size: 10px">
                                <tr v-for="predmet in crm.predmeti" key="ext_predmetprofilaid">
                                    <td>{{ predmeti[predmet._ext_predmet_value] }}</td>
                                    <td>{{ predmet.ext_korisnik ? 'Korisnik' : 'Nije korisnik'}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr/>
                    <div class="d-flex align-items-center justify-content-center">
                        <b-button variant="primary" type="button" class="my-2" size="sm" @click="importUser">Uvezi</b-button>
                    </div>
                </div>

                <div class="shadow w-100 mt-4">
                    <p class="text-center bg-dark text-light w-100">STATUS UVOZA</p>
                    <progress-bar :max="total" :value="imported" class="mt-2 mx-2"></progress-bar>
                </div>

            </b-col>
        </b-row>
    </div>

</template>

<script>
import axios from 'axios';

export default {
    name: 'ImportUsersPanel',

    data() {
        return {
            currentPage: 1,
            totalCount: 0,
            pageSize: 15,
            fields: [
                {
                    key: "username",
                    sortable: true,
                    label: window.i18n.gui.user,
                    thStyle: {
                        width: "15%"
                    }
                },
                {
                    key: "ime",
                    sortable: true,
                    label: window.i18n.gui.firstName,
                    thStyle: {
                        width: "15%"
                    }
                },
                {
                    key: "prezime",
                    sortable: true,
                    label: window.i18n.gui.lastName,
                    thStyle: {
                        width: "15%"
                    }
                },
                {
                    key: "email",
                    sortable: true,
                    label: window.i18n.gui.email,
                    thStyle: {
                        width: "20%"
                    }
                },

                {
                    key: "isTeacher",
                    label: window.i18n.gui.teacher,
                    sortable: true,
                    thStyle: {
                        width: "5%"
                    }
                },

            ],
            crm: {
                id: '',
                username: '',
                ime: '',
                prezime: '',
                email: '',
                adresa: '',
                pb: '',
                mesto: '',
                telefon: '',
                mobilni: '',
                predmeti: []
            },
            predmeti: [],
            skole: [],
            selectedUser: {
                userId: 0,
                username: '',
                firstName: '',
                lastName: '',
                email: '',
                isTeacher: false
            },
            total: 0,
            imported: 0,

        };
    },

    async mounted() {
        await this.getPredmeti();
        await this.getCounts();
    },

    methods: {
        async getItems(ctx, callback) {
            let params = "?page=" + ctx.currentPage;
            var items = [];
            axios.get('/userimports/data' + params)
            .then(response => {
                console.log(response.data);
                this.pageSize = response.data.perPage;
                this.totalCount = response.data.count;
                this.currentPage = response.data.currentPage;
                for(let row in response.data.rows) {
                    items.push(response.data.rows[row]);
                }

                // Select first in the list.
                this.checkUser(items[0].email);

                callback(items);
            })
            .catch(error => {
                callback([]);
            })
        },
        pageChanged(ctx) {
            console.log("Page changed " + this.currentPage);
        },
        async tableClicked(item, index) {
            await this.checkUser(item.email);

            this.selectedUser.userId = item.id;
            this.selectedUser.email = item.email;
            this.selectedUser.firstName = item.ime;
            this.selectedUser.lastName = item.prezime;
            this.selectedUser.username = item.username;
            this.selectedUser.isTeacher = item.isTeacher == "DA" ? true : false;
        },
        async checkUser(email) {
            await axios.get('/crm/checkUser/' + email)
            .then(response => {
                console.log(response.data);
                let nastavnik = response.data[0];
                this.crm.id = nastavnik.contactid;
                this.crm.email = nastavnik.emailaddress1;
                this.crm.ime = nastavnik.firstname;
                this.crm.prezime = nastavnik.lastname;
                this.crm.pb = nastavnik.ext_postanskibroj;
                this.crm.mesto = nastavnik._ext_grad_value;
                this.crm.telefon1 = nastavnik.mobilephone;
                this.crm.telefon2 = nastavnik.telephone1;
                this.crm.adresa = nastavnik.address1_line1;
                this.crm.predmeti.length = 0;
                if(nastavnik.ext_Predmetprofila_Nastavnik_Contact.length > 0) {
                    for(var i = 0; i < nastavnik.ext_Predmetprofila_Nastavnik_Contact.length; i++) {
                        let profil = nastavnik.ext_Predmetprofila_Nastavnik_Contact[i];
                        this.crm.predmeti.push(profil);
                    }
                }
            });
        },

        async getPredmeti() {
            await axios.get('/crm/arrayPredmeta')
            .then(response => {
                // this.subjects = response.data;
                this.predmeti = response.data;
            });
        },
        async getSkole() {

        },
        async importUser() {
            let formData = new FormData();
            for(let property in this.selectedUser) {
                formData.append(property, this.selectedUser[property]);
            }

            await axios.post('/remoteusers/import', formData)
            .then(response => {
                console.log(response.data);
                this.total = response.data.total;
                this.imported = response.data.imported;
            });

            this.$refs.myTable.refresh();
        },
        async getCounts() {
            await axios.get('/userimports/counts')
            .then(response => {
                this.total = response.data.total;
                this.imported = response.data.imported;
            })
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
