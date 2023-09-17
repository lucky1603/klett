<template>
    <div class="d-flex align-items-center justify-content-center flex-column">
        <div class="w-100 m-1 d-flex aligh-items-center justify-content-between" style="font-size: 12px">
            <span ><strong>{{ min }}</strong></span>
            <span >{{ layoutValue }} </span>
            <span ><strong>{{ max }}</strong></span>
        </div>
        <div class="progress-bar w-100 mx-4 my-1">
            <div class="progress-bar-bar" :style="barStyle"></div>
        </div>
    </div>

</template>

<script>
export default {
    name: 'ProgressBar',
    props: {
        min: { type: Number, default: 0 },
        max: { type: Number, default: 1 },
        value: { typeof: Number, default: 0 },
    },
    computed: {
        barStyle() {
            let frac = Math.min(1, Math.max(0, (this.value - this.min) / (this.max - this.min)) || 0);
            return {
                width: frac * 100 + "%"
            };
        },
        layoutValue() {
            return this.value + " od " + this.max;
        }
    },
    data() {
        return {

        };
    },

    mounted() {

    },

    methods: {

    },
};
</script>

<style lang="scss" scoped>
    .progress-bar {
        height: 5px;
        background-color: #eee;
    }

    .progress-bar-bar {
        height: 100%;
        background-color: #007fff;
        transition: width 0.2s ease-in-out;
    }
</style>
