<form>
    <div class="col-md-12">
        <div class="row">

            <div class="error general_error"></div>

            <input type="hidden" name="id" id="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""; ?>" />
                
            <div class="col-md-8 form-group">
                <label for="shift_name">Shift Name</label>
                <input type="text" name="shift_name" class="form-control req" value="<?=(!empty($dataRow->shift_name))?$dataRow->shift_name:""; ?>" />
            </div>
            <div class="col-md-4 form-group">
                <label for="shift_type">Day/Night?</label>
                <select name="shift_type" id="shift_type" class="form-control">
                    <option value="1" <?=(!empty($dataRow) && $dataRow->shift_type == 1) ? "selected" : "";?>>Day</option>
                    <option value="2" <?=(!empty($dataRow) && $dataRow->shift_type == 2) ? "selected" : "";?>>Night</option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="shift_start">Shift Start Time</label>
                <input type="time" name="shift_start" class="form-control req" value="<?=(!empty($dataRow->shift_start))?$dataRow->shift_start:""; ?>" />
            </div>
            <div class="col-md-6 form-group">
                <label for="shift_end">Shift End Time</label>
                <input type="time" name="shift_end" class="form-control req" value="<?=(!empty($dataRow->shift_end))?$dataRow->shift_end:""; ?>" />
            </div>
            <div class="col-md-6 form-group">
                <label for="lunch_start">Lunch Start Time</label>
                <input type="time" name="lunch_start" class="form-control req" value="<?=(!empty($dataRow->lunch_start))?$dataRow->lunch_start:""; ?>" />
            </div>
            <div class="col-md-6 form-group">
                <label for="lunch_end">Lunch End Time</label>
                <input type="time" name="lunch_end" class="form-control req" value="<?=(!empty($dataRow->lunch_end))?$dataRow->lunch_end:""; ?>" />
            </div>
            
            <div class="col-md-12 form-group req sizeDiv" style="">
                <div class="input-group">
                    <label for="ru_in_time" style="width:50%">Round Up <small>(Early In Minutes)</small></label>
                    <label for="rd_in_time" style="width:50%">Round Down <small>(Late In Minutes)</small></label>
                </div>
                <div class="input-group">
                    <input type="text" name="ru_in_time" class="form-control numericOnly req" value="<?=(!empty($dataRow->ru_in_time))?$dataRow->ru_in_time:"0"; ?>" maxlength="3" />
                    <input type="text" name="rd_in_time" class="form-control numericOnly req" value="<?=(!empty($dataRow->rd_in_time))?$dataRow->rd_in_time:"0"; ?>" maxlength="3" />
                </div>            
            </div>
            <div class="col-md-12 form-group req sizeDiv" style="">
                <div class="input-group">
                    <label for="ru_out_time" style="width:50%">Round Up <small>(Early Out Minutes)</small></label>
                    <label for="rd_out_time" style="width:50%">Round Down <small>(Late Out Minutes)</small></label>
                </div>
                <div class="input-group">
                    <input type="text" name="ru_out_time" class="form-control numericOnly req" value="<?=(!empty($dataRow->ru_out_time))?$dataRow->ru_out_time:"0"; ?>" maxlength="3" />
                    <input type="text" name="rd_out_time" class="form-control numericOnly req" value="<?=(!empty($dataRow->rd_out_time))?$dataRow->rd_out_time:"0"; ?>" maxlength="3" />
                </div>            
            </div>
        </div>
    </div>
</form>