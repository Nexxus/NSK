<template>
<div>
    
    <SearchForm :productStatuses="productStatuses" :productTypes="productTypes" :locations="locations" />

    <form name="index_bulk_edit_form" method="get" action="bulkedit/0" target="_blank" class="form-horizontal">

        <BulkEditForm />

        <Index :products="products" :sort="sort" :loading="loading" />

    </form>

    <Pagination :page="page" :pageCount="pageCount" />

    <ModalEdit      :productId="product.id" v-if="modal=='Edit'" :productStatuses="productStatuses" :locations="locations" :productTypes="productTypes" :saleable="product.stock.saleable" />
    <ModalSplit     :product="product" v-if="modal=='Split'" :productStatuses="productStatuses" :productTypes="productTypes" />
    <ModalChecklist :product="product" v-if="modal=='Checklist'" />
    <ModalBulkEditStatus :locations="locations" :productStatuses="productStatuses" />

</div>
</template>

<script>

import Index from './components/Index.vue'
import SearchForm from './components/SearchForm.vue'
import BulkEditForm from './components/BulkEditForm.vue'
import Pagination from './components/Pagination.vue'
import ModalSplit from './components/modals/ModalSplit.vue'
import ModalEdit from './components/modals/ModalEdit.vue'
import ModalBulkEditStatus from './components/modals/ModalBulkEditStatus.vue'
import ModalChecklist from './components/modals/ModalChecklist.vue'

export default {
    name: 'App',
    components: { Index, SearchForm, BulkEditForm, Pagination, ModalSplit, ModalEdit, ModalBulkEditStatus, ModalChecklist },
    data() { 
        var dataElement = document.querySelector('div#product-stock');
        var productCount = parseInt(dataElement.dataset.productcount);
        var pageLength = parseInt(dataElement.dataset.pagelength);

        return { 
            productCount,
            pageLength,
            productStatuses: [],
            productTypes: [],
            locations: [],
            page: 1,
            products: [],
            sort: "p_id",
            loading: false,
            product: null,
            modal: null,
            search: {
                query: '',
                availability: '',
                status: '',
                type: '',
                location: '',
            }
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
        this.loadProducts()
    },
    methods: {
        loadProducts() {
            var params = {
                offset: this.offset,
                limit: this.pageLength,
                sort: this.sort,
                query: this.search.query,
                availability: this.search.availability,
                status: this.status,
                type: this.search.type,
                location: this.search.location,
            }
            this.products = []
            this.loading = true
            this.axios.get("../vue/product/index", { params })
                .then(response => {
                    this.products = response.data
                    this.loading = false
              
                    this.$nextTick(function () {
                        this.products.forEach(p => { 
                            if (p.tasks_count) this.loadChecklist(p)
                        })
                    })
                })
        },
        loadMeta() {
            this.axios.get("../vue/product/meta")
                .then(response => {
                    this.productStatuses = response.data.productStatuses
                    this.productTypes = response.data.productTypes
                    this.locations = response.data.locations
                })
        },
        loadChecklist(product) {
            this.axios.get("../vue/product/checklist/"+product.id)
                .then(response => {
                    product.purchase_order_relation = response.data
                    product.services_done = response.data.services_done
                })
        },        
        deleteProduct(id) {
            if (!confirm("Are you sure you want to delete this product from stock?")) return
            this.axios.get("delete/"+id)
                .then(response => {
                    this.products = this.products.filter(p => p.id != id)
                })
        },
        sortPage(s) {
            this.sort = s
            this.loadProducts()
        },
        changePage(p) {
            this.page = p
            this.loadProducts()
        },         
        showModalEdit(product) {
            // param can also be id
            if (typeof product !== 'object')
                product = this.products.find(p => p.id==product)
            
            this.showModal(product, 'Edit')
        },        
        showModalSplit(product) {
            this.showModal(product, 'Split')
        },
        showModalChecklist(product) {
            this.showModal(product, 'Checklist')
        },        
        showModalBulkEditStatus(event) {
            if (event.target.value == 'productstatus') {
                this.showModal(null, 'BulkEditStatus')
            }
            else {
                $('form[name="index_bulk_edit_form"]').submit()
            }
        },
        showModal(product, name) {
            this.product=product
            this.modal=name
            this.$nextTick(function () {
                $('#modal'+name).modal('show')
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


