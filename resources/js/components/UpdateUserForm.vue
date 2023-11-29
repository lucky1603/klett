<template>
    <div>
        <div v-if="!sent" class="d-flex align-items-center justify-content-center flex-column">
            <h4 class="text-center mt-4 w-100 display-4">{{ _('gui.updateUserFormTitle')}}</h4>
            <hr/>
            <div class="d-flex align-items-center container p-4 border-top border-secondary">
                Vaši kontakt podaci preneseni su iz naše baze. Molimo Vas da ih proverite,
                po potrebi izmenite i dopunite ono što nedostaje.
            </div>
            <div class="container">
                <remote-user-form ref="userForm" :anonimous="true" :update="true" :user-id="userId" style="width: 55%"></remote-user-form>
            </div>

            <div class="d-flex align-items-center justify-content-center mt-2" >
                <b-button variant="primary" type="button" class="m-2" @click="ok"><b-spinner v-if="spinner" small class="mr-2"></b-spinner>{{ _('gui.Send') }}</b-button>
            </div>
        </div>
        <div v-if="sent && success" class="container">
            <h4 class="text-center mt-4 w-100 display-4 text-center">Potvrda</h4>
            <hr/>
            <p class="text-center">
                Uspešno se ažurirali Vaše podatke.
            </p>
            <div class="d-flex align-items-center justify-content-center">
                <a class="btn btn-sm btn-primary" role="button" href="https://klett.rs">Na glavnu stranu</a>
            </div>
        </div>
        <div v-if="sent && !success">
            <h4 class="text-center mt-4 w-100 display-4 text-center">Greška</h4>
            <hr/>
            <p class="text-center">
                Desila se greška prilikom ažuriranja podataka.
            </p>
            <div class="d-flex align-items-center justify-content-center">
                <a class="btn btn-sm btn-primary" role="button" href="https://klett.rs">Na glavnu stranu</a>
            </div>
        </div>
        <b-modal ref="messageDialog" :cancel-disabled="true" title="Greška pri unosu" header-bg-variant="warning" header-text-variant="light">
            <p>{{ message }}</p>
            <template #modal-footer>
                <div class="d-flex align-items-center justify-content-center">
                    <b-button variant="outline-warning" @click="closeMessage">Ok</b-button>
                </div>
            </template>
        </b-modal>
    </div>

</template>

<script>
export default {
    name: 'UpdateUserForm',
    props: {
        userId: { type: String, default: '' },
        superAdmin: { typeof: Boolean, default: false}
    },
    data() {
        return {
            sent: false,
            success: false,
            spinner: false,
            message: "Greška pri unosu podataka.",
        };
    },

    mounted() {
        console.log("Remote user form mounted");
        console.log(this.$refs.userForm);
    },

    methods: {
        showMessage($message) {
            this.message = $message;
            this.$refs.messageDialog.show();
        },
        closeMessage($message) {
            this.$refs.messageDialog.hide();
        },
        ok() {
            this.spinner = true;
            this.$refs.userForm.sendData()
            .then(response => {
                this.spinner = false;
                if(response.data.status == 201 || response.data.status == 204) {
                    this.sent = true;
                    this.success = true;
                } else {
                    // alert(response.data.message);
                    if(response.data.status == 409) {
                        this.showMessage("Već postoji korisnik sa tim korisničkim imenom!");
                    } else {
                        this.showMessage(response.data.message + " status - " + response.data.status);
                    }

                }

            })
            .catch(error => {
                this.spinner = false;
            });

        },
        applicationSent() {
            this.sent = true;
        }
    },
};
</script>

<style lang="scss" scoped>

</style>
