<form>
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""; ?>" />
            <input type="hidden" name="item_type" value="<?=(!empty($dataRow->item_type))?$dataRow->item_type:$item_type; ?>" />
            <input type="hidden" name="category_id" value="<?=(!empty($dataRow->category_id))?$dataRow->category_id:180; ?>" />

            <div class="col-md-12 form-group">
                <label for="item_name">Scrap Group Name</label>
                <input type="text" name="item_name" class="form-control req" value="<?=(!empty($dataRow->item_name))?$dataRow->item_name:""?>" />
            </div>
            <div class="col-md-6 form-group">
                <label for="unit_id">Unit</label>
                <select name="unit_id" id="unit_id" class="form-control select2 req">
                    <option value="">--</option>
                    <?php
						foreach ($unitData as $row) :
							$selected = (!empty($dataRow->unit_id) && $dataRow->unit_id == $row->id) ? "selected" : "";
							echo '<option value="' . $row->id . '" ' . $selected . '>[' . $row->unit_name . '] ' . $row->description . '</option>';
						endforeach;
                    ?>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="stock_type">Stock Type</label>
                <select name="stock_type" id="stock_type" class="form-control select2 req">
                    <option value="1" <?=(!empty($dataRow->stock_type) && $dataRow->stock_type == 1)?'selected':''?>>Minus From Used</option>
                    <option value="2" <?=(!empty($dataRow->stock_type) && $dataRow->stock_type == 2)?'selected':''?>>Minus From Issued</option>
                </select>
            </div>
            <div class="col-md-12 form-group">
                <label>Process</label>
                <select name="item_image[]" id="item_image" class="form-control select2 req" multiple="multiple">
					<?php
						foreach($processList as $row):
							$processIds	= explode(",",$dataRow->item_image);
							$selected = (in_array($row->id,$processIds)) ? "selected" : "";
							echo '<option '.$selected.' value="'.$row->id.'">'.$row->process_name.'</option>';
						endforeach;
					?>
				</select>
			</div>
        </div>
    </div>
</form>