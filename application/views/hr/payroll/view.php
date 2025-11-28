<?php $this->load->view('includes/header'); ?>
<style>
	.countSalary{width:100px;}
</style>
<div class="page-content-tab">
    <div class="container-fluid bg-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h4><u>Payroll View</u></h4>
                    </div>
					<div class="card-body">
                        <form autocomplete="off" id="savePayRoll">
                            <div class="row">
								<div class="col-md-3 form-group">
									<label for="cm_id">UNIT</label>
									<select name="cm_id" id="cm_id" class="form-control select2 req">
										<?php
											foreach($companyList as $row):
												$selected = (!empty($dataRow->cm_id) && $row->id == $dataRow->cm_id)?"selected":"";
												echo '<option value="'.$row->id.'" '.$selected.'>'.$row->company_name.'</option>';
											endforeach;
										?>
									</select>
								</div>
                                <div class="col-md-3 form-group">
                                    <label for="dept_id">Department</label>
                                    <select name="dept_id" id="dept_id" class="form-control select2 req">
                                        <option value="0">ALL Department</option>
                                        <?php
                                            foreach($deptRows as $row):
                                                $selected = (!empty($salaryData) && $salaryData[0]->dept_id == $row->id)?"selected":"";
                                                echo '<option value="'.$row->id.'" '.$selected.'>'.$row->name.'</option>';
                                            endforeach;
                                        ?>
                                    </select>
                                    <div class="error dept_id"></div>
                                </div>
                                <div class="col-md-2 form-group">
                                    <label for="month">Month</label>
                                    <select name="month" id="month" class="form-control select2 req">
                                        <option value="">Select Month</option>
                                        <?php
                                            foreach($monthList as $row):
                                                $selected = (!empty($salaryData) && $salaryData[0]->month == $row)?"selected":((!empty($month) && $row == $month)?"selected":"");
                                                echo '<option value="'.$row.'" '.$selected.'>'.date("F-Y",strtotime($row)).'</option>';
                                            endforeach;
                                        ?>
                                    </select>
                                    <div class="error month"></div>
                                </div>
                                <div class="col-md-2 form-group">
                                    <label for="ledger_id">Select Ledger</label>
                                    <select name="ledger_id" id="ledger_id" class="form-control select2 req" tabindex="-1">
                                        <option value="1" selected>CASH IN HAND</option>
                                    </select>
                                    <div class="error ledger_id"></div>
                                </div>
                                <div class="col-md-2 form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn waves-effect waves-light btn-success btn-block loadSalaryData"  > Load</button>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <div class="row form-group">
                                        <div class="table-responsive ">
                                            <table id="empSalary" class="table table-striped jpExcelTable jpDataTable ">
                                                <thead class="thead-info" id="empSalaryHead">
                                                    <tr class="text-center">
														<th>Emp Code</th>
														<th>Emp Name</th>
														<th>Hours</th>
														<th>Wages/Hour</th>
														<th>Total<br>Earning</th>
														<th>Other<br>Allowances</th>
														<th>Gross Salary</th>
														<th>Advance</th>
														<th>Canteen</th>
														<th>PT</th>
														<th>PF</th>
														<th>Loan EMI</th>
														<th>Other<br>Deduction</th>
														<th>Gross<br>Deduction</th>
														<th>Net Salary</th>
														<!--<th>Action</th>-->
													</tr>
                                                </thead>
                                                <tbody id="empSalaryData"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="hidden_inputs"></div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <div class="col-md-2 float-right form-group">
                            <a href="<?= base_url($headData->controller) ?>" class="btn waves-effect waves-light btn-outline-secondary float-right save-form" style="margin-right:10px;"><i class="fa fa-arrow-left"></i> Go Back</a>
                            <!--<button type="button" class=" btn waves-effect waves-light btn-outline-success btn-block save-form" onclick="savePayRoll('savePayRoll');" ><i class="fa fa-check"></i> Save</button>-->
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
    viewDataTable("empSalary");
    $(document).on('click','.loadSalaryData',function(){
       
        var dept_id = $("#dept_id :selected").val() || 0;
        var month = $("#month :selected").val();
        var cm_id = $("#cm_id :selected").val();
        var valid = 1;

        if(dept_id == ""){ $(".dept_id").html("Department is required."); valid = 0; }
        if(cm_id == ""){ $(".cm_id").html("Unit is required."); valid = 0; }
        if(month == ""){ $(".month").html("Month is required."); valid = 0; }

        if(valid == 1){
            $.ajax({
                url:base_url + controller + '/getEmployeeSalaryData',
                type: 'post',
                data : {dept_id:dept_id,cm_id:cm_id, month:month,view:0},
                dataType:'json',
                success:function(data){
                    $('#empSalary').DataTable().clear().destroy();
                    $("#empSalaryHead").html("");
                    $("#empSalaryHead").html(data.emp_salary_head);
                    $("#empSalaryData").html("");
                    $("#empSalaryData").html(data.emp_salary_html); 
                    $(".hidden_inputs").html(data.hidden_inputs); 
                    $(".sal_sav_btn").html(data.save_button); 
                    viewDataTable("empSalary");
                }
            });
        }
    });
});

function exportData(file_type="pdf"){
    var dept_id = $("#dept_id :selected").val();
    var month = $("#month :selected").val();
    var valid = 1;

    if(dept_id == ""){ $(".dept_id").html("Department is required."); valid = 0; }
    if(month == ""){ $(".month").html("Month is required."); valid = 0; }
    
    if(valid == 1){
        if(file_type == 'excel2'){
            window.open(base_url + controller + '/getEmployeeActualSalaryData/' + dept_id + '/' + month + '/' + file_type, '_blank').focus();
        }else{
            window.open(base_url + controller + '/getEmployeeSalaryData/' + dept_id + '/' + month + '/' + file_type, '_blank').focus();
        }
    }
}

function viewDataTable(tableId){
	var table = $('#'+tableId).DataTable( {
		lengthChange: false,
		responsive: true,
		'stateSave':false,
		retrieve: true,
		buttons: [ 'pageLength', {text: 'PDF',action: function ( e, dt, node, config ) {exportData('pdf');}}, {text: 'Excel 1',action: function ( e, dt, node, config ) {exportData('excel');}},{text: 'Excel 2',action: function ( e, dt, node, config ) {exportData('excel2');}}]
	});
	table.buttons().container().appendTo( '#'+tableId+'_wrapper .col-md-6:eq(0)' );
	return table;
};
</script>
