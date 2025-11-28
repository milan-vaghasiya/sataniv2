<form>
    <div class="row">
        <div class="col-md-3 form-group">
            <label for="rework_type">Rework</label>
            <select id="rework_type" name="rework_type" class="form-control req">
                <option value="">Select type</option>
                <option value="1">Regular</option>
                <option value="2">Separate</option>
            </select>
            <div class="error rework_type"></div>
        </div>
        <div class="col-md-3 form-group">
            <input type="hidden" name="id">
            <input type="hidden" id="prc_id" name="prc_id" value="<?= (!empty($dataRow->prc_id) ? $dataRow->prc_id : '') ?>">
            <input type="hidden" id="log_id" name="log_id" value="<?= (!empty($dataRow->id) ? $dataRow->id : '') ?>">
            <input type="hidden" id="decision_type" name="decision_type" value="2">
            <label for="qty">Rework Qty</label>
            <input type="text" id="qty" name="qty" class="form-control req numericOnly">
        </div>
        <div class="col-md-3 form-group">
            <label for="rr_reason">Rework Reason</label>
            <select id="rr_reason" name="rr_reason" class="form-control select2 req">
                <option value="">Select Reason</option>
                <?php
                foreach ($reworkComments as $row) :
                    $code = (!empty($row->code)) ? '[' . $row->code . '] - ' : '';
                    echo '<option value="' . $row->id . '" data-code="' . $row->code . '" data-reason="' . $row->remark . '" >' . $code . $row->remark . '</option>';

                endforeach;
                ?>
            </select>
        </div>
        <div class="col-md-3 form-group">
            <label for="rr_type">Rework Type</label>
            <select id="rr_type" name="rr_type" class="form-control req">
                <option value="">Select type</option>
                <option value="Machine">Machine</option>
                <option value="Raw Material">Raw Material</option>
            </select>
            <div class="error rr_type"></div>
        </div>
        <div class="col-md-4 form-group">
            <label for="rr_stage">Rework Stage</label>
            <select id="rr_stage" name="rr_stage" class="form-control select2 req">
                <?php if (empty($dataRow->stage)) { ?> <option value="">Select Stage</option> <?php } else {
                                                                                                echo $dataRow->stage;
                                                                                            } ?>
            </select>
            <div class="error rr_stage"></div>
        </div>
        <div class="col-md-4 form-group">
            <label for="rr_by">Rework By <span class="text-danger">*</span></label>
            <select id="rr_by" name="rr_by" class="form-control select2 req">
                <option value="">Select Rej. From</option>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <label for="rw_process">Rework Process</label>
            <select  id="rw_process" name="rw_process[]" class="form-control select2 req" >
                <?php if (empty($dataRow->stage)) { ?> <option value="">Select Stage</option> <?php } else {
                                                                                                echo $dataRow->stage;
                                                                                            } ?>
            </select>
            <div class="error rw_process"></div>
        </div>
        <div class="col-md-4 form-group rwJob" style="display:none">
            <label for="rw_job_id">Rework Job</label>       
            <select name="rw_job_id" id="rw_job_id" class="form-control select2 req">
                <option value="-1">New</option>
                <?php
                if(!empty($rwJobList)){
                    foreach($rwJobList AS $row){
                        
                        ?><option value="<?=$row->id?>"><?=$row->prc_number?></option><?php
                    }
                }
                ?>
            </select>                                                                    
        </div>
        <div class="col-md-12 form-group">
            <label for="rr_comment">Note</label>
            <textarea id="rr_comment" name="rr_comment" class="form-control" value=""></textarea>
        </div>
    </div>
</form>
<script>
    $(document).on("change", "#rework_type", function() {
        var rework_type = $("#rework_type").val();
        if(rework_type == 1){
            $(".rwJob").hide();
            $("#rw_process").removeAttr("multiple");
            $('#rw_process option').filter('[data-rework_type="2"]').prop('disabled', true).parent().val('');

        }else{
            $(".rwJob").show();
             $("#rw_process").attr("multiple","multiple");
             $('#rw_process option').prop('disabled', false).filter('[data-rework_type="2"], [value=""]').prop('disabled', false).parent().val('');
        }
        $("#rw_process").val("");
        $("#rw_process").select2();
    });
</script>