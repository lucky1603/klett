<template>
    <div>

    </div>
</template>

<script>
export default {
    name: 'ImportUsersPanel',

    data() {
        return {
            items: [],
            fields: [],
        };
    },

    mounted() {

    },

    methods: {
        async getData() {
            axios.get('/userimports/data')
            .then(response => {
                console.log(response.data);
            })
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
