<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-3">
                                <h4 class="card-title">Approve OT</h4>
                            </div>
                            <div class="col-md-9">
                                <div class="input-group">
									<div class="input-group-append" style="width:30%;margin-bottom:0px;">
										<select name="emp_unit_id" id="emp_unit_id" class="form-control select2" >
											<option value="">Select All Company</option>
											<?php
												foreach($cmList as $row):
													echo '<option value="'.$row->id.'" >'.$row->company_name.'</option>';
												endforeach;
											?>
										</select>
									</div>
									<div class="input-group-append" style="width:20%;margin-bottom:0px;">
									
										<select name="ot_type" id="ot_type" class="form-control select2">
											<option value="">Select All</option>
											<option value="1">Pending</option>
											<option value="2">Approved</option>
										</select>
									</div>
									<div class="input-group-append" style="width:20%;margin-bottom:0px;">
										<select name="ot_filter" id="ot_filter" class="form-control select2" >
											<option value="">Select All</option>
											<option value="300">Less Than 5</option>
											<option value="600">Less Than 10</option>
											<option value="900">Less Than 15</option>
											<!--<option value="1200">Less Than 20</option>
											<option value="1500">Less Than 25</option>-->
											<option value="1800">Less Than 30</option>
											<!--<option value="-1">Above 30</option>-->
										</select>
									</div>
									<div class="input-group-append" style="width:20%;margin-bottom:0px;">
									
                                    	<input type="date" name="attendance_date" id="attendance_date" class="form-control" max="<?=date("Y-m-d")?>" value="<?=date("Y-m-d")?>" >
									</div>
                                    <div class="input-group-append"  style="width:10%;margin-bottom:0px;">
                                        <button type="button" class="btn btn-block waves-effect waves-light btn-success float-right loadData" title="Load Data"	>
									        <i class="fas fa-sync-alt"></i> Load
								        </button>
                                    </div>
                                </div>
                            </div>
						</div>                                         
                    </div>
                    <div class="card-body" style="min-height:50vh;">
						<form id="bulkOtForm">
							<div class="table-responsive">
								<table id="empOtTable" class="table table-bordered jpDataTable">
									<thead class="thead-info">
										<tr>
											<th>Action</th>
											<th>#</th>
											<th><input type="checkbox" id="masterSelect" class="filled-in chk-col-success BulkRequest" value=""><label for="masterSelect">ALL</label></th>
											<th>Emp Code</th>
											<th>Emp Name</th>
											<!--<th>Department</th>-->
											<th>Shift</th>
											<th>Date</th>
											<!--<th>Day</th>
											<th>Status</th>
											<th>WH</th>
											<th>Lunch</th>
											<th>Ex. Hrs</th>
											<th>TWH</th>-->
											<th>OT</th>
											<th>AOT</th>
											<th>AOT By</th>
											<th>Adj. From</th>
											<th>Adj. To</th>
											<th>All Punches</th>
										</tr>
									</thead>
									<tbody id="empOtTabledata">
									
									</tbody>
								</table>
							</div>
						</form>
					</div>
                </div>
            </div>
        </div>        
    </div>
</div>

<?php $this->load->view('includes/footer'); ?>
<script>
$(document).ready(function(){
    reportTable('empOtTable');
    
    $(document).on('click','.loadData',function(){
        $(".error").html("");
		var valid = 1;    
		var attendance_date = $('#attendance_date').val();
		var emp_unit_id = $('#emp_unit_id').val();
		var ot_filter = $('#ot_filter').val();
		var ot_type = $('#ot_type').val();
		if(attendance_date == ""){$(".fromDate").html("Date is required.");valid=0;}
		if(valid)
		{
			$.ajax({
				url: base_url + controller + '/getEmployeeAttendanceData',
				data: {from_date:attendance_date,to_date:attendance_date,ot_filter:ot_filter,emp_unit_id:emp_unit_id,ot_type:ot_type},
				type: "POST",
				dataType:'json',
				success:function(data){
				    $('#empOtTable').DataTable().clear().destroy();
					$("#empOtTabledata").html(data.tbody);
					reportTable('empOtTable');
				}
			});
		}
    });

	$(document).on('click', '.BulkRequest', function() {
		if ($(this).attr('id') == "masterSelect") {
			if ($(this).prop('checked') == true) {
				$(".bulkOT").show();
				$("input[name='ref_id[]']").prop('checked', true);
			} else {
				$(".bulkOT").hide();
				$("input[name='ref_id[]']").prop('checked', false);
			}
		} else {
			if ($("input[name='ref_id[]']").not(':checked').length != $("input[name='ref_id[]']").length) {
				$(".bulkOT").show();
				$("#masterSelect").prop('checked', false);
			}else{ $(".bulkOT").hide(); }

			if ($("input[name='ref_id[]']:checked").length == $("input[name='ref_id[]']").length) {
				$("#masterSelect").prop('checked', true);
				$(".bulkOT").show();
			}else{$("#masterSelect").prop('checked', false);}
		}
	});

	$(document).on('click', '.bulkOT', function() {
		var ref_id = [];
		$("input[name='ref_id[]']:checked").each(function() {
			ref_id.push(this.value);
		});

		Swal.fire({
			title: 'Confirm!',
			html:'<lable for"remark">Remark</lable><textarea name="remark" id="del_remark" class="form-control m-input" placeholder="Enter Remark" autocomplete="off"></textarea> <div class="error remark_error"></div>Are you sure want to Approve OT?',
			showCancelButton: true,
			confirmButtonText: "Submit",
			showLoaderOnConfirm: true,
			preConfirm: async () => {
			  try {
				var del_remark = $("#del_remark").val();
				var ids = ref_id.join("~");
				var send_data = { ids:ids, remark:del_remark };
				$.ajax({
					url: base_url + controller + '/saveBulkOT',
					data: send_data,
					type: "POST",
					dataType:"json",
				}).done(function(data){
					if(data.status==1){
						Swal.fire( 'Success', data.message, 'success' );
						$(".loadData").trigger("click");
					}else{
						return Swal.showValidationMessage( data.message);
					}	
				});
			  } catch (error) {
				Swal.showValidationMessage(`
				  Request failed: ${error}
				`);
			  }
			},
			allowOutsideClick: () => !Swal.isLoading()
		  }).then((result) => {
			if (result.isConfirmed) {
			  Swal.fire({
				title: `${result.value.login}'s avatar`,
				imageUrl: result.value.avatar_url
			  });
			}
		});
		/* Swal.fire({
			title: 'Confirm!',
			text: '',
			icon: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Yes, delete it!',
		}).then(function(result) {
			if (result.isConfirmed){
				var del_remark = $("#del_remark").val();
				var ids = ref_id.join("~");
				var send_data = { ids:ids, remark:del_remark };
				$.ajax({
					url: base_url + controller + '/saveBulkOT',
					data: send_data,
					type: "POST",
					dataType:"json",
					success:function(response)
					{
						if(response.status==0){
							Swal.fire( 'Sorry...!', response.message, 'error' );
						}else{
							initTable();
							Swal.fire( 'Deleted!', response.message, 'success' );
						}	
					}
				});
				
			}
		}); */
		
		
	});
});

function approveOT(data){
	var button = data.button;if(button == "" || button == null){button="both";};
	var fnEdit = data.fnedit;if(fnEdit == "" || fnEdit == null){fnEdit="edit";}
	var fnsave = data.fnsave;if(fnsave == "" || fnsave == null){fnsave="save";}
	var savebtn_text = data.savebtn_text;if(savebtn_text == "" || savebtn_text == null){savebtn_text="Save";}
	
	var sendData = {id:data.id,ot:data.ot};
	$.ajax({ 
		type: "POST",   
		url: base_url + controller + '/' + fnEdit,   
		data: sendData,
	}).done(function(response){
		$("#"+data.modal_id).modal();
		$("#"+data.modal_id+' .modal-body').html('');
		$("#"+data.modal_id+' .modal-title').html(data.title);
		$("#"+data.modal_id+' .modal-body').html(response);
		$("#"+data.modal_id+" .modal-body form").attr('id',data.form_id);
		//$("#"+data.modal_id+" .modal-footer .btn-save").html(savebtn_text);
		$("#"+data.modal_id+" .modal-footer .btn-save").attr('onclick',"saveApproveOT('"+data.form_id+"','"+fnsave+"');");
		$("#"+data.modal_id+" .modal-footer .btn-save-close").attr('onclick',"store('"+data.form_id+"','"+fnsave+"','save_close');");
		$("#"+data.modal_id+" .modal-footer .btn-close").attr('data-modal_id',data.form_id);
		if(button == "close"){
			$("#"+data.modal_id+" .modal-footer .btn-close").show();
			$("#"+data.modal_id+" .modal-footer .btn-save").hide();
			$("#"+data.modalId+" .modal-footer .btn-save-close").hide();
		}else if(button == "save"){
			$("#"+data.modal_id+" .modal-footer .btn-close").hide();
			$("#"+data.modal_id+" .modal-footer .btn-save").show();
            $("#"+data.modalId+" .modal-footer .btn-save-close").show();
		}else{
			$("#"+data.modal_id+" .modal-footer .btn-close").show();
			$("#"+data.modal_id+" .modal-footer .btn-save").show();
            $("#"+data.modalId+" .modal-footer .btn-save-close").show();
		}
		// initModalSelect();
		// $(".single-select").comboSelect();
		// $('.model-select2').select2({ dropdownParent: $('.model-select2').parent() });
		// $("#"+data.modal_id+" .scrollable").perfectScrollbar({suppressScrollX: true});
		initMultiSelect();setPlaceHolder();
	});
}

function saveApproveOT(formId,fnsave){
    setPlaceHolder();
	if(fnsave == "" || fnsave == null){fnsave="save";}
	var form = $('#'+formId)[0];
	var fd = new FormData(form);
	$.ajax({
		url: base_url + controller + '/' + fnsave,
		data:fd,
		type: "POST",
		processData:false,
		contentType:false,
		dataType:"json",
	}).done(function(data){
    	if(data.status===0){
    		$(".error").html("");
    		$.each( data.message, function( key, value ) {$("."+key).html(value);});
    	}else if(data.status==1){
    		$('#'+formId)[0].reset();$(".modal").modal('hide');$(".loadData").trigger('click');
    		toastr.success(data.message, 'Success', { "showMethod": "slideDown", "hideMethod": "slideUp", "closeButton": true, positionClass: 'toastr toast-bottom-center', containerId: 'toast-bottom-center', "progressBar": true });
    	}else{
    		toastr.error(data.message, 'Error', { "showMethod": "slideDown", "hideMethod": "slideUp", "closeButton": true, positionClass: 'toastr toast-bottom-center', containerId: 'toast-bottom-center', "progressBar": true });
    	}
	});
}

function reportTable(tableId){
	var reportTable = $('#'+tableId).DataTable({
		responsive: true,
		//'stateSave':true,
		"autoWidth" : false,
		// order:[],
		"columnDefs": 	[
							{ type: 'natural', targets: 0 },
							// { orderable: false, targets: "_all" }, 
							{ className: "text-left", targets: [0,1] }, 
							{ className: "text-center", "targets": "_all" } 
						],
		pageLength:25,
		language: { search: "" },
		lengthMenu: [
			[ 10, 25, 50, 100, -1 ],[ '10 rows', '25 rows', '50 rows', '100 rows', 'Show all' ]
		],
		dom: "<'row'<'col-sm-7'B><'col-sm-5'f>>" +"<'row'<'col-sm-12't>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
		buttons: [ 'pageLength', 'excel', {text: 'Refresh',action: function ( e, dt, node, config ) {$(".loadData").trigger('click')}}]
	});
	reportTable.buttons().container().appendTo( '#'+tableId+'_wrapper toolbar' );
	var bulkOTBtn = '<button class="btn btn-outline-primary bulkOT" tabindex="0" aria-controls="empOtTable" type="button"><span>Bulk OT</span></button>';
	$("#empOtTable_wrapper .dt-buttons").append(bulkOTBtn);
	$(".bulkOT").hide();
	$('.dataTables_filter .form-control-sm').css("width","97%");
	$('.dataTables_filter .form-control-sm').attr("placeholder","Search.....");
	$('.dataTables_filter').css("text-align","left");
	$('.dataTables_filter label').css("display","block");
	$('.btn-group>.btn:first-child').css("border-top-right-radius","0");
	$('.btn-group>.btn:first-child').css("border-bottom-right-radius","0");
}

</script>