<template>
    <div>
        <b-tabs>
            <b-tab title="Statistika">
                <b-row>
                    <b-col>
                        <apexchart type="pie" height="300" :options="pieChart.options" :series="pieChart.series"></apexchart>
                    </b-col>
                    <b-col>
                        <apexchart height="300" type="bar" :options="horizontalChart.options" :series="horizontalChart.series"></apexchart>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col>
                        <apexchart type="pie" height="300" :options="pieChartLogged.options" :series="pieChartLogged.series"></apexchart>
                    </b-col>
                    <b-col>
                        <apexchart height="300" type="bar" :options="horizontalChartLogged.options" :series="horizontalChartLogged.series"></apexchart>
                    </b-col>
                </b-row>
            </b-tab>
            <b-tab v-if="isAdmin == 'true'" title="Uvoz">
                <b-row>
                    <b-col>
                        <mass-import :interval="1000"></mass-import>
                    </b-col>
                </b-row>
            </b-tab>
            <b-tab v-if="isAdmin == 'true'" title="E-Mail">
                <send-mail :table-height="400"></send-mail>
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
export default {
    name: 'Dashboard',
    props: {
        isAdmin: { typeof: String, default: "false" }
    },
    data() {
        return {
            verticalChart: {
                options: {
                    chart: {
                        id: 'vuechart-example'
                    },
                    xaxis: {
                        categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998]
                    }
                },
                series: [{
                    name: 'series-1',
                    data: [30, 40, 45, 50, 49, 60, 70, 91]
                }]
            },
            horizontalChart: {
                series: [{
                    data: [8400,700,2340,1500,4540,15800]
                }],
                options: {
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 4
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: ['Klett.rs', 'E-Knjižara', "Freska", "Novi Logos", "E-Učionica", "E-Uči" ]
                    },
                    title: {
                        text: "Broj korisnika po stranicama",
                        align: 'left',
                        margin: 10,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: "14px",
                            fontWeight: "bold",
                        }
                    }

                }
            },
            horizontalChartLogged: {
                series: [{
                    data: [56,12,23,15,45,205]
                }],
                options: {
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            horizontal: true,
                            borderRadius: 4
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    xaxis: {
                        categories: ['Klett.rs', 'E-Knjižara', "Freska", "Novi Logos", "E-Učionica", "E-Uči" ]
                    },
                    title: {
                        text: "Broj trenutno prijavljenih korisnika po stranicama",
                        align: 'left',
                        margin: 10,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: "14px",
                            fontWeight: "bold",
                        }
                    }

                }
            },
            pieChart: {
                series: [50534, 128336],
                options: {
                    chart: {
                        width: 380,
                        type: "pie"
                    },
                    labels: ["Nastavnici", "Obični korisnici"],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }],
                    title: {
                        text: "Odnos nastavnika i ostalih korisnika",
                        align: 'left',
                        margin: 10,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: "14px",
                            fontWeight: "bold",
                        }
                    },
                    colors: ['#546E7A', '#E91E63']

                }
            },
            pieChartLogged: {
                series: [303000, 10356],
                options: {
                    chart: {
                        width: 380,
                        type: "pie"
                    },
                    labels: ["Svi korisnici", "Prijavljeni korisnici"],
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }],
                    title: {
                        text: "Udeo prijavljenih korisnika",
                        align: 'left',
                        margin: 10,
                        offsetX: 0,
                        offsetY: 0,
                        floating: false,
                        style: {
                            fontSize: "14px",
                            fontWeight: "bold",
                        }
                    },
                    colors: ['#546E7A', '#E91E63']

                }
            },



        };
    },

    mounted() {

    },

    methods: {

    },
};
</script>

<style lang="scss" scoped>

</style>
