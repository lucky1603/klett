<template>
    <div>
        <b-form @submit.prevent="onSubmit">
            <b-form-group id="municipalities-group" :label="_('gui.municipality')" label-for="municipality">
                <b-form-select
                    id="municipality"
                    v-model="form.municipality"
                    :options="municipalities"
              ></b-form-select>
              <span v-if="errors.municipality" class="text-danger">{{ errors.municipality}}</span>
            </b-form-group>
            <b-form-group id="institution-types-group" :label="_('gui.institutionType')" label-for="institution-type">
                <b-form-select
                    id="institution-type"
                    v-model="form.institution_type"
                    :options="institutionTypes"
              ></b-form-select>
              <span v-if="errors.institution_type" class="text-danger">{{ errors.institution_type}}</span>
            </b-form-group>

            <b-form-group
                id="name-group"
                :label="_('gui.schoolName')"
                label-for="name"
                :description="_('gui.schoolNameHolder')"
            >
                <b-form-input
                    id="name"
                    v-model="form.name"
                    type="text"
                    placeholder="Unesite ime škole"
                    required></b-form-input>

                <span v-if="errors.name" class="text-danger">{{ errors.name}}</span>
            </b-form-group>
        </b-form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'SchoolForm',
    props: {
        token: { typeof: String, default: ''},
        action: { typeof: String, default: '/schools/create'},
        schoolId: { typeof: Number, default: 0 },
    },
    data() {
        return {
            form: {
                municipality: 0,
                institution_type: 0,
                name: '',
            },
            municipalities: [
                { value: 0, text: "Izaberite"}
            ],
            institutionTypes: [
                { value: 0, text: "Izaberite"}
            ],
            errors: {}
        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
        getData() {


            axios.get('/schools/getMunicipalities')
            .then(response => {
                for(let property in response.data) {
                    var entry = response.data[property];
                    this.municipalities.push({
                        value: entry.id,
                        text: entry.name,
                    });
                }

            });

            axios.get('/schools/getInstitutionTypes')
            .then(response => {
                for(let property in response.data) {
                    var entry = response.data[property];
                    this.institutionTypes.push({
                        value: entry.id,
                        text: entry.name,
                    });
                }
            });

            if(this.schoolId != 0) {
                axios.get('/schools/getSchool/' + this.schoolId)
                .then(response => {
                    this.form.municipality = response.data['municipality_id'];
                    this.form.institution_type = response.data['institution_type_id'];
                    this.form.name = response.data['name'];
                });
            }

        },
        onSubmit() {
            let formData = new FormData();
            formData.append("_token", this.token);
            for(let property in this.form) {
                formData.append(property, this.form[property]);
            }

            if(this.schoolId != 0) {
                formData.append('id', this.schoolId);
            }

            return new Promise((resolve, reject) => {
                axios.post(this.action, formData)
                .then(response => {
                    console.log(response.data);
                    resolve(response);
                })
                .catch(error => {
                    console.log(error.response.data.message);
                    this.errors = {};
                    for(let err in error.response.data.errors) {
                        this.errors[err] = error.response.data.errors[err][0];
                    }
                    reject(error);
                });
            })
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
