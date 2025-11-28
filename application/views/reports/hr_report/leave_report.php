<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="card-title text-center pageHeader"><?=$pageHeader?></h4>
                            </div>    
                        </div>  
                        <hr>
                        <div class="row"> 
                            <div class="col-md-2">
								<select name="unit_id" id="unit_id" class="form-control select2 req">
									<option value="">Select All Unit</option>
									<?php 
										foreach($companyList as $row):
											echo '<option value="'.$row->id.'">'.$row->company_name.'</option>';
										endforeach;
									?>
								</select>
							</div>
                            <div class="col-md-2">
								<select name="dept_id" id="dept_id" class="form-control select2 req">
									<option value="">Select All Department</option>
									<?php 
										foreach($deptList as $row):
											echo '<option value="'.$row->id.'">'.$row->name.'</option>';
										endforeach;
									?>
								</select>
							</div>   
                            <div class="col-md-3">
								<select name="emp_id" id="emp_id" class="form-control select2 req">
									<option value="">Select All Employee</option>
									<?php 
										foreach($empList as $row):
											echo '<option value="'.$row->id.'">['.$row->emp_code.'] '.$row->emp_name.'</option>';
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
                                        <button type="button" class="btn waves-effect waves-light btn-success float-right loaddata" title="Load Data">
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
                            <table id='reportTable' class="table table-bordered">
								<thead class="thead-info" id="theadData">
                                    <tr class="text-center">
                                        <th colspan="9">Leave Report</th>
                                    </tr>
									<tr>
										<th>#</th>
										<th>Unit</th>
										<th>Department</th>
										<th>Employee</th>
										<th>Leave Type</th>
										<th>From</th>
										<th>To</th>
										<th>Leave Days</th>
										<th>Reason</th>
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

    $(document).on('change','#dept_id',function(){
        var dept_id = $(this).val();
        var unit_id = $("#unit_id").val();
		if(dept_id){
			$.ajax({
				url : base_url + controller + '/getEmpList',
				type : 'post',
				data:{dept_id:dept_id, unit_id:unit_id},
				dataType:'json',
				success:function(data){
					$("#emp_id").html("");
					$("#emp_id").html(data.options);
					$("#emp_id").select2();
				}
			});
		}
    });

	reportTable();
    $(document).on('click','.loaddata',function(e){
		$(".error").html("");
		var valid = 1;
		var emp_id = $('#emp_id').val();
		var dept_id = $('#dept_id').val();
		var unit_id = $('#unit_id').val();
		var from_date = $('#from_date').val();
		var to_date = $('#to_date').val();
		if($("#from_date").val() == ""){$(".fromDate").html("From Date is required.");valid=0;}
		if($("#to_date").val() == ""){$(".toDate").html("To Date is required.");valid=0;}
		if($("#to_date").val() < $("#from_date").val()){$(".toDate").html("Invalid Date.");valid=0;}
		if(valid)
		{
            $.ajax({
                url: base_url + controller + '/getLeaveReportData',
                data: { emp_id:emp_id, dept_id:dept_id, unit_id:unit_id, from_date:from_date, to_date:to_date },
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
		scrollY: '55vh',
        scrollCollapse: true,
		"scrollX": true,
		"scrollCollapse":true,
		"autoWidth" : false,
		order:[],
		"columnDefs": 	[
							{ type: 'natural', targets: 0 },
							{ orderable: false, targets: "_all" }, 
							{ className: "text-left", targets: [0,2] }, 
							{ className: "text-center", "targets": "_all" } 
						],
		pageLength:25,
		language: { search: "" },
		lengthMenu: [
            [ 10, 25, 50, 100, -1 ],[ '10 rows', '25 rows', '50 rows', '100 rows', 'Show all' ]
        ],
		dom: "<'row'<'col-sm-7'B><'col-sm-5'f>>" +"<'row'<'col-sm-12't>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
		buttons: [ 'pageLength', 'excel', {text: 'Refresh',action: function ( e, dt, node, config ) { $(".loaddata").trigger('click'); }}]
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