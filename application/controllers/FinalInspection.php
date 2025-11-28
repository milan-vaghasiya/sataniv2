<?php 
class FinalInspection extends MY_Controller
{
    private $indexPage = "final_inspection/index";
    private $iprIndexPage = "final_inspection/fir_index";
    private $formPage = "final_inspection/form";
    
	public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "Final Inspection";
		$this->data['headData']->controller = "finalInspection";
        $this->data['headData']->pageUrl = "finalInspection";
	}
	 
	public function index(){
        $this->data['tableHeader'] = getQualityDtHeader('pendingFir');
        $this->load->view($this->indexPage,$this->data);
    }

    public function getDTRows(){
        $data = $this->input->post();
        $result = $this->finalInspection->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):
            $row->sr_no = $i++;
            $sendData[] = getPendingFirData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function addFinalInspection(){
        $data = $this->input->post();
        $this->data['dataRow'] = $this->sop->getPRC(['id'=>$data['main_ref_id']]);
		$this->data['paramData'] = $this->item->getPreInspectionParam(['item_id'=>$this->data['dataRow']->item_id ,'control_method'=>'FIR']);
        $this->data['batchData'] = $this->itemStock->getItemStockBatchWise(['location_id'=>$this->FIR_STORE->id,'item_id'=>$this->data['dataRow']->item_id,'batch_no'=>$this->data['dataRow']->prc_number,'single_row'=>1]);
        $this->data['trans_no'] = $this->finalInspection->getFirNextNo();
        $this->data['trans_number'] = "FIR".sprintf(n2y(date('Y'))."%03d",$this->data['trans_no']);
		$this->load->view($this->formPage,$this->data);
	}

    public function getFinalInspectionData(){
        $data = $this->input->post();
        $paramData = $this->item->getPreInspectionParam(['item_id'=>$data['item_id'] ,'control_method'=>'FIR']);
        $tbodyData="";$i=1; $theadData='';
                $theadData .= '<tr class="thead-info" style="text-align:center;">
                            <th rowspan="2" style="width:5%;">#</th>
                            <th rowspan="2">Parameter</th>
                            <th rowspan="2">Specification</th>
                            <th rowspan="2" style="width:10%">Instrument</th>
                            <th rowspan="2" style="width:15%">Min. Value</th>
                            <th rowspan="2" style="width:15%">Max. Value</th>
                            <th colspan="'.$data['sampling_qty'].'" style="text-align:center;">Observation on Samples</th>
                        </tr>
                        <tr style="text-align:center;">';
                        for($j=1; $j<=$data['sampling_qty']; $j++):
                            $theadData .= '<th>'.$j.'</th>';
                        endfor;    
                $theadData .='</tr>';
        if(!empty($paramData)):
            foreach($paramData as $row):
                $tbodyData.= '<tr>
                            <td style="text-align:center;">'.$i++.'</td>
                            <td style="width:10px;">'.$row->parameter.'</td>
                            <td style="width:10px;">'.$row->specification.'</td>   
                            <td style="width:20px;">'.$row->instrument.'</td>
                            <td style="width:20px;">'.$row->min.'</td>
                            <td style="width:20px;">'.$row->max.'</td>';
                            for($j=1; $j<=$data['sampling_qty']; $j++):
                $tbodyData.=' <td style="min-width:100px;"><input type="text" name="sample'.($j).'_'.$row->id.'" class="form-control" value=""></td>';
                            endfor;  
                $tbodyData.='</tr>';
            endforeach;
        else:
            $tbodyData.= '<tr><td colspan="11" style="text-align:center;">No Data Found</td></tr>';
        endif;
        $this->printJson(['status'=>1,"tbodyData"=>$tbodyData,"theadData"=>$theadData]);
    }

    public function saveFinalInspection(){ 
		$data = $this->input->post();
        $errorMessage = Array(); 

        if(empty($data['inspected_qty']) AND $data['inspected_qty'] == 0){
            $errorMessage['inspected_qty'] = "Inspected Qty is required.";
        }else{
            if($data['inspected_qty'] > ($data['ok_qty'] + $data['rej_found'])){
                $errorMessage['inspected_qty'] = "Qty is invalid.";
            }
        }
        
        
		if(empty($data['ok_qty']) && empty($data['rej_found'])){
            $errorMessage['ok_qty'] = "Qty is required.";
        }else{
            $batchData = $this->itemStock->getItemStockBatchWise(['location_id'=>$this->FIR_STORE->id,'item_id'=> $data['item_id'],'batch_no'=>$data['prc_number'],'single_row'=>1]);
            $totalQty = ($data['ok_qty'] + $data['rej_found']);
			if(($totalQty) > abs($batchData->qty)){ $errorMessage['ok_qty'] = "Qty is invalid."; }
        }
            
        $insParamData = $this->item->getPreInspectionParam(['item_id'=>$data['item_id'],'control_method'=>'FIR']);
        if(count($insParamData) <= 0)
            $errorMessage['general'] = "Item Parameter is required.";

        $pre_inspection = Array(); $param_ids = Array();
        if(!empty($insParamData)):
            foreach($insParamData as $row):
                $param = Array();
                for($j = 1; $j <= $data['sampling_qty']; $j++):
                    $param[] = $data['sample'.$j.'_'.$row->id];
                    unset($data['sample'.$j.'_'.$row->id]);
                endfor;
                $pre_inspection[$row->id] = $param;
				$param_ids[] = $row->id;
            endforeach;
        endif;

        $data['observation_sample'] = json_encode($pre_inspection);
		$data['parameter_ids'] = implode(',',$param_ids);
        $data['param_count'] = count($insParamData);

		if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $data['created_by'] = $this->session->userdata('loginId');
            $this->printJson($this->finalInspection->saveFinalInspection($data));
        endif;
	}

    public function firIndex(){
        $this->data['tableHeader'] = getQualityDtHeader('finalInspection');
        $this->load->view($this->iprIndexPage,$this->data);
    }

    public function getFirDTRows(){
        $data = $this->input->post();
        $result = $this->finalInspection->getFirDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):          
            $row->sr_no = $i++;         
            $sendData[] = getFinalInspectionData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->finalInspection->delete($id));
        endif;
    }

    public function printFinalInspection($id){
        $this->data['firData'] = $firData = $this->finalInspection->getFinalInspectData(['id'=>$id]);
        $this->data['paramData'] = $this->item->getPreInspectionParam(['item_id'=>$firData->item_id,'control_method'=>'FIR']);

		$logo=base_url('assets/images/logo.png'); 
		$this->data['letter_head']=base_url('assets/images/letterhead_top.png');
		
		$pdfData = $this->load->view('final_inspection/fir_pdf',$this->data,true);
		
		$htmlHeader = '<table class="table">
							<tr>
								<td style="width:25%;"><img src="'.$logo.'" style="height:50px;"></td>
								<td class="org_title text-center" style="font-size:1rem;width:50%">FINAL INSPECTION REPORT</td>
								<td style="width:25%;" class="text-right"><span style="font-size:0.8rem;"></td>
							</tr>
						</table><hr>';
		$htmlFooter = '<table class="table" style="border-bottom:1px solid #000000;">
						<tr>
							<td style="width:50%;"></td>
							<td style="width:25%;" class="text-center">'.$firData->emp_name.'</td>
						</tr>
						<tr>
							<td style="width:50%;"></td>
							<td style="width:25%;" class="text-center"><b>Prepared By</b></td>
						</tr>
					</table>
					<table class="table top-table" style="margin-top:10px;">
						<tr>
							<td style="width:25%;"></td>
							<td style="width:25%;text-align:right;">Page No. {PAGENO}/{nbpg}</td>
						</tr>
					</table>';
		$mpdf = new \Mpdf\Mpdf();
		$pdfFileName='DC-REG-'.$id.'.pdf';
		$stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->SetWatermarkImage($logo,0.05,array(120,60));
		$mpdf->SetProtection(array('print'));
		
		$mpdf->SetHTMLHeader($htmlHeader);
		$mpdf->SetHTMLFooter($htmlFooter);
		$mpdf->AddPage('P','','','','',5,5,30,5,5,5,'','','','','','','','','','A4-P');
		$mpdf->WriteHTML($pdfData);
		$mpdf->Output($pdfFileName,'I');		
	}

}
?>