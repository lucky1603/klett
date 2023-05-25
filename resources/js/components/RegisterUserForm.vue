<template>
    <div>
        <div v-if="!sent" class="d-flex align-items-center justify-content-center flex-column">
            <h4 class="text-center mt-4 w-100 display-4">{{ _('gui.registerUserFormTitle')}}</h4>
            <hr/>
            <div class="d-flex align-items-center container p-4 border-top border-secondary">
                Vaši kontakt podaci koje unesete biće korišćeni kako biste poboljšali
                Vaše iskustvo prilikom pregleda sajta, kako biste mogli samostalno da
                upravljate Vašim nalogom i da mu pristupate, kao i u druge svrhe navedene
                u našoj politika privatnosti. Ukoliko se registrujete kao nastavnik,
                tačnost podataka omogućiće Vam da imate pristup nastavnim materijalima
                i da budete na vreme obavešteni o novim izdanjima i stručnim skupovima.
            </div>
            <user-form style="width: 55%"></user-form>
            <div class="d-flex align-items-center justify-content-center mt-2" >
                <b-button variant="primary" type="button" class="m-2" @click="applicationSent">{{ _('gui.Accept') }}</b-button>
                <b-button type="button" class="m-2">{{ _('gui.Cancel') }}</b-button>
            </div>
        </div>
        <div v-else class="container">
            <h4 class="text-center mt-4 w-100 display-4 text-center">Potvrda</h4>
            <hr/>
            <p class="text-center">
                Vaša prijava za registraciju na našem sajtu je evidentirana. U kratkom
                roku ćete biti obavešteni o statusu vaše prijave na email adresu koju
                ste naveli u formularu za prijavu.
            </p>
            <div class="d-flex align-items-center justify-content-center">
                <a class="btn btn-sm btn-primary" role="button" href="https://klett.rs">Na glavnu stranu</a>
            </div>
        </div>
    </div>

</template>

<script>
export default {
    name: 'RegisterUserForm',

    data() {
        return {
            sent: false,
        };
    },

    mounted() {

    },

    methods: {
        applicationSent() {
            this.sent = true;
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
