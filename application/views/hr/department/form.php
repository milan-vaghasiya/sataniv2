<form>
	<div class="col-md-12">
        <div class="row">
			<input type="hidden" name="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""?>" />
			
			<div class="col-md-12 form-group">
				<label for='name' class="control-label">Department Name</label>
				<input type="text" id="name" name="name" class="form-control req" value="<?=(!empty($dataRow->name))?$dataRow->name:""?>">
			</div>
            
			<div class="col-md-12 form-group">
				<label for="section">Section</label>
				<select name="section[]" id="section" class="form-control select2 req" multiple>
                    <?php
                        foreach($categoryData as  $key => $value):
							$selected = (!empty($dataRow->section) && in_array($value,explode(",",$dataRow->section)))?"selected":"";
                            echo '<option value="'.$value.'" '.$selected.'>'.$value.'</option>';
                        endforeach;
                    ?>
                </select>
			</div>
		</div>
	</div>	
</form>
            
