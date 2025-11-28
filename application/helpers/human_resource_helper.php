<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/* get Pagewise Table Header */
function getHrDtHeader($page){
    /* Department Header */
    $data['departments'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['departments'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['departments'][] = ["name"=>"Department Name"];
    $data['departments'][] = ["name"=>"Section Name"];

    /* Designation Header */
    $data['designation'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['designation'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['designation'][] = ["name"=>"Designation Name"];
    $data['designation'][] = ["name"=>"Remark"];

    /* Employee Category Header */
    $data['employeeCategory'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['employeeCategory'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['employeeCategory'][] = ["name"=>"Category Name"];
    $data['employeeCategory'][] = ["name"=>"Over Time"];

    /* Employee Header */
    $data['employees'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['employees'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['employees'][] = ["name"=>"Employee Name"];
    $data['employees'][] = ["name"=>"Emp Code","textAlign"=>'center'];
    $data['employees'][] = ["name"=>"Unit","textAlign"=>'center'];
    $data['employees'][] = ["name"=>"Department"];
    $data['employees'][] = ["name"=>"Designation"];
    $data['employees'][] = ["name"=>"Category","textAlign"=>'center'];
    $data['employees'][] = ["name"=>"Contact No.","textAlign"=>'center'];

    /* Employee Loan Header */
   $data['empLoan'][] = ["name"=>"Action","style"=>"width:5%;"];
   $data['empLoan'][] = ["name"=>"#","style"=>"width:5%;","textAlign"=>"center"];
   $data['empLoan'][] = ["name"=>"Sanction Date"];
   $data['empLoan'][] = ["name"=>"Sanction No."];
   $data['empLoan'][] = ["name"=>"Employee Name"];
   $data['empLoan'][] = ["name"=>"Amount"];
   $data['empLoan'][] = ["name"=>"reason"];
   
    /* Advance Salary Header */
    $data['advanceSalary'][] = ["name"=>"Action","style"=>"width:5%;"];
    $data['advanceSalary'][] = ["name"=>"#","style"=>"width:5%;","textAlign"=>"center"];
    $data['advanceSalary'][] = ["name"=>"Name"];
    $data['advanceSalary'][] = ["name"=>"Date"];
    $data['advanceSalary'][] = ["name"=>"Amount"];
    $data['advanceSalary'][] = ["name"=>"reason"];

    /* Leave Header */
    $data['leave'][] = ["name"=>"Action","style"=>"width:5%;"];
    $data['leave'][] = ["name"=>"#","style"=>"width:5%;","textAlign"=>"center"];
    $data['leave'][] = ["name"=>"Employee"];
    $data['leave'][] = ["name"=>"Emp Code"];
    $data['leave'][] = ["name"=>"Leave Type"];
    $data['leave'][] = ["name"=>"From"];
    $data['leave'][] = ["name"=>"To"];
    $data['leave'][] = ["name"=>"Leave Days"];
    $data['leave'][] = ["name"=>"Reason"];
    $data['leave'][] = ["name"=>"Status"];

    /* Leave Setting Header */
    $data['leaveSetting'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['leaveSetting'][] = ["name"=>"#","style"=>"width:5%;","textAlign"=>"center"];
	$data['leaveSetting'][] = ["name"=>"Leave Type"];
    $data['leaveSetting'][] = ["name"=>"Remark"];

	/* Leave Approve Header */
	$data['leaveApprove'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['leaveApprove'][] = ["name"=>"#","style"=>"width:5%;","textAlign"=>"center"];
	$data['leaveApprove'][] = ["name"=>"Employee"];
	$data['leaveApprove'][] = ["name"=>"Emp Code"];
	$data['leaveApprove'][] = ["name"=>"Leave Type"];
	$data['leaveApprove'][] = ["name"=>"From"];
	$data['leaveApprove'][] = ["name"=>"To"];
	$data['leaveApprove'][] = ["name"=>"Leave Days"];
	$data['leaveApprove'][] = ["name"=>"Reason"];
	$data['leaveApprove'][] = ["name"=>"Status"];
	
	/* Shift Header */
	$data['shift'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['shift'][] = ["name"=>"#","style"=>"width:5%;","textAlign"=>"center"];
	$data['shift'][] = ["name"=>"Shift Name"];
	$data['shift'][] = ["name"=>"Start Time"];
	$data['shift'][] = ["name"=>"End Time"];
	$data['shift'][] = ["name"=>"Production Time"];
	$data['shift'][] = ["name"=>"Lunch Time"];
	$data['shift'][] = ["name"=>"Shift Hour"];
	

    /* Employee Recruitment */
    $data['empRecruitment'][] = ["name"=>"Action"];
    $data['empRecruitment'][] = ["name"=>"#","textAlign"=>'center']; 
    $data['empRecruitment'][] = ["name"=>"Interview Date"];
    $data['empRecruitment'][] = ["name"=>"Employee Name"];
    $data['empRecruitment'][] = ["name"=>"Category"];
    $data['empRecruitment'][] = ["name"=>"Department"];
    $data['empRecruitment'][] = ["name"=>"Designation"];
    $data['empRecruitment'][] = ["name"=>"Education","textAlign"=>'center'];
    $data['empRecruitment'][] = ["name"=>"Experience","textAlign"=>'center'];
    $data['empRecruitment'][] = ["name"=>"Contact No.","textAlign"=>'center'];

    /* Relieved Employee Header */
    $data['relievedEmployee'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['relievedEmployee'][] = ["name"=>"#","textAlign"=>"center"]; 
    $data['relievedEmployee'][] = ["name"=>"Employee Name"];
    $data['relievedEmployee'][] = ["name"=>"Emp Code."];
    $data['relievedEmployee'][] = ["name"=>"Contact No."];
    $data['relievedEmployee'][] = ["name"=>"Department"];
    $data['relievedEmployee'][] = ["name"=>"Designation"];
    $data['relievedEmployee'][] = ["name"=>"Relive Date"];
    
    /* Extra Hours Header */
    $data['extraHours'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['extraHours'][] = ["name"=>"#","textAlign"=>"center"];
	$data['extraHours'][] = ["name"=>"Emp Code"];
	$data['extraHours'][] = ["name"=>"Employee"];
	$data['extraHours'][] = ["name"=>"Date"];
    $data['extraHours'][] = ["name"=>"Extra Hours"];
    $data['extraHours'][] = ["name"=>"Reason"];

    /* Employee Facility Header */
    $data['employeeFacility'][] = ["name"=>"Action","style"=>"width:5%;"];
    $data['employeeFacility'][] = ["name"=>"#","textAlign"=>"center"];
    $data['employeeFacility'][] = ["name"=>"Facility Type"];
    $data['employeeFacility'][] = ["name"=>"Returnable"];
    
    /* CTC Format Header */
    $data['ctcFormat'][] = ["name"=>"Action","sortable"=>"FALSE"];
    $data['ctcFormat'][] = ["name"=>"#","sortable"=>"FALSE","textAlign"=>"center"];
    $data['ctcFormat'][] = ["name"=>"Format Name"];
    $data['ctcFormat'][] = ["name"=>"Format No"];
    $data['ctcFormat'][] = ["name"=>"Salary Duration"];
    $data['ctcFormat'][] = ["name"=>"Gratuity Days"];
    $data['ctcFormat'][] = ["name"=>"Gratuity(%)"];
    $data['ctcFormat'][] = ["name"=>"Effect From"];
	
	 /* Salary Heads Header */
    $data['salaryHead'][] = ["name"=>"Action","style"=>"width:5%;"];
    $data['salaryHead'][] = ["name"=>"#","textAlign"=>"center"];
    $data['salaryHead'][] = ["name"=>"Salary Head"];
    $data['salaryHead'][] = ["name"=>"Type"];
	
	/* Skill Master Header */
    $data['skillMaster'][] = ["name"=>"Action","sortable"=>"FALSE"];
    $data['skillMaster'][] = ["name"=>"#","sortable"=>"FALSE","textAlign"=>"center"];
    $data['skillMaster'][] = ["name"=>"Skill"];
    $data['skillMaster'][] = ["name"=>"Department"];
    $data['skillMaster'][] = ["name"=>"Designation"];
    $data['skillMaster'][] = ["name"=>"Req. Per(%)"];
	
	/* Notice Board Header */
	$data['noticeBoard'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['noticeBoard'][] = ["name"=>"#","textAlign"=>"center"];
    $data['noticeBoard'][] = ["name"=>"Title"];
    $data['noticeBoard'][] = ["name"=>"Description"];
    $data['noticeBoard'][] = ["name"=>"Circular From"];
    $data['noticeBoard'][] = ["name"=>"Circular To"];
	
	/* Increment Policy Header */
	$data['incrementPolicy'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['incrementPolicy'][] = ["name"=>"#","textAlign"=>"center"];
    $data['incrementPolicy'][] = ["name"=>"Effect Date"];
    $data['incrementPolicy'][] = ["name"=>"Policy No."];
    $data['incrementPolicy'][] = ["name"=>"Policy Name"];
    $data['incrementPolicy'][] = ["name"=>"Ref. Month"];
	
	/* Facility Meghavi */
    $data['facility'][] = ["name"=>"Action","style"=>"width:5%;"];
    $data['facility'][] = ["name"=>"#","textAlign"=>"center"];
    $data['facility'][] = ["name"=>"Date"];
    $data['facility'][] = ["name"=>"Name"];
    $data['facility'][] = ["name"=>"Facility Type"];
    $data['facility'][] = ["name"=>"Qty"];
    $data['facility'][] = ["name"=>"Size"];
	
	/* Panelty Meghavi */
    $data['penalty'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['penalty'][] = ["name"=>"#","textAlign"=>"center"];
    $data['penalty'][] = ["name"=>"Name"];
    $data['penalty'][] = ["name"=>"Date"];
    $data['penalty'][] = ["name"=>"Amount"];
    $data['penalty'][] = ["name"=>"reason"];
	
	/* HR Payroll*/
    $data['payroll'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['payroll'][] = ["name"=>"Month"];
    $data['payroll'][] = ["name"=>"Total Employees"];
    $data['payroll'][] = ["name"=>"Salary Amount"];

    return tableHeader($data[$page]);
}

/* Department Table Data */
function getDepartmentData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Department'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editDepartment', 'title' : 'Update Department'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->name,$data->section];
}

/* Designation Table Data */
function getDesignationData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Designation'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editDesignation', 'title' : 'Update Designation','call_function':'edit'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->title,$data->description];
}

/* Employee Category Table Data */
function getEmployeeCategoryData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Employee Category'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editEmployeeCategory', 'title' : 'Update Employee Category','call_function':'edit'}";


    $editButton = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->category,$data->overtime];
}

/* Employee Table Data */
function getEmployeeData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Employee'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-xl-modal', 'form_id' : 'editEmployee', 'title' : 'Update Employee','call_function':'edit'}";
    
    $leaveButton = '';$addInDevice = '';$activeButton = '';$empRelieveBtn = '';$editButton = '';$deleteButton = '';

    $emprelieveParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'master-modal-md', 'form_id' : 'empEdu', 'title' : 'Employee Relieve', 'call_function' : 'empRelive', 'fnsave' : 'saveEmpRelieve' ,'button' : 'both'}";
    $empRelieveBtn = '<a class="btn btn-dark btn-edit permission-remove" href="javascript:void(0)" datatip="Relieve" flow="down" onclick="modalAction('.$emprelieveParam.');"><i class="fas fa-close" ></i></a>';

    if($data->is_active == 1):
        $activeParam = "{'postData':{'id' : ".$data->id.", 'is_active' : 0},'fnsave':'activeInactive','message':'Are you sure want to De-Active this Employee?'}";
        $activeButton = '<a class="btn btn-youtube permission-modify" href="javascript:void(0)" datatip="De-Active" flow="down" onclick="confirmStore('.$activeParam.');"><i class="fa fa-ban"></i></a>';    

        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

        $deviceParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'empEdu', 'title' : 'Add Employee In Device', 'call_function' : 'addEmployeeInDevice' ,'button' : 'close'}";
        $addInDevice = '<a class="btn btn-dark permission-modify" href="javascript:void(0)" datatip="Device" flow="down"  onclick="modalAction('.$deviceParam.');"><i class="fas fa-desktop"></i></a>';

        $empName = '<a href="'.base_url("hr/employees/empProfile/".$data->id).'" datatip="View Profile" flow="down">'.$data->emp_name.'</a>';
        // $empName = $data->emp_name;
    else:
        $activeParam = "{'postData':{'id' : ".$data->id.", 'is_active' : 1},'fnsave':'activeInactive','message':'Are you sure want to Active this Employee?'}";
        $activeButton = '<a class="btn btn-success permission-remove" href="javascript:void(0)" datatip="Active" flow="down" onclick="confirmStore('.$activeParam.');"><i class="fa fa-check"></i></a>';  
          
        $empName = $data->emp_name;
    endif;
    
    $CI = & get_instance();
    $userRole = $CI->session->userdata('role');

    $resetPsw='';
    if(in_array($userRole,[-1,1])):
        $resetParam = "{'postData':{'id' : ".$data->id."},'fnsave':'resetPassword','message':'Are you sure want to Change ".$data->emp_name." Password?'}";
        $resetPsw='<a class="btn btn-danger" href="javascript:void(0)" onclick="confirmStore('.$resetParam.');" datatip="Reset Password" flow="down"><i class="fa fa-key"></i></a>';
    endif;
    
    $action = getActionButton($resetPsw.$leaveButton.$addInDevice.$activeButton.$empRelieveBtn.$editButton.$deleteButton);

    return [$action,$data->sr_no,$empName,$data->emp_code,$data->cmp_alias,$data->dept_name,$data->emp_designation,$data->emp_category,$data->emp_contact];
}

function getEmpLoanData($data){	
	$deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Employee Loan'}";
	$editButton ="";$deleteButton =""; $printBtn =""; $approveButton="";$senctionBtn="";
    if(empty($data->trans_status)){
        $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

        $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editLoan', 'title' : 'Update Loan','call_function':'edit'}";
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
        
		$approveParam = "{'postData':{'id' : ".$data->id.",'approve_type':'1'},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'loanApproval', 'title' : 'Loan Approval','fnsave' : 'saveLoanApproval', 'savebtn_text':'Approve'}";
        $approveButton = '<a class="btn btn-success btn-edit permission-approve" href="javascript:void(0)" datatip="Approve" flow="down" onclick="modalAction('.$approveParam.');"><i class="mdi mdi-check"></i></a>';
    }else{
        $printBtn = '<a class="btn btn-success btn-edit permission-approve" href="'.base_url('hr/empLoan/printLoan/'.$data->id).'" target="_blank" datatip="Print" flow="down"><i class="mdi mdi-file-pdf" ></i></a>';
    }
    if($data->trans_status == 1){
        $senctionParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'loanApproval', 'title' : 'Senction','fnsave' : 'saveLoanSenction', 'savebtn_text':'Senction','fnedit':'loanSenction'}";

        $senctionBtn = '<a class="btn btn-warning  permission-approve" href="javascript:void(0)" datatip="Senction" flow="down" onclick="modalAction('.$senctionParam.');"><i class="mdi mdi-check"></i></a>';
    }
    $action = getActionButton($senctionBtn.$approveButton.$printBtn.$editButton.$deleteButton);
    return [$action,$data->sr_no,$data->trans_number,formatDate($data->entry_date),'['.$data->emp_code.'] '.$data->emp_name,$data->demand_amount,$data->reason];
}

function getAdvanceSalaryData($data){
	$deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Advance Salary'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editExtraHours', 'title' : 'Update Advance Salary'}";
    $sanctionParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'sanctionAdvance', 'title' : 'Sanction Advance','call_function':'sanctionAdvance'}";

    $editButton = '';$deleteButton = '';$sanction = '';

    if(empty($data->sanctioned_by)):
        $editButton = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
    
        $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

        $sanction = '<a class="btn btn-warning permission-write" href="javascript:void(0)" datatip="Sanction Advance" flow="down" onclick="modalAction('.$sanctionParam.');"><i class="mdi mdi-check"></i></a>';
    endif;
	
    $action = getActionButton($sanction.$editButton.$deleteButton);
    
    return [$action,$data->sr_no,formatDate($data->entry_date),'['.$data->emp_code.'] '.$data->emp_name,floatVal($data->amount),$data->reason,$data->sanctioned_by_name,((!empty($data->sanctioned_at))?formatDate($data->sanctioned_at):""),floatVal($data->sanctioned_amount),floatVal($data->deposit_amount),floatVal($data->pending_amount)];
}

/* Leave Table Data */
function getLeaveData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Leave'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editLeave', 'title' : 'Update Leave'}";
	
    $editButton = '';$deleteButton = '';$approveButton = '';
    if($data->approve_status == 0 AND strtotime($data->end_date) >= strtotime(date('Y-m-d'))){
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';

        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    }
    if($data->showLeaveAction){
        $approveButton = '<a class="btn btn-warning btn-leaveAction permission-modify" href="javascript:void(0)" data-id="'.$data->id.'" data-min_date="'.date("Y-m-d",strtotime($data->start_date)).'" datatip="Leave Action" flow="down"><i class="mdi mdi-check"></i></a>';
    }
    
	$action = getActionButton( $approveButton.$editButton.$deleteButton);
    return [$action,$data->sr_no,$data->emp_name,$data->emp_code,$data->leave_type,date('d-m-Y',strtotime($data->start_date)),date('d-m-Y',strtotime($data->end_date)),$data->total_days,$data->leave_reason,$data->status];
}

/* Leave Setting Table Data */
function getLeaveSettingData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Leave Type'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editLeaveType', 'title' : 'Update Leave Type'}";
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';

    $action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->leave_type,$data->remark];
}


/* Leave Approve Table Data */
function getLeaveApproveData($data){
	$approveButton='';
    if($data->approval_type == 1)
    {
        if($data->approve_status == 0 AND (in_array($data->loginId,explode(',',$data->fla_id))))
        {
            $approveButton = '<a class="btn btn-success btn-leaveAction permission-modify" href="javascript:void(0)" data-id="'.$data->id.'" data-emp_id="'.$data->emp_id.'" data-type_leave="'.$data->type_leave.'" data-min_date="'.date("Y-m-d",strtotime($data->start_date)).'" data-created_at="'.date("Y-m-d",strtotime($data->created_at)).'" data-approve_status="'.$data->approve_status.'" datatip="Leave Action" flow="down"><i class="mdi mdi-check"></i></a>';
        }
    }
	
	$start_date = date('d-m-Y',strtotime($data->start_date));
    $end_date = date('d-m-Y',strtotime($data->end_date));
    $total_days = $data->total_days.' Days';
    
    if(!empty($data->type_leave) && $data->type_leave == 'SL'){
        $start_date = date('d-m-Y H:i',strtotime($data->start_date));
        $end_date = date('d-m-Y H:i',strtotime($data->end_date));
        $hours = intval($data->total_days/60);
        $mins = intval($data->total_days%60);
        $total_days = sprintf('%02d',$hours).':'.sprintf('%02d',$mins).' Hours';
    }
    
	$action = getActionButton($approveButton);
    return [$action,$data->sr_no,$data->emp_name,$data->emp_code,$data->leave_type,$start_date,$end_date,$total_days,$data->leave_reason,$data->status];


    //$approveButton = '<a class="btn btn-success btn-leaveAction permission-modify" href="javascript:void(0)" data-id="'.$data->id.'" data-la="'.$data->leave_authority.'" data-created_at="'.date("Y-m-d",strtotime($data->start_date)).'" datatip="Leave Action" flow="down"><i class="mdi mdi-check"></i></a>';
	
	//$action = getActionButton( $approveButton);
    //return [$action,$data->sr_no,$data->emp_name,$data->emp_code,$data->leave_type,$data->start_date,$data->end_date,$data->total_days,$data->leave_reason,$data->status];
}

/* get Shift Data */
/* function getShiftData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Shift'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editShift', 'title' : 'Update Shift'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

	$action = getActionButton($editButton.$deleteButton);

    return [$action,$data->sr_no,$data->shift_name,$data->shift_start,$data->shift_end,$data->production_hour,$data->total_lunch_time,$data->total_shift_time];
} */

/* Employee Recruitment Table Data */
function getEmpRecruitmenteData($data){ 
     $deleteParam = "{'postData':{'id' : ".$data->id."}}";
    $confirm = '';$reject = '';$interviewBtn = '';$editButton = '';$deleteButton = ''; $rejectViewButton ='';$confirmViewButton ='';
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-xl-modal', 'form_id' : 'editEmployee', 'title' : 'Update Employee'}";

    $rejectParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'master-modal-md' ,'form_id' : 'rejectAppointment', 'title' : 'Reject', 'call_function' : 'rejectAppointment', 'fnsave' : 'saveRejectAppointment','button' : 'both'}";
    $confirmParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'master-modal-md', 'form_id' : 'confirmAppointment', 'title' : 'Confirm', 'call_function' : 'confirmAppointment', 'fnsave' : 'saveAppointment','button' : 'both'}";
    $viewRejectParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'master-modal-md','button' : 'close', 'form_id' : 'rejectDetails', 'title' : 'Confirm Details', 'call_function' : 'getRejectDetail'}";
    $viewConfirmParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'master-modal-md','button' : 'close', 'form_id' : 'confirmDetails', 'title' : 'Rejection Details', 'call_function' : 'getConfirmDetail'}";
   
    if($data->status == 1){
        $intSchedule = "{'postData':{'id' : ".$data->id."} ,'modal_id' : 'master-modal-md', 'form_id' : 'empRecruitment', 'title' : 'Interview Schedule', 'call_function' : 'interviewSchedule', 'fnsave' : 'saveInterviewSchedule' ,'button' : 'both'}";
        
        $interviewBtn = '<a class="btn btn-info btn-edit permission-remove" href="javascript:void(0)" datatip="Interview Schedule" flow="down" onclick="modalAction('.$intSchedule.');"><i class="fa fa-calendar-check"></i></a>';
        $reject = '<a class="btn btn-info btn-solution permission-modify" href="javascript:void(0)" datatip="Reject" flow="down" onclick="modalAction('.$rejectParam.');"><i class="fa fa-window-close"></i></a>';  
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="fas fa-edit" ></i></a>';

        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="fas fa-trash"></i></a>';
    }elseif($data->status == 2){
        $confirm = '<a class="btn btn-info btn-solution permission-modify" href="javascript:void(0)" datatip="Confirm" flow="down" onclick="modalAction('.$confirmParam.');"><i class="fa fa-check"></i></a>';  

        $reject = '<a class="btn btn-info btn-solution permission-modify" href="javascript:void(0)" datatip="Reject" flow="down" onclick="modalAction('.$rejectParam.');"><i class="fa fa-window-close"></i></a>';  

        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
       
    }elseif($data->status == 4){
        $reject = '<a class="btn btn-info btn-solution permission-modify" href="javascript:void(0)" datatip="Reject" flow="down" onclick="modalAction('.$rejectParam.');"><i class="fa fa-window-close"></i></a>';  
        $rejectViewButton = '<a class="btn btn-info btn-edit permission-modify" href="javascript:void(0)" datatip="View" flow="down" onclick="modalAction(' . $viewRejectParam . ');"><i class="fa fa-info"></i></a>';
 
    }elseif($data->status == 3){
        $confirmViewButton = '<a class="btn btn-info btn-edit permission-modify" href="javascript:void(0)" datatip="View" flow="down" onclick="modalAction(' . $viewConfirmParam . ');"><i class="fa fa-info"></i></a>';
    }
    
    $resetPsw='';
    if($data->loginId == 1):
        $resetParam = $data->id.",'".$data->emp_name."'";
        $resetPsw='<a class="btn btn-danger" href="javascript:void(0)" onclick="changeEmpPsw('.$resetParam.');" datatip="Reset Password" flow="down"><i class="fa fa-key"></i></a>';
    endif;
    
    $experience = (!empty($data->emp_experience)) ? $data->emp_experience.' <small>Months</small>' : '';
    $interviewDate = (!empty($data->interview_date)) ? $data->interview_date : date("d-m-Y");
    
    $action = getActionButton($confirmViewButton.$rejectViewButton.$interviewBtn.$confirm.$reject.$editButton.$deleteButton);
    return [$action,$data->sr_no, $interviewDate,$data->emp_name,$data->emp_category,$data->dept_name,$data->emp_designation,$data->emp_education,$experience,$data->emp_contact];
}

function getEmpRelievedData($data){
    $deleteParam = $data->id.",'Employee'";
    
    $emprejoinParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'master-modal-md', 'form_id' : 'empEdu', 'title' : 'Employee Rejoin', 'call_function' : 'empRejoin', 'fnsave' : 'saveEmpRelieve' ,'button' : 'both'}";

    
    $empRejoinBtn='<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Rejoin" flow="down" onclick="modalAction('.$emprejoinParam.');"><i class="mdi mdi-reload" ></i></a>';
   
   
    $empName = '<a href="'.base_url("hr/employees/empProfile/".$data->id).'" datatip="View Profile" flow="down">'.$data->emp_name.'</a>';

    $action = getActionButton($empRejoinBtn);
    return [$action,$data->sr_no,$data->emp_name,$data->emp_code,$data->emp_contact,$data->name,$data->title,formatDate($data->emp_relieve_date)];
}

function getExtraHoursData($data){
    
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message':'Extra Hours'}";
    $approveButton='';$editButton='';$deleteButton='';
    $editParam =  "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'master-modal-md', 'form_id' : 'editExtraHours', 'title' : 'editExtraHours', 'call_function' : 'edit', 'fnsave' : 'save' ,'button' : 'both'}";
    
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="fas fa-edit" ></i></a>';
    
	if($data->approved_by == 0):
		$deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="fas fa-trash"></i></a>';
        
        if($data->approvalAuth == 1 OR $data->loginID == 1):
            $approveParam =  "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'master-modal-md', 'form_id' : 'getXHRSDetail', 'title' : 'Extra Hours Approval', 'call_function' : 'getXHRSDetail', 'fnsave' : 'approveXHRS' ,'button' : 'both','savebtn_text':'Approve'}";
            $approveButton = '<a class="btn btn-info permission-modify " onclick="modalAction('.$approveParam.');" href="javascript:void(0)"  datatip="Approve EX. Hours" flow="down"><i class="fas fa-check"></i></a>';
    	endif;
    	
    endif;
    
    
	$action = getActionButton($approveButton.$editButton.$deleteButton);
    $punch_date = str_pad($data->ex_hours,2,"0",STR_PAD_LEFT).":".str_pad($data->ex_mins,2,"0",STR_PAD_LEFT);
    $punch_date = ($data->xtype < 0 ) ? '<strong class="text-danger">'.$punch_date.'</strong>' : $punch_date;
    return [$action,$data->sr_no,$data->emp_code,$data->emp_name,date('d-m-Y',strtotime($data->punch_date)),$punch_date,$data->remark];
}

/* Employee Facility Table Data */
function getEmployeeFacilityData($data){
	$deleteParam = "{'postData':{'id' : ".$data->id."},'message':'Employee Facility'}";
	$editParam =  "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'modal-md', 'form_id' : 'editEmployeeFacility', 'title' : 'Update EmployeeFacility', 'call_function' : 'edit', 'fnsave' : 'save' ,'button' : 'both'}";
	
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    $is_returnable ="";
    if($data->is_returnable == 1) { $is_returnable = "Yes"; }
    else { $is_returnable =  "No"; }
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->ficility_type,$is_returnable];
}

function getCtcFormatData($data){
	$deleteParam = "{'postData':{'id' : ".$data->id."},'message':'CTC Format'}";
	$editParam =  "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'modal-lg', 'form_id' : 'edit', 'title' : 'Update CTC Format', 'call_function' : 'edit', 'fnsave' : 'save' ,'button' : 'both'}";
    $editButton = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->format_name,$data->format_no,$data->salary_duration_text,$data->gratuity_days,$data->gratuity_per,formatDate($data->effect_from)];
}

function getSalaryHeadData($data){
	$deleteParam = "{'postData':{'id' : ".$data->id."},'message':'Salary Head','fndelete':'deleteSalaryHead'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'modal-md', 'form_id' : 'editSalaryHead', 'title' : 'Update Salary Head','call_function':'editSalaryHead','fnsave':'saveSalaryHead'}";
    $editButton = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';

    $deleteButton = '';
    if($data->is_system == 0):
        $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    endif;

    $action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->head_name,$data->type_text];
}

/* Skill Master Table Data */
function getSkillMasterData($data){
	$deleteParam = "{'postData':{'id' : ".$data->id."},'message':'Skill Master'}";
	$editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'modal-lg', 'form_id' : 'edit', 'title' : 'Update Skill Master'}";
    $editButton = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->skill,$data->name,$data->title,$data->req_per];
}

/* Notice Board Table Data */
function getNoticeBoardData($data){
	$deleteParam = "{'postData':{'id' : ".$data->id."},'message':'Notice'}";
	$editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'modal-md', 'form_id' : 'editNoticeBoard', 'title' : 'Update Notice'}";
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    $download = "";

    if(!empty($data->attachment)):
        $download = '<a href="'.base_url('assets/uploads/notice_board/'.$data->attachment).'" target="_blank" datatip="Download" class="btn btn-info waves-effect waves-light" download ><i class="fa fa-arrow-down"></i></a>';
    endif;
    
	$action = getActionButton($download.$editButton.$deleteButton);
    return [$action,$data->sr_no,$data->title,$data->description,formatDate($data->valid_from_date),formatDate($data->valid_to_date)];
}

/* Increment Policy Table Data */
function getIncrementPolicyData($data){
	$deleteParam = "{'postData':{'id' : ".$data->id."},'message':'Increment Policy'}";
    $applybtn=""; $editButton=""; $deleteButton="";
    if(empty($data->status)):
		$applyParam = "{'postData':{'id' : ".$data->policy_no.", 'status' : 1},'fnsave':'policyApply','message':'Are you sure want to Apply this Policy?'}";
        $applybtn = '<a class="btn btn-instagram permission-modify" href="javascript:void(0)" datatip="Apply" flow="down" onclick="confirmStore('.$applyParam.');"><i class="fas fa-check"></i></a>';

        $editButton = '<a href="'.base_url('hr/incrementPolicy/edit/'.$data->policy_no).'" class="btn btn-success btn-edit permision-modify" datatip="Edit" flow="down"><i class="mdi mdi-square-edit-outline"></i></a>';
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    endif;
   
	$action = getActionButton($applybtn.$editButton.$deleteButton);
    return [$action,$data->sr_no,formatDate($data->effect_date),$data->policy_no,$data->policy_name,formatDate($data->ref_month)];
}

function getFacilityData($data){
	$deleteParam = "{'postData':{'id' : ".$data->id.",'msg':'Facility'},'message':'Facility','fndelete':'deleteFacility'}";
	$editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'modal-lg', 'form_id' : 'editFacility', 'title' : 'Update Facility','fnsave' : 'saveFacility','call_function' : 'editFacility'}";
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
    $action = getActionButton($editButton.$deleteButton);
    
    return [$action,$data->sr_no,formatDate($data->entry_date),'['.$data->emp_code.'] '.$data->emp_name,$data->ficility_type,$data->amount,$data->reason];
}

function getPenaltyData($data){
	$deleteParam = "{'postData':{'id' : ".$data->id.",'msg':'Penalty'},'message':'Penalty','fndelete':'deleteFacility'}";
	$editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'modal-lg', 'form_id' : 'editPenalty', 'title' : 'Update Penalty','fnsave' : 'savePenalty','call_function' : 'editPenalty'}";
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
    $action = getActionButton($editButton.$deleteButton);
    
    return [$action,$data->sr_no,formatDate($data->entry_date),'['.$data->emp_code.'] '.$data->emp_name,$data->amount,$data->reason];
}

/* Payroll Table Data */
function getPayrollData($data){
	$deleteParam = "{'postData':{'sal_month' : '".$data->sal_month."','dept_id':".$data->dept_id."},'message':'Payroll'}";
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="'.base_url('hr/payroll/edit/'.$data->sal_month).'" datatip="Edit" flow="down"><i class="mdi mdi-square-edit-outline" ></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
	$mnth = '<a href="'.base_url('hr/payroll/getPayrollData/'.$data->sal_month).'" target="_blank">'.date("F-Y",strtotime($data->sal_month)).'</a>';
    return [$action,$data->sr_no,date("F-Y",strtotime($data->sal_month)),$data->salary_sum];
}
?>