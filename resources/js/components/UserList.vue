<template>
    <div>
        <b-table
            responsive
            small striped hover bordered
            :items="items"
            :fields="fields"
            head-variant="dark"
        >
        </b-table>
        <div class="d-flex align-items-center justify-content-center">
            <b-button variant="primary" @click="createUser" class="m-1" size="sm"><i class="bi bi-person-fill-add mr-1"></i>{{ _('gui.Add')}}</b-button>
        </div>
        <b-modal ref="userFormDialog" size="lg" header-bg-variant="dark" header-text-variant="light">
            <template #modal-header>{{ userDialogTitle }}</template>
            <user-form ref="userForm"></user-form>
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
            userDialogTitle: "Ovo je tipican naslov",
            selectedUserId: '',
            items: [],
            fields: [
                {
                    key: "name",
                    sortable: true,
                    label: window.i18n.gui.name,
                    thStyle: {
                        width: "20%"
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
                    key: "role",
                    sortable: true,
                    label: window.i18n.gui.email,
                    thStyle: {
                        width: "30%"
                    }
                },
            ],
            form: {
                name: null,
                email: null,
            }
        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
        async getData() {
            this.items = [];
            let formData = new FormData();
            formData.append('name', this.form.name);
            formData.append('email', this.form.email);
            await axios.post('/users/filter', formData)
            .then(response => {
                console.log(response.data);
                for(let property in response.data) {
                    let user = response.data[property];
                    this.items.push(user);
                }
            })
        },
        createUser() {
            this.userDialogTitle = window.i18n.gui.addUser;
            this.showForm();
        },
        onOk() {
            this.$refs.userForm.sendData()
            .then(response => {
                console.log(response.data);
                // if(response.data.status == 201 || response.data.status == 204) {
                //     this.getData();
                //     this.closeForm();
                // } else {
                //     alert(response.data.message);
                // }

                this.getData();
                    this.closeForm();

            })
            .catch(error => {
                console.log("greska!!!");
                console.log(error.message);
            });
        },
        onCancel() {
            this.closeForm();
        },
        showForm() {
            this.$refs.userFormDialog.show();
        },
        closeForm() {
            this.$refs.userFormDialog.hide();
            this.selectedUserId = '';
        },
    },
};
</script>

<style lang="scss" scoped>

</style>
