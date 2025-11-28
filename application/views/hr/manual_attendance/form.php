<form autocomplete="off"  data-res_function="getAttendanceResponse">
    <div class="col-md-12">
        <div class="row">
            <input type="hidden" name="id" value="" />
            <input type="hidden" name="punch_type" value="2" />
            <input type="hidden" name="attendance_date" id="attendance_date" value="<?=$attendance_date?>">

            <div class="col-md-6 form-group">
                <label for="emp_id">Employee</label>
                <select name="emp_id" id="emp_id" class="form-control select2 req loadtrans">
                    <option value="">Select Employee</option>
                    <!--<option value="<?=$loginID?>" <?php //(!empty($dataRow->emp_id) && $loginID == $dataRow->emp_id)?"selected":""; ?>>My Self</option>-->
                    <?php
                        foreach($empList as $row):
							if($loginID != $row->id):
								$selected = ((!empty($dataRow->emp_id) && $row->id == $dataRow->emp_id)?"selected":((!empty($emp_id) && $row->id == $emp_id)?"selected":""));
								echo '<option value="'.$row->id.'" '.$selected.'>['.$row->emp_code.'] '.$row->emp_name.'</option>';
							endif;
                        endforeach;
                    ?>
                </select>
            </div> 
            <div class="col-md-3 form-group">
                <label for="punch_date">Attendance Date</label>
                <input type="date" name="punch_date" id="punch_date" class="form-control req changeDate1" value="<?=((!empty($dataRow->punch_date))?formatDate($dataRow->punch_date, 'Y-m-d'):((!empty($attendance_date))?formatDate($attendance_date, 'Y-m-d'):date("Y-m-d")))?>" min="<?=$attendance_date?>" max="<?=date('Y-m-d', strtotime($attendance_date . ' +1 day'));?>" />
            </div>

            <div class="col-md-3 form-group">
                <label for="punch_in">Punch Time</label>
                <input type="time" name="punch_in" id="punch_in" class="form-control req" value="" />
            </div>
            
            <div class="col-md-10 form-group">
                <label for="remark">Reason</label>
                <input type="text" name="remark" id="remark" class="form-control req" value="" />
            </div>
            <div class="col-md-2 ">
                <?php
                    $param = "{'formId':'addManualAttendance','fnsave':'save','res_function':'getAttendanceResponse','controller':'hr/manualAttendance'}";
                ?>
                <button type="button" class="btn waves-effect waves-light btn-success btn-save save-form float-right btn-block" onclick="customStore(<?=$param?>)" style="height:35px"><i class="fa fa-check"></i> Save</button>
            </div>
        </div>
    </div>
</form>
<div class="row">
    <div class="col-md-12 form-group">
        <div class="table-responsive">
            <table id="attenData" class="table table-bordered align-items-center">
                <thead class="thead-info">
                    <tr>
                        <th style="width:5%;" rowspan="2">#</th>
                        <th>Shift</th>
                        <th>Punch Date & Time</th>
                        <th>Punch Type</th>
                        <th>Reason</th>
                        <th class="text-center" style="width:10%;">Action</th>
                    </tr>
                </thead>
                <tbody id="attenBody">
                    <?php 
                        if(!empty($punchData['tbody'])):
                            echo $punchData['tbody'];
                        endif;
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
var tbodyData = false;
$(document).ready(function(){
    setTimeout(function(){ $('#process_by').trigger('change'); }, 50);

    if(!tbodyData){
        var postData = {'postData':{'emp_id':$("#emp_id").val(),'punch_date':$("#punch_date").val()},'table_id':"attenData",'tbody_id':'attenBody','tfoot_id':'','fnget':'getEmpPunchData','controller':'hr/manualAttendance'};
        getEmpPunchData(postData);
        tbodyData = true;
    }

});

function getEmpPunchData(data){
	var postData = data.postData || {};
	var fnget = data.fnget || "";
	var controllerName = data.controller || controller;

	var table_id = data.table_id || "";
	var thead_id = data.thead_id || "";
	var tbody_id = data.tbody_id || "";
	var tfoot_id = data.tfoot_id || "";	

	if(thead_id != ""){
		$("#"+table_id+" #"+thead_id).html(data.thead);
	}
	
	$.ajax({
		url: base_url + controllerName + '/' + fnget,
		data:postData,
		type: "POST",
		dataType:"json",
		beforeSend: function() {
			if(table_id != ""){
				var columnCount = $('#'+table_id+' thead tr').first().children().length;
				$("#"+table_id+" #"+tbody_id).html('<tr><td colspan="'+columnCount+'" class="text-center">Loading...</td></tr>');
			}
		},
	}).done(function(res){
		$("#"+table_id+" #"+tbody_id).html('');
        $("#"+table_id+" #"+tbody_id).html(res.tbody);
		
	});
}

function trashPunch(id,emp_id,name='Record'){
    var emp_id = $('#emp_id').val();
	var punch_date = $('#punch_date').val();
	var attendance_date = $('#attendance_date').val();
	var send_data = { id:id, emp_id:emp_id, punch_date:punch_date,attendance_date:attendance_date };

    Swal.fire({
		title: 'Are you sure?',
		text: "You won't be able to revert this!",
		icon: 'warning',
		showCancelButton: true,
		confirmButtonColor: '#3085d6',
		cancelButtonColor: '#d33',
		confirmButtonText: 'Yes, Do it!',
	}).then(function(result) {
		if (result.isConfirmed){
			$.ajax({
                url: base_url + 'hr/manualAttendance/deletePunch',
                data: send_data,
                type: "POST",
                dataType:"json",
                success:function(data)
                {
                   getAttendanceResponse(data)
                }
            });
		}
	});
	
}

function getAttendanceResponse(data,formId=""){ 
    if(data.status==1){
        if(formId){
			$("#punch_in").val("");
			$("#remark").val("");
        }
        var postData = {'postData':{'emp_id':$("#emp_id").val(),'punch_date':$("#punch_date").val()},'table_id':"attenData",'tbody_id':'attenBody','tfoot_id':'','fnget':'getEmpPunchData','controller':'hr/manualAttendance'};
        getEmpPunchData(postData);
        tbodyData = true;
    }else{
        if(typeof data.message === "object"){
            $(".error").html("");
            $.each( data.message, function( key, value ) {$("."+key).html(value);});
        }else{
            Swal.fire({ icon: 'error', title: data.message });
        }			
    }
}

</script>