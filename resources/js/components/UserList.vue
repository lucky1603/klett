<template>
    <div class="container">
        <div class="my-4">
            <h5 class="float-left">{{ _('gui.usersList') }}</h5>
            <a class="btn btn-sm btn-primary float-right" role="button" href="/appusers/export"><i class="bi bi-box-arrow-right mr-2"></i>Export</a>
        </div>
        <div class="d-flex align-items-center justify-content-center flex-column w-100">
            <b-table
                responsive
                small striped bordered hover
                :items="items"
                :fields="fields"
                :current-page="currentPage"
                head-variant="dark"
                class="w-100 h-100"
                @row-clicked="tableClick"
                :per-page="pageSize"
                @context-changed="pageChanged">
            </b-table>
            <b-pagination
                v-model="currentPage"
                :total-rows="items.length"
                :per-page="pageSize"
                aria-controls="profileTable"
                align="right"
                ></b-pagination>
            <b-button variant="primary" @click="createUser">{{ _('gui.Add')}}</b-button>
        </div>
        <b-modal ref="userFormDialog" size="lg" header-bg-variant="dark" header-text-variant="light">
            <template #modal-header>{{ userDialogTitle }}</template>
            <user-form ref="userForm" :user-id="selectedUserId"></user-form>
            <template #modal-footer>
                <b-button type="button" variant="primary" @click="onOk">{{ _('gui.Accept')}}</b-button>
                <b-button type="button" @click="onCancel">{{ _('gui.Cancel')}}</b-button>
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
            currentPage: 1,
            pageSize: 15,
            selectedUserId: 0,
            userDialogTitle: window.i18n.gui.addUser,
            items: [],
            fields: [
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
                    key: "tel1",
                    label: window.i18n.contactPhone,
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
                    label: window.i18n.gui.city,
                    sortable: true,
                    thStyle: {
                        width: "15%"
                    }
                },
                {
                    key: "country",
                    label: window.i18n.gui.country,
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
                    label: window.i18n.gui.teacher,
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
        pageChanged(ctx) {
            console.log("Page changed " + this.currentPage);
        },
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
            this.userDialogTitle = window.i18n.gui.addUser;
            this.showForm();
        },
        onCancel() {
            this.closeForm();
        },
        tableClick(item, index) {
            this.selectedUserId = item.id;
            this.userDialogTitle = window.i18n.gui.changeUser;
            this.showForm();
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
