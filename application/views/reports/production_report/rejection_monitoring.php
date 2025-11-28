<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
				<div class="page-title-box">
					<!-- <div class="card-header"> -->
						<div class="row">
                            <div class="col-md-4">
                                <h4 class="card-title pageHeader"><?=$pageHeader?></h4>
                            </div>      
                            <div class="col-md-3">
								<select name="item_id" id="item_id" class="form-control select2">
                                    <option value="">Select Item</option>
                                    <?php
										foreach($itemList as $row):
											echo '<option value="'.$row->id.'">'.(!empty($row->item_code) ? '['.$row->item_code.'] ' : '') . $row->item_name.'</option>';
										endforeach;  
                                    ?>
                                </select>
							</div>
                            <div class="col-md-2">   
                                <input type="date" name="from_date" id="from_date" class="form-control" max="<?=date('Y-m-d')?>" value="<?=date('Y-m-d')?>" />
                                <div class="error fromDate"></div>
                            </div>     
                            <div class="col-md-3">  
                                <div class="input-group">
                                    <input type="date" name="to_date" id="to_date" class="form-control" value="<?=date('Y-m-d')?>" />
                                    <div class="input-group-append ml-2">
                                        <button type="button" class="btn waves-effect waves-light btn-success float-right loadData" title="Load Data">
									        <i class="fas fa-sync-alt"></i> Load
								        </button>
                                    </div>
                                </div>
                                <div class="error toDate"></div>
                            </div>                 
                        </div>  
                    <!-- </div> -->
				</div>
            </div>
		</div>
        <div class="row">
            <div class="col-12">
				<div class="col-12">
					<div class="card">
                        <div class="card-body reportDiv" style="min-height:75vh">
                            <div class="table-responsive">
                                <table id='reportTable' class="table table-bordered">
                                    <thead id="theadData" class="thead-dark">
                                        <tr class="text-center">
                                            <th colspan="14">REJECTION MONITORING  REPORT</th>
                                        </tr>
									
                                        <tr>
                                            <th style="min-width:50px;">#</th>
                                            <th style="min-width:100px;">Date</th>
                                            <th style="min-width:80px;">Part No</th>
                                            <th style="min-width:80px;">Setup No.</th>
                                            <th style="min-width:100px;">Shift</th>
                                            <th style="min-width:150px;">Machine No.</th>
                                            <th style="min-width:50px;">Operator Name</th>
                                            <th style="min-width:50px;">Jobcard</th>
                                            <th style="min-width:50px;">Rejection Qty.</th>
                                            <th style="min-width:50px;">Reason of Rejection</th>
                                            <th style="min-width:50px;">Rejection Remarks</th>
                                            <th style="min-width:50px;">Defect Belong To</th>
                                            <th style="min-width:50px;">Rejection From</th>
                                            <th style="min-width:50px;">Rejection Type</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyData"></tbody>
                                    <tfoot id="tfootData" class="thead-dark">
										<tr>
											<th colspan="8">Total</th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
											<th></th>
										</tr>
									</tfoot>
                                </table>
                            </div>
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
        var item_id = $("#item_id").val();
        var from_date = $('#from_date').val();
	    var to_date = $('#to_date').val();
        if($("#from_date").val() == ""){$(".fromDate").html("From Date is required.");valid=0;}
	    if($("#to_date").val() == ""){$(".toDate").html("To Date is required.");valid=0;}
	    if($("#to_date").val() < $("#from_date").val()){$(".toDate").html("Invalid Date.");valid=0;}

		if(valid){
            $.ajax({
                url: base_url + controller + '/getRejectionMonitoring',
                data: {item_id:item_id,from_date:from_date,to_date:to_date},
				type: "POST",
				dataType:'json',
				success:function(data){
                    $("#reportTable").DataTable().clear().destroy();
					$("#tbodyData").html(data.tbodyData);
					$("#tfootData").html(data.tfootData);
					reportTable();
                }
            });
        }
    });   
});
</script>