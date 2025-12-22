<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
    <div class="container-fluid bg-container">
        <div class="row">
            <div class="col-12">
			    <div class="page-title-box">
					<div class="row">
						<div class="col-md-12 mb-3">
							<h4 class="card-title pageHeader"><?=$pageHeader?></h4>
						</div>      
						<div class="col-md-2">
							<select name="item_id" id="item_id" class="form-control select2">
								<option value="">All Items</option>
								<?php
									if(!empty($itemList)){
										foreach($itemList as $row){
											echo '<option value="'.$row->id.'">'.(!empty($row->item_code) ? '['.$row->item_code.'] ' : '') . $row->item_name.'</option>';
										}
									}
								?>
							</select>
						</div>
						<div class="col-md-2">
							<select name="employee_id" id="employee_id" class="form-control select2">
								<option value="">All Employee</option>
								<?php
									if(!empty($empData)){
										foreach ($empData as $row) {
											echo "<option value='".$row->id."'>".$row->emp_name."</option>";
										}
									}
								?>
							</select>
						</div>
						<div class="col-md-2 form-group">
							<select name="unit_id" id="unit_id" class="form-control select2">
								<option value="0">All Unit</option>
								<?php
									foreach($companyList as $row){
										echo '<option value="'.$row->id.'" '.$selected.' '.$disabled.'>'.$row->company_name.'</option>';
									}
								?>
							</select>
						</div>
						<div class="col-md-2 form-group">
							<select name="machine_id" id="machine_id" class="form-control select2">
							<option value="">All Machine</option>
								<?php 
									foreach ($machineList as $row){
										echo '<option value="' . $row->id . '">' . (!empty($row->item_code) ? '['.$row->item_code.'] ' : ''). $row->item_name . '</option>';
									}
								?>
							</select>
						</div>  
						<div class="col-md-2">   
							<input type="date" name="from_date" id="from_date" class="form-control" value="<?=$startDate?>">
							<div class="error fromDate"></div>
						</div>     
						<div class="col-md-2">  
							<div class="input-group">
								<input type="date" name="to_date" id="to_date" class="form-control" value="<?=$endDate?>">
								<div class="input-group-append ml-2">
									<button type="button" class="btn waves-effect waves-light btn-success float-right refreshReportData loadData" title="Load Data">
										<i class="fas fa-sync-alt"></i> Load
									</button>
								</div>
							</div>
							<div class="error toDate"></div>
						</div>                 
					</div>  
				</div>
				<div class="card">
                    <div class="card-body reportDiv" style="min-height:75vh">
                        <div class="table-responsive">
                            <table id='reportTable' class="table table-bordered">
								<thead class="thead-dark" id="theadData">
									<tr>
										<th>#</th>
										<th>Issue Number</th>
										<th>Issue Date</th>
										<th>Item Name</th>
										<th>Issue Qty</th>
										<th>Heat No</th>
										<th>Unit</th>
										<th>Machine</th>
										<th>Issued To</th>
									</tr>
								</thead>
								<tbody id="tbodyData"></tbody>
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
		var valid = 1;
		$(".error").html("");

		var item_id = $("#item_id").val();
		var employee_id = $("#employee_id").val();
		var unit_id = $("#unit_id").val();
		var machine_id = $("#machine_id").val();
        var from_date = $('#from_date').val();
	    var to_date = $('#to_date').val();

        if($("#from_date").val() == ""){$(".fromDate").html("From Date is required.");valid=0;}
	    if($("#to_date").val() == ""){$(".toDate").html("To Date is required.");valid=0;}
	    if($("#to_date").val() < $("#from_date").val()){$(".toDate").html("Invalid Date.");valid=0;}	

		if(valid)
		{
			$.ajax({
				url: base_url + controller + '/getIssueRegister',
                data: {item_id:item_id, employee_id:employee_id, unit_id:unit_id, machine_id:machine_id, from_date:from_date, to_date:to_date},
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