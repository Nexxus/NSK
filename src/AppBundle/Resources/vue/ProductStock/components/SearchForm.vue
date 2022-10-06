<template>
    
    <form method="get" class="form-horizontal" @submit.prevent="$parent.loadProducts()">

        <div class="row">
            <div class="col-sm-3">
                <input type="text" id="query" name="query" v-model.trim="search.query" class="focus form-control" placeholder="Zoeken op Id, Sku of (deel van) naam" />
            </div>
            <div class="col-sm-2">
                <select id="availability" name="availability" v-model="search.availability" class="form-control">
                    <option value="">All availability</option>
                    <option value="stock">In stock</option>
                    <option value="hold">On hold</option>
                    <option value="saleable">For sale</option>
                    <option value="sold">Sold</option></select>
            </div>
            <div class="col-sm-2">
                <select id="status" name="status" v-model="search.status" class="form-control">
                    <option value="">All statuses</option>
                    <option v-for="productStatus in productStatuses" :key="productStatus.id" :value="productStatus.id">{{ productStatus.name }}</option>
                </select>
            </div>
            <div class="col-sm-2">
                <select id="producttype" name="producttype" v-model="search.type" class="form-control">
                    <option value="">All types</option>
                    <option v-for="productType in productTypes" :key="productType.id" :value="productType.id">{{ productType.name }}</option>
                </select>
            </div>
            <div class="col-sm-2">
                <select id="location" name="location" v-model="search.location" class="form-control">
                    <option value="">All locations</option>
                    <option v-for="location in locations" :key="location.id" :value="location.id">{{ location.name }}</option>
                </select>
            </div>
            <div class="col-sm-1">
                <button type="submit" id="submit" name="submit" class="btn-default btn">Search</button>
            </div>
        </div>

        <hr />
        
    </form>

</template>

<script>

export default {
    name: 'SearchForm',
    props: ['productStatuses', 'productTypes', 'locations'],
    computed: {
        search() {
            return this.$parent.search
        }
    }
}

</script>