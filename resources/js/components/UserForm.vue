<template>
    <div>
        <b-form @submit.prevent="sendData">
            <b-form-group
                id="name-group"
                label="Name"
                label-for="name">
                <b-form-input
                    id="name"
                    v-model="form.name"
                    placeholder="Ime korisnika"
                ></b-form-input>
                <span v-if="errors.name" class="text-danger">{{ errors.name}}</span>
            </b-form-group>
            <b-form-group
                id="email-group"
                label="Email"
                label-for="email">
                <b-form-input
                    id="email"
                    type="email"
                    v-model="form.email"
                    placeholder="Email korisnika"
                ></b-form-input>
                <span v-if="errors.email" class="text-danger">{{ errors.email}}</span>
            </b-form-group>
            <b-form-group
                label="Rola">
                <b-form-select v-model="form.role" :options="roles"></b-form-select>
                <span v-if="errors.role" class="text-danger">{{ errors.role}}</span>
            </b-form-group>
        </b-form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'UserForm',

    data() {
        return {
            form: {
                name: '',
                email: '',
                role: 2
            },
            errors: {},
            roles: [],

        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
        async getData() {
            await axios.get('/roles/list')
            .then(response => {
                for(let property in response.data) {
                    this.roles.push(response.data[property]);
                }
            });
        },
        async sendData() {
            return new Promise((resolve, reject) => {
                let formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('email', this.form.email);
                formData.append('role', this.form.role);

                axios.post('/users/create', formData)
                .then(response => {
                    resolve(response);
                })
                .catch(error => {
                    this.errors = {};
                    for(let err in error.response.data.errors) {
                        this.errors[err] = error.response.data.errors[err][0];
                    }
                    reject(error);
                })
            });
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
