<template>

</template>

<script>

export default {
    name: 'App',
    data() { 
        var dataElement = document.querySelector('div#product-stock');
        var products = dataElement.dataset.products;
        return { 
            products
        }
    }
}
</script>


