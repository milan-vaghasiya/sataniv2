<?php
class RejectionReview extends MY_Controller
{
    public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "Rejection Review";
		$this->data['headData']->controller = "rejectionReview";
		$this->data['headData']->pageUrl = "rejectionReview";
	}
	
	public function index(){
        $this->data['tableHeader'] = getProductionDtHeader('pendingReview');
        $this->load->view("rejection_review/index",$this->data);
    }

    public function getDTRows($status = 0){
        $data = $this->input->post();$data['status'] = $status;
        $result = $this->rejectionReview->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):          
            $row->sr_no = $i++;         
            $sendData[] = getPendingReviewData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function reviewedIndex(){
        $this->data['tableHeader'] = getProductionDtHeader('rejectionReview');
        $this->load->view("rejection_review/review_index",$this->data);
    }

    public function getReviewDTRows($status = 0){
        $data = $this->input->post();$data['status'] = $status;
        $result = $this->rejectionReview->getReviewDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):          
            $row->sr_no = $i++;         
            $sendData[] = getRejectionReviewData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function convertToOk(){
        $data = $this->input->post();
        $this->data['dataRow'] = $this->sop->getProcessLogList(['id'=>$data['id'],'single_row'=>1]);
        $this->load->view('rejection_review/cft_ok_form', $this->data);
    }

    public function convertToRej()
    {
        $data = $this->input->post();
        $this->data['dataRow'] = $dataRow = $this->sop->getProcessLogList(['id'=>$data['id'],'single_row'=>1]); 
        $this->data['rejectionComments'] = $this->comment->getCommentList(['type'=>1]);
        $resData = $this->comment->getComment($dataRow->rej_reason);
        if(!empty($resData->param_ids)){
           $this->data['rejParam'] = $this->rejectionParameter->getRejectionParameterList(['id'=>$resData->param_ids]);
        }
        $this->data['rejectionType'] = $this->rejectionType->getRejectionTypeList();
        $stageHtml = '<option value="">Select Stage</option><option value="0" data-process_name="Raw Material" data-process_id="Raw Material">Raw Material</option>';
        $this->data['prcProcessData'] = $prcProcessData = $this->sop->getPRCProcessList(['process_ids'=>$dataRow->process_ids,'item_id'=>$dataRow->item_id,'prc_id'=>$dataRow->prc_id]); 
        if (!empty($dataRow->process_id)) {
            $in_process_key = array_keys(array_column($prcProcessData,'current_process_id'), $dataRow->process_id)[0];
            foreach ($prcProcessData as $key => $row) {
                if ($key <= $in_process_key) {
                    $stageHtml .= '<option value="' . $row->current_process_id . '" data-process_name="' . $row->current_process . '" data-process_id="' . $row->current_process_id . '" >' . $row->current_process . '</option>';
                }
            }
        }
        $this->data['dataRow']->stage = $stageHtml;

        $this->load->view('rejection_review/cft_rej_form', $this->data);
    }

    public function convertToRw()
    {

        $data = $this->input->post();
        $this->data['dataRow'] = $dataRow = $this->sop->getProcessLogList(['id'=>$data['id'],'single_row'=>1]);
        $this->data['reworkComments'] = $this->comment->getCommentList(['type'=>3]);
        $this->data['rejectionType'] = $this->rejectionType->getRejectionTypeList();
        $stageHtml = '<option value="">Select Stage</option>';
        $this->data['prcProcessData'] = $prcProcessData = $this->sop->getPRCProcessList(['process_ids'=>$dataRow->process_ids,'item_id'=>$dataRow->item_id,'prc_id'=>$dataRow->prc_id]); 
        if (!empty($dataRow->process_id)) {
            $in_process_key = array_keys(array_column($prcProcessData,'current_process_id'), $dataRow->process_id)[0];

            foreach ($prcProcessData as $key => $row) {
                $reworkType = (($key <= $in_process_key)?'1':2);
                // if ($key <= $in_process_key) {
                    $stageHtml .= '<option value="' . $row->current_process_id . '" data-process_name="' . $row->current_process . '" data-process_id="' . $row->current_process_id . '" data-rework_type = "'.$reworkType.'">' . $row->current_process . '</option>';
                // }
                
            }
        }
        $this->data['dataRow']->stage = $stageHtml;
        $this->data['rwJobList'] = $this->sop->getPRCList(['prc_type'=>3,'ref_job_id'=>$dataRow->prc_id]);
        $this->load->view('rejection_review/cft_rw_form', $this->data);
    }


    public function saveReview(){
        $data = $this->input->post(); 
        $errorMessage = array();
        $i = 1;
        if (empty($data['qty'])) :
            $errorMessage['qty'] = "Qty is required.";
        else :
            $reviewData = $this->sop->getProcessLogList(['id'=>$data['log_id'],'rejection_review_data'=>1,'single_row'=>1]);
            if ($data['qty'] > ($reviewData->pending_qty)) {
                $errorMessage['qty'] = "Qty is Invalid.";
            }
        endif;
        if(in_array($data['decision_type'],[1,2])){
            if(empty($data['rr_type'])){$errorMessage['rr_type'] = "Type is required.";}
            if(empty($data['rr_reason'])){$errorMessage['rr_reason'] = "Reason is required.";}
            if($data['rr_stage'] ==''){$errorMessage['rr_stage'] = "Stage is required.";}
            if($data['rr_by'] == ''){$errorMessage['rr_by'] = "required.";}
            if($data['decision_type'] == 2){
                if(empty($data['rw_process'])){
                    $errorMessage['rw_process'] = "required.";
                }
                if(empty($data['rework_type'])){
                    $errorMessage['rework_type'] = "required.";
                }

                if($data['rework_type'] == 2 && empty($data['rw_job_id'])){
                    $errorMessage['rw_job_id'] = "required.";
                }
                
            }
        }
        if (!empty($errorMessage)) :
            $this->printJson(['status' => 0, 'message' => $errorMessage]);
        else :
            if($data['decision_type'] == 2){
                $data['rw_process'] = implode(",",$data['rw_process']);
            }
            $data['created_at'] = date("Y-m-d H:i:s");
            $data['created_by'] = $this->session->userdata('loginId');
            $this->printJson($this->rejectionReview->saveReview($data));
        endif;
    }

    public function getRRByOptions()
    {
        $data = $this->input->post(); 
        $option = '<option value="">Select</option>';
        if(!empty($data['rr_type']) && $data['rr_type'] == 'Raw Material'){
           
            $rmData = $this->store->getMaterialIssueData(['prc_id'=>$data['prc_id'],'group_by'=>'batch_history.party_id','supplier_data'=>1]);
            if (!empty($rmData)) :
                foreach($rmData as $row):
                    $option .= '<option value="'.(!empty($row->party_id)?$row->party_id:0).'">'.(!empty($row->party_name)?$row->party_name:'Inhouse').'</option>';
                endforeach;
            else:
                $option .= '<option value="0">Inhouse</option>';
            endif;
        } else {
            $vendorData = $this->sop->getProcessLogList(['prc_id'=>$data['prc_id'],'process_id'=>$data['process_id'],'group_by'=>'prc_log.process_by,prc_log.processor_id']);
            if (!empty($vendorData)) :
                foreach ($vendorData as $row) :
                    $option .= '<option value="' . (($row->process_by == 3) ? $row->processor_id : 0) . '" >' . ((($row->process_by == 3) ? $row->processor_name : 'Inhouse')) . '</option>';
                endforeach;
            else:
                $option .= '<option value="0">Inhouse</option>';
            endif;
        }

        $this->printJson(['status' => 1, 'rejOption' => $option]);
    }

    public function deleteReview(){
		$data = $this->input->post();
		if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->rejectionReview->deleteReview($data));
        endif;
	}
	
	public function getRejParams()
    {
        $data = $this->input->post(); 
       
        $paramList = $this->rejectionParameter->getRejectionParameterList(['id'=>$data['param_ids']]);
        $option = '<option value="0">Select Parameter</option>';
        if (!empty($paramList)) :
            foreach($paramList as $row):
                $option .= '<option value="'.$row->id.'">'.$row->parameter.'</option>';
            endforeach;
        endif;

        $this->printJson(['status' => 1, 'options' => $option]);
    }
    
    public function printPRCRejLog($id) {
		$logData = $this->rejectionReview->getReviewData(['id'=>$id,'single_row'=>1]);

        $vendorName = (!empty($logData->emp_name)) ? $logData->emp_name : (!empty($tagData->processor_name) ? $tagData->processor_name : '' );
        $machineName = ($logData->process_by == 1)? (!empty($logData->processor_name) ? $logData->processor_name:''):'';
		$title = "";
        $mtitle = "";
        $revno = "";
        $qtyLabel = "";
        $qty = 0;
        
		$mtitle = 'Rejection at M/c';
		$revno = 'R-QC-65 (00/01.10.22)';
		$qtyLabel = "Rej Qty";

        $logo = base_url('assets/images/logo.png');


        $topSection = '<table class="table">
            <tr>
                <td style="width:20%;"><img src="' . $logo . '" style="height:40px;"></td>
                <td class="org_title text-center" style="font-size:1rem;width:50%;">' . $mtitle . ' <br><small><span class="text-dark">' . $title . '</span></small></td>
                <td style="width:30%;" class="text-right"><span style="font-size:0.8rem;">' . $revno . '</td>
            </tr>
        </table>';
    
        $itemList = '<table class="table table-bordered vendor_challan_table">
			<tr>
				<td style="font-size:0.7rem;"><b>PRC No.</b></td>
				<td style="font-size:0.7rem;">' . $logData->prc_number . '</td>
				<td style="font-size:0.7rem;"><b>Date</b></td>
				<td style="font-size:0.7rem;">' . formatDate($logData->created_at) . '</td>
			</tr>
			<tr>
				<td style="font-size:0.7rem;"><b>Part</b></td>
				<td style="font-size:0.7rem;" colspan="3">' . (!empty($logData->item_code) ? '['.$logData->item_code.'] ' : '') . $logData->item_name . '</td>
			</tr>
			<tr>
				<td style="font-size:0.7rem;"><b>Ok Qty</b></td>
				<td style="font-size:0.7rem;">' . floatval($logData->ok_qty) . '</td>
				<td style="font-size:0.7rem;"><b>Rej Qty</b></td>
				<td style="font-size:0.7rem;">' . floatval($logData->qty) . '</td>
			</tr>
			<tr>
				<td style="font-size:0.7rem;"><b>RejReason</b></td>
				<td style="font-size:0.7rem;">' . $logData->reason . '</td>
				<td style="font-size:0.7rem;"><b>Rej Param.</b></td>
				<td style="font-size:0.7rem;">' . $logData->parameter . '</td>
			</tr>
			<tr>
				<td style="font-size:0.7rem;"><b>Vendor/Ope.</b></td>
				<td style="font-size:0.7rem;">' . $vendorName . '</td>
				<td style="font-size:0.7rem;"><b>M/c No</b></td>
				<td style="font-size:0.7rem;">' .$machineName . '</td>
			</tr>
			<tr>
				<td style="font-size:0.7rem;"><b>Issue By</b></td>
				<td style="font-size:0.7rem;" colspan="3">' . $logData->created_name . '</td>
			</tr>
		</table>';
        $pdfData = '<div style="width:97mm;height:50mm;text-align:center;float:left;padding:0mm 0.7mm;">' . $topSection . $itemList . '</div>';
    
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [100, 60]]);
        $pdfFileName = str_replace(" ", "_", str_replace("/", " ", $mtitle)) . '.pdf';
        $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->SetProtection(array('print'));
        $mpdf->AddPage('P', '', '', '', '', 0, 0, 2, 2, 2, 2);
        $mpdf->WriteHTML($pdfData);
        $mpdf->Output($pdfFileName, 'I');
	}
	
    public function acceptRejectionReview(){
		$data = $this->input->post(); 
		if(empty($data['id'])):
			$this->printJson(['status'=>0,'message'=>'Something went wrong...Please try again.']);
		else:
			$this->printJson($this->rejectionReview->acceptRejectionReview($data));
		endif;
	}
	
    public function printPRCMovement($id = "") {
        $logData = $this->rejectionReview->getReviewData(['id'=>$id,'single_row'=>1]);
        /* $movementData = $this->sop->getProcessMovementList(['prc_id'=>$logData->prc_id,'next_process_id'=> $logData->rw_process,'single_row'=>1]); */
        // print_r($this->db->last_query());exit;
        // if(!empty($movementData->id)){
            $id = (!empty($log_id)?$log_id:$this->input->post('id'));
            $tag_qty = $logData->qty;
    		$logo = base_url('assets/images/logo.png');
    		$title = 'Rework Tag';
    		$qrText = encodeURL(['id'=>'','tag_qty'=>$tag_qty,'prc_id'=>$logData->prc_id,'next_process_id'=> $logData->rw_process,'type'=>'move_rw_tag']);
    		$file_name = 'RW'.$logData->id.time();
    		$qrIMG = base_url().$this->getQRCode($qrText,'assets/uploads/movement_tag/',$file_name);
    		$qrIMG =  '<td  rowspan="4" colspan="2" class="text-center" style="padding:2px;"><img src="'.$qrIMG.'" style="height:30mm;"></td>';
    		
    		$itemList = '<table class="table tag_print_table">
    				<tr>
    					<td style="width:23%;"><img src="' . $logo . '" style="height:39px;"></td>
    					<td class="org_title text-center" style="font-size:1rem;width:47%;">' . $title . '<br><small>('.$logData->process_name.')</small></td>
    					'.$qrIMG.'
    				</tr>
    				<tr class="text-left">
    					<td class="bg-light">Batch No</td>
    					<th>' . $logData->prc_number . '</th>
    				</tr>
    				<tr class="text-left">
    					<td class="bg-light">Qty</td>
    					<th>' . floatval($tag_qty) . '</th>
    				</tr>
    				<tr class="text-left">
    					<td class="bg-light">Date</td>
    					<th>' . formatDate($logData->created_at) . '</th>
    				</tr>
    				<tr class="text-left">
    					<td class="bg-light">Part</td>
    					<th colspan="3">' . (!empty($logData->item_code) ? '['.$logData->item_code.'] ' : '') . $logData->item_name . '</th>
    				</tr>
    				<tr class="text-left">
    					<td class="bg-light">Next Process</td>
    					<th colspan="3">' . $logData->rw_process_name . '</th>
    				</tr>
    			</table>';
        // }else{
        //     $itemList = '<h4>No Data Found...!!</h4>';   
        // }

		$pdfData = '<div style="width:97mm;height:50mm;text-align:center;float:left;padding:0mm 0.7mm;">'.$itemList.'</div>';
        // print_r($pdfData); exit;
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [100, 60]]);
        $pdfFileName = str_replace(" ", "_", str_replace("/", " ", 'movement_tag'.time())) . '.pdf';
        $stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->AddPage('P', '', '', '', '', 1, 1, 2, 2, 1, 1);
        $mpdf->WriteHTML($pdfData);
        $mpdf->Output($pdfFileName, 'I');
	}
}
?>