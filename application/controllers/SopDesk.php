<?php
class SopDesk extends MY_Controller{

	public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "SOP DESK";
		$this->data['headData']->controller = "sopDesk";
	}
	
	public function index($mfg_type = "Forging"){
		$this->data['mfg_type'] = $mfg_type;
		$this->data['headData']->pageTitle = "SOP Desk";
        $this->load->view('sopDesk/sop_desk',$this->data);
    }
    
	public function getPRCList($fnCall = "Ajax"){
        $postData = $this->input->post();
		if(empty($postData)){$fnCall = 'Outside';}
        $next_page = 0;
		
		$prcData = Array();
		if(isset($postData['page']) AND isset($postData['start']) AND isset($postData['length']))
        {
            $next_page = intval($postData['page']) + 1;
        }
        if($postData['status'] == 'mc_material'){
			$mcStockData = $this->sop->getMachiningStockData($postData);
			$this->data['mcStockData'] = $mcStockData;
			$this->data['status'] = $postData['status'];
			$prcList = $this->load->view('sopDesk/machining_stock_list',$this->data,true);
		}elseif($postData['status'] == 'mc_request'){
			$postData['status'] = 'pending';
			$postData['req_type'] = 2;
			$mcStockData = $this->store->getRequestList($postData);
			$this->data['requestList'] = $mcStockData;
			$this->data['status'] = $postData['status'];
			$prcList = $this->load->view('sopDesk/mt_request_list',$this->data,true);
		}
		else{
			$prcData = $this->sop->getPRCList($postData);
			$this->data['prcData'] = $prcData;
			$this->data['status'] = $postData['status'];
			$prcList = $this->load->view('sopDesk/prc_list',$this->data,true);
		}
		
		
        if($fnCall == 'Ajax'){$this->printJson(['prcList'=>$prcList,'next_page'=>$next_page]);}
		else{return $leadDetail;}
    }
    
	public function getPRCDetail(){
        $postData = $this->input->post();
		$prcDetail ='';$prcMaterial ='';$processDetail ='';
		
		$prcData = $this->data['prcData'] = $this->sop->getPRCDetail(['prc_id'=>$postData['id']]);
	
		if(!empty($prcData))
		{
			if($prcData->mfg_type == 'Machining'){
				$prcProcess = explode(",",$prcData->process_ids);
				$firstPrsData= $this->sop->getPRCProcessList(['current_process_id'=>$prcProcess[0],'prc_id'=>$postData['id'],'log_data'=>1,'single_row'=>1]); 
				if(!empty($firstPrsData)){
					$pendingReview = $firstPrsData->rej_found - $firstPrsData->review_qty;
					$this->data['production_qty'] =  ($firstPrsData->ok_qty+$firstPrsData->rej_qty+$firstPrsData->rw_qty+$pendingReview);
				}else{
					$this->data['production_qty'] = 0 ;
				}
					
			}
    		$this->data['status'] = (!empty($prcData->status)) ? $prcData->status : 1;
    		$this->data['prcMaterialData'] = $prcMaterialData = $this->sop->getMaterialIssueData(['prc_id'=>$postData['id'],'production_data'=>1,'return_data'=>1,'item_id'=>(($prcData->mfg_type == 'Machining')?$prcData->item_id:'')]); 
    		$this->data['reqMaterialData'] = (empty($prcMaterialData)) ? $this->item->getProductKitData(['item_id'=>$prcData->item_id]) : [];
    		$this->data['prcProcessData'] = (!empty($prcData->prcProcessData)) ? $prcData->prcProcessData : [];
    		$prcProcessData = $this->sop->getPRCProcessList(['current_process_id'=>0,'prc_id'=>$postData['id'],'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
			$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
			$movement_qty =!empty($prcProcessData->movement_qty)?$prcProcessData->movement_qty:0;
			$this->data['pending_movement'] = $ok_qty - $movement_qty;
    		$prcDetail = $this->load->view('sopDesk/prc_detail',$this->data,true);
    		
    		$prcMaterial = $this->load->view('sopDesk/prc_material',$this->data,true);
    		
    		$processDetail = $this->load->view('sopDesk/prc_process',$this->data,true);
		}
        $this->printJson(['prcDetail'=>$prcDetail,'prcMaterial'=>$prcMaterial,'processDetail'=>$processDetail]);
    }
    
	public function addPrc(){
		$data = $this->input->post();
		$this->data['mfg_type'] = $data['mfg_type'];
		if($data['mfg_type'] == 'Forging'){
			$prcNo = $this->sop->getNextPRCNo(1,'Forging');
			$this->data['prc_no'] = $prcNo;			
			$this->data['prc_number'] = $prcNo;
		}else{
			$prcNo = $this->sop->getNextPRCNo(1,'Machining',$data['batch_no']);
			$this->data['prc_no'] = $prcNo; // 26-10-2024			
			$this->data['prc_number'] = $data['batch_no'].'/MC'.LPAD($prcNo, 3, "0"); // 26-10-2024		
			$this->data['ref_batch'] = $data['batch_no'];
			$this->data['heat_no'] = $data['heat_no'];
			$this->data['item_id'] = $data['item_id'];
        	$this->data['processData'] = $this->getProcessList(['item_id'=>$data['item_id']]);
		}
		$this->data['productList'] = $this->item->getItemList(['item_type'=>1]);
		$this->load->view('sopDesk/prc_form',$this->data);
	}
	
	public function getProductList($param = []){
		$data = (!empty($param)) ? $param : $this->input->post();
		
		$result = array();
		if(!empty($data['party_id'])){
			$result = $this->salesOrder->getPendingOrderItems(['party_id'=>$data['party_id']]);
		}else{
			$result = $this->item->getItemList(['item_type'=>1]);
		}

		$selected='';
		$options='<option value="">Select Product</option>';
		foreach($result as $row):
			$value = (!empty($row->item_code)? "[".$row->item_code."] " : "").$row->item_name;
			$value .= (!empty($row->trans_number))?' ('.$row->trans_number.' | Pend. Qty: ' . $row->pending_qty.')':'';
			
			$so_trans_id=0;
			if(!empty($row->trans_number)){ $so_trans_id = $row->id; }
			
			$selected = (!empty($data['item_id']) && $data['item_id'] == $row->item_id) ? 'selected' : '';
			$options.='<option value="'.$row->item_id.'" data-so_trans_id="'.$so_trans_id.'" '.$selected.'>'.$value.'</option>';
		endforeach;
		if(!empty($param)):
			return $options;
		else:
        	$this->printJson(['options'=>$options]);
		endif;		
	}
	
	public function savePRC(){
		$data = $this->input->post();
		
        $errorMessage = array();
        if ($data['party_id'] == "")
            $errorMessage['party_id'] = "Customer is required.";
        if (empty($data['item_id']))
            $errorMessage['item_id'] = "Product is required.";
        if (empty($data['qty']) || $data['qty'] < 0){
            $errorMessage['qty'] = "Quantity is required.";
		}elseif($data['mfg_type'] == 'Machining'){
			$stockData = $this->itemStock->getItemStockBatchWise(['location_id'=>$this->MACHINING_STORE->id,'batch_no'=>$data['ref_batch'],'item_id'=>$data['item_id'],'single_row'=>1]);
			$oldQty = 0;
			if(!empty($data['id'])){
				$prcData = $this->sop->getPRC(['id'=>$data['id']]);
				$oldQty = $prcData->prc_qty;
			}
			$stockQty = $stockData->qty + $oldQty;
			if($data['qty'] > $stockQty){ $errorMessage['qty'] = "Quantity is invalid."; }
		}
        if (empty($data['prc_date'])) 
            $errorMessage['prc_date'] = "PRC Date is required.";
		if (empty($data['process'])){
		    $errorMessage['process'] = "Product Process is required.";
		}elseif($data['mfg_type'] == 'Forging'){
		    $kitData = $this->item->getProductKitData(['item_id'=>$data['item_id'],'process_id'=>$data['process']]);
		    if(empty($kitData)){
		        $errorMessage['process'] = "Material Required.";
		    }
		}
            
		
        if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
			$masterData = [
				'id'=>$data['id'],
				'prc_no'=>$data['prc_no'],
				'prc_date'=>$data['prc_date'],
				'party_id'=>$data['party_id'],
				'item_id'=>$data['item_id'],
				'so_trans_id'=>$data['so_trans_id'],
				'prc_qty'=>$data['qty'],
				'mfg_type'=>$data['mfg_type'],
				'target_date'=>$data['target_date'],
				// 26-10-2024
				'heat_no'=>$data['heat_no'],
				'ref_batch'=>$data['ref_batch'],
				'prc_number'=>$data['prc_number']
			];
			$prcDetail = [
				'remark'=>$data['remark'],
				'id'=>$data['prc_detail_id'],
				'process_ids'=>implode(",",$data['process']),
			];
			if(empty($data['id'])){
				$masterData['created_by'] = $this->session->userdata('loginId');
				$prcDetail['created_by'] = $this->session->userdata('loginId');
			}else{
				$masterData['updated_by'] = $this->session->userdata('loginId');
				$prcDetail['updated_by'] = $this->session->userdata('loginId');
			}
			$sendData['masterData'] = $masterData;
			$sendData['prcDetail'] = $prcDetail;
            $this->printJson($this->sop->savePRC($sendData));
        endif;
	}

	public function getProcessList($param = []){
		$data = (!empty($param)) ? $param : $this->input->post();
		$processList = $this->item->getProductProcessList(['item_id'=>$data['item_id']]);
		$i = 1;$html="";
		if(!empty($processList)):
			$html = '<table class="table jpExcelTable mt-3">
					<tr class="bg-light"><th class="text-center" style="width:5%;">Sr.No.</th><th class="text-center">Process Detail</th></tr>';
			foreach($processList as $row):
				$checked = (!empty($data['process']) ? ((in_array($row->process_id,explode(",",$data['process']))) ? "checked" : "") : 'checked');
				$html .='<tr>
				            <td class="text-center">'.$i.'</td>
							<td class="text-left">
								<input type="checkbox" id="md_checkbox_'.$i.'" name="process[]" class="filled-in chk-col-success" value="'.$row->process_id.'" '.$checked.' ><label for="md_checkbox_'.$i.'" class="mr-10">'.$row->process_name.'</label>
							</td>
						</tr>';
				$i++;
			endforeach;
			$html .='</table>';
		else:
			$html .= '<span class="text-danger">Set Product Process</span>';
		endif;

		if(!empty($param)):
			return $html;
		else:
        	$this->printJson(['html'=>$html]);
		endif;		
	}

	public function startPRC(){
		$data = $this->input->post();
		if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
// 			$prcBom = $this->sop->getPrcBomData(['prc_id'=>$data['id']]);
			/*if(empty($prcBom)){
				$this->printJson(['status'=>0,'message'=>'Set Required Material for production']);
			}else{
				$this->printJson($this->sop->startPRC($data));
			}*/
            $this->printJson($this->sop->startPRC($data));
        endif;
	}

	/** If Challanid > 0 then vendor receive log Else Inhouse log */
	public function prcLog(){
		$data = $this->input->post();
		$this->data['dataRow'] = $this->sop->getPRCProcessList(['id'=>$data['id'],'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1,'single_row'=>1]);
		$prcProcess = explode(",",$this->data['dataRow']->process_ids);
		if(!empty($data['challan_id'])){
			$this->data['challan_id'] = $data['challan_id'];
			$this->data['ref_trans_id'] = $data['ref_trans_id'];
			$this->data['process_by'] = $data['process_by'];
			$this->data['processor_id'] = $data['processor_id'];
			if($this->data['dataRow']->current_process_id == $prcProcess[0] && $this->data['dataRow']->mfg_type == 'Forging'){
				$this->data['inputDiv'] = 1;
				$this->data['wt_nos'] = $data['wt_nos'];
			}
		}else{
			
			if($this->data['dataRow']->current_process_id == $prcProcess[0]){
				if($this->data['dataRow']->mfg_type == 'Forging'){
					$this->data['inputDiv'] = 1;
					$bomData = $this->item->getProductKitData(['group_name'=>'RM GROUP','item_id'=>$this->data['dataRow']->item_id,'process_id'=>$this->data['dataRow']->current_process_id,'single_row'=>1]);
					$this->data['wt_nos'] = !empty($bomData)?$bomData->qty:'';
				}else{
					$this->data['wt_nos'] = 1;
				}

				
			}
			$this->data['machineList'] = $this->item->getItemList(['item_type'=>5]);
			$this->data['shiftData'] = $this->shiftModel->getShiftList();
			$this->data['operatorList'] = $this->employee->getEmployeeList();
		}
        $this->data['rejectionComments'] = $this->comment->getCommentList(['type'=>1]);
	
		$this->load->view('sopDesk/prc_log_form',$this->data);
	}
	
	public function savePRCLogOld(){
		$data = $this->input->post(); 
        $errorMessage = array();
		$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'pending_accepted'=>1,'log_process_by'=>1,'single_row'=>1]);
        if (empty($data['prc_id'])){ $errorMessage['prc_id'] = "Job Card No. is required.";}
        if (empty($data['prc_process_id'])){ $errorMessage['prc_process_id'] = "Process is required.";}
        if (isset($data['wt_nos']) && empty($data['wt_nos'])){ $errorMessage['wt_nos'] = "Input weight is required.";}
		
		 
		if (empty($data['trans_date']) or $data['trans_date'] == null or $data['trans_date'] == ""){ 
			$errorMessage['trans_date'] = "Date is required."; 
		}else{
			if($prcProcessData->prc_date > $data['trans_date']) :
				$errorMessage['trans_date'] = "Invalid Date.";
			endif;
		}
        if (empty($data['ok_qty']) && empty($data['rej_found'])  && empty($data['without_process_qty'])){
            $errorMessage['production_qty'] = "OK Qty Or Rejection Qty. is required.";
       	}else{
			
			
			$totalProdQty = (!empty($data['ok_qty']))?$data['ok_qty']:0 ;$totalProdQty += (!empty($data['rej_found'])) ? $data['rej_found'] : 0;$totalProdQty += (!empty($data['without_process_qty'])) ? $data['without_process_qty'] : 0;
			$pending_production = 0;
			if($data['process_by'] == 3){
				$challanData = $this->sop->getChallanRequestData(['id'=>$data['ref_trans_id'],'challan_receive'=>1,'single_row'=>1]); 
				$pending_production =$challanData->qty - ($challanData->ok_qty + $challanData->rej_qty+ $challanData->without_process_qty);
			}else{
				$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
				$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
				$rej_found = !empty($prcProcessData->rej_found)?$prcProcessData->rej_found:0;
				$rw_qty = !empty($prcProcessData->rw_qty)?$prcProcessData->rw_qty:0;
				$rej_qty = !empty($prcProcessData->rej_qty)?$prcProcessData->rej_qty:0;
				$pendingReview = $rej_found - $prcProcessData->review_qty;
				$pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
				if($data['trans_type'] == 1){
					;
				}else{
					$rwData = $this->rejectionReview->getReworkData(['prc_id'=>$data['prc_id'],'rw_process'=>$data['prc_id'],'decision_type'=>2]);
				}
				
			}
			
			if($pending_production < $totalProdQty ||  $totalProdQty < 0) :
				$errorMessage['production_qty'] = "Invalid Qty.";
			endif;

		}
		if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
            $start_time = !empty($data['start_time'])?$data['start_time']:date("Y-m-d H:i:s");
            $end_time = !empty($data['end_time'])?$data['end_time']:date("Y-m-d H:i:s");
				$start = new DateTime($start_time);
				$end = new DateTime($end_time);
				$diff = $start->diff($end);
				$daysInSecs = $diff->format('%r%a')* 24 * 60 * 60; $hoursInSecs = $diff->h * 60 * 60; $minsInSecs = $diff->i * 60;
				$totalPrdseconds = $daysInSecs + $hoursInSecs + $minsInSecs + $diff->s;
				$data['production_time'] = $totalPrdseconds;
			$logData = [
				'id'=>'',
				'trans_type' => $data['trans_type'],
				'prc_id' => $data['prc_id'],
				'prc_process_id' => $data['prc_process_id'],
				'process_id' => $data['process_id'],
				'ref_id' => !empty($data['ref_id'])?$data['ref_id']:'',
				'ref_trans_id' => !empty($data['ref_trans_id'])?$data['ref_trans_id']:'',
				'trans_date' => $data['trans_date'],
				'qty' => !empty($data['ok_qty'])?$data['ok_qty']:0,
				'rej_found' =>  !empty($data['rej_found'])?$data['rej_found']:0,
				'without_process_qty' =>  !empty($data['without_process_qty'])?$data['without_process_qty']:0, // Used in outsource Receive Form
				'production_time' => !empty($data['production_time'])?$data['production_time']:0,
				'in_challan_no' => !empty($data['in_challan_no'])?$data['in_challan_no']:0,
				'process_by' => $data['process_by'],
				'processor_id' =>$data['processor_id'],
				'shift_id' => !empty($data['shift_id'])?$data['shift_id']:'',
				'operator_id' => !empty($data['operator_id'])?$data['operator_id']:'',
				'wt_nos' => !empty($data['wt_nos'])?$data['wt_nos']:(($prcProcessData->mfg_type == 'Machining')?1:""),
				'logDetail'=>[
					'id'=>'',
					'remark'=>$data['remark'],
					'rej_reason'=>!empty($data['rej_reason'])?$data['rej_reason']:'',
					'rej_param'=>!empty($data['rej_param'])?$data['rej_param']:'',
					'rej_type'=>!empty($data['rej_type'])?$data['rej_type']:'',
					'rej_stage'=>!empty($data['rej_stage'])?$data['rej_stage']:'',
					'rej_by'=>!empty($data['rej_by'])?$data['rej_by']:'',
					'rej_comment'=>!empty($data['rej_comment'])?$data['rej_comment']:'',
					'start_time'=>!empty($data['start_time'])?$data['start_time']:'',
					'end_time'=>!empty($data['end_time'])?$data['end_time']:'',
				]
			];

			$this->printJson($this->sop->savePRCLog($logData));
		endif;	
	}

	public function savePRCLog(){
		$data = $this->input->post(); 
        $errorMessage = array();
		$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'pending_accepted'=>1,'log_process_by'=>1,'single_row'=>1,'trans_type'=>$data['trans_type']]);
        if (empty($data['prc_id'])){ $errorMessage['prc_id'] = "Job Card No. is required.";}
        if (empty($data['prc_process_id'])){ $errorMessage['prc_process_id'] = "Process is required.";}
        if (isset($data['wt_nos']) && empty($data['wt_nos'])){ $errorMessage['wt_nos'] = "Input weight is required.";}
		
		 
		if (empty($data['trans_date']) or $data['trans_date'] == null or $data['trans_date'] == ""){ 
			$errorMessage['trans_date'] = "Date is required."; 
		}else{
			if($prcProcessData->prc_date > $data['trans_date']) :
				$errorMessage['trans_date'] = "Invalid Date.";
			endif;
		}
        if (empty($data['ok_qty']) && empty($data['rej_found'])  && empty($data['without_process_qty'])){
            $errorMessage['production_qty'] = "OK Qty Or Rejection Qty. is required.";
       	}else{
			
			
			$totalProdQty = (!empty($data['ok_qty']))?$data['ok_qty']:0 ;$totalProdQty += (!empty($data['rej_found'])) ? $data['rej_found'] : 0;$totalProdQty += (!empty($data['without_process_qty'])) ? $data['without_process_qty'] : 0;
			$pending_production = 0;
			if($data['process_by'] == 3){
				$challanData = $this->sop->getChallanRequestData(['id'=>$data['ref_trans_id'],'challan_receive'=>1,'single_row'=>1]); 
				$pending_production =$challanData->qty - ($challanData->ok_qty + $challanData->rej_qty+ $challanData->without_process_qty);
			}else{
				$logData = $this->sop->getProcesStates(['prc_process_id'=>$data['prc_process_id'],'trans_type'=>$data['trans_type'],'process_by'=>'0,1,2','rejection_review_data'=>1]);
				$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
				$ok_qty = !empty($logData->ok_qty)?$logData->ok_qty:0;
				$rej_found = !empty($logData->rej_found)?$logData->rej_found:0;
				$rw_qty = !empty($logData->rw_qty)?$logData->rw_qty:0;
				$rej_qty = !empty($logData->rej_qty)?$logData->rej_qty:0;
				$pendingReview = $rej_found - $logData->review_qty;
				$rwData = $this->rejectionReview->getReworkData(['prc_id'=>$data['prc_id'],'decision_type'=>2,'rw_process'=>$data['process_id']]);
				if($data['trans_type'] ==1){
					$pending_production =($in_qty - ($rwData->rw_qty)) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
				}else{
					$pending_production = $rwData->rw_qty - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
				}	
				
			}
			
			if($pending_production < $totalProdQty ||  $totalProdQty < 0) :
				$errorMessage['production_qty'] = "Invalid Qty.".$pending_production;
			else:
				$totalOkQty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
				$totalRejFound = !empty($prcProcessData->rej_found)?$prcProcessData->rej_found:0;
				$totalRwQty = !empty($prcProcessData->rw_qty)?$prcProcessData->rw_qty:0;
				$totalRejQty = !empty($prcProcessData->rej_qty)?$prcProcessData->rej_qty:0;
				$totalPendReview = $totalRejFound - $prcProcessData->review_qty;
				$totalPendProd =($in_qty) - ($totalOkQty+$totalRejQty+$totalRwQty+$totalPendReview+$prcProcessData->ch_qty);
				if($totalPendProd < $totalProdQty ||  $totalProdQty < 0) :
					$errorMessage['production_qty'] = "Invalid Qty.";
				endif;
			endif;

			

		}
		if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
			
            $start_time = !empty($data['start_time'])?$data['start_time']:date("Y-m-d H:i:s");
            $end_time = !empty($data['end_time'])?$data['end_time']:date("Y-m-d H:i:s");
				$start = new DateTime($start_time);
				$end = new DateTime($end_time);
				$diff = $start->diff($end);
				$daysInSecs = $diff->format('%r%a')* 24 * 60 * 60; $hoursInSecs = $diff->h * 60 * 60; $minsInSecs = $diff->i * 60;
				$totalPrdseconds = $daysInSecs + $hoursInSecs + $minsInSecs + $diff->s;
				$data['production_time'] = $totalPrdseconds;
			$logData = [
				'id'=>'',
				'trans_type' => $data['trans_type'],
				'prc_id' => $data['prc_id'],
				'prc_process_id' => $data['prc_process_id'],
				'process_id' => $data['process_id'],
				'ref_id' => !empty($data['ref_id'])?$data['ref_id']:'',
				'ref_trans_id' => !empty($data['ref_trans_id'])?$data['ref_trans_id']:'',
				'trans_date' => $data['trans_date'],
				'qty' => !empty($data['ok_qty'])?$data['ok_qty']:0,
				'rej_found' =>  !empty($data['rej_found'])?$data['rej_found']:0,
				'without_process_qty' =>  !empty($data['without_process_qty'])?$data['without_process_qty']:0, // Used in outsource Receive Form
				'production_time' => !empty($data['production_time'])?$data['production_time']:0,
				'in_challan_no' => !empty($data['in_challan_no'])?$data['in_challan_no']:0,
				'process_by' => $data['process_by'],
				'processor_id' =>$data['processor_id'],
				'shift_id' => !empty($data['shift_id'])?$data['shift_id']:'',
				'operator_id' => !empty($data['operator_id'])?$data['operator_id']:'',
				'wt_nos' => !empty($data['wt_nos'])?$data['wt_nos']:(($prcProcessData->mfg_type == 'Machining')?1:""),
				'logDetail'=>[
					'id'=>'',
					'remark'=>$data['remark'],
					'rej_reason'=>!empty($data['rej_reason'])?$data['rej_reason']:'',
					'rej_param'=>!empty($data['rej_param'])?$data['rej_param']:'',
					'rej_type'=>!empty($data['rej_type'])?$data['rej_type']:'',
					'rej_stage'=>!empty($data['rej_stage'])?$data['rej_stage']:'',
					'rej_by'=>!empty($data['rej_by'])?$data['rej_by']:'',
					'rej_comment'=>!empty($data['rej_comment'])?$data['rej_comment']:'',
					'start_time'=>!empty($data['start_time'])?$data['start_time']:'',
					'end_time'=>!empty($data['end_time'])?$data['end_time']:'',
				]
			];

			$this->printJson($this->sop->savePRCLog($logData));
		endif;	
	}

	public function deletePRCLog(){
		$data = $this->input->post();
		if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->sop->deletePRCLog($data));
        endif;
	}

	public function getPRCLogHtml(){
		$data = $this->input->post(); 
		if($data['process_by'] != 3){$data['process_by'] = "";}
		else{ $data['outsource_without_process'] = 1; }
		$logData = $this->sop->getProcessLogList($data);
		$html="";
        if (!empty($logData)) :
            $i = 1;
            foreach ($logData as $row) :
				// if($row->qty > 0 || $row->rej_found > 0):
					$td = '';
					if($data['process_by'] == 3){
						$td = '<td>'.$row->without_process_qty.'</td><td>'.$row->in_challan_no.'</td>';
					}else{
						$td = '<td>' . $row->emp_name . '</td><td>' . $row->shift_name . '</td>';
					}
					$rejTag = ''; $rejQty = floatval($row->rej_found);
					if(!empty($rejQty)){
						$rejTag .= '<a href="' . base_url('pos/printPRCRejLog/' . $row->id) . '" target="_blank" class="btn btn-sm btn-dark waves-effect waves-light mr-1" title="Rejection Tag"><i class="fas fa-print"></i></a>';
					}
					$productionTag = '<a href="' . base_url('pos/printPRCLog/' . $row->id) . '" target="_blank" class="btn btn-sm btn-success waves-effect waves-light mr-1" title="Tag"><i class="fas fa-print"></i></a>';
					$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deletePRCLog','res_function':'getPrcLogResponse','controller':'sopDesk'}";
					$deleteBtn = '<a class="btn btn-danger btn-sm btn-delete permission-remove" href="javascript:void(0)" onclick="trashSop('.$deleteParam.');" datatip="Remove" flow="down"><i class=" fas fa-trash-alt"></i></a>';
					if($row->qty >= 0):
					$html .='<tr class="text-center">
								<td>' . $i++ . '</td>
								<td>' . (($row->trans_type == 2)?'Rework':'Regular'). '</td>
								<td>' . formatDate($row->trans_date). '</td>
								<td>' . $row->production_time . ' Sec.</td>
								<td '.(empty($data['inputWt'])?'hidden':'').'>' . $row->wt_nos . '</td>
								<td>' . $row->processor_name . '</td>
								<td>' . floatval($row->qty) . '</td>
								<td>' . floatval($row->rej_found) . '</td>
								'.$td.'
								<td>' . $row->remark . '</td>
								<td>' . $productionTag.$rejTag .$deleteBtn . '</td>
							</tr>';
					endif;
            endforeach;
        else :
            $html = '<td colspan="11" class="text-center">No Data Found.</td>';
        endif;
		if($data['process_by'] != 3){
			$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'movement_data'=>1,'log_process_by'=>1,'pending_accepted'=>1,'single_row'=>1,'trans_type'=>$data['trans_type']]); 
			$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
			/* $ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
			$rej_found = !empty($prcProcessData->rej_found)?$prcProcessData->rej_found:0;
			$rw_qty = !empty($prcProcessData->rw_qty)?$prcProcessData->rw_qty:0;
			$rej_qty = !empty($prcProcessData->rej_qty)?$prcProcessData->rej_qty:0;
			$pendingReview = $rej_found - $prcProcessData->review_qty;
			$pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty); */
			$logData = $this->sop->getProcesStates(['prc_process_id'=>$data['prc_process_id'],'trans_type'=>$data['trans_type'],'process_by'=>'0,1,2','rejection_review_data'=>1]);
			$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
			$ok_qty = !empty($logData->ok_qty)?$logData->ok_qty:0;
			$rej_found = !empty($logData->rej_found)?$logData->rej_found:0;
			$rw_qty = !empty($logData->rw_qty)?$logData->rw_qty:0;
			$rej_qty = !empty($logData->rej_qty)?$logData->rej_qty:0;
			$pendingReview = $rej_found - $logData->review_qty;
			$rwData = $this->rejectionReview->getReworkData(['prc_id'=>$data['prc_id'],'decision_type'=>2,'rw_process'=>$prcProcessData->current_process_id]);
			if($data['trans_type'] ==1){
				$pending_production =($in_qty - ($rwData->rw_qty)) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
			}else{
				$pending_production = $rwData->rw_qty - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
			}
		}else{
			$challanData = $this->sop->getChallanRequestData(['id'=>$data['ref_trans_id'],'challan_receive'=>1,'single_row'=>1]); 
			$pending_production =$challanData->qty - ($challanData->ok_qty + $challanData->rej_qty+ $challanData->without_process_qty);
		}
		
		$this->printJson(['status'=>1,'tbodyData'=>$html,'pendingQty'=>$pending_production]);
	}

	public function prcMovement(){
		$data = $this->input->post();
		$this->data['dataRow'] = $this->sop->getPRCProcessList(['id'=>$data['id'],'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1,'single_row'=>1]);
		$this->data['machineList'] = $this->item->getItemList(['item_type'=>5]);
        $this->data['locationList'] = $this->storeLocation->getStoreLocationList(['final_location'=>1]);
		$this->load->view('sopDesk/prc_movement_form',$this->data);
	}

	public function savePRCMovement(){
		$data = $this->input->post(); 
        $errorMessage = array();
        if (empty($data['prc_id'])){ $errorMessage['prc_id'] = "Job Card No. is required.";}
        if (empty($data['prc_process_id'])){ $errorMessage['prc_process_id'] = "Process is required.";}

		$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
		if (empty($data['trans_date']) or $data['trans_date'] == null or $data['trans_date'] == ""){ 
			$errorMessage['trans_date'] = "Date is required.";
		}else{
			if($prcProcessData->prc_date > $data['trans_date']) :
				$errorMessage['trans_date'] = "Invalid Date.";
			endif;
		}
        if (empty($data['qty'])){
            $errorMessage['qty'] = "Qty. is required.";
       	}else{
			$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
			$movement_qty =!empty($prcProcessData->movement_qty)?$prcProcessData->movement_qty:0;
			$pending_movement = $ok_qty - $movement_qty;
			if( $data['qty'] > $pending_movement ||  $data['qty'] < 0) :
				$errorMessage['qty'] = "Invalid Qty.";
			endif;
		}
		if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
			$logData = [
				'id'=>'',
				'prc_id' => $data['prc_id'],
				'prc_process_id' => $data['prc_process_id'],
				'process_id' => $data['process_id'],
				'next_process_id' => $data['next_process_id'],
				'send_to' =>$data['send_to'],
				'processor_id' =>$data['processor_id'],
				'trans_date' => $data['trans_date'],
				'qty' => !empty($data['qty'])?$data['qty']:0,
				'wt_nos' =>  !empty($data['wt_nos'])?$data['wt_nos']:0,
				'remark' => !empty( $data['remark'])? $data['remark']:'',
			];
			$this->printJson($this->sop->savePRCMovement($logData));
		endif;	
	}

	public function deletePRCMovement(){
		$data = $this->input->post();
		if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->sop->deletePRCMovement($data));
        endif;
	}

	public function getPRCMovementHtml(){
		$data = $this->input->post(); 
		$movementData = $this->sop->getProcessMovementList(['prc_process_id'=>$data['prc_process_id']]);
		$html="";
        if (!empty($movementData)) :
            $i = 1;
            foreach ($movementData as $row) :
				if($row->qty > 0):
					$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deletePRCMovement','res_function':'getPrcMovementResponse'}";
					$deleteBtn = '<a class="btn btn-danger btn-sm btn-delete permission-remove" href="javascript:void(0)" onclick="trashSop('.$deleteParam.');" datatip="Remove" flow="down"><i class=" fas fa-trash-alt"></i></a>';
					
					$printTag = '<a href="' . base_url('pos/printPRCMovement/' . $row->id) . '" target="_blank" class="btn btn-sm btn-info waves-effect waves-light mr-2" title="Print"><i class="fas fa-print"></i></a>';
                    $html .='<tr class="text-center">
                                <td>' . $i++ . '</td>
                                <td>' . formatDate($row->trans_date). '</td>
                                <td>' . $row->send_to_name . ' </td>
                                <!--<td>' . $row->processor_name . '</td>-->
                                <td>' . floatval($row->qty) . '</td>
                                <!--<td>' . $row->wt_nos . '</td>-->
                                <td>' . $row->remark . '</td>
                                <td>' . $printTag.$deleteBtn . '</td>
                            </tr>';
				endif;
            endforeach;
        else :
            $html = '<td colspan="6" class="text-center">No Data Found.</td>';
        endif;

		$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
		$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
		$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
		$movement_qty =!empty($prcProcessData->movement_qty)?$prcProcessData->movement_qty:0;
		$pending_movement = $ok_qty - $movement_qty;
		$this->printJson(['status'=>1,'tbodyData'=>$html,'pendingQty'=>$pending_movement]);
	}

	public function prcAccept(){
		$data = $this->input->post();
		$this->data['accepted_process_id'] = $data['id'];
		$this->data['prc_id'] = $data['prc_id'];
		$this->data['prev_prc_process_id'] = $data['prev_prc_process_id'];
		$this->load->view('sopDesk/accept_prc_qty',$this->data);
	}

	public function saveAcceptedQty(){
		$data = $this->input->post(); 
		$errorMessage = array();
        if (empty($data['accepted_process_id'])){ $errorMessage['accepted_process_id'] = "Prc Process required.";}
        if (empty($data['accepted_qty']) &&  empty($data['short_qty'])) {  $errorMessage['accepted_qty'] = "Quantity is required.";}
		else{
			$acceptedQty = !empty($data['accepted_qty'])?$data['accepted_qty']:0;
			$shortQty = !empty($data['short_qty'])?$data['short_qty']:0;
			$totalQty = $acceptedQty + $shortQty;
			$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['accepted_process_id'],'pending_accepted'=>1,'single_row'=>1]); 
			$pending_accept =!empty($prcProcessData->pending_accept)?$prcProcessData->pending_accept:0;
			if($acceptedQty > $pending_accept){
				$errorMessage['accepted_qty'] = "Accept Quantity is Invalid.";
			}
			if(!empty($shortQty)){
				$pendingShort =( $pending_accept - $acceptedQty);
				if($shortQty > $pendingShort){
					$errorMessage['short_qty'] = " Short Quantity is Invalid.";
				}
			}
		}
        if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
			$data['trans_date'] = date("Y-m-d");
			$data['created_by'] = $this->loginId;
			$data['created_at'] = date("Y-m-d H:i:s");
			$result = $this->sop->saveAcceptedQty($data);
			$this->printJson($result);
		endif;
	}

	public function getPRCAcceptHtml(){
		$data = $this->input->post(); 
		$acceptData = $this->sop->getPrcAcceptData(['accepted_process_id'=>$data['accepted_process_id']]);
		$html="";
        if (!empty($acceptData)) :
            $i = 1;
            foreach ($acceptData as $row) :
					if($row->accepted_qty > 0 || $row->short_qty > 0):
						$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deletePrcAccept','res_function':'getPrcAcceptResponse','controller':'sopDesk'}";
						$deleteBtn = '<a class="btn btn-outline-danger btn-sm btn-delete permission-remove" href="javascript:void(0)" onclick="trashSop('.$deleteParam.');" datatip="Remove" flow="down"><i class=" fas fa-trash-alt"></i></a>';
						$html .='<tr class="text-center">
									<td>' . $i++ . '</td>
									<td>' . formatDate($row->trans_date). '</td>
									<td>' . floatval($row->accepted_qty) . ' </td>
									<td>' . floatval($row->short_qty) . '</td>
									<td>' . $deleteBtn . '</td>
								</tr>';
					endif;
            endforeach;
        else :
            $html = '<td colspan="5" class="text-center">No Data Found.</td>';
        endif;
		$this->printJson(['status'=>1,'tbodyData'=>$html]);
	}

	public function deletePrcAccept(){
		$data = $this->input->post();
		if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->sop->deletePrcAccept($data));
        endif;
	}

	public function challanRequest(){
		$data = $this->input->post();
		$this->data['dataRow'] = $this->sop->getPRCProcessList(['id'=>$data['id'],'single_row'=>1]);
		$this->load->view('sopDesk/prc_challan_request',$this->data);
	}

	public function saveChallanRequest(){
		$data = $this->input->post();
        $errorMessage = array();
        if (empty($data['prc_id'])){ $errorMessage['prc_id'] = "Job Card No. is required.";}
        if (empty($data['prc_process_id'])){ $errorMessage['prc_process_id'] = "Process is required.";}
		if (empty($data['trans_date']) or $data['trans_date'] == null or $data['trans_date'] == ""){ $errorMessage['trans_date'] = "Date is required."; }
        if (empty($data['qty'])){
            $errorMessage['qty'] = "Request qty required";
       	}else{
			$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'pending_accepted'=>1,'single_row'=>1,'log_process_by'=>1,'trans_type'=>$data['trans_type']]); 
			$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
			/* $ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
			$rej_found = !empty($prcProcessData->rej_found)?$prcProcessData->rej_found:0;
			$rw_qty = !empty($prcProcessData->rw_qty)?$prcProcessData->rw_qty:0;
			$rej_qty = !empty($prcProcessData->rej_qty)?$prcProcessData->rej_qty:0;
			$pendingReview = $rej_found - $prcProcessData->review_qty;
			$pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty); */
			$logData = $this->sop->getProcesStates(['prc_process_id'=>$data['prc_process_id'],'trans_type'=>$data['trans_type'],'process_by'=>'0,1,2','rejection_review_data'=>1]);
			$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
			$ok_qty = !empty($logData->ok_qty)?$logData->ok_qty:0;
			$rej_found = !empty($logData->rej_found)?$logData->rej_found:0;
			$rw_qty = !empty($logData->rw_qty)?$logData->rw_qty:0;
			$rej_qty = !empty($logData->rej_qty)?$logData->rej_qty:0;
			$pendingReview = $rej_found - $logData->review_qty;
			$rwData = $this->rejectionReview->getReworkData(['prc_id'=>$data['prc_id'],'decision_type'=>2,'rw_process'=>$prcProcessData->current_process_id]);
			if($data['trans_type'] ==1){
				$pending_production =($in_qty - ($rwData->rw_qty)) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
			}else{
				$pending_production = $rwData->rw_qty - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
			}
			
			if($pending_production < $data['qty'] ||  $data['qty'] < 0) :
				$errorMessage['qty'] = "Invalid Qty.".$pending_production;
			endif;
		}
		if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
			$data['old_qty'] = $data['qty'];
			$this->printJson($this->sop->saveChallanRequest($data));
		endif;	
	}

	public function deleteChallanRequest(){
		$data = $this->input->post();
		if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->sop->deleteChallanRequest($data));
        endif;
	}

	public function getChallanRequestHtml(){
		$data = $this->input->post();
		$requestData = $this->sop->getChallanRequestData(['prc_process_id'=>$data['prc_process_id']]);
		$html="";
        if (!empty($requestData)) :
            $i = 1;
            foreach ($requestData as $row) :
				$deleteBtn = "";
				if($row->challan_id == 0){
					$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deleteChallanRequest','res_function':'getChallanRequestResponse'}";
					$deleteBtn = '<a class="btn btn-outline-danger btn-sm btn-delete permission-remove" href="javascript:void(0)" onclick="trashSop('.$deleteParam.');" datatip="Remove" flow="down"><i class=" fas fa-trash-alt"></i></a>';
				}
				$html .='<tr class="text-center">
							<td>' . $i++ . '</td>
							<td>' . (($row->trans_type == 2)?'Rework':'Regular'). '</td>
							<td>' . formatDate($row->trans_date). '</td>
							<td>' . $row->qty . ' </td>
							<td>' . $deleteBtn . '</td>
						</tr>';
            endforeach;
        else :
            $html = '<td colspan="5" class="text-center">No Data Found.</td>';
        endif;
    	$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'pending_accepted'=>1,'single_row'=>1,'log_process_by'=>1,'trans_type'=>$data['trans_type']]); 
		$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
		/* $ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
		$rej_found = !empty($prcProcessData->rej_found)?$prcProcessData->rej_found:0;
		$rw_qty = !empty($prcProcessData->rw_qty)?$prcProcessData->rw_qty:0;
		$rej_qty = !empty($prcProcessData->rej_qty)?$prcProcessData->rej_qty:0;
		$pendingReview = $rej_found - $prcProcessData->review_qty;
		$pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty); */
		$logData = $this->sop->getProcesStates(['prc_process_id'=>$data['prc_process_id'],'trans_type'=>$data['trans_type'],'process_by'=>'0,1,2','rejection_review_data'=>1]);
		$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
		$ok_qty = !empty($logData->ok_qty)?$logData->ok_qty:0;
		$rej_found = !empty($logData->rej_found)?$logData->rej_found:0;
		$rw_qty = !empty($logData->rw_qty)?$logData->rw_qty:0;
		$rej_qty = !empty($logData->rej_qty)?$logData->rej_qty:0;
		$pendingReview = $rej_found - $logData->review_qty;
		$rwData = $this->rejectionReview->getReworkData(['prc_id'=>$data['prc_id'],'decision_type'=>2,'rw_process'=>$prcProcessData->current_process_id]);
		if($data['trans_type'] ==1){
			$pending_production =($in_qty - ($rwData->rw_qty)) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
		}else{
			$pending_production = $rwData->rw_qty - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
		}
		$this->printJson(['status'=>1,'tbodyData'=>$html,'pending_ch_qty'=>$pending_production]);
	}

	public function addPrcStock(){
		$data = $this->input->post();
		$this->data['dataRow'] = $this->sop->getPRCProcessList(['id'=>$data['id'],'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1,'single_row'=>1]);
		$this->load->view('sopDesk/prc_stock_form',$this->data);
	}

	public function getPRCStockHtml(){
		$data = $this->input->post(); 
		$movementData = $this->sop->getProcessMovementList(['prc_process_id'=>$data['prc_process_id']]);
		$html="";
        if (!empty($movementData)) :
            $i = 1;
            foreach ($movementData as $row) :
				if($row->qty > 0): 
					$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deletePRCMovement','res_function':'getStockResponse','controller':'sopDesk'}";
					$deleteBtn = '<a class="btn btn-danger btn-sm btn-delete permission-remove" href="javascript:void(0)" onclick="trashSop('.$deleteParam.');" datatip="Remove" flow="down"><i class=" fas fa-trash-alt"></i></a>';
                    $html .='<tr class="text-center">
                        <td>' . $i++ . '</td>
                        <td>' . formatDate($row->trans_date). '</td>
                        <td>' . $row->qty . '</td>
                        <td>' . $deleteBtn . '</td>
                    </tr>';
				endif;
            endforeach;
        else :
            $html = '<td colspan="4" class="text-center">No Data Found.</td>';
        endif;

		$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
		$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
		$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
		$movement_qty =!empty($prcProcessData->movement_qty)?$prcProcessData->movement_qty:0;
		$pending_movement = $ok_qty - $movement_qty;
		$this->printJson(['status'=>1,'tbodyData'=>$html,'pendingQty'=>$pending_movement]);
	}

	public function edit(){
		$data = $this->input->post();
        $this->data['customerData'] = $this->salesOrder->getPendingOrderItems(['group_by'=>'party_id']);
		$this->data['dataRow'] = $dataRow = $this->sop->getPRC(['id'=>$data['id']]);
		$this->data['productList'] = $this->item->getItemList(['item_type'=>1]);
		$this->data['processData'] = $this->getProcessList(['process'=>$dataRow->process_ids,'item_id'=>$dataRow->item_id]);
        $this->load->view('sopDesk/prc_form',$this->data);
	}

	public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->sop->deletePRC($id));
        endif;
    }

	public function getProcessorList(){
		$process_by = $this->input->post('process_by');		
		$options = '<option value="0">Select</option>';

		if($process_by == 2){
			$deptList = $this->department->getDepartmentList();
			if(!empty($deptList)){
				foreach($deptList as $row){
					$options .= '<option value="'.$row->id.'">'.$row->name.'</option>';
				}
			}
		}else{
			$machineList = $this->item->getItemList(['item_type'=>5]);
			if(!empty($machineList)){
				foreach($machineList as $row){
					$options .= '<option value="'.$row->id.'">[ '.$row->item_code. ' ] '.$row->item_name.'</option>'; 
				}
			}
		}
		$this->printJson(['status'=>1, 'options'=>$options]);
	}

	public function receiveStoredMaterial(){
        $data = $this->input->post();
		$this->data['dataRow'] = $this->sop->getPRCProcessList(['id'=>$data['id'],'single_row'=>1]);
        $this->data['movementList'] = $this->sop->getProcessMovementList(['prc_process_id'=>$data['id'],'send_to'=>4]);
        $this->load->view('sopDesk/receive_movement',$this->data);
    }

    public function saveReceiveStoredMaterial(){
        $data = $this->input->post(); 
        $errorMessage = array();
		
        if(empty(array_sum($data['qty']))){ $errorMessage['general_qty'] = "Qty is required.";}
		else{
			foreach($data['qty'] as $key=>$qty){
				$movementData = $this->sop->getProcessMovementList(['id'=>$data['trans_id'][$key],'single_row'=>1]);
				if($qty > $movementData->qty){
					$errorMessage['qty'.$data['trans_id'][$key]] = "Qty is invalid.";
				}
			}
		}
        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $this->printJson($this->sop->saveReceiveStoredMaterial($data));
        endif;
    }

	public function clearPrcData(){
		$data = $this->input->post();
        if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
			$result = $this->sop->clearPrcData($data);
			$result['prc_id'] = $data['id'];
            $this->printJson($result);
        endif;
	}

	public function requiredMaterial(){
		$data = $this->input->post();
		$this->data['prc_id'] = $data['id'];
		$this->data['kitData'] = $this->item->getProductKitData(['item_id'=>$data['item_id']]);
		$this->data['prcBom'] = $this->sop->getPrcBomData(['prc_id'=>$data['id'],'production_data'=>1]);
		$this->load->view('sopDesk/prc_bom',$this->data);
	}

	public function savePrcMaterial(){
		$data = $this->input->post();
		if(empty($data['item_id'])){ $errorMessage['general_error'] = "Item is required."; }
		if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $this->printJson($this->sop->savePrcMaterial($data));
        endif;
	}

	public function materialReturn(){
		$data = $this->input->post();
		$this->data['dataRow'] = $this->sop->getBatchData(['prc_id'=>$data['prc_id'],'item_id'=>$data['item_id']]); //$this->sop->printQuery();
		$this->data['locationList'] = $this->storeLocation->getStoreLocationList(['store_type'=>'0,1,2','final_location'=>1]);
		$this->load->view('sopDesk/prc_material_return',$this->data);
	}
	
	public function storeReturnedMaterial(){
		$data = $this->input->post();
		if(empty($data['item_id'])){ $errorMessage['general_error'] = "Item is required."; }
		if(empty($data['location_id'])){ $errorMessage['general_error'] = "Location is required."; }
		if(empty($data['batch_no'])){ $errorMessage['batch_no'] = "Batch No is required."; }
		if(empty($data['qty'])){ $errorMessage['qty'] = "Qty is required."; }
		else{
			$stockData =$this->sop->getMaterialIssueData(['prc_id'=>$data['prc_id'],'item_id'=>$data['item_id'],'single_row'=>1]);
			if($data['qty'] > $stockData->issue_qty){ $errorMessage['qty'] = "Qty is invalid."; }
			else{
				$issueData =  $this->sop->getMaterialIssueData(['prc_id'=>$data['prc_id'],'group_name'=>$data['bom_group'],'production_data'=>1,'stock_data'=>1,'single_row'=>1,'return_data'=>1,'group_by'=>'item_kit.group_name']);
				if($issueData->category_id == 55){
					$usedQty = round($issueData->issue_qty - ($issueData->return_qty + $issueData->used_material),3);
				}else{
					$usedQty = round($issueData->issue_qty - ($issueData->scrap_qty + $issueData->return_qty + $issueData->used_material),3);
				}
				
				if(round($data['qty'],3) > $usedQty){ $errorMessage['qty'] = "Qty is not available."; }
			}
		}

		if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $this->printJson($this->sop->storeReturnedMaterial($data));
        endif;
		
	}

	public function getReturnHtml(){
		$data = $this->input->post();
		$batchData = $batchData = $this->itemStock->getItemStockBatchWise(['child_ref_id'=>$data['prc_id'],'item_id'=> $data['item_id'],'entry_type'=>'1001','supplier'=>1,'group_by'=>'stock_transaction.id']);
		$html = "";
		if(!empty($batchData)){
			$i=1;
			foreach($batchData as $row){
				$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deleteReturn','res_function':'getReturnResponse'}";
				$deleteBtn = '<a class="btn btn-danger btn-sm btn-delete permission-remove" href="javascript:void(0)" onclick="trashSop('.$deleteParam.');" datatip="Remove" flow="down"><i class=" fas fa-trash-alt"></i></a>';
				$html .= '<tr>
					<td>'.$i++.'</td>
					<td>'.formatDate($row->ref_date).'</td>
					<td>'.$row->location.'</td>
					<td>'.$row->heat_no.' [Batch No : '.$row->batch_no.']</td>
					<td>'.$row->qty.'</td>
					<td>'.$row->remark.'</td>
					<td>'.$deleteBtn.'</td>
				</tr>';
			}
		} else {
			$html = '<td colspan="7" class="text-center">No Data Found.</td>';
		}
            
		$stockData = $this->sop->getMaterialIssueData(['prc_id'=>$data['prc_id'],'group_name'=>$data['bom_group'],'production_data'=>1,'stock_data'=>1,'single_row'=>1,'return_data'=>1,'group_by'=>'item_kit.group_name']);
		if($stockData->category_id == 55){
			$stockQty = round($stockData->issue_qty - ($stockData->used_material + $stockData->return_qty),3);
		}else{
			$stockQty = round($stockData->issue_qty - ($stockData->used_material + $stockData->return_qty +  $stockData->scrap_qty),3);
		}
		
		
		$this->printJson(['status'=>1,'tbodyData'=>$html,'pending_qty'=>$stockQty]);
	}

	public function deleteReturn(){
		$id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->sop->deleteReturn($id));
        endif;
	}

	function printDetailRouteCard($id){
		$prcData = $this->data['prcData'] = $this->sop->getPRCDetail(['prc_id'=>$id]);
		if(!empty($prcData))
		{
    		$this->data['prcMaterialData'] = $this->sop->getMaterialIssueData(['prc_id'=>$id,'production_data'=>1,'stock_data'=>1]);
    		$this->data['prcProcessData'] = (!empty($prcData->prcProcessData)) ? $prcData->prcProcessData : [];
    		$prcProcessData = $this->sop->getPRCProcessList(['current_process_id'=>0,'prc_id'=>$id,'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
		}

		$this->data['letter_head']=base_url('assets/images/letterhead_top.png');
        $pdfData = $this->load->view('sopDesk/print_route_card', $this->data, true);
		// print_r($pdfData);exit;
        $printedBy = $this->employee->getEmployee(['id'=>$this->loginId]);
		$htmlHeader = '<img src="'.$this->data['letter_head'].'" class="img">';
        $htmlFooter = '
			<table class="table top-table" style="margin-top:10px;border-top:1px solid #000000;">
				<tr>
					<td style="width:50%;">
					    Created By & Date : '.$prcData->emp_name.' ('.formatDate($prcData->created_at, 'd-m-Y H:s:i').')<br>
					    Printed By & Date : '.$printedBy->emp_name.' ('.formatDate(date('Y-m-d H:s:i'), 'd-m-Y H:s:i').')
					</td>
					<td style="width:50%;text-align:right;">Page No. {PAGENO}/{nbpg}</td>
				</tr>
			</table>';

        $mpdf = new \Mpdf\Mpdf();

        $pdfFileName = 'PRC-' . $id . '.pdf';
        $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetProtection(array('print'));
		$mpdf->SetHTMLHeader($htmlHeader);
		$mpdf->SetHTMLFooter($htmlFooter);
		$mpdf->useSubstitutions = false;
		$mpdf->simpleTables = true;

        $mpdf->AddPage('P', '', '', '', '', 5, 5, 38, 20, 5, 5, '', '', '', '', '', '', '', '', '', 'A4-P');
        $mpdf->WriteHTML($pdfData);
        $mpdf->Output($pdfFileName, 'I');
    }  

	/*** Cutting PRC */
		public function cuttingIndex(){
			$this->data['headData']->pageTitle = "Cutting";
			$this->data['headData']->pageUrl = "sopDesk/cuttingIndex";
			$this->data['tableHeader'] = getProductionDtHeader('cutting');
			$this->load->view('cutting/index',$this->data);
		}

		public function getDTRows($status = 1){
			$data = $this->input->post();$data['status'] = $status;
			$result = $this->sop->getCuttingDTRows($data);
			$sendData = array();$i=($data['start']+1);
			foreach($result['data'] as $row):          
				$row->sr_no = $i++;         
				$sendData[] = getCuttingData($row);
			endforeach;
			$result['data'] = $sendData;
			$this->printJson($result);
		}

		public function addCuttingPRC(){
			$this->data['prc_prefix'] = 'CUT/'.getYearPrefix('SHORT_YEAR').'/';
			$this->data['prc_no'] = $this->sop->getNextPRCNo(2);
			$this->data['customerData'] = $this->salesOrder->getPendingOrderItems(['group_by'=>'party_id']);
			$this->load->view('cutting/form',$this->data);
		}

		public function editCutting(){
			$data = $this->input->post();
			$this->data['dataRow'] = $dataRow = $this->sop->getPRC(['id'=>$data['id']]);        
			$this->data['customerData'] = $this->salesOrder->getPendingOrderItems(['group_by'=>'party_id']);
			$this->data['productData'] = $this->getProductList(['party_id'=>$dataRow->party_id,'item_id'=>$dataRow->item_id]);
			$this->load->view('cutting/form',$this->data);
		}

		public function saveCutting(){
			$data = $this->input->post(); 
			$errorMessage = array();
			if ($data['party_id'] == ""){ $errorMessage['party_id'] = "Customer is required."; }
			if (empty($data['item_id'])){ $errorMessage['item_id'] = "Product is required.";}
			if (empty($data['qty']) || $data['qty'] < 0){ $errorMessage['qty'] = "Quantity is required.";}
			if (empty($data['prc_date'])){ $errorMessage['prc_date'] = "PRC Date is required.";}
		
			if (!empty($errorMessage)) :
				$this->printJson(['status' => 0, 'message' => $errorMessage]);
			else :
				$masterData = [
					'id'=>$data['id'],
					'prc_type'=>2,
					'prc_no'=>$data['prc_no'],
					'prc_number'=>$data['prc_number'],
					'prc_date'=>$data['prc_date'],
					'item_id'=>$data['item_id'],	
					'party_id'=>$data['party_id'],
					'so_trans_id'=>$data['so_trans_id'],
					'prc_qty'=>$data['qty'],
					'target_date'=>$data['target_date']
				];
				$prcDetail = [
					'remark'=>$data['remark'],
					'id'=>$data['prc_detail_id'],
					'cutting_length'=>$data['cutting_length'],
					'cutting_dia'=>$data['cutting_dia'],
					'cut_weight'=>$data['cut_weight'],
				];
				if(empty($data['id'])){
					$masterData['created_by'] = $this->session->userdata('loginId');
					$prcDetail['created_by'] = $this->session->userdata('loginId');
				}else{
					$masterData['updated_by'] = $this->session->userdata('loginId');
					$prcDetail['updated_by'] = $this->session->userdata('loginId');
				}
				$sendData['masterData'] = $masterData;
				$sendData['prcDetail'] = $prcDetail;
				$this->printJson($this->sop->savePRC($sendData));
			endif;
		}

		public function addCuttingLog(){
			$data = $this->input->post();
			$this->data['dataRow'] = $this->sop->getPrc(['id'=>$data['id']]);
			$this->load->view('cutting/cutting_log_form',$this->data);

		}

		public function getCuttingLogHtml(){
			$data = $this->input->post();
			$logData = $this->sop->getProcessLogList(['prc_id'=>$data['prc_id']]);
			$html = "";
			if(!empty($logData)){
				$i = 1;
				foreach($logData as $row){
					$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deleteCuttingLog','res_function':'getCuttingResponse','controller':'sopDesk'}";
					$deleteBtn = '<a class="btn btn-danger btn-sm btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class=" fas fa-trash-alt"></i></a>';
					$html.= '<tr>
								<td>' . $i++ . '</td>
								<td>' . formatDate($row->trans_date). '</td>
								<td>' . floatval($row->qty) . '</td>
								<td>' . floatval($row->wt_nos) . '</td>
								<td>' . $row->remark . '</td>
								<td>' . $deleteBtn . '</td>
							</tr>';
				}
			}else{
				$html.= '<tr><th class="text-center" colspan="6">No data available</th></tr>';
			}
			$logData = $this->sop->getCuttingPrcData(['id'=>$data['prc_id'],'production_data'=>1,'single_row'=>1]);
			$this->printJson(['status'=>1,'tbodyData'=>$html,'production_qty'=>$logData->production_qty]);
		}

		public function saveCuttingLog(){
			$data = $this->input->post(); 
			$errorMessage = array();
			if (empty($data['prc_id'])){ $errorMessage['prc_id'] = "Job Card No. is required.";}
			
			if (empty($data['trans_date']) or $data['trans_date'] == null or $data['trans_date'] == ""){ $errorMessage['trans_date'] = "Date is required."; }
			if (empty($data['qty'])){
				$errorMessage['qty'] = "Qty. is required.";
			}
			if (!empty($errorMessage)) :
				$this->printJson(['status' => 0, 'message' => $errorMessage]);
			else :

				$data['logDetail'] = [
					'id'=>"",
					'remark'=>$data['remark'],
				];
				unset($data['remark']);
				$this->printJson($this->sop->saveCuttingLog($data));
			endif;	
		}

		public function deleteCuttingLog(){
			$data = $this->input->post();
			if(empty($data['id'])):
				$this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
			else:
				$this->printJson($this->sop->deleteCuttingLog($data));
			endif;
		}

		public function getMaterialDetail(){
			$postData = $this->input->post();
			
			$prcData = $this->data['prcData'] = $this->sop->getPRCDetail(['prc_id'=>$postData['id']]);
			$this->data['status'] = (!empty($prcData->status)) ? $prcData->status : 1;
			$this->data['prcMaterialData'] = $this->sop->getPrcBomData(['prc_id'=>$postData['id'],'production_data'=>1,'stock_data'=>1]);
			$this->data['prcMaterialDetail'] = $this->load->view('sopDesk/prc_material',$this->data,true);
			$this->load->view('cutting/material_return',$this->data);
		}

		public function cuttingPrint($id){
			$prcData = $this->data['prcData'] = $this->sop->getPRCDetail(['prc_id'=>$id]);
			$this->data['prcMaterialData'] = $this->sop->getPrcBomData(['prc_id'=>$id,'production_data'=>1,'stock_data'=>1]);
			$pdfData = $this->load->view('cutting/print_view',$this->data,true);
			$printedBy = $this->employee->getEmployee(['id'=>$this->loginId]);
			$this->data['letter_head']=base_url('assets/images/letterhead_top.png');
			$htmlHeader = '<img src="'.$this->data['letter_head'].'" class="img">';
			$htmlFooter = '
				<table class="table top-table" style="margin-top:10px;border-top:1px solid #000000;">
					<tr>
						<td style="width:50%;">
							Printed at : '.$printedBy->emp_name.' ('.formatDate(date('Y-m-d H:s:i'), 'd-m-Y H:s:i').')
						</td>
						<td style="width:50%;text-align:right;">Page No. {PAGENO}/{nbpg}</td>
					</tr>
				</table>';

			$mpdf = new \Mpdf\Mpdf();

			$pdfFileName = 'CUT-' . $id . '.pdf';
			$stylesheet = file_get_contents(base_url('assets/css/pdf_style.css?v='.time()));
			$mpdf->WriteHTML($stylesheet, 1);
			$mpdf->SetDisplayMode('fullpage');
			$mpdf->SetProtection(array('print'));
			$mpdf->SetHTMLHeader($htmlHeader);
			$mpdf->SetHTMLFooter($htmlFooter);
			$mpdf->useSubstitutions = false;
			$mpdf->simpleTables = true;

			$mpdf->AddPage('P', '', '', '', '', 5, 5, 38, 20, 5, 5, '', '', '', '', '', '', '', '', '', 'A4-P');
			$mpdf->WriteHTML($pdfData);
			$mpdf->Output($pdfFileName, 'I');
		}
	/*** End Cutting*/
	
	/* End-Piece Return */ 
	public function endPcsReturn(){
		$data = $this->input->post();
		$this->data['dataRow'] = $data;
		$this->data['prcMaterialData'] = $this->sop->getMaterialIssueData(['prc_id'=>$data['prc_id'],'production_data'=>1,'return_data'=>1]);
        $this->data['locationList'] = $this->storeLocation->getStoreLocationList(['final_location'=>1,'store_type'=>0]);
		$this->load->view('sopDesk/end_pcs_return',$this->data);
	}
	

	public function getScrapGroupList(){
		$data = $this->input->post();
		if($data['entry_type'] == 1002){
			$gradeData = $this->materialGrade->getMaterial(['id'=>$data['grade_id']]);
			$scrapList = $this->scrapGroup->getScrapGroupList(['scrap_id'=>$gradeData->scrap_group]);
	
			$options='<option value="">Select Scrap Group</option>';
			if(!empty($scrapList)){
				foreach($scrapList as $row){
					$options .= '<option value="'.$row->id.'">'.$row->item_name.'</option>';
				}
			}
		}else{
			$itemData = $this->item->getItemList(['item_type'=>3,'category_id'=>55]);
			$options='<option value="">Select Item</option>';
			if(!empty($itemData)){
				foreach($itemData as $row){
					$options .= '<option value="'.$row->id.'">'.$row->item_name.'</option>';
				}
			}
		}
		
		$totalQty = 0;

		$this->printJson(['options'=>$options, 'totalQty'=>(($totalQty > 0)?$totalQty:0)]);
	}

	public function saveEndPcsReturn(){
		$data = $this->input->post();
        $errorMessage = array(); 
		if(empty($data['rm_item_id'])){  $errorMessage['rm_item_id'] = "Product is required.";  }
		if(empty($data['scrap_item_id'])){  $errorMessage['scrap_item_id'] = "Required.";  }
		if(empty($data['qty'])){ 
			$errorMessage['qty'] = "Qty is required."; 
		}else{
			$itemData = $this->item->getItem(['id'=>$data['rm_item_id']]);
			if($itemData->category_id != 55){
				$stockData =$this->sop->getMaterialIssueData(['prc_id'=>$data['prc_id'],'item_id'=>$data['rm_item_id'],'single_row'=>1]);
				if($data['qty'] > $stockData->issue_qty){ 
					$errorMessage['qty'] = "Qty is invalid."; 
				}
				else{
					$issueData =  $this->sop->getMaterialIssueData(['prc_id'=>$data['prc_id'],'group_name'=>'RM GROUP','production_data'=>1,'stock_data'=>1,'single_row'=>1,'return_data'=>1,'group_by'=>'item_kit.group_name']);
					$used_material = round(($issueData->used_material - $issueData->used_scrap),3);
					$availableQty = round(($issueData->issue_qty - ($issueData->scrap_qty + $issueData->return_qty + $issueData->used_material)),3);
					$scrapDetail = $this->scrapGroup->getScrapGroup(['id'=>$data['scrap_item_id']]);
					if($scrapDetail->stock_type == 2 && $data['entry_type'] == 1002){
						// If Endpc Minus from issue qty then check validation on available qty (issue - (used+return))
						if(round($data['qty'],3) > $availableQty){ $errorMessage['qty'] = "Qty is not available. Available Qty is ".$availableQty; }
					}else{ 
						// If Endpc Minus from Used qty then check validation on Used qty
						if(round($data['qty'],3) > $used_material){ $errorMessage['qty'] = "Qty is not available. Used Qty is ".$used_material; }
					}
				}
			}
		}

		if($data['entry_type'] == 1003 && empty($data['qty_pcs'])){ $errorMessage['qty_pcs'] = "Qty is required.";  }
		if($data['entry_type'] == 1003 && empty($data['location_id'])){ $errorMessage['location_id'] = "Location is required.";  }
		
		if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $this->printJson($this->sop->saveEndPcsReturn($data));
        endif;		
	}

	public function getEndPcsReturnHtml(){
		$data = $this->input->post();
		$stockData = $this->sop->getEndPcsStock(['main_ref_id'=>$data['prc_id'],'child_ref_id'=>$data['prc_process_id'],'entry_type'=>'1002,1003','group_by'=>'stock_transaction.id']);
		$html = "";
		if(!empty($stockData)){
			$i=1;
			foreach($stockData as $row){
				$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deleteReturn','res_function':'getEndPcsReturnResponse'}";
				$deleteBtn = '<a class="btn btn-danger btn-sm btn-delete permission-remove" href="javascript:void(0)" onclick="trashSop('.$deleteParam.');" datatip="Remove" flow="down"><i class=" fas fa-trash-alt"></i></a>';
				$qty = floatval($row->qty);
				if($row->entry_type == 1003){
					$qty = floatval($row->qty).' pcs'.'<hr style="margin:0px">'. floatval($row->size).' kgs';
				}
				$html .= '<tr class="text-center">
							<td>'.$i++.'</td>
							<td>'.formatDate($row->ref_date).'</td>
							<td>'.$row->rm_item_name.'</td>
							<td>'.$row->scrap_item.'</td>
							<td>'.$qty.'</td>
							<td>'.$deleteBtn.'</td>
						</tr>';
			}
		} else {
			$html = '<td colspan="6" class="text-center">No Data Found.</td>';
		}
		
		$stockData = $this->sop->getEndPcsStock(['main_ref_id'=>$data['prc_id'],'child_ref_id'=>$data['prc_process_id'],'entry_type'=>'1002,1003','single_row'=>1]);
		$stockQty = floatval($data['issue_qty']) - ((!empty($stockData->stock_qty)?floatval($stockData->stock_qty):0) + floatval($data['qty']));

		$this->printJson(['status'=>1,'tbodyData'=>$html,'pending_qty'=>$stockQty]);
	}

	public function deleteEndPcsReturn(){
		$id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->sop->deleteEndPcsReturn($id));
        endif;
	}
	
	/* Update PRC Qty */
	public function updatePrcQty(){
        $this->data['prc_id'] = $this->input->post('id');
        $this->load->view('sopDesk/prc_update', $this->data);
    }

    public function getUpdatePrcQtyHtml(){
        $data = $this->input->post();
        $logdata = $this->sop->getPrcLogData(['prc_id'=> $data['prc_id']]); 
        $tbodyData = ''; 
        if(!empty($logdata)): $i=1; 
            foreach($logdata as $row): 
                $deleteParam = $row->id . ",'PRC Qty'";
                $logType = ($row->log_type == 1)?'(+) Add':'(-) Reduce';
                $deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deletePrcUpdateQty','res_function':'updatePrcQtyHtml','controller':'sopDesk'}";
                $deleteBtn = '<a class="btn btn-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trash('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i></a>';

                $tbodyData .= '<tr>
                    <td>'.$i++.'</td>
                    <td>'.formatDate($row->log_date).'</td>
                    <td>'.$logType.'</td>
                    <td>'.$row->qty.'</td>
                    <td>'.$deleteBtn.'</td>
                </tr>';
            endforeach;
		else:
			$tbodyData .= '<tr class="text-center"><td colspan="5">Data not available.</td></tr>';
        endif; 
        $this->printJson(['status' => 1, 'tbodyData' => $tbodyData]);
    }
    
	public function savePrcQty(){
        $data = $this->input->post();  
        $errorMessage = array();

		if (empty($data['qty'])) :
			$errorMessage['qty'] = "Qty is required.";
		endif;

        if ($data['log_type'] == -1) :		
            $prcData = $this->sop->getPRC(['id'=>$data['prc_id']]);
            $firstProcess = explode(",",$prcData->process_ids)[0];
			$prcProcessData = $this->sop->getPRCProcessList(['current_process_id'=>$firstProcess,'prc_id'=>$data['prc_id'],'single_row'=>1,'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1,'log_process_by'=>1]);
			
			$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
			$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
			$rej_found = !empty($prcProcessData->rej_found)?$prcProcessData->rej_found:0;
			$rw_qty = !empty($prcProcessData->rw_qty)?$prcProcessData->rw_qty:0;
			$rej_qty = !empty($prcProcessData->rej_qty)?$prcProcessData->rej_qty:0;
            $pendingReview = $rej_found - $prcProcessData->review_qty;
            $pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);

            if ($pending_production < $data['qty']) :
                $errorMessage['qty'] = "Invalid Qty.";
            endif;
        endif;
        if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
            $result = $this->sop->savePrcQty($data);
            $this->printJson($result);
        endif;
    }

    public function deletePrcUpdateQty(){
        $id = $this->input->post('id');		
		$logData = $this->sop->getPrcLogData(['id'=>$id,'single_row'=>1]);

        $errorMessage = '';
        if ($logData->log_type == 1) :
            $prcData = $this->sop->getPRC(['id'=>$logData->prc_id]);
            $firstProcess = explode(",",$prcData->process_ids)[0];
			$prcProcessData = $this->sop->getPRCProcessList(['current_process_id'=>$firstProcess,'prc_id'=>$logData->prc_id,'single_row'=>1,'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1,'log_process_by'=>1]);
			
			$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
			$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
			$rej_found = !empty($prcProcessData->rej_found)?$prcProcessData->rej_found:0;
			$rw_qty = !empty($prcProcessData->rw_qty)?$prcProcessData->rw_qty:0;
			$rej_qty = !empty($prcProcessData->rej_qty)?$prcProcessData->rej_qty:0;
            $pendingReview = $rej_found - $prcProcessData->review_qty;
            $pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);
			
            if ($pending_production < $logData->qty) :
                $errorMessage = "Sorry...! You can't delete this PRC log because this qty moved to next process.&&&&".$firstProcess.'#####'.$pending_production .'<'. $logData->qty;
            endif;
        endif;

        if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
            $result = $this->sop->deletePrcUpdateQty($id);
            $this->printJson($result);
        endif;
    }
    
    public function changePrcStatus(){
        $data = $this->input->post();
        if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->sop->changePrcStatus($data));
        endif;
    }

	// Material Request For Machining
	// 26-10-2024
	public function materialRequest(){
		$data = $this->input->post();
		$entryData = $this->transMainModel->getEntryType(['controller'=>'store']);
        $this->data['trans_prefix'] = $entryData->trans_prefix;
        $this->data['trans_no'] = $this->store->getNextReqNo();
        $this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];
        $this->data['itemData'] = $this->item->getItemList(['item_type'=>'1']);
        $this->load->view('sopDesk/material_request_form',$this->data);
	}

	public function saveMaterialRequest() {
        $data = $this->input->post();
        $errorMessage = array();

        if(is_array($data['item_id'])){
            if (empty($data['item_id']))
                $errorMessage['genral_error'] = "Request Items is required.";
        } else {
            if (empty($data['item_id']))
                $errorMessage['item_id'] = "Request Items is required.";
            if (empty($data['req_qty']))
                $errorMessage['req_qty'] = "Qty is required.";
            if (empty($data['trans_date']))
                $errorMessage['trans_date'] = "Date is required.";
        }

        if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
            $this->printJson($this->store->saveRequest($data));
        endif;
    }

	// 26-10-2024
	public function editRequest(){
		$data = $this->input->post();
		$this->data['dataRow'] = $this->store->getRequest($data);
        $this->data['itemData'] = $this->item->getItemList(['item_type'=>'1']);
        $this->load->view('sopDesk/material_request_form',$this->data);
	}

	public function deleteRequest(){
        $data = $this->input->post();
        if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->store->deleteRequest($data['id']));
        endif;
    }

	public function updateMachiningQty(){
        $data = $this->input->post();
		$this->data['prcList'] = $this->sop->getPRCList(['item_id'=>$data['item_id'],'ref_batch'=>$data['batch_no'],'heat_no'=>$data['heat_no'],'status'=>2]);
		
        $this->load->view('sopDesk/machining_update', $this->data);
    }

	public function saveMachiningQty(){
        $data = $this->input->post();  
        $errorMessage = array();

		if (empty($data['qty'])) :
			$errorMessage['qty'] = "Qty is required.";
		else:
			$prcData =  $this->sop->getPRC(['id'=>$data['prc_id']]);
			$stockData = $this->itemStock->getItemStockBatchWise(['location_id'=>$this->MACHINING_STORE->id,'batch_no'=>$prcData->ref_batch,'item_id'=>$prcData->item_id,'single_row'=>1]);
			if($data['qty'] > $stockData->qty){
				$errorMessage['qty'] = "Qty is invalid.";
			}
		endif;

        
        if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
            $result = $this->sop->savePrcQty($data);
            $this->printJson($result);
        endif;
    }
    
    public function changeJobStage(){
        //$this->data['prcList'] = $this->sop->getPRCList(['status'=>4]);
		$entryData = $this->transMainModel->getEntryType(['controller'=>'sopDesk']);
        $this->data['prcList'] = $this->sop->getPRCList(['status'=>4,'prc_stock'=>1,'entry_type'=>$entryData->id]);
        $this->load->view('sopDesk/change_job_stage', $this->data);
    }

	public function getJobStages(){
        $data = $this->input->post();
        $result = $this->getJobStagesHtml($data);;
        
        $this->printJson(['status' => 1, 'stageRows' => $result['stageRows'],'processOptions'=>$result['processOptions']]);
    }

	public function getJobStagesHtml($data){
        $processData = $this->sop->getPRCProcessList(['prc_id'=>$data['prc_id'],'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1]);
		$prcData = $this->sop->getPRC(['id'=>$data['prc_id']]);
        $stageRows = '';
        if (!empty($processData)) :
            $i = 1;
            foreach ($processData as $row) :
				if(!empty($row->prev_id) && $row->inward_qty <= 0){
                    $stageRows .= '<tr id="' . $row->current_process_id . '">
                    <td class="text-center">' . $i. '</td>
                    <td>' . $row->current_process .'</td>
                    <td class="text-center">

                        <input type="hidden" name="current_process_id[]" value="' . $row->current_process_id . '">
                        <button type="button" data-pid="' . $row->current_process_id . '" class="btn btn-outline-danger waves-effect waves-light permission-remove " onclick="removeJobStage(this)"><i class="fas fa-trash"></i></button>
                    </td>
                    </tr>';
                    $i++;
                }
               
            endforeach;
        else :
            echo '<tr><td colspan="3" class="text-center">No Data Found.</td></tr>';
        endif;
        
        $processData = $this->process->getProcessList();
        $processOptions ='<option value="">Select Stage</option>';
        $productProcess = explode(",",  $prcData->process_ids);
        foreach ($processData as $row) :
            if (!empty($productProcess) && (!in_array($row->id, $productProcess))) :
                $processOptions .= '<option value="' . $row->id . '">' . $row->process_name . '</option>';
            endif;
        endforeach;
        return (['status' => 1, 'stageRows' => $stageRows,'processOptions'=>$processOptions]);
    }

	public function saveJobProcessSequence(){
        $data = $this->input->post();
        $errorMessage = array();
        if (empty($data['prc_id'])){ $errorMessage['prc_id'] = "PRC Number is required."; }    
        
        if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
            $result = $this->sop->saveJobProcessSequence($data);
            $this->printJson($result);
        endif;
    }

	public function addJobStage(){
        $data = $this->input->post();
        $errorMessage = array();
        if (empty($data['prc_id'])){ $errorMessage['prc_id'] = "PRC Number is required."; }    
        if (empty($data['process_id'])){ $errorMessage['stage_id'] = "Process is required."; }
        
        if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
            $result =  $this->sop->addJobStage($data);
            $jobStageData = $this->getJobStagesHtml($data);
            $result['stageRows'] = $jobStageData['stageRows'];
            $result['processOptions'] = $jobStageData['processOptions'];
            $this->printJson($result);
        endif;

    }
    
    public function getIssueDetail(){
		$data = $this->input->post();
		$this->data['itemData'] = $this->sop->getIssueDetailData(['prc_id'=>$data['prc_id'],'item_id'=>$data['item_id']]);
        $this->load->view('sopDesk/view_issue_detail',$this->data);
	}
	
	public function printPRCRejLog($log_id = "") {
		$id = (!empty($log_id)?$log_id:$this->input->post('id'));
		$processData = $this->sop->getProcessLogList(['id'=>$id,'single_row'=>1]);
		$logo = base_url('assets/images/logo.png');
		$qrIMG = base_url('assets/uploads/sop/'.$id.'.png');
        $qrText = encodeURL(['id'=>$id,'type'=>'rej_tag']);
        $file_name = $id;
        $qrIMG = base_url().$this->getQRCode($qrText,'assets/uploads/sop/',$file_name);
        $qrIMG =  '<td rowspan="4" class="text-right" style="padding:1px;" style="width:23%;"><img src="'.$qrIMG.'" style="height:30mm;"></td>';
	
        $itemList = '<table class="table tag_print_table">
                <tr>
                    <td style="width:23%;"><img src="' . $logo . '" style="height:39px;"></td>
                    <td class="org_title text-center" style="font-size:1rem;width:47%;">Rejection Tag <br><small>('.(!empty($processData->process_name)?$processData->process_name:'Initial Stage').')</small></td>
                    '.$qrIMG.'
                </tr>
                <tr class="text-left">
                    <td class="bg-light">Batch No</td>
                    <th>' . $processData->prc_number . '</th>
                </tr>
                <tr class="text-left">
                    <td class="bg-light"> Qty</td>
                    <th>' . floatval($processData->rej_found) . '</th>
                </tr>
                <tr class="text-left">
                    <td class="bg-light">Date</td>
                    <th>' . formatDate($processData->trans_date) . '</th>
                </tr>
                <tr class="text-left">
                    <td class="bg-light">Part</td>
                    <th colspan="2">' . (!empty($processData->item_code) ? '['.$processData->item_code.'] ' : '') . $processData->item_name . '</th>
                </tr>
                <tr class="text-left">
                    <td class="bg-light">Process By</td>
                    <th colspan="2">' . (!empty($processData->emp_name)?$processData->emp_name:'') . '</th>
                </tr>
            </table>';

		$pdfData = '<div style="width:97mm;height:50mm;text-align:center;float:left;padding:0mm 0.7mm;">'  . $itemList . '</div>';
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [100, 60]]);
        $pdfFileName = str_replace(" ", "_", str_replace("/", " ",'log_tag')) . '.pdf';
        $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->AddPage('P', '', '', '', '', 1, 1, 2, 2, 1, 1);
        $mpdf->WriteHTML($pdfData);
		
        if(!empty($log_id)){
			$mpdf->Output($pdfFileName, 'I');
        }else{
			$pdfOutputPath = 'assets/uploads/sop/' . $pdfFileName;
            $mpdf->Output($pdfOutputPath, 'F'); 
            $pdfUrl = base_url($pdfOutputPath);
            $this->printJson(['status'=>1,'url'=>$pdfUrl]);
        }
	}

	
	public function getMaterialAcceptTag($url=""){
		//$data = decodeURL($url);
		$postData = $this->input->post();
		$data = decodeURL($postData['url']);
		
		$mtitle = (!empty($data->title)?$data->title:"TAG PRINT");
		$data->id = (!empty($data->id)?$data->id:0);
		$mtrData = $this->sop->getBatchData(['prc_id'=>$data->prc_id,'item_id'=>$data->item_id,'id'=>$data->id,'single_row'=>1]); 
		$logo = base_url('assets/images/logo.png');
		$qrIMG = "";
		$qrText = encodeURL(['prc_id'=>$data->prc_id,'process_id'=>$mtrData->process_id,'item_id'=>$data->item_id,'id'=>$data->id,'qty'=>floatval($mtrData->issue_qty),'type'=>'material_tag']);
		$file_name = 'mtr_tag'.$data->prc_id;
		$qrIMG =base_url().$this->getQRCode($qrText,'assets/uploads/movement_tag/',$file_name);
		$qrIMG =  '<td  rowspan="5" class="text-right" style="padding:2px;"><img src="'.$qrIMG.'" style="height:25mm;"></td>';
		
		$topSectionO = '<table class="table">
							<tr>
								<td style="width:20%;"><img src="' . $logo . '" style="height:40px;"></td>
								<td class="org_title text-center" style="font-size:1rem;width:50%;">Material Tag<br><small>('.$mtrData->process_name.(!empty($data->id) ? ' - '.$mtrData->ref_no : '').')</small></td>
							</tr>
						</table>';

		$itemList = '<table class="table tag_print_table text-center">
		                <tr>
                            <td style="width:23%;"><img src="' . $logo . '" style="height:39px;"></td>
                            <td class="org_title text-center" style="font-size:1rem;width:50%;">Material Tag<br><small>('.$mtrData->process_name.(!empty($data->id) ? ' - '.$mtrData->ref_no : '').')</small></td>
                            '.$qrIMG.'
                        </tr>
	                	<tr>
	                	    <td class="bg-light" colspan="2">Batch No</td>
						</tr>
						<tr>
						    <th colspan="2">' . (!empty($mtrData->prc_number) ? $mtrData->prc_number : '') . '</th>
						</tr>
						<tr>
	                	    <td class="bg-light" colspan="2">Qty</td>
	                	</tr>
						<tr>
							<th colspan="2">' . (!empty($mtrData->issue_qty) ? floatVal($mtrData->issue_qty) : '') . '</th>
						</tr>
						<tr>
	                	    <td class="bg-light" colspan="2">Part</td>
	                	    <td class="bg-light">Date</td>
	                	</tr>
						<tr>    
							<th colspan="2">' . (!empty($mtrData->item_code) ? '['.$mtrData->item_code.'] ' : '') . $mtrData->item_name . '</th>
							<th>' . (!empty($mtrData->ref_date) ? formatDate($mtrData->ref_date) : '') . '</th>
						</tr>
						
					</table>';

		$pdfData = '<div style="width:97mm;height:50mm;text-align:center;float:left;padding:0mm 0.7mm;">' . $itemList . '</div>';
		
		
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [100, 60]]);
        $pdfFileName = str_replace(" ", "_", str_replace("/", " ", $mtitle)) . '.pdf';
        $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetProtection(array('print'));
        $mpdf->AddPage('P', '', '', '', '', 1, 1, 2, 2, 1, 1);
        $mpdf->WriteHTML($pdfData);
        //$mpdf->Output($pdfFileName, 'I');
        //$pdfContent = $mpdf->output();
        $pdfContent = base64_encode($mpdf->Output('', 'S'));
		
		$result['status'] = (!empty($pdfData) ? 1 : 0);
		$result['printHtml'] = $pdfContent;
		
		$this->printJson($result);
	}

	public function itemConversion(){
		$data = $this->input->post();
		$this->data['dataRow'] = $this->sop->getPRCProcessList(['id'=>$data['id'],'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1,'single_row'=>1]);
		$this->data['itemList'] = $this->item->getItemList(['item_type'=>1]);
		$this->load->view('sopDesk/item_conversion',$this->data);
	}

	public function saveConversion(){
		$data = $this->input->post(); 
        $errorMessage = array();
        if (empty($data['prc_id'])){ $errorMessage['prc_id'] = "Job Card No. is required.";}
        if (empty($data['process_id'])){ $errorMessage['process_id'] = "Process is required.";}
        if (empty($data['convert_item'])){ $errorMessage['convert_item'] = "Item is required.";}
		if (empty($data['trans_date']) or $data['trans_date'] == null or $data['trans_date'] == ""){ $errorMessage['trans_date'] = "Date is required."; }
        if (empty($data['qty'])){
            $errorMessage['qty'] = "Qty. is required.";
       	}else{
			$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
			$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
			$movement_qty =!empty($prcProcessData->movement_qty)?$prcProcessData->movement_qty:0;
			$pending_movement = $ok_qty - $movement_qty;

			if( $data['qty'] > $pending_movement ||  $data['qty'] < 0) :
				$errorMessage['qty'] = "Invalid Qty.";
			endif;

			if($data['convert_item'] == $prcProcessData->item_id){
				$errorMessage['convert_item'] = "Select the item you want to convert";
			}
		}
		if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
			$logData = [
				'id'=>'',
				'prc_id' => $data['prc_id'],
				'prc_process_id' => $data['prc_process_id'],
				'process_id' => $data['process_id'],
				'convert_item' => $data['convert_item'],
				'send_to' => 5,
				'trans_date' => $data['trans_date'],
				'qty' => !empty($data['qty'])?$data['qty']:0,
				'remark' => !empty( $data['remark'])? $data['remark']:'',
			];
			$this->printJson($this->sop->saveConversion($logData));
		endif;	
	}

	public function getPRCConversionHtml(){
		$data = $this->input->post(); 
		$movementData = $this->sop->getProcessMovementList(['prc_process_id'=>$data['prc_process_id'],'send_to'=>5,'convert_item'=>1]);
		$html="";
        if (!empty($movementData)) :
            $i = 1;
            foreach ($movementData as $row) :
				$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Record','fndelete' : 'deleteItemConversion','res_function':'getPrcMovementResponse'}";
				$deleteBtn = '<a class="btn btn-danger btn-sm btn-delete permission-remove" href="javascript:void(0)" onclick="trashSop('.$deleteParam.');" datatip="Remove" flow="down"><i class=" fas fa-trash-alt"></i></a>';
			   
				$printTag = '<a href="' . base_url('sopDesk/printPRCMovement/' . $row->id) . '" target="_blank" class="btn btn-sm btn-info waves-effect waves-light mr-2" title="Print"><i class="fas fa-print"></i></a>';
				
				$html .='<tr class="text-center">
					<td>' . $i++ . '</td>
					<td>' . formatDate($row->trans_date). '</td>
					<td>' . floatval($row->qty) . '</td>
					<td>' . $row->convert_item_name . '</td>
					<td>' . $row->remark . '</td>
					<td>'.$deleteBtn . '</td>
				</tr>';
            endforeach;
        else :
            $html = '<td colspan="6" class="text-center">No Data Found.</td>';
        endif;
		$prcProcessData = $this->sop->getPRCProcessList(['id'=>$data['prc_process_id'],'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
		$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
		$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
		$movement_qty =!empty($prcProcessData->movement_qty)?$prcProcessData->movement_qty:0;
		$pending_movement = $ok_qty - $movement_qty;
		$this->printJson(['status'=>1,'tbodyData'=>$html,'pendingQty'=>$pending_movement]);
	}

	public function deleteItemConversion(){
		$data = $this->input->post();
		if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->sop->deleteItemConversion($data));
        endif;
	}
}
?>