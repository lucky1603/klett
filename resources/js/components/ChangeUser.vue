<template>
    <div class="container-fluid">
        <b-card bg-variant="white" header="Pošalji E-Mail" header-bg-variant="dark" header-text-variant="white">
            <div id="searchFormPanel">
                <b-form inline>
                    <b-form-input v-model="searchForm.username" placeholder="Korisničko ime ..." class="ml-1"/>
                    <b-form-input v-model="searchForm.email" placeholder="Email ..." class="ml-1"/>
                    <b-form-select v-model="searchForm.role" :options="roles" class="ml-1"></b-form-select>
                    <b-form-select v-model="searchForm.status" :options="statuses" class="ml-1"></b-form-select>
                    <b-form-select v-model="searchForm.source" :options="sources" class="ml-1"></b-form-select>
                    <b-form-select v-model="searchForm.klf" :options="klfs" class="ml-1"></b-form-select>
                    <b-button variant="primary" class="ml-2" @click="setTable">Kreiraj tabelu</b-button>
                </b-form>
                <b-progress v-if="showImport" :value="imported" :max="count" show-progress class="my-2"></b-progress>
            </div>
            <div id="tablePanel">
                <b-table
                ref="table"
                responsive
                striped hover small bordered
                :items="myItems"
                :fields="fields"
                :currentPage="currentPage"
                :per-page="pageSize"
                :style="tableStyle"
                class="shadow mt-2">
            </b-table>
            <div class="d-flex align-items-center justify-content-center">
                <b-pagination
                    v-model="currentPage"
                    :total-rows="totalCount"
                    :per-page="pageSize"
                    aria-controls="profileTable"
                    align="right"
                    ></b-pagination>
            </div>
            </div>
            <b-progress v-if="showActionProgress" :value="itemsChanged" :max="count" show-progress class="my-2"></b-progress>
            <div id="sendMailPanel" class="d-flex align-items-center justify-content-center p-2">
                <b-button variant="primary" @click="executeAction">{{ buttonTitle }}</b-button>
            </div>
        </b-card>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ChangeUser',
    props: {
        tableHeight: { typeof: Number, default: 500 },
        fields: { typeof: Array, default: []},
        action: { typeof: String, default: ''},
        buttonTitle: { typeof: String, default: "Izvrši" }
    },
    computed: {
        tableStyle() {
            return {
                height: this.tableHeight + "px"
            }
        }
    },
    data() {
        return {
            searchForm : {
                username: '',
                email: '',
                role: 0,
                status: 0,
                source: null,
                klf: -1
            },
            rows: [],
            roles: [],
            count: 0,
            imported: 0,
            showImport: false,
            totalCount: 0,
            changeCount: 0,
            currentPage: 1,
            pageSize: 10,
            accessToken: '',
            itemsChanged: 0,
            itemsToChange: 0,
            showActionProgress: false,
            statuses: [
                { value: 0, text: "Svi" },
                { value: 1, text: "Omogućeni" },
                { value: 2, text: "Onemogućeni" },
            ],
            sources: [
                { value: null, text: "Svi izvori" },
                { value: 'Klett', text: 'Klett' },
                { value: 'E-Uci', text: "eUči" },
                { value: 'E-Ucionica', text: "eUčionica" },
                { value: 'ZOUV', text: 'ZOUV' }
            ],
            klfs: [
                { value: -1, text: "Svi korisnici" },
                { value: 0, text: "Nije KLF korisnik" },
                { value: 1, text: "KLF korisnik" },
            ]

        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
        async setTable() {
            await axios.get('/changeusers/deleteall');
            this.imported = 0;

            this.showImport = true;
            let formData = new FormData();
            for(let property in this.searchForm) {
                formData.append(property, this.searchForm[property]);
            }

            formData.append('firstName', '');
            formData.append('lastName', '');

            if(this.accessToken == '') {
                await this.getToken();
                formData.append('token', this.accessToken);
            }

            // Get count first.
            await axios.post('/remoteusers/filtercount', formData)
            .then(response => {
                console.log(response.data);
                this.count = response.data;
            });
1
            for(let i = 0; i < this.count; i += 100) {
                let fData = new FormData();
                for(let property in this.searchForm) {
                    fData.append(property, this.searchForm[property]);
                }
                fData.append('first', i);
                fData.append('max', 100);
                fData.append('firstName', '');
                fData.append('lastName', '');
                fData.append('token', this.accessToken);

                // Get items for table.
                let users = [];
                await axios.post('/remoteusers/filterUsers', fData)
                .then(response => {
                    console.log(response.data);
                    for(let property in response.data) {
                        users.push(response.data[property]);
                    }
                });

                for(let j = 0; j < users.length; j++) {
                    let user = users[j];
                    let changeUserData = new FormData();
                    changeUserData.append('username', user.username);
                    changeUserData.append('email', user.email);
                    changeUserData.append('firstName', user.firstName);
                    changeUserData.append('lastName', user.lastName);
                    changeUserData.append('userId', user.id);
                    changeUserData.append('source', user.source);
                    changeUserData.append('role', user.role);
                    changeUserData.append('klfKorisnik', user.klf_korisnik);
                    changeUserData.append('pedagoskaSveska', user.pedagoska_sveska);
                    changeUserData.append('testomat', user.testomat);

                    await axios.post('/changeusers/create', changeUserData);
                    this.imported ++;
                }
            }
            this.showImport = false;
            this.getTableData();
        },
        async executeAction() {
            this.changeCount = await this.getCount();
            this.itemsChanged = await this.getCountDone();
            this.showActionProgress = true;
            console.log(this.rows);
            for(let i = 0; i < this.rows.length; i++) {
                let row = this.rows[i];
                if(!row.changed) {
                    await axios.get(this.action + '/' + row.user_id);
                    await axios.get('/changeusers/setchanged/' + row.user_id);
                    this.itemsChanged ++;
                }
            }

            this.showActionProgress = false;
            this.$refs.table.refresh();
        },
        async getCountToSend() {
            console.log('getting count');
            this.rows = [];
            await axios.get(this.countAction)
            .then(response => {
                this.rows = response.data;
            });

            return this.rows.length;

        },
        /**
         * Gets total count in the table.
         */
        async getCount() {
            let count = 0;
            await axios.get('/changeusers/listall')
            .then(response => {
                this.rows = [];
                for(let property in response.data) {
                    this.rows.push(response.data[property]);
                }
            });

            return this.rows.count;
        },

        /**
         * Gets the count of the changed rows in the table.
         */
        async getCountDone() {
            let countDone = 0;
            await axios.get('/changeusers/countDone')
            .then(response => {
                countDone = response.data;
            });

            return countDone;
        },

        /**
         * Gets the keycloak access token.
         */
        async getToken() {
            await axios.get('/keycloak')
            .then(response => {
                console.log(response.data);
                this.accessToken = response.data.access_token;
            });
        },
        async getTableData() {
            this.$refs.table.refresh();
        },
        async getData() {
            await this.getRoles();
            await this.getTableData();
        },
        /**
         * Daj grupe.
         */
         async getRoles() {
            this.roles = [{
                value: 0,
                text: 'Svi'
            }];

            await axios.get('/getRealmGroups')
            .then(response => {
                console.log(response.data);
                for(let property in response.data) {
                    let group = response.data[property];
                    if(['Administrator', 'Student', 'Teacher'].includes(group.name)) {
                        this.roles.push({
                            value: group.id,
                            text: group.name
                        });
                    }
                }
            });
        },
        async myItems(ctx, callback) {
            let params = "?page=" + ctx.currentPage + "&&size=" + ctx.perPage;
            var items = [];
            axios.get('/changeusers/list' + params)
            .then(response => {
                this.pageSize = response.data.perPage;
                this.totalCount = response.data.count;
                this.currentPage = response.data.currentPage;

                for(let property in response.data.rows) {
                    let item = response.data.rows[property];
                    items.push(item);
                }
                callback(items);
            })
            .catch(error => {
                callback([]);
            });
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
