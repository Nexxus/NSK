<template>
    
    <div class="row" style="margin-bottom: 20px; margin-top: 10px">
        <div class="col-sm-1 col-xs-3">
            <button type="button" class="btn btn-default btn-selectall" @click="$parent.selectAll()">Select all</button>
        </div>
        <div class="col-sm-3 col-xs-9">
            <select class="form-control" @change='$parent.showModalBulkEditStatus($event)'>
                <option value="" disabled="disabled" selected="selected">With selected lines...</option>
                <option value="productstatus">Edit status</option>
                <option value="barcodes">Print barcodes</option>
                <option value="pricecards">Print price cards</option>
                <option value="checklists">Print checklists</option>
            </select>
        </div>
        <div class="col-sm-4 col-sm-offset-1 col-xs-12">
            <div v-if="$parent.modalResult=='success'" class="alert alert-success alert-dismissible" role="alert" style="padding-top: 6px; padding-bottom: 6px; margin-bottom: 0; background-color: #99c5ab; border-color: #99c5ab">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                Saved successfully
            </div> 
            <div v-else-if="$parent.modalResult" class="alert alert-warning alert-dismissible" role="alert" style="padding-top: 6px; padding-bottom: 6px; margin-bottom: 0">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                Error: {{ $parent.modalResult }}
            </div>                           
        </div>          
        <div class="col-sm-3 col-xs-12">
            <a href="../purchaseorder/edit" class="btn btn-primary pull-right" role="button">Create new purchase</a>
        </div>        
    </div>
    
</template>

<script>

export default {
    name: 'BulkEditForm'
}

</script>
