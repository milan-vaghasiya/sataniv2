<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
							<div class="col-md-10"> 
								<h4 class="card-title">Product Details</h4>
							</div>
							<div class="col-md-2"> 
								<a href="<?= base_url($headData->controller.'/list/1') ?>" class="btn waves-effect waves-light btn-outline-dark float-right"><i class="fa fa-arrow-left"></i> Back</a>
							</div>
                        </div>
					</div>
					<div class="card-body">
						<div class="col-md-12">
							<div class="row">
								<div class="col-lg-4 col-xlg-4 col-md-4">
									<div class="card">
										<div class="card-body">
											<table class="table table-bordered">
												<thead class="thead-grey">
													<tr>
														<th style="width:35%;">Item Code</th>
														<td> <?= (!empty($productData->item_code)) ? $productData->item_code : "-"; ?> </td>
													</tr>
													<tr>
														<th>Item Name</th>
														<td> <?= (!empty($productData->item_name)) ? $productData->item_name : "-" ?> </td>
													</tr>
													<tr>
														<th>Unit</th>
														<td> <?= (!empty($productData->unit_name)) ? $productData->unit_name : "-"; ?> </td>
													</tr>
													<tr>
														<th>Category</th>
														<td> <?= (!empty($productData->category_name)) ? $productData->category_name : "-"; ?> </td>
													</tr>
													<tr>
														<th>HSN</th>
														<td> <?= (!empty($productData->hsn_code)) ? $productData->hsn_code : "-"; ?> </td>
													</tr>
													<tr>
														<th>Drawing No</th>
														<td> <?= (!empty($productData->drawing_no)) ? $productData->drawing_no : "-"; ?> </td>
													</tr>
													<tr>
														<th>Product Description</th>
														<td> <?= (!empty($productData->description)) ? $productData->description : "-"; ?> </td>
													</tr>
												</thead>
											</table>
										</div>
									</div>
								</div>
								<div class="col-lg-8 col-xlg-8 col-md-8">
									<div class="card">
										<ul class="nav nav-pills custom-pills" id="pills-tab" role="tablist">
											<li class="nav-item">
											    <?php
											    $bomData = "{postData:{'item_id':".$item_id."},'table_id':'productKit','tbody_id':'kitItems','tfoot_id':'','fnget':'productKitHtml'}";
											    ?>
												<a class="nav-link active " id="pills-bomDetails-tab" onclick="stageFilter(<?=$bomData?>)" data-toggle="pill" href="#bomDetails" role="tab" aria-controls="pills-bomDetails" aria-selected="true" flow="up">BOM</a>
											</li>
											<li class="nav-item">
											    <?php
											    $prcData = "{'postData':{'item_id':".$item_id."},'table_id':'itemProcess','tbody_id':'itemProcessData','tfoot_id':'','fnget':'productProcessHtml'}";
											    ?>
												<a class="nav-link " id="pills-productProcess-tab" data-toggle="pill" onclick="stageFilter(<?=$prcData?>)" href="#productProcess" role="tab" aria-controls="pills-productProcess" aria-selected="false">Process</a>
											</li>
											<li class="nav-item">
											    <?php
											    $cycleData = "{'postData':{'item_id':".$item_id."},'table_id':'ctTable','tbody_id':'ctItems','tfoot_id':'','fnget':'cycleTimeHtml'}";
											    ?>
												<a class="nav-link " id="pills-ctDetails-tab" data-toggle="pill" onclick="stageFilter(<?=$cycleData?>)" href="#ctDetails" role="tab" aria-controls="pills-ctDetails" aria-selected="false">Cycle Time</a>
											</li>
											<li class="nav-item">
											    <?php
											    $stdData = "{'postData':{'item_id':".$item_id."},'table_id':'stTable','tbody_id':'stItems','tfoot_id':'','fnget':'standardsHtml'}";
											    ?>
												<a class="nav-link " id="pills-stDetails-tab" onclick="stageFilter(<?=$stdData?>)" data-toggle="pill" href="#stDetails" role="tab" aria-controls="pills-stDetails" aria-selected="false">Standards</a>
											</li>
										</ul> 
										<div class="tab-content" id="pills-tabContent">

											<div class="tab-pane fade show active" id="bomDetails" role="tabpanel"aria-labelledby="pills-bomDetails-tab">
												<form id="addProductKitItems" data-res_function="getProductKitHtml">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <input type="hidden" name="id" id="id" value="" />
                                                            <input type="hidden" name="item_id" id="item_id" value="<?=$item_id ?>" />

                                                            <div class="col-md-3 form-group">
                                                                <label for="group_name">Group Name</label>
                                                                <input type="text" name="group_name" id="group_name" class="form-control req" value="RM GROUP" readOnly/>
                                                            </div>
                                                            <div class="col-md-3 form-group">
                                                                <label for="process_id">Process</label>
                                                                <select name="process_id" class="form-control select2 req">
                                                                    <?php
                                                                    if(!empty($process)):
                                                                        foreach($process as $row):
                                                                            echo '<option value="'.$row->process_id.'" >'.$row->process_name.'</option>';
                                                                        endforeach;
                                                                    endif;
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-4 form-group">
                                                                <label for="kit_item_id">Used To Be Item</label>
                                                                <select id="kit_item_id" name="kit_item_id" class="form-control select2 req">
                                                                    <option value="">Select Item</option>
                                                                    <?php
                                                                    if(!empty($rawMaterial)):
                                                                        foreach($rawMaterial as $row):
                                                                            $item_name = (!empty($row->item_code)) ? '['.$row->item_code.'] '.$row->item_name : $row->item_name;
                                                                            echo '<option value="'.$row->id.'" data-unit_id="'.$row->unit_id.'">'.$item_name.'</option>';
                                                                        endforeach;
                                                                    endif;
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-2 form-group">
                                                                <label for="kit_item_qty">Bom Qty</label>
                                                                <input type="text" id="kit_item_qty" name="kit_item_qty" class="form-control floatOnly req" value="" min="0" />        
                                                            </div>
                                                            <div class="col-md-12 form-group">
                                                                <?php
                                                                    $param = "{'formId':'addProductKitItems','fnsave':'saveProductKit','controller':'items','res_function':'getProductKitHtml'}";
                                                                ?> 
                                                                <button type="button" class="btn waves-effect waves-light btn-outline-success btn-save save-form float-right" onclick="customStore(<?=$param?>)"><i class="fa fa-check"></i> Save</button>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="table-responsive">
                                                            <table id="productKit" class="table jpExcelTable align-items-center">
                                                                <thead class="thead-info">
                                                                    <tr class="text-center">
                                                                        <th style="width:5%;">#</th>
                                                                        <th>Group Name</th>
                                                                        <th>Process</th>
                                                                        <th>Item Name</th>
                                                                        <th>Bom Qty</th>
                                                                        <th class="text-center" style="width:10%;">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="kitItems">
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </form>
											</div>

											<div class="tab-pane fade" id="productProcess" role="tabpanel" aria-labelledby="pills-productProcess-tab">
                                                <form id="viewProductProcess" data-res_function="getProductProcessHtml">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <input type="hidden" name="id" id="id" value="" />
                                                            <input type="hidden" name="item_id" id="item_id" value="<?=$item_id ?>" />

                                                            <div class="col-md-10 form-group">
                                                                <label for="process_id">Production Process</label>
                                                                <select name="process_id[]" id="process_id" class="form-control select2" multiple>
                                                                    <?php
                                                                    foreach ($processDataList as $row) :
                                                                            echo '<option value="' . $row->id . '" >' . $row->process_name . '</option>';
                                                                    endforeach;
                                                                    ?>
                                                                </select>
                                                                <div class="error process_id"></div>
                                                            </div>
                                                            <div class="col-md-2 form-group">
                                                                <?php
                                                                    $param = "{'formId':'viewProductProcess','fnsave':'saveProductProcess','controller':'items','res_function':'getProductProcessHtml'}";
                                                                ?> 
                                                                <button type="button" class="btn btn-block waves-effect waves-light btn-outline-success btn-save save-form float-right mt-20" onclick="customStore(<?=$param?>)" style="height:36px"><i class="fa fa-check"></i> Update</button>
                                                            </div>
                                                        </div>
                                                        <hr>
                                                        <div class="col-md-12 form-group">
                                                            <h6 style="color:#ff0000;font-size:1rem;"><i>Note : Drag & Drop Row to Change Process Sequence</i></h6>
                                                        </div>  
                                                        <div class="table-responsive">
                                                            <table id="itemProcess" class="table jpExcelTable table-bordered">
                                                                <thead class="thead-info">
                                                                    <tr>
                                                                        <th style="width:10%;text-align:center;">#</th>
                                                                        <th style="width:70%;">Process Name</th>
                                                                        <th style="width:20%;">Sequence</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="itemProcessData">
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </form>
											</div>

											<div class="tab-pane fade" id="ctDetails" role="tabpanel" aria-labelledby="pills-ctDetails-tab">
                                                <form id="cycleTime" data-res_function="getCycleTimeHtml">
                                                    <div class="card-body">
                                                        <div class="row">                                                            
                                                            <h6 style="color:#ff0000;font-size:1rem;"><i>Note : Cycle Time Per Piece In Seconds</i></h6>
                                                            <div class="table-responsive">
                                                                <table id="ctTable" class="table jpExcelTable align-items-center">
                                                                    <thead class="thead-info">
                                                                        <tr>
                                                                            <th style="width:10%;text-align:center;">#</th>
                                                                            <th style="width:40%;">Process Name</th>
                                                                            <th style="width:25%;">Cycle Time</th>
                                                                            <th style="width:25%;">Finished Weight</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="ctItems">
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-12 form-group">
                                                                <?php
                                                                    $param = "{'formId':'cycleTime','fnsave':'saveCT','controller':'items','res_function':'getCycleTimeHtml'}";
                                                                ?> 
                                                                <button type="button" class="btn waves-effect waves-light btn-outline-success btn-save save-form float-right" onclick="customStore(<?=$param?>)"><i class="fa fa-check"></i> Save</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
											</div>

											<div class="tab-pane fade" id="stDetails" role="tabpanel" aria-labelledby="pills-stDetails-tab">
                                                <form id="standards" data-res_function="getStandardsHtml">
                                                    <div class="card-body">
                                                        <div class="row">                                                            
                                                            <h6 style="color:#ff0000;font-size:1rem;"><i>Note : Cycle Time Per Piece In Seconds</i></h6>
                                                            <div class="table-responsive">
                                                                <table id="stTable" class="table jpExcelTable align-items-center">
                                                                    <thead class="thead-info">
                                                                        <tr>
                                                                            <th style="width:10%;text-align:center;">#</th>
                                                                            <th style="width:50%;">Process Name</th>
                                                                            <th style="width:40%;">Attachment</th>
                                                                            <th style="width:40%;">Download</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="stItems">
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-12 form-group">
                                                                <?php
                                                                    $param = "{'formId':'standards','fnsave':'saveStandards','controller':'items','res_function':'getStandardsHtml'}";
                                                                ?> 
                                                                <button type="button" class="btn waves-effect waves-light btn-outline-success btn-save save-form float-right" onclick="customStore(<?=$param?>)"><i class="fa fa-check"></i> Save</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
											</div>

										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('includes/footer'); ?>
<script src="<?php echo base_url();?>assets/js/custom/product.js"></script>
<script>
var tbodyData = false;
$(document).ready(function(){
    setPlaceHolder();
    if(!tbodyData){
        var bomData = {'postData':{'item_id':$("#item_id").val()},'table_id':"productKit",'tbody_id':'kitItems','tfoot_id':'','fnget':'productKitHtml'};    
        getTransHtml(bomData);
        
        // var processData = {'postData':{'item_id':$("#item_id").val()},'table_id':"itemProcess",'tbody_id':'itemProcessData','tfoot_id':'','fnget':'productProcessHtml'};  
        // getTransHtml(processData);
        
        // var ctData = {'postData':{'item_id':$("#item_id").val()},'table_id':"ctTable",'tbody_id':'ctItems','tfoot_id':'','fnget':'cycleTimeHtml'};        
        // getTransHtml(ctData);
        
        // var stData = {'postData':{'item_id':$("#item_id").val()},'table_id':"stTable",'tbody_id':'stItems','tfoot_id':'','fnget':'standardsHtml'};        
        // getTransHtml(stData);

        tbodyData = true;
    }

});

function stageFilter(postdata){
	 getTransHtml(postdata);
}
function getProductKitHtml(data,formId="addProductKitItems"){ 
    if(data.status==1){
        $('#'+formId)[0].reset();
        var postData = {'postData':{'item_id':$("#item_id").val()},'table_id':"productKit",'tbody_id':'kitItems','tfoot_id':'','fnget':'productKitHtml'};
        getTransHtml(postData);
    }else{
        if(typeof data.message === "object"){
            $(".error").html("");
            $.each( data.message, function( key, value ) {$("."+key).html(value);});
        }else{
            initTable();
            Swal.fire({ icon: 'error', title: data.message });
        }			
    }	
}

function getProductProcessHtml(data,formId="viewProductProcess"){ 
    if(data.status==1){
        $('#'+formId)[0].reset();
        var postData = {'postData':{'item_id':$("#item_id").val()},'table_id':"itemProcess",'tbody_id':'itemProcessData','tfoot_id':'','fnget':'productProcessHtml'};
        getTransHtml(postData);
    }else{
        if(typeof data.message === "object"){
            $(".error").html("");
            $.each( data.message, function( key, value ) {$("."+key).html(value);});
        }else{
            initTable();
            Swal.fire({ icon: 'error', title: data.message });
        }			
    }	
}

function getCycleTimeHtml(data,formId="cycleTime"){ 
    if(data.status==1){
        $('#'+formId)[0].reset();
        var postData = {'postData':{'item_id':$("#item_id").val()},'table_id':"ctTable",'tbody_id':'ctItems','tfoot_id':'','fnget':'cycleTimeHtml'};
        getTransHtml(postData);
    }else{
        if(typeof data.message === "object"){
            $(".error").html("");
            $.each( data.message, function( key, value ) {$("."+key).html(value);});
        }else{
            initTable();
            Swal.fire({ icon: 'error', title: data.message });
        }			
    }	
}

function getStandardsHtml(data,formId="standards"){ 
    if(data.status==1){
        $('#'+formId)[0].reset();
        var postData = {'postData':{'item_id':$("#item_id").val()},'table_id':"stTable",'tbody_id':'stItems','tfoot_id':'','fnget':'standardsHtml'};
        getTransHtml(postData);
    }else{
        if(typeof data.message === "object"){
            $(".error").html("");
            $.each( data.message, function( key, value ) {$("."+key).html(value);});
        }else{
            initTable();
            Swal.fire({ icon: 'error', title: data.message });
        }			
    }	
}
</script>