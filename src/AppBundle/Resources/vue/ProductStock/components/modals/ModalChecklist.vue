<template>

    <div class="modal fade in" tabindex="-1" role="dialog" id="modalChecklist" data-backdrop="static" data-keyboard="false" style="display: block; padding-right: 17px;">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <form class="form-horizontal" @submit.prevent="submit">

                    <div class="modal-header">
                        <button type="button" class="close" @click="$parent.closeModal('')"><span aria-hidden="true">×</span></button>
                        <h4 class="modal-title">Checklist: {{ product.name }}</h4>
                    </div>

                    <div class="modal-body">

                        <!--
                        {% if success %}
                         script> location.reload(); </script 
                        {% elseif success is same as(false) %}
                        <div class="alert alert-danger" role="alert">The checklist could not be saved due to an unknown error.</div>
                        {% endif %}    
                        -->
                        
                        <ModalChecklistService v-for="service in product.purchase_order_relation.services" :key="service.id" :service="service" />
                                
                        <div class="text-center">
                            <button type="submit" class="btn-success btn-120 btn">Save</button>
                            <a class="btn btn-default" :href="'checklistprint/'+product.purchase_order_relation.id" target="_blank">Print</a>
                            <button type="button" class="btn btn-default" @click="$parent.closeModal('')">Close</button>
                        </div>

                    </div>

                </form>
                
            </div>
        </div>
    </div>

</template>

<script>

import ModalChecklistService from './ModalChecklistService.vue'

export default {
    name: 'ModalChecklist',
    components: { ModalChecklistService },
    props: ['product'],
    methods: {
        submit() {
            this.axios.post("../rest/post/product/checklist", { services: this.product.purchase_order_relation.services, productId: this.product.id })
                .then(_ => { 
                    this.$parent.closeModal('success') 
                    this.$parent.loadChecklist(this.product) 
                })
                .catch(err => this.$parent.closeModal(err.message))
        }
    }    
}

</script>


