<form>
    <div class="col-md-12">
        <div class="row">

            <input type="hidden" name="id" id="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""; ?>" />
            
            <div class="col-md-12 form-group">
                <label for="remark">Type</label>
                <select name="type" id="type" class="form-control select2">
                    <option value="1" <?=(!empty($dataRow->type) && $dataRow->type == 1)?"selected":""?>>Rejection Reason</option>
                    <option value="2" <?=(!empty($dataRow->type) && $dataRow->type == 2)?"selected":""?>>Idle Reason</option>
                    <option value="3" <?=(!empty($dataRow->type) && $dataRow->type == 3)?"selected":""?>>Rework Reason</option>                    
                </select>
            </div>

            <div class="col-md-12 form-group">
                <label for="code"> Code</label>
                <textarea name="code" id="code" class="form-control req" placeholder="Rejection Reason" ><?=(!empty($dataRow->code))?$dataRow->code:"";?></textarea>
                <div class="error code"></div>
            </div>

            <div class="col-md-12 form-group rejParam">
                <label for="param_ids">Rejection Parameter</label>
                <select name="param_ids[]" id="param_ids" class="form-control select2" multiple>
                    <option value="">Select Parameter</option>
                    <?php
                    foreach ($rejParamData as $row) :
                        $selected = (!empty($dataRow->param_ids) && (in_array($row->id,  explode(',', $dataRow->param_ids)))) ? "selected" : "";
                        echo '<option value="' . $row->id . '" ' . $selected . '>' . $row->parameter . '</option>';
                    endforeach;
                    ?>
                </select>
            </div>
           
            <div class="col-md-12 form-group">
                <label for="remark"> Reason</label>
                <textarea name="remark" id="remark" class="form-control req" placeholder="Rejection Reason" ><?=(!empty($dataRow->remark))?$dataRow->remark:"";?></textarea>
                <div class="error remark"></div>
            </div>
           
        </div>
    </div>
</form>
<script>
$(document).ready(function(){
    setTimeout(function(){$("#type").trigger('change')},500);

    $(document).on('change','#type',function(){
		var type = $(this).val();
		if(type == 1){ $('.rejParam').show(); }
		else{ $('.rejParam').hide(); }
	});
});
</script>
