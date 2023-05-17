<template>
    <div class="container">
        <div class="d-flex align-items-center justify-content-center flex-column w-100">
            <b-table
                :sticky-header="true"
                small striped bordered hover
                :items="items"
                :fields="fields"
                head-variant="dark"
                class="w-100" @row-clicked="tableClick">
            </b-table>
            <b-button variant="primary" @click="createUser">Dodaj</b-button>
        </div>
        <b-modal ref="userFormDialog" size="lg" header-bg-variant="dark" header-text-variant="light">
            <template #modal-header>{{ userDialogTitle }}</template>
            <user-form ref="userForm" :user-id="selectedUserId"></user-form>
            <template #modal-footer>
                <b-button type="button" variant="primary" @click="onOk">Prihvati</b-button>
                <b-button type="button" @click="onCancel">Odustani</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'UserList',

    data() {
        return {
            selectedUserId: 0,
            userDialogTitle: "Dodaj korisnika",
            items: [],
            fields: [
                {
                    key: "ime",
                    sortable: true,
                    label: "Ime",
                    thStyle: {
                        width: "15%"
                    }
                },
                {
                    key: "prezime",
                    sortable: true,
                    label: "Prezime",
                    thStyle: {
                        width: "15%"
                    }
                },
                {
                    key: "email",
                    sortable: true,
                    label: "E-Mail",
                    thStyle: {
                        width: "20%"
                    }
                },

                {
                    key: "tel1",
                    label: "Kontakt telefon",
                    sortable: true,
                    thStyle: {
                        width: "15%"
                    }
                },

                // {
                //     key: "adresa",
                //     sortable: true,
                //     label: "Adrese"
                // },
                // {
                //     key: "pb",
                //     sortable: true,
                //     label: "Poštanski broj"
                // },
                {
                    key: "mesto",
                    label: "Mesto",
                    sortable: true,
                    thStyle: {
                        width: "15%"
                    }
                },
                {
                    key: "country",
                    label: "Država",
                    sortable: true,
                    thStyle: {
                        width: "15%"
                    }
                },

                // {
                //     key: "tel2",
                //     label: "Telefon 2",
                //     sortable: true
                // },
                {
                    key: "isTeacher",
                    label: "Nastavnik",
                    sortable: true,
                    thStyle: {
                        width: "5%"
                    }
                },

            ],
        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
        async getData() {
            await axios.get('/appusers/data')
            .then(response => {
                console.log(response.data);
                this.items.length = 0;
                for(let property in response.data) {
                    this.items.push(response.data[property]);
                }
            });
        },
        showForm() {
            this.$refs.userFormDialog.show();
        },
        closeForm() {
            this.$refs.userFormDialog.hide();
            this.selectedUserId = 0;
        },
        onOk() {
            this.$refs.userForm.sendData()
            .then(response => {
                console.log(response.data);
                this.getData();
                this.closeForm();
            })
            .catch(error => {
                console.log(error);
            });
        },
        createUser() {
            this.userDialogTitle = "Kreiraj novog korisniika";
            this.showForm();
        },
        onCancel() {
            this.closeForm();
        },
        tableClick(item, index) {
            this.selectedUserId = item.id;
            this.userDialogTitle = "Promeni podatke korisnika";
            this.showForm();
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
