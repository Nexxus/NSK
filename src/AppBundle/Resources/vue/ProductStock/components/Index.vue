<template>

    <table class="table table-striped">
        <thead>
            <tr>
                <th></th>
                <th><a href="#" @click.prevent="$parent.sortPage('p_sku')">SKU</a></th>
                <th><a href="#" @click.prevent="$parent.sortPage('p_name')">Name</a></th>
                <th><a href="#" @click.prevent="$parent.sortPage('t_name')">Type</a></th>
                <th><a href="#" @click.prevent="$parent.sortPage('l_name')">Location</a></th>
                <th width="10%">Price</th>
                <th width="5%">Purch</th>
                <th width="5%">Stock</th>
                <th width="5%">Hold</th>
                <th width="5%">Sale</th>
                <th width="5%">Sold</th>
                <th width="1%"></th>
            </tr>
        </thead>
        <tbody v-if="loading">
        <tr>
            <td style="height: 200px; text-align: center; padding-top: 80px" colspan="99">
                Loading...
            </td>
        </tr>
        </tbody>
        <tbody v-else-if="products.length==0">
        <tr>
            <td style="height: 200px; text-align: center; padding-top: 80px" colspan="99">
                No records found
            </td>
        </tr>
        </tbody>        
        <tbody v-else>
            <tr v-for="product in products" :key="product.id">
                <td><input type="checkbox" :id="'index_bulk_edit_form_index_'+product.id" name="index_bulk_edit_form_index[]" :value="product.id" v-model="checkedProducts"></td>
                <td><a href="#" data-target="#modalEditor" class="btn-modal" data-toggle="tooltip" :title="product.attributesList">{{ product.sku }}</a></td>
                <td><a href="#" data-target="#modalEditor" class="btn-modal" data-toggle="tooltip" :title="product.attributesList">{{ product.name }}</a></td>
                <td>{{ product.type && product.type.name }}</td>
                <td>{{ product.location && product.location.name }}</td>
                <td>{{ $parent.formattedPrice(product, true) }}</td>
                <td>{{ product.stock.purchased }}</td>
                <td>{{ product.stock.stock }}</td>
                <td>{{ product.stock.hold }}</td>
                <td>{{ product.stock.saleable }}</td>
                <td>{{ product.stock.sold }}</td>
                <td nowrap align="right">
                    <a v-if="product.tasks_count > 0"
                        class="btn btn-default btn-modal" 
                        href="#" 
                        @click.prevent="$parent.showModalChecklist(product)"
                        title="Checklist of tasks">
                        <span class="glyphicon glyphicon-check" aria-label="Checklist" style="margin-right: 3px"></span>
                        {{ product.services_done }} / {{ product.tasks_count }}
                    </a>
                    <a v-if="product.stock.purchased > 1"
                        class="btn btn-default btn-modal" 
                        href="#" 
                        @click.prevent="$parent.showModalSplit(product)"
                        title="Split bundle">
                        <span class="glyphicon glyphicon-flash" aria-label="Split"></span>
                    </a>
                    <a class="btn btn-success btn-modal" 
                        href="#" 
                        @click.prevent="$parent.showModalEdit(product)"
                        title="Edit">
                        <span class="glyphicon glyphicon-pencil" aria-label="Edit"></span>
                    </a>
                    <a class="btn btn-danger btn-delete btn-modal" href="#" title="Delete" @click.prevent="$parent.deleteProduct(product.id)"><span class="glyphicon glyphicon-remove" aria-label="Delete"></span></a>
                </td>
            </tr>        
        </tbody>
    </table>

</template>

<script>

export default {
    name: 'Index',
    data() { return { 
        checkedProducts: []
    }},
    props: ['products', "sort", "loading"]
}

</script>


