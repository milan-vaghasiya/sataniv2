<?php
class Estimate extends MY_Controller{
    private $indexPage = "estimate/index";
    private $form = "estimate/form"; 
    private $paymentForm = "estimate/estimate_payment";
    private $estimate_ledger = "estimate/estimate_ledger";
    private $estimate_ledger_details = "estimate/estimate_ledger_details";

    public function __construct(){
		parent::__construct();
		$this->data['headData']->pageTitle = "Estimate";
		$this->data['headData']->controller = "estimate";        
        $this->data['headData']->pageUrl = "estimate";
        $this->data['entryData'] = $this->transMainModel->getEntryType(['controller'=>'estimate']);
	}

    public function index(){
        $this->data['tableHeader'] = getSalesDtHeader("estimate");
        $this->load->view($this->indexPage,$this->data);
    }

    public function getDTRows($status = 0){
        $data = $this->input->post();$data['status'] = $status;
        $data['entry_type'] = $this->data['entryData']->id;
        $result = $this->estimate->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):
            $row->sr_no = $i++;
            $sendData[] = getEstimateData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function addEstimate(){
        $this->data['entry_type'] = $this->data['entryData']->id;
        $this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
        $this->data['trans_no'] = $this->data['entryData']->trans_no;
        $this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>1]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>1]);
        $this->data['brandList'] = $this->brandMaster->getBrandList();
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
		$this->data['taxList'] = array();//$this->taxMaster->getActiveTaxList(2);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(2);
        $this->data['termsList'] = $this->terms->getTermsList(['type'=>'Sales']);
		$this->data['ledgerList'] = $this->party->getPartyList(["'DT'","'ED'","'EI'","'ID'","'II'"]);
        $this->load->view($this->form,$this->data);
    }

    public function save(){
        $data = $this->input->post();
        $errorMessage = array();

        if(empty($data['party_id']))
            $errorMessage['party_id'] = "Party Name is required.";
        if(empty($data['itemData'])):
            $errorMessage['itemData'] = "Item Details is required.";
        else:
            $bQty = array();
            foreach($data['itemData'] as $key => $row):
                /*if(!empty(floatVal($row['qty'])) && !empty($row['packing_qty'])):
                    if(is_int(($row['qty'] / $row['packing_qty'])) == false):
                        $errorMessage['qty'.$key] = "Invalid qty against packing standard.";
                    endif;
                endif;*/

                if($row['stock_eff'] == 1):
                    $postData = ['location_id' => $this->RTD_STORE->id,'batch_no' => $row['brand_name'],'item_id' => $row['item_id'],'stock_required'=>1,'single_row'=>1];
                    
                    $stockData = $this->itemStock->getItemStockBatchWise($postData); 
                    $batchKey = "";
                    $batchKey = $row['brand_id'].$row['item_id']; 
                    
                    $stockQty = (!empty($stockData->qty))?$stockData->qty:0;
                    if(!empty($row['id'])):
                        $oldItem = $this->estimate->getEstimateItem(['id'=>$row['id']]);
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
            $this->printJson($this->estimate->save($data));
        endif;
    }

    public function edit($id){
        $this->data['dataRow'] = $dataRow = $this->estimate->getEstimate(['id'=>$id,'itemList'=>1]);
        $this->data['gstinList'] = $this->party->getPartyGSTDetail(['party_id' => $dataRow->party_id]);
        $this->data['partyList'] = $this->party->getPartyList(['party_category' => 1]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>1]);
        $this->data['brandList'] = $this->brandMaster->getBrandList();
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
		$this->data['taxList'] = array();//$this->taxMaster->getActiveTaxList(2);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(2);
        $this->data['termsList'] = $this->terms->getTermsList(['type'=>'Sales']);
        $this->data['ledgerList'] = $this->party->getPartyList(["'DT'","'ED'","'EI'","'ID'","'II'"]);
        $this->load->view($this->form,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->estimate->delete($id));
        endif;
    }

    public function printEstimate($id){
        $this->data['dataRow'] = $dataRow = $this->salesOrder->getSalesOrder(['id'=>$id,'itemList'=>1]);
        $this->data['partyData'] = $this->party->getParty(['id'=>$dataRow->party_id]);
        $this->data['companyData'] = $companyData = $this->masterModel->getCompanyInfo();
        
        $pdfData = $this->load->view('estimate/print', $this->data, true);        
        
		$mpdf = new \Mpdf\Mpdf();
		$filePath = realpath(APPPATH . '../assets/uploads/sales_quotation/');
        $pdfFileName = $filePath.'/' . str_replace(["/","-"],"_",$dataRow->trans_number) . '.pdf';
        $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css?v='.time()));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetWatermarkImage($logo, 0.03, array(120, 120));
        $mpdf->showWatermarkImage = true;
		$mpdf->AddPage('P','','','','',10,5,5,15,5,5,'','','','','','','','','','A4-P');
        $mpdf->WriteHTML($pdfData);
		
		ob_clean();
		$mpdf->Output($pdfFileName, 'I');		
    }

    public function estimatePayment(){
        $data = $this->input->post();
        $this->data['main_ref_id'] = $data['id'];
        $this->load->view($this->paymentForm,$this->data);
    }

    public function saveEstimatePayment(){
        $data = $this->input->post();
        $errorMessage = array();

        if(empty($data['entry_date']))
            $errorMessage['entry_date'] = "Date is required.";
        if(empty($data['amount']))
            $errorMessage['amount'] = "Amount is required.";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $this->printJson($this->estimate->saveEstimatePayment($data));
        endif;
    }

    public function getEstimatePaymentTrans(){
        $data = $this->input->post();
        $result = $this->estimate->getEstimatePayments($data);

        $tbodyData="";$i=1; 
        if(!empty($result)):
            foreach($result as $row):
                $deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Payment','fndelete':'deleteEstimatePayment','res_function':'resTrashEstimatePayment'}";
                $tbodyData.= '<tr>
                    <td>' . $i++ . '</td>
                    <td>' . formatDate($row->entry_date) . '</td>
                    <td>' . $row->received_by . '</td>
                    <td>' . $row->amount . ' </td>
                    <td>' . $row->remark . '</td>
                    <td class="text-center">
                        <button type="button" onclick="trash('.$deleteParam.');" class="btn btn-sm btn-outline-danger waves-effect waves-light btn-delete permission-remove"><i class="ti-trash"></i></button>
                    </td>
                </tr>';
            endforeach;
        else:
            $tbodyData.= '<tr><td colspan="6" class="text-center">No data available in table</td></tr>';
        endif;

        $this->printJson(['status'=>1,"tbodyData"=>$tbodyData]);
    }

    public function deleteEstimatePayment(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->estimate->deleteEstimatePayment($id));
        endif;
    }

    public function ledger(){
        $this->data['startDate'] = $this->startYearDate;
        $this->data['endDate'] = $this->endYearDate;
        $this->load->view($this->estimate_ledger,$this->data);
    }

    public function getLedger($jsonData=""){
        if(!empty($jsonData)):
            $postData = (Array) decodeURL($jsonData);
        else: 
            $postData = $this->input->post();
        endif;

        $ledgerSummary = $this->estimate->getLedgerSummary($postData);
        $i=1; $tbody="";
        foreach($ledgerSummary as $row):
            if(empty($jsonData)):
                $accountName = '<a href="' . base_url('estimate/ledgerDetail/' . $row->party_id) . '" target="_blank" datatip="Account Details" flow="down"><b>'.$row->party_name.'</b></a>';
            else:
                $accountName = $row->party_name;
            endif;

            if($row->op_balance > 0): $row->op_balance = $row->op_balance ." Cr.";
            elseif($row->op_balance < 0): $row->op_balance = abs($row->op_balance) ." Dr.";
            endif;

            if($row->cl_balance > 0): $row->cl_balance = $row->cl_balance ." Cr.";
            elseif($row->cl_balance < 0): $row->cl_balance = abs($row->cl_balance) ." Dr.";
            endif;

            $tbody .= '<tr>
                <td>'.$i++.'</td>
                <td class="text-left">'.$accountName.'</td>
                <td class="text-right">'.$row->op_balance.'</td>
                <td class="text-right">'.$row->cl_balance.'</td>
            </tr>';
        endforeach;         
        
        if(!empty($postData['pdf'])):
            $reportTitle = 'ACCOUNT LEDGER';
            $report_date = date('d-m-Y',strtotime($postData['from_date'])).' to '.date('d-m-Y',strtotime($postData['to_date']));   
            $thead = (empty($jsonData)) ? '<tr class="text-center"><th colspan="11">'.$reportTitle.' ('.$report_date.')</th></tr>' : '';
            $thead .= '<tr>
                <th>#</th>
                <th class="text-left">Account Name</th>
                <th class="text-right">Opening Amount</th>
                <th class="text-right">Closing Amount</th>
            </tr>';

            $companyData = $this->masterModel->getCompanyInfo();
            $logoFile = (!empty($companyData->company_logo)) ? $companyData->company_logo : 'logo.png';
            $logo = base_url('assets/images/' . $logoFile);
            $letter_head = base_url('assets/images/letterhead_top.png');
            
            $pdfData = '<table class="table table-bordered item-list-bb" repeat_header="1">
                <thead class="thead-dark" id="theadData">'.$thead.'</thead>
                <tbody>'.$tbody.'</tbody>
            </table>';
            $htmlHeader = '<table class="table" style="border-bottom:1px solid #036aae;">
                <tr>
                    <td class="org_title text-uppercase text-left" style="font-size:1rem;width:30%">'.$reportTitle.'</td>
                    <td class="org_title text-uppercase text-center" style="font-size:1.3rem;width:40%">'.$companyData->company_name.'</td>
                    <td class="org_title text-uppercase text-right" style="font-size:1rem;width:30%">'.$report_date.'</td>
                </tr>
            </table>
            <table class="table" style="border-bottom:1px solid #036aae;margin-bottom:2px;">
                <tr><td class="org-address text-center" style="font-size:13px;">'.$companyData->company_address.'</td></tr>
            </table>';
            $htmlFooter = '<table class="table top-table" style="margin-top:10px;border-top:1px solid #545454;">
                <tr>
                    <td style="width:50%;font-size:12px;">Printed On ' . date('d-m-Y') . '</td>
                    <td style="width:50%;text-align:right;font-size:12px;">Page No. {PAGENO}/{nbpg}</td>
                </tr>
            </table>';

            $mpdf = new \Mpdf\Mpdf();
            $filePath = realpath(APPPATH . '../assets/uploads/');
            $pdfFileName = $filePath.'/AccountLedger.pdf';
            $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css?v='.time()));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetWatermarkImage($logo, 0.08, array(120, 120));
            $mpdf->showWatermarkImage = true;
            $mpdf->SetTitle($reportTitle);
            $mpdf->SetHTMLHeader($htmlHeader);
            $mpdf->SetHTMLFooter($htmlFooter);
            $mpdf->AddPage('P','','','','',5,5,19,20,3,3,'','','','','','','','','','A4-P');
            $mpdf->WriteHTML($pdfData);
            
            ob_clean();
            $mpdf->Output($pdfFileName, 'I');
        
        else:
            $this->printJson(['status'=>1, 'tbody'=>$tbody]);
        endif;
    }

    public function ledgerDetail($acc_id,$start_date="",$end_date=""){
        $ledgerData = $this->party->getParty(['id'=>$acc_id]);
        $this->data['acc_id'] = $acc_id;
        $this->data['acc_name'] = $ledgerData->party_name;
        $this->data['ledgerData'] = $ledgerData;
        $this->data['startDate'] = $this->startYearDate;
        $this->data['endDate'] = $this->endYearDate;
        $this->load->view($this->estimate_ledger_details,$this->data);
    }

    public function getLedgerTransaction($jsonData=""){
        if(!empty($jsonData)):
            $postData = (Array) decodeURL($jsonData);
        else:
            $postData = $this->input->post();
        endif;
        
        $ledgerTransactions = $this->estimate->getLedgerDetails($postData);
        $ledgerBalance = $this->estimate->getLedgerSummary($postData);

        $i=1; $tbody="";$balance = $ledgerBalance->op_balance;$totalCrAmount = $totalDrAmount = 0;
        foreach($ledgerTransactions as $row):
            $balance += round(($row->amount * $row->p_or_m),2); 
            $balanceText = ($balance > 0)?abs($balance)." CR":(($balance < 0)?abs($balance)." DR":0);

            $tbody .= '<tr>
                <td>'.$i++.'</td>
                <td>'.formatDate($row->trans_date).'</td>
                <td>'.$row->trans_number.'</td>
                <td class="text-right">'.$row->cr_amount.'</td>
                <td class="text-right">'.$row->dr_amount.'</td>
                <td style="text-align: center;">'.$balanceText.'</td>
            </tr>';

            $totalCrAmount += $row->cr_amount; 
            $totalDrAmount += $row->dr_amount;
        endforeach;    
        
        
        if($ledgerBalance->op_balance > 0): $ledgerBalance->op_balance_type = "Cr.";
        elseif($ledgerBalance->op_balance < 0): $ledgerBalance->op_balance_type = "Dr.";
        else: $ledgerBalance->op_balance_type = ""; endif;

        if($ledgerBalance->cl_balance > 0): $ledgerBalance->cl_balance_type = "Cr.";
        elseif($ledgerBalance->cl_balance < 0): $ledgerBalance->cl_balance_type = "Dr.";
        else: $ledgerBalance->cl_balance_type = ""; endif;

        $ledgerBalance->cl_balance = abs($ledgerBalance->cl_balance);
        $ledgerBalance->op_balance = abs($ledgerBalance->op_balance);
        $ledgerBalance->cr_balance = abs($totalCrAmount);
        $ledgerBalance->dr_balance = abs($totalDrAmount);

        
        if(!empty($postData['pdf'])):
            $acc_name=$this->party->getParty(['id'=>$postData['acc_id']])->party_name;
            $reportTitle = $acc_name;
            $report_date = date('d-m-Y',strtotime($postData['from_date'])).' to '.date('d-m-Y',strtotime($postData['to_date']));   
            $thead = (empty($jsonData)) ? '<tr class="text-center"><th colspan="11">'.$reportTitle.' ('.$report_date.')</th></tr>' : '';

            $companyData = $this->masterModel->getCompanyInfo();
			$logoFile = (!empty($companyData->company_logo)) ? $companyData->company_logo : 'logo.png';
			$logo = base_url('assets/images/' . $logoFile);
			$letter_head = base_url('assets/images/letterhead_top.png');

            $thead .= '<tr>
                <th>#</th>
                <th>Vou. Date</th>
                <th>Vou. No.</th>
                <th>Amount(CR.)</th>
                <th>Amount(DR.)</th>
                <th>Balance</th>
            </tr>';

            $pdfData = '<table id="commanTable" class="table table-bordered item-list-bb" repeat_header="1">
                <thead class="thead-dark" id="theadData">'.$thead.'</thead>
                <tbody id="receivableData">'.$tbody.'</tbody>
                <tfoot class="thead-dark">
                    <tr>
                        <th colspan="5" class="text-right">Total</th>
                        <th id="cr_balance">'.$ledgerBalance->cr_balance.'</th>
                        <th id="dr_balance">'.$ledgerBalance->dr_balance.'</th>
                        <th></th>
                    </tr>
                </tfoot>    
            </table>
            <table class="table" style="border-top:1px solid #036aae;border-bottom:1px solid #036aae;margin-bottom:10px;margin-top:10px;">
                <tr>
                    <td class="org_title text-uppercase text-left" style="font-size:1rem;width:50%">'.$ledgerBalance->bcl_balance_text.'</td>
                    <td class="org_title text-uppercase text-right" style="font-size:1rem;width:50%"> Closing Balance: '.$ledgerBalance->cl_balance.' '.$ledgerBalance->cl_balance_type.'</td>
                </tr>
            </table>';

            $htmlHeader = '<table class="table" style="border-bottom:1px solid #036aae;">
                <tr>
                    <td class="org_title text-uppercase text-left" style="font-size:1rem;width:30%"></td>
                    <td class="org_title text-uppercase text-center" style="font-size:1.3rem;width:40%">'.$companyData->company_name.'</td>
                    <td class="org_title text-uppercase text-right" style="font-size:1rem;width:30%"></td>
                </tr>
            </table>
            <table class="table" style="border-bottom:1px solid #036aae;margin-bottom:2px;">
                <tr><td class="org-address text-center" style="font-size:13px;">'.$companyData->company_address.'</td></tr>
            </table>
            <table class="table" style="border-bottom:1px solid #036aae;margin-bottom:10px;">
                <tr>
                    <td class="org_title text-uppercase text-left" style="font-size:1rem;width:30%">Date : '.$report_date.'</td>
                    <td class="org_title text-uppercase text-center" style="font-size:1.3rem;width:40%">'.$reportTitle.'</td>
                    <td class="org_title text-uppercase text-right" style="font-size:1rem;width:30%"> Opening Balance: '.$ledgerBalance->op_balance.' '.$ledgerBalance->op_balance_type.'</td>
                </tr>
            </table>';  
			$htmlFooter = '<table class="table top-table" style="margin-top:10px;border-top:1px solid #545454;">
                <tr>
                    <td style="width:50%;font-size:12px;">Printed On ' . date('d-m-Y') . '</td>
                    <td style="width:50%;text-align:right;font-size:12px;">Page No. {PAGENO}/{nbpg}</td>
                </tr>
            </table>';
                        
            $mpdf = new \Mpdf\Mpdf();
            $filePath = realpath(APPPATH . '../assets/uploads/');
            $pdfFileName = $filePath.'/AccountLedgerDetail.pdf';
            $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css?v='.time()));
            $mpdf->WriteHTML($stylesheet, 1);
            $mpdf->SetDisplayMode('fullpage');
            $mpdf->SetWatermarkImage($logo, 0.08, array(120, 120));
            $mpdf->showWatermarkImage = true;
            $mpdf->SetTitle($reportTitle);
            $mpdf->SetHTMLHeader($htmlHeader);
            $mpdf->SetHTMLFooter($htmlFooter);
            $mpdf->AddPage('L','','','','',5,5,30,5,3,3,'','','','','','','','','','A4-L');
            $mpdf->WriteHTML($pdfData);
            
            ob_clean();
            $mpdf->Output($pdfFileName, 'I');
        
        else:
            $this->printJson(['status'=>1, 'tbody'=>$tbody,'ledgerBalance'=>$ledgerBalance]);
        endif;
    }
}
?>