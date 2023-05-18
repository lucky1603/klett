<template>
    <div>
        <b-row>
            <b-col>
                <apexchart type="pie" width="500" :options="pieChart.options" :series="pieChart.series"></apexchart>
            </b-col>
            <b-col>
                <apexchart width="500" type="bar" :options="horizontalChart.options" :series="horizontalChart.series"></apexchart>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <apexchart type="pie" width="500" :options="pieChartLogged.options" :series="pieChartLogged.series"></apexchart>
            </b-col>
            <b-col>
                <apexchart width="500" type="bar" :options="horizontalChartLogged.options" :series="horizontalChartLogged.series"></apexchart>
            </b-col>
        </b-row>

    </div>
</template>

<script>
export default {
    name: 'Dashboard',

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
