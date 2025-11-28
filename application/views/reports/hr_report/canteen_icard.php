<?php $this->load->view('includes/header'); ?>
<div class="page-wrapper">
    <div class="container-fluid bg-container">
        <!-- <div class="row"> -->
			<div class="card">
				<div class="card-body">
					<form id="printMultiTags" action="<?=base_url($headData->controller.'/printCanteenIcard')?>" method="POST"  target="_blank">
						<div class="row">
							<div class="col-md-3 form-group">
								<h4 class="card-title pageHeader"><?=$pageHeader?></h4>
							</div>  
						
							<div class="col-md-3 form-group">
								<select name="emp_dept_id" id="emp_dept_id" class="form-control single-select" >
									<option value="">All Department</option>
									<?php
										foreach($deptList as $row):
											echo '<option value="'.$row->id.'" >'.$row->name.'</option>';
										endforeach;
									?>
								</select>
							</div>
							<div class="col-md-3 form-group">
								<select name="emp_id" id="emp_id" class="form-control select2" >
									<option value="">Select Employee</option>
									<?php
										foreach($empList as $row):
											echo '<option value="'.$row->id.'">['.$row->emp_code.'] '.$row->emp_name.'</option>';
										endforeach;
									?>
								</select>
							</div>
							<div class="col-md-3 form-group">
								<button type="submit" class="btn waves-effect waves-light btn-facebook float-left" datatip="Generate QR Code" flow="down" ><i class=" fas fa-address-card"></i> Generate Icard</button>
							</div>
						</div>  
					</form>      
				</div>                                         
			</div>  


			<div class="card">
				<div class="card-body">
					<form id="canteenAppointment">
						<div class="row">
							<div class="col-md-2 form-group">
								<h4 class="card-title pageHeader">Guest Appointment</h4>
							</div>  
							<div class="col-md-3 form-group">
								<label for="trans_date">Date</label>
								<input type="date" name="trans_date" id="trans_date" class="form-control req" value="<?=date("Y-m-d")?>">
							</div>
							<div class="col-md-3 form-group">
								<label for="empId">Employee Name</label>
								<select name="empId" id="empId" class="form-control select2 req" >
									<option value="">Select Employee</option>
									<?php
										foreach($empList as $row):
											echo '<option value="'.$row->id.'">['.$row->emp_code.'] '.$row->emp_name.'</option>';
										endforeach;
									?>
								</select>
								<div class="error empId"></div>
							</div>
							<div class="col-md-4 form-group">
								<label for="no_person">No OF Guest</label>
								<div class="input-group-append">
									<input type="text" name="no_person" id="no_person" class="form-control req" value="" style="width:70%">
									<button type="button" class="btn waves-effect waves-light btn-facebook float-left" datatip="Save" flow="down" onclick="saveAppointment()">Save</button>
								</div>
							</div>
						</div>  
						<div class="col-md-12 row">
							<h5>Today's Appointments : </h5>
							<table class="table table-bordered">
								<thead class="thead-info">
									<tr >
										<th>#</th>
										<th>Employee </th>
										<th>No Of Guest</th>
										<th>Status</th>
									</tr>
								</thead>
								<tbody>
									<?php
										if(!empty($canteenData)){
											$i=1;
											foreach($canteenData as $row){
												?>
												<tr>
													<td><?=$i++;?></td>
													<td><?=$row->emp_name?></td>
													<td><?=$row->no_person?></td>
													<td><?=(!empty($row->canteen_status)?'Meal Over':'Pending')?></td>
												</tr>
												<?php
											}
										}
									?>
								</tbody>
							</table>
						</div>
					</form>      
				</div>                                         
			</div>  
        <!-- </div> -->
    </div>      
</div>


<?php $this->load->view('includes/footer'); ?>

<script>
$(document).ready(function(){
	$(document).on('change',"#emp_dept_id",function(){
		var emp_dept_id = $("#emp_dept_id").val();
		$.ajax({ 
            type: "POST",   
            url: base_url + controller + '/getEmpListByDept',   
            data: {emp_dept_id : emp_dept_id},
			dataType:"json",
        }).done(function(response){
			$('#emp_id').html("");
			$('#emp_id').html(response.options);
			$('#emp_id').select2();
        });
    });
});

function saveAppointment() {
	var fd = $('#canteenAppointment')[0];
	var formData = new FormData(fd);
	$.ajax({
		url: base_url + controller + '/saveCanteenAppointment',
		data: formData,
		processData: false,
		contentType: false,
		type: "POST",
		dataType: "json",
	}).done(function (data) {
		if (data.status === 0) {
			$(".error").html("");
			$.each(data.message, function (key, value) {
				$("." + key).html(value);
			});
		} else if (data.status == 1) {
			toastr.success(data.message, 'Success', { "showMethod": "slideDown", "hideMethod": "slideUp", "closeButton": true, positionClass: 'toastr toast-bottom-center', containerId: 'toast-bottom-center', "progressBar": true });
			window.location.reload();
		} else {
			toastr.error(data.message, 'Error', { "showMethod": "slideDown", "hideMethod": "slideUp", "closeButton": true, positionClass: 'toastr toast-bottom-center', containerId: 'toast-bottom-center', "progressBar": true });
		}
	});
}
</script>