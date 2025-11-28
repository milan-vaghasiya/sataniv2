<?php
class PurchaseOrders extends MY_Controller{
    private $indexPage = "purchase_order/index";
    private $form = "purchase_order/form";

    public function __construct(){
		parent::__construct();
		$this->data['headData']->pageTitle = "Purchase Order";
		$this->data['headData']->controller = "purchaseOrders";        
        $this->data['headData']->pageUrl = "purchaseOrders";
        $this->data['entryData'] = $this->transMainModel->getEntryType(['controller'=>'purchaseOrders','tableName'=>'po_master']);
	}

    public function index(){
        $this->data['tableHeader'] = getPurchaseDtHeader("purchaseOrders");
        $this->load->view($this->indexPage,$this->data);
    }

    public function getDTRows($status = 0){
        $data = $this->input->post();$data['status'] = $status;
        $data['entry_type'] = $this->data['entryData']->id;
        $result = $this->purchaseOrder->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):
            $row->sr_no = $i++;
            $sendData[] = getPurchaseOrderData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function createOrder($id){  
        $dataRow = $this->purchaseEnquiry->getEnquiry($id);

        $dataRow->from_entry_type = $dataRow->entry_type;
        $dataRow->ref_id = $dataRow->id;
        $dataRow->entry_type = "";
        $dataRow->id = "";
        $dataRow->trans_prefix = "";
        $dataRow->trans_no = "";
        $dataRow->trans_number = "";

        $itemList = array();
        foreach($dataRow->itemData as $row):
            if($row->trans_status == 2):
                $row->from_entry_type = $row->entry_type;
                $row->ref_id = $row->id;
                $row->entry_type = "";
                $row->id = "";
                $row->price = $row->confirm_rate;
                $row->taxable_amount = $row->amount = round(($row->qty * $row->price),2);
                if(!empty($row->disc_per) && !empty($row->amount)):
                    $row->disc_amount = round((($row->disc_per * $row->amount) / 100),2);
                    $row->taxable_amount = $row->taxable_amount - $row->disc_amount;
                endif;

                $row->net_amount = $row->taxable_amount;
                if(!empty($row->taxable_amount) && !empty($row->gst_per)):
                    $row->gst_amount = round((($row->gst_per * $row->taxable_amount) / 100),2);

                    $row->igst_per = $row->gst_per;
                    $row->igst_amount = $row->gst_amount;

                    $row->cgst_per = round(($row->gst_per / 2),2);
                    $row->cgst_amount = round(($row->gst_amount / 2),2);
                    $row->sgst_per = round(($row->gst_per / 2),2);
                    $row->sgst_amount = round(($row->gst_amount / 2),2);

                    $row->net_amount = $row->taxable_amount + $row->gst_amount;
                endif;

                $itemList[] = $row;
            endif;
        endforeach;
        $dataRow->itemList = $itemList;
        
        $this->data['dataRow'] = $dataRow;
        $this->data['entry_type'] = $this->data['entryData']->id;
        $this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
        $this->data['trans_no'] = $this->data['entryData']->trans_no;
        $this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>"1,2"]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>"1,2,3"]);        
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
		$this->data['taxList'] = $this->taxMaster->getActiveTaxList(1);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(1);
        $this->data['termsTitleList'] = $this->terms->getTermsList(['type'=>'Purchase','multi_row'=>1]);
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['transportList'] = $this->transport->getTransportList();
        $this->data['companyList'] = $this->purchaseOrder->getCompanyList();
        $this->load->view($this->form,$this->data);
    }

    public function addOrder(){
        $this->data['entry_type'] = $this->data['entryData']->id;
        $this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
        $this->data['trans_no'] = $this->data['entryData']->trans_no;
        $this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>"1,2"]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>"1,2,3,5"]);
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
		$this->data['taxList'] = $this->taxMaster->getActiveTaxList(1);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(1);
        $this->data['termsTitleList'] = $this->terms->getTermsList(['type'=>'Purchase','multi_row'=>1]);
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['transportList'] = $this->transport->getTransportList();
        $this->data['companyList'] = $this->purchaseOrder->getCompanyList();
        $this->load->view($this->form,$this->data);
    }

    public function save(){
        $data = $this->input->post();
        $errorMessage = array();

        if(empty($data['party_id']))
            $errorMessage['party_id'] = "Party Name is required.";
        if(empty($data['itemData']))
            $errorMessage['itemData'] = "Item Details is required.";
        
        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
			$companyData = $this->purchaseOrder->getCompanyInfo($data['cm_id']);
			$data['trans_number'] = $data['trans_no'].'/'.date("dmy",strtotime($data['trans_date'])).'/'.$companyData->company_alias;
	        $data['doc_date'] = (!empty($data['doc_date']))? $data['doc_date'] : NULL;
            $data['vou_name_l'] = $this->data['entryData']->vou_name_long;
            $data['vou_name_s'] = $this->data['entryData']->vou_name_short;
            $this->printJson($this->purchaseOrder->save($data));
        endif;
    }

    public function edit($id){
        $this->data['dataRow'] = $dataRow = $this->purchaseOrder->getPurchaseOrder(['id'=>$id,'itemList'=>1]);
        $this->data['gstinList'] = $this->party->getPartyGSTDetail(['party_id' => $dataRow->party_id]);
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>"1,2"]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>"1,2,3,5"]);
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
		$this->data['taxList'] = $this->taxMaster->getActiveTaxList(1);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(1);
        $this->data['termsTitleList'] = $this->terms->getTermsList(['type'=>'Purchase','multi_row'=>1]);
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['transportList'] = $this->transport->getTransportList();
        $this->data['companyList'] = $this->purchaseOrder->getCompanyList();
        $this->load->view($this->form,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->purchaseOrder->delete($id));
        endif;
    }

    public function printPO($id){
		$this->data['dataRow'] = $poData = $this->purchaseOrder->getPurchaseOrder(['id'=>$id,'itemList'=>1,'item_group'=>1]);
		$this->data['poTrans'] = $poTrans = $this->purchaseOrder->getPurchaseOrderItems(['id'=>$id]);
		$this->data['partyData'] = $this->party->getParty(['id'=>$poData->party_id]);
        $this->data['taxList'] = $this->taxMaster->getActiveTaxList(1);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(1);
		$this->data['termsData'] = (!empty($poData->termsConditions) ? $poData->termsConditions: "");
		
		$cmid = (!empty($poData->cm_id)) ? $poData->cm_id : 1;
		$this->data['companyData'] = $companyData = $this->masterModel->getCompanyInfo($cmid);
		$response="";
		$logo=base_url('assets/images/'.$companyData->company_logo);
		$this->data['lht']= base_url('assets/images/letterhead/'.$companyData->lht_img);
		$this->data['lhb']= base_url('assets/images/letterhead/'.$companyData->lhb_img);
		

        $pdfData = $this->load->view('purchase_order/print',$this->data,true);
		
		$htmlHeader = '<img src="'.$this->data['lht'].'" class="img">';
		$htmlFooter = '<img src="'.$this->data['lhb'].'" class="img">';
		
		/*$htmlFooterSign = '<div style="position:fixed;bottom:100px;right:150px;z-index:999999;">For, '.$companyData->company_name.'</div>';
		$htmlFooterSign .= '<div style="position:fixed;bottom:60px;right:150px;">Authorised By</div>';
		$htmlFooter = '<img src="'.$this->data['lhb'].'" class="img">';

        $pdfData .=$htmlFooterSign;*/
        
		$mpdf = new \Mpdf\Mpdf();
		$pdfFileName = str_replace('/','-',$poData->trans_number.'_'.$poData->party_name).'.pdf';
		$mpdf->setTitle($poData->trans_number.'_'.$poData->party_name);
        $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css?v='.time()));
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->SetWatermarkImage($logo,0.03,array(120,70));
		$mpdf->showWatermarkImage = true;
		$mpdf->SetHTMLHeader($htmlHeader);
		$mpdf->SetHTMLFooter($htmlFooter);
		$mpdf->AddPage('P','','','','',0,0,38,15,0,0,'','','','','','','','','','A4-P');
		$mpdf->WriteHTML($pdfData);
		$mpdf->Output($pdfFileName,'I');
	}

    public function getPartyOrderItems(){
        $data = $this->input->post();
        $this->data['orderItems'] = $this->purchaseOrder->getPendingInvoiceItems($data);
        $this->load->view('purchase_invoice/create_po_invoice',$this->data);
    }

	/* Created By :- Sweta @15-09-2023 */
	public function nextPoNoByCmId(){
		$data = $this->input->post();
		$po_no = $this->purchaseOrder->nextPoNoByCmId($data);
		$this->printJson(['status'=>1, 'po_no'=>(!empty($po_no)?$po_no:'')]);
	}

	public function changeOrderStatus(){
        $postData = $this->input->post();
        if(empty($postData['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->purchaseOrder->changeOrderStatus($postData));
        endif;
    }
   
	public function addPOFromRequest($id){ 
        $this->data['req_id'] = $id;
        $this->data['entry_type'] = $this->data['entryData']->id;
        $this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
        $this->data['trans_no'] = $this->data['entryData']->trans_no;
        $this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>"1,2"]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>"1,2,3,5"]);
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
		$this->data['taxList'] = $this->taxMaster->getActiveTaxList(1);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(1);
        $this->data['termsTitleList'] = $this->terms->getTermsList(['type'=>'Purchase','multi_row'=>1]);
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['transportList'] = $this->transport->getTransportList();
        $this->data['companyList'] = $this->purchaseOrder->getCompanyList();
        $this->data['reqItemList'] = $this->purchaseIndent->getPurchaseRequestForOrder($id);
        $this->load->view($this->form,$this->data);
	}
}
?>