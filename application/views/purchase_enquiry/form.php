<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h4><u>Purchase Enquiry</u></h4>
                    </div>
                    <div class="card-body">
                        <form autocomplete="off" id="savePurchaseEnquiry" data-res_function="resSaveEnquiry">
                            <div class="col-md-12">
								<input type="hidden" name="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""?>" />
                                <input type="hidden" name="entry_type" id="entry_type" value="<?= !empty($dataRow->entry_type)?$dataRow->entry_type:((!empty($entry_type)) ? $entry_type : '') ?>" />

                                <div class="row form-group">
									<div class="col-md-3">
                                        <label for="trans_no">Enquiry No.</label>
                                        <div class="input-group mb-3">
                                            <input type="text" name="trans_prefix" class="form-control" value="<?=(!empty($dataRow->trans_prefix))?$dataRow->trans_prefix:$trans_prefix?>" readonly />
                                            <input type="text" name="trans_no" class="form-control req" value="<?=(!empty($dataRow->trans_no))?$dataRow->trans_no:$trans_no?>" readonly />
                                        </div>
									</div>
									<div class="col-md-3">
										<label for="trans_date">Enquiry Date</label>
                                        <input type="date" id="trans_date" name="trans_date" class=" form-control" aria-describedby="basic-addon2" value="<?=(!empty($dataRow->trans_date))?$dataRow->trans_date:date("Y-m-d")?>" />
									</div>
									<div class="col-md-6 form-group">
										<label for="party_id">Supplier Name</label>
										<select name="party_id[]" id="party_id" class="form-control select2 req" multiple="multiple"> 
											<option value="">Select Supplier</option>
											<?=getPartyListOption($partyList,((!empty($dataRow->party_id))?$dataRow->party_id:0))?>
										</select>
										<div class="error party_id"></div>
									</div>
                                    <div class="col-md-12">
                                        <label for="remark">Remark</label>
                                        <input type="text" name="remark" id="remark" class="form-control" value="<?=(!empty($dataRow->remark))?$dataRow->remark:""?>">
                                    </div>
                                </div>
							</div>
							<hr>
                            <div class="col-md-12 ">
                                <div class="col-md-6 row"><h4>Item Details : </h4></div>

                                <div class="row form-group enquiryItemForm">

                                    <input type="hidden" name="trans_id" id="trans_id" value="" />
                                    <input type="hidden" name="unit_name" id="unit_name" value="" />
                                    <input type="hidden" name="row_index" id="row_index" value="" />
                                    <input type="hidden" name="req_id" id="req_id" value=""  />

                                    <div class="col-md-6 form-group">
                                        <label for="item_name">Item Name</label>
                                        <input type="text" name="item_name" id="item_name" class="form-control req" value="" />
                                        <input type="hidden" name="item_id" id="item_id" value="" />
                                    </div>

                                    <div class="col-md-2 form-group">
                                        <label for="item_type">Item Type</label>
                                        <select name="item_type" id="item_type" class="form-control select2 req">
                                            <option value="">Select Item Type</option>
                                            <?php
                                                foreach($categoryList as $row):
                                                    echo '<option value="'.$row->id.'">'.$row->category_name.'</option>';
                                                endforeach;
                                            ?>
                                        </select>
                                        <input type="hidden" name="item_type_name" id="item_type_name" value="">
                                    </div>
                                    
                                    <div class="col-md-2 form-group">
                                        <label for="qty">Quantity</label>
                                        <input type="text" name="qty" id="qty" class="form-control floatOnly" value="0">
                                    </div>

                                    <div class="col-md-2 form-group">
                                        <label for="unit_id">Unit</label>
                                        <select name="unit_id" id="unit_id" class="form-control select2 req">
                                            <option value="0">--</option>
                                            <?php
                                                foreach($unitData as $row):
                                                    echo '<option value="'.$row->id.'">['.$row->unit_name.'] '.$row->description.'</option>';
                                                endforeach;
                                            ?>
                                        </select>
                                        <div class="error unit_id"></div>
                                    </div>

                                    <div class="col-md-10 form-group">
                                        <label for="item_remark">Item Remark</label>
                                        <input type="text" name="item_remark" id="item_remark" class="form-control" value="">
                                    </div>
                                    
                                    <div class="col-md-2 form-group">
										<button type="button" class="btn btn-outline-success waves-effect float-right btn-block mt-25 saveItem"><i class="fa fa-plus"></i> Add Item</button>
									</div>
                                </div>
                            </div>							
							<div class="col-md-12 mt-3">
								<div class="row form-group">
									<div class="table-responsive ">
										<table id="purchaseEnqItems" class="table table-striped table-borderless">
											<thead class="thead-info">
												<tr>
													<th style="width:5%;">#</th>
													<th>Item Name</th>
													<th>Item Type</th>
													<th>Qty.</th>
													<th>Unit</th>
													<th>Item Remark</th>
													<th class="text-center" style="width:10%;">Action</th>
												</tr>
											</thead>
											<tbody id="tempItem" class="temp_item">
												<tr id="noData">
													<td colspan="7" class="text-center">No data available in table</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
								<hr>								
							</div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <div class="col-md-12">
							<button type="button" class="btn waves-effect waves-light btn-success float-right save-form" onclick="customStore({'formId':'savePurchaseEnquiry'});" ><i class="fa fa-check"></i> Save</button>
                            <button type="button" class="btn btn-secondary press-close-btn btn-close-modal save-form float-right m-r-10" onclick="window.location.href='<?=base_url($headData->controller)?>'"><i class="fa fa-times"></i> Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>


<?php $this->load->view('includes/footer'); ?>
<script src="<?php echo base_url();?>assets/js/custom/purchase-enquiry-form.js?v=<?=time()?>"></script>
<?php
if(!empty($dataRow->itemData)):
    foreach($dataRow->itemData as $row):
        $row->row_index = '';
        $row->trans_id = $row->id;
        $row->item_type_name = $row->category_name;
        unset($row->id);
        echo '<script>AddRow('.json_encode($row).');</script>';
    endforeach;
endif;

if(!empty($reqItemList)):
	foreach($reqItemList as $row):  
		$row->req_id = $row->id;
		$row->item_remark = $row->remark;
        $row->item_type_name = $row->category_name;
        $row->item_type = $row->item_type;
		$row->row_index = "";
		$row->id = "";
		$row = json_encode($row);
		echo '<script>AddRow('.$row.');</script>';
	endforeach;
endif;
?>