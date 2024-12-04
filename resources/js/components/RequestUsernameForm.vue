<template>
  <div class="mainFormBody d-flex align-items-center justify-content-center h-100 w-100 border">
    <div v-if="!sent" class="shadow bg-white p-4">
        <p class="h4 open-sans text-center">Заборавили сте корисничко име?</p>
        <hr/>        
        <b-form v-if="sending == 0" @submit.prevent="sendAddress">
            <p class="open-sans" style="font-size: 14px">Молимо Вас унесите имејл адресу за коју желите да Вам пошаљемо корисничка имена:</p>
            <b-form-group>
                <b-form-input id="email" type="email" placeholder="Унесите Вашу имејл адресу овде ..." v-model="form.email" ></b-form-input>
            </b-form-group>            
            <div class="d-flex align-items-center justify-content-center">
                <button type="submit" class="btn btn-primary">Пошаљи</button>
            </div>            
        </b-form>
        <div v-if="sending == 1" class="d-flex flex-column align-items-center justify-content-center">
            <p class="open-sans text-center w-100">Сачекајте пар секунди, имејл се шаље!</p>
            <b-spinner variant="primary" type="grow" style="z-index: 1000"></b-spinner>
        </div>
    </div>        

    <div v-if="sent" class="shadow bg-white p-4 d-flex flex-column align-items-center justify-content-center">
        <p class="h4 open-sans text-center">ПОТВРДА</p>
        <hr class="my-1"/>
        <p class="open-sans text-center">Послат Вам је имејл на адресу - {{ form.email }}.</p>
        <a href="https://klett.rs" class="btn btn-primary">На главну страну</a>
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