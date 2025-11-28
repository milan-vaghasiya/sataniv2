<form>
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" id="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""; ?>" />
            
            <div class="col-md-12 form-group">
                <label for="store_name">Store Name</label>
                <input type="text" name="store_name" class="form-control req" value="<?=(!empty($dataRow->store_name))?$dataRow->store_name:""; ?>" />
            </div>

            <div class="col-md-12 form-group">
                <label for="location">Location</label>
                <input type="text" name="location" class="form-control req" value="<?=(!empty($dataRow->location))?$dataRow->location:""; ?>" />
            </div>
			
            <div class="col-md-12 form-group">
                <label for="remark">Remark</label>
                <textarea name="remark" id="remark" rows="2" class="form-control"></textarea>
            </div>

        </div>
    </div>
</form>