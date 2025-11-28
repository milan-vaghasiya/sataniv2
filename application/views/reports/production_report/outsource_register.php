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
                                <select name="vendor_id" id="vendor_id" class="form-control select2 float-right">
                                    <option value="">Select Vendor</option>
                                    <?php   
										foreach($vendorList as $row): 
											echo '<option value="'.$row->id.'">'.$row->party_name.'</option>';
										endforeach; 
                                    ?>
                                </select>
                                <div class="error vendor_id"></div>
							</div>
							<div class="col-md-2">   
                                <input type="date" name="from_date" id="from_date" class="form-control"  value="<?=date('Y-m-01')?>" />
                                
                                <div class="error fromDate"></div>
                            </div>     
                            <div class="col-md-3">  
                                <div class="input-group">
                                    <input type="date" name="to_date" id="to_date" class="form-control" value="<?=date('Y-m-d')?>" />
                                    <div class="input-group-append">
                                        <button type="button" class="btn waves-effect waves-light btn-success float-right loadData" title="Load Data">
									        <i class="fas fa-sync-alt"></i> Load
								        </button>
                                    </div>
                                </div>
                                <div class="error toDate"></div>
                            </div>     
                        </div>                                         
                    </div>
                    <div class="card-body reportDiv" style="min-height:75vh">
                        <div class="table-responsive">
                            <table id='reportTable' class="table table-bordered" >
								<thead id="theadData" class="thead-dark">
									<tr>
										<th colspan="8" style="text-align: center;">Outward Details</th>
										<th colspan="5" style="text-align: center;">Inward Details</th>
									</tr>
									<tr class="text-center">
										<th style="min-width:50px;">#</th>
										<th style="min-width:50px;">Challan No.</th>
										<th style="min-width:100px;">Challan Date</th>
										<th style="min-width:50px;">PRC No.</th>
										<th style="min-width:100px;">Vendor</th>
										<th style="min-width:100px;">Product</th>
										<th style="min-width:180px;">Process</th>
										<th style="min-width:80px;">Qty.</th>
										
										<th style="min-width:100px;">Date</th>
										<th style="min-width:100px;">Vendor</th>
										<th style="min-width:50px;">Vendor Challan No.</th>
										<th style="min-width:80px;">Qty.</th>
										<th style="min-width:80px;">Balance Qty.</th>
									</tr>
								</thead>
								<tbody id="tbodyData"></tbody>
								<tfoot id="tfootData" class="thead-dark">
								    <th colspan="7" class="text-right">Total</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
								</tfoot>
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
		$('#vendor_name').text($('#vendor_idc').val());
		var vendor_id = $('#vendor_id').val();
		var from_date = $('#from_date').val();
		var to_date = $('#to_date').val();
		var valid = 1;
		if($("#vendor_id").val() == ""){$(".vendor_id").html("Vendor is required.");valid=0;}
		if($("#from_date").val() == ""){$(".fromDate").html("From Date is required.");valid=0;}
		if($("#to_date").val() == ""){$(".toDate").html("To Date is required.");valid=0;}
		if($("#to_date").val() < $("#from_date").val()){$(".toDate").html("Invalid Date.");valid=0;}
		if(valid)
		{
			$.ajax({
				url: base_url + controller + '/getOutSourceRegister',
				data: {vendor_id:vendor_id,from_date:from_date,to_date:to_date},
				type: "POST",
				dataType:'json',
				success:function(data){
                    $("#reportTable").DataTable().clear().destroy();
					$("#tbodyData").html(data.tblData);
					$("#tfootData").html(data.tfoot);
					reportTable();
				}
			});
		}
	});
});

</script>