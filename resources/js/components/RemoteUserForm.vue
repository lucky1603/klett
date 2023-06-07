<template>
    <div>
        <div v-if="showSpinner" class="d-flex align-items-center justify-content-center w-100 h-100 position-absolute">
            <b-spinner></b-spinner>
        </div>
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

            <b-form-checkbox v-model="form.isTeacher" :value="true">{{ _('gui.isTeacher') }}</b-form-checkbox>
            <div v-if="form.isTeacher">

                <b-row>
                    <b-col>
                        <b-form-group :label="_('gui.institutionType')" label-for="institution_type">
                            <b-form-select v-model="form.institutionType" id="institution_type" :options="institutionTypes" @change="filterSchools"></b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <b-form-group :label="_('gui.municipality')" label-for="township">
                            <b-form-select v-model="form.township" id="township" :options="townships" @change="filterSchools"></b-form-select>
                        </b-form-group>
                    </b-col>
                </b-row>

                <b-form-group :label="_('gui.school')" label-for="school">
                    <b-form-select v-model="form.institution" id="school" :options="schools"></b-form-select>
                    <span v-if="errors.school" class="text-danger">{{ errors.school}}</span>
                </b-form-group>


                <h5 class="mb-0">{{ _('gui.subjects') }}</h5>
                <small class="text-secondary mt-0">{{_('gui.subjectsSubtitle')}}</small>
                <div class="d-flex flex-column flex-wrap" style="height:300px">
                    <b-form-checkbox
                        v-for="subject in subjects"
                        :key="subject.id"
                        v-model="form.subjects"
                        :value="subject.id">
                        {{ subject.name }}
                    </b-form-checkbox>
                </div>

                <h5 class="mt-3 mb-0">{{_('gui.professionalStatus')}}</h5>
                <small class="text-secondary mt-0">{{_('gui.professionalStatusSubtitle')}}</small>
                <div class="d-flex flex-column flex-wrap" style="height:150px">
                    <b-form-checkbox
                        v-for="profession in professions"
                        :key="profession.id"
                        v-model="form.professions"
                        :value="profession.id">
                        {{ profession.name }}
                    </b-form-checkbox>
                </div>
            </div>

            <hr />
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
                updatePassword: true,
                isTeacher: false,
                institutionType: 0,
                township: 0,
                institution: 0,
                subjects : [],
                professions: []
            },
            accessToken: '',
            errors: {},
            formAction: '/remoteusers/create',
            subjects: [],
            townships: [],
            schools: [],
            institutionTypes: [],
            professions: [],
            showSpinner: false
        };
    },

    async mounted() {

        this.showSpinner = true;
        await this.getSubjects();
        await this.getProfessionalStatuses();
        await this.getInstitutionTypes();
        await this.getMunicipalities();

        if(this.userId != null && this.userId != '') {
            await this.getData();
            this.form.updatePassword = false;
            await this.getUserGroup();
        } else {
            this.showSpinner = false;
        }
    },

    methods: {
        async filterSchools() {
            let formData = new FormData();
            formData.append('municipalityId', this.form.township);
            formData.append('institutionTypeId', this.form.institutionType);
            axios.post('/schools', formData)
            .then(response => {
                this.schools.length = 0;
                for(let property in response.data) {
                    let item = response.data[property];
                    this.schools.push({ value: item.id, text: item.name});
                }
            });
        },
        async getSubjects() {
            await axios.get('/subjects')
            .then(response => {
                this.subjects = response.data;
            });
        },
        async getProfessionalStatuses() {
            await axios.get('/professional_statuses')
            .then(response => {
                this.professions = response.data;
            });
        },
        async getMunicipalities() {
            await axios.get('/municipalities')
            .then(response => {
                this.townships.length = 0;
                this.townships.push({
                    value: 0,
                    text: "Izaberite opštinu"
                });

                for(let property in response.data) {
                    let item = response.data[property];
                    this.townships.push({
                        value: item.id,
                        text: item.name
                    });
                }
            });
        },
        async getInstitutionTypes() {
            await axios.get('/institution_types')
            .then(response => {
                this.institutionTypes.length = 0;
                this.institutionTypes.push({
                    value: 0,
                    text: "Izaberite tip ustanove"
                });
                for(let property in response.data) {
                    let item = response.data[property];
                    this.institutionTypes.push({
                        value: item.id,
                        text: item.name
                    });
                }
            });
        },
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
                console.log(resultObject);
                for(let property in this.form) {
                    if(property == 'requiredActions' || property == 'isTeacher')
                        continue;
                    if(property == 'institution') {
                        this.filterSchools();
                    }
                    this.form[property] = resultObject[property];
                }

                this.showSpinner = false;

                // if(this.form.institution != null && this.form.institution != undefined) {
                //     this.form.isTeacher = true;
                // }
            });
        },
        async getUserGroup() {
            let token = '';
            await axios.get('remoteusers/keycloak')
            .then(response => {
                token = response.data.access_token;
            });

            if(token != '' && this.userId != 0) {
                let formData = new FormData();
                formData.append('token', token);
                formData.append('userId', this.userId);
                await axios.post('/remoteusers/user_group', formData)
                .then(response => {
                    let group = response.data;
                    console.log(group);

                    if(group.name == "Teacher") {
                        this.form.isTeacher = true;
                    } else {
                        this.form.isTeacher = false;
                    }
                });
            }
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
                    if(property == 'subjects') {
                        let subjects = this.form[property];
                        for(let idx in subjects) {
                            formData.append('subjects[]', subjects[idx]);
                        }
                    }
                    else if(property == 'professions') {
                        let statuses = this.form[property];
                        for(let idx in statuses) {
                            formData.append('professions[]', statuses[idx]);
                        }
                    }
                    else {
                        formData.append(property, this.form[property]);
                    }
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
        },

    },
};
</script>

<style lang="scss" scoped>

</style>
