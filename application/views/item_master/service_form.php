<form class="itemMasterForm" enctype="multipart/form-data">
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" id="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""?>">
            <input type="hidden" name="item_type" id="item_type" value="<?=(!empty($dataRow->item_type))?$dataRow->item_type:$item_type?>">
            <input type="hidden" name="stock_type" id="stock_type" value="1">

            <div class="col-md-4 form-group">
                <label for="item_code">Item Code</label>	
                <input type="text" name="item_code" class="form-control req" value="<?=(!empty($dataRow->item_code))?$dataRow->item_code:(!empty($item_code) ? $item_code : ""); ?>"/>
            </div>
            
            <?php 
                $itmtp = (!empty($dataRow->item_type))?$dataRow->item_type:$item_type;  
            ?>
            <div class="<?=($itmtp == 3)?'col-md-5':'col-md-8'?> form-group">
                <label for="item_name">Item Name</label>
				<?php if($itmtp == 3): ?>
                <div class="input-group">
                    <input type="text" name="size" id="size" class="form-control" placeholder="Size" value="<?=(!empty($dataRow->size))?$dataRow->size:""?>" style="max-width:33%;" />
                    <input type="text" name="shape" id="shape" class="form-control noSpecialChar" placeholder="Shape" value="<?=(!empty($dataRow->shape))?$dataRow->shape:""?>" />
                    <input type="text" name="bartype" id="bartype" class="form-control" placeholder="Bar Type" value="<?=(!empty($dataRow->bartype))?$dataRow->bartype:""?>" style="max-width:33%;" />
                  <input type="hidden" name="item_name" class="form-control req" value="<?=htmlentities((!empty($dataRow->item_name)) ? $dataRow->item_name : "")?>" />
                </div>
				<?php else: ?>
					<input type="text" name="item_name" class="form-control req" value="<?=htmlentities((!empty($dataRow->item_name)) ? $dataRow->item_name : "")?>" />
				<?php endif; ?>                
            </div>
            <?php if($itmtp == 3): ?>
            <div class="col-md-3 form-group">
                <label for="grade_id">Material Grade</label>
                <select name="grade_id" id="grade_id" class="form-control select2">
                    <option value="">Select Material Grade</option>
                    <?php
                        foreach($materialGrade as $row):
                            $selected = (!empty($dataRow->grade_id) && $dataRow->grade_id == $row->id)?"selected":"";
                            echo '<option value="'.$row->id.'" '.$selected.'>'.$row->material_grade.'</option>';
                        endforeach;
                    ?>
                </select>
            </div>
            <?php endif; ?>

            <div class="col-md-4 form-group">
                <label for="category_id">Category</label>
                <select name="category_id" id="category_id" class="form-control select2 req">
                    <option value="0">Select</option>
                    <?php
                        foreach ($categoryList as $row) :
                            $selected = (!empty($dataRow->category_id) && $dataRow->category_id == $row->id) ? "selected" : "";
                            echo '<option value="' . $row->id . '" ' . $selected . '>' . $row->category_name . '</option>';
                        endforeach;
                    ?>
                </select>
            </div>     
            
			<div class="col-md-4 form-group">
                <label for="unit_id">Unit</label>
                <select name="unit_id" id="unit_id" class="form-control select2 req">
                    <option value="0">--</option>
                    <?=getItemUnitListOption($unitData,((!empty($dataRow->unit_id))?$dataRow->unit_id:""))?>
                </select>
            </div>
            
			<div class="col-md-4 form-group">
                <label for="hsn_code">HSN Code</label>
                <select name="hsn_code" id="hsn_code" class="form-control select2">
                    <option value="">Select HSN Code</option>
                    <?=getHsnCodeListOption($hsnData,((!empty($dataRow->hsn_code))?$dataRow->hsn_code:""))?>
                </select>
            </div>

            <div class="<?=($itmtp == 3)?'col-md-3':'col-md-4'?> form-group">
                <label for="gst_per">GST (%)</label>
                <select name="gst_per" id="gst_per" class="form-control calMRP select2">
                    <?php
                        foreach($this->gstPer as $per=>$text):
                            $selected = (!empty($dataRow->gst_per) && floatVal($dataRow->gst_per) == $per)?"selected":"";
                            echo '<option value="'.$per.'" '.$selected.'>'.$text.'</option>';
                        endforeach;
                    ?>
                </select>
            </div>
            <div class="col-md-8 form-group">
                <label for="description">Product Description</label>
                <textarea name="description" id="description" class="form-control" rows="1"><?=(!empty($dataRow->description))?$dataRow->description:""?></textarea>
            </div>
        </div>

        <?php if(!empty($customFieldList)): ?>
            <h4 class="fs-15 text-primary border-bottom-sm">Custom Fields</h4>
            <div class="row">
                <?php
                    
                    foreach($customFieldList as $field):
                        ?>
                        <div class="col-md-4 form-group">
                            <label for="wt_pcs"><?=$field->field_name?></label>
                            <?php
                            if($field->field_type == 'SELECT'):
                                ?>
                                <select name="customField[f<?=$field->field_idx?>]" id="f<?=$field->field_idx?>" class="form-control select2">
                                    <option value="">Select</option>
                                <?php
                                foreach($customOptionList as $row):
                                    if($row->type == $field->id):
                                        $selected = (!empty($customData) && !empty(htmlentities($customData->{'f'.$field->field_idx}) && htmlentities($customData->{'f'.$field->field_idx}) == htmlentities($row->title)))?'selected':'';
                                        ?>
                                        <option value="<?=htmlentities($row->title)?>" <?=$selected?>><?=$row->title?></option>
                                        <?php
                                    endif;
                                endforeach;
                            elseif($field->field_type == 'TEXT'):
                                ?>
                                <input type="text" name="customField[f<?=$field->field_idx?>]" id="f<?=$field->field_idx?>" class="form-control" value="<?=(!empty($customData) && !empty($customData->{'f'.$field->field_idx}))?$customData->{'f'.$field->field_idx}:''?>">
                                <?php
                            elseif($field->field_type == 'NUM'):
                                ?>
                                <input type="text" name="customField[f<?=$field->field_idx?>]" id="f<?=$field->field_idx?>" class="form-control floatOnly" value="<?=(!empty($customData) && !empty($customData->{'f'.$field->field_idx}))?$customData->{'f'.$field->field_idx}:''?>">
                                <?php
                            endif;
                            ?>
                            </select>
                        </div>
                        <?php
                    endforeach;
                ?>                
            </div>
        <?php endif; ?>
    </div>
</form>
<script>
$(document).ready(function(){
    $(document).on('change','.itemMasterForm #hsn_code',function(){
        $(".itemMasterForm #gst_per").val(($(this).find(':selected').data('gst_per') || 0));
        initSelect2();
    });
});
</script>
