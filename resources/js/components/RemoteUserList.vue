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
                        <b-input-group size="sm" class="m-1">
                            <b-form-input
                                type="search"
                                id="searchLastName"
                                placeholder="Po email adresi ..."
                                v-model="searchForm.email"
                            ></b-form-input>
                            <template #append>
                                <b-input-group-text><b-icon-zoom-in></b-icon-zoom-in></b-input-group-text>
                            </template>
                        </b-input-group>

                    </b-form>
                </b-col>
                <b-col lg="2">
                    <div class="d-flex align-items-center justify-content-end justify-content-right flex-row w-100 h-100">
                        <b-button variant="outline-secondary" size="sm" class="m-1" title="Filtriraj" @click="submitFilter"><i class="bi bi-filter"></i></b-button>
                        <b-button variant="success" size="sm" title="Resetuj filter" @click="cancelFilter"><i class="bi bi-arrow-repeat"></i></b-button>
                    </div>
                </b-col>
            </b-row>


        <div class="d-flex align-items-center justify-content-center flex-column w-100">
            <b-table
                responsive
                small bordered hover
                selectable
                select-mode="range"
                :items="items"
                :fields="fields"
                head-variant="dark"
                class="w-100 h-100"
                @row-selected="onRowSelected"
                >
                <template #cell(enabled)="data">
                    <div class="d-flex align-items-center justify-content-center">
                        <img v-if="data.item.enabled" src="/images/greenuser.png" height="15" />
                        <img v-else src="/images/reduser.png" height="15" />
                    </div>
                </template>
                <template #cell(activity)="data">
                    <div class="d-flex align-items-center justify-content-center">
                        <a href="#" @click.prevent="editClicked(data.item.id)" class="mx-1"><b-icon icon="pencil"></b-icon></a>
                        <a href="#" @click.prevent="deleteClicked(data.item.id, data.item.username)" class="mx-1"><b-icon icon="trash"></b-icon></a>
                    </div>
                </template>
            </b-table>
            <!-- <b-pagination
                v-model="currentPage"
                :total-rows="items.length"
                :per-page="pageSize"
                aria-controls="profileTable"
                align="right"
                ></b-pagination> -->
            <key-cloak-pagination v-model="currentPosition" ref="nav" :count="rowsCount"></key-cloak-pagination>
            <div class="d-flex align-items-center justify-content-center">
                <b-button variant="primary" @click="createUser" class="m-1" size="sm"><i class="bi bi-person-fill-add mr-1"></i>{{ _('gui.Add')}}</b-button>
                <b-button variant="warning" size="sm" title="Obriši izabrane" class="m-1" @click="deleteSelectedClicked" :disabled="busy">
                    <b-spinner small v-if="busy"></b-spinner>
                    <i class="bi bi-database-dash mr-1"></i>
                    {{_('gui.DeleteSelected')}}
                </b-button>
                <b-button variant="danger" size="sm" title="Obriši sve" class="m-1" @click="deleteAllClicked" :disabled="busyDeleteAll">
                    <b-spinner small v-if="busyDeleteAll"></b-spinner>
                    <i class="bi bi-database-dash mr-1"></i>
                    {{ _('gui.DeleteAll') }}
                </b-button>
                <a class="btn btn-sm btn-primary float-right m-1" role="button" href="/remoteusers/export"><i class="bi bi-box-arrow-right mr-2"></i>Export</a>
            </div>
            <div class="d-flex align-items-center justify-content-center">
                <b-button variant="outline-success" class="m-1" size="sm" @click="sendMailSelected">
                    <div class="d-flex flex-column">
                        <b-progress v-if="mailToSendCount > 0" :max="mailToSendCount" show-value>
                            <b-progress-bar :value="mailSentCount" variant="success"></b-progress-bar>
                        </b-progress>
                        <div class="d-flex">
                            <i class="bi bi-envelope-check mr-2"></i>Pošalji mail izabranima
                        </div>
                    </div>
                </b-button>
                <b-button variant="success" class="m-1" size="sm" @click="sendMailToEverybody"><i class="bi bi-envelope mr-2"></i>Pošalji mail svima</b-button>
            </div>

        </div>
        <b-modal ref="userFormDialog" size="lg" header-bg-variant="dark" header-text-variant="light">
            <template #modal-header>{{ userDialogTitle }}</template>
            <user-admin-form ref="remoteUserForm" :user-id="selectedUserId"></user-admin-form>
            <template #modal-footer>
                <b-button type="button" variant="primary" @click="onOk">{{ _('gui.Accept')}}</b-button>
                <b-button type="button" @click="onCancel">{{ _('gui.Cancel')}}</b-button>
            </template>
        </b-modal>
        <b-modal ref="deleteDialog" header-bg-variant="dark" header-text-variant="light">
            <template #modal-header>Brisanje korisnika</template>
            <p>{{ deleteDialogMessage }}</p>
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
    watch: {
        currentPosition : {
            handler(oldVal, newVal) {
                if(this.isFilter) {
                    this.getFilter();
                } else {
                    this.getData();
                }
            }
        }
    },
    data() {
        return {
            isFilter: false,
            deleteDialogMessage: '',
            deleteMode: 1,
            currentPage: 1,
            currentPosition: 0,
            pageSize: 15,
            rowsCount: 0,
            selectedUserId: '',
            selectedUsername: '',
            userDialogTitle: window.i18n.gui.addUser,
            items: [],
            fields: [
                {
                    key: "username",
                    sortable: true,
                    label: 'Korisnik',
                    thStyle: {
                        width: "10%"
                    }
                },
                {
                    key: "firstName",
                    sortable: true,
                    label: window.i18n.gui.firstName,
                    thStyle: {
                        width: "15%"
                    }
                },
                {
                    key: "lastName",
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
                        width: '5%'
                    }
                },



            ],
            accessToken: '',
            searchForm: {
                firstName: '',
                lastName: '',
                email: '',
            },
            selected: [],
            busy: false,
            busyDeleteAll: false,
            mailToSendCount: 0,
            mailSentCount: 0
        };
    },

    async mounted() {
        console.log('mounted!!!');
        await this.getData();
    },

    methods: {
        /**
         * Table row selected event
         */
        onRowSelected(items) {
            this.selected = items;
        },
        /**
         * Filter submitted from the search form
         */
        async submitFilter() {
            this.isFilter = true;
            if(this.currentPosition != 0) {
                this.currentPosition = 0;
            } else {
                await this.getFilter();
            }
            this.$refs.nav.start();
        },
        /**
         * Reset filter
         */
        async cancelFilter() {
            this.isFilter = false;
            await this.resetSearchForm();
        },
        /**
         * Get filtered data
         */
        async getFilter() {
            await axios.get('/remoteusers/keycloak')
            .then(response => {
                console.log(response.data);
                this.accessToken = response.data.access_token;
            })
            .then(error => {
                console.log(error);
            });

            let formData = new FormData();
            formData.append('token', this.accessToken);
            formData.append('first', this.currentPosition);
            formData.append('max', this.pageSize);
            for(let property in this.searchForm) {
                formData.append(property, this.searchForm[property]);
            }

            await axios.post('/remoteusers/filtercount', formData)
            .then(response => {
                console.log(response.data);
                this.rowsCount = response.data;
            });

            await axios.post('/remoteusers/filterUsers', formData)
            .then(response => {
                console.log(response.data);
                this.items = [];
                for(let property in response.data) {
                    this.items.push(response.data[property]);
                }

            });
        },
        /**
         * Get unfiltered data
         */
        async getData() {
            await axios.get('/remoteusers/keycloak')
            .then(response => {
                console.log(response.data);
                this.accessToken = response.data.access_token;
            })
            .then(error => {
                console.log(error);
            });

            this.items = [];

            let formData = new FormData();
            formData.append('token', this.accessToken);
            await axios.post('/remoteusers/count', formData)
            .then(response => {
                console.log(response.data);
                this.rowsCount = response.data;
            });

            formData.append('first', this.currentPosition);
            formData.append('max', this.pageSize);

            await axios.post('/remoteusers/data', formData)
            .then(response => {
                console.log(response.data);
                for(let i = 0; i < response.data.length; i++) {
                    this.items.push(response.data[i]);
                }

                // this.rowsCount = response.data.length;

            });
        },
        /**
         * Help function - show the user edit/create form
         */
        showForm() {
            this.$refs.userFormDialog.show();
        },
        /**
         * Help function - Close the user edit/create form
         */
        closeForm() {
            this.$refs.userFormDialog.hide();
            this.selectedUserId = '';
        },
        /**
         * Ok selected in user edit/create form
         */
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
        /**
         * Shows the form for the user creation
         */
        createUser() {
            this.userDialogTitle = window.i18n.gui.addUser;
            this.showForm();
        },
        /**
         * Cancel edit/create form
         */
        onCancel() {
            this.closeForm();
        },
        /**
         * Edit icon clicked
         */
        editClicked(id)  {
            this.selectedUserId = id;
            this.userDialogTitle = window.i18n.gui.changeUser;
            this.showForm();
        },
        /**
         * Delete icon clicked
         */
        deleteClicked(id, username) {
            this.deleteMode = 1;
            this.selectedUserId = id;
            this.selectedUsername = username;
            this.deleteDialogMessage = "Da li ste sigurni da hoćete da obrišete korisnike '" + this.selectedUsername + "'?";
            this.$refs.deleteDialog.show();
        },
        /**
         * Delete selected rows/users
         */
        deleteSelectedClicked() {

            this.deleteMode = 2;
            this.deleteDialogMessage = "Da li ste sigurni da hoćete da obrišete odabrane korisnike?";
            this.$refs.deleteDialog.show();

        },
        /**
         * Delete all rows/users.
         */
        deleteAllClicked() {

            this.deleteMode = 3;
            this.deleteDialogMessage = "Da li ste sigurni da hoćete da obrišete sve korisnike?";
            this.$refs.deleteDialog.show();
        },
        /**
         * Confim action selected in the delete dialog
         */
        async onDelete() {

            this.$refs.deleteDialog.hide();
            if(this.deleteMode == 1) {
                await this.deleteFromIcon();
            } else if (this.deleteMode == 2) {
                await this.onDeleteSelected();
            } else {
                await this.onDeleteAll();
            }

        },
        /**
         * Afirmative callback from the deletion from icon.
         */
        async deleteFromIcon() {
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
        /**
         * Afirmative callback from the deletion of the selected ones.
         */
        async onDeleteSelected() {
            if(this.selected.length == 0)
                return;

            this.busy = true;

            await axios.get('/remoteusers/keycloak')
            .then(response => {
                this.accessToken = response.data.access_token;
            });

            for(var i = 0; i < this.selected.length; i++) {
                let selectedItem = this.selected[i];

                let formData = new FormData();
                formData.append('token', this.accessToken);
                formData.append('userId', selectedItem.id);

                await axios.post('/remoteusers/delete', formData)
                .then(response => {
                    console.log(response.data);
                });
            }

            await this.getData();
            this.busy = false;
        },
        /**
         * Afirmative callback from the deletion of everything.
         */
        async onDeleteAll() {
            this.busyDeleteAll = true;

            await axios.get('/remoteusers/deleteall')
            .then(response => {
                console.log(response.data);
            });

            await this.getData();
            this.busyDeleteAll = false;
        },
        /**
         * Negative callback from the deletion dialog.
         */
        onCancelDelete() {
            this.$refs.deleteDialog.hide();
            this.selectedUserId = 0;
            this.selectedUsername = '';
        },
        /**
         * Reset search form fields, and get the data.
         */
        async resetSearchForm() {
            this.searchForm.firstName = '';
            this.searchForm.lastName = '';
            this.searchForm.userStatus = 0;
            this.searchForm.userRole = 0;
            await this.getData();
            this.$refs.nav.start();
        },
        /**
         * Enable all selected
         */
        enableSelected() {

        },
        /**
         * Disable all selected
         */
        disableSelected() {

        },
        /**
         * Send the password reset mail to all selected\
         */
        async sendMailSelected() {
            console.log('Pressed send mail selected ...');
            console.log("There are " + this.selected.length + " selected users.");
            this.mailToSendCount = this.selected.length;
            this.mailSentCount = 0;
            if(this.mailToSendCount > 0) {
                for(let i = 0; i < this.selected.length; i++) {
                    let user = this.selected[i];
                    await axios.get('remoteusers/' + user.id + '/updatePassword')
                    .then(response => {
                        this.mailSentCount ++;
                    })
                }

                this.mailSentCount = 0;
                this.mailToSendCount = 0;

            }
        },
        async sendMailToEverybody() {
            console.log(this.rowsCount);
            let formData = new FormData();
            formData.append('token', this.accessToken);
            formData.append('first', 0);
            formData.append('max', this.rowsCount);

            axios.post('/remoteusers/data', formData)
            .then(response => {
                let users = response.data;
                var userids = [];
                for(let i = 0; i < users.length; i++) {
                    userids.push(users[i].id);
                }

                console.log(userids);
            });

        }

    },
};
</script>

<style lang="scss" scoped>

</style>
