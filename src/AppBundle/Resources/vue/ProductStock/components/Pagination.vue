<template>
    
<div class="navigation" v-if="pageCount>1">
        
    <ul class="pagination">

        <li :class="{ 'disabled': page==1 }">
            <span v-if="page==1">&laquo;&nbsp;Previous</span>
            <a v-else rel="previous" href="#" @click.prevent="$parent.changePage(page-1)">&laquo;&nbsp;Previous</a>
        </li>
    
        <li v-for="p in pageButtonCount" :key="p" :class="{ 'active': page==p }">
            <span v-if="page==p">{{ p }}</span>
            <a v-else href="#" @click.prevent="$parent.changePage(p)">{{ p }}</a>
        </li>
    
        <li class="disabled" v-if="pageCount>7">
            <span>&hellip;</span>
        </li>

        <li :class="{ 'active': page==pageCount }">
            <span v-if="page==pageCount">{{ pageCount }}</span>
            <a v-else href="#" @click.prevent="$parent.changePage(pageCount)"><span>{{ pageCount }}</span></a>
        </li>
    
        <li :class="{ 'disabled': page==pageCount }">
            <span v-if="page==pageCount">Next&nbsp;&raquo;</span>
            <a v-else rel="next" href="#" @click.prevent="$parent.changePage(page+1)">Next&nbsp;&raquo;</a>
        </li>

    </ul>

</div>

</template>

<script>

export default {
    name: 'Pagination',
    props: ['page', 'pageCount'],
    computed: {
        pageButtonCount() {
            return this.pageCount > 7 ? 5 : this.pageCount-1
        }
    }
}

</script>