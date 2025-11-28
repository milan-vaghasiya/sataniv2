<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-7">
                                <h4 class="card-title pageHeader"><?=$pageHeader?></h4>
                            </div>  
							
                            <div class="col-md-5 col-lg-5">  
                                <div class="row">
                                    <div class="col-md-6 col-lg-6 mb-1">
                                        <select class="form-control single-select select2 req" name="process_id" id="process_id">
                                            <option value="">All Set up</option>
                                            <?php 
                                                if(!empty($processList)){
                                                    foreach($processList as $row){
                                                        echo '<option value="'.$row->id.'">'.$row->process_name.'</option>';
                                                    }
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 col-lg-6 mb-1">
                                        <div class="input-group">
                                            <input type="date" name="from_date" id="from_date" class="form-control" value="<?=$startDate?>">
                                            <input type="date" name="to_date" id="to_date" class="form-control" value="<?=$endDate?>">
                                        
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
                        </div>                                         
                    </div>
                    <div class="card-body reportDiv" style="min-height:75vh">
                        <div class="table-responsive">
                            <table id='reportTable' class="table table-bordered" >
								<thead id="theadData" class="thead-dark">
									<tr class="text-center">
                                        <th>#</th>
                                        <th>Entry Date</th>
                                        <th>Operator Name</th>
                                        <th>M/C NO.</th>
                                        <th>Shift</th>
                                        <th>Product Name</th>
                                        <th>PRC No.</th>
                                        <th>Set up</th>
                                        <th>Cycle time<br>(Sec.)</th>
                                        <th>Total time<br>(Min.)</th>
                                        <th>Qty</th>
                                        <th>Rejection qty.</th>
                                        <th>Rejection reason</th>
									</tr>
								</thead>
								<tbody id="tbodyData"></tbody>
								<tfoot id="tfootData" class="thead-dark">
								    <th colspan="10" class="text-right">Total</th>
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
    setTimeout(function(){$(".loadData").trigger('click');},500);

	$(document).on('click','.loadData',function(e){
		var process_id = $('#process_id').val();
		var from_date = $('#from_date').val();
		var to_date = $('#to_date').val();
		var valid = 1;
		if($("#to_date").val() == ""){$(".toDate").html("To Date is required.");valid=0;}
		if(valid)
		{
			$.ajax({
				url: base_url + controller + '/getProductionLogSheet',
				data: {process_id:process_id,from_date:from_date,to_date:to_date},
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

        $("#from_date").on("change", function () {
            let fromDate = $(this).val();
            $("#to_date").attr("min", fromDate);

            let toDate = $("#to_date").val();
            if (toDate && toDate < fromDate) {
                $("#to_date").val(fromDate); 
            }
        }).trigger('change');
	});
});
</script>