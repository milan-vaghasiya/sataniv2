<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/* get Pagewise Table Header */
function getConfigDtHeader($page){
    /* terms header */
    $data['terms'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['terms'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['terms'][] = ["name"=>"Title"];
    $data['terms'][] = ["name"=>"Type"];
    $data['terms'][] = ["name"=>"Conditions"];

    /* Transport Header*/
    $data['transport'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['transport'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['transport'][] = ["name"=>"Transport Name"];
    $data['transport'][] = ["name"=>"Transport ID"];
    $data['transport'][] = ["name"=>"Address"];

    /* HSN Master header */
    $data['hsnMaster'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['hsnMaster'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['hsnMaster'][] = ["name"=>"HSN"];
    $data['hsnMaster'][] = ["name"=>"CGST"];
    $data['hsnMaster'][] = ["name"=>"SGST"];
    $data['hsnMaster'][] = ["name"=>"IGST"];
    $data['hsnMaster'][] = ["name"=>"Description"];

    /* Material Grade header */
    $data['materialGrade'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['materialGrade'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['materialGrade'][] = ["name"=>"Material Grade"];
    $data['materialGrade'][] = ["name"=>"Standard"];
    $data['materialGrade'][] = ["name"=>"Scrap Group"];
    $data['materialGrade'][] = ["name"=>"Colour Code"];

    /* Scrap Group Header*/
    $data['scrapGroup'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['scrapGroup'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['scrapGroup'][] = ["name"=>"Scrap Group Name"];
    $data['scrapGroup'][] = ["name"=>"Unit Name"];

    /* Vehicle Type header */
    $data['vehicleType'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['vehicleType'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['vehicleType'][] = ["name"=>"Vehicle Type"];
    $data['vehicleType'][] = ["name"=>"Remark"];

    /* Tax Master Header */
    $data['taxMaster'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['taxMaster'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['taxMaster'][] = ["name" => "Tax Name"];
    $data['taxMaster'][] = ["name" => "Tax Type"];
    $data['taxMaster'][] = ["name" => "Calcu. Type"];
    $data['taxMaster'][] = ["name" => "Ledger Name"];
    $data['taxMaster'][] = ["name" => "Is Active"];
    $data['taxMaster'][] = ["name" => "Add/Deduct"];

    /* Expense Master Header */
    $data['expenseMaster'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['expenseMaster'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['expenseMaster'][] = ["name" => "Exp. Name"];
    $data['expenseMaster'][] = ["name" => "Entry Name"];
    $data['expenseMaster'][] = ["name" => "Sequence"];
    $data['expenseMaster'][] = ["name" => "Calcu. Type"];
    $data['expenseMaster'][] = ["name" => "Ledger Name"];
    $data['expenseMaster'][] = ["name" => "Is Active"];
    $data['expenseMaster'][] = ["name" => "Add/Deduct"];

    /* Rejection Type Header */
    $data['rejectionType'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
    $data['rejectionType'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['rejectionType'][] = ["name" => "Rejection Type"];
    
    /* Rejection Parameter Header */
    $data['rejectionParameter'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
    $data['rejectionParameter'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['rejectionParameter'][] = ["name" => "Rejection Parameter"];
    
    /* Shift Header */
    $data['shift'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['shift'][] = ["name"=>"#","style"=>"width:5%;"];
	$data['shift'][] = ["name"=>"Shift Name"];
	$data['shift'][] = ["name"=>"Start Time"];
	$data['shift'][] = ["name"=>"End Time"];
	$data['shift'][] = ["name"=>"Production Time"];
	$data['shift'][] = ["name"=>"Lunch Time"];
	$data['shift'][] = ["name"=>"Shift Hour"];

    /* Attendance Policy Header */
    $data['attendancePolicy'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['attendancePolicy'][] = ["name"=>"#","style"=>"width:5%;"];
	$data['attendancePolicy'][] = ["name"=>"Policy Name"];
	$data['attendancePolicy'][] = ["name"=>"Policy Type"];
    $data['attendancePolicy'][] = ["name"=>"Max./Day"];
    $data['attendancePolicy'][] = ["name"=>"Max./Month"];
    $data['attendancePolicy'][] = ["name"=>"Penalty"];
    $data['attendancePolicy'][] = ["name"=>"Penalty Hours"];

    /* Holidays Header */
    $data['holidays'][] = ["name"=>"Action","style"=>"width:5%;"];
	$data['holidays'][] = ["name"=>"#","style"=>"width:5%;"];
	$data['holidays'][] = ["name"=>"Holiday Date"];
    $data['holidays'][] = ["name"=>"Holiday Type"];
    $data['holidays'][] = ["name"=>"Title"];

    return tableHeader($data[$page]);
}

/* Terms Table Data */
function getTermsData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Terms'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editTerms', 'title' : 'Update Terms','call_function':'edit','txt_editor' : 'conditions'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->title,str_replace(',',', ',$data->type),$data->conditions];
}

/* Transport Data */
function getTransportData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Transport'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editTransport', 'title' : 'Update Transport','call_function':'edit'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->transport_name,$data->transport_id,$data->address];
}

/* HSN Master Table Data */
function getHSNMasterData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'HSN Master'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editHsnMaster', 'title' : 'Update HSN Master','call_function':'edit'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->hsn,$data->cgst,$data->sgst,$data->igst,$data->description];
}

/* Material Grade Table Data */
function getMaterialData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Material Grade'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editMaterialGrade', 'title' : 'Update Material Grade','call_function':'edit'}";

    $editButton = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->material_grade,$data->standard,$data->group_name,$data->color_code];
}

/* Scrap Group Data */
function getScrapGroupData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Scrap Group'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editScrap', 'title' : 'Update Scrap Group','call_function':'edit'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->item_name,$data->unit_name];
}

/* Vehicle Type Data */
function getVehicleTypeData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Vehicle Type'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editVehicleType', 'title' : 'Update Vehicle Type','call_function':'edit'}";

    $editButton = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->vehicle_type,$data->remark];
}

/* Tax Master Table Data */
function getTaxMasterData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Tax'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editTax', 'title' : 'Update Tax','call_function':'edit'}";
    
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    $deleteButton = "";

    $action = getActionButton($editButton.$deleteButton);    

    return [$action,$data->sr_no,$data->name,$data->tax_type_name,$data->calc_type_name,$data->acc_name,$data->is_active_name,$data->add_or_deduct_name];
}

/* Expense Master Table Data */
function getExpenseMasterData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Expense'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editExpense', 'title' : 'Update Expense','call_function':'edit'}";
    

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    $action = getActionButton($editButton.$deleteButton);    

    return [$action,$data->sr_no,$data->exp_name,$data->entry_name,$data->seq,$data->calc_type_name,$data->party_name,$data->is_active_name,$data->add_or_deduct_name];
}

/* Rejection Type Data */
function getRejectionTypeData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Rejection Type'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editRejectionType', 'title' : 'Update Rejection Type','call_function':'edit'}";

    $editButton = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->rejection_type];
}

/* Rejection Parameter Data */
function getRejectionParameterData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Rejection Parameter'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editRejectionParameter', 'title' : 'Update Rejection Parameter','call_function':'edit'}";

    $editButton = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->parameter];
}

/* get Shift Data */
function getShiftData($data){
   $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Shift'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editShift', 'title' : 'Update Shift'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="fas fa-edit" ></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="fas fa-trash"></i></a>';

	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->shift_name,$data->shift_start,$data->shift_end,$data->production_hour,$data->total_lunch_time,$data->total_shift_time];
}

/* get Attendance Policy Data */
function getAttendancePolicyData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Attendance Policy'}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editAttendancePolicy', 'title' : 'Update Attendance Policy'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="fas fa-edit" ></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="fas fa-trash"></i></a>';

	$action = getActionButton($editButton.$deleteButton);

    return [$action,$data->sr_no,$data->policy_name,$data->policy_type,$data->minute_day.$data->min_lbl,$data->day_month.' <small>Days</small>',$data->penalty_lbl,$data->penalty_hrs.' <small>Hours</small>'];
}

/* get Holidays Data */
function getHolidaysData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."}}";
    $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editHolidays', 'title' : 'Update Holidays'}";
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="fas fa-edit" ></i></a>';

    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="fas fa-trash"></i></a>';

	$action = getActionButton($editButton.$deleteButton);
    $holidayType = ($data->holiday_type == "1")?"Public Holiday":"Special Holiday";
    return [$action,$data->sr_no,formatdate($data->holiday_date),$holidayType,$data->title];
}
?>