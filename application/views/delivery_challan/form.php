<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
				<div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form autocomplete="off" id="saveDeliveryChallan" data-res_function="resSaveChallan" enctype="multipart/form-data">
                                <div class="col-md-12">
                                    <div class="row">

                                        <div class="hiddenInput">
                                            <input type="hidden" name="id" id="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""?>">
                                            <input type="hidden" name="entry_type" id="entry_type" value="<?=(!empty($dataRow->entry_type))?$dataRow->entry_type:$entry_type?>">
                                            <input type="hidden" name="from_entry_type" id="from_entry_type" value="<?=(!empty($dataRow->from_entry_type))?$dataRow->from_entry_type:((!empty($from_entry_type))?$from_entry_type:"")?>">
                                            <input type="hidden" name="ref_id" id="ref_id" value="<?=(!empty($dataRow->ref_id))?$dataRow->ref_id:((!empty($ref_id))?$ref_id:"")?>">                                            

                                            <input type="hidden" name="party_name" id="party_name" value="<?=(!empty($dataRow->party_name))?$dataRow->party_name:""?>">
                                            <input type="hidden" name="gst_type" id="gst_type" value="<?=(!empty($dataRow->gst_type))?$dataRow->gst_type:""?>">
                                            <input type="hidden" name="party_state_code" id="party_state_code" value="<?=(!empty($dataRow->party_state_code))?$dataRow->party_state_code:""?>">
                                            <input type="hidden" name="apply_round" id="apply_round" value="<?=(!empty($dataRow->apply_round))?$dataRow->apply_round:"1"?>">
                                            <input type="hidden" name="vou_acc_id" id="vou_acc_id" value="<?=(!empty($dataRow->vou_acc_id))?$dataRow->vou_acc_id:((!empty($vou_acc_id))?$vou_acc_id:0)?>">

                                            <input type="hidden" name="ledger_eff" id="ledger_eff" value="0">
                                            <input type="hidden" id="inv_type" value="SALES">
                                        </div>

                                        <div class="col-md-2 form-group">
											<label for="cm_id">UNIT</label>
											<select name="cm_id" id="cm_id" class="form-control select2 req">
												<?php
													foreach($companyList as $row):
														$selected = (!empty($dataRow->cm_id) && $row->id == $dataRow->cm_id)?"selected":((!empty($this->cm_id) && $this->cm_id != $row->id)?"disabled":"");
														echo '<option value="'.$row->id.'" '.$selected.'>'.$row->company_name.'</option>';
													endforeach;
												?>
											</select>
										</div>

                                        <div class="col-md-2 form-group">
                                            <label for="trans_number">DC No.</label>
                                            
                                            <div class="input-group">
                                                <input type="text" name="trans_prefix" id="trans_prefix" class="form-control" value="<?=(!empty($dataRow->trans_prefix))?$dataRow->trans_prefix:((!empty($trans_prefix))?$trans_prefix:"")?>" readonly>
                                                <input type="text" name="trans_no" id="trans_no" class="form-control numericOnly" value="<?=(!empty($dataRow->trans_no))?$dataRow->trans_no:((!empty($trans_no))?$trans_no:"")?>">
                                            </div>
                                            
                                            <input type="hidden" name="trans_number" id="trans_number" class="form-control" value="<?=(!empty($dataRow->trans_number))?$dataRow->trans_number:((!empty($trans_number))?$trans_number:"")?>" readonly>

                                            <div class="error trans_number"></div>
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label for="trans_date">DC. Date</label>
                                            <input type="date" name="trans_date" id="trans_date" class="form-control  fyDates req" value="<?=(!empty($dataRow->trans_date))?$dataRow->trans_date:getFyDate()?>">
                                        </div>

                                        <div class="col-md-4 form-group">
                                            <label for="party_id">Customer Name</label>
                                            <div class="float-right">
                                                <span class="float-right m-r-10">
                                                    <a class="text-primary font-bold waves-effect waves-dark getPendingOrders" href="javascript:void(0)">+ Sales Order</a>
                                                </span>
                                            </div>
                                            <select name="party_id" id="party_id" class="form-control select2 partyDetails partyOptions req" data-res_function="resPartyDetail" data-party_category="1">
                                                <option value="">Select Party</option>
                                                <?=getPartyListOption($partyList,(!empty($dataRow->party_id)?$dataRow->party_id:0))?>
                                            </select>

                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label for="gstin">GST NO.</label>
                                            <select name="gstin" id="gstin" class="form-control select2">
                                                <option value="">Select GST No.</option>
                                                <?php
                                                    if(!empty($dataRow->party_id)):
                                                        foreach($gstinList as $row):
                                                            $selected = ($dataRow->gstin == $row->gstin)?"selected":"";
                                                            echo '<option value="'.$row->gstin.'" '.$selected.'>'.$row->gstin.'</option>';
                                                        endforeach;
                                                    endif;
                                                ?>
                                            </select>
                                        </div>  

                                        <div class="col-md-2 form-group">
                                            <label for="doc_no">PO. No.</label>
                                            <input type="text" name="doc_no" id="doc_no" class="form-control" value="<?=(!empty($dataRow->doc_no))?$dataRow->doc_no:""?>">
                                        </div>

                                        <div class="col-md-2 form-group">
                                            <label for="doc_date">PO. Date</label>
                                            <input type="date" name="doc_date" id="doc_date" class="form-control" value="<?=(!empty($dataRow->doc_date))?$dataRow->doc_date:getFyDate()?>">
                                        </div>

                                        <div class="col-md-8 form-group">
                                            <label for="remark">Remark</label>
                                            <input type="text" name="remark" id="remark" class="form-control" value="<?=(!empty($dataRow->remark))?$dataRow->remark:""?>">
                                        </div>
                                    </div>

                                    <hr>

                                    <div class="col-md-12 row">
                                        <div class="col-md-6"><h4>Item Details : </h4></div>
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-outline-success waves-effect float-right add-item"><i class="fa fa-plus"></i> Add Item</button>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mt-3">
                                        <div class="error itemData"></div>
                                        <div class="row form-group">
                                            <div class="table-responsive">
                                                <table id="deliveryChallanItems" class="table table-striped table-borderless">
                                                    <thead class="thead-dark">
                                                        <tr>
                                                            <th style="width:5%;">#</th>
                                                            <th>Item Name</th>
                                                            <th>Qty.</th>
                                                            <th>UOM</th>
                                                            <th>Remark</th>
                                                            <th class="text-center" style="width:10%;">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tempItem" class="temp_item">
                                                        <tr id="noData">
                                                            <td colspan="15" class="text-center">No data available in table</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <?php $this->load->view('includes/terms_form',['termsList'=>$termsList,'termsConditions'=>(!empty($dataRow->termsConditions)) ? $dataRow->termsConditions : array()])?>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer bg-facebook">
                            <div class="col-md-12"> 
                                <button type="button" class="btn btn-success waves-effect show_terms" >Terms & Conditions</button>
                                <span class="term_error text-danger font-bold"></span>

                                <button type="button" class="btn waves-effect waves-light btn-success float-right save-form" onclick="customStore({'formId':'saveDeliveryChallan','txt_editor':'conditions'});" ><i class="fa fa-check"></i> Save </button>

                                <button type="button" class="btn btn-secondary press-close-btn btn-close-modal save-form float-right m-r-10" onclick="window.location.href='<?=base_url($headData->controller)?>'"><i class="fa fa-times"></i> Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal modal-right fade" id="itemModel" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content animated slideDown">
            <div class="modal-header" style="display:block;"><h4 class="modal-title">Add or Update Item</h4></div>
            <div class="modal-body">
                <form id="itemForm">
                    <div class="col-md-12">

                        <div class="row form-group">
							<div id="itemInputs">
								<input type="hidden" name="id" id="id" value="" />
								<input type="hidden" name="from_entry_type" id="from_entry_type" value="" />
                                <input type="hidden" name="ref_id" id="ref_id" value=""  />
								<input type="hidden" name="row_index" id="row_index" value="">
								<input type="hidden" name="item_code" id="item_code" value="" />
                                <input type="hidden" name="item_name" id="item_name" value="" />
                                <input type="hidden" name="item_type" id="item_type" value="1" />
                                <input type="hidden" name="unit_name" id="unit_name" value="" /> 
                                <input type="hidden" name="hsn_code" id="hsn_code" value="" />
                                <input type="hidden" name="gst_per" id="gst_per" value="" />
                                <input type="hidden" name="stock_eff" id="stock_eff" value="" />
                            </div>
                            

                            <div class="col-md-8 form-group">
								<label for="item_id">Product Name</label>                                
                                <select name="item_id" id="item_id" class="form-control select2 itemDetails itemOptions" data-res_function="resItemDetail" data-item_type="1,2,3,4" data-price_structure_id="">
                                    <option value="">Select Product Name</option>
                                    <?=getItemListOption($itemList)?>
                                </select>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="nature_of_process">Nature of Process</label>
                                <input type="text" name="nature_of_process" id="nature_of_process" class="form-control" value="" />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="packing_type">Packing Type</label>
                                <select name="packing_type" id="packing_type" class="form-control select2">
                                    <option value="1">Box</option>
                                    <option value="2">Wire Mesh Jaal</option>
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="qty">Qty.</label>
                                <input type="text" name="qty" id="qty" class="form-control floatOnly calculateQty req" value="0">
                            </div>                            
                            <div class="col-md-3 form-group">
                                <label for="price">Price</label>
                                <input type="text" name="price" id="price" class="form-control floatOnly req" value="0" />
                            </div>
                            <div class="col-md-3 form-group hidden">
                                <label for="disc_per">Disc. (%)</label>
                                <input type="text" name="disc_per" id="disc_per" class="form-control floatOnly" value="0">
                            </div>
                            <div class="col-md-3 form-group hidden">
                                <label for="org_price">MRP</label>
                                <input type="text" name="org_price" id="org_price" class="form-control floatOnly req" value="0" />
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="unit_id">UOM</label>        
                                <select name="unit_id" id="unit_id" class="form-control select2">
                                    <option value="">Select Unit</option>
                                    <?=getItemUnitListOption($unitList)?>
                                </select>                                                      
                            </div>
                            <div class="col-md-12 form-group">
                                <label for="item_remark">Remark</label>
                                <input type="text" name="item_remark" id="item_remark" class="form-control" value="" />
                            </div>         
                            
                            <div id="batchTransactions" class="col-md-12">
                                <h4>Batch Detail : </h4>
                                <div class="error batchError"></div>
                                <div class="table table-responsive">
                                    <table id="batchDetail" class="table table-bordered">
                                        <thead class="thead-info">
                                            <tr>
                                                <th>Location</th>
                                                <th>Batch No.</th>
                                                <th>Stock</th>
                                                <th>Qty.</th>
                                            </tr>
                                        </thead>
                                        <tbody id="batchTrans">
                                            <tr>
                                                <td colspan="4" class="text-center">No data available in table</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="thead-info">
                                            <tr>
                                                <th colspan="3" class="text-right">Total</th>
                                                <th>
                                                    <span id="total_qty">0</span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>          
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn waves-effect waves-light btn-outline-success saveItem btn-save" data-fn="save"><i class="fa fa-check"></i> Save</button>
                <button type="button" class="btn waves-effect waves-light btn-outline-warning saveItem btn-save-close" data-fn="save_close"><i class="fa fa-check"></i> Save & Close</button>
                <button type="button" class="btn waves-effect waves-light btn-outline-secondary btn-item-form-close" data-bs-dismiss="modal"><i class="fa fa-times"></i> Close</button>
            </div>
        </div>
    </div>
</div>


<?php $this->load->view('includes/footer'); ?>
<script src="<?php echo base_url(); ?>assets/js/custom/delivery-challan-form.js?v=<?= time() ?>"></script>
<script src="<?php echo base_url(); ?>assets/js/custom/calculate.js?v=<?= time() ?>"></script>
<script src="<?=base_url()?>assets/plugins/tinymce/tinymce.min.js"></script>
<script>
    $(document).ready(function(){
        initEditor({
            selector: '#conditions',
            height: 400
        });
    });
</script>
<?php
if(!empty($dataRow->itemList)):
    foreach($dataRow->itemList as $row):
        $row->row_index = "";
        $row->gst_per = floatVal($row->gst_per);
        echo '<script>AddRow('.json_encode($row).');</script>';
    endforeach;
endif;

if(!empty($orderItems)):
    foreach($orderItems as $row): 
        $row->row_index = "";
        $row->stock_eff = "1";
        $row->ref_id = $row->id;
        $row->from_entry_type = $row->entry_type;
        $row->entry_type = "";
        $row->batch_detail = json_encode($row->batch_detail);
        $row->id = "";
        echo '<script>AddRow('.json_encode($row).');</script>';
    endforeach;
endif;
?>