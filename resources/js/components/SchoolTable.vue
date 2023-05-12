<template>
    <div >
        <div v-if="data.length > 0"  class="d-flex align-items-center justify-content-center flex-column w-100">
            <b-table striped hover :items="data"></b-table>
            <b-button variant="success" @click="openForm">Dodaj</b-button>
        </div>

        <div v-else class="d-flex align-items-center flex-column w-100">
            <img src="/images/noschool.jpg" class="m-4" alt="No schools in the list" title="No schools in the list"/>
            <b-button variant="success" @click="openForm">Dodaj</b-button>
        </div>

        <b-modal id="createSchoolDialog" ref="createSchoolDialog" header-bg-variant="dark" header-text-variant="light">
            <template #modal-title>{{ dialogTitle }}</template>
            <school-form ref="schoolForm"></school-form>
            <template #modal-footer>
                <b-button type="button" variant="primary" @click.prevent="onOk" >Prihvati</b-button>
                <b-button type="button" variant="light" @click="onCancel" >Zatvori</b-button>
            </template>
        </b-modal>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'SchoolTable',

    data() {
        return {
            data: [],
            entries: [
                { value: 0, text: "Prvi"},
                { value: 1, text: "Drugi"},
                { value: 2, text: "Treci"},
            ],
            test: 1,
            dialogTitle: "DODAJ ŠKOLU"
        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
        async getData() {
            await axios.get('/schools/getSchools')
            .then(response => {
                console.log(response.data);
                this.data = response.data;
            });
        },
        openForm() {
            this.$refs['createSchoolDialog'].show()
        },
        onOk() {
            this.$refs['schoolForm'].onSubmit()
            .then(response => {
                this.getData();
                this.$refs['createSchoolDialog'].hide();
            })
            .catch(error => {
                console.log(error);
            });
        },
        onCancel() {
            this.$refs['createSchoolDialog'].hide();
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
