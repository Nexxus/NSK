<template>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalSplit" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <form class="form-horizontal" @submit.prevent="submit">

                    <div class="modal-header">
                        <button type="button" class="close" @click="$parent.closeModal('')"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Split: {{ product.name }}</h4>
                    </div>

                    <div class="modal-body">

                        <!-- {% if success %}
                         script> location.reload(); </script
                        {% elseif success is same as(false) %}
                        <div class="alert alert-danger" role="alert">The product could not be split. Please check details below.</div>
                        {% endif %} -->

                        <p>
                        Enter the desired values for the separated and new product bundle (or set of individual products).
                        </p>

                        <div id="product_split_form">
                            <div class="form-group">
                                <label class="col-sm-3 control-label required" for="product_split_form_how">How</label>
                                <div class="col-sm-9">
                                    <select id="product_split_form_how" name="product_split_form_how" class="form-control" v-model="how">
                                        <option v-for="(val, option) in howOptions" :key="val" :value="val">{{ option }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" v-show="product.stock.stock > 2 && (how == 'split_stockpart' || how == 'individualize_stockpart')">
                                <label class="col-sm-3 control-label required" for="product_split_form_quantity">Quantity</label>
                                <div class="col-sm-9">
                                    <input type="number" id="product_split_form_quantity" name="product_split_form_quantity" required="required" min="1" :max="product.stock.stock-1" class="form-control" v-model="quantity">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label required" for="product_split_form_status">Status</label>
                                <div class="col-sm-9">
                                    <select id="status" name="status" v-model="status" class="form-control">
                                        <option v-for="productStatus in productStatuses" :key="productStatus.id" :value="productStatus.id">{{ productStatus.name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <div class="checkbox">
                                        <label for="product_split_form_newSku">
                                            <input type="checkbox" id="product_split_form_newSku" name="product_split_form_newSku" v-model="newSku"> Create new SKU(s)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <button type="submit" id="product_split_form_split" name="product_split_form_split" class="btn-success btn-120 btn">Split</button>
                                </div>
                            </div>

                        </div>
                    </div>

                </form>

            </div>
        </div>
    </div>

</template>

<script>

export default {
    name: 'ModalSplit',
    data() { return {
        how: null,
        quantity: 1,
        status: null,
        newSku: true
    }},
    props: ['product', 'productStatuses'],
    computed: {
        howOptions() {
            var choices = []
            if (this.product.stock.stock > 2) {
                choices = {
                    'Split part of stock to new bundle': 'split_stockpart',
                    'Individualize part of stock': 'individualize_stockpart',
                    'Individualize whole stock': 'individualize_stock',
                    'Individualize whole bundle': 'individualize_bundle'
                }
            }
            else if (this.product.stock.stock == 2) {
                choices = {
                    'Individualize stock': 'individualize_stock',
                    'Individualize whole bundle': 'individualize_bundle'
                }
            }
            else {
                choices = {'Individualize whole bundle': 'individualize_bundle'};           
            }
            return choices
        }
    },
    methods: {
        submit() {
            this.axios.post("../rest/post/product/split", { ...this.$data, id: this.product.id })
                .then(_ => { 
                    this.$parent.closeModal('success') 
                    this.$parent.loadProducts() 
                })
                .catch(err => this.$parent.closeModal(err.message))
        }
    }
}

</script>


