<template>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalBulkEditStatus" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <button type="button" class="close" @click="$parent.closeModal()"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Bulk edit</h4>
                </div>

                <div class="modal-body">

                    <p style="margin-bottom: 30px">Change status and/or location for all selected products</p>

                    <!--
                    {% if success %}
                    <div class="alert alert-success" role="alert">The products are saved successfully.</div>
                    {% elseif success is same as(false) %}
                    <div class="alert alert-danger" role="alert">The products could not be saved. Please check details below.</div>
                    {% endif %} -->

                    <form class="form-horizontal" @submit.prevent="submit">

                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="status">Status</label>
                            <div class="col-sm-9">
                                <select id="status" name="status" v-model="status" class="form-control">
                                    <option value=""></option>
                                    <option v-for="productStatus in productStatuses" :key="productStatus.id" :value="productStatus.id">{{ productStatus.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="location">Location</label>
                            <div class="col-sm-9">
                                <select id="location" name="location" v-model="location" class="form-control">
                                    <option value=""></option>
                                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <button type="submit" class="btn-success btn-120 btn">Save</button>
                            </div>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>

</template>

<script>

export default {
    name: 'ModalBulkEditStatus',
    data() { return {
        status: null,
        location: null
    }},
    props: ['productStatuses', 'locations', 'productIds'],
    methods: {
        submit() {
            this.axios.post("../rest/post/product/bulkedit", { ...this.$data, productIds: this.productIds })
                .then(_ => { 
                    this.$parent.closeModal() 
                    this.productIds.forEach(id => {
                        var product = this.$parent.products.find(p => p.id == id)
                        product.location.name = this.locations.find(l => l.id == this.location).name
                    })
                })
        }
    }    
}

</script>


