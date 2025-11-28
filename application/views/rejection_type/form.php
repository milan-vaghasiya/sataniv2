
<form>
	<div class="col-md-12">
        <div class="row">
			<input type="hidden" name="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""?>" />
			
			<div class="col-md-12 form-group">
				<label for='rejection_type' class="control-label">Rejection Type</label>
				<input type="text" id="rejection_type" name="rejection_type" class="form-control req" value="<?=(!empty($dataRow->rejection_type))?$dataRow->rejection_type:""?>">
			</div>
		</div>
	</div>	
</form>
            
