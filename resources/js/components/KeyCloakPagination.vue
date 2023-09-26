<template>
    <div class="d-flex align-items-center justify-content-center w-100 my-2">
        <b-button variant="dark" class="mx-1" size="sm" @click="start"><b-icon icon="skip-start-fill"></b-icon></b-button>
        <b-button variant="outline-dark" class="mx-1" size="sm" @click="prev"><b-icon icon="skip-backward-fill"></b-icon></b-button>
        <label style="width: 50px" class="text-center">{{ page }} / {{ pages }}</label>
        <b-button variant="outline-dark" class="mx-1" size="sm" @click="next"><b-icon icon="skip-forward-fill"></b-icon></b-button>
        <b-button variant="dark" class="mx-1" size="sm" @click="end"><b-icon icon="skip-end-fill"></b-icon></b-button>
    </div>
</template>

<script>
export default {
    name: 'KeyCloakPagination',
    props: {
        range: {typeof: Number, default: 15},
        value: {typeof: Number, default: 1},
        count: { typeof: Number, default: 0 },
    },
    computed:  {
        page() {
            return Math.ceil(this.position / this.range) + 1;
        },
        pages() {
            return Math.floor(this.count / this.range) + 1;
        },

    },
    data() {
        return {
            position: this.value,
        };
    },

    async mounted() {

    },

    methods: {
        next() {
            if(this.position + this.range <= this.count) {
                this.position += this.range;
                this.notifyUpdatePosition();
            }
        },
        prev() {
            if(this.position - this.range > 0) {
                this.position -= this.range;
            } else {
                this.position = 0;
            }
            this.notifyUpdatePosition();
        },
        start() {
            this.position = 0;
            this.notifyUpdatePosition();
        },
        end() {
            this.position = (this.pages - 1) * this.range;
            this.notifyUpdatePosition();
        },
        notifyUpdatePosition() {
            this.$emit('input', this.position);
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
