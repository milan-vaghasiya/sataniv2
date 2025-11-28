<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-4">
                                <h4 class="card-title pageHeader"><?=$pageHeader?></h4>
                            </div>       
                            <div class="col-md-8 float-right">  
                                <div class="input-group float-right d-flex justify-content-end">
                                	<div class="input-group-append" style="width:30%;">
                                        <select id="item_type" class="form-control select2">
                                        	<option value="">Select All</option>
                                            <?php
                                                foreach($this->itemTypes as $type=>$typeName):
                                                    echo '<option value="'.$type.'">'.$typeName.'</option>';
                                                endforeach;
                                            ?>
                                        </select>
                                    </div>
                                    <div class="input-group-append" style="width:30%;">
                                        <select id="item_id" class="form-control select2">
                                            <option value="">Select Item</option>
                                        </select>
                                    </div>
                                    <div class="input-group-append" style="width:30%;">
                                        <select id="location_id" class="form-control select2">
                                        	 <option value="">Seclect location List</option>
                                            <?=getLocationListOption($locationList)?>
                                        </select>
                                    </div>
                                    <div class="input-group-append">
                                        <button type="button" class="btn waves-effect waves-light btn-success refreshReportData loadData" title="Load Data">
									        <i class="fas fa-sync-alt"></i> Load
								        </button>
                                    </div>
                                </div>
                                <div class="error stock_type"></div>
                            </div>                  
                        </div>                                         
                    </div>
                    <div class="card-body reportDiv" style="min-height:75vh">
                        <div class="table-responsive">
                            <table id='reportTable' class="table table-bordered">
								<thead class="thead-dark" id="theadData">
                                    <tr class="text-center">
                                        <th colspan="6">LOCATION WISE STOCK</th>
                                    </tr>
									<tr>
										<th class="text-center">#</th>
										<th class="text-left">Item Description</th>
										<th class="text-left">Location</th>
										<th class="text-left">Heat No.</th>
										<th class="text-left">Batch No.</th>
										<th class="text-left">Qty</th>
									</tr>
								</thead>
								<tbody id="tbodyData"></tbody>
								<tfoot id="tfootData"></tfoot>
							</table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>


<?php $this->load->view('includes/footer'); ?>
<script>
$(document).ready(function(){
	reportTable();
    setTimeout(function(){$(".loadData").trigger('click');},500);
    
    $(document).on('click','.loadData',function(e){
		$(".error").html("");
		var valid = 1;
		var item_id = $('#item_id').val();
		var item_type = $('#item_type').val();
		var location_id = $('#location_id').val();
		if(valid){
            $.ajax({
                url: base_url + controller + '/getLocationWiseStock',
                data: {location_id:location_id,item_type:item_type,item_id:item_id},
				type: "POST",
				dataType:'json',
				success:function(data){
                    $("#reportTable").DataTable().clear().destroy();
					$("#tbodyData").html(data.tbody);
					$("#tfootData").html(data.tfoot);
					reportTable();
                }
            });
        }
    });
    $(document).on('change','#item_type',function(e){
		$(".error").html("");
		var valid = 1;
		var item_type = $('#item_type').val();
		if(valid){
            $.ajax({
                url: base_url + controller + '/getItemType',
                data: {item_type:item_type},
				type: "POST",
				dataType:'json',
				success:function(data){
                    $("#reportTable").DataTable().clear().destroy();
					$("#item_id").html(data.tbody);
					reportTable();
                }
            });
        }
    });  
});


</script>