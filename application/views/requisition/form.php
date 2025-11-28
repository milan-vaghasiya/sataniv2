<form>
    <div class="col-md-12">
        <div class="row">

            <input type="hidden" name="id" value="<?=(isset($dataRow->id) && !empty($dataRow->id) ? $dataRow->id : "") ?>">

            <div class="col-md-6 form-group">
                <label for="challan_no">Req No.</label>
                <div class="input-group">
                    <input type="text" name="log_number" class="form-control" value="<?=(isset($dataRow->log_number) && !empty($dataRow->log_number) ? $dataRow->log_number : $log_prefix) ?>" readOnly />
                    <input type="hidden" name="log_no" value="<?=$log_no?>" readOnly />
                </div>
            </div>

            <div class="col-md-6 form-group">
                <label for="item_id">Items</label>
                <select name="item_id" id="item_id" class="form-control select2 req">
                    <option value="">Select Item</option>
                    <?php
                        if(isset($itemData) && !empty(isset($itemData))){
                            foreach ($itemData as $value) {
                                $selected = "";
                                if(isset($dataRow->item_id) && !empty($dataRow->item_id))
                                    if($dataRow->item_id == $value->id)
                                        $selected = "selected";
                                echo "<option value='".$value->id."' ".$selected.">".$value->item_name."</option>";
                            }
                        }
                    ?>
                </select>
                <div class="error item_err"></div>
            </div>

            <div class="col-md-6 form-group">
                <label for="mc_id">Machine</label>
                <select name="mc_id" id="mc_id" class="form-control select2">
                    <option value="">Select Machine</option>
                    <?php
                        if(isset($mcData) && !empty(isset($mcData))){
                            foreach ($mcData as $value) {
                                $selected = "";
                                if(isset($dataRow->mc_id) && !empty($dataRow->mc_id))
                                    if($dataRow->mc_id == $value->id)
                                        $selected = "selected";
                                echo "<option value='".$value->id."' ".$selected.">".$value->item_name."</option>";
                            }
                        }
                    ?>
                </select>
            </div>

            <div class="col-md-6 form-group">
                <label for="fg_id">Finish Good</label>
                <select name="fg_id" id="fg_id" class="form-control select2">
                    <option value="">Select Finish Good</option>
                    <?php
                        if(isset($fgData) && !empty(isset($fgData))){
                            foreach ($fgData as $value) {
                                $selected = "";
                                if(isset($dataRow->fg_id) && !empty($dataRow->fg_id))
                                    if($dataRow->fg_id == $value->id)
                                        $selected = "selected";
                                echo "<option value='".$value->id."' ".$selected.">".$value->item_name."</option>";
                            }
                        }
                    ?>
                </select>
            </div>

            <div class="col-md-6 form-group">
                <label for="req_qty">Req. Qty</label>
                <input type="text" name="req_qty" class="form-control req" value="<?=(isset($dataRow->req_qty) && !empty($dataRow->req_qty) ? $dataRow->req_qty : "") ?>" />
                <div class="error qty_err"></div>
            </div>

            <div class="col-md-6 form-group">
                <label for="urgency">Urgency</label>
                <select name="urgency" id="urgency" class="form-control single-select select2">
                    <option value="">Select Urgency</option>
                    <option value="0" <?=(isset($dataRow->urgency) && !empty($dataRow->urgency) ? (($dataRow->urgency == 0) ? "selected" : "") : "") ?>>Low</option>
                    <option value="1" <?=(isset($dataRow->urgency) && !empty($dataRow->urgency) ? (($dataRow->urgency == 1) ? "selected" : "") : "") ?>>Medium</option>
                    <option value="2" <?=(isset($dataRow->urgency) && !empty($dataRow->urgency) ? (($dataRow->urgency == 2) ? "selected" : "") : "") ?>>High</option>
                </select>
            </div>

            <div class="col-md-12 form-group">
                <label for="remark">Remark</label>
                <input type="text" name="remark" class="form-control" value="<?=(isset($dataRow->remark) && !empty($dataRow->remark) ? $dataRow->remark : "") ?>" />
            </div>

        </div>
    </div>
</form>