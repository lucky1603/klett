<template>
    <div class="container-fluid">
            <b-row class="w-100 mt-4">
                <b-col lg="10">
                    <b-form id="filterForm" ref="filterForm" inline class="w-100 m-2" @submit.prevent="submitFilter">
                        <b-input-group size="sm" class="m-1">
                            <b-form-input
                                type="search"
                                id="searchUsername"
                                placeholder="Po korisničkom imenu ..."
                                v-model="searchForm.username" @update="submitFilter"/>
                            <template #append>
                                <b-input-group-text><b-icon-zoom-in></b-icon-zoom-in></b-input-group-text>
                            </template>
                        </b-input-group>
                        <b-input-group size="sm" class="m-1">
                            <b-form-input
                                type="search"
                                id="searchFirstName"
                                placeholder="Po imenu ..."
                                v-model="searchForm.firstName"
                                @update="submitFilter"
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
                                @update="submitFilter"
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
                                @update="submitFilter"
                            ></b-form-input>
                            <template #append>
                                <b-input-group-text><b-icon-zoom-in></b-icon-zoom-in></b-input-group-text>
                            </template>
                        </b-input-group>
                        <b-input-group size="sm" class="m-1">
                            <b-form-select v-model="searchForm.status" :options="statuses" @change="submitFilter"/>
                        </b-input-group>
                        <b-input-group size="sm" class="m-1">
                            <b-form-select v-model="searchForm.role" :options="roles" @change="submitFilter"/>
                        </b-input-group>
                        <b-input-group size="sm" class="m-1">
                            <b-form-select v-model="searchForm.source" :options="sources" @change="submitFilter"/>
                        </b-input-group>
                        <b-input-group size="sm" class="m-1">
                            <b-form-select v-model="searchForm.klf" :options="klfs" @change="submitFilter"/>
                        </b-input-group>
                    </b-form>
                </b-col>
                <b-col lg="2">
                    <div class="d-flex align-items-center justify-content-end flex-row w-100 h-100">
                        <b-button v-if="false" variant="outline-secondary" size="sm" class="m-1" title="Filtriraj" @click="submitFilter"><i class="bi bi-filter"></i></b-button>
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
                <template #cell(klf_korisnik)="data">
                    <span v-if="data.item.klf_korisnik == '0'">NE</span>
                    <span v-else>DA</span>
                </template>
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
            <key-cloak-pagination v-model="currentPosition" ref="nav" :count="rowsCount"></key-cloak-pagination>
            <div class="d-flex align-items-center justify-content-center">
                <b-button variant="primary" @click="createUser" class="m-1" size="sm"><i class="bi bi-person-fill-add mr-1"></i>{{ _('gui.Add')}}</b-button>
                <b-button variant="warning" size="sm" title="Obriši izabrane" class="m-1" @click="deleteSelectedClicked" :disabled="busy">
                    <b-spinner small v-if="busy"></b-spinner>
                    <i class="bi bi-database-dash mr-1"></i>
                    {{_('gui.DeleteSelected')}}
                </b-button>
                <b-button v-if="superAdmin" variant="danger" size="sm" title="Obriši sve" class="m-1" @click="deleteAllClicked" :disabled="busyDeleteAll">
                    <b-spinner small v-if="busyDeleteAll"></b-spinner>
                    <i class="bi bi-database-dash mr-1"></i>
                    {{ _('gui.DeleteAll') }}
                </b-button>
                <b-button variant="primary" size="sm" title="Izvesi selektovane" class="m-1" @click="exportSelected">
                    <b-spinner v-if="showSpinner" small class="me-1"></b-spinner>
                    Export
                </b-button>
                
            </div>
        </div>
        <b-modal ref="userFormDialog" size="lg" header-bg-variant="dark" header-text-variant="light">
            <template #modal-header>{{ userDialogTitle }}</template>
            <user-admin-form ref="remoteUserForm" :user-id="selectedUserId" :super-admin="superAdmin"></user-admin-form>
            <template #modal-footer>
                <b-button type="button" variant="primary" @click="onOk">{{ _('gui.Accept')}}</b-button>
                <b-button type="button" @click="onCancel">{{ _('gui.Cancel')}}</b-button>
            </template>
        </b-modal>
        <b-modal ref="deleteDialog" header-bg-variant="dark" header-text-variant="light">
            <template #modal-header>Brisanje korisnika</template>
            <p>{{ deleteMessage }}</p>
            <b-progress v-if="userToDeleteCount > 0" :value="deletedUsers" :max="deleteUsersMax" show-progress></b-progress>
            <template #modal-footer>
                <b-button v-if="!cancelMode" type="button" variant="primary" @click="onDelete">{{ _('gui.Yes')}}</b-button>
                <b-button v-if="!cancelMode" type="button" @click="onCancelDelete">{{ _('gui.No')}}</b-button>
                <b-button v-if="cancelMode" type="button" @click="onStopDelete">Stop</b-button>
            </template>
        </b-modal>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'RemoteUserList',
    props: {
        superAdmin: { typeof: Boolean, default: false }
    },
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
    computed: {
        deleteMessage() {
            if(this.cancelMode) {
                return "Molimo, sačekajte, brisanje je u toku. Možete prekinuti pritiskom na dugme.";
            }

            switch(this.deleteMode) {
                case 1:
                    return "Da li ste sigurni da hoćete da obrišete korisnike '" + this.selectedUsername + "'?";
                case 2:
                    return "Da li ste sigurni da želite da obrišete izabrane korisnike?";
                default:
                    return "Da li ste sigurni da želite da obrišete sve korisnike?";
            }
        }
    },
    data() {
        return {
            showSpinner: false,
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
                        width: "20%"
                    }
                },
                {
                    key: "role",
                    sortable: true,
                    label: "Tip korisnika",
                    thStyle: {
                        width: "10%"
                    }
                },
                {
                    key: "klf_korisnik",
                    sortable: true,
                    label: "KLF",
                    thStyle: {
                        width: "5%",
                    }
                },
                {
                    key: 'createdAt',
                    sortable: true,
                    label: "Kreiran",
                    thStyle: {
                        width: '5%'
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
                role: 0,
                username: '',
                status: 0,
                source: null,
                klf: -1
            },
            selected: [],
            busy: false,
            busyDeleteAll: false,
            mailToSendCount: 0,
            mailSentCount: 0,
            userIds: [],
            requestingUserIds: false,
            userToDeleteCount: 0,
            deletedUsers: 0,
            deleteUsersMax: 0,
            cancelMode: false,
            stopDelete: false,
            roles: [],
            statuses: [
                { value: 0, text: "Svi statusi" },
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
                { value: 1, text: "Jeste KLF korisnik" },
            ]
        };
    },

    async mounted() {
        await this.getRoles();
        await this.getData();
        let userCount = await this.getCount();
        console.log("User count is " + userCount);
    },

    methods: {
        /**
         * Dohvati trenutni broj korisnika platforme.
         */
        async getCount() {
            let formData = new FormData();
            formData.append('token', this.accessToken);
            var userCount = 0;
            await axios.post('/remoteusers/count', formData)
            .then(response => {
                userCount = response.data;
            });

            return userCount;
        },

        /**
         * Daj grupe.
         */
        async getRoles() {
            this.roles = [{
                value: 0,
                text: 'Sve role'
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
            document.body.style.cursor = 'wait';
            this.isFilter = true;
            if(this.currentPosition != 0) {
                this.currentPosition = 0;
            } else {
                await this.getFilter();
            }
            this.$refs.nav.start();
            document.body.style.cursor = 'default';
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
            await axios.get('/keycloak')
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
                console.log("rows count...");
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
            await axios.get('/keycloak')
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
                    if(this.isFilter) this.getFilter();
                    else this.getData();
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


            if(this.deleteMode == 1) {
                await this.deleteFromIcon();
                this.$refs.deleteDialog.hide();
            } else if (this.deleteMode == 2) {
                await this.onDeleteSelected();
                this.$refs.deleteDialog.hide();
            } else {
                await this.onDeleteAll();
            }

        },
        /**
         * Afirmative callback from the deletion from icon.
         */
        async deleteFromIcon() {
            await axios.get('/keycloak')
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

        async exportSelected() {
            this.showSpinner = true;
            if(this.selected.length == 0)
                return;

            // Fill the exports table.
            for(let record of this.selected) {
                let formData = new FormData();
                for(let property in record) {
                    formData.append(property, record[property]);
                }

                await axios.post('/exports/create', formData);
                               
            }      

            // Export should be called like this
            // in order for the download to start.
            location.href='/exports/export';
            

            this.showSpinner = false;

        },

        /**
         * Afirmative callback from the deletion of the selected ones.
         */
        async onDeleteSelected() {
            if(this.selected.length == 0)
                return;

            this.busy = true;

            await axios.get('/keycloak')
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

            this.userToDeleteCount = await this.getCount();
            this.deleteUsersMax = this.userToDeleteCount;
            this.deletedUsers = 0;
            this.cancelMode = true;

            while(this.userToDeleteCount > 0 && !this.stopDelete) {
                await axios.get('/remoteusers/deleteall');
                this.userToDeleteCount -= 100;
                this.deletedUsers += 100;
            }

            this.stopDelete = false;
            this.cancelMode = false;
            this.$refs.deleteDialog.hide();

            await this.getData();
            this.busyDeleteAll = false;
        },
        onStopDelete() {
            this.stopDelete = true;
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
            this.searchForm.username = '';
            this.searchForm.email = '';
            this.searchForm.role = 0;
            this.searchForm.status = 0;
            this.searchForm.source = null;
            this.searchForm.klf = -1;
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

            let intervals = [];
            this.userIds = [];

            this.requestingUserIds = true;

            for(let i = 0; i < this.rowsCount; i += 100) {
                intervals.push({
                    first: i,
                    max: 100
                });
            }

            console.log(intervals);

            for(let i = 0; i < intervals.length; i ++) {
                let interval = intervals[i];
                let formData = new FormData();
                formData.append('token', this.accessToken);
                formData.append('first', interval.first);
                formData.append('max', interval.max);

                await axios.post('/remoteusers/data', formData)
                .then(response => {
                    let users = response.data;
                    for(let i = 0; i < users.length; i++) {
                        this.userIds.push(users[i].id);
                    }
                });
            }

            this.requestingUserIds = false;
            console.log(this.userIds);

        }

    },
};
</script>

<style lang="scss" scoped>

</style>
