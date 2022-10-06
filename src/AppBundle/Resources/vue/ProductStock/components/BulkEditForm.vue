<template>
    
    <div class="row" style="margin-bottom: 20px; margin-top: 10px">
        <div class="col-sm-1 col-xs-3">
            <button type="button" class="btn btn-default btn-selectall">Select all</button>
        </div>
        <div class="col-sm-3 col-xs-9">
            <select id="index_bulk_edit_form_action" name="index_bulk_edit_form[action]" class="form-control" @change='$parent.showModalBulkEditStatus($event)'>
                <option value="" disabled="disabled" selected="selected">With selected lines...</option>
                <option value="productstatus">Edit status</option>
                <option value="barcodes">Print barcodes</option>
                <option value="pricecards">Print price cards</option>
                <option value="checklists">Print checklists</option>
            </select>
        </div>
        <div class="col-sm-3 col-sm-offset-5 col-xs-12">
            <a href="../purchaseorder/edit" class="btn btn-primary pull-right" role="button">Create new purchase</a>
        </div>        
    </div>
    
</template>

<script>

export default {
    name: 'BulkEditForm',
}

</script>