<template>
    <div class="h-100 w-100">
        <div v-if="showSpinner" class="d-flex align-items-center justify-content-center h-100 w-100 position-absolute">
            <b-spinner variant="primary" type="grow" style="z-index: 1000"></b-spinner>
        </div>
        <b-form @submit.prevent="sendData">
            <b-row>
                <b-col>
                    <b-form-group label="Korisničko ime" label-for="username">
                        <b-input id="username" v-model="form.username"></b-input>
                        <span v-if="errors.username" class="text-danger">{{ errors.username}}</span>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Imejl" label-for="email">
                        <b-input id="email" v-model="form.email"></b-input>
                        <span v-if="errors.email" class="text-danger">{{ errors.email}}</span>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-form-group label="Ime" label-for="firstName">
                        <b-input id="firstName" v-model="form.firstName"></b-input>
                        <span v-if="errors.firstName" class="text-danger">{{ errors.firstName}}</span>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Prezime" label-for="lastName">
                        <b-input id="lastName" v-model="form.lastName"></b-input>
                        <span v-if="errors.lastName" class="text-danger">{{ errors.lastName}}</span>
                    </b-form-group>
                </b-col>
            </b-row>


            <b-row>
                <b-col cols="5">
                    <b-form-group label="Adresa i broj" label-for="billingAddress">
                        <b-input id="billingAddress" v-model="form.address"></b-input>
                        <span v-if="errors.address" class="text-danger">{{ errors.address}}</span>
                    </b-form-group>
                </b-col>
                <b-col cols="2">
                    <b-form-group label="Poštanski broj" label-for="pb">
                        <b-input id="pb" v-model="form.pb"></b-input>
                        <span v-if="errors.pb" class="text-danger">{{ errors.pb}}</span>
                    </b-form-group>
                </b-col>
                <b-col cols="5">
                    <b-form-group label="Mesto" label-for="city">
                        <b-input id="city" v-model="form.city"></b-input>
                        <span v-if="errors.city" class="text-danger">{{ errors.city}}</span>
                    </b-form-group>
                </b-col>
                <!-- <b-col cols="5">
                    <b-form-group label="Država" label-for="country">
                        <b-select id="country" v-model="form.country" :options="countries"></b-select>
                    </b-form-group>
                </b-col> -->
            </b-row>
            <b-row>
                <b-col>
                    <b-form-group label="Telefon 1" label-for="tel1">
                        <b-input id="tel1" placeholder="Unesite broj telefona" v-model="form.tel1"></b-input>
                        <span v-if="errors.tel1" class="text-danger">{{ errors.tel1}}</span>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Telefon 2" label-for="tel2">
                        <b-input id="tel2" placeholder="Unesite alternativni broj telefona" v-model="tel2"></b-input>
                    </b-form-group>
                </b-col>
            </b-row>
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
                    <span v-if="errors.institution" class="text-danger">{{ errors.institution}}</span>
                </b-form-group>
                <!-- <h5 class="mb-0">{{ _('gui.subjects') }}</h5> -->
                <!-- <small class="text-secondary mt-0">{{_('gui.subjectsSubtitle')}}</small> -->
                <!-- <div class="d-flex flex-column flex-wrap" style="height:300px">
                    <b-form-checkbox
                        v-for="subject in subjects"
                        :key="subject.id"
                        v-model="form.subjects"
                        :value="subject.id">
                        {{ subject.name }}
                    </b-form-checkbox>
                </div> -->

                <b-form-group :label="_('gui.subjects')" label-for="subjects">
                    <b-form-select v-model="form.subjects" :options="subjects" multiple :select-size="6"></b-form-select>
                    <span v-if="errors.subjects" class="text-danger">{{ errors.subjects}}</span>
                </b-form-group>


                <!-- <h5 class="mt-3 mb-0">{{_('gui.professionalStatus')}}</h5>
                <small class="text-secondary mt-0">{{_('gui.professionalStatusSubtitle')}}</small>
                <div class="d-flex flex-column flex-wrap" style="height:150px">
                    <b-form-checkbox
                        v-for="profession in professions"
                        :key="profession.id"
                        v-model="form.professions"
                        :value="profession.id">
                        {{ profession.name }}
                    </b-form-checkbox>
                </div> -->
            </div>

            <hr />
            <div v-if="!anonimous">
                <b-form-checkbox v-model="form.enabled" :value="true">{{ _('gui.enabled') }}</b-form-checkbox>
                <b-form-checkbox v-model="form.updatePassword" :value="true">{{ _('gui.updatePassword') }}</b-form-checkbox>
            </div>

        </b-form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'RemoteUserForm',
    props: {
        userId: {typeof: String, default: ''},
        anonimous: {typeof: Boolean, default: false}
    },
    data() {
        return {
            form: {
                username: '',
                firstName: '',
                lastName: '',
                email: '',
                enabled: true,
                updatePassword: true,
                isTeacher: false,
                institutionType: 0,
                township: 0,
                institution: 0,
                subjects : [],
                professions: [],
                country: '',
                address: '',
                pb: '',
                tel1: '',
                tel2: '',
                city: ''
            },
            accessToken: '',
            errors: {},
            formAction: '/remoteusers/create',
            subjects: [],
            townships: [],
            schools: [],
            institutionTypes: [],
            professions: [],
            showSpinner: false,
            countries: []
        };
    },

    async mounted() {

        this.showSpinner = true;
        await this.getSubjects();
        await this.getProfessionalStatuses();
        await this.getInstitutionTypes();
        await this.getMunicipalities();
        await this.getCountries();

        if(this.userId != null && this.userId != '') {
            await this.getData();
            this.form.updatePassword = false;
            await this.getUserGroup();
        } else {
            this.showSpinner = false;
        }
    },

    methods: {
        async getCountries() {
            await axios.get('/countries')
            .then(response => {
                this.countries.push({
                    value: '',
                    text: 'Izaberite'
                });

                for(let property in response.data) {
                    let item = response.data[property];


                    this.countries.push({
                        value: item.code,
                        text: item.name
                    })
                }
            });
        },
        async checkCRM() {
            axios.get('/crm/checkUser/' + this.form.email)
            .then(response => {
                let values = response.data;
                if(values.length > 0) {
                    let user = values[0];
                    alert("In CRM! With ID=" + user.contactid);
                } else {
                    alert("Not in CRM");
                }
            })
        },
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
                // this.subjects = response.data;
                this.subjects = [];
                let subjects = response.data;
                for(let property in subjects) {
                    let subject = subjects[property];
                    this.subjects.push({
                        value: subject.id,
                        text: subject.name
                    });
                }
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
