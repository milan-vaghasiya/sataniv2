<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/* get Pagewise Table Header */
function getStoreDtHeader($page){
    /* Location Master header */
    $data['storeLocation'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['storeLocation'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['storeLocation'][] = ["name"=>"Store Name"];
    $data['storeLocation'][] = ["name"=>"Location"];
    $data['storeLocation'][] = ["name"=>"Remark"];

    /* Gate Entry */
    $data['gateEntry'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['gateEntry'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['gateEntry'][] = ["name"=> "GE No.", "textAlign" => "center"];
    $data['gateEntry'][] = ["name" => "GE Date", "textAlign" => "center"];
    $data['gateEntry'][] = ["name" => "Transport"];
    $data['gateEntry'][] = ["name" => "LR No."];
    $data['gateEntry'][] = ["name" => "Vehicle Type"];
    $data['gateEntry'][] = ["name" => "Vehicle No."];
    $data['gateEntry'][] = ['name' => "Invoice No."];
    $data['gateEntry'][] = ['name' => "Invoice Date"];
    $data['gateEntry'][] = ['name' => "Challan No."];
    $data['gateEntry'][] = ['name' => "Challan Date"];

    /* Gate Inward Pending GE Tab Header */
    $data['pendingGE'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['pendingGE'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['pendingGE'][] = ["name"=> "GE No.", "textAlign" => "center"];
    $data['pendingGE'][] = ["name" => "GE Date", "textAlign" => "center"];
    $data['pendingGE'][] = ["name" => "Party Name"];
    $data['pendingGE'][] = ["name" => "Inv. No."];
    $data['pendingGE'][] = ["name" => "Inv. Date"];
    $data['pendingGE'][] = ['name' => "CH. NO."];
    $data['pendingGE'][] = ['name' => "CH. Date"];

    /* Gate Inward Pending/Compeleted Tab Header */
    $masterCheckBox = '<input type="checkbox" id="masterSelect" class="filled-in chk-col-success BulkTagPrint" value=""><label for="masterSelect">ALL</label>';

    $data['gateInward'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['gateInward'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['gateInward'][] = ["name"=>$masterCheckBox,"style"=>"width:10%;","textAlign"=>"center","orderable"=>"false"];
    $data['gateInward'][] = ["name"=> "GRN No.", "textAlign" => "center"];
    $data['gateInward'][] = ["name" => "GRN Date", "textAlign" => "center"];
    $data['gateInward'][] = ["name" => "Party Name"];
    $data['gateInward'][] = ["name" => "Item Name"];
    $data['gateInward'][] = ["name" => "Heat No"];
    $data['gateInward'][] = ["name" => "Batch No"];
    $data['gateInward'][] = ["name" => "GRN Qty"];
    $data['gateInward'][] = ["name" => "Ok Qty"];
    $data['gateInward'][] = ["name" => "Rej. Qty"];
    $data['gateInward'][] = ["name" => "Short Qty"];
    $data['gateInward'][] = ["name" => "PO. NO."]; 
    
    /* FG Stock Inward Table Header */
    $data['stockTrans'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['stockTrans'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['stockTrans'][] = ["name" => "Date"];
    $data['stockTrans'][] = ["name" => "Item Name"];
    $data['stockTrans'][] = ["name" => "Qty"];
    $data['stockTrans'][] = ["name" => "Remark"];

    /* LIST OF STOCK VERIFICATION  */
    $data['stockVerification'][] = ["name"=>"#","style"=>"width:5%;","textAlign"=>"center","srnoPosition"=>0];
    $data['stockVerification'][] = ["name"=>"Item Code."];
    $data['stockVerification'][] = ["name"=>"Item Name"];
    $data['stockVerification'][] = ["name"=>"Stock Register Qty."];
    $data['stockVerification'][] = ["name"=>"Action","style"=>"width:5%;","textAlign"=>"center"];

    /* Requisition Table Header */
    $data['requisition'][] = ["name" => "Action", "textAlign" => "center"];
    $data['requisition'][] = ["name" => "#", "textAlign" => "center"];
    $data['requisition'][] = ["name" => "Req. No."];
    $data['requisition'][] = ["name" => "Req. Date"];
    $data['requisition'][] = ["name" => "Item Name"];
    $data['requisition'][] = ["name" => "Req. Qty"];
    $data['requisition'][] = ["name" => "Issue Qty"];
    $data['requisition'][] = ["name" => "Finish Goods"];
    $data['requisition'][] = ["name" => "Urgency"];

    /* Return Requisition Table Header */
    $data['returnRequisition'][] = ["name" => "Action", "textAlign" => "center"];
    $data['returnRequisition'][] = ["name" => "#", "textAlign" => "center"];
    $data['returnRequisition'][] = ["name" => "Req Number"];
    $data['returnRequisition'][] = ["name" => "Req Date"];
    $data['returnRequisition'][] = ["name" => "Issue Number"];
    $data['returnRequisition'][] = ["name" => "Issue Date"];
    $data['returnRequisition'][] = ["name" => "Item Name"];
    $data['returnRequisition'][] = ["name" => "Issue Qty"];
    $data['returnRequisition'][] = ["name" => "Return Qty"];

    /* Pending Issue Requisition Table Header */
    $data['pendingRequisition'][] = ["name" => "Action", "textAlign" => "center"];
    $data['pendingRequisition'][] = ["name" => "#", "textAlign" => "center"];
    $data['pendingRequisition'][] = ["name" => "Req Number"];
    $data['pendingRequisition'][] = ["name" => "Req Date"];
    $data['pendingRequisition'][] = ["name" => "Item Name"];
    $data['pendingRequisition'][] = ["name" => "Req Qty"];
    $data['pendingRequisition'][] = ["name" => "Issue Qty"];
    $data['pendingRequisition'][] = ["name" => "Pending Qty"];
    $data['pendingRequisition'][] = ["name" => "Batch/PRC No"];
    $data['pendingRequisition'][] = ["name" => "Requested By"];

    /* Issued Requisition Table Header */
    $data['issueRequisition'][] = ["name" => "Action", "textAlign" => "center"];
    $data['issueRequisition'][] = ["name" => "#", "textAlign" => "center"];
    $data['issueRequisition'][] = ["name" => "Issue Number"];
    $data['issueRequisition'][] = ["name" => "Issue Date"];
    $data['issueRequisition'][] = ["name" => "Request No"];
    $data['issueRequisition'][] = ["name" => "Batch/PRC No"];
    $data['issueRequisition'][] = ["name" => "Item Name"];
    $data['issueRequisition'][] = ["name" => "Req. Qty"];
    $data['issueRequisition'][] = ["name" => "Issue Qty"];
    $data['issueRequisition'][] = ["name" => "Heat No"];
    $data['issueRequisition'][] = ["name" => "Issued To"];
    $data['issueRequisition'][] = ["name" => "Issued By"];

    /* Inspection Table Header */
    $data['inspection'][] = ["name" => "Action", "textAlign" => "center"];
    $data['inspection'][] = ["name" => "#", "textAlign" => "center"];
    $data['inspection'][] = ["name" => "Issue Number"];
    $data['inspection'][] = ["name" => "Date"];
    $data['inspection'][] = ["name" => "Item Name"];
    $data['inspection'][] = ["name" => "Total Qty"];
    $data['inspection'][] = ["name" => "Batch No"];
    $data['inspection'][] = ["name" => "Remark"];

    /* PRC Material Issue Table Header */
    $data['prcMaterialIssue'][] = ["name" => "Action", "textAlign" => "center"];
    $data['prcMaterialIssue'][] = ["name" => "#", "textAlign" => "center"];
    $data['prcMaterialIssue'][] = ["name" => "Batch No."];
    $data['prcMaterialIssue'][] = ["name" => "Batch Date"];
    $data['prcMaterialIssue'][] = ["name" => "GROUP"];
    $data['prcMaterialIssue'][] = ["name" => "Item"];
    $data['prcMaterialIssue'][] = ["name" => "Required Qty"];
    $data['prcMaterialIssue'][] = ["name" => "Issue Qty"];
    
    /* Stock Inward Table Header */
    $data['stockInward'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
    $data['stockInward'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['stockInward'][] = ["name" => "Item Name"];
    $data['stockInward'][] = ["name" => "Location"];
    $data['stockInward'][] = ["name" => "Qty"];
    
    return tableHeader($data[$page]);
}

/* Store Location Data */
function getStoreLocationData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Store Location'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editStoreLocation', 'title' : 'Update Store Location','call_function':'edit'}";

    $editButton = ''; $deleteButton = '';
    if(!empty($data->ref_id) && empty($data->store_type)):
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';

        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
	else:
		$editLocationParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'editLocationForm', 'title' : 'Update Store Location','call_function':'editStoreLocation', 'fnsave' : 'editLocation'}";
		
		$editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editLocationParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
		
		//$deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" 	datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    endif;

    if($data->final_location == 0):
        $locationName = '<a href="' . base_url("storeLocation/list/" . $data->id) . '">' . $data->location . '</a>';
    else:
        $locationName = $data->location;
    endif;
	
	$action = getActionButton($editButton.$deleteButton);
    return [$action,$data->sr_no,$data->store_name,$locationName,$data->remark];
}

/* Gate Entry Data  */
function getGateEntryData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Gate Entry'}";
    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editGateEntry', 'title' : 'Update Gate Entry','call_function':'edit'}";

    $editButton = "";
    $deleteButton = "";
    if($data->trans_status == 0):
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';

        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    endif;

    $action = getActionButton($editButton.$deleteButton);

    return [$action,$data->sr_no,$data->trans_number,formatDate($data->trans_date),$data->transport_name,$data->lr,$data->vehicle_type_name,$data->vehicle_no,$data->inv_no,((!empty($data->inv_date))?formatDate($data->inv_date):""),$data->doc_no,((!empty($data->doc_date))?formatDate($data->doc_date):"")];
}

/* GateInward Data Data  */
function getGateInwardData($data){
    $action = '';
    if($data->trans_type == 1): 
		//Pending GE Data
        $createGIParam = "{postData:{'id' : ".$data->id."}, 'modal_id' : 'bs-right-xl-modal', 'form_id' : 'addGateInward', 'title' : 'Gate Inward',fnsave: 'save','call_function':'createGI'}";
        $createGI = '<a class="btn btn-success btn-edit permission-write" href="javascript:void(0)" datatip="Create GI" flow="down" onclick="modalAction('.$createGIParam.');"><i class="fa fa-plus" ></i></a>';

        $action = getActionButton($createGI);

        return [$action,$data->sr_no,$data->trans_number,formatDate($data->trans_date),$data->party_name,$data->inv_no,$data->inv_date,$data->doc_no,$data->doc_date];
    else: 
		// Gate Inward Pending/Completed Data		
        $editButton = $inspection = $iirPrint = $iirInsp = $tcButton = $deleteButton = ""; 
        
		if($data->trans_status == 0):
		    $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-xl-modal', 'form_id' : 'editGateInward', 'title' : 'Update Gate Inward','call_function':'edit'}";
            $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
      
            $insParam = "{'postData':{'id' : ".$data->mir_trans_id."},'modal_id' : 'bs-right-md-modal', 'form_id' : 'materialInspection', 'title' : 'Qty Verification','call_function':'materialInspection','fnsave':'saveInspectedMaterial'}";
			$inspection = '<a href="javascript:void(0);" type="button" class="btn btn-warning permission-modify" datatip="Qty Verification" flow="down" onclick="modalAction('.$insParam.');"><i class="fas fa-search"></i></a>';
        endif;
		
		if($data->item_type == 3):
		    $testReport = "{'postData':{'id' : '".$data->mir_trans_id."','grn_id' : '".$data->id."','heat_no' : '".$data->heat_no."'}, 'button' : 'close', 'modal_id' : 'bs-right-xl-modal', 'form_id' : 'testReport', 'title' : 'Test Report', 'call_function' : 'getTestReport'}";
            $tcButton = '<a class="btn btn-dark btn-salary permission-modify" href="javascript:void(0)" datatip="Test Report" flow="down" onclick="modalAction('.$testReport.');"><i class="mdi mdi-file-multiple"></i></a>';
		
			$iirParam = "{'postData':{'id' : ".$data->mir_trans_id."},'modal_id' : 'bs-right-xl-modal', 'form_id' : 'iirInsp', 'title' : 'Dimensional Inspection','call_function':'getInwardQc','fnsave':'saveInwardQc'}";
			$iirInsp = '<a href="javascript:void(0)" type="button" class="btn btn-info permission-modify" datatip="Dimensional Inspection" flow="down" onclick="modalAction('.$iirParam.');"><i class="fa fa-file-alt"></i></a>';

			$iirPrint = '<a class="btn btn-dribbble btn-edit permission-modify" href="'.base_url($data->controller.'/inInspection_pdf/'.$data->mir_trans_id).'" target="_blank" datatip="Inspection Print" flow="down"><i class="fas fa-print" ></i></a>';
		endif;

        $selectBox = '';
        if($data->trans_status == 0){
            $iirTagPrint = '<a href="'.base_url('gateInward/ir_print/'.$data->mir_trans_id).'" type="button" class="btn btn-primary" datatip="Pending QC Tag" flow="down" target="_blank"><i class="fas fa-print"></i></a>';

            $selectBox = '<input type="checkbox" name="ref_id[]" id="ref_id_'.$data->sr_no.'" class="filled-in chk-col-success BulkTagPrint" value="'.$data->mir_trans_id.'"><label for="ref_id_'.$data->sr_no.'"></label>';
        }else{
            $iirTagPrint = '<a href="'.base_url('gateInward/printMaterialTag/'.$data->mir_trans_id).'" type="button" class="btn btn-primary" datatip="QC OK Tag" flow="down" target="_blank"><i class="fas fa-print"></i></a>';

            $selectBox = "<input type='checkbox' name='ref_id[]' id='ref_id_".$data->sr_no."' class='filled-in chk-col-success BulkTagPrint' value='".$data->mir_trans_id."' data-status='".$data->trans_status."'><label for='ref_id_".$data->sr_no."'></label>";
        }	    
        $grnPrint = '<a class="btn btn-success btn-info" href="'.base_url('gateInward/printGRN/'.$data->id).'" target="_blank" datatip="GRN Print" flow="down"><i class="fas fa-print" ></i></a>';

        $deleteParam = "{'postData':{'id' : ".$data->mir_trans_id.", 'mir_id' : ".$data->id."},'message' : 'Gate Inward'}";
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

	    $action = getActionButton($inspection.$iirTagPrint.$grnPrint.$tcButton.$iirInsp.$iirPrint.$editButton.$deleteButton);
        return [$action,$data->sr_no,$selectBox,$data->trans_number,formatDate($data->trans_date),$data->party_name,$data->item_name,$data->heat_no,$data->batchNo,$data->qty,$data->ok_qty,$data->reject_qty,$data->short_qty,$data->po_number];
    endif;
}

/* FG Stock Inward Table Data */
function getStockTransData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Stock'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    $action = getActionButton($deleteButton);

    return [$action,$data->sr_no,formatDate($data->ref_date),$data->item_name,$data->qty,$data->remark];
}

/* Stock Verification Table Data */
function getStockVerificationData($data){
 
    $editParam = "{'postData':{'id' : ".$data->id.",'item_id': ".$data->item_id."},'modal_id' : 'bs-right-xl-modal', 'form_id' : 'editStock', 'title' : 'Update Stock','call_function':'editStock','fnsave':'save'}";
    $editButton = '<a href="javascript:void(0)" type="button" class="btn btn-sm btn-success permission-modify" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';

    return [$data->sr_no,$data->item_code,$data->item_name,floatVal($data->stock_qty),$editButton];
}

/* Return Requisition Table Data */
function getReturnRequisitionData($data){
    $returnButton = '';
    $issue_qty = floatval($data->issue_qty);
    $return_qty = floatval($data->return_qty);
    if($issue_qty > $return_qty){
        $returnParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'modal-md', 'form_id' : 'closeRequisition', 'fnedit' : 'return', 'call_function' : 'return', 'title' : 'Return Material', 'fnsave' : 'saveReturnReq'}";
        $returnButton = '<a class="btn btn-warning btn-edit permission-modify" href="javascript:void(0)" datatip="Return" flow="down" onclick="modalAction('.$returnParam.');"><i class="fa fa-reply" ></i></a>';
    }

    $action = getActionButton($returnButton);
    return [$action,$data->sr_no,$data->trans_number,formatDate($data->trans_date),$data->issue_number,formatDate($data->issue_date),$data->item_name,floatval($data->issue_qty),floatval($data->return_qty)];
}

// 26-10-2024
/* Pending Requisition Table Data */
function getPendingRequisitionData($data) {
    $closeBtn = $issueBtn = "";
    if($data->status == 1) {  
        $closeParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'modal-md', 'form_id' : 'closeRequisition', 'fnedit' : 'close', 'call_function' : 'close', 'title' : 'Close Requisition', 'fnsave' : 'closeRequisition'}";
        $closeBtn = '<a class="btn btn-primary btn-edit permission-modify" href="javascript:void(0)" datatip="Close" flow="down" onclick="modalAction('.$closeParam.');"><i class="fa fa-close" ></i></a>';

        if($data->req_type == 2){
            $issueParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'issueRequisition', 'fnedit' : 'addMCIssueRequisition', 'call_function': 'addMCIssueRequisition', 'title' : 'Issue Requisition', 'fnsave' : 'saveIssueRequisition'}";
            $issueBtn = '<a class="btn btn-success" href="javascript:void(0)" onclick="modalAction(' . $issueParam . ');" datatip="Material Issue" flow="down"><i class="fas fa-paper-plane"></i></a>';
        }else{
            $issueParam = "{'postData':{'id' : ".$data->id.", 'trans_no' : '".$data->trans_no."'},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'issueRequisition', 'fnedit' : 'addIssueRequisition', 'call_function': 'addIssueRequisition', 'title' : 'Issue Requisition', 'fnsave' : 'saveIssueRequisition'}";
            $issueBtn = '<a class="btn btn-success" href="javascript:void(0)" onclick="modalAction(' . $issueParam . ');" datatip="Material Issue" flow="down"><i class="fas fa-paper-plane"></i></a>';
        }
    }

    $action = getActionButton($issueBtn.$closeBtn);
    return [$action,$data->sr_no,$data->trans_number,formatDate($data->trans_date),$data->item_name,abs($data->req_qty),abs($data->issue_qty),abs($data->pending_qty),$data->prc_number,$data->created_by_name];
}

/* Requisition Table Data */
function getRequisitionData($data){

    $editBtn = $deleteBtn = $closeBtn = "";

    if($data->status == 0) {

        if($data->issue_qty <= 0)
        {
            $editParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editRequisition', 'title' : 'Update Requisition', 'fnsave' : 'save'}";
            $editBtn = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline" ></i></a>';

            $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Requisition'}";
            $deleteBtn = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
        }

        $closeParam = "{'postData':{'id' : ".$data->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'closeRequisition', 'call_function' : 'close', 'fnedit' : 'close', 'title' : 'Close Requisition', 'fnsave' : 'closeRequisition'}";
        $closeBtn = '<a class="btn btn-primary btn-edit permission-modify" href="javascript:void(0)" datatip="Close" flow="down" onclick="modalAction('.$closeParam.');"><i class="fa fa-close" ></i></a>';
    }

    $action = getActionButton($closeBtn.$editBtn.$deleteBtn);
    
    if($data->urgency == 0){ $urgency = "Low"; }
    elseif($data->urgency == 1){ $urgency = "Medium"; }
    elseif($data->urgency == 2){ $urgency = "High"; }
    
    return [$action,$data->sr_no,$data->trans_number,formatDate($data->trans_date),$data->item_name,abs($data->req_qty),abs($data->issue_qty),$data->prc_number,$urgency];
}

/* Issue Requisition Table Data */
function getIssueRequisitionData($data){
    $deleteButton = "";$printBtn = "";$printBtn1="";
    $return_qty = floatval($data->return_qty);
    if(empty($return_qty) ){
        $deleteParam = "{'postData':{'id' : ".$data->id."}, 'fndelete' : 'deleteIssueRequisition','message' : 'Stock'}";
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    }
    if(!empty($data->prc_id)){
        $pUrl = encodeURL(['item_id'=>$data->item_id,'prc_id'=>$data->prc_id,'id'=>$data->id,'title'=>"MATERIAL TAG"]);
        
        $printParam = "{'postData':{'url' : '".$pUrl."'}, 'controller' : 'sopDesk', 'fnname' : 'getMaterialAcceptTag','message' : 'Print'}";
        //$printBtn = '<a class="btn btn-facebook btn-edit" href="javascript:void(0)" onclick="printTag('.$printParam.');" datatip="Material Tag Print" flow="down"><i class="fas fa-print" ></i></a>';
        $printBtn1 = '<a class="btn btn-dribbble btn-edit" href="'.base_url('pos/printMaterialAcceptTag/'.$pUrl).'" target="_blank" datatip="Material Tag Print" flow="down"><i class="fas fa-print" ></i></a>';
    }
    $action = getActionButton($printBtn.$printBtn1.$deleteButton);
    return [$action,$data->sr_no,$data->issue_number,formatDate($data->issue_date),$data->trans_number,$data->prc_number,$data->item_name,abs($data->req_qty),abs($data->issue_qty),$data->heat_no,$data->emp_name,$data->created_by_name];
}

/* Inspection Table Data */
function getInspectionData($data) {
    $inspectButton = "";
    if($data->trans_type == 1){
        $inspectParam = "{'postData':{'id' : ".$data->id.",'issue_id' : ".$data->issue_id."},'modal_id' : 'modal-md', 'fnedit' : 'addInspection', 'call_function':'addInspection','form_id' : 'addInspection', 'title' : 'Inspection', 'fnsave' : 'saveInspection'}";
        $inspectButton = '<a class="btn btn-warning btn-edit permission-modify" href="javascript:void(0)" datatip="Inspect" flow="down" onclick="modalAction('.$inspectParam.');"><i class="fa fa-search" ></i></a>';
    }
    $action = getActionButton($inspectButton);
    return [$action,$data->sr_no,$data->issue_number,formatDate($data->trans_date),$data->item_name,$data->total_qty,$data->batch_no,$data->remark];
}

/* Prc Material Issue Table Data */
function getPrcMaterialIssueData($data){
    $action = getActionButton("");
    return [$action,$data->sr_no,$data->prc_number,formatDate($data->prc_date),$data->bom_group,$data->item_name,$data->req_qty,$data->issue_qty];
}

/* Stock Inward Table Data */
function getStockInwardData($data){
    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Stock'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    $action = getActionButton($deleteButton);
    return [$action,$data->sr_no,$data->item_name,$data->location,$data->qty];
}
?>