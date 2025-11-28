<form>
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" id="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""; ?>" />
           
            <div class="col-md-12 form-group">
                <label for="reject_remark">Reason</label>
                <textarea name="reject_remark" id="reject_remark" class="form-control req" placeholder="Close Reason"><?=(!empty($dataRow->reject_remark))?$dataRow->reject_remark:""?></textarea>
				<div class="error reject_remark"></div>
            </div>
        </div>
    </div>
</form>   
