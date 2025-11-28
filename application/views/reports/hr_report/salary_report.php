<?php 
	$this->load->view('includes/header'); 
	$today = new DateTime();
	$today->modify('first day of this month');$first_day = date('Y-m-d');
	$today->modify('last day of this month');$last_day = date("t",strtotime($today->format('Y-m-d')));
	$monthArr = ['Apr-'.$start_year=>'01-04-'.$start_year,'May-'.$start_year=>'01-05-'.$start_year,
	'Jun-'.$start_year=>'01-06-'.$start_year,'Jul-'.$start_year=>'01-07-'.$start_year,'Aug-'.$start_year=>'01-08-'.$start_year,'Sep-'.$start_year=>'01-09-'.$start_year,'Oct-'.$start_year=>'01-10-'.$start_year,'Nov-'.$start_year=>'01-11-'.$start_year,'Dec-'.$start_year=>'01-12-'.$start_year,'Jan-'.$end_year=>'01-01-'.$end_year,'Feb-'.$end_year=>'01-02-'.$end_year,'Mar-'.$end_year=>'01-03-'.$end_year];	
?>
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
							<div class="col-md-4 form-group">
								<select name="emp_unit_id" id="emp_unit_id" class="form-control single-select getEmp">
									<option value="">Select All Company</option>
									<?php
										foreach($cmList as $row):
											echo '<option value="'.$row->id.'" >'.$row->company_name.'</option>';
										endforeach;
									?>
								</select>
							</div> 
							<div class="col-md-4 form-group">
								<div class="input-group">
									<select name="month" id="month" class="form-control single-select" style="width:55%">
										<?php
											foreach($monthArr as $key=>$value):
												$selected = (date('m') == $value)?"selected":"";
												echo '<option value="'.$value.'" '.$selected.'>'.$key.'</option>';
											endforeach;
										?>
									</select>

									<button type="button" class="btn waves-effect waves-light btn-success float-right loaddata mr-2" title="Load Data"><i class="fas fa-sync-alt"></i> Load</button>	
								</div>
							</div>   
						</div>                           
					</div>
                    <div class="card-body reportDiv" style="min-height:75vh">
                        <div class="table-responsive">
                            <table id='reportTable' class="table table-bordered">
								<thead class="thead-info" id="theadData">
                                    <tr class="text-center">
                                        <th colspan="12">Employee Salary Details</th>
                                    </tr>
									<tr>
										<th>#</th>
										<th>Employee Name</th>
										<th>Employee Code</th>
										<th>Department</th>
										<th>Rate/Hour</th>
										<th>Monthly Hours</th>
										<th>Monthly Salary</th>
										<th>Actual Hours</th>
										<th>Actual Salary</th>
										<th>Increment Date</th>
										<th>Last Increment</th>
										<th>Remark</th>
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
	$(document).on('click','.loaddata',function(e){
		$(".error").html("");
		var valid = 1;
		var emp_unit_id = $('#emp_unit_id').val();
		var month = $("#month").val();
		if(valid)
		{
            $.ajax({
                url: base_url + controller + '/getEmpSalaryDetails',
                data: {emp_unit_id:emp_unit_id,month:month},
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