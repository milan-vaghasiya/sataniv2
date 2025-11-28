<form>
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" id="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""; ?>" />
            <div class="col-md-12 form-group">
                <label for="joining_date">Joining Date</label>
                <input type="date" name="joining_date" id="joining_date" class="form-control req" value="<?=(!empty($dataRow->joining_date))?$dataRow->joining_date:date("Y-m-d");?>" />
            </div>
            <div class="col-md-12 form-group">
                <label for="remark">Reason</label>
                <textarea name="remark" id="remark" class="form-control req" placeholder="Close Reason"><?=(!empty($dataRow->remark))?$dataRow->remark:""?></textarea>
				<div class="error remark"></div>
            </div>
        </div>
    </div>
</form>   
