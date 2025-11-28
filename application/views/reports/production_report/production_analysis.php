<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
				<div class="page-title-box">
					<div class="float-end" style="width:60%;">
					    <div class="input-group">
                            <div class="input-group-append" style="width:40%;">
                                <select id="item_id" class="form-control select2">
                                    <option value="">All Item</option>
                                    <?=getItemListOption($itemList)?>
                                </select>
                            </div>
                            <input type="date" name="from_date" id="from_date" class="form-control" value="<?=$startDate?>"  style="width:20%;"/>                                    
                            <input type="date" name="to_date" id="to_date" class="form-control" value="<?=$endDate?>"  style="width:20%;"/>
                            <div class="input-group-append">
                                <button type="button" class="btn waves-effect waves-light btn-success float-right refreshReportData loadData" title="Load Data">
                                    <i class="fas fa-sync-alt"></i> Load
                                </button>
                            </div>
                        </div>
                        <div class="error fromDate"></div>
                        <div class="error toDate"></div>
					</div>
                    <h4 class="card-title pageHeader"><?=$pageHeader?></h4>
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
                                            <th rowspan="2">#</th>
                                            <th rowspan="2">Date</th>
                                            <th rowspan="2">Item Name</th>
                                            <th rowspan="2">Process</th>
                                            <th rowspan="2">Machine</th>
                                            <th rowspan="2">Cycle Time <small>(In Sec.)</small> <br> (A)</th>
                                            <th rowspan="2">Working Hours <br> (B)</th>
                                            <th rowspan="2">Breakdown Time <br> (C)</th>
                                            <th colspan="2">Ideal</th>
                                            <th colspan="2">Actual</th>
                                            <th colspan="2">Lost</th>
                                            <th rowspan="2">Total Lost <br> (J) = (I + (C * D))</th>
                                        </tr>
                                        <tr>
                                            <th>Qty Per Hours <br> (D) = (3600 / A)</th>
                                            <th>Total Qty <br> (E) = (B * D)</th>
                                            <th>Qty Per Hours <br> (F) = (G / B)</th>
                                            <th>Total Qty <br> (G)</th>
                                            <th>Qty Per Hours <br> (H) = (D - F)</th>
                                            <th>Total Qty <br> (I) = (E - G)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyData"></tbody>
                                    <tfoot id="tfootData" class="thead-dark">
                                        <th colspan="8" class="text-right">Total</th>
                                        <th></th>
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
                url: base_url + controller + '/getProductionAnalysisData',
                data: {item_id:item_id,from_date:from_date,to_date:to_date},
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
});
</script>