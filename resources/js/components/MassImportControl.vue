<template>
    <div>
        <b-row>
            <b-col>
                <div class="shadow my-2">
                    <apexchart type="pie" height="380" :options="importChart.options" :series="importChart.series"></apexchart>
                </div>
            </b-col>
            <b-col>
                <div class="w-100 shadow my-2">
                    <p class="text-center bg-dark text-light">PROGRES UVOZA</p>
                    <progress-bar :max="total" :value="imported" class="mx-2"></progress-bar>
                </div>
                <div class="d-flex align-items-center justify-content-center">
                    <label>Import interval:</label>
                    <b-form-select v-model="pace" :options="intervals" class="mx-1"></b-form-select>
                </div>
                <div class="d-flex align-items-center justify-content-center my-2">
                    <b-button variant="primary" size="sm" class="m-1" type="button" @click="startMultipleImport">Pokreni</b-button>
                    <b-button variant="danger" size="sm" class="m-1" type="button" @click="stopMultipleImport">Zustavi</b-button>
                    <b-button variant="success" size="sm" class="m-1" type="button" @click="importOne">Uvezi jednog</b-button>
                </div>
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
                    title: {
                        text: "Odnos izmedju uvezenih i preostalih"
                    },
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
            pace: this.interval
        };
    },

    async mounted() {
        await this.getData();
    },

    methods: {
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
        async importMore() {
            if(this.stop)
                return;
            await this.importOne();
            setTimeout(this.importMore, this.pace);
        },
        startMultipleImport() {
            this.stop = false;
            this.importMore();
        },
        stopMultipleImport() {
            this.stop = true;
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
