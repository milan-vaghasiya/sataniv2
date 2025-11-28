<div class="card">
    <div class="media align-items-center btn-group process-tags">
        <span class="badge bg-light-peach btn flex-fill">Currunt Process : <?=!empty($dataRow->current_process)?$dataRow->current_process:'Initial Stage'?></span>
        <span class="badge bg-light-cream btn flex-fill" id="pending_ch_qty">Pending Qty :  </span>
    </div>                                       
</div>
<form data-res_function="getChallanRequestResponse">
    <div class="row">
        <input type="hidden" name="id" id="id" value="">
        <input type="hidden" name="prc_id" id="prc_id" value="<?=$dataRow->prc_id?>">
        <input type="hidden" name="prc_process_id" id="prc_process_id" value="<?=$dataRow->id?>">
        <input type="hidden" name="process_id" id="process_id" value="<?=$dataRow->current_process_id?>">
         <div class="col-md-6 form_group">
            <label for="trans_type">Type</label>
            <select name="trans_type" id="trans_type" class="form-control">
                <option value="1">Regular</option>
                <option value="2">Rework</option>
            </select>
        </div>
        <div class="col-md-6 form-group" >
            <label for="trans_date">Request Date</label>
            <input type="text" name="trans_date" id="trans_date" class="form-control" value="<?=date("Y-m-d")?>">
        </div>
        <div class="col-md-6 form-group" >
            <label for="qty">Request Qty</label>
            <input type="text" name="qty" id="qty" class="form-control numericOnly">
        </div>
        <div class="col-md-6 form-group float-end">
            <?php $param = "{'formId':'addChallanRequest','fnsave':'saveChallanRequest','res_function':'getChallanRequestResponse'}";  ?>
            <button type="button" class="btn waves-effect waves-light btn-success btn-save save-form float-right mt-20" onclick="storeSop(<?=$param?>)" style="height:35px"><i class="fa fa-check"></i> Save</button>
        </div>
    </div>
</form>
<div class="col-md-12">
    <div class="row">
        <h5 style="width:100%;margin:0 auto;vertical-align:middle;border-top:1px solid #ccc;padding:5px 0px;">Process Transaction :</h5>
        <div class="table-responsive  mb-3">
            <table id='requestTable' class="table table-bordered jpExcelTable mb-5">
                <thead class="text-center">
                    <tr>
                        <th style="min-width:20px">#</th>
                        <th style="min-width:20px">Type</th>
                        <th style="min-width:100px">Date</th>
                        <th>Request Qty</th>
                        <th style="width:50px;">Action</th>
                    </tr>
                </thead>
                <tbody id="requestTabodyData">
                   
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
var tbodyData = false;
$(document).ready(function(){
    if(!tbodyData){
        var postData = {'postData':{'prc_process_id':$("#prc_process_id").val(),'prc_id':$("#prc_id").val(),'trans_type':$("#trans_type").val()},'table_id':"requestTable",'tbody_id':'requestTabodyData','tfoot_id':'','fnget':'getChallanRequestHtml'};
        getPRCAcceptHtml(postData);
        tbodyData = true;
    }

    $(document).on('change','#trans_type',function(e){
        e.stopImmediatePropagation();e.preventDefault();
		var postData = {'postData':{'prc_process_id':$("#prc_process_id").val(),'prc_id':$("#prc_id").val(),'trans_type':$("#trans_type").val()},'table_id':"requestTable",'tbody_id':'requestTabodyData','tfoot_id':'','fnget':'getChallanRequestHtml'};
        getPRCAcceptHtml(postData);
    });
});
function getChallanRequestResponse(data,formId="addChallanRequest"){ 
    if(data.status==1){
        $('#'+formId)[0].reset();
        var postData = {'postData':{'prc_process_id':$("#prc_process_id").val(),'prc_id':$("#prc_id").val(),'trans_type':$("#trans_type").val()},'table_id':"requestTable",'tbody_id':'requestTabodyData','tfoot_id':'','fnget':'getChallanRequestHtml'};
        getPRCAcceptHtml(postData);
    }else{
        if(typeof data.message === "object"){
            $(".error").html("");
            $.each( data.message, function( key, value ) {$("."+key).html(value);});
        }else{
            Swal.fire({ icon: 'error', title: data.message });
        }			
    }
}
</script>