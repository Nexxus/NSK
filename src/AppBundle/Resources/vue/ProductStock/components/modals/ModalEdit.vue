<template>

    <div class="modal fade" tabindex="-1" role="dialog" id="modalEdit" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" v-if="!product">
                <div class="modal-header">
                    <button type="button" class="close close-modal" @click="$parent.closeModal('')"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Loading product...</h4>
                </div>            
            </div>            
            <div class="modal-content" v-else>

                <form @submit.prevent="submit">

                <div class="modal-header">
                    <button type="button" class="close close-modal" @click="$parent.closeModal('')"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Edit product: {{ product ? product.name : "[new]" }}</h4>
                </div>

                <div class="modal-body">

                    <!--
                    {% if success %}
                    <div class="alert alert-success" role="alert">The product is saved successfully.</div>
                    {% elseif success is same as(false) %}
                    <div class="alert alert-danger" role="alert">The product could not be saved. Please check details below.</div>
                    {% endif %}
                    -->

                    <div class="row">
                        <div class="col-md-6">
                        
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="edit_sku">Sku</label> 
                                <div class="col-sm-9">
                                    <div v-if="product.sku" class="input-group">
                                        <input type="text" id="edit_sku" name="edit[sku]" placeholder="Keep empty for autogeneration" class="focus form-control" v-model="product.sku"> 
                                        <span class="input-group-btn">
                                            <a class="btn btn-default" :href="urlPrefix+'barcode/single/'+product.sku" target="_blank"><span class="glyphicon glyphicon-barcode" aria-label="Barcode"></span></a>
                                        </span>
                                    </div>
                                    <input v-else type="text" id="edit_sku" name="edit[sku]" placeholder="Keep empty for autogeneration" class="focus form-control" v-model="product.sku"> 
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label required" for="edit_name">Name</label>
                                <div class="col-sm-9">
                                    <input type="text" id="edit_name" name="edit[name]" required="required" class="form-control" v-model="product.name">
                                </div>
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="edit_description">Description</label>
                                <div class="col-sm-9">
                                    <input type="text" id="edit_description" name="edit[description]" class="form-control" v-model="product.description">
                                </div>
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="edit_price">Retail price</label>
                                <div class="col-sm-9">                
                                    <div class="input-group">
                                        <span class="input-group-addon">€ </span>
                                        <input type="text" id="edit_price" name="edit[price]" class="form-control" v-model="formattedPrice">
                                    </div>
                                </div>
                            </div>            
                            <div class="row form-display">
                                <div class="col-sm-9 col-md-offset-3"><b>List price: </b>&euro; {{ product.total_standard_price_of_attributes.toLocaleString('nl', {minimumFractionDigits: 2, maximumFractionDigits: 2})  }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="col-sm-3 control-label required" for="edit_type">Type</label>
                                <div class="col-sm-9">
                                    <select id="edit_type" name="edit[type]" class="form-control" v-model="typeId">
                                        <option v-for="productType in productTypes" :key="productType.id" :value="productType.id">{{ productType.name }}</option>
                                    </select>
                                </div>
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-3 control-label required" for="edit_location">Location</label>
                                <div class="col-sm-9">
                                    <select id="edit_location" name="edit[location]" required="required" class="form-control" v-model="locationId">
                                        <option value=""></option>
                                        <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                                    </select>
                                </div>
                            </div> 
                            <div class="form-group">
                                <label class="col-sm-3 control-label" for="edit_status">Status</label>
                                <div class="col-sm-9">
                                    <select id="edit_status" name="edit[status]" class="form-control" v-model="statusId">
                                        <option value=""></option>
                                        <option v-for="productStatus in productStatuses" :key="productStatus.id" :value="productStatus.id">{{ productStatus.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span class="glyphicon glyphicon-play" aria-hidden="true"></span> Attributes
                        </div>
                        <div class="panel-body">

                            <div v-if="product.attribute_relations.length>0">
                                <div class="row" style="margin-bottom: 2px" v-for="(attribute_relation, idx) in sortedAttributeRelations" :key="attribute_relation.attribute.id">
                                    <div class="col-md-3 col-md-offset-1">
                                        {{ attribute_relation.attribute.name }}
                                    </div>
                                    <div class="col-md-5" v-if="attribute_relation.attribute.type == 2"><!-- Files -->
                                            <div class="row" v-for="file in attribute_relation.files" :key="file.id">
                                                <div class="col-md-4 col-filecontainer">
                                                    <div class="panel panel-default panel-filecontainer">
                                                        <div class="panel-body panel-body-small">
                                                            <div class="row">
                                                                <div class="col-xs-10" style="overflow:hidden; text-overflow:ellipsis;">
                                                                    <a :href="urlPrefix+'download/'+file.id" :title="file.original_client_filename" target="_blank">{{ file.original_client_filename }}</a>
                                                                </div>
                                                                <div class="col-xs-2" style="margin-top: -4px">
                                                                    <button type="button" class="close delete-file" aria-label="Delete" @click="deleteFile(attribute_relation.attribute.id, file.id)"><span aria-hidden="true">&times;</span></button>
                                                                </div>
                                                            </div>
                                                            
                                                            <img :src="urlPrefix+'download/'+file.id" :title="file.original_client_filename" v-if="isImage(file.original_client_filename)" />

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <input type="file" class="file-input" :data-attribute-relation-idx="idx">
                
                                        </div>
                                        <div class="col-md-5" v-else-if="attribute_relation.attribute.type == 3"><!-- Product -->                          
                                            <div class="form-group">
                                                <div class="col-sm-3" v-if="attribute_relation.attribute.has_quantity">
                                                    <input type="number" required="required" class="form-control" v-model="attribute_relation.quantity">
                                                </div>
                                                <div class="col-sm-1" v-if="attribute_relation.attribute.has_quantity" style="padding: 8px 0 0 0">X</div>
                                                <div :class="attribute_relation.attribute.has_quantity ? 'col-sm-8' : 'col-sm-12'">
                                                    <div class="input-group">
                                                        <select class="form-control" v-model="attribute_relation.value">
                                                            <option value=""></option>
                                                            <option v-for="p in loadAttributableProducts(attribute_relation.attribute.id)" :key="p.id" :value="p.id">{{ p.name }}</option>
                                                        </select>
                                                        <span class="input-group-btn">
                                                            <a href="#" @click.prevent="openSub(attribute_relation.value)" class="btn btn-success"><span class="glyphicon glyphicon-pencil" aria-label="Edit"></span></a>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-5" v-else-if="attribute_relation.attribute.type == 1"><!-- Options --> 
                                            <select class="form-control" v-model="attribute_relation.value">
                                                <option value=""></option>
                                                <option v-for="option in attribute_relation.attribute.options" :key="option.id" :value="option.id">{{ option.name }}</option>
                                            </select>
                                        </div> 
                                        
                                        <div class="col-md-5" v-else><!-- Open text --> 
                                            <input type="text" class="form-control" v-model="attribute_relation.value" />
                                        </div>                          
                        
                                        <div class="col-md-1">
                                            &euro; {{ attribute_relation.total_standard_price.toLocaleString('nl', {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                                        </div>
                                </div>
                        
                            </div>
                        
                            <div v-else>
                                No attributes available for products of this type. Please ask your manager to relate attributes to product types.
                            </div>

                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <span class="glyphicon glyphicon-play" aria-hidden="true"></span> Orders
                        </div>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th width="1%"></th>
                                    <th width="10%">Order nr</th>
                                    <th width="10%">Order date</th>
                                    <th width="20%">Supplier/Customer</th>
                                    <th width="10%">Status</th>
                                    <th width="3%">Quantity</th>
                                    <th width="1%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="product.purchase_order_relation">
                                    <td><span class="glyphicon glyphicon glyphicon-log-in" aria-label="Purchase"></span></td>
                                    <td>{{ product.purchase_order_relation.order.order_nr }}</td>
                                    <td>{{ product.purchase_order_relation.order.order_date.toLocaleString('nl') }}</td>
                                    <td>From: {{ product.purchase_order_relation.order.supplier ? product.purchase_order_relation.order.supplier.name : "" }}</td>
                                    <td>{{ product.purchase_order_relation.order.status ? product.purchase_order_relation.order.status.name : "" }}</td>
                                    <td>{{ product.purchase_order_relation.quantity }}</td>
                                    <td><a class="btn btn-success" :href="urlPrefix+'purchaseorder/edit/'+product.purchase_order_relation.order.id"><span class="glyphicon glyphicon-chevron-right" aria-label="Edit"></span></a></td>
                                </tr>
                            
                                <tr v-for="sales_order_relation in product.sales_order_relations" :key="sales_order_relation.id">
                                    <td><span class="glyphicon glyphicon glyphicon-log-out" aria-label="Sales"></span></td>
                                    <td>{{ sales_order_relation.order.order_nr }}</td>
                                    <td>{{ sales_order_relation.order.order_date.toLocaleString('nl') }}</td>
                                    <td>To: {{ sales_order_relation.order.customer ? sales_order_relation.order.customer.name : "" }}</td>
                                    <td>{{ sales_order_relation.order.status ? sales_order_relation.order.status.name : "" }}</td>
                                    <td>{{ sales_order_relation.quantity }}</td>
                                    <td><a class="btn btn-success" :href="urlPrefix+'salesorder/edit/'+sales_order_relation.order.id"><span class="glyphicon glyphicon-chevron-right" aria-label="Edit"></span></a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center">
                        <button type="submit" id="edit_save" name="edit[save]" class="btn-success btn-120 btn">Save</button>
                        <a v-if="product" class="btn btn-default" role="button" :href="'print/'+product.id" target="_blank">Print</a>
                        <a v-if="saleable" class="btn btn-default" role="button" :href="urlPrefix+'salesorder/new/'+productId">Sell</a>
                        <button type="button" class="btn btn-default close-modal" @click="$parent.closeModal('')">Close</button>
                        <a v-if="refId" class="btn btn-default" role="button" href="#" @click.prevent="openRef()">Back</a>
                    </div>

                </div>

                </form>

            </div>
        </div>
    </div>

</template>

<script>

export default {
    name: 'ModalEdit',
    data() { return {
        product: null,
        refId: null     
    }},
    props: {
        productId: {
            type: Number,
            default: 0 // new
        },        
        productStatuses: {
            type: Array,
            required: true
        }, 
        locations: {
            type: Array,
            required: true
        }, 
        productTypes: {
            type: Array,
            required: true
        }, 
        saleable: {
            type: Number,
            default: 0
        }, 
        urlPrefix: {
            type: String,
            default: '../' // existing
        },
        purchaseOrderId: {
            type: Number,
            default: null // needed when new product
        },
        salesOrderId: {
            type: Number,
            default: null // needed when new product
        },
        productTypeId: {
            type: Number,
            default: null // needed when new product
        },
    },  
    mounted() {
        this.loadProduct()
        this.loadUploadifive()
    },
    computed: {
        sortedAttributeRelations() {
            if (!this.product || !this.product.attribute_relations) 
                return [];
            return this.product.attribute_relations.sort(
                (ar1, ar2) => 
                (ar1.attribute.id > ar2.attribute.id) ? 1 : (ar1.attribute.id < ar2.attribute.id) ? -1 : 0);
        },
        formattedPrice: {
            get: function () {
                return this.$parent.formattedPrice(this.product, false)
            },
            set: function (newValue) {
                this.product.price = parseInt(parseFloat(newValue.replace(",", ".")) * 100);
            }           
        },
        statusId: {
            get: function () {
                return this.product && this.product.status ? this.product.status.id : null
            },
            set: function (newValue) {
                if (this.product && this.product.status) this.product.status.id = newValue
            }           
        },
        typeId: {
            get: function () {
                return this.product && this.product.type ? this.product.type.id : this.productTypeId
            },
            set: function (newValue) {
                if (this.product && this.product.type) this.product.type.id = newValue
            }           
        },
        locationId: {
            get: function () {
                return this.product && this.product.location ? this.product.location.id : null
            },
            set: function (newValue) {
                if (this.product && this.product.location) this.product.location.id = newValue
            }           
        }
    },
    methods: {
        loadProduct() {
            if (this.productId > 0)
                this.axios.get(this.urlPrefix+"rest/get/product/edit/"+this.productId)
                    .then(response => {
                        this.product = response.data
                    })
            else // new product
                this.axios.get(this.urlPrefix+"rest/get/product/new/"+this.purchaseOrderId+"/"+this.salesOrderId+"/"+this.productTypeId)
                    .then(response => {
                        this.product = response.data
                    })

        },
        loadUploadifive() {
            var that = this
            $.getScript("/js/jquery.uploadifive.min.js", function() {
                $('input.file-input').uploadifive({
                    'checkScript': that.urlPrefix+'uploadexists',
                    'formData': {},
                    'uploadScript': that.urlPrefix+'upload',
                    'multi': true,
                    'onUploadComplete': function (file, data) {
                        if (data.substring(0, 5) == 'Error') {
                            alert(data)
                        }
                        else {
                            var idx = parseInt($(this).data('attribute-relation-idx'));
                            if (that.product.attribute_relations[idx].value)
                                that.product.attribute_relations[idx].value += ',' + data
                            else
                                that.product.attribute_relations[idx].value = data
                        }
                    }
                });
            });
        },
        async loadAttributableProducts(attributeId) {
            let response = await this.axios.get(this.urlPrefix+"rest/get/product/attributable/"+this.product.id+"/"+attributeId)
            return response.data        
        },
        submit() {
            this.axios.post("../rest/post/product/edit", { ...this.product, purchaseOrderId: this.purchaseOrderId, salesOrderId: this.salesOrderId })
                .then(response => { 
                    const i = this.$parent.products.findIndex(p => p.id==this.productId)
                    var old = this.$parent.products[i]
                    var nww = response.data
                    var two = {...old, ...nww}
                    this.$parent.products[i] = two
                    this.$parent.closeModal('success')
                })
                .catch(err => this.$parent.closeModal(err.message))
        },  
        isImage(originalClientFilename) {
            if (!originalClientFilename) return false
            const ext = originalClientFilename.slice(-3).toLowerCase()
            return ext == "jpg" || ext == "png" || ext == "gif"
        },      
        deleteFile(attributeId, fileId) {
            var that = this
            this.axios.post(this.urlPrefix+"rest/post/product/deletefile", { attributeId, fileId })
                .then(_ => {
                    var relation = that.product.attribute_relations.find(ar => ar.attribute.id==attributeId)
                    relation.files = relation.files.filter(f => f.id != fileId)
                })
                .catch(err => this.$parent.closeModal(err.message))
        },
        openSub(productId) {
            this.refId = this.productId
            this.product = null
            this.$parent.showModalEdit(productId)
            this.loadProduct()
        },
        openRef() {
            this.product = null
            this.$parent.showModalEdit(this.refId)
            this.refId = null
            this.loadProduct()
        }
    }
}

</script>


