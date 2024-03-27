<template>
    <div class="h-100 w-100">
        <div v-if="showSpinner" class="d-flex align-items-center justify-content-center h-100 w-100 position-absolute">
            <b-spinner variant="primary" type="grow" style="z-index: 1000"></b-spinner>
        </div>
        <b-form @submit.prevent="sendData">
            <b-row class="p-2">
                <b-col>
                    <div class="form-input">
                        <label for="username">Korisničko ime</label>
                        <b-input id="username" v-model="form.korisnickoIme" :disabled="userId != 0"></b-input>
                        <span v-if="errors.korisnickoIme" class="text-danger">{{ errors.korisnickoIme}}</span>
                    </div>
                </b-col>
                <b-col>
                    <div class="form-input">
                        <label for="email">Imejl adresa</label>
                        <b-input id="email" v-model="form.email" ></b-input>
                        <span v-if="errors.email" class="text-danger">{{ errors.email}}</span>
                    </div>
                </b-col>                
            </b-row>
            <b-row class="p-2">
                <b-col>
                    <div class="form-input">
                        <label for="firstName">Ime</label>
                        <b-input id="firstName" v-model="form.ime"></b-input>
                        <span v-if="errors.ime" class="text-danger">{{ errors.ime}}</span>
                    </div>
                </b-col>
                <b-col>
                    <div class="form-input">
                        <label for="lastName">Prezime</label>
                        <b-input id="lastName" v-model="form.prezime"></b-input>
                        <span v-if="errors.prezime" class="text-danger">{{ errors.prezime}}</span>
                    </div>
                </b-col>
                <b-col>
                    <div class="form-input">
                        <label for="tel1">Kontakt telefon</label>
                        <b-input id="tel1" placeholder="Unesite broj telefona" v-model="form.telefon1"></b-input>
                        <span v-if="errors.telefon1" class="text-danger">{{ errors.telefon1}}</span>
                    </div>
                </b-col>
            </b-row>

            <!-- <small class="text-secondary mt-0">Sledeća polja služe samo u tu svrhu</small> -->

            <b-row class="p-2">
                <b-col cols="5">
                    <b-form-group label="Ulica i broj" label-for="billingAddress">
                        <b-input id="billingAddress" v-model="form.adresa"></b-input>
                        <span v-if="errors.adresa" class="text-danger">{{ errors.adresa}}</span>
                    </b-form-group>
                </b-col>
                <b-col cols="2">
                    <b-form-group label="Poštanski broj" label-for="pb">
                        <b-input id="pb" v-model="form.postanskiBroj"></b-input>
                        <span v-if="errors.postanskiBroj" class="text-danger">{{ errors.postanskiBroj}}</span>
                    </b-form-group>
                </b-col>
                <b-col cols="5">
                    <b-form-group label="Mesto" label-for="city">
                        <b-input id="city" v-model="form.mesto"></b-input>
                        <span v-if="errors.mesto" class="text-danger">{{ errors.mesto}}</span>
                    </b-form-group>
                </b-col>
            </b-row>

            <b-form-group label="Role">
                <b-form-select v-model="form.rola" :options="role"></b-form-select>
            </b-form-group>

            <b-form-group v-if="enablePasswordEntry" label="Lozinka">
            
            </b-form-group>

            <div v-if="form.rola == 'bab78444-87f6-45e9-86fc-fd1b1d5b6530'">
                <b-row>
                    <b-col>
                        <b-form-group :label="_('gui.institutionType')" label-for="institution_type">
                            <b-form-select v-model="form.institutionType" id="institution_type" :options="institutionTypes" @change="filtrirajSkole"></b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <b-form-group :label="_('gui.municipality')" label-for="township">
                            <b-form-select v-model="form.township" id="township" :options="opstine" @change="filtrirajSkole"></b-form-select>
                        </b-form-group>
                    </b-col>
                </b-row>

                <b-form-group :label="_('gui.school')" label-for="school">
                    <b-form-select v-model="form.skola" id="school" :options="schools"></b-form-select>
                    <span v-if="errors.skola" class="text-danger">{{ errors.skola}}</span>
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
                    <b-form-select v-model="form.predmeti" :options="predmeti" multiple :select-size="6"></b-form-select>
                    <span v-if="errors.predmeti" class="text-danger">{{ errors.predmeti}}</span>
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
                <b-form-checkbox v-model="form.klf_korisnik" :value="true" :disabled="!superAdmin">KLF Korisnik</b-form-checkbox>
                <b-form-checkbox v-model="form.pedagoska_sveska" :value="true">Pedagoška sveska</b-form-checkbox>
                <b-form-checkbox v-model="form.testomat" :value="true">Testomat</b-form-checkbox>
                <b-form-checkbox v-model="form.enabled" :value="true">{{ _('gui.enabled') }}</b-form-checkbox>
                <b-form-checkbox v-model="form.updatePassword" :value="true">{{ _('gui.updatePassword') }}</b-form-checkbox>
            </div>
            <div v-else class="d-flex align-items-center justify-content-center flex-column">
                <div class="d-flex flex-row align-items-center m-2">
                    <span v-html="captchaImg"/><b-button variant="success" class="ml-1" @click="getCaptchaImage" title="Osveži"><b-icon-arrow-repeat class="mr-1" /></b-button>
                </div>
                <b-form-input v-model="form.captcha" name="captcha" style="width: 200px; margin: 5px" placeholder="Unesite tekst sa slike" />
                <span v-if="errors.captcha" class="text-danger">{{ errors.captcha }}</span>
            </div>
`
        </b-form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'UserAdminForm',
    props: {
        userId: {typeof: String, default: ''},
        anonimous: {typeof: Boolean, default: false},
        superAdmin: {typeof: Boolean, default: false},
    },
    data() {
        return {
            form: {
                korisnickoIme: '',
                ime: '',
                prezime: '',
                email: '',
                enabled: true,
                updatePassword: true,
                isTeacher: false,
                institutionType: 0,
                township: 0,
                skola: 0,
                predmeti : [],
                professions: [],
                country: '',
                adresa: '',
                postanskiBroj: '',
                telefon1: '',
                mesto: '',
                captcha: '',
                rola: 'a32c8e1b-a442-458c-9889-3567e19797ea',
                testomat: false,
                pedagoska_sveska: false,
                klf_korisnik: false,
                password: null
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
            countries: [],
            opstine: [],
            tipoviKontakata: [],
            predmeti: [],
            captchaImg: null,
            role: [

            ],
            crm: null
        };
    },

    async mounted() {
        this.showSpinner = true;
        this.getCaptchaImage();
        await this.getPredmeti();
        await this.getTipoviKontakata();
        await this.getOpstine();
        await this.filtrirajSkole();
        await this.getRoles();

        console.log("User id je " + this.userId);

        if(this.userId != null && this.userId != '') {
            await this.getData();
            this.form.updatePassword = false;
            await this.getUserGroup();
        } else {
            this.showSpinner = false;
        }
    },

    methods: {
        async getCaptchaImage() {
            await axios.get('/refreshCaptcha')
            .then(response => {
                console.log(response.data);
                this.captchaImg = response.data;
            })
        },
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

        checkCRMUser() {
            return new Promise((resolve, reject) => {
                if(this.form.email == null || this.form.email == undefined || this.form.email == '') {
                    reject({
                        message: 'Polje za email ne može biti prazno!'
                    });
                }

                axios.get('/crm/checkUser/' + this.form.email)
                .then(response => {
                    console.log(response.data);
                    let nastavnik = response.data[0];

                    if(nastavnik == null) {
                        reject({
                            message: "Nema CRM unosa sa tom email adresom!"
                        })
                    } else {
                        console.log(nastavnik);

                        // this.form.email = nastavnik.emailaddress1;
                        this.form.ime = nastavnik.firstname;
                        this.form.prezime = nastavnik.lastname;
                        this.form.postanskiBroj = nastavnik.ext_postanskibroj ?? null;
                        this.form.telefon1 = nastavnik.mobilephone ?? null;
                        this.form.adresa = nastavnik.address1_line1 ?? null;
                        this.form.mesto = nastavnik._ext_grad_value ?? null;
                        this.form.postanskiBroj = nastavnik.ext_postanskibroj ?? null;
                        let korisnik = false;
                        if(nastavnik.ext_Predmetprofila_Nastavnik_Contact.length > 0) {
                            for(var i = 0; i < nastavnik.ext_Predmetprofila_Nastavnik_Contact.length; i++) {
                                let profil = nastavnik.ext_Predmetprofila_Nastavnik_Contact[i];
                                console.log('profil je ');
                                console.log(profil);

                                this.form.skola = profil._ext_skola_value;
                                this.form.predmeti.push(profil._ext_predmet_value);
                                // If any of profiles ext_korisnik value is true, the form 
                                // will have the positive value.
                                if(profil.ext_korisnik == true) {
                                    korisnik = true;
                                }
                                
                            }
                        }

                        this.form.klf_korisnik = korisnik;

                        resolve({
                            data: {
                                message: "Sinhronizacija sa CRM uspešna!"
                            }
                        });
                    }
                    
                });
            });
            
        },

        async getOpstine() {
            axios.get('/crm/opstine')
            .then(response => {
                console.log('Opstine:');
                console.log(response.data);
                this.opstine = [];
                this.opstine.push({
                    value: 0,
                    text: "Izaberite opštinu..."
                });

                for(let property in response.data) {
                    let opstina = response.data[property];
                    this.opstine.push({
                        value: opstina.value,
                        text: opstina.text
                    });
                }
            })
        },

        async getTipoviKontakata() {
            this.institutionTypes = [];
            this.institutionTypes.push({
                value: 0,
                text: 'Izaberite tip skole'
            });

            await axios.get('/crm/tipoviKontakata')
            .then(response => {
                response.data.forEach(element => {
                    this.institutionTypes.push(element);
                });
            })
        },

        async getRoles() {
            this.role = [];
            await axios.get('/getRealmGroups')
            .then(response => {
                console.log(response.data);
                for(let property in response.data) {
                    let group = response.data[property];
                    if(['Administrator', 'Student', 'Teacher'].includes(group.name)) {
                        this.role.push({
                            value: group.id,
                            text: group.name
                        });
                    }
                }
            });
        },

        async filtrirajSkole() {
            let formData = new FormData();
            if(this.form.township != 0 && this.form.institutionType != 0) {
                formData.append('opstina', this.form.township);
                formData.append('tipSkole', this.form.institutionType);
            }

            axios.post('/crm/skole', formData)
            .then(response => {
                this.schools.length = 0;
                for(let property in response.data) {
                    this.schools.push(response.data[property]);
                }
            });

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
        async getPredmeti() {
            await axios.get('/crm/predmeti')
            .then(response => {
                // this.subjects = response.data;
                this.predmeti = response.data;
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
            // await axios.get('/remoteusers/keycloak')
            // .then(response => {
            //     this.accessToken = response.data.access_token;
            // });

            let formData = new FormData();
            formData.append('token', this.accessToken);
            formData.append('userId', this.userId);
            await axios.post('/remoteusers/userData', formData)
            .then(response => {
                let resultObject = response.data;
                console.log(resultObject);
                for(let property in this.form) {
                    if(property == 'requiredActions')
                        continue;
                    if(property == 'skola') {
                        this.filtrirajSkole();
                    }
                    if(property == 'predmeti') {
                        this.form['predmeti'] = resultObject['subjects'];
                    } else {
                        if(resultObject[property] != null && resultObject[property] != undefined) {
                            this.form[property] = resultObject[property];
                        }
                        
                    }

                }

                this.showSpinner = false;

                // if(this.form.institution != null && this.form.institution != undefined) {
                //     this.form.isTeacher = true;
                // }
            });

        },
        async getUserGroup() {
            let token = '';
            await axios.get('/keycloak')
            .then(response => {
                token = response.data.access_token;
            });

            if(token != '' && this.userId != 0) {
                let formData = new FormData();
                formData.append('token', token);
                formData.append('userId', this.userId);
                await axios.post('/user_group', formData)
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
            await axios.get('/keycloak')
            .then(response => {
                this.accessToken = response.data.access_token;
            });

            return new Promise((resolve, reject) => {
                let formData = new FormData();
                formData.append('token', this.accessToken);
                for(let property in this.form) {
                    if(property == 'predmeti') {
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

                formData.append('source', "Klett");

                if(this.userId != null && this.userId != '') {
                    formData.append('userId', this.userId);
                    this.formAction = '/remoteusers/adminupdate';
                } else {
                    this.formAction = '/remoteusers/adminstore';
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
                    this.getCaptchaImage();
                    reject(error);
                });
            });
        },

    },
};
</script>

<style scoped>
label::after {
    content: " *";
    color: red;
}
</style>
