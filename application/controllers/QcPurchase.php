<?php
class QcPurchase extends MY_Controller{

    private $indexPage = 'qc_purchase/index';
    private $orderForm = "qc_purchase/form";
	private $gstPercentage = Array(["rate"=>0,"val"=>'NIL'],["rate"=>0.25,"val"=>'0.25%'],["rate"=>3,"val"=>'3%'],["rate"=>5,"val"=>'5%'],["rate"=>12,"val"=>'12%'],["rate"=>18,"val"=>'18%'],["rate"=>28,"val"=>'28%']);

    public function __construct(){
        parent::__construct();
        $this->isLoggedin();
		$this->data['headData']->pageTitle = "QC Purchase Order";
		$this->data['headData']->controller = "qcPurchase";
		$this->data['headData']->pageUrl = "qcPurchase";
		$this->data['entryData'] = $this->transMainModel->getEntryType(['controller'=>'qcPurchase']);
    }

    public function index(){
        $this->data['tableHeader'] = getQualityDtHeader($this->data['headData']->controller);
        $this->load->view($this->indexPage,$this->data);
    }
    
    public function getDTRows($status=0){
        $data = $this->input->post(); $data['status'] = $status;
        $result = $this->qcPurchase->getDTRows($data);
        $sendData = array();$i=1;
        foreach($result['data'] as $row):
            $row->sr_no = $i++;        
            $pq = $row->qty - $row->receive_qty;
            $row->pending_qty = ($pq >=0 ) ? $pq : 0;;    
            $row->controller = "qcPurchase";    
            $sendData[] = getQCPurchaseData($row);
        endforeach;        
        $result['data'] = $sendData;
        $this->printJson($result);
    }

	public function createOrder(){
		$this->data['entry_type'] = $this->data['entryData']->id;
		$this->data['trans_no'] = $this->data['entryData']->trans_no;
		$this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
		$this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
		$this->data['categoryList'] = $this->qcPurchase->getCategoryList();
		$this->data['partyList'] = $this->party->getPartyList(['party_category'=>"1,2,3"]);
        $this->data['itemData'] = $this->item->getItemList(['item_type'=>"2,3,4,8"]);
		$this->data['taxList'] = $this->taxMaster->getActiveTaxList(1);
		$this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(1);
		$this->data['termsList'] = $this->terms->getTermsList(['type'=>'Purchase']);
		$this->data['unitList'] = $this->item->itemUnits();
        $this->data['companyList'] = $this->purchaseOrder->getCompanyList();
        $this->load->view($this->orderForm,$this->data);
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
            $data['doc_date'] = (!empty($data['doc_date']))?$data['doc_date']:NULL;
            unset($data['_per'],$data['_amount']);
			$this->printJson($this->qcPurchase->save($data));
		endif;
    }

	public function edit($id){
		$this->data['entry_type'] = $this->data['entryData']->id;
		$this->data['trans_no'] = $this->data['entryData']->trans_no;
		$this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
		$this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
		$this->data['categoryList'] = $this->qcPurchase->getCategoryList();
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>"1,2,3"]);
		$this->data['itemData'] = $this->item->getItemList(['item_type'=>"2,3,4,8"]);
		$this->data['taxList'] = $this->taxMaster->getActiveTaxList(1);
		$this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(1);
        $this->data['unitData'] = $this->item->itemUnits();
        $this->data['dataRow'] = $this->qcPurchase->getPurchaseOrder($id);
        $this->data['termsList'] = $this->terms->getTermsList(['type'=>'Purchase']);
        $this->data['companyList'] = $this->purchaseOrder->getCompanyList();
        $this->load->view($this->orderForm,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
		if(empty($id)):
			$this->printJson(['status'=>0,'message'=>'Something went wrong...Please try again.']);
		else:
			$this->printJson($this->qcPurchase->deleteOrder($id));
		endif;
    }

	public function getPurchaseOrderForReceive(){
        $data = $this->input->post();
        $poData = $this->qcPurchase->getPurchaseOrder($data['po_id']);
        $po_no = $poData->trans_number;
        
        $tbody="";$i=1;
		foreach($poData->itemList as $row):
			$tbody .= '<tr class="text-center">
				<td>'.$i.'</td>
				<td>['.$row->category_code.'] '.$row->category_name.' '.$row->size.'</td>
				<td>'.floatVal($row->qty).'</td>
				<td>'.floatVal($row->qty - $row->receive_qty).'</td>
				<td>
				    <input type="hidden" name="id[]" value="'.$row->id.'" />
					<input type="hidden" name="qty[]" value="'.floatVal($row->qty).'" />
					<input type="hidden" name="pending_qty[]" value="'.floatVal($row->qty - $row->receive_qty).'" />
			        <input type="text" name="receive_qty[]" value="" class="form-control floatOnly" />
					<div class="error qty_err_'.$i.'"></div>
			    </td>	
		   </tr>';
		   $i++;
		endforeach;
		$this->printJson(['status'=>1,'htmlData'=>$tbody,'po_no'=>$po_no]);
    }

	public function purchaseRecive(){
        $data = $this->input->post();
        $errorMessage = array();
        $itemValidation = false;
        
        if(empty($data['grn_date']))
            $errorMessage['grn_date'] = 'Date is required.';
        if(empty($data['in_challan_no']))
            $errorMessage['in_challan_no'] = 'Challan No. is required.';
		if(empty($data['receive_qty']))
			$errorMessage['general'] = "Revceive Item Qty Required";
		if(!empty($data['qty']) && !empty($data['pending_qty'])) {
    		foreach($data['qty'] as $key => $value) {
                if(!empty($data['receive_qty'][$key])) {
					if($data['receive_qty'][$key] > $data['pending_qty'][$key]){
						$errorMessage['qty_err_'.($key+1)] = "Revceive Item Qty is grater than Pending Qty";
					}
				}
    		}
		}

		if(!empty($errorMessage)):
			$this->printJson(['status'=>0,'message'=>$errorMessage]);
		else:
			$this->printJson($this->qcPurchase->purchaseRecive($data));
		endif;
    }

    function printQP($id){
		$this->data['poData'] = $poData = $this->qcPurchase->getPurchaseOrder($id);
        $this->data['taxList'] = $this->taxMaster->getActiveTaxList(1);
        $this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(1);
		$this->data['termsData'] = (!empty($poData->termsConditions) ? $poData->termsConditions: "");
		$cmid = (!empty($poData->cm_id)) ? $poData->cm_id : 1;
		$this->data['companyData'] = $companyData = $this->masterModel->getCompanyInfo($cmid);
		
		$logo=base_url('assets/images/'.$companyData->company_logo);
		$this->data['lht']= base_url('assets/images/letterhead/'.$companyData->lht_img);
		$this->data['lhb']= base_url('assets/images/letterhead/'.$companyData->lhb_img);
		
		$pdfData = $this->load->view('qc_purchase/printqp',$this->data,true);
		
		$poData = $this->data['poData'];

		$htmlHeader = '<img src="'.$this->data['lht'].'" class="img">';
		$htmlFooter = '<table class="table top-table" style="margin-top:10px;border-top:1px solid #545454;">
            <tr>
                <td style="width:70%;"></td>
                <th class="text-center">For, '.$companyData->company_name.'</th>
            </tr>
            <tr>
                <td></td>
                <td class="text-center"><br> <br> Authorised By</td>
            </tr>
        </table>
        <img src="'.$this->data['lhb'].'" class="img">';

        
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

	public function addPOFromRequest($id){
		$this->data['req_id'] = $id;
		$this->data['entry_type'] = $this->data['entryData']->id;
		$this->data['trans_no'] = $this->data['entryData']->trans_no;
		$this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
		$this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
		$this->data['categoryList'] = $this->qcPurchase->getCategoryList();
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>"1,2,3"]);
		$this->data['itemData'] = $this->item->getItemList(['item_type'=>"2,3,4,8"]);
		$this->data['taxList'] = $this->taxMaster->getActiveTaxList(1);
		$this->data['expenseList'] = $this->expenseMaster->getActiveExpenseList(1);
        $this->data['unitData'] = $this->item->itemUnits();
		$this->data['termsList'] = $this->terms->getTermsList(['type'=>'Purchase']);
		$this->data['reqItemList'] = $this->qcPurchase->getQCPRListForPO($id);
        $this->data['companyList'] = $this->purchaseOrder->getCompanyList();
        $this->load->view($this->orderForm,$this->data);
	}
}
?>
