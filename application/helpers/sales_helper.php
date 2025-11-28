<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

function getSalesDtHeader($page){

    /* Sales Enquiry Header */
    $data['salesEnquiry'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['salesEnquiry'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
    $data['salesEnquiry'][] = ["name"=>"SE. No."];
    $data['salesEnquiry'][] = ["name"=>"SE. Date"];
    $data['salesEnquiry'][] = ["name"=>"Customer Name"];
    $data['salesEnquiry'][] = ["name"=>"Item Name"];
    $data['salesEnquiry'][] = ["name"=>"Qty"];

    /* Sales Quotation Header */
    $data['salesQuotation'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['salesQuotation'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
	$data['salesQuotation'][] = ["name"=>"Rev. No.","textAlign"=>"center"];
	$data['salesQuotation'][] = ["name"=>"SQ. No."];
	$data['salesQuotation'][] = ["name"=>"SQ. Date"];
	$data['salesQuotation'][] = ["name"=>"Customer Name"];
	$data['salesQuotation'][] = ["name"=>"Item Name"];
    $data['salesQuotation'][] = ["name"=>"Qty"];
    $data['salesQuotation'][] = ["name"=>"Price"];
    $data['salesQuotation'][] = ["name"=>"Confirmed BY"];
    $data['salesQuotation'][] = ["name"=>"Confirmed Date"];

    /* Sales Order Header */
    $data['salesOrders'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['salesOrders'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
	$data['salesOrders'][] = ["name"=>"SO. No."];
	$data['salesOrders'][] = ["name"=>"SO. Date"];
	$data['salesOrders'][] = ["name"=>"Customer Name"];
	$data['salesOrders'][] = ["name"=>"Item Name"];
	$data['salesOrders'][] = ["name"=>"Stock Qty"];
    $data['salesOrders'][] = ["name"=>"Order Qty"];
    $data['salesOrders'][] = ["name"=>"Dispatch Qty"];
    $data['salesOrders'][] = ["name"=>"Pending Qty"];

    /* Party Order Header */
    $data['partyOrders'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['partyOrders'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
	$data['partyOrders'][] = ["name"=>"Order Status"];
	$data['partyOrders'][] = ["name"=>"SO. No."];
	$data['partyOrders'][] = ["name"=>"SO. Date"];
	$data['partyOrders'][] = ["name"=>"Item Name"];
	$data['partyOrders'][] = ["name"=>"Brand Name"];
    $data['partyOrders'][] = ["name"=>"Order Qty"];
    $data['partyOrders'][] = ["name"=>"Received Qty"];
    $data['partyOrders'][] = ["name"=>"Pending Qty"];

    /* Estimate [Cash] Header */
    $data['estimate'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['estimate'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
	$data['estimate'][] = ["name"=>"Inv No."];
	$data['estimate'][] = ["name"=>"Inv Date"];
	$data['estimate'][] = ["name"=>"Customer Name"];
	$data['estimate'][] = ["name"=>"Taxable Amount"];
    $data['estimate'][] = ["name"=>"Net Amount"];
    
    
    /* Delivery Challan Header */
    $data['deliveryChallan'][] = ["name"=>"Action","class"=>"text-center no_filter noExport","sortable"=>FALSE];
	$data['deliveryChallan'][] = ["name"=>"#","class"=>"text-center no_filter","sortable"=>FALSE]; 
	$data['deliveryChallan'][] = ["name"=>"DC. No."];
	$data['deliveryChallan'][] = ["name"=>"DC. Date"];
	$data['deliveryChallan'][] = ["name"=>"Customer Name"];
	$data['deliveryChallan'][] = ["name"=>"Remark"];
 
    return tableHeader($data[$page]);
}

/* Sales Enquiry Table data */
function getSalesEnquiryData($data){
    $quotationBtn=""; $editButton=""; $deleteButton=""; 
    if(empty($data->trans_status)):
        $editButton = '<a class="btn btn-success permission-modify" href="'.base_url('salesEnquiry/edit/'.$data->trans_main_id).'" datatip="Edit" flow="down" ><i class="mdi mdi-square-edit-outline"></i></a>';

        $deleteParam = "{'postData':{'id' : ".$data->trans_main_id."},'message' : 'Sales Enquiry'}";
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

        $quotationBtn = '<a href="'.base_url('salesQuotation/createQuotation/'.$data->trans_main_id).'" class="btn btn-primary permission-write" datatip="Create Quotation" flow="down"><i class="fa fa-file-alt"></i></a>';  
    endif;

    $action = getActionButton($quotationBtn.$editButton.$deleteButton);

    return [$action,$data->sr_no,$data->trans_number,formatDate($data->trans_date),$data->party_name,$data->item_name,floatVal($data->qty)];
}

/* Sales Quotation Table data */
function getSalesQuotationData($data){
    $editButton = $deleteButton = $confirm = "";
    if(empty($data->confirm_by)):
        $confirm = '<a href="javascript:void(0)" class="btn btn-info confirmQuotation permission-write" data-id="'.$data->trans_main_id.'" data-quote_id="'.$data->trans_main_id.'"  data-party="'.$data->party_name.'" data-customer_id="'.$data->party_id.'" data-quote_no="'.$data->trans_number.'" data-quotation_date="'.date("d-m-Y",strtotime($data->trans_date)).'" data-button="both" data-modal_id="modal-lg" data-function="getQuotationItems" data-form_title="Confirm Quotation" datatip="Confirm Quotation" flow="down"><i class="fa fa-check"></i></a>';

        $editButton = '<a class="btn btn-success btn-edit permission-modify" href="'.base_url('salesQuotation/edit/'.$data->trans_main_id).'" datatip="Edit" flow="down" ><i class="mdi mdi-square-edit-outline"></i></a>';

        $deleteParam = "{'postData':{'id' : ".$data->trans_main_id."},'message' : 'Sales Quotation'}";
        $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    endif;

    $revision = '<a href="'.base_url('salesQuotation/reviseQuotation/'.$data->trans_main_id).'" class="btn btn-primary btn-edit permission-modify" datatip="Revision" flow="down"><i class="fa fa-retweet"></i></a>';

    $orderBtn = '<a href="'.base_url('salesOrders/createOrder/'.$data->trans_main_id).'" class="btn btn-dark permission-write" datatip="Create Order" flow="down"><i class="fa fa-file-alt"></i></a>'; 

    $printBtn = '<a class="btn btn-dribbble btn-edit permission-approve" href="'.base_url('salesQuotation/printQuotation/'.$data->trans_main_id).'" target="_blank" datatip="Print" flow="down"><i class="fas fa-print" ></i></a>';

    $printRevisionBtn = '<a class="btn btn-facebook btn-edit permission-approve createSalesQuotation" datatip="View Revised Quatation" data-id="'.$data->trans_main_id.'" data-sq_no="'.$data->trans_number.'" flow="down"><i class="fas fa-eye" ></i></a>';
  
    if($data->trans_status == 2):
        $revision = $editButton = $deleteButton = $confirm = "";
    endif;

    $action = getActionButton($printBtn.$orderBtn.$confirm.$revision.$editButton.$deleteButton);

    $rev_no = sprintf("%02d",$data->quote_rev_no);
    
    if($data->quote_rev_no == 2):
        $revParam = "{'postData' : {'trans_number' : '".$data->trans_number."'}, 'modal_id' : 'modal-md', 'form_id' : 'revisionList', 'title' : 'Quotation Revision History','call_function':'revisionHistory','button':'close'}";
        $rev_no = '<a href="javascript:void(0)" datatip="Revision History" flow="down" onclick="modalAction('.$revParam.');">'.sprintf("%02d",$data->quote_rev_no).'</a>';
    endif;

    return [$action,$data->sr_no,$rev_no,$data->trans_number,formatDate($data->trans_date),$data->party_name,$data->item_name,$data->qty,$data->price,$data->confirm_by_name,((!empty($data->cod_date))?formatDate($data->cod_date):"")];
}

/* Sales Order Table data */
function getSalesOrderData($data){
    $editButton = '<a class="btn btn-warning btn-edit permission-modify" href="'.base_url('salesOrders/edit/'.$data->id).'" datatip="Edit" flow="down" ><i class="mdi mdi-square-edit-outline"></i></a>';

    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Sales Order'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    $printBtn = '<a class="btn btn-info btn-edit permission-approve1" href="'.base_url('salesOrders/printOrder/'.$data->id).'" target="_blank" datatip="Print" flow="down"><i class="fas fa-print" ></i></a>';

    $acceptButton = '';
    if($data->sales_executive == $data->party_id):
        $acceptButton = '<a class="btn btn-success btn-edit permission-modify" href="'.base_url('salesOrders/edit/'.$data->id.'/1').'" datatip="Accept Order" flow="down" ><i class="ti-check"></i></a>';
    endif;

    if($data->trans_status > 0):
        $acceptButton = $editButton = $deleteButton = "";
    endif;

    $action = getActionButton($printBtn.$acceptButton.$editButton.$deleteButton);

    return [$action,$data->sr_no,$data->trans_number,$data->trans_date,$data->party_name,$data->item_name,$data->stock_qty,$data->qty,$data->dispatch_qty,$data->pending_qty];
    //return [$action,$data->sr_no,$data->ordered_by,$data->trans_number,$data->trans_date,$data->party_name,$data->item_name,$data->brand_name,$data->stock_qty,$data->qty,$data->dispatch_qty,$data->pending_qty];
}

/* Party Order Table Data */
function getPartyOrderData($data){
    $action = getActionButton("");

    return [$action,$data->sr_no,$data->order_status,$data->trans_number,$data->trans_date,$data->item_name,$data->brand_name,$data->qty,$data->dispatch_qty,$data->pending_qty];
}

/* Estimate [Cash] Table Data */
function getEstimateData($data){
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="'.base_url('estimate/edit/'.$data->id).'" datatip="Edit" flow="down" ><i class="mdi mdi-square-edit-outline"></i></a>';

    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Estimate'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

    $printBtn = '<a class="btn btn-info btn-edit" href="'.base_url('estimate/printEstimate/'.$data->id).'" target="_blank" datatip="Print" flow="down"><i class="fas fa-print" ></i></a>';

    $paymentParam = "{'postData':{'id' : ".$data->id."},'modal_id':'modal-lg','form_id':'estimatePayment','title':'Payment','call_function':'estimatePayment','button':'close','res_function':'resSaveEstimatePayment'}";
    $paymentBtn = '<a class="btn btn-warning btn-edit permission-modify" href="javascript:void(0)" datatip="Payment" flow="down" onclick="modalAction('.$paymentParam.');"><i class="fas fa-rupee-sign"></i></a>';

    if($data->trans_no == 0):
        $editButton = $deleteButton = "";
    endif;

    $action = getActionButton($printBtn.$paymentBtn.$editButton.$deleteButton);

    return [$action,$data->sr_no,$data->trans_number,$data->trans_date,$data->party_name,$data->taxable_amount,$data->net_amount];
}

/* Delivery Challan Table Data */
function getDeliveryChallanData($data){
    $editButton = '<a class="btn btn-success btn-edit permission-modify" href="'.base_url('deliveryChallan/edit/'.$data->id).'" datatip="Edit" flow="down" ><i class="mdi mdi-square-edit-outline"></i></a>';

    $deleteParam = "{'postData':{'id' : ".$data->id."},'message' : 'Delivery Challan'}";
    $deleteButton = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';
    
    $printBtn = '<a class="btn btn-info btn-edit permission-approve1" href="'.base_url('deliveryChallan/printChallan/'.$data->id).'" target="_blank" datatip="Print" flow="down"><i class="fas fa-print" ></i></a>';

    if($data->trans_status > 0):
        $editButton = $deleteButton = "";
    endif;

    $action = getActionButton($printBtn.$editButton.$deleteButton);

    return [$action,$data->sr_no,$data->trans_number,formatDate($data->trans_date),$data->party_name,$data->remark];
}
?>