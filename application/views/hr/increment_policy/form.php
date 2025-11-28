<?php 
	$this->load->view('includes/header'); 
	$today = new DateTime();
	$today->modify('first day of this month');$first_day = date('Y-m-d');
	$today->modify('last day of this month');$last_day = date("t",strtotime($today->format('Y-m-d')));
	$monthArr = ['Apr-'.$start_year=>'01-04-'.$start_year,'May-'.$start_year=>'01-05-'.$start_year,
	'Jun-'.$start_year=>'01-06-'.$start_year,'Jul-'.$start_year=>'01-07-'.$start_year,'Aug-'.$start_year=>'01-08-'.$start_year,'Sep-'.$start_year=>'01-09-'.$start_year,'Oct-'.$start_year=>'01-10-'.$start_year,'Nov-'.$start_year=>'01-11-'.$start_year,'Dec-'.$start_year=>'01-12-'.$start_year,'Jan-'.$end_year=>'01-01-'.$end_year,'Feb-'.$end_year=>'01-02-'.$end_year,'Mar-'.$end_year=>'01-03-'.$end_year];	
?>
<div class="page-content-tab">
    <div class="container-fluid bg-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h4><u>Increment Policy</u></h4>
                    </div>
                    <div class="card-body">
                        <form autocomplete="off" id="saveIncrementpolicy">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-2 form-group">
                                        <label for="policy_no">Policy No.</label>
										<input type="text" name="policy_no" class="form-control" value="<?=(!empty($dataRow[0]->policy_no))?$dataRow[0]->policy_no:$nextPolicyNo?>" readonly />
									</div>
                                    <div class="col-md-2 form-group">
                                        <label for="effect_date">Effect Date</label>
                                        <select name="effect_date" id="effect_date" class="form-control select2">
                                            <?php
                                                foreach($monthArr as $key=>$value):
                                                    $selected = (!empty($dataRow[0]->effect_date) && $dataRow[0]->effect_date == $value)?"selected":"";
                                                    echo '<option value="'.$value.'" '.$selected.'>'.$key.'</option>';
                                                endforeach;
                                            ?>
                                        </select>
                                    </div> 
                                    <div class="col-md-4 form-group">
                                        <label for="policy_name">Policy Name</label>
										<input type="text" name="policy_name" class="form-control" value="<?=(!empty($dataRow[0]->policy_name))?$dataRow[0]->policy_name:""?>"  />
									</div>
                                  
                                    <div class="col-md-4 form-group">
                                        <label for="remark">Remark</label>
                                        <input type="text" name="remark" id="remark" class="form-control" value="<?=(!empty($dataRow[0]->remark))?$dataRow[0]->remark:""?>" />
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-3 form-group">
                                        <select name="emp_unit_id" id="emp_unit_id" class="form-control select2">
                                            <option value="">Select All Company</option>
                                            <?php
                                                foreach($cmList as $row):
                                                    echo '<option value="'.$row->id.'" >'.$row->company_name.'</option>';
                                                endforeach;
                                            ?>
                                        </select>
                                    </div> 
                                    <div class="col-md-3 form-group">
                                        <select name="dept_id" id="dept_id" class="form-control select2">
                                            <option value="">Select All Department</option>
                                            <?php
                                                foreach($deptData as $row):
                                                    echo '<option value="'.$row->id.'" >'.$row->name.'</option>';
                                                endforeach;
                                            ?>
                                        </select>
                                    </div> 
                                    <div class="col-md-6 form-group">
                                        <div class="input-group">
                                            <select name="ref_month" id="ref_month" class="form-control single-select" style="width:55%">
                                                <?php
                                                    foreach($monthArr as $key=>$value):
                                                        $selected = (!empty($dataRow[0]->ref_month) && $dataRow[0]->ref_month == $value)?"selected":"";
                                                        echo '<option value="'.$value.'" '.$selected.'>'.$key.'</option>';
                                                    endforeach;
                                                ?>
                                            </select>
                                            <button type="button" class="btn waves-effect waves-light btn-success float-right loaddata mr-2" title="Load Data"><i class="fas fa-sync-alt"></i> Load</button>	
                                        </div>
                                    </div>   
                                </div>
                            </div>

                            <hr>
                            <div class="col-md-12">
                                <div class="error general"></div>
                            </div>

							<div class="col-md-12 mt-3">
								<div class="row form-group">
									<div class="table-responsive">
										<table id="finaltbl" class="table table-bordered generalTable">
											<thead class="thead-info">
                                                <tr class="text-center">
                                                    <th colspan="9">Employee Salary Details</th>
                                                    <th colspan="3">Increment Details</th>
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
                                                    <th>Monthly Hours</th>
                                                    <th>Monthly Salary</th>
                                                    <th>Rate/Hour</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyData">
                                               <?php
                                                echo $tbody;
                                               ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
						</form>
                    </div>

                    <div class="card-footer">
                        <div class="col-md-12">
                            <button type="button" class="btn waves-effect waves-light btn-outline-success float-right save-form" onclick="savePolicy('saveIncrementpolicy');" ><i class="fa fa-check"></i> Save</button>
                            <a href="<?=base_url($headData->controller)?>" class="btn waves-effect waves-light btn-outline-secondary float-right save-form" style="margin-right:10px;"><i class="fa fa-times"></i> Cancel</a>
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
	$(document).on('click','.loaddata',function(e){
		$(".error").html("");
		var valid = 1;
		var emp_unit_id = $('#emp_unit_id').val();
		var dept_id = $('#dept_id').val();
		var ref_month = $("#ref_month").val();
		if(valid)
		{
            $.ajax({
                url: base_url + controller + '/getEmpSalaryDetails',
                data: {emp_unit_id:emp_unit_id,dept_id:dept_id,ref_month:ref_month},
				type: "POST",
				dataType:'json',
				success:function(data){
					$("#tbodyData").html(data.tbody);
                }
            });
        }
    });   

    $(document).on("keyup", ".rate_calc", function () {
        var row_id = $(this).data('row_id');
		var monthly_hours = $("#monthly_hours"+row_id).val();
		var monthly_salary = $("#monthly_salary"+row_id).val();
		
		$("#rate_hour"+row_id).val(0);
		if((monthly_hours != '' || monthly_hours != 0) && (monthly_salary != '' || monthly_salary != 0)){
			var rate_hour = (parseFloat(monthly_salary) / parseFloat(monthly_hours)).toFixed(2);
			$("#rate_hour"+row_id).val(rate_hour);
		}
	});
	
});
function savePolicy(formId,fnsave){
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
			initTable(); $('#'+formId)[0].reset();$(".modal").modal('hide');   
			Swal.fire({ icon: 'success', title: data.message});
            location.href = base_url + 'hr/incrementPolicy';
        }else{
			initTable();  $('#'+formId)[0].reset();$(".modal").modal('hide');   
			Swal.fire({ icon: 'error', title: data.message });
            location.href = base_url + 'hr/incrementPolicy';
        }
	});
}
</script>