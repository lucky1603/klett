<template>
    <div >
        <div class="d-flex align-items-center justify-content-center flex-column w-100">
            <b-table
                responsive
                small
                striped
                bordered
                hover
                :items="data"
                :fields="fields"
                :current-page="currentPage"
                :per-page="perPage"
                head-variant="dark"
                class="w-100 h-100">
                <template #cell(action)="data">
                    <a :href="'/schools/edit/' + data.item.id" @click.prevent="onEditClicked(data.item.id)"><b-icon icon="pencil-square"></b-icon></a>
                </template>
            </b-table>
            <b-pagination
                v-model="currentPage"
                :total-rows="data.length"
                :per-page="pageSize"
                aria-controls="profileTable"
                align="right"
                ></b-pagination>
            <b-button variant="primary" @click="onNewClicked">Dodaj</b-button>
        </div>

        <b-modal id="createSchoolDialog" ref="createSchoolDialog" header-bg-variant="dark" header-text-variant="light">
            <template #modal-title>{{ dialogTitle }}</template>
            <school-form ref="schoolForm" :school-id="selectedSchoolId" :action="dialogDataAction"></school-form>
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
            currentPage: 1,
            perPage: 15,
            data: [],
            entries: [
                { value: 0, text: "Prvi"},
                { value: 1, text: "Drugi"},
                { value: 2, text: "Treci"},
            ],
            fields: [
                {
                    key: "institution_type",
                    sortable: true,
                    label: "Tip ustanove"
                },
                {
                    key: "municipality",
                    sortable: true,
                    label: "Opština"
                },
                {
                    key: "school",
                    sortable: true,
                    label: "Škola"
                },
                {
                    key: "action",
                    label: ""
                }

            ],
            test: 1,
            dialogTitle: "DODAJ ŠKOLU",
            dialogDataAction: '/schools/create',
            selectedSchoolId: 0,
            hasData: true
        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
        async getData() {
            await axios.get('/schools/getSchools')
            .then(response => {
                console.log(response.data.length);
                this.data = response.data;
                if(this.data.length == 0) {
                    this.hasData = false;
                }
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
        },
        onEditClicked(id) {
            this.selectedSchoolId = id;
            this.dialogDataAction = '/schools/edit';
            this.dialogTitle = "Promeni podatke o školi"
            this.openForm();
        },
        onNewClicked() {
            this.selectedSchoolId = 0;
            this.dialogDataAction = '/schools/create';
            this.dialogTitle = "Dodaj novu školu"
            this.openForm();
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
