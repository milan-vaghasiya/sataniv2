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
                            <div class="col-md-3">
                                <select id="report_type" class="form-control select2">
                                    <option value="1">Batch Wise</option>
                                    <option value="2">Item Wise</option>
                                    
                                </select>
                            </div>
                            <div class="col-md-5 float-right">  
                                <div class="input-group">
                                    <div class="input-group-append" style="width:80%;">
                                        <select id="item_id" class="form-control select2">
                                            <option value="">Select Item</option>
                                            <?php
                                            if(!empty($itemList)){
                                                foreach($itemList as $row){
                                                    ?>
                                                    <option value="<?=$row->id?>"><?=((!empty($row->item_code))?'['.$row->item_code.'] ':'').$row->item_name?></option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="input-group-append" style="width:20%;">
                                        <button type="button" class="btn btn-block waves-effect waves-light btn-success refreshReportData loadData" title="Load Data">
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
								    <tr>
                                        <th rowspan="2">#</th>
                                        <th rowspan="2">Item</th>
                                        <th rowspan="2">Batch No</th>
                                        <th rowspan="2">Batch Qty</th>
                                        <th colspan="4">Forging Process</th>
                                        <th colspan="4">Machining Process</th>
                                        <th colspan="4">Other Process</th>
                                    </tr>
                                    <tr>
                                        <th>In Transit</th>
                                        <th>Pending</th>
                                        <th>Stock</th>
                                        <th>Rejection</th>
                                        
                                        <th>In Transit</th>
                                        <th>Pending</th>
                                        <th>Stock</th>
                                        <th>Rejection</th>
                                        
                                        <th>In Transit</th>
                                        <th>Pending</th>
                                        <th>Stock</th>
                                        <th>Rejection</th>
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
    
    
    $(document).on('click','.loadData',function(e){
		$(".error").html("");
		var valid = 1;
		var item_id = $('#item_id').val();
		var report_type = $('#report_type').val();
		
		if(valid){
            $.ajax({
                url: base_url + controller + '/getProductionReviewData',
                data: {item_id:item_id,report_type:report_type},
				type: "POST",
				dataType:'json',
				success:function(data){
                    $("#reportTable").DataTable().clear().destroy();
					$("#theadData").html(data.thead);
					$("#tbodyData").html(data.tbody);
					$("#tfootData").html(data.tfoot);
					reportTable();
                }
            });
        }
    });  

});


</script>