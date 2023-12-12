<template>
    <div id="mainFormBody">
        <div v-if="!sent" class="d-flex align-items-center justify-content-center flex-column">
            <h4 class="text-center w-100 display-4">{{ _('gui.registerUserFormTitle')}}</h4>
            <hr class="my-0 bg-danger"/>
            <div class="d-flex flex-column align-items-center container p-4 border-top border-secondary">
                <p class="formEntryText">
                    {{ _('gui.registerIntro1') }}
                        <a href="https://klett.rs" target="_blank">klett.rs</a>,
                        <a href="https://freska.rs" target="_blank">freska.rs</a>,
                        <a href="https://euci.rs" target="_blank">euci.rs</a>,
                        <a href="https://testomat.rs" target="_blank">testomat.rs</a>,
                        <a href="https://eknjizara.rs" target="_blank">eknjizara.rs</a>).
                </p>
                <p class="formEntryText">
                    {{ _('gui.registerIntro2') }}
                </p>
            </div>
            <div class="container">
                <remote-user-form ref="userForm" :anonimous="true" style="width: 55%"></remote-user-form>
                <hr class="bg-secondary mb-2"/>
                <div class="d-flex align-items-center justify-content-end w-100" >
                    <b-button variant="primary" type="button" class="m-2" @click="ok" style="width: 150px"><b-spinner v-if="spinner" small class="mr-2"></b-spinner>{{ _('gui.Send') }}</b-button>
                </div>
            </div>

        </div>
        <div v-if="sent && success" class="container">
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
        <div v-if="sent && !success">
            <h4 class="text-center mt-4 w-100 display-4 text-center">Greška</h4>
            <hr/>
            <p class="text-center">
                Desila se greška prilikom slanja prijave.
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
    name: 'RegisterUserForm',
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
                if(response.data.status == 201) {
                    this.sent = true;
                    this.success = true;
                } else {
                    // alert(response.data.message);
                    if(response.data.status == 409) {
                        this.showMessage("Već postoji korisnik sa tim korisničkim imenom!");
                    } else {
                        this.showMessage(response.data.message);
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
.mainFormBody {
    background-image: url('/images/Main_Slider_Bckg.jpg') !important;
    background-repeat: repeat;
}
</style>
