<template>
    <div>
        <b-form @submit.prevent="sendData">
            <b-form-group label="Korisničko ime" label-for="username">
                <b-input id="username" v-model="form.username"></b-input>
            </b-form-group>
            <b-row>
                <b-col>
                    <b-form-group label="Ime" label-for="firstName">
                        <b-input id="firstName" v-model="form.firstName"></b-input>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Prezime" label-for="lastName">
                        <b-input id="lastName" v-model="form.lastName"></b-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-form-group label="Imejl" label-for="email">
                <b-input id="email" v-model="form.email"></b-input>
            </b-form-group>
            <b-form-checkbox v-model="form.enabled" :value="true">{{ _('gui.enabled') }}</b-form-checkbox>
            <b-form-checkbox v-model="form.updatePassword" :value="true">{{ _('gui.updatePassword') }}</b-form-checkbox>
        </b-form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'RemoteUserForm',
    props: {
        userId: {typeof: String, default: ''},
    },
    data() {
        return {
            form: {
                username: '',
                firstName: '',
                lastName: '',
                email: '',
                enabled: false,
                updatePassword: true
            },
            accessToken: '',
            errors: {},
            formAction: '/remoteusers/create'
        };
    },

    async mounted() {
        if(this.userId != null && this.userId != '') {
            await this.getData();
            this.form.updatePassword = false;
        }
    },

    methods: {
        async getData() {
            await axios.get('/remoteusers/keycloak')
            .then(response => {
                this.accessToken = response.data.access_token;
            });

            let formData = new FormData();
            formData.append('token', this.accessToken);
            formData.append('userId', this.userId);
            await axios.post('/remoteusers/userData', formData)
            .then(response => {
                let resultObject = response.data;
                for(let property in this.form) {
                    if(property == 'requiredActions')
                        continue;
                    this.form[property] = resultObject[property];
                }
            });
        },
        async sendData() {
            await axios.get('/remoteusers/keycloak')
            .then(response => {
                this.accessToken = response.data.access_token;
            });

            return new Promise((resolve, reject) => {
                let formData = new FormData();
                formData.append('token', this.accessToken);
                for(let property in this.form) {
                    formData.append(property, this.form[property]);
                }

                if(this.userId != null && this.userId != '') {
                    formData.append('userId', this.userId);
                    this.formAction = '/remoteusers/update';
                } else {
                    this.formAction = '/remoteusers/create';
                }

                axios.post(this.formAction, formData)
                .then(response => {
                    resolve(response);
                })
                .catch(error => {
                    this.errors = {};
                    for(let err in error.response.data.errors) {
                        this.errors[err] = error.response.data.errors[err][0];
                    }
                    reject(error);
                });
            });
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
