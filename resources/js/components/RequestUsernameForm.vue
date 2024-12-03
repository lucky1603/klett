<template>
  <div class="mainFormBody d-flex align-items-center justify-content-center h-100 w-100 border">
    <div v-if="!sent" class="shadow bg-white p-4">
        <p class="h4 open-sans text-center">Zaboravili ste korisničko ime?</p>
        <hr/>        
        <b-form v-if="sending == 0" @submit.prevent="sendAddress">
            <p class="open-sans" style="font-size: 14px">Pošaljite nam Vašu email adresu i na nju će Vam stići sva korisnička imena sa kojima ste registrovani u našem sistemu.</p>
            <b-form-group>
                <b-form-input id="email" type="email" placeholder="Unesite Vašu email adresu" v-model="form.email" ></b-form-input>
            </b-form-group>            
            <div class="d-flex align-items-center justify-content-center">
                <button type="submit" class="btn btn-primary">Pošalji</button>
            </div>            
        </b-form>
        <div v-if="sending == 1" class="d-flex flex-column align-items-center justify-content-center">
            <p class="open-sans text-center w-100">Sačekajte par sekundi, email se šalje!</p>
            <b-spinner variant="primary" type="grow" style="z-index: 1000"></b-spinner>
        </div>
    </div>        

    <div v-if="sent" class="shadow bg-white p-4 d-flex flex-column align-items-center justify-content-center">
        <p class="h4 open-sans text-center">POTVRDA</p>
        <hr class="my-1"/>
        <p class="open-sans text-center">Poslat vam je email na adresu koju ste naveli.</p>
        <a href="https://klett.rs" class="btn btn-primary">Na glavnu stranu</a>
    </div>
  </div>
</template>

<script>
export default {
    name: 'RequestUsernameForm',
    data() {
        return {
            form: {
                email: ''
            },
            sent: false,
            sending: 0
        }     
    },

    methods: {
        sendAddress() {
            let formData = new FormData();
            formData.append('email', this.form.email);
            this.sending = 1;
            axios.post('/retrieveusernames', formData)
            .then(response => {                
                // alert('Poslat vam je email na adresu koju ste naveli.');
                this.sending = 0;
                this.sent = true;
            })
            
        }
    }
}
</script>

<style lang="scss" scoped>
.mainFormBody {
    background-image: url('/images/Main_Slider_Bckg.jpg') !important;
    background-repeat: repeat;
    height: 100% !important;
    width: 100% !important;

}
.open-sans {    
    font-family: 'Open Sans', sans-serif !important;    
}
</style>