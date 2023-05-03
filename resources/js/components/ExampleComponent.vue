<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">
                        I'm an example component.
                    </div>
                </div>
            </div>
        </div>

        <b-button v-b-modal.modal-1>Pokazi modal</b-button>
        <b-modal id="modal-1" title="BootstrapVue">
            <p class="my-4">Hello from Modal!!!</p>
        </b-modal>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        }
    }
</script>
