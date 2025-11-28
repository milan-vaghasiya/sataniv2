<?php
class DeliveryChallan extends MY_Controller{
    private $indexPage = "delivery_challan/index";
    private $form = "delivery_challan/form";    

    public function __construct(){
		parent::__construct();
		$this->data['headData']->pageTitle = "Delivery Challan";
		$this->data['headData']->controller = "deliveryChallan";        
        $this->data['headData']->pageUrl = "deliveryChallan";
        $this->data['entryData'] = $this->transMainModel->getEntryType(['controller'=>'deliveryChallan']);
	}

    public function index(){
        $this->data['tableHeader'] = getSalesDtHeader("deliveryChallan");
        $this->load->view($this->indexPage,$this->data);
    }

    public function getDTRows($status = 0){
        $data = $this->input->post();$data['status'] = $status;
        $data['entry_type'] = $this->data['entryData']->id;
        $result = $this->deliveryChallan->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):
            $row->sr_no = $i++;
            $sendData[] = getDeliveryChallanData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function addChallan(){
        $this->data['entry_type'] = $this->data['entryData']->id;
        $this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
        $this->data['trans_no'] = $this->data['entryData']->trans_no;
        $this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
        $this->data['partyList'] = $this->party->getPartyList(['party_category' => "1,2,3"]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>"1,2,3,4"]);
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
        $this->data['termsList'] = $this->terms->getTermsList(['type'=>'Sales']);
        $this->data['transportList'] = $this->transport->getTransportList();
        $this->data['companyList'] = $this->purchaseOrder->getCompanyList();
        $this->load->view($this->form,$this->data);
    }

    public function save(){
        $data = $this->input->post();
        $errorMessage = array();

        if(empty($data['trans_no']))
            $errorMessage['trans_number'] = "DC. No. is required.";
        if(empty($data['trans_date']))
            $errorMessage['trans_date'] = "DC. Date is required.";
        if(empty($data['party_id']))
            $errorMessage['party_id'] = "Party Name is required.";
        if(empty($data['itemData'])):
            $errorMessage['itemData'] = "Item Details is required.";
        else:           
            foreach($data['itemData'] as $key => $row):
                $bQty = array();
                if($row['stock_eff'] == 1):
                    if(empty($row['batch_detail'])):
                        $errorMessage['qty'.$key] = "Batch Details is required.";
                    else:
                        $batchDetail = $row['batch_detail'];
                        $batchDetail = json_decode($batchDetail,true);$oldBatchQty = array();
                        if(!empty($row['id'])):
                            $oldItem = $this->deliveryChallan->getDeliveryChallanItem(['id'=>$row['id'],'batchDetail'=>1]);

                            $oldBatchDetail = json_decode($oldItem->batch_detail);
                            $oldBatchQty = array_reduce($oldBatchDetail, function($oldBatchDetail, $batch) { 
                                $oldBatchDetail[$batch->remark]= $batch->batch_qty; 
                                return $oldBatchDetail; 
                            }, []);
                        endif;

                        $batchQty = (!empty($batchDetail))?array_sum(array_column($batchDetail,'batch_qty')):0;
                        if(floatval($row['qty']) <> floatval($batchQty)):
                            $errorMessage['qty'.$key] = "Invalid Batch Qty.";
                        else:
                            foreach($batchDetail as $batch):
                                if(!empty($batch['batch_qty']) && $batch['batch_qty'] > 0):
                                    $postData = [
                                        'location_id' => $batch['location_id'],
                                        'batch_no' => $batch['batch_no'],
                                        'heat_no' => $batch['heat_no'],
                                        'item_id' => $row['item_id'],
                                        'stock_required' => 1,
                                        'single_row' => 1
                                    ];                        
                                    $stockData = $this->itemStock->getItemStockBatchWise($postData);  
                                
                                    $batchKey = "";
                                    $batchKey = $batch['remark'];
                                    
                                    $stockQty = (!empty($stockData->qty))?floatVal($stockData->qty):0;
                                    if(!empty($row['id'])):                            
                                        $stockQty = $stockQty + (isset($oldBatchQty[$batchKey])?$oldBatchQty[$batchKey]:0);
                                    endif;
                                    
                                    if(!isset($bQty[$batchKey])):
                                        $bQty[$batchKey] = $batch['batch_qty'] ;
                                    else:
                                        $bQty[$batchKey] += $batch['batch_qty'];
                                    endif;
            
                                    if(empty($stockQty)):
                                        $errorMessage['qty'.$key] = "Stock not available.";
                                    else:
                                        if($bQty[$batchKey] > $stockQty):
                                            $errorMessage['qty'.$key] = "Stock not available.".$bQty[$batchKey] .'>'. $stockQty;
                                        endif;
                                    endif;
                                endif;
                            endforeach;
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
            $this->printJson($this->deliveryChallan->save($data));
        endif;
    }

    public function edit($id){
        $this->data['dataRow'] = $dataRow = $this->deliveryChallan->getDeliveryChallan(['id'=>$id,'itemList'=>1]);
        $this->data['gstinList'] = $this->party->getPartyGSTDetail(['party_id' => $dataRow->party_id]);
        $this->data['partyList'] = $this->party->getPartyList(['party_category' => "1,2,3"]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>"1,2,3,4"]);
        $this->data['unitList'] = $this->item->itemUnits();
        $this->data['hsnList'] = $this->hsnModel->getHSNList();
        $this->data['termsList'] = $this->terms->getTermsList(['type'=>'Sales']);
        $this->data['transportList'] = $this->transport->getTransportList();
        $this->data['companyList'] = $this->purchaseOrder->getCompanyList();
        $this->load->view($this->form,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->deliveryChallan->delete($id));
        endif;
    }

    public function printChallan($id){
        $this->data['dataRow'] = $dataRow = $this->deliveryChallan->getDeliveryChallan(['id'=>$id,'itemList'=>1]);
        $this->data['partyData'] = $this->party->getParty(['id'=>$dataRow->party_id]);
        $this->data['termsData'] = (!empty($dataRow->termsConditions) ? $dataRow->termsConditions: "");
        $cmid = (!empty($dataRow->cm_id)) ? $dataRow->cm_id : 1;
		$this->data['companyData'] = $companyData = $this->masterModel->getCompanyInfo($cmid);
        
        $logo = base_url('assets/images/logo.png');
        $this->data['letter_head'] =  (!empty($companyData->print_header))?base_url($companyData->print_header):base_url('assets/images/letterhead_top.png');
		$this->data['lht']= base_url('assets/images/letterhead/'.$companyData->lht_img);
		$this->data['lhb']= base_url('assets/images/letterhead/'.$companyData->lhb_img);
        
        $pdfData = $this->load->view('delivery_challan/print', $this->data, true);
        
		$htmlHeader = '<img src="'.$this->data['lht'].'" class="img">';
		$htmlFooter = '<img src="'.$this->data['lhb'].'" class="img">';
        
		$mpdf = new \Mpdf\Mpdf();
		$filePath = realpath(APPPATH . '../assets/uploads/delivery_challan/');
        $pdfFileName = $filePath.'/' . str_replace(["/","-"],"_",$dataRow->trans_number) . '.pdf';

        $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css?v='.time()));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetWatermarkImage($logo, 0.03, array(120, 120));
        $mpdf->showWatermarkImage = true;
		$mpdf->SetHTMLHeader($htmlHeader);
        $mpdf->SetHTMLFooter($htmlFooter);
		$mpdf->AddPage('P','','','','',0,0,38,15,0,0,'','','','','','','','','','A4-P');
        $mpdf->WriteHTML($pdfData);
		
		ob_clean();
		$mpdf->Output($pdfFileName, 'I');
    }

    public function getPartyChallan(){
        $data = $this->input->post();
        $this->data['orderItems'] = $this->deliveryChallan->getPendingChallanItems($data);
        $this->load->view('sales_invoice/create_invoice',$this->data);
    }

    public function getBatchWiseItemStock(){
		$data = $this->input->post();
		$readOnly = (!empty($data['qty_readonly']))?$data['qty_readonly']:'';
		$data['location_not_in'] = [6];		
		$data['batchDetail'] = (!empty($data['batchDetail']))?json_decode($data['batchDetail'],true):[];

		$postData = ["item_id" => $data['item_id'], 'location_not_in' => $data['location_not_in'], 'stock_required'=>1, 'group_by'=>'location_id,batch_no,item_id,heat_no'];	

		if(!empty($data['batchDetail']) && !empty($data['id'])):			
			$batch_no = array_column($data['batchDetail'],'batch_no');
			$batch_no = "'".implode("','",$batch_no)."'";
			
			$postData['customHaving'] = "(SUM(stock_transaction.qty * stock_transaction.p_or_m) > 0 OR (stock_transaction.batch_no IN (".$batch_no.") ))";
		endif;

		if(!empty($data['party_id'])):
			$postData['party_id'] = $data['party_id'];
		endif;
		$batchData = $this->itemStock->getItemStockBatchWise($postData);
		$batchDetail = [];
		if(!empty($data['batchDetail'])):			
			$batchDetail = array_reduce($data['batchDetail'],function($item,$row){
				$item[$row['remark']] = $row['batch_qty'];
				return $item;
			},[]);
		endif;
        $tbody = '';$i=1;
        if(!empty($batchData)):
            foreach($batchData as $row):
                $batchId = trim(preg_replace('/[^A-Za-z0-9]/', '', $row->batch_no)).trim(preg_replace('/[^A-Za-z0-9]/', '', $row->heat_no)).$row->location_id.$row->item_id;
                $location_name = '['.$row->store_name.'] '.$row->location;

				$qty = (isset($batchDetail[$batchId]))?$batchDetail[$batchId]:0;
				
				if(!empty($data['id'])):
					$row->qty = $row->qty + $qty;
				endif;

                $tbody .= '<tr id="'.$batchId.'" data-ind="'.$i.'">
                    <td>['.$row->store_name.'] '.$row->location.'</td>
                    <td>
						'.$row->batch_no.'
						<br><small>'.$row->heat_no.'</small>
					</td>
                    <td>
                        '.floatval($row->qty).'
                    </td>
                    <td>
                        <input type="text" name="batchDetail['.$i.'][batch_qty]" id="batch_qty_'.$i.'" class="calculateBatchQty form-control" value="'.$qty.'" '.$readOnly.'>
                        <input type="hidden" name="batchDetail['.$i.'][location_id]" id="location_id_'.$i.'" value="'.$row->location_id.'">
                        <input type="hidden" name="batchDetail['.$i.'][batch_no]" id="batch_no_'.$i.'" value="'.$row->batch_no.'">
						<input type="hidden" name="batchDetail['.$i.'][heat_no]" id="heat_no_'.$i.'" value="'.$row->heat_no.'">
                        <input type="hidden" name="batchDetail['.$i.'][remark]" id="batch_id_'.$i.'" value="'.$batchId.'">
                        <input type="hidden" name="batchDetail['.$i.'][batch_stock]" id="batch_stock_'.$i.'" value="'.floatVal($row->qty).'">
                        <div class="error batch_qty_'.$i.'"></div>
                    </td>
                </tr>';
                $i++;
            endforeach;
        endif;

		if(empty($tbody)):
            $tbody = '<tr>
                <td colspan="4" class="text-center">No data available in table</td>
            </tr>';
        endif;

        $this->printJson(['status'=>1,'tbodyData'=>$tbody]);
	}
}
?>