<template>
    <div class="container">
            <b-row class="w-100 mt-4">
                <b-col lg="10">
                    <b-form id="filterForm" ref="filterForm" inline class="w-100 m-2" @submit.prevent="submitFilter">
                        <b-input-group size="sm" class="m-1">
                            <b-form-input
                                type="search"
                                id="searchFirstName"
                                placeholder="Po imenu ..."
                                v-model="searchForm.firstName"
                            ></b-form-input>
                            <template #append>
                                <b-input-group-text><b-icon-zoom-in></b-icon-zoom-in></b-input-group-text>
                            </template>
                        </b-input-group>
                        <b-input-group size="sm" class="m-1">
                            <b-form-input
                                type="search"
                                id="searchLastName"
                                placeholder="Po prezimenu ..."
                                v-model="searchForm.lastName"
                            ></b-form-input>
                            <template #append>
                                <b-input-group-text><b-icon-zoom-in></b-icon-zoom-in></b-input-group-text>
                            </template>
                        </b-input-group>
                        <!-- <b-form-select v-model="searchForm.userRole" :options="roles" class="m-1" size="sm"></b-form-select> -->
                        <b-form-select v-model="searchForm.userStatus" :options="statuses" class="m-1" size="sm"></b-form-select>
                    </b-form>
                </b-col>
                <b-col lg="2">
                    <div class="d-flex align-items-center justify-content-end justify-content-right flex-row w-100 h-100">
                        <b-button variant="outline-secondary" size="sm" class="m-1" title="Filtriraj" @click="submitFilter"><i class="bi bi-filter"></i></b-button>
                        <b-button variant="success" size="sm" title="Resetuj filter" @click="resetSearchForm"><i class="bi bi-arrow-repeat"></i></b-button>
                    </div>
                </b-col>
            </b-row>


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
            <div class="d-flex align-items-center justify-content-center">
                <b-button variant="primary" @click="createUser" class="m-1" size="sm"><i class="bi bi-person-fill-add mr-1"></i>{{ _('gui.Add')}}</b-button>
                <a class="btn btn-sm btn-primary float-right m-1" role="button" href="/remoteusers/export"><i class="bi bi-box-arrow-right mr-2"></i>Export</a>
            </div>

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
            accessToken: '',
            searchForm: {
                firstName: '',
                lastName: '',
                userRole: 0,
                userStatus: 0
            },
            roles: [
                { value: 0, text: "Po tipu korisnika"},
                { value: 1, text: "Pratioci"},
                { value: 2, text: "Nastavnici"},
                { value: 3, text: "Ucenici" }
            ],
            statuses: [
                { value: 0, text: "Po statusu"},
                { value: 1, text: "Neaktivan(-na)"},
                { value: 2, text: "Aktivan(-na)"}
            ]
        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
        pageChanged(ctx) {
            console.log("Page changed " + this.currentPage);
        },
        submitFilter() {
            let formData = new FormData();
            formData.append('token', this.accessToken);
            for(let property in this.searchForm) {
                formData.append(property, this.searchForm[property]);
            }
            axios.post('/remoteusers/filterUsers', formData)
            .then(response => {
                console.log(response.data);
                //this.items.length = 0;
                this.items = [];
                for(let i = 0; i < response.data.length; i++) {
                    this.items.push(response.data[i]);
                }
            });
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
                if(response.data.status == 201 || response.data.status == 204) {
                    this.getData();
                    this.closeForm();
                } else {
                    alert(response.data.message);
                }

            })
            .catch(error => {
                console.log("greska!!!");
                console.log(error.message);
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
        },
        resetSearchForm() {
            this.searchForm.firstName = '';
            this.searchForm.lastName = '';
            this.searchForm.userStatus = 0;
            this.getData();
        }

    },
};
</script>

<style lang="scss" scoped>

</style>
