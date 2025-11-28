<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">                       
						<form id="printMultiTags" action="<?=base_url($headData->controller.'/printCanteenIcard')?>" method="POST"  target="_blank">
							<div class="row">
								<div class="col-md-2">
									<h4 class="card-title pageHeader"><?=$pageHeader?></h4>
								</div>  
								<div class="col-md-3 form-group">
									<select name="emp_unit_id" id="emp_unit_id" class="form-control select2 getEmp">
										<option value="">Select All Unit</option>
										<?php
											foreach($cmList as $row):
												echo '<option value="'.$row->id.'" >'.$row->company_name.'</option>';
											endforeach;
										?>
									</select>
								</div> 
								<div class="col-md-2 form-group">
									<select name="emp_dept_id" id="emp_dept_id" class="form-control select2 getEmp">
										<option value="">Select All Department</option>
										<?php
											foreach($deptList as $row):
												echo '<option value="'.$row->id.'" >'.$row->name.'</option>';
											endforeach;
										?>
									</select>
								</div>
								<div class="col-md-5 form-group">
									<div class="input-group">
										<div class="input-group-append" style="width:65%">
											<select name="emp_id" id="emp_id" class="form-control select2" >
												<option value="">Select All Employee</option>
												<?php
													foreach($empList as $row):
														echo '<option value="'.$row->id.'">['.$row->emp_code.'] '.$row->emp_name.'</option>';
													endforeach;
												?>
											</select>
										</div>
										<div class="input-group-append">
											<button type="button" class="btn waves-effect waves-light btn-success float-left loaddata mr-2" title="Load Data"><i class="fas fa-sync-alt"></i> Load</button>
										
											<button type="submit" class="btn waves-effect waves-light btn-facebook float-right iCard" datatip="Generate QR Code" flow="down" ><i class="fas fa-address-card"></i> Generate Icard</button>
										</div>
									</div>
								</div>   
							</div>  
						</form>                               
                    </div>
                    <div class="card-body reportDiv" style="min-height:75vh">
                        <div class="table-responsive">
                            <table id='reportTable' class="table table-bordered">
								<thead class="thead-info" id="theadData">
                                    <tr class="text-center">
                                        <th colspan="13">Employee Report</th>
                                        <th colspan="10">Doc. No.: D HR 01</th>
                                        <th colspan="10">Rev. No. & Dt.: 00/01-06-20</th>
                                    </tr>
									<tr>
										<th>#</th>
										<th>Name of Employee</th>
										<th>Employee Code</th>
										<th>Alias Name</th>
										<th>Phone No.</th>
										<th>Alternate Phone</th>
										<th>Gender</th>
										<th>Marital Status</th>
										<th>Date Of Birth</th>
										<th>Emp. Email</th>
										<th>Mark Of Identification</th>
										<th>Address</th>
										<th>Permanent Address</th>
										<th>Father Name</th>
										<th>Department</th>
										<th>Designation</th>
										<th>System Designation</th>
										<th>Emp. Category</th>
										<th>Emp. Type</th>
										<th>Emp. Class</th>
										<th>Education</th>
										<th>Experience</th>
										<th>PF Applicable</th>
										<th>PF Number</th>
										<th>UAN Number</th>
										<th>Payment Mode </th>
										<th>Bank Name</th>
										<th>Account No.</th>
										<th>IFSC Code</th>
										<th>Joining Date</th>
										<th>Resign Date</th>
										<th>Unit</th>
										<th>Unit (Payroll)</th>
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
	$(document).on('change',".getEmp",function(){
		var emp_dept_id = $("#emp_dept_id").val();
		var emp_unit_id = $("#emp_unit_id").val();
		$.ajax({ 
            type: "POST",   
            url: base_url + controller + '/getEmpListByDept',   
            data: {emp_dept_id:emp_dept_id, emp_unit_id:emp_unit_id},
			dataType:"json",
        }).done(function(response){
			$('#emp_id').html("");
			$('#emp_id').html(response.options);
			$('#emp_id').select2();
        });
    });

	reportTable();
	$(document).on('click','.loaddata',function(e){
		$(".error").html("");
		var valid = 1;
		var emp_dept_id = $('#emp_dept_id').val();
		var emp_id = $('#emp_id').val();
		var emp_unit_id = $('#emp_unit_id').val();
		if(valid)
		{
            $.ajax({
                url: base_url + controller + '/getEmpReport',
                data: {emp_dept_id:emp_dept_id, emp_id:emp_id, emp_unit_id:emp_unit_id},
				type: "POST",
				dataType:'json',
				success:function(data){
                    $("#reportTable").dataTable().fnDestroy();
					$("#tbodyData").html(data.tbody);
					reportTable();
                }
            });
        }
    });   
});
function reportTable()
{
	var reportTable = $('#reportTable').DataTable( 
	{
		responsive: true,
		//'stateSave':true,
		"autoWidth" : false,
		order:[],
		"columnDefs": 	[
							{ type: 'natural', targets: 0 },
							{ orderable: false, targets: "_all" }, 
							{ className: "text-left", targets: [0,1] }, 
							{ className: "text-center", "targets": "_all" } 
						],
		pageLength:25,
		language: { search: "" },
		lengthMenu: [
            [ 10, 25, 50, 100, -1 ],[ '10 rows', '25 rows', '50 rows', '100 rows', 'Show all' ]
        ],
		dom: "<'row'<'col-sm-7'B><'col-sm-5'f>>" +"<'row'<'col-sm-12't>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
		buttons: [ 'pageLength', 'excel', {text: 'Refresh',action: function ( e, dt, node, config ) {loadAttendanceSheet();}}]
	});
	reportTable.buttons().container().appendTo( '#reportTable_wrapper toolbar' );
	$('.dataTables_filter .form-control-sm').css("width","97%");
	$('.dataTables_filter .form-control-sm').attr("placeholder","Search.....");
	$('.dataTables_filter').css("text-align","left");
	$('.dataTables_filter label').css("display","block");
	$('.btn-group>.btn:first-child').css("border-top-right-radius","0");
	$('.btn-group>.btn:first-child').css("border-bottom-right-radius","0");
	return reportTable;
}
</script>