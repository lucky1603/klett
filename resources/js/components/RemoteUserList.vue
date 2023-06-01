<template>
    <div class="container">
        <div class="my-4">
            <h5 class="float-left">{{ _('gui.usersList') }}</h5>
            <a class="btn btn-sm btn-primary float-right" role="button" href="/remoteusers/export"><i class="bi bi-box-arrow-right mr-2"></i>Export</a>
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
                :per-page="pageSize"
                @context-changed="pageChanged">
                <template #cell(enabled)="data">
                    <div class="d-flex align-items-center justify-content-center">
                        <img v-if="data.item.enabled" src="/images/greenuser.png" height="15" />
                        <img v-else src="/images/reduser.png" height="15" />
                    </div>
                </template>
                <template #cell(activity)="data">
                    <div class="d-flex align-items-center justify-content-center">
                        <a href="#" @click.prevent="editClicked(data.item.id)" class="mx-1"><b-icon icon="pencil"></b-icon></a>
                        <a href="#" @click.prevent="deleteClicked(data.item.id, data.item.username)" class="mx-1"><b-icon icon="eraser"></b-icon></a>
                    </div>
                </template>
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
            <remote-user-form ref="remoteUserForm" :user-id="selectedUserId"></remote-user-form>
            <template #modal-footer>
                <b-button type="button" variant="primary" @click="onOk">{{ _('gui.Accept')}}</b-button>
                <b-button type="button" @click="onCancel">{{ _('gui.Cancel')}}</b-button>
            </template>
        </b-modal>
        <b-modal ref="deleteDialog" header-bg-variant="dark" header-text-variant="light">
            <template #modal-header>Brisanje korisnika</template>
            <p>Da li ste sigurni da hoćete da obrišete korisnika <strong>{{ selectedUsername }}</strong>?</p>
            <template #modal-footer>
                <b-button type="button" variant="primary" @click="onDelete">{{ _('gui.Yes')}}</b-button>
                <b-button type="button" @click="onCancelDelete">{{ _('gui.No')}}</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'RemoteUserList',

    data() {
        return {
            currentPage: 1,
            pageSize: 15,
            selectedUserId: '',
            selectedUsername: '',
            userDialogTitle: window.i18n.gui.addUser,
            items: [],
            fields: [
                {
                    key: "username",
                    sortable: true,
                    label: window.i18n.gui.username,
                    thStyle: {
                        width: "20%"
                    }
                },
                {
                    key: "firstName",
                    sortable: true,
                    label: window.i18n.gui.firstName,
                    thStyle: {
                        width: "25%"
                    }
                },
                {
                    key: "lastName",
                    sortable: true,
                    label: window.i18n.gui.lastName,
                    thStyle: {
                        width: "25%"
                    }
                },
                {
                    key: "email",
                    sortable: true,
                    label: window.i18n.gui.email,
                    thStyle: {
                        width: "30%"
                    }
                },
                {
                    key: "enabled",
                    sortable: true,
                    label: window.i18n.gui.enabled,
                    thStyle: {
                        width: '5%'
                    }
                },
                {
                    key: "activity",
                    label: window.i18n.gui.activity,
                    thStyle: {
                        width: '10%'
                    }
                }


            ],
            accessToken: ''
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
            await axios.get('/remoteusers/keycloak')
            .then(response => {
                // console.log(response.data);
                this.accessToken = response.data.access_token;
            });

            let formData = new FormData();
            formData.append('token', this.accessToken);
            await axios.post('/remoteusers/data', formData)
            .then(response => {
                console.log(response.data);
                this.items.length = 0;
                for(let i = 0; i < response.data.length; i++) {
                    this.items.push(response.data[i]);
                }
            });
        },
        showForm() {
            this.$refs.userFormDialog.show();
        },
        closeForm() {
            this.$refs.userFormDialog.hide();
            this.selectedUserId = '';
        },
        onOk() {
            this.$refs.remoteUserForm.sendData()
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
        },
        editClicked(id)  {
            this.selectedUserId = id;
            this.userDialogTitle = window.i18n.gui.changeUser;
            this.showForm();
        },
        deleteClicked(id, username) {
            this.selectedUserId = id;
            this.selectedUsername = username;
            this.$refs.deleteDialog.show();
        },
        async onDelete() {
            await axios.get('/remoteusers/keycloak')
            .then(response => {
                this.accessToken = response.data.access_token;
            });


            let formData = new FormData();
            formData.append('token', this.accessToken);
            formData.append('userId', this.selectedUserId)

            axios.post('/remoteusers/delete', formData)
            .then(response => {
                console.log(response.data);
                this.getData();
                this.$refs.deleteDialog.hide();

                this.selectedUserId = 0;
                this.selectedUsername = '';
            })

        },
        onCancelDelete() {
            this.$refs.deleteDialog.hide();
            this.selectedUserId = 0;
            this.selectedUsername = '';
        }

    },
};
</script>

<style lang="scss" scoped>

</style>
