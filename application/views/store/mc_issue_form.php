<form>
    <div class="col-md-12">
        <div class="row">

            <input type="hidden" name="req_id" id="req_id" value="<?=$reqData->id?>">
            <input type="hidden" name="item_id" value="<?=$reqData->item_id?>">
            <input type="hidden" name="prc_id" id="prc_id" value="">
            
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="challan_no">Issue No.</label>
                    <div class="input-group">
                        <input type="text" name="issue_number" class="form-control" value="<?= $issue_prefix ?>" readOnly />
                        <input type="hidden" name="issue_no" value="<?= $issue_no ?>" readOnly />
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label for="issue_date">Issue Date</label>
                    <input type="date" name="issue_date" id="issue_date" class="form-control" max="<?=date('Y-m-d')?>" value="<?=date("Y-m-d")?>">
                </div>
				<div class="col-md-4 form-group">
                    <label for="issued_to">Issued To</label>
                    <select name="issued_to" id="issued_to" class="form-control select2 req">
                        <option value="">Select Issued To</option>
                        <?php
                            if(!empty($empData)){
                                foreach ($empData as $row) {
                                    echo "<option value='".$row->id."'>".$row->emp_name."</option>";
                                }
                            }
                        ?>
                    </select>
                </div>
                 <div class="col-md-12 form-group">
                    <label for="remark">Request Remark</label>
                    <input type="text" id="remark" class="form-control" value="<?=(!empty($reqData->remark) ? $reqData->remark : "")?>" readOnly/>
                </div>
            </div>

            <div class="col-md-12 form-group mt-4">
                <div class="error general_batch_no"></div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-info">
                            <th>Location</th>
                            <th>Batch No.</th>
                            <th>Heat No.</th>
                            <th>Stock Qty.</th>
                            <th>Issue Qty.</th>
                        </thead>
                        <tbody id="tbodyData">
                            <?php 
                            if(isset($batchData) && !empty($batchData)){
                                $i = 1;
                                foreach ($batchData as $value) {
                                    echo "<tr>";
                                    echo '<td>'.$value->location.'</td>';
                                    echo '<td>'.$value->batch_no.'</td>';
                                    echo '<td>'.$value->heat_no.'</td>';
                                    echo '<td>'.floatVal($value->qty).'</td>';
                                    echo '<td>
                                            <input type="text" name="batch_qty[]" class="form-control batchQty floatOnly" min="0" value="" />
                                            <div class="error batch_qty_' . $i . '"></div>
                                            <input type="hidden" name="batch_no[]" id="batch_number_' . $i . '" value="' . $value->batch_no . '" />
                                            <input type="hidden" name="heat_no[]" id="heat_no_' . $i . '" value="' . $value->heat_no . '" />
											<input type="hidden" name="location_id[]" id="location_' . $i . '" value="' . $value->location_id . '" />
                                        </td>
									</tr>';
                                    $i++;
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                    <div class="error table_err"></div>
                </div>
            </div>

        </div>
    </div>
</form>
<script>
    $(document).ready(function(){
        initModalSelect();
        
        setTimeout(function(){ $('#item_id').trigger('change'); }, 1000);
        
        $(document).on('change', '#item_id', function (e) {
            e.stopImmediatePropagation();e.preventDefault();

            var item_id = $(this).val();
            var req_id = $("option:selected", this).data('req_id');
            $('#req_id').val(req_id);
			
			var prc_id = $("option:selected", this).data('prc_id');
            $('#prc_id').val(prc_id);
            $("#prc_id").select2();
			
			if(item_id){
				$.ajax({
					url:base_url + controller + "/getBatchWiseStock",
					type:'post',
					data:{item_id:item_id},
					dataType:'json',
					success:function(data){
						if(data.status == 1){
							$('#tbodyData').html('');
							$('#tbodyData').html(data.tbodyData);
							$('#is_return').val(data.is_return)
						}
					}
				});
			}
        });
    });
</script>