<template>
<div>
    
    <ModalEdit urlPrefix="../../"
        :purchaseOrderId="purchaseOrderId" :salesOrderId="salesOrderId" :productTypeId="productTypeId" 
        :productStatuses="productStatuses" :locations="locations" :productTypes="productTypes" 
        />

</div>
</template>

<script>

import ModalEdit from '../ProductStock/components/modals/ModalEdit.vue'

export default {
    name: 'App',
    components: { ModalEdit },
    data() { 
        var dataElement = document.querySelector('div#product-new');
        var purchaseOrderId = parseInt(dataElement.dataset.purchaseorderid);
        var salesOrderId = parseInt(dataElement.dataset.salesorderid);
        var productTypeId = parseInt(dataElement.dataset.producttypeid);

        return { 
            purchaseOrderId,
            salesOrderId,
            productTypeId,
            productId: null,
            productStatuses: [],
            productTypes: [],
            locations: [],            
        }
    },
    computed: {
        offset() {
            return (this.page-1) * this.pageLength
        },
        pageCount() {
            return Math.ceil(parseFloat(this.productCount) / this.pageLength)
        },                        
    },
    mounted() {
        this.loadMeta()
    
                $('#modalEdit').modal('show')
       
    },
    methods: {
        loadMeta() {
            this.axios.get("../../rest/get/product/meta")
                .then(response => {
                    this.productStatuses = response.data.productStatuses
                    this.productTypes = response.data.productTypes
                    this.locations = response.data.locations
                })
        },
        closeModal() {
            this.product=null
            this.modal=null
            $('.modal').modal('hide')            
        },
        formattedPrice(product, withEuroSign) {
            if (!product || !product.price) return ''
            var price = parseFloat(product.price) / 100
            price = price.toLocaleString('nl', {minimumFractionDigits: 2, maximumFractionDigits: 2})
            return withEuroSign ? '€ '+price : price
        }
    }
}

</script>


