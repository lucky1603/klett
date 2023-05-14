<template>
    <div>
        <b-form size="sm">
            <b-row>
                <b-col>
                    <b-form-group label="Ime" label-for="ime" description="Unesite ime">
                        <b-input id="ime" type="text" v-model="form.ime"></b-input>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Prezime" label-for="prezime" description="Unesite prezime">
                        <b-input id="prezime" type="text" v-model="form.prezime"></b-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-form-group label="E-Mail" label-for="email" description="Unesite email">
                        <b-input id="email" type="text" v-model="form.email"></b-input>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Država" label-for="country" description="Izaberite jednu državu">
                        <b-form-select id="country" v-model="form.country" :options="countries"></b-form-select>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-form-group label="Poštanski broj" label-for="pb" description="Unesite poštanski broj">
                        <b-input id="pb" type="text" v-model="form.pb"></b-input>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Mesto" label-for="mesto" description="Unesite mesto boravka">
                        <b-input id="mesto" type="text" v-model="form.mesto"></b-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-form-group label="Broj telefona 1" label-for="tel1" description="Unesite glavni broj telefona">
                        <b-input id="tel1" type="text" v-model="form.tel1"></b-input>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Broj telefona 2" label-for="tel2" description="Unesite drugi broj telefona (opciono)">
                        <b-input id="tel2" type="text" v-model="form.tel2"></b-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-form-checkbox v-model="form.isTeacher" :value="true">Da li je nastavnik?</b-form-checkbox>
            <div v-if="form.isTeacher">
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
                </b-form-group>

            </div>
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
                id: 0,
                ime: '',
                prezime: '',
                email: '',
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
            professionalStatuses: [],
            countries: [],
            institutionType: 0,
            municipality: 0,
            institutionTypes: [],
            municipalities: [],
            schools: []
        };
    },

    async mounted() {
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
        async getData() {
            await this.getCountries();
            await this.getSubjects();
            await this.getProfessionalStatuses();
            await this.getInstitutionTypes();
            await this.getMunicipalities();
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
        }

    },
};
</script>

<style lang="scss" scoped>

</style>
