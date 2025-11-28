<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

function getProductionDtHeader($page){

    /* Process Header */
    $data['process'][] = ["name"=>"Action","class"=>"no_filter noExport","sortable"=>FALSE,"textAlign"=>"center"];
	$data['process'][] = ["name"=>"#","class"=>"no_filter","sortable"=>FALSE,"style"=>"width:3%;","textAlign"=>"center"];
    $data['process'][] = ["name"=>"Process Name"];
    $data['process'][] = ["name"=>"Remark"];

    /* Rejection Header */
    $data['rejectionComments'][] = ["name"=>"Action","class"=>"no_filter noExport","sortable"=>FALSE,"textAlign"=>"center"];
	$data['rejectionComments'][] = ["name"=>"#","class"=>"no_filter","sortable"=>FALSE,"style"=>"width:3%;","textAlign"=>"center"];
    $data['rejectionComments'][] = ["name"=>"Code"];
    $data['rejectionComments'][] = ["name"=>"Reason"];

    /* Estimation & Design Header */
    $data['estimation'][] = ["name"=>"Action","style"=>"width:5%;","sortable"=>"FALSE","textAlign"=>"center"];
	$data['estimation'][] = ["name"=>"#","style"=>"width:5%;","sortable"=>"FALSE","textAlign"=>"center"]; 
	$data['estimation'][] = ["name"=>"Job No."];
	$data['estimation'][] = ["name"=>"Job Date"];
	$data['estimation'][] = ["name"=>"Customer Name"];
	$data['estimation'][] = ["name"=>"Item Name"];
    $data['estimation'][] = ["name"=>"Order Qty"];
    $data['estimation'][] = ["name"=>"Bom Status"];
    $data['estimation'][] = ["name"=>"Priority"];
    $data['estimation'][] = ["name"=>"FAB. PRODUCTION NOTE"];
    $data['estimation'][] = ["name"=>"POWER COATING NOTE"];
    $data['estimation'][] = ["name"=>"ASSEMBALY NOTE"];
    $data['estimation'][] = ["name"=>"GENERAL NOTE"];

    /* Job Card Header */
    $data['jobcard'][] = ["name"=>"Action","class"=>"no_filter noExport","sortable"=>FALSE,"textAlign"=>"center"];
	$data['jobcard'][] = ["name"=>"#","class"=>"no_filter","sortable"=>FALSE,"style"=>"width:3%;","textAlign"=>"center"];
    $data['jobcard'][] = ["name"=>"Job No.","style"=>"width:9%;","textAlign"=>"center"];
    $data['jobcard'][] = ["name"=>"Job Date","textAlign"=>"center"];
    $data['jobcard'][] = ["name"=>"Customer","textAlign"=>"center"];
    $data['jobcard'][] = ["name"=>"Product","textAlign"=>"center"];
    $data['jobcard'][] = ["name"=>"Order Qty.","textAlign"=>"center"];
    $data['jobcard'][] = ["name"=>"Status","textAlign"=>"center"];
    $data['jobcard'][] = ["name"=>"Remark"];
    $data['jobcard'][] = ["name"=>"Last Activity"];

    /** Outsource */
    $data['outsource'][] = ["name" => "Action", "style" => "width:5%;", "textAlign" => "center", "srnoPosition" => ''];
    $data['outsource'][] = ["name" => "#", "style" => "width:4%;", "textAlign" => "center"];
    $data['outsource'][] = ["name" => "Challan Date", "textAlign" => "center"];
    $data['outsource'][] = ["name" => "Challan No.", "style" => "width:9%;", "textAlign" => "center"];
    $data['outsource'][] = ["name" => "Batch No.", "style" => "width:9%;", "textAlign" => "center"];
    $data['outsource'][] = ["name" => "Vendor"];
    $data['outsource'][] = ["name" => "Product"];
    $data['outsource'][] = ["name" => "Process"];
    $data['outsource'][] = ["name" => "Challan Qty", "textAlign" => "center"];
    $data['outsource'][] = ["name" => "Received Qty", "textAlign" => "center"];
    $data['outsource'][] = ["name" => "Without Process Return", "textAlign" => "center"];
    $data['outsource'][] = ["name" => "Pending Qty", "textAlign" => "center"];

    /* Pending Rejection Review */
    $data['pendingReview'][] = ["name"=>"Action","class"=>"no_filter noExport","sortable"=>FALSE,"textAlign"=>"center"];
	$data['pendingReview'][] = ["name"=>"#","class"=>"no_filter","sortable"=>FALSE,"textAlign"=>"center"];
    $data['pendingReview'][] = ["name"=>"Batch No."];
    $data['pendingReview'][] = ["name"=>"Product"];
    $data['pendingReview'][] = ["name"=>"Date"];
    $data['pendingReview'][] = ["name"=>"Process"];
    $data['pendingReview'][] = ["name"=>"Machine/Vendor/Dept."];
    $data['pendingReview'][] = ["name"=>"Operator"];
    $data['pendingReview'][] = ["name"=>"Qty"];
    $data['pendingReview'][] = ["name"=>"Reviewed Qty"];
    $data['pendingReview'][] = ["name"=>"Pending Qty"];
    $data['pendingReview'][] = ["name"=>"Accepted By"];


    /* Pending Rejection Review */
    $data['rejectionReview'][] = ["name"=>"Action","class"=>"no_filter noExport","sortable"=>FALSE];
	$data['rejectionReview'][] = ["name"=>"#","class"=>"no_filter","sortable"=>FALSE];
    $data['rejectionReview'][] = ["name"=>"Batch No."];
    $data['rejectionReview'][] = ["name"=>"Product"];
    $data['rejectionReview'][] = ["name"=>"Process"];
    $data['rejectionReview'][] = ["name"=>"Decision Date"];
    $data['rejectionReview'][] = ["name"=>"Decision"];
    $data['rejectionReview'][] = ["name"=>"Reviewed Qty"];
    $data['rejectionReview'][] = ["name"=>"Rej/Rw Reason"];
    $data['rejectionReview'][] = ["name"=>"Rej. Parameter"];
    $data['rejectionReview'][] = ["name"=>"Rej/Rw Type"];
    $data['rejectionReview'][] = ["name"=>"Rej/Rw Stage"];
    $data['rejectionReview'][] = ["name"=>"Rej/Rw By"];
    $data['rejectionReview'][] = ["name"=>"Note"];
    $data['rejectionReview'][] = ["name"=>"Accepted By"];


    /*** Cutting Header */
    $data['cutting'][] = ["name" => "Action", "style" => "width:5%;", "textAlign" => "center"];
    $data['cutting'][] = ["name"=>"#","style"=>"width:4%;","textAlign"=>"center"];
    $data['cutting'][] = ["name"=>"Batch No.","textAlign"=>"center"];
    $data['cutting'][] = ["name"=>"PRC Date","textAlign"=>"center"];
    $data['cutting'][] = ["name"=>"Product","textAlign"=>"center"];
    $data['cutting'][] = ["name"=>"Plan Qty","textAlign"=>"center"];
    $data['cutting'][] = ["name"=>"Cutting Lenght","textAlign"=>"center"];
    $data['cutting'][] = ["name"=>"Cutting Dia.","textAlign"=>"center"];
    $data['cutting'][] = ["name"=>"Cut Weight","textAlign"=>"center"];
    $data['cutting'][] = ["name"=>"Remark","textAlign"=>"center"];

    /* Vendor Price Report */
    $data['vendorPrice'][] = ["name" => "Action", "style" => "width:5%;", "textAlign" => "center", "srnoPosition" => ''];
    $data['vendorPrice'][] = ["name"=>"#","style"=>"width:4%;","textAlign"=>"center"];
    $data['vendorPrice'][] = ["name"=>"Date"];
    $data['vendorPrice'][] = ["name"=>"Vendor"];
    $data['vendorPrice'][] = ["name"=>"Die Number"];
    $data['vendorPrice'][] = ["name"=>"Product"];
    $data['vendorPrice'][] = ["name"=>"Process"];
    $data['vendorPrice'][] = ["name"=>"Rate"];
    $data['vendorPrice'][] = ["name"=>"Rate Per Unit"];
    $data['vendorPrice'][] = ["name"=>"Apprve By"];
    return tableHeader($data[$page]);
}

/* Process Table Data */
function getProcessData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Process'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editProcess', 'title' : 'Update Process','call_function':'edit'}";
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->process_name,$data->remark];
}

/* Rejection Comment Table Data */
function getRejectionCommentData($data){
    $rejection_type = ($data->type == 1 ? "Rejection Reason": ($data->type == 2 ? "Idle Reason":"Rework Reason"));

    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : '".$rejection_type."'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editRejection', 'title' : 'Update  ".$rejection_type."','call_function':'edit'}";
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';

    $action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->code,$data->remark];
}

function getEstimationData($data){

    $soBomParam = "{'postData':{'trans_main_id' : ".$data->trans_main_id.",'trans_child_id':".$data->trans_child_id."},'modal_id' : 'modal-xxl', 'form_id' : 'addOrderBom', 'fnedit':'orderBom', 'fnsave':'saveOrderBom','title' : 'Order Bom','res_function':'resSaveOrderBom','js_store_fn':'customStore'}";
    $soBom = '<a class="btn btn-info btn-delete permission-write" href="javascript:void(0)" onclick="edit('.$soBomParam.');" datatip="SO Bom" flow="down"><i class="fa fa-database"></i></a>';

    $viewBomParam = "{'postData':{'trans_child_id':".$data->trans_child_id."},'modal_id' : 'modal-xl','fnedit':'viewOrderBom','title' : 'View Bom [Item Name : ".$data->item_name."]','button':'close'}";
    $viewBom = '<a class="btn btn-primary permission-read" href="javascript:void(0)" onclick="edit('.$viewBomParam.');" datatip="View Item Bom" flow="down"><i class="fa fa-eye"></i></a>';

    $reqParam = "{'postData':{'trans_child_id':".$data->trans_child_id.",'trans_number':'".$data->trans_number."','item_name':'".$data->item_name."'},'modal_id' : 'modal-xl', 'form_id' : 'addOrderBom', 'fnedit':'purchaseRequest', 'fnsave':'savePurchaseRequest','title' : 'Send Purchase Request'}";
    $reqButton = '<a class="btn btn-info btn-delete permission-write" href="javascript:void(0)" onclick="edit('.$reqParam.');" datatip="Purchase Request" flow="down"><i class="fa fa-paper-plane"></i></a>';

    $estimationParam = "{'postData':{'id':'".$data->id."','trans_child_id':".$data->trans_child_id.",'trans_main_id':'".$data->trans_main_id."'},'modal_id' : 'modal-xl', 'form_id' : 'estimation', 'fnedit':'addEstimation', 'fnsave':'saveEstimation','title' : 'Estimation & Design'}";
    $estimationButton = '<a class="btn btn-success permission-write" href="javascript:void(0)" onclick="edit('.$estimationParam.');" datatip="Estimation" flow="down"><i class="fa fa-plus"></i></a>';

    if($data->priority == 1):
        $data->priority_status = '<span class="badge badge-pill badge-danger m-1">'.$data->priority_status.'</span>';
    elseif($data->priority == 2):
        $data->priority_status = '<span class="badge badge-pill badge-warning m-1">'.$data->priority_status.'</span>';
    elseif($data->priority == 3):
        $data->priority_status = '<span class="badge badge-pill badge-info m-1">'.$data->priority_status.'</span>';
    endif;

    $data->bom_status = '<span class="badge badge-pill badge-'.(($data->bom_status == "Generated")?"success":"danger").' m-1">'.$data->bom_status.'</span>';

    $action = getActionButton($soBom.$viewBom.$reqButton.$estimationButton);

    return [$action,$data->sr_no,$data->job_number,$data->trans_date,$data->party_name,$data->item_name,$data->qty,$data->bom_status,$data->priority_status,$data->fab_dept_note,$data->pc_dept_note,$data->ass_dept_note,$data->remark];
}

/* Outsource Table Data */
function getOutsourceData($data){
    
    $logParam = "{'postData':{'id' : ".$data->prc_process_id.",'ref_trans_id':".$data->id.",'challan_id':".$data->challan_id.",'wt_nos':".$data->wt_nos.",'processor_id':".$data->party_id.",'challan_process':'".$data->challan_process."','process_by':'3'},'modal_id' : 'bs-right-lg-modal', 'call_function':'addLog', 'form_id' : 'addLog', 'title' : 'Receive Challan', 'js_store_fn' : 'customStore', 'fnsave' : 'saveLog','controller':'outsource','button':'close'}";
    $logBtn = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Receive Challan" flow="down" onclick="loadform('.$logParam.')"><i class=" fas fa-paper-plane"></i></a>';

    $pending_qty = $data->qty - ($data->ok_qty+$data->rej_qty+$data->without_process_qty);
    $deleteButton = "";
    if($pending_qty > 0){
        $deleteParam = "{'postData':{'id' : ".$data->challan_id."}}";
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    }
    
    $print = '<a href="'.base_url('outsource/outSourcePrint/'.$data->out_id).'" type="button" class="btn btn-primary" datatip="Print" flow="down" target="_blank"><i class="fas fa-print"></i></a>';

    $action = getActionButton($print.$logBtn.$deleteButton);

    return [$action,$data->sr_no,date('d-m-Y',strtotime($data->ch_date)),$data->ch_number,$data->prc_number,$data->party_name,$data->item_name,$data->process_names,floatVal($data->qty),floatVal($data->ok_qty+$data->rej_qty),floatval($data->without_process_qty),floatVal($pending_qty)];
}


/* Get Pending Rejection Review Data */
function getPendingReviewData($data){
    $acceptBtn = "";$okBtn = $rejBtn = $rwBtn = "";

    if(empty($data->accepted_by)){
        $acceptParam = "{'postData':{'id' : ".$data->id.",'accepted_by':1,'msg':'Accepted'},'fnsave':'acceptRejectionReview','message':'Are you sure want to Accept this Rejection?','controller':'rejectionReview'}";
        $acceptBtn = '<a class="btn btn-info permission-modify" href="javascript:void(0)" datatip="Accept Rejection" flow="down" onclick="confirmStore('.$acceptParam.');"><i class="mdi mdi-check"></i></a>'; 
    }else{
        $title = '[ Pending Decision : '.floatval($data->pending_qty).' ]';
        $okBtnParam = "{'postData':{'id' : " . $data->id . "} ,'modal_id' : 'bs-right-md-modal', 'form_id' : 'okOutWard', 'title' : 'Ok ".$title."','button' : 'both','call_function' : 'convertToOk','fnsave' : 'saveReview'}";
        $rejBtnParam = "{'postData':{'id' : " . $data->id . "} ,'modal_id' : 'bs-right-lg-modal', 'form_id' : 'rejOutWard', 'title' : 'Rejection ".$title." ','button' : 'both','call_function' : 'convertToRej','fnsave' : 'saveReview'}";
        $rwBtnParam = "{'postData':{'id' : " . $data->id . "} ,'modal_id' : 'bs-right-lg-modal', 'form_id' : 'rwOutWard', 'title' : 'Rework ".$title." ','button' : 'both','call_function' : 'convertToRw','fnsave' : 'saveReview'}";

        $okBtn = '<a  onclick="modalAction('. $okBtnParam . ')"  class="btn btn-success btn-edit permission-modify" datatip="Ok" flow="down"><i class="mdi mdi-check"></i></a>';
        $rejBtn = '<a onclick="modalAction(' . $rejBtnParam . ')"  class="btn btn-danger btn-edit permission-modify" datatip="Rejection" flow="down"><i class="mdi mdi-close"></i></a>';
        $rwBtn = '<a  onclick="modalAction('. $rwBtnParam . ')"  class="btn btn-info btn-edit permission-modify" datatip="Rework" flow="down"><i class=" fas fa-retweet"></i></a>';
    }
  
    $action = getActionButton($acceptBtn.$okBtn.$rejBtn.$rwBtn);
 
    $acceptBy = ((!empty($data->accepted_by))?($data->empName.' <br>'.date('d-m-Y H:i:s',strtotime($data->accepted_at))):'');
    return [$action,$data->sr_no,$data->prc_number,'['.$data->item_code.'] '.$data->item_name,formatDate($data->trans_date),$data->process_name,$data->processor_name,$data->emp_name,$data->rej_found,$data->review_qty,$data->pending_qty,$acceptBy];
}

/* Get Rejection Review Data */
function getRejectionReviewData($data){
    
    $deleteParam = "{'postData':{'id' : ".$data->id."},'fndelete':'deleteReview'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    
    $tagPrint = '';
    if($data->decision_type == 1){
        $tagPrint = '<a href="' . base_url('rejectionReview/printPRCRejLog/' . $data->id) . '" target="_blank" class="btn btn-sm btn-dark waves-effect waves-light mr-1" title="Rejection Tag"><i class="fas fa-print"></i></a>';
    }elseif($data->decision_type == 2){
        $tagPrint = '<a href="' . base_url('rejectionReview/printPRCMovement/' . $data->id) . '" target="_blank"  class="btn btn-sm btn-info waves-effect waves-light mr-2" title="Print"><i class="fas fa-print"></i></a>';
    }elseif($data->decision_type == 5){
        $tagPrint = '<a href="' . base_url('pos/printPRCLog/' . $data->log_id.'/'.floatval($data->qty)) . '" target="_blank"  class="btn btn-sm btn-info waves-effect waves-light mr-2" title="Print"><i class="fas fa-print"></i></a>';
    }
    $action = getActionButton($deleteButton.$tagPrint);
    $acceptBy = ((!empty($data->accepted_by))?($data->emp_name.' <br>'.date('d-m-Y H:i:s',strtotime($data->accepted_at))):'');
    return [$action,$data->sr_no,$data->prc_number,'['.$data->item_code.'] '.$data->item_name,$data->process_name,formatDate($data->created_at),$data->decision,$data->qty,$data->reason,$data->parameter,$data->rr_type,$data->rr_stage_name,$data->rr_by_name,$data->rr_comment,$acceptBy];
}

/* Cutting PRC Table Data */
function getCuttingData($data){
    $deleteButton = ""; $editButton=""; $startButton = ""; $logButton = "";
    if($data->status == 1){
        $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Process'}";
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    
        $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editPRC', 'title' : 'Update Cutting PRC','call_function':'editCutting','fnsave':'saveCutting'}";
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    
        $startParam = "{'postData':{'id' : ".$data->id."},'message' : 'Are you sure you want to start PRC ? once you start you can not edit or delete','fnsave':'startPRC'}";
        $startButton = '<a class="btn btn-primary btn-edit permission-modify" href="javascript:void(0)" datatip="Start" flow="down" onclick="confirmStore('.$startParam.');"><i class=" fas fa-play"></i></a>';
    }else{
        $logParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'addCuttingLog', 'title' : 'Cutting Log','call_function':'addCuttingLog','controller':'sopDesk','button':'close'}";
        $logButton = '<a class="btn btn-primary btn-edit permission-modify" href="javascript:void(0)" datatip="Log" flow="down" onclick="modalAction('.$logParam.');"><i class=" fas fa-paper-plane
        "></i></a>';
    }
    $mtParam = "{'postData':{'id' : ".$data->id.",'item_id':".$data->item_id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'prcMaterial', 'title' : 'Required Material For PRC', 'fnsave' : 'savePrcMaterial', 'js_store_fn' : 'store','call_function':'requiredMaterial','controller':'sopDesk'}";
    $materialBtn = '<a class="btn btn-info btn-edit permission-modify" href="javascript:void(0)" datatip="Required Material" flow="down" onclick="modalAction('.$mtParam.');"><i class="fas fa-clipboard-check"></i></a>';

    $mtParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'prcMaterial', 'title' : 'Material Detail', 'fnsave' : 'savePrcMaterial', 'js_store_fn' : 'store','call_function':'getMaterialDetail','controller':'sopDesk','button':'close'}";
    $issueMaterialBtn = '<a class="btn btn-info btn-edit permission-modify" href="javascript:void(0)" datatip="Material Detail" flow="down" onclick="modalAction('.$mtParam.');"><i class="fas fa-th"></i></a>';

    $print = '<a href="'.base_url('sopDesk/cuttingPrint/'.$data->id).'" type="button" class="btn btn-primary" datatip="Print" flow="down" target="_blank"><i class="fas fa-print"></i></a>';

	$action = getActionButton($logButton.$startButton.$issueMaterialBtn.$materialBtn.$print.$editButton.$deleteButton);
    return [$action,$data->sr_no,$data->prc_number,formatDate($data->prc_date),$data->item_name,floatval($data->prc_qty),floatval($data->cutting_length),floatval($data->cutting_dia),floatval($data->cut_weight),$data->job_instruction];
}


/* Production Opration Data */
function getVendorPriceData($data){
   
    $editButton='';$deleteButton="";$approveBtn="";$rejectBtn="";
    if(empty($data->status)){
		$editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editPrice', 'title' : 'Update Vendor Price','fnsave' : 'save','js_store_fn':'storePrice'}";
		$editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="editPrice('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    
        $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Price'}";
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    
        $approveBtn = '<a class="btn btn-primary permission-approve1" href="javascript:void(0)" onclick="approvePrice('.$data->id.');" datatip="Approve" flow="down"><i class="fa fa-check"></i></a>';
        
        $rejectBtn = '<a class="btn btn-dark btn-reject permission-modify" href="javascript:void(0)" onclick="rejectPrice('.$data->id.');" datatip="Reject" flow="down"><i class="mdi mdi-close"></i></a>';
    }
    
	$action = getActionButton($approveBtn.$rejectBtn.$editButton.$deleteButton);
    return [$action,$data->sr_no,date("d-m-Y",strtotime($data->created_at)),$data->party_name,$data->item_code,$data->item_name,$data->process_name,$data->rate,(($data->rate_unit == 1)?'Per Piece':'Per Kg'),$data->emp_name];
}
?>