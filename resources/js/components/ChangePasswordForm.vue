<template>
    <div class="container">
        <div class="jumbotron">
            <h4>Zdravo {{ user.name }}!</h4>
            <p>Molimo te, promeni svoju lozinku u formi ispod.</p>
        </div>
        <div class="d-flex align-items-center justify-content-center">
            <b-form @submit.prevent="sendData" class="w-50">
                <b-form-group label="Lozinka">
                    <b-form-input type="password" v-model="form.password"/>
                </b-form-group>
                <span v-if="errors.password" class="text-danger">{{ errors.password }}</span>
                <b-form-group label="Ponovite lozinku">
                    <b-form-input type="password" v-model="form.repeatPassword"/>
                </b-form-group>
                <span v-if="errors.repeatPassword" class="text-danger">{{ errors.repeatPassword }}</span>
                <div v-if="showButtons" class="d-flex align-items-center justify-content-center">
                    <b-button type="submit" variant="primary">Ok</b-button>
                </div>
            </b-form>
        </div>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ChangePasswordForm',
    props: {
        userToken: {typeof: String, default: ''},
        showButtons: {typeof: Boolean, default: true},
    },

    data() {        
        return {
            form: {
                password: '',
                repeatPassword: '',
            },
            user: {
                name: '',
                email: '',
                token: ''
            },
            errors: {}
        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
        async getData() {
            let formData = new FormData();
            formData.append('token', this.userToken);
            await axios.post('/users/data', formData)
            .then(response => {
                console.log(response.data);
                this.user.name = response.data.name;
                this.user.email = response.data.email;
                this.user.token = this.userToken;
            });
        },
        async sendData() {
            let formData = new FormData();
            formData.append('password', this.form.password);
            formData.append('repeatPassword', this.form.repeatPassword);
            formData.append('token', this.userToken);

            // todo send
            await axios.post('/anonimous/password', formData)
            .then(response => {
                window.location.href = "/remoteusers";
            })
            .catch(error => {
                this.errors = {};
                for(let err in error.response.data.errors) {
                    this.errors[err] = error.response.data.errors[err][0];
                }
                reject(error);
            });
        },

    },
};
</script>

<style lang="scss" scoped>

</style>
