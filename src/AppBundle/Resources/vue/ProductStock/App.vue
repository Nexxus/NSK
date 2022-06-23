<template>

    <table class="table table-striped">
        <thead>
            <tr>
                <th></th>
                <th>SKU</th>
                <th>Name</th>
                <th>Type</th>
                <th>Location</th>
                <th width="10%">Price</th>
                <th width="5%">Purch</th>
                <th width="5%">Stock</th>
                <th width="5%">Hold</th>
                <th width="5%">Sale</th>
                <th width="5%">Sold</th>
                <th width="1%"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="product in products" :key="product.id">
                <td><input type="checkbox" :id="'index_bulk_edit_form_index_'+product.id" name="index_bulk_edit_form[index][]" :value="product.id"></td>
                <td><a href="#" data-target="#modalEditor" class="btn-modal" data-toggle="tooltip" title="product.getAttributesList()">{{ product.sku }}</a></td>
                <td><a href="#" data-target="#modalEditor" class="btn-modal" data-toggle="tooltip" title="product.getAttributesList()">{{ product.name }}</a></td>
                <td>{{ product.type.name }}</td>
                <td>{{ product.location.name }}</td>
                <td>&euro; {{ product.price|number_format(2, ',', '.') }}</td>
                <td>{{ product.stock.purchased }}</td>
                <td>{{ product.stock.stock }}</td>
                <td>{{ product.stock.hold }}</td>
                <td>{{ product.stock.saleable }}</td>
                <td>{{ product.stock.sold }}</td>
                <td nowrap align="right">
                    <a v-if="product.purchaseOrderRelation && product.type && product.type.tasks.length > 0"
                        class="btn btn-default btn-modal" 
                        href="#" 
                        data-target="#modalEditor" 
                        title="Checklist of tasks">
                        <span class="glyphicon glyphicon-check" aria-label="Checklist" style="margin-right: 3px"></span>
                        {{ product.purchaseOrderRelation.servicesDone }} / {{ product.type.tasks.length }}
                    </a>
                    <a v-if="product.stock.purchased > 1"
                        class="btn btn-default btn-modal" 
                        href="#" 
                        data-target="#modalSplitter" 
                        title="Split bundle">
                        <span class="glyphicon glyphicon-flash" aria-label="Split"></span>
                    </a>
                    <a class="btn btn-success btn-modal" href="#" data-target="#modalEditor" title="Edit"><span class="glyphicon glyphicon-pencil" aria-label="Edit"></span></a>
                    <a class="btn btn-danger btn-delete btn-modal" href="#" title="Delete" data-class="product" :data-name="product.name"><span class="glyphicon glyphicon-remove" aria-label="Delete"></span></a>
                </td>
            </tr>        
        </tbody>
    </table>

</template>

<script>

export default {
    name: 'App',
    data() { 
        var dataElement = document.querySelector('div#product-stock');
        var productCount = parseInt(dataElement.dataset.productcount);
        var pageLength = parseInt(dataElement.dataset.pagelength);
        var pageCount = parseInt(dataElement.dataset.pagecount);

        return { 
            productCount,
            pageLength,
            pageCount,
            page: 1,
            products: []
        }
    },
    computed: {
        offset() {
            return (this.page-1) * this.pageLength
        }
    },
    mounted() {
      this.axios.get("../vue/product/" + this.offset + "/" + this.pageLength)
        .then(response => {
          this.products = response.data
        })
        .catch(err => { throw err })         
    }
}

</script>


