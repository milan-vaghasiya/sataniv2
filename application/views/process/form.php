<form>
    <div class="col-md-12">
        <div class="row">

            <input type="hidden" name="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""; ?>" />

            <div class="col-md-12 form-group">
                <label for="process_name">Process Name</label>
                <input type="text" name="process_name" class="form-control req" value="<?=(!empty($dataRow->process_name))?$dataRow->process_name:"";?>" />
            </div>
            <div class="col-md-6 form-group">
                <label for="line_inspection">Line Inspection</label>
                <select id="line_inspection" name="line_inspection" class="form-control select2">
                    <option value="0" <?=((!empty($dataRow->line_inspection) && $dataRow->line_inspection == 0)?'selected':'')?>>No</option>
                    <option value="1" <?=((!empty($dataRow->line_inspection) && $dataRow->line_inspection == 1)?'selected':'')?>>Yes</option>
                 </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="process_type">Process Type</label>
                <select id="process_type" name="process_type" class="form-control select2">
                    <option value="1" <?=((!empty($dataRow->process_type) && $dataRow->process_type == 1)?'selected':'')?>>Machining</option>
                    <option value="2" <?=((!empty($dataRow->process_type) && $dataRow->process_type == 2)?'selected':'')?>>Forging</option>
                    <option value="3" <?=((!empty($dataRow->process_type) && $dataRow->process_type == 3)?'selected':'')?>>Other</option>
                 </select>
            </div>
            <div class="col-md-12 form-group">
                <label for="remark">Remark</label>
                <textarea name="remark" id="remark" class="form-control"><?=(!empty($dataRow->remark))?$dataRow->remark:""?></textarea>
            </div>
            
        </div>
    </div>
</form>