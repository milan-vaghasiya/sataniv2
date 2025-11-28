<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

function getPurchaseDtHeader($page){

    /* Purchase Enquiry Header */
    $data['purchaseEnquiry'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['purchaseEnquiry'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
	$data['purchaseEnquiry'][] = ["name"=>"Enquiry No."];
    $data['purchaseEnquiry'][] = ["name"=>"Enquiry Date"];
    $data['purchaseEnquiry'][] = ["name"=>"Supplier Name"];
    $data['purchaseEnquiry'][] = ["name"=>"Item Description"];
    $data['purchaseEnquiry'][] = ["name"=>"Qty"];
    $data['purchaseEnquiry'][] = ["name"=>"Quotation Qty"];
    $data['purchaseEnquiry'][] = ["name"=>"Quotation Price"];
    $data['purchaseEnquiry'][] = ["name"=>"Quotation Date"];
    $data['purchaseEnquiry'][] = ["name"=>"Status","class"=>"text-center no_filter","sortable"=>FALSE];
    $data['purchaseEnquiry'][] = ["name"=>"Remark"];

    /* Purchase Order Header */
    $data['purchaseOrders'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['purchaseOrders'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
	$data['purchaseOrders'][] = ["name"=>"PO. No."];
	$data['purchaseOrders'][] = ["name"=>"PO. Date"];
	$data['purchaseOrders'][] = ["name"=>"Party Name"];
	$data['purchaseOrders'][] = ["name"=>"Item Name"];
    $data['purchaseOrders'][] = ["name"=>"Delivery On"];
    $data['purchaseOrders'][] = ["name"=>"Order Qty"];
    $data['purchaseOrders'][] = ["name"=>"Received Qty"];
    $data['purchaseOrders'][] = ["name"=>"Pending Qty"];
    $data['purchaseOrders'][] = ["name"=>"Remark"];
	
	/* Purchase Request Header */
    $data['purchaseRequest'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['purchaseRequest'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
    $data['purchaseRequest'][] = ["name"=>"Indent No"];
    $data['purchaseRequest'][] = ["name"=>"Indent Date"];
    $data['purchaseRequest'][] = ["name"=>"Item Name"];
    $data['purchaseRequest'][] = ["name"=>"Req. Qty"];    
    $data['purchaseRequest'][] = ["name"=>"Delivery Date"];
    $data['purchaseRequest'][] = ["name"=>"Remark"];
    $data['purchaseRequest'][] = ["name"=>"Status"];

     /* Purchase Indent Header */
    $masterCheckBox = '<input type="checkbox" id="masterSelect" class="filled-in chk-col-success BulkRequest" value=""><label for="masterSelect">ALL</label>';
    
    $data['purchaseIndent'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['purchaseIndent'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
    $data['purchaseIndent'][] = ["name"=>$masterCheckBox,"style"=>"width:10%;","textAlign"=>"center","orderable"=>"false"];
    $data['purchaseIndent'][] = ["name"=>"Indent No"];
	$data['purchaseIndent'][] = ["name"=>"Indent Date"];
    $data['purchaseIndent'][] = ["name"=>"Item Name"];
    $data['purchaseIndent'][] = ["name"=>"Req. Qty"];    
    $data['purchaseIndent'][] = ["name"=>"Delivery Date"];
    $data['purchaseIndent'][] = ["name"=>"Remark"];
    $data['purchaseIndent'][] = ["name"=>"Status"];

    return tableHeader($data[$page]);
}

/* Purchase Enquiry Data */
function getPurchaseEnquiryData($data){
    $deleteParam = "{'postData':{'id' : ".$data->trans_main_id."},'message' : 'Purchase Enquiry'}";

    $enqComplete = "";$edit = "";$delete = "";$approve="";$reject="";$order="";
    if($data->trans_status == 0):
        $reject = '<a href="javascript:void(0)" class="btn btn-success approvePEnquiry permission-modify" data-id="'.$data->id.'" data-val="3" data-msg="Reject" datatip="Reject Enquiry" flow="down"><i class="fa fa-window-close"></i></a>';    

        $enqComplete = '<a href="javascript:void(0)" class="btn btn-info btn-complete enquiryConfirmed permission-modify" data-id="'.$data->trans_main_id.'" data-party="'.$data->party_name.'" data-enqno="'.$data->trans_number.'" data-enqdate="'.date("d-m-Y",strtotime($data->trans_date)).'" data-button="both" data-modal_id="modal-xl" data-function="getEnquiryData" data-form_title="Purchase Enquiry Quotation" datatip="Quotation" flow="down"><i class="fa fa-check"></i></a>';

        $delete = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

        $edit = '<a class="btn btn-success btn-edit permission-modify" href="'.base_url('purchaseEnquiry/edit/'.$data->trans_main_id).'" datatip="Edit" flow="down" ><i class="mdi mdi-square-edit-outline"></i></a>';
   
    elseif($data->trans_status == 1):
        $approve = '<a href="javascript:void(0)" class="btn btn-facebook approvePEnquiry permission-modify" data-id="'.$data->id.'" data-val="2" data-msg="Approve" datatip="Approve Enquiry" flow="down"><i class="fa fa-check"></i></a>';

        $reject = '<a href="javascript:void(0)" class="btn btn-success approvePEnquiry permission-modify" data-id="'.$data->id.'" data-val="3" data-msg="Reject" datatip="Reject Enquiry" flow="down"><i class="fa fa-window-close"></i></a>';   
      
    elseif($data->trans_status == 2):
        $order = '<a href="'.base_url('purchaseOrders/createOrder/'.$data->trans_main_id).'" class="btn btn-info btn-edit permission-write" datatip="Create Order" flow="down"><i class="fas fa-file"></i></a>';
    endif; 

    $cnDate = (!empty($data->quote_date))?formatDate($data->quote_date):"";

    $action = getActionButton($order.$approve.$reject.$enqComplete.$edit.$delete);
    return [$action,$data->sr_no,$data->trans_number,formatDate($data->trans_date),$data->party_name,htmlentities($data->item_name),$data->qty,$data->confirm_qty,$data->confirm_rate, $cnDate,$data->status,$data->item_remark];
}

function getPurchaseOrderData($data){
    $shortClose =""; $editButton="";  $deleteButton =""; $printBtn ="";
   
    if($data->trans_status == 0):
        $shortCloseParam = "{'postData':{'id' : ".$data->po_trans_id.", 'trans_status' : 2},'fnsave':'changeOrderStatus','message':'Are you sure want to Short Close this Purchase Order?'}";
        $shortClose = '<a class="btn btn-instagram permission-modify" href="javascript:void(0)" datatip="Short Close" flow="down" onclick="confirmStore('.$shortCloseParam.');"><i class="mdi mdi-close-circle-outline"></i></a>';    

        if (floatval($data->dispatch_qty) == 0):
            $editButton = '<a class="btn btn-success btn-edit permission-modify" href="'.base_url('purchaseOrders/edit/'.$data->id).'" datatip="Edit" flow="down" ><i class="mdi mdi-square-edit-outline"></i></a>';
    
            $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Purchase Order'}";
            $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
        endif;
    endif;
    $printBtn = '<a class="btn btn-success btn-info" href="'.base_url('purchaseOrders/printPO/'.$data->id).'" target="_blank" datatip="Print" flow="down"><i class="fas fa-print" ></i></a>';

    $delivery_on = '';
    if($data->schedule_type == 1){
        $delivery_on = (!empty($data->delivery_date)) ? formatDate($data->delivery_date) : '';
    }else{
        $delivery_on = (!empty($data->sch_label)) ? $data->sch_label : '';
    }
    
    

    $action = getActionButton($shortClose.$printBtn.$editButton.$deleteButton);
    return [$action,$data->sr_no,$data->trans_number,$data->trans_date,$data->party_name,$data->item_name,$delivery_on,$data->qty,$data->dispatch_qty,round(($data->qty - $data->dispatch_qty),2),$data->item_remark];
}

/* Purchase Request Data  */
function getPurchaseRequestData($data){
    $shortClose =""; $editButton="";  $deleteButton ="";
    if($data->order_status == 1):
        $shortCloseParam = "{'postData':{'id' : ".$data->id.", 'order_status' : 3},'fnsave':'changeReqStatus','message':'Are you sure want to Short Close this Purchase Request?'}";
        $shortClose = '<a class="btn btn-instagram permission-modify" href="javascript:void(0)" datatip="Short Close" flow="down" onclick="confirmStore('.$shortCloseParam.');"><i class="mdi mdi-close-circle-outline"></i></a>';    

        $editParam = "{'postData':{'id' : ".$data->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'editPurchaeRequest', 'title' : 'Update PurchaeRequest','call_function':'edit'}";
        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i></a>';
    
        $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Purchase Request'}";
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    endif;
  
    $action = getActionButton($shortClose.$editButton.$deleteButton);
   return [$action,$data->sr_no,$data->trans_number,$data->trans_date,$data->item_name,$data->qty.' ('.$data->unit_name.')',formatDate($data->delivery_date),$data->remark,$data->order_status_label];
}

/* Purchase Indent Data  */
function getPurchaseIndentData($data){
    $shortClose=""; $selectBox ="";
    if($data->order_status == 1):
		$selectBox = '<input type="checkbox" name="ref_id[]" id="ref_id_'.$data->sr_no.'" class="filled-in chk-col-success BulkRequest" value="'.$data->id.'"><label for="ref_id_'.$data->sr_no.'"></label>';   

        $shortCloseParam = "{'postData':{'id' : ".$data->id.", 'order_status' : 3},'fnsave':'changeReqStatus','message':'Are you sure want to Short Close this Purchase Request?'}";
        $shortClose = '<a class="btn btn-instagram permission-modify" href="javascript:void(0)" datatip="Short Close" flow="down" onclick="confirmStore('.$shortCloseParam.');"><i class="mdi mdi-close-circle-outline"></i></a>';    
	endif;

    $action = getActionButton($shortClose);
    return [$action,$data->sr_no,$selectBox,$data->trans_number,$data->trans_date,$data->item_name,$data->qty.' ('.$data->unit_name.')',formatDate($data->delivery_date),$data->remark,$data->order_status_label];
}
?>