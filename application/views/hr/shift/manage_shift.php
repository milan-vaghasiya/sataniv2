<?php $this->load->view('includes/header'); ?>
<!-- <form autocomplete="off" id="saveManageShift"> -->
	<div class="page-content-tab">
		<div class="container-fluid">
			<div class="row">
				<div class="col-sm-12">
					<div class="page-title-box">
						<div class="row">
							<div class="col-md-2">
								<h4 class="card-title">Manage Shift</h4>
							</div>
							<div class="col-md-10">
								<div class="input-group">
									<div class="input-group-append"  style="width:25%;margin-bottom:0px;">
										<select name="dept_id" id="dept_id" class="form-control select2 req">
											<option value="">All Department</option>
											<?php
												foreach($deptRows as $row):
													echo '<option value="'.$row->id.'">'.$row->name.'</option>';
												endforeach;
											?>
										</select>
									</div>
									<div class="input-group-append"  style="width:25%;margin-bottom:0px;">
										<select name="shift_id" id="shift_id" class="form-control select2 req">
											<option value="">All Shift</option>
											<?php
												foreach($shiftList as $row):
													echo '<option value="'.$row->id.'">'.$row->shift_name.'</option>';
												endforeach;
											?>
										</select>
									</div>
									<div class="input-group-append"  style="width:20%;margin-bottom:0px;">
										<select name="shift_status" id="shift_status" class="form-control select2 req" style="width:25%;margin-bottom:0px;">
											<option value="">All Employees</option>
											<option value="1">New Joined</option>
										</select>
									</div>
									<div class="input-group-append"  style="width:20%;margin-bottom:0px;">
										<input name="shift_date" id="shift_date" type="date" class="form-control" max="<?=date('Y-m-d')?>" value="<?=date('Y-m-d')?>"  />
									</div>
									<div class="input-group-append"  style="width:10%;margin-bottom:0px;">
										<button type="button" class="btn waves-effect btn-block waves-light btn-success loaddata" title="Load Data">
											<i class="fas fa-sync-alt"></i> Load
										</button>
										<div class="error shift_date"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-12">
					<div class="col-12">
						<div class="card">
							<div class="card-body">
								<div class="table-responsive">
									<div class="table-responsive">
										<table id="manageTable" class="table table-bordered jpDataTable colSearch">
											<thead class="thead-info" id="theadData">
												<tr>
													<th>Code</th>
													<th>Employee Name</th>
													<th>Department</th>
													<th>Designation</th>
													<th>Current Shift</th>
													<th>Change Shift</th>
													<th>Action</th>
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
		</div>
	</div>

<!-- </form> -->
<!--
<div class="bottomBtn bottom-25 right-25 permission-write">
    <button type="button" class="btn btn-primary btn-rounded font-bold permission-write save-form" style="letter-spacing:1px;" onclick="saveManageShift('saveManageShift');">SAVE SHIFT</button>
</div>-->
<?php $this->load->view('includes/footer'); ?>
<script>
$(document).ready(function(){
	reportTable("manageTable");
    $(document).on('click','.loaddata',function(e){
        $(".error").html("");
		var valid = 1;
        var dept_id = $('#dept_id').val();
        var shift_id = $('#shift_id').val();
        var shift_status = $('#shift_status').val();
        var shift_date = $('#shift_date').val();

		if($("#shift_date").val() == ""){$(".shift_date").html("Shift Date is required.");valid=0;}
        if(valid)
		{
            $.ajax({
                url: base_url + controller + '/getManageShift',
                data: {dept_id:dept_id,shift_id:shift_id,shift_status:shift_status,shift_date:shift_date},
                type: "POST",
                dataType:'json',
                success:function(data){
                    if(data.status===0){
                        $(".error").html("");
                        $.each( data.message, function( key, value ) {$("."+key).html(value);});
                    } else {
						$("#manageTable").dataTable().fnDestroy();
                        $("#tbodyData").html(data.tbodyData);
						reportTable("manageTable");
                    }
                }
            });
        }
    });
});

function saveManageShift(id)
{
	if(id)
	{
		var trans_id = $("#trans_id_"+id).val();
		var emp_id = $("#emp_id_"+id).val();
		var field_id = $("#field_id_"+id).val();
		var shift_date = $("#shift_date_"+id).val();
		var new_shift_id = $("#new_shift_id_"+id).val();
		$.ajax({
			url: base_url + controller + '/saveManageShift',
			data:{id:trans_id,emp_id:emp_id,field_id:field_id,new_shift_id:new_shift_id,shift_date:shift_date},
			type: "POST",
			dataType:"json",
		}).done(function(data){
			if(data.status===0){
				$(".error").html("");
				$.each( data.message, function( key, value ) {
					$("."+key).html(value);
				});
			}else if(data.status==1){
				$('.loaddata').trigger('click');
				Swal.fire({ icon: 'success', title: data.message});
			}else{
				$('.loaddata').trigger('click');
				Swal.fire({ icon: 'error', title: data.message });
			}		
		});
	}
	else
	{
		Swal.fire({ icon: 'error', title: "ID NOT FOUND" });
	}
}

function manageTable(tableId = 'manageTable') 
{
	var manageTable = $('#'+tableId).DataTable({
		responsive: true,
		"scrollXInner": true,
		"paging":   false,
		"fixedHeader": true,
		"autoWidth" : false,
		order: [],
		"columnDefs": [
		    {type: 'natural',targets: 0},
			{orderable: false,targets: "_all"},
			{className: "text-center",targets: [0, 1]},
			{className: "text-center","targets": "_all"}
		],
		//pageLength: 25,
		language: {search: ""},
		/*lengthMenu: [
			[ 10, 20, 25, 50, 75, 100, 250,500 ],
			[ '10 rows', '20 rows', '25 rows', '50 rows', '75 rows', '100 rows','250 rows','500 rows' ]
		],*/
		dom: "<'row'<'col-sm-7'B><'col-sm-5'f>>" + "<'row'<'col-sm-12't>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
		buttons: ['pageLength', 'excel'],
		"fnInitComplete":function(){$('.dataTables_scrollBody').perfectScrollbar();},
	    "fnDrawCallback": function( oSettings ) {$('.dataTables_scrollBody').perfectScrollbar('destroy').perfectScrollbar();}
	});
	manageTable.buttons().container().appendTo('#'+tableId+'_wrapper toolbar');
	$('.dataTables_filter .form-control-sm').css("width", "97%");
	$('.dataTables_filter .form-control-sm').attr("placeholder", "Search.....");
	$('.dataTables_filter').css("text-align", "left");
	$('.dataTables_filter label').css("display", "block");
	$('.btn-group>.btn:first-child').css("border-top-right-radius", "0");
	$('.btn-group>.btn:first-child').css("border-bottom-right-radius", "0");
	setTimeout(function(){ manageTable.columns.adjust().draw();}, 10);
	$('.page-wrapper').resizer(function() {manageTable.columns.adjust().draw(); });
	return manageTable;
}

//Datatable with column filter for jpTable Class
function manageTable1()
{
	var manageTable1 = $('#manageTable').DataTable( 
	{
		responsive: true,
        "paging":   false,
		//'stateSave':true,
		"autoWidth" : false,
		// order:[],
		"columnDefs": 	[
							{ type: 'natural', targets: 0 },
							// { orderable: false, targets: "_all" }, 
							{ className: "text-left", targets: [0,1] }, 
							{ className: "text-center", "targets": "_all" } 
						],
		//pageLength:25,
		language: { search: "" },
		// lengthMenu: [
        //     [ 10, 25, 50, 100, -1 ],[ '10 rows', '25 rows', '50 rows', '100 rows', 'Show all' ]
        // ],
		dom: "<'row'<'col-sm-12'B>>" +"<'row'<'col-sm-12't>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
		buttons: ['excel']
	});
	manageTable1.buttons().container().appendTo( '#manageTable_wrapper toolbar' );
	$('.dataTables_filter .form-control-sm').css("width","97%");
	$('.dataTables_filter .form-control-sm').attr("placeholder","Search.....");
	$('.dataTables_filter').css("text-align","left");
	$('.dataTables_filter label').css("display","block");
	$('.btn-group>.btn:first-child').css("border-top-right-radius","0");
	$('.btn-group>.btn:first-child').css("border-bottom-right-radius","0");
	
	//Datatable Column Filter
    $('.jdt thead tr:eq(0) th').each( function (i) {
		if($(this).index()!=0)
		{
			$( 'input', this ).on( 'keyup change', function () {
				if ( manageTable.column(i).search() !== this.value ) {manageTable.column(i).search( this.value ).draw();}
			});
		}else{$(this).html('');}
	} );
}

</script>