<template>
    <div>
        <div v-if="showSpinner" class="d-flex align-items-center justify-content-center w-100 h-100 position-absolute">
            <b-spinner></b-spinner>
        </div>

        <b-form size="sm" @submit.prevent="sendData" autocomplete="nope">
            <b-row v-if="userId == 0">
                <b-col>
                    <b-form-group label="Imejl adresa" label-for="email" autocomplete="nope">
                        <b-input ref="email" id="email" type="email" v-model="form.email"></b-input>
                        <span v-if="errors.email" class="text-danger">{{ errors.email}}</span>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Ponovite imejl adresu" label-for="email-repeat">
                        <b-input id="email-repeat" type="email" v-model="form.repeated_email"></b-input>
                        <span v-if="errors.repeated_email" class="text-danger">{{ errors.repeated_email}}</span>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-row v-if="userId == 0">
                <b-col>
                    <b-form-group label="Lozinka" label-for="password" autocomplete="newhope">
                        <b-input ref="password" id="password" type="password" v-model="form.password"></b-input>
                        <span v-if="errors.password" class="text-danger">{{ errors.password}}</span>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Ponovite lozinku" label-for="password-repeat">
                        <b-input id="password-repeat" type="password" v-model="form.repeated_password"></b-input>
                        <span v-if="errors.repeated_password" class="text-danger">{{ errors.repeated_password}}</span>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-form-group v-if="userId != 0" label="Imejl adresa" label-for="email" autocomplete="nope">
                <b-input id="email" type="email" v-model="form.email"></b-input>
                <span v-if="errors.email" class="text-danger">{{ errors.email}}</span>
            </b-form-group>

            <b-row>
                <b-col>
                    <b-form-group label="Ime" label-for="ime" >
                        <b-input id="ime" type="text" v-model="form.ime"></b-input>
                        <span v-if="errors.ime" class="text-danger">{{ errors.ime}}</span>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Prezime" label-for="prezime" >
                        <b-input id="prezime" type="text" v-model="form.prezime"></b-input>
                        <span v-if="errors.prezime" class="text-danger">{{ errors.prezime}}</span>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-form-group label="Država" label-for="country">
                <b-form-select id="country" v-model="form.country" :options="countries"></b-form-select>
                <span v-if="errors.country" class="text-danger">{{ errors.country}}</span>
            </b-form-group>
            <b-form-group label="Adresa i broj" label-for="adresa">
                <b-form-input id="adresa" v-model="form.adresa"></b-form-input>
                <span v-if="errors.adresa" class="text-danger">{{ errors.adresa}}</span>
            </b-form-group>
            <b-row>
                <b-col>
                    <b-form-group label="Poštanski broj" label-for="pb">
                        <b-input id="pb" type="text" v-model="form.pb"></b-input>
                        <span v-if="errors.pb" class="text-danger">{{ errors.pb}}</span>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Mesto" label-for="mesto">
                        <b-input id="mesto" type="text" v-model="form.mesto"></b-input>
                        <span v-if="errors.mesto" class="text-danger">{{ errors.mesto}}</span>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-form-group label="Broj telefona 1" label-for="tel1">
                        <b-input id="tel1" type="text" v-model="form.tel1"></b-input>
                        <span v-if="errors.tel1" class="text-danger">{{ errors.tel1}}</span>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Broj telefona 2" label-for="tel2">
                        <b-input id="tel2" type="text" v-model="form.tel2"></b-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-form-checkbox v-model="form.isTeacher" :value="true">Da li je nastavnik?</b-form-checkbox>
            <div v-if="form.isTeacher" class="mt-3">
                <b-row>
                    <b-col>
                        <b-form-group label="Tip ustanove" label-for="institution_type">
                            <b-form-select v-model="institutionType" id="institution_type" :options="institutionTypes" @change="filterSchools"></b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <b-form-group label="Opštine" label-for="municipalities">
                            <b-form-select v-model="municipality" id="municipalities" :options="municipalities" @change="filterSchools"></b-form-select>
                        </b-form-group>
                    </b-col>
                </b-row>

                <b-form-group label="Škola" label-for="school">
                    <b-form-select v-model="form.school" id="school" :options="schools"></b-form-select>
                    <span v-if="errors.school" class="text-danger">{{ errors.school}}</span>
                </b-form-group>


                <h5 class="mb-0">Predmeti</h5>
                <small class="text-secondary mt-0">(Možete izabrati i više od jednog odgovora)</small>
                <div class="d-flex flex-column flex-wrap" style="height:300px">
                    <b-form-checkbox
                        v-for="subject in subjects"
                        :key="subject.id"
                        v-model="form.subjects"
                        :value="subject.id">
                        {{ subject.name }}
                    </b-form-checkbox>
                </div>
                <span v-if="errors.subjects" class="text-danger">{{ errors.subjects}}</span>

                <h5 class="mt-3 mb-0">Profesionalni status</h5>
                <small class="text-secondary mt-0">(Možete izabrati i više od jednog odgovora)</small>
                <div class="d-flex flex-column flex-wrap" style="height:150px">
                    <b-form-checkbox
                        v-for="professionalStatus in professionalStatuses"
                        :key="professionalStatus.id"
                        v-model="form.professionalStatuses"
                        :value="professionalStatus.id">
                        {{ professionalStatus.name }}
                    </b-form-checkbox>
                </div>
                <span v-if="errors.professionalStatuses" class="text-danger">{{ errors.professionalStatuses}}</span>
            </div>
        </b-form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'UserForm',
    props: {
        userId: { typeof: Number, default: 0 }
    },
    computed : {
        action() {
            if(this.userId != 0) {
                return '/appusers/edit';
            } else {
                return '/appusers/create';
            }
        }
    },
    data() {
        return {
            form: {
                id: 0,
                ime: '',
                prezime: '',
                email: '',
                repeated_email: '',
                password: '',
                repeated_password: '',
                country: 0,
                adresa: '',
                pb: '',
                mesto: '',
                tel1: '',
                tel2: '',
                isTeacher: false,
                school: 0,
                subjects: [],
                professionalStatuses: []
            },
            subjects: [],
            selectedSubjects: [],
            professionalStatuses: [],
            countries: [],
            municipality: 0,
            institutionTypes: [],
            institutionType: 0,
            selectedProfessionalStatuses: [],
            municipalities: [],
            schools: [],
            passDontMatch: false,
            emailDontMatch: false,
            errors: {},
            showSpinner: false,
        };
    },

    async mounted() {
        this.form.email = '';
        this.form.password = '';

        await this.getData();
    },

    methods: {
        async getCountries() {
            await axios.get('/countries')
            .then(response => {
                this.countries.length = 0;
                for(let property in response.data) {
                    let item = response.data[property];
                    this.countries.push({
                        value: item.id,
                        text: item.name
                    })
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
                this.professionalStatuses = response.data;
            });
        },
        async getMunicipalities() {
            await axios.get('/municipalities')
            .then(response => {
                this.municipalities.length = 0;
                this.municipalities.push({
                    value: 0,
                    text: "Izaberite opštinu"
                });

                for(let property in response.data) {
                    let item = response.data[property];
                    this.municipalities.push({
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
        async getUserData() {
            let formData = new FormData();
            formData.append("id", this.userId);
            await axios.post('/appusers/user', formData)
            .then(response => {
                let userObject = response.data;
                console.log(userObject);

                this.form.email = this.form.repeated_email = userObject.email;
                this.form.password = this.form.repeated_password = userObject.password;
                this.form.ime = userObject.ime;
                this.form.prezime = userObject.prezime;
                this.form.adresa = userObject.adresa;
                this.form.pb = userObject.pb;
                this.form.mesto = userObject.mesto;
                this.form.country = userObject.country["id"];
                this.form.tel1 = userObject.tel1;
                this.form.tel2 = userObject.tel2;

                if(userObject.isTeacher) {
                    this.municipality = userObject.school["municipality"];
                    this.institutionType = userObject.school["institutionType"];
                    this.filterSchools();
                    this.form.school = userObject.school["id"];

                    this.form.subjects = [];
                    for(var i = 0; i < userObject.subjects.length; i++) {
                        let subject = userObject.subjects[i];
                        this.form.subjects.push(subject.id);
                    }

                    this.form.professionalStatuses = [];
                    for(var i = 0; i < userObject.professionalStatuses.length; i++) {
                        let status = userObject.professionalStatuses[i];
                        this.form.professionalStatuses.push(status.id);
                    }
                }

                this.form.isTeacher = userObject.isTeacher;
            });
        },
        async getData() {
            if(this.userId != 0) {
                this.showSpinner = true;
            }


            await this.getCountries();
            await this.getSubjects();
            await this.getProfessionalStatuses();
            await this.getInstitutionTypes();
            await this.getMunicipalities();

            if(this.userId != 0) {
                await this.getUserData();
                this.showSpinner = false;
            } else {
                this.form.email = null;
                this.form.password = null;
            }

        },
        async filterSchools() {
            let formData = new FormData();
            formData.append('municipalityId', this.municipality);
            formData.append('institutionTypeId', this.institutionType);
            axios.post('/schools', formData)
            .then(response => {
                this.schools.length = 0;
                for(let property in response.data) {
                    let item = response.data[property];
                    this.schools.push({ value: item.id, text: item.name});
                }
            });
        },
        sendData() {
            return new Promise((resolve, reject) => {
                let formData = new FormData();
                this.form.id = this.userId;
                for(let property in this.form) {
                    if(property == 'subjects') {
                        let subjects = this.form[property];
                        for(let idx in subjects) {
                            formData.append('subjects[]', subjects[idx]);
                        }
                    }
                    else if(property == 'professionalStatuses') {
                        let statuses = this.form[property];
                        for(let idx in statuses) {
                            formData.append('professionalStatuses[]', statuses[idx]);
                        }
                    }
                    else {
                        formData.append(property, this.form[property]);
                    }
                }

                axios.post(this.action, formData)
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
