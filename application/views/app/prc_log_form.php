<form data-res_function="prcResponse">
    <div class="card">
        <div class="media align-items-center btn-group process-tags">
            <span class="badge bg-light-peach btn flex-fill" style="padding:5px">CP : <?=!empty($dataRow->current_process)?$dataRow->current_process:'Initial Stage'?></span>
            <span class="badge bg-light-teal btn flex-fill" style="padding:5px">NP : <?=$dataRow->next_process?></span>
            <span class="badge bg-light-cream btn flex-fill" style="padding:5px" id="pending_log_qty">PQ : <?=(!empty($pending_log)?$pending_log:0)?></span>
        </div>                                       
    </div>
    <input type="hidden" name="prc_id" id="prc_id" value="<?=$dataRow->prc_id?>">
    <input type="hidden" name="prc_process_id" id="prc_process_id" value="<?=$dataRow->id?>">
    <input type="hidden" name="process_id" id="process_id" value="<?=$dataRow->current_process_id?>">
    <input type="hidden" name="ref_id" id="ref_id" value="<?=!empty($challan_id)?$challan_id:0?>">
    <input type="hidden" name="ref_trans_id" id="ref_trans_id" value="<?=!empty($ref_trans_id)?$ref_trans_id:0?>">
    <input type="hidden"  id="inputWt" value="<?=!empty($inputDiv)?$inputDiv:0?>">
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label for="trans_date">Date</label>
                <input type="date" name="trans_date" id="trans_date" class="form-control" value="<?=date("Y-m-d")?>" max="<?=date("Y-m-d")?>">
            </div> 
        </div>
        <div class="col">
            <div class="mb-3">
                <label for="production_qty">Production Qty</label>
                <input type="text" id="production_qty" class="form-control numericOnly req qtyCal" value="">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label for="ok_qty">Ok Qty</label>
                <input type="text" name="ok_qty" id="ok_qty" class="form-control numericOnly req " value="" readonly>
                <div class="error batch_stock_error"></div>
            </div>
        </div>
        <div class="col">
            <div class="mb-3">
                <label for="rej_found">Rejection Qty</label>
                <input type="text" name="rej_found" id="rej_found" class="form-control numericOnly qtyCal">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label for="rej_reason">Rejection Reason</label>
                <select id="rej_reason" name="rej_reason" class="form-control select2 req">
                    <option value="">Select Reason</option>
                    <?php
                    if(!empty($rejectionComments)){
                        foreach ($rejectionComments as $row) :
                            $code = (!empty($row->code)) ? '[' . $row->code . '] - ' : '';
                            echo '<option value="' . $row->id . '" data-code="' . $row->code . '" data-reason="' . $row->remark . '" data-param_ids="'.$row->param_ids.'">' . $code . $row->remark . '</option>';
                        endforeach;
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label for="rej_param">Rejection Parameter</label>
                <select id="rej_param" name="rej_param" class="form-control select2">
                    <option value="">Select Parameter</option>
                    <?php
                    if(!empty($rejParam)):
                        foreach ($rejParam as $row) :
                            echo '<option value="' . $row->id . '"  '.((!empty($dataRow->rej_param) && $dataRow->rej_param == $row->id)?'selected':'').'>'  . $row->parameter . '</option>';
                        endforeach;
                    endif;
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label for="production_time">Production Time</label>
                <input type="text" name="production_time" id="production_time" class="form-control">
            </div>
        </div>
        <div class="col">
            <div class="mb-3">
                <label for="process_by">Process By</label>
                <select name="process_by" id="process_by" class="form-control select2">
                    <option value="1">Inhouse Machining</option>
                    <option value="2">Department Process</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label for="processor_id">Machine/Dept.</label>
                <select name="processor_id" id="processor_id" class="form-control select2">
                    <option value="0">Select</option>
                </select>
            </div>
        </div>
        <div class="col">
            <div class="mb-3">
                <label for="shift_id">Shift</label>
                <select name="shift_id" id="shift_id" class="form-control select2">
                    <option value="">Select Shift</option>
                    <?php
                    if(!empty($shiftData)){
                        foreach ($shiftData as $row) :
                            echo '<option value="' . $row->id . '" >' . $row->shift_name . '</option>';
                        endforeach;
                    }
                    ?>
                </select>
                <div class="error shift_id"></div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label for="operator_id">Operator</label>
                <select name="operator_id" id="operator_id" class="form-control select2">
                    <option value="0">Select</option>
                    <?php
                    if(!empty($operatorList)){
                        foreach($operatorList as $row){
                            ?><option value="<?=$row->id?>"><?=$row->emp_name?></option><?php
                        }
                    }
                    ?>
                </select>
            </div>
        </div>
        <?php
        if(!empty($inputDiv)){ ?>
        <div class="col">
            <div class="mb-3">
                <label for="wt_nos">Input Weight</label>
                <input type="text" class="form-control floatOnly" name="wt_nos" id="wt_nos" value="<?=!empty($wt_nos)?$wt_nos:''?>">
            </div>
        </div><?php
        }
        ?>
    </div>
    <div class="row">
        <div class="mb-3">
            <label for="remark">Remark</label>
            <input type="text" name="remark" id="remark" class="form-control" value="">
        </div>
    </div>
    
</form>

<script>
var tbodyData = false;
$(document).ready(function(){
    setTimeout(function(){ $('#process_by').trigger('change'); }, 50);

    $(document).on("change keyup",".qtyCal", function(){
        var rej_qty = ($("#rej_found").val() !='')?$("#rej_found").val():0;
        
		var okQty=parseFloat($("#production_qty").val())-parseFloat(rej_qty);
      
		$("#ok_qty").val(okQty);
    });

    $(document).on('change','#process_by',function(e){ 
        e.stopImmediatePropagation();e.preventDefault();
		var process_by = $(this).val();
        if(process_by)
        {		
            $.ajax({
                url:base_url  + "sopDesk/getProcessorList",
                type:'post',
                data:{process_by:process_by}, 
                dataType:'json',
                success:function(data){
                    $("#processor_id").html("");
                    $("#processor_id").html(data.options);
                }
            });
        }
    });

    $(document).on("change", "#rej_reason", function(e) {
        e.stopImmediatePropagation();e.preventDefault();
        var param_ids = $("#rej_reason :selected").data('param_ids');
        $("#rej_param").html("");
        $.ajax({
            url: base_url  + 'rejectionReview/getRejParams',
            type: 'post',
            data: {  param_ids: param_ids },
            dataType: 'json',
            success: function(data) {
                $("#rej_param").html(data.options);
            }
        });
        // $("#rej_param").select2();
    });
});
</script>