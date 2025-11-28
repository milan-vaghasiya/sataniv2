<form>
	<?php
	$mfg_type = (!empty($dataRow->mfg_type)) ? $dataRow->mfg_type : $mfg_type;
	?>
	<div class="col-md-12">
		<div class="row">
			<input type="hidden" name="id" value="<?= (!empty($dataRow->id)) ? $dataRow->id : ""; ?>" />
			<input type="hidden" name="prc_detail_id" id="prc_detail_id" value="<?= (!empty($dataRow->prc_detail_id)) ? $dataRow->prc_detail_id : ""?>">
			<input type="hidden" name="mfg_type" id="mfg_type" value="<?= (!empty($dataRow->mfg_type)) ? $dataRow->mfg_type : $mfg_type?>">
			<input type="hidden" name="party_id" value="<?= (!empty($dataRow->party_id)) ? $dataRow->party_id :0 ?>" />
			<input type="hidden" name="so_trans_id" id="so_trans_id" value="">
			<input type="hidden" name="ref_batch" id="ref_batch" value="<?= (!empty($dataRow->ref_batch)) ? $dataRow->ref_batch : (!empty($ref_batch)?$ref_batch:'')?>">
			<input type="hidden" name="heat_no" id="heat_no" value="<?= (!empty($dataRow->heat_no)) ? $dataRow->heat_no : (!empty($heat_no)?$heat_no:'')?>">

			<?php if(!empty($mfg_type) && $mfg_type == 'Machining'): ?>
			<input type="hidden" name="prc_no" value="<?= (!empty($dataRow->prc_no)) ? $dataRow->prc_no : $prc_no ?>" />
			<div class="col-md-4 form-group">
				<label for="prc_number">Batch No.</label>
				<input type="text" name="prc_number" id="prc_number" class="form-control req" value="<?= (!empty($dataRow->prc_number)) ? $dataRow->prc_number : $prc_number ?>" readonly />
			</div>
			<?php else: ?>
				<input type="hidden" name="prc_number" value="<?= (!empty($dataRow->prc_number)) ? $dataRow->prc_number :"" ?>" />
				<div class="col-md-4 form-group">
					<label for="prc_no">Batch No.</label>
					<input type="text" name="prc_no" id="prc_no" class="form-control req" value="<?= (!empty($dataRow->prc_no)) ? $dataRow->prc_no : $prc_no ?>" readonly />
				</div>
			<?php endif; ?>
			<div class="col-md-4 form-group">
				<label for="prc_date">Batch Date</label>
				<input type="date" id="prc_date" name="prc_date" class="form-control req" value="<?= (!empty($dataRow->prc_date)) ? $dataRow->prc_date : date("Y-m-d") ?>" max="<?=date("Y-m-d")?>" onkeydown="return false" />
			</div>
			<div class="col-md-4 form-group">
				<label for="target_date">Target Date</label>
				<input type="date" id="target_date" name="target_date" class="form-control req" value="<?= (!empty($dataRow->target_date)) ? $dataRow->target_date : date("Y-m-d") ?>" />
			</div>
			<?php if($mfg_type == 'Forging'): ?>
			<div class="col-md-8 form-group">
				<label for="item_id">Product Name</label>
				<select name="item_id" id="item_id" class="form-control select2 req" autocomplete="off">
					<option value="">Select Product</option>
					<?php
					if (!empty($productList)) :
						foreach($productList as $row):
							$selected = (!empty($dataRow->item_id) && $dataRow->item_id == $row->id)?'selected':'';
					?>
							<option value="<?=$row->id?>" <?=$selected?>><?=(!empty($row->item_code)?' ['.$row->item_code.'] ':'').$row->item_name?></option>
					<?php
						endforeach;
					endif;
					?>
				</select>
			</div>
			<?php else: ?>
				<input type="hidden" id="item_id" name="item_id" value="<?=!empty($dataRow->item_id)?$dataRow->item_id:$item_id?>">
			<?php endif; ?>
			<div class="col-md-4 form-group">
				<label for="qty">Quantity</label>
				<input type="text" name="qty" id="qty" class="form-control numericOnly countWeight req" min="0" placeholder="Enter Qty." value="<?= (!empty($dataRow->prc_qty)) ? floatval($dataRow->prc_qty) : "" ?>" />
			</div>
			<div class="col-md-12 form-group">
				<label for="remark">Production Instruction</label>
				<textarea name="remark" id="remark" class="form-control" rows="2" ><?= (!empty($dataRow->remark)) ? $dataRow->remark : "" ?></textarea>
			</div>
			<div class="col-md-12 form-group mb-5" id="processData">
			<?php
				if (!empty($processData)) :
					echo $processData;
				endif;
				?>
			</div>
			<div class="error process"></div>
		</div>
	</div>
</form>