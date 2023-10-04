<template>
    <div>
        <b-row>
            <b-col>
                <b-card bg-variant="white" header="STANJE TABELE UVOZA" header-bg-variant="dark" header-text-variant="white" class="mb-2">
                    <div class="my-2">
                        <apexchart type="pie" height="380" :options="importChart.options" :series="importChart.series"></apexchart>
                    </div>
                    <div class="d-flex align-items-center justify-content-center my-2">
                        <b-button variant="primary" size="sm" class="m-1" type="button" @click="reset">
                            <b-spinner small v-if="busy" class="mr-1"></b-spinner>
                            Reset
                        </b-button>
                    </div>
                </b-card>


            </b-col>
            <b-col>
                <!-- <div class="w-100 shadow my-2 py-2">
                    <p class="text-center bg-dark text-light">PROGRES UVOZA</p>
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <label class="mr-1">interval:</label>
                        <b-form-select v-model="pace" :options="intervals" class="mx-1 w-50"></b-form-select>
                    </div>
                    <progress-bar :max="total" :value="imported" class="mx-2"></progress-bar>

                    <div class="d-flex align-items-center justify-content-center my-2">
                        <b-button variant="primary" size="sm" class="m-1" type="button" @click="startMultipleImport">Pokreni</b-button>
                        <b-button variant="danger" size="sm" class="m-1" type="button" @click="stopMultipleImport">Zustavi</b-button>
                        <b-button variant="success" size="sm" class="m-1" type="button" @click="importOne">Uvezi jednog</b-button>
                    </div>
                </div> -->

                <b-card bg-variant="white" header="PROGRES UVOZA" header-bg-variant="dark" header-text-variant="white" class="mb-2">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <label class="mr-1">interval:</label>
                        <b-form-select v-model="pace" :options="intervals" class="mx-1 w-50"></b-form-select>
                    </div>
                    <progress-bar :max="total" :value="imported" class="mx-2"></progress-bar>

                    <div class="d-flex align-items-center justify-content-center my-2">
                        <b-button variant="primary" size="sm" class="m-1" type="button" @click="startMultipleImport">Pokreni</b-button>
                        <b-button variant="danger" size="sm" class="m-1" type="button" @click="stopMultipleImport">Zaustavi</b-button>
                        <b-button variant="success" size="sm" class="m-1" type="button" @click="importOne">Uvezi jednog</b-button>
                    </div>
                </b-card>


                <b-card bg-variant="white" header="PODEŠAVANJE UVOZA" header-bg-variant="dark" header-text-variant="white" class="mt-2">
                    <b-form @submit.prevent="send">
                        <b-form-group label="Izaberi fajl">
                            <b-form-select v-model="selectedFile" :options="files"></b-form-select>
                        </b-form-group>

                        <b-form-checkbox v-model="append" value="true" unchecked-value="false">Dodaj na postojeće</b-form-checkbox>

                        <div class="d-flex align-items-center justify-content-center my-2">
                            <b-button type="submit" variant="primary">
                                <b-spinner small v-if="busyImport" class="mr-1"></b-spinner>
                                Podesi
                            </b-button>
                        </div>

                    </b-form>
                </b-card>


            </b-col>
        </b-row>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'MassImportControl',
    props: {
        interval: { type: Number, default: 2000 }
    },
    data() {
        return {
            importChart: {
                series: [],
                options: {
                    chart: {
                        width: 380,
                        type: "pie"
                    },
                    labels: ["Uvezeni", "Preostali"],

                    colors: ['#546E7A', '#E91E63']

                }
            },

            total: 0,
            imported: 0,
            stop: true,
            intervals: [
                { value:250,  text: "0.25 sec"},
                { value:500,  text: "0.5 sec" },
                { value:1000, text: "1 sec" },
                { value:1500, text: "1.5 sec" },
                { value:2000, text: "2 sec" },
                { value:2500, text: "2.5 sec" },
                { value:3000, text: "3 sec" },

            ],
            pace: this.interval,
            busy: false,
            files: [],
            selectedFile: '',
            append: "true",
            busyImport: false,
            ids: [],
        };
    },

    async mounted() {
        await this.getFiles();
        await this.getData();
    },

    methods: {
        async send() {
            this.busyImport = true;
            let formData = new FormData();
            formData.append('file', this.selectedFile);
            formData.append('append', this.append);
            await axios.post('/userimports/setimport', formData)
            .then(response => {
                console.log(response.data);
                this.getData();
            });
            this.busyImport = false;
        },
        async getIds() {
            await axios.get('/remoteusers/unimporteduserids')
            .then(response => {
                this.ids = response.data;
            })
        },
        async getFiles() {
            await axios.get('/userimports/files')
            .then(response => {
                var files = response.data;

                files.splice(0,1);
                console.log(files);
                for(var i = 0; i < files.length; i++) {
                    this.files.push({
                        value: files[i],
                        text: files[i]
                    });
                }
            });
        },
        async getData() {
            await axios.get('/userimports/counts')
            .then(response => {
                this.total = response.data.total;
                this.imported = response.data.imported;
                this.importChart.series.length = 0;
                this.importChart.series.push(this.imported);
                this.importChart.series.push(this.total - this.imported);
            })
        },
        async importOne() {
            await axios.get('/remoteusers/importFirstUser')
            .then(response => {
                console.log(response.data);
                this.total = response.data.total;
                this.imported = response.data.imported;
                this.importChart.series.length = 0;
                this.importChart.series.push(this.imported);
                this.importChart.series.push(this.total - this.imported);
            });
        },
        async importId(id) {
            await axios.get('/remoteusers/importuserbyid/' + id)
            .then(response => {
                this.total = response.data.total;
                this.imported = response.data.imported;
                this.importChart.series.length = 0;
                this.importChart.series.push(this.imported);
                this.importChart.series.push(this.total - this.imported);
            });
        },
        async importMore() {
            if(this.stop)
                return;
            await this.importOne();
            setTimeout(this.importMore, this.pace);
        },
        async importMoreIds() {
            if(this.stop)
                return;
            await this.importId(this.ids[0]);
            this.ids.shift();
            setTimeout(this.importMoreIds, this.pace);
        },
        async startMultipleImport() {
            this.stop = false;
            // await this.getIds();
            // await this.importMoreIds();
            this.importMore();
        },
        stopMultipleImport() {
            this.stop = true;
            // this.ids = [];
        },
        async reset() {
            this.busy = true;
            await axios.get('/userimports/reset');
            await this.getData();
            this.busy = false;
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
