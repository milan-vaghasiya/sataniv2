<?php
class SalesInvoice extends MY_Controller{
    private $indexPage = "sales_invoice/index";
    private $form = "sales_invoice/form";    
    private $packingPrintForm = "sales_invoice/packing_print_form";

    public function __construct(){
		parent::__construct();
		$this->data['headData']->pageTitle = "Sales Invoice";
		$this->data['headData']->controller = "salesInvoice";        
        $this->data['headData']->pageUrl = "salesInvoice";
        $this->data['entryData'] = $this->transMainModel->getEntryType(['controller'=>'salesInvoice']);
	}

    public function index(){
        $this->data['tableHeader'] = getAccountingDtHeader("salesInvoice");
        $this->load->view($this->indexPage,$this->data);
    }

    public function getDTRows($status = 0){
        $data = $this->input->post();$data['status'] = $status;
        $data['entry_type'] = $this->data['entryData']->id;
        $result = $this->salesInvoice->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):
            $row->sr_no = $i++;
            $sendData[] = getSalesInvoiceData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function addInvoice(){
        $this->data['entry_type'] = $this->data['entryData']->id;
        $this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
        $this->data['trans_no'] = $this->data['entryData']->trans_no;
        $this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>"1,2"]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>1]);
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
        $this->data['salesAccounts'] = $this->party->getPartyList(['system_code'=>$this->salesTypeCodes]);
		$this->data['taxList'] = $this->taxMaster->getActiveTaxList(2);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(2);
        $this->data['termsList'] = $this->terms->getTermsList(['type'=>'Sales']);
		$this->data['ledgerList'] = $this->party->getPartyList(['group_code'=>["'DT'","'ED'","'EI'","'ID'","'II'"]]);
        $this->data['transportList'] = $this->transport->getTransportList();
        $this->load->view($this->form,$this->data);
    }	

    public function save(){
        $data = $this->input->post();
        $errorMessage = array();

        if(empty($data['party_id']))
            $errorMessage['party_id'] = "Party Name is required.";
        if(empty($data['sp_acc_id']))
            $errorMessage['sp_acc_id'] = "GST Type is required.";
        if(empty($data['masterDetails']['i_col_1']))
            $errorMessage['master_i_col_1'] = "Bill Per. is required.";
        if(empty($data['itemData'])):
            $errorMessage['itemData'] = "Item Details is required.";
        else:
            $bQty = array();
            foreach($data['itemData'] as $key => $row):
                if($row['stock_eff'] == 1):
                    $postData = ['location_id' => $this->RTD_STORE->id,'batch_no' => "GB",'item_id' => $row['item_id'],'stock_required'=>1,'single_row'=>1];
                    
                    $stockData = $this->itemStock->getItemStockBatchWise($postData);  
                    $batchKey = "";
                    $batchKey = $row['item_id'];
                    
                    $stockQty = (!empty($stockData->qty))?floatVal($stockData->qty):0;
                    if(!empty($row['id'])):
                        $oldItem = $this->salesInvoice->getSalesInvoiceItem(['id'=>$row['id']]);
                        $stockQty = $stockQty + $oldItem->qty;
                    endif;
                    
                    if(!isset($bQty[$batchKey])):
                        $bQty[$batchKey] = $row['qty'] ;
                    else:
                        $bQty[$batchKey] += $row['qty'];
                    endif;

                    if(empty($stockQty)):
                        $errorMessage['qty'.$key] = "Stock not available.";
                    else:
                        if($bQty[$batchKey] > $stockQty):
                            $errorMessage['qty'.$key] = "Stock not available.";
                        endif;
                    endif;
                endif;
            endforeach;
        endif;
	
        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $data['vou_name_l'] = $this->data['entryData']->vou_name_long;
            $data['vou_name_s'] = $this->data['entryData']->vou_name_short;
            $this->printJson($this->salesInvoice->save($data));
        endif;
    }

    public function edit($id){
        $this->data['dataRow'] = $dataRow = $this->salesInvoice->getSalesInvoice(['id'=>$id,'itemList'=>1]);
        $this->data['gstinList'] = $this->party->getPartyGSTDetail(['party_id' => $dataRow->party_id]);
        $this->data['partyList'] = $this->party->getPartyList(['party_category' => "1,2"]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>1]);
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
        $this->data['salesAccounts'] = $this->party->getPartyList(['system_code'=>$this->salesTypeCodes]);
		$this->data['taxList'] = $this->taxMaster->getActiveTaxList(2);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(2);
        $this->data['termsList'] = $this->terms->getTermsList(['type'=>'Sales']);
        $this->data['ledgerList'] = $this->party->getPartyList(['group_code'=>["'DT'","'ED'","'EI'","'ID'","'II'"]]);
        $this->data['transportList'] = $this->transport->getTransportList();
        $this->load->view($this->form,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->salesInvoice->delete($id));
        endif;
    }

    public function printInvoice($jsonData=""){
        if(!empty($jsonData)):
            $postData = (Array) decodeURL($jsonData);
        else: 
            $postData = $this->input->post();
        endif;
        
        $printTypes = array();
        if(!empty($postData['original'])):
            $printTypes[] = "ORIGINAL";
        endif;

        if(!empty($postData['duplicate'])):
            $printTypes[] = "DUPLICATE";
        endif;

        if(!empty($postData['triplicate'])):
            $printTypes[] = "TRIPLICATE";
        endif;

        if(!empty($postData['extra_copy'])):
            for($i=1;$i<=$postData['extra_copy'];$i++):
                $printTypes[] = "EXTRA COPY";
            endfor;
        endif;

        $postData['header_footer'] = (!empty($postData['header_footer']))?1:0;
        $this->data['header_footer'] = $postData['header_footer'];
        $print_format = (!empty($postData['print_format']) ? 'sales_invoice/print_'.$postData['print_format'] : 'sales_invoice/print');

        $inv_id = (!empty($id))?$id:$postData['id'];

		$this->data['invData'] = $invData = $this->salesInvoice->getSalesInvoice(['id'=>$inv_id,'itemList'=>1]);
		$this->data['partyData'] = $this->party->getParty(['id'=>$invData->party_id]);
        $this->data['taxList'] = $this->taxMaster->getActiveTaxList(2);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(2);
		$this->data['companyData'] = $companyData = $this->masterModel->getCompanyInfo();
        $this->data['descriptionSetting'] = $this->masterModel->getAccountSettings();
		$this->data['termsData'] = (!empty($invData->termsConditions) ? $invData->termsConditions: "");
		$response="";
		$logo = (!empty($companyData->print_header))?base_url("assets/uploads/company_logo/".$companyData->company_logo):'';
        $this->data['letter_head'] =  (!empty($companyData->print_header))?base_url("assets/uploads/company_logo/".$companyData->print_header):base_url('assets/images/letterhead_top.png');
				
        $pdfData = "";
        $countPT = count($printTypes); $i=0;
        foreach($printTypes as $printType):
            ++$i;           
            $this->data['printType'] = $printType;
            $this->data['maxLinePP'] = (!empty($postData['max_lines']))?$postData['max_lines']:10;
		    $pdfData .= $this->load->view($print_format,$this->data,true);
            if($i != $countPT): $pdfData .= "<pagebreak>"; endif;
        endforeach;
            
		$mpdf = new \Mpdf\Mpdf();
		$pdfFileName = str_replace(["/","-"," "],"_",$invData->trans_number).'.pdf';
        $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css?v='.time()));
		$mpdf->SetTitle($pdfFileName); 
        $mpdf->WriteHTML($stylesheet,1);
		$mpdf->SetDisplayMode('fullpage');
		if(!empty($logo))
		{
		    $mpdf->SetWatermarkImage($logo,0.03,array(120,45));
		    $mpdf->showWatermarkImage = true;
		}
		//$mpdf->SetProtection(array('print'));
		$mpdf->AddPage('P','','','','',10,5,(($postData['header_footer'] == 1)?5:35),35,5,5,'','','','','','','','','','A4-P');
		$mpdf->WriteHTML($pdfData);
		$mpdf->Output($pdfFileName,'I');
	}

    public function getPartyBillPer(){
        $data = $this->input->post();
        $result = $this->salesInvoice->getPartyBillPer($data);
        $this->printJson(['status'=>1,'bill_per'=>((!empty($result))?$result->bill_per:100),'data'=>$result]);
    }

    public function getPartyItemPrice(){
        $data = $this->input->post();
        $result = $this->salesInvoice->getPartyItemPrice($data);
        $this->printJson(['status'=>1,'price'=>((!empty($result))?$result->price:0),'org_price'=>((!empty($result))?$result->org_price:0),'data'=>$result]);
    }    

    public function getPartyInvoiceItems(){
        $data = $this->input->post();
        $this->data['orderItems'] = $this->salesInvoice->getPendingInvoiceItems($data);
        $this->load->view('credit_note/create_creditnote',$this->data);
    }
}
?>