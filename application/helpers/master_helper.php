<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/* get Pagewise Table Header */
function getMasterDtHeader($page){
    /* Customer Header */
    $data['customer'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE,"style"=>"width:5%;"];
	$data['customer'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE,"style"=>"width:5%;"]; 
	$data['customer'][] = ["name"=>"Company Name"];
	$data['customer'][] = ["name"=>"Contact Person"];
    $data['customer'][] = ["name"=>"Contact No."];
    $data['customer'][] = ["name"=>"Party Code"];
    $data['customer'][] = ["name"=>"Currency"];

    /* Supplier Header */
    $data['supplier'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE,"style"=>"width:5%;"];
	$data['supplier'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE,"style"=>"width:5%;"]; 
	$data['supplier'][] = ["name"=>"Company Name"];
	$data['supplier'][] = ["name"=>"Contact Person"];
    $data['supplier'][] = ["name"=>"Contact No."];
    $data['supplier'][] = ["name"=>"Party Code"];
    $data['supplier'][] = ["name"=>"Assessment"];

    /* Vendor Header */
    $data['vendor'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE,"style"=>"width:5%;"];
	$data['vendor'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE,"style"=>"width:5%;"]; 
	$data['vendor'][] = ["name"=>"Company Name"];
	$data['vendor'][] = ["name"=>"Contact Person"];
    $data['vendor'][] = ["name"=>"Contact No."];
    $data['vendor'][] = ["name"=>"Party Address"];

    /* Ledger Header */
    $data['ledger'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE,"style"=>"width:5%;"];
	$data['ledger'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE,"style"=>"width:5%;"];
    $data['ledger'][] = ["name"=>"Ledger Name"];
    $data['ledger'][] = ["name"=>"Group Name"];
    $data['ledger'][] = ["name"=>"Op. Balance"];
    $data['ledger'][] = ["name"=>"Cl. Balance"];

    /* Item Category Header */
    $data['itemCategory'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE,"style"=>"width:5%;"];
	$data['itemCategory'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE,"style"=>"width:5%;"];
    $data['itemCategory'][] = ["name"=>"Category Name"];
    $data['itemCategory'][] = ["name"=>"Parent Category"];
    $data['itemCategory'][] = ["name"=>"Is Final ?"];
    $data['itemCategory'][] = ["name"=>"Remark"];

    /* Finish Goods Header */
    $data['finish_goods'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE,"style"=>"width:5%;"];
	$data['finish_goods'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE,"style"=>"width:5%;"];
    $data['finish_goods'][] = ["name"=>"Item Code"];
    $data['finish_goods'][] = ["name"=>"Item Name"];
    $data['finish_goods'][] = ["name"=>"Category"];
    $data['finish_goods'][] = ["name"=>"Unit"];
    $data['finish_goods'][] = ["name"=>"HSN Code"];
    $data['finish_goods'][] = ["name"=>"GST (%)"];
    $data['finish_goods'][] = ["name"=>"Price"];
	
    /* Row Material Header */
    $data['raw_material'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE,"style"=>"width:5%;"];
	$data['raw_material'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE,"style"=>"width:5%;"];
    $data['raw_material'][] = ["name"=>"Item Code"];
    $data['raw_material'][] = ["name"=>"Item Name"];
    $data['raw_material'][] = ["name"=>"Category"];
    $data['raw_material'][] = ["name"=>"Unit"];
    $data['raw_material'][] = ["name"=>"HSN Code"];
    $data['raw_material'][] = ["name"=>"GST (%)"];
    $data['raw_material'][] = ["name"=>"Price"];

    /* Consumable Header */
    $data['consumable'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE,"style"=>"width:5%;"];
	$data['consumable'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE,"style"=>"width:5%;"];
    $data['consumable'][] = ["name"=>"Item Code"];
    $data['consumable'][] = ["name"=>"Item Name"];
    $data['consumable'][] = ["name"=>"Category"];
    $data['consumable'][] = ["name"=>"Unit"];
    $data['consumable'][] = ["name"=>"HSN Code"];
    $data['consumable'][] = ["name"=>"GST (%)"];
    $data['consumable'][] = ["name"=>"Price"];
    
    /* Machine Master Header */
    $data['machineries'][] = ["name"=>"Action","class"=>"no_filter noExport","sortable"=>FALSE,"textAlign"=>"center"];
	$data['machineries'][] = ["name"=>"#","class"=>"no_filter","sortable"=>FALSE,"style"=>"width:3%;","textAlign"=>"center"];
    $data['machineries'][] = ["name"=>"Machine Code"];
    $data['machineries'][] = ["name"=>"Machine Name"];
    $data['machineries'][] = ["name"=>"Category"];
    $data['machineries'][] = ["name"=>"Unit"];
    $data['machineries'][] = ["name"=>"Price"];
    $data['machineries'][] = ["name"=>"HSN Code"];
    $data['machineries'][] = ["name"=>"GST (%)"];

	/* Countries Table Header */
    $data['country'][] = ["name"=>"Action","sortable"=>"FALSE","textAlign"=>"center"];
    $data['country'][] = ["name"=>"#","sortable"=>"FALSE","textAlign"=>"center"];
    $data['country'][] = ["name"=>"Country"];

    /* states Table Header */
    $data['states'][] = ["name"=>"Action","sortable"=>"FALSE","textAlign"=>"center"];
    $data['states'][] = ["name"=>"#","sortable"=>"FALSE","textAlign"=>"center"];
    $data['states'][] = ["name"=>"States"];

    /* cities Table Header */
    $data['cities'][] = ["name"=>"Action","sortable"=>"FALSE","textAlign"=>"center"];
    $data['cities'][] = ["name"=>"#","sortable"=>"FALSE","textAlign"=>"center"];
    $data['cities'][] = ["name"=>"Country"];
	$data['cities'][] = ["name"=>"States"];
	$data['cities'][] = ["name"=>"Cities"];

    /** Custom Field Data */
    $data['customField'][] = ["name"=>"Action","sortable"=>"FALSE","textAlign"=>"center"];
    $data['customField'][] = ["name"=>"#","sortable"=>"FALSE","textAlign"=>"center"]; 
    $data['customField'][] = ["name"=>"Field"];
    $data['customField'][] = ["name"=>"Field Type"];

    /* Custom Option Header */
    $data['customOption'][] = ["name"=>"Action","sortable"=>"FALSE","textAlign"=>"center"];
    $data['customOption'][] = ["name"=>"#","sortable"=>"FALSE","textAlign"=>"center"]; 
    $data['customOption'][] = ["name"=>"Type"];
    $data['customOption'][] = ["name"=>"Title"];

    return tableHeader($data[$page]);
}

function getPartyData($data){
    
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : '".(($data->table_status!=4)?"bs-right-lg-modal":"bs-right-md-modal")."', 'form_id' : 'edit".$data->party_category_name."', 'title' : 'Update ".$data->party_category_name."','call_function':'edit'}";
    $editButton = '<a class="btn btn-warning btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : '".$data->party_category_name."'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    /* $approvalParam = "{'id' : ".$data->id.", 'modal_id' : 'modal-md', 'form_id' : 'partyApproval', 'title' : 'Party Approval', 'fnEdit' : 'partyApproval', 'fnsave' : 'savePartyApproval'}";
    $approvalButton = '<a class="btn btn-info btn-approval permission-approve" href="javascript:void(0)" datatip="Party Approval" flow="down" onclick="modalAction('.$approvalParam.');"><i class="fa fa-check" ></i></a>'; */

    $assessmentbtn = '';
    if($data->party_category == 2){
        $assessment = "{'postData':{'id' : ".$data->id."},'modal_id' : 'modal-md', 'form_id' : 'supplierAssessment', 'title' : 'Supplier Assessment','call_function':'supplierAssessment','fnsave':'saveAssessment'}";
        $assessmentbtn = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Assessment" flow="down" onclick="modalAction('.$assessment.');"><i class="mdi mdi-file-document"></i></a>';
    }

    $action = getActionButton($assessmentbtn.$editButton.$deleteButton);

    if($data->table_status == 1):
        $responseData = [$action,$data->sr_no,$data->party_name,$data->contact_person,$data->party_mobile,$data->party_code,$data->currency];
    elseif($data->table_status == 2):
        
        $assessment_file = "";
        if(!empty($data->assessment_file)):
            $assessment_file = '<a href="'.base_url('assets/uploads/assessment_file/'.$data->assessment_file).'" target="_blank"><i class="fa fa-download" aria-hidden="true"></i></a>';
        endif;
        
        $responseData = [$action,$data->sr_no,$data->party_name,$data->contact_person,$data->party_mobile,$data->party_code,$assessment_file];
    elseif($data->table_status == 3):
        $responseData = [$action,$data->sr_no,$data->party_name,$data->contact_person,$data->party_mobile,$data->party_address];
    else:
        
        if($data->system_code != ""):
            $editButton = $deleteButton = "";
        endif;

        if(in_array($data->group_code,["SC","SD"])):
            $editButton = $deleteButton = "";
        endif;

        $action = getActionButton($editButton.$deleteButton);
        $responseData = [$action,$data->sr_no,$data->party_name,$data->group_name,$data->op_balance,$data->cl_balance];
    endif;

    return $responseData;
}

function getItemCategoryData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Item Category'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editItemCategory', 'title' : 'Update Item Category','call_function':'edit'}";

    $editButton=''; $deleteButton='';
	if(!empty($data->ref_id) && $data->is_system == 0):
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    endif;

    $cat_code ='';
	if($data->ref_id ==6 || $data->ref_id == 7):
        $cat_code = (!empty($data->tool_type))?'['.str_pad($data->tool_type,3,'0',STR_PAD_LEFT).'] ':'';
    endif;

    if($data->final_category == 0):
        $data->category_name = $cat_code.'<a href="' . base_url("itemCategory/list/" . $data->id) . '">' . $data->category_name . '</a>';
    else:
        $data->category_name = $cat_code.$data->category_name;
    endif;

    $action = getActionButton($editButton.$deleteButton);

    return [$action,$data->sr_no,$data->category_name,$data->parent_category_name,$data->is_final_text,$data->remark];
}

function getProductData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : '".$data->item_type_text."'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editItem', 'title' : 'Update ".$data->item_type_text."','call_function':'edit'}";
    $revisionParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'itemRevision', 'title' : 'Set Item Revision','call_function':'addItemRevision','button':'close','fnedit':'addItemRevision'}";
    
    $inspectionParam = "{'postData':{'id' : ".$data->id.",'item_type' : ".$data->item_type."},'modal_id' : 'bs-right-xl-modal', 'form_id' : 'inspection', 'title' : 'Set Inspection','call_function':'addInspection','button':'close','fnedit':'addInspection'}";
    $editButton = "";$deleteButton="";$revisionButton ="";$inspectionButton="";
    
    if($data->item_type == 1){
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
        $revisionButton  = '<a class="btn btn-primary permission-modify" href="javascript:void(0)" datatip="Item Revision" flow="down" onclick="modalAction('.$revisionParam.');"><i class="fa fa-retweet"></i></a>';
        $inspectionButton  = '<a class="btn btn-info permission-modify" href="javascript:void(0)" datatip="Inspection" flow="down" onclick="modalAction('.$inspectionParam.');"><i class="mdi mdi-file-check"></i></a>';
    }elseif($data->item_type == 3){
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
        
        if($data->is_inspection == 1){
            $inspectionButton  = '<a class="btn btn-info permission-modify" href="javascript:void(0)" datatip="Inspection" flow="down" onclick="modalAction('.$inspectionParam.');"><i class="mdi mdi-file-check"></i></a>';
        }
    }else{
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    }
    $item_code = $data->item_code;
    if($data->item_type == 1){
        $item_code = '<a href="'.base_url('items/getProductDetails/'.$data->id).'" datatip="Reference Data" flow="down">'.$data->item_code.'</a>';
    }
    
    $action = getActionButton($inspectionButton.$revisionButton.$editButton.$deleteButton);

    
    return [$action,$data->sr_no,$item_code,$data->item_name,$data->category_name,$data->unit_name,$data->hsn_code,floatVal($data->gst_per),floatVal($data->price)];
}

/* Countries Table Data */
function getCountriesData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Cusstom Field','fndelete':'delete'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'right_modal', 'form_id' : 'editcustomField', 'title' : 'Update Field Option','fnsave':'save','fnedit':'edit'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="edit('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    $action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->name];
}

/* State Table Data */
function getStatesData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Cusstom Field','fndelete':'deleteState'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'right_modal', 'form_id' : 'editState', 'title' : 'Update Field Option','fnsave':'saveState','fnedit':'editState'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="edit('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    $action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->name];
}

/* Cities Table Data */
function getCitiesData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Cities','fndelete':'deleteCities'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';


    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editProcess', 'title' : 'Update Process','call_function':'editCities','fnsave':'saveCities'}";
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
    
    $action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->country_name,$data->state_name,$data->name];
}

/* Custom Field Table Data */
function getCustomFieldData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Cusstom Field','fndelete':'deleteCustomField'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editcustomField', 'title' : 'Update Field Option','fnedit':'editCustomField'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton);
    return [$action,$data->sr_no,$data->field_name,$data->field_type];
}

/* Custom Option Table Data */
function getCustomOptionData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Custom Option'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editCustomOption', 'title' : 'Update Custom Option'}";

    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->field_name,$data->title];
}
?>