<form>
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" value="<?=(!empty($dataRow->id) ? $dataRow->id : "") ?>">
            <input type="hidden" name="req_type" value="<?=(!empty($dataRow->req_type) ? $dataRow->req_type : "2")?>">
            <div class="col-md-3 form-group">
                <label for="challan_no">Req No.</label>
                <div class="input-group">
                    <input type="text" name="trans_number" class="form-control" value="<?=(!empty($dataRow->trans_number)) ? $dataRow->trans_number : (!empty($trans_number) ? $trans_number : '') ?>" readOnly />
                    <input type="hidden" name="trans_no" value="<?=(!empty($dataRow->trans_no)) ? $dataRow->trans_no : (!empty($trans_no) ? $trans_no : '') ?>" readOnly />
                    <input type="hidden" name="trans_prefix" value="<?=(!empty($dataRow->trans_prefix)) ? $dataRow->trans_prefix : (!empty($trans_prefix) ? $trans_prefix : '') ?>" readOnly />
                </div>
            </div>
            <div class="col-md-3 form-group">
                <label for="trans_date">Req Date</label>
                <input type="date" name="trans_date" id="trans_date" class="form-control" max="<?=date('Y-m-d')?>" value="<?=(!empty($dataRow->trans_date)) ? $dataRow->trans_date : date("Y-m-d")?>">
            </div>
            <div class="col-md-6 form-group">
                <label for="item_id">Items</label>
                <select name="item_id" id="item_id" class="form-control reqItemId modal-select2 select2 req">
                    <option value="">Select Item</option>
                    <?php
                        if(!empty($itemData)){
                            foreach ($itemData as $row) {
								$selected = (!empty($dataRow->item_id) && $dataRow->item_id == $row->id) ? "selected" : (!empty($md_item_id && $md_item_id == $row->id) ? "selected" : "");
								$item_name = (!empty($row->item_code)) ? "[".$row->item_code."] ".$row->item_name : $row->item_name;
								echo "<option value='".$row->id."' data-item_name='".$item_name."' ".$selected.">".$item_name."</option>";
                            }
                        }
                    ?>
                </select>
                <div class="error item_err"></div>
            </div>
            <div class="col-md-3 form-group">
                <label for="req_qty">Req. Qty</label>
                <input type="text" name="req_qty" id="req_qty" class="form-control req" value="<?=(!empty($dataRow->req_qty) ? floatval($dataRow->req_qty) : (!empty($md_req_qty) ? floatval($md_req_qty) : "") ) ?>" />
                <div class="error qty_err"></div>
            </div>
            <div class="col-md-9 form-group">
                <label for="remark">Remark</label>
                <input type="text" name="remark" id="remark" class="form-control" value="<?=(!empty($dataRow->remark) ? $dataRow->remark : "") ?>" />
            </div>   
        </div>    
    </div>
</form>