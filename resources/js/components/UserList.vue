<template>
    <div class="container">
        <div class="d-flex align-items-center justify-content-center flex-column w-100">
            <b-table small striped bordered hover :items="items" :fields="fields" head-variant="dark"></b-table>
            <b-button variant="primary" @click="showForm">Dodaj</b-button>
        </div>
        <b-modal ref="userFormDialog" size="lg" header-bg-variant="dark" header-text-variant="light">
            <template #modal-header>{{ userDialogTitle }}</template>
            <user-form ref="userForm"></user-form>
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
            userDialogTitle: "Dodaj korisnika",
            items: [],
            fields: [
                {
                    key: "ime",
                    sortable: true,
                    label: "Ime"
                },
                {
                    key: "prezime",
                    sortable: true,
                    label: "Prezime"
                },
                {
                    key: "email",
                    sortable: true,
                    label: "E-Mail"
                },
                {
                    key: "country",
                    label: "Država",
                    sortable: true
                },
                {
                    key: "adresa",
                    sortable: true,
                    label: "Adrese"
                },
                {
                    key: "pb",
                    sortable: true,
                    label: "Poštanski broj"
                },
                {
                    key: "mesto",
                    label: "Mesto",
                    sortable: true
                },
                {
                    key: "tel1",
                    label: "Telefon 1",
                    sortable: true
                },
                {
                    key: "tel2",
                    label: "Telefon 2",
                    sortable: true
                },
                {
                    key: "isTeacher",
                    label: "Nastavnik",
                    sortable: true
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
        onOk() {
            this.$refs.userForm.sendData()
            .then(response => {
                console.log(response.data);
                this.$refs.userFormDialog.hide();
            })
            .catch(error => {
                console.log(error);
            });
        },
        onCancel() {
            this.$refs.userFormDialog.hide();
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
