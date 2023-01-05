<template>

    <div>

        <div class="form-group">
            <div class="col-xs-3" style="padding-top: 8px">
                {{ service.task.name }}
            </div>
            <div class="col-xs-2">
                <select :id="'service_status_'+service.id" :name="'service_status_'+service.id" class="service-status form-control" v-model="service.status">
                    <option value="0" selected="selected">Todo</option>
                    <option value="1">Hold</option>
                    <option value="2">Busy</option>
                    <option value="4">Cancel</option>
                    <option value="3">Done</option>
                </select> 
            </div>
            <div class="col-xs-1">
                <div class="checkbox">
                    <label :for="'service_done_'+service.id">
                        <input type="checkbox" :id="'service_done_'+service.id" :name="'service_done_'+service.id" class="service-done" value="1" v-model="done"> Done
                    </label>
                </div> 
            </div>
            <div class="col-xs-6">
                <textarea :id="'service_description_'+service.id" :name="'service_description_'+service.id" class="form-control" v-model="service.description"></textarea> 
            </div>
        </div>
        <hr style="margin: 4px 0px">

    </div>

</template>

<script>

export default {
    name: 'ModalChecklistService',
    props: ['service'],
    data() { return {
        done: this.service.status == 3
    }},
    watch: {
        'service.status': function (newVal){
            this.done = newVal == 3;
        },
        'done': function(newVal){
            this.service.status = newVal == true ? 3 : 0;
        }
    }    
}

</script>


