<template>
    <div class="container-fluid">
        <b-card bg-variant="white" header="Pošalji E-Mail" header-bg-variant="dark" header-text-variant="white">
            <div id="searchFormPanel">
                <b-form inline>
                    <b-form-input v-model="searchForm.username" placeholder="Korisničko ime ..." class="ml-1"/>
                    <b-form-input v-model="searchForm.email" placeholder="Email ..." class="ml-1"/>
                    <b-form-select v-model="searchForm.type" class="ml-1"></b-form-select>
                    <b-button variant="primary" class="ml-2">Kreiraj tabelu</b-button>
                </b-form>
            </div>
            <div id="tablePanel">
                <b-table striped hover :items="items" style="height: 500px" class="shadow mt-2"></b-table>
            </div>
            <div id="sendMailPanel" class="d-flex align-items-center justify-content-center p-2">
                <b-button variant="primary">Pošalji E-Mail</b-button>
            </div>
        </b-card>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'SendMail',

    data() {
        return {
            searchForm : {
                username: '',
                email: '',
                type: 0
            },
            items: []
        };
    },

    mounted() {

    },

    methods: {
        async setTable() {

        },
        async getData() {

        }
    },
};
</script>

<style lang="scss" scoped>

</style>
