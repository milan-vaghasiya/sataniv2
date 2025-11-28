<?php
class ProductionReport extends MY_Controller{
    public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "Production Report";
		$this->data['headData']->controller = "reports/productionReport";
    }

    public function stageWiseProduction(){
        $this->data['pageHeader'] = 'Stage Wise Production';
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>1]);
        $this->load->view("reports/production_report/stage_wise_production",$this->data);
    }

    public function getStageWiseProductionData(){
        $data = $this->input->post();
        $productProcessData = $this->item->getProductProcessList(['item_id'=>$data['item_id']]);
        $thead = '<tr>
                    <th rowspan="2">#</th>
                    <th rowspan="2">Batch Date</th>
                    <th rowspan="2">Batch No</th>
                    <th rowspan="2">Batch Qty</th>';
        $thead2 = '<tr>';
        $processArray = [];
        if(!empty($productProcessData)){
            foreach($productProcessData as $row){
                $thead .= '<th colspan="4">'.$row->process_name.'</th>';
                $thead2 .= '<th>In</th>
                            <th>Ok</th>
                            <th>Rej</th>
                            <th>Pending</th>';
                $processArray[] = $row->process_id;
            }
        } 
        $thead2 .= '</tr>';
        $thead .= '</tr>';
        $prcData = $this->sop->getPRCProcessList(['item_id'=>$data['item_id'],'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1,'group_by'=>'prc_process.prc_id,prc_process.current_process_id']);
        $tbody = '';$i=1;$tfoot = "";
        if(!empty($prcData)){
            $prcArray = [];
            foreach($prcData as $row){
                $prcArray[$row->prc_id]['prc_number'] = $row->prc_number;
                $prcArray[$row->prc_id]['prc_date'] = $row->prc_date;
                $prcArray[$row->prc_id]['prc_qty'] = $row->prc_qty;
                $prcArray[$row->prc_id][$row->current_process_id] = $row;
            }
            $totalIn = [];$totalOk=[];$totalRej=[];$totalPending = [];
            foreach($prcArray as $prc){
                $tbody .= '<tr>
                            <td>'.$i++.'</td>
                            <td>'.formatDate($prc['prc_date']).'</td>
                            <td>'.$prc['prc_number'].'</td>
                            <td>'.$prc['prc_qty'].'</td>';
                foreach($processArray as $key=>$process_id){
                    // print_r($prc[$process_id]);
                    $in_qty = (!empty($prc[$process_id]->in_qty)?$prc[$process_id]->in_qty:'0');
                    $ok_qty = !empty($prc[$process_id]->ok_qty)?$prc[$process_id]->ok_qty:0;
                    $rej_found_qty = !empty($prc[$process_id]->rej_found)?$prc[$process_id]->rej_found:0;
                    $rej_qty = !empty($prc[$process_id]->rej_qty)?$prc[$process_id]->rej_qty:0;
                    $rw_qty = !empty($prc[$process_id]->rw_qty)?$prc[$process_id]->rw_qty:0;
                    $pendingReview = $rej_found_qty - (!empty($prc[$process_id]->review_qty)?$prc[$process_id]->review_qty:0);
                    $pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview);
                    $tbody .= '<td>'.floatval($in_qty).'</td>
                               <td>'.floatval($ok_qty).'</td>
                               <td>'.floatval($rej_qty).'</td>
                               <td>'.floatval($pending_production).'</td>';
                   $totalIn[$process_id][] = $in_qty;$totalOk[$process_id][] =$ok_qty;$totalRej[$process_id][]=$rej_qty; $totalPending[$process_id][]= $pending_production;
                }
             $tbody .= '</tr>';
            }
            $tfoot = '<tr>
                        <th colspan="4" class="text-right">Total</th>';
            foreach($processArray as $key=>$process_id){
                $tfoot .= ' <th>'.array_sum($totalIn[$process_id]).'</th>
                            <th>'.array_sum($totalOk[$process_id]).'</th>
                            <th>'.array_sum($totalRej[$process_id]).'</th>
                            <th>'.array_sum($totalPending[$process_id]).'</th>';
            }
                       
            $tfoot .= '</tr>';
            
        }
        
        $this->printJson(['status'=>1,'tbody'=>$tbody,'thead'=>$thead.$thead2,'tfoot'=>$tfoot]);
    }
    
    /* PRC REGISTER */
	public function prcRegister(){
        $this->data['pageHeader'] = 'PRC REGISTER';
        $this->data['startDate'] = getFyDate(date("Y-m-01"));
        $this->data['endDate'] = getFyDate(date("Y-m-t"));
        $this->load->view("reports/production_report/prc_register",$this->data);
    }

    public function getPrcRegisterData(){
        $data = $this->input->post();

        $jobCardData = $this->productionReport->getPrcRegisterData($data);
        $tbody = ''; $tfoot = '';
        $i = 1;  $totalQty = 0; $totalOkQty = 0;$totalRejQty = 0;
        foreach ($jobCardData as $row) :
            $cname = !empty($row->party_name) ? $row->party_name : "Self Stock";
            $jobNo = '<a href="'.base_url("production/sopDesk/printDetailRouteCard/".$row->id).'" target="_blank">'.$row->prc_number.'</a>';
            $itemName = (!empty($row->item_code)? "[".$row->item_code."] " : "").$row->item_name;

            $tbody .= '<tr>
                <td>' . $i . '</td>
                <td>' . $row->prc_number . '</td>
                <td>' . formatDate($row->prc_date) . '</td>
                <td>' . $cname . '</td>
                <td>' . $itemName . '</td>
                <td>' . floatVal($row->prc_qty) . '</td>
                <td>' . floatVal($row->ok_qty) . '</td>
                <td>' . floatVal($row->rej_qty) . '</td>
                <td>' . $row->emp_name . '</td>
                <td>' . $row->job_instruction . '</td>
                
            </tr>';
            $i++;
            $totalQty += floatval($row->prc_qty);
            $totalOkQty += floatval($row->ok_qty);
            $totalRejQty += floatval($row->rej_qty);

        endforeach;
        $tfoot .= '<tr>
            <th colspan="5" class="text-right">Total</th>
            <th class="text-center">'.$totalQty.'</th>
            <th class="text-center">'.$totalOkQty.'</th>
            <th class="text-center">'.$totalRejQty.'</th>
            <th></th>
            <th></th>
        </tr>';
        $this->printJson(['status' => 1, 'tbody' => $tbody,'tfoot' => $tfoot]);
    }

    /* Jobwork Register */
    public function outSourceRegister(){
        $this->data['pageHeader'] = 'OutSource Register';
        $this->data['startDate'] = getFyDate(date("Y-m-01"));
        $this->data['endDate'] = getFyDate(date("Y-m-t"));
        $this->data['vendorList'] = $this->party->getPartyList(['party_category'=>3]);
        $this->load->view("reports/production_report/outsource_register",$this->data);
    }

    public function getOutSourceRegister(){
        $data = $this->input->post();
        $jobOutData = $this->productionReport->getOutSourceRegister($data);
        $blankInTd = '<td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td><td>-</td>';
        $i = 1;
        $tblData = "";$tfoot=""; $totalQty=0; $totalInQty=0;$totalBalQty=0;
        foreach ($jobOutData as $row) :
            $outData = $this->productionReport->getJobInwardData(['ref_id'=>$row->out_id]);
            $outCount = count($outData); 
            $tblData .= '<tr>
                            <td>' . $i++ . '</td>
                            <td>' . $row->ch_number. '</td>
                            <td>' . formatDate($row->ch_date) . '</td>
                            <td>' . $row->prc_number. '</td>
                            <td>' . $row->party_name. '</td>
                            <td>' . (!empty($row->item_code)?'['.$row->item_code.'] ':'').$row->item_name . '</td>
                            <td>' . $row->process_name . '</td>
                            <td>' . $row->qty . '</td>';
                            $totalQty+= $row->qty;
            if ($outCount > 0) :
                $usedQty = 0; $j=1;
                foreach ($outData as $outRow) : 
					$outQty = $row->qty;
					$usedQty += $outRow->qty;
                    
					$balQty = floatVal($outQty) - floatVal($usedQty);
                    
					$tblData .= '<td>' . formatDate($outRow->trans_date) . '</td>
								<td>' . $row->party_name. '</td>
								<td>' . $outRow->in_challan_no . '</td>
								<td>' . $outRow->qty . '</td>
								<td>' . $balQty . '</td>';           
                    if ($j != $outCount) {
                        $tblData .= '</tr><tr><td>' . $i++ . '</td>' . $blankInTd;
                    }
                    $j++;

                    // $totalQty+= $row->qty;
                    $totalInQty+=$outRow->qty;
                    $totalBalQty+=$balQty;
                endforeach;
            else :
                $tblData .= '<td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>';
            endif;
            $tblData .= '</tr>';
           
        endforeach;
        $tfoot = '<tr>
                <th colspan="7" class="text-right">Total</th>
                <th>'.$totalQty.'</th>
                <th></th>
                <th></th>
                <th></th>
                <th>'.$totalInQty.'</th>
                <th>'.$totalBalQty.'</th>
            </tr>';
        $this->printJson(['status' => 1, "tblData" => $tblData,"tfoot"=>$tfoot]);
    }

    /* Rejection Monitoring Report*/
	public function rejectionMonitoring(){
        $this->data['pageHeader'] = 'REJECTION MONITORING REPORT';
		$this->data['itemList'] = $this->item->getItemList(['item_type'=>1]);
        $this->load->view("reports/production_report/rejection_monitoring",$this->data);
	}

	public function getRejectionMonitoring(){
		$data = $this->input->post();
		$errorMessage = array();

		if($data['to_date'] < $data['from_date'])
			$errorMessage['to_date'] = "Invalid date.";

		$rejData = $this->productionReport->getRejectionMonitoring($data);

		$tbodyData=""; $tfootData="";$i=1; $totalRejQty=0;

		foreach($rejData as $row):
			$tbodyData .= '<tr>
				<td class="text-center">'.$i++.'</td>
				<td>'.formatDate($row->trans_date).'</td>
				<td>'.(!empty($row->item_code) ? '['.$row->item_code.'] ' : '') . $row->item_name.'</td>
				<td>'.$row->process_name.'</td>
				<td>'.$row->shift_name .'</td>
				<td>'.$row->processor_name.'</td>
				<td>'.$row->emp_name.'</td>
				<td>'.$row->prc_number.'</td>
				<td>'.$row->qty.'</td>
				<td>'.$row->remark.'</td>
				<td>'.$row->rr_comment.'</td>
				<td>'.(!empty($row->rr_stage) ? $row->rejction_stage : 'Raw Material') .'</td>
				<td>'.(!empty($row->rr_by) ? $row->vendor_name : 'In House') .'</td>
				<td>'.$row->rr_type.'</td>';
			$tbodyData .='</tr>';
			$totalRejQty += $row->qty;
		endforeach;
		$tfootData .= '<tr class="thead-dark">
						<th colspan="8" style="text-align:right !important;">Total</th>
						<th>'.round($totalRejQty,2).'</th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>';

		if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
			$this->printJson(['status'=>1,"tbodyData"=>$tbodyData,"tfootData"=>$tfootData]);
		endif;
	}

    /* Production Analysis Report*/
    public function productionAnalysis(){
		$this->data['headData']->pageTitle = "PRODUCTION ANALYSIS";
        $this->data['pageHeader'] = 'PRODUCTION ANALYSIS';
        $this->data['startDate'] = getFyDate(date("Y-m-01"));
        $this->data['endDate'] = getFyDate(date("Y-m-t"));
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>1]);
        $this->load->view("reports/production_report/production_analysis",$this->data);
    }

    public function getProductionAnalysisData(){
        $data = $this->input->post();
        $customWhere = "prc_log.trans_date BETWEEN '".$data['from_date']."' AND '".$data['to_date']."'";
        $result = $this->sop->getProcessLogList(['item_id'=>$data['item_id'],'rejection_review_data'=>1,'customWhere'=>$customWhere,'grouped_data'=>1,'group_by'=>'prc_log.trans_date,prc_master.item_id,prc_log.processor_id,prc_log.process_id','breakdown'=>1]);
        $tbody = '';$tfoot = ''; 
        $i = 1;  $totalIdeal = 0;$totalActual = 0;$totalLost = 0;
        foreach($result as $row):

            $totalProd = floor($row->ok_qty + $row->rej_qty + $row->rw_qty + $row->pending_qty);
    
            $workingHour = round($row->production_time / 3600 ,2);

            $breakdownHour = 0;//round($row->breakdown_time / 3600 ,2);

            $ideal_ph=0;
            if(!empty($row->cycle_time) && $row->cycle_time > 0) { $ideal_ph  = floor(3600 / $row->cycle_time); }
            $ideal_total  = floor($ideal_ph * $workingHour);

            $actual_ph=0;
            if(!empty($totalProd && !empty($workingHour))) { $actual_ph  = floor($totalProd / $workingHour); }

            $lost_ph = ($ideal_ph - $actual_ph);
            $lost_total = ($ideal_total - $totalProd);

            $tbody .= '<tr>
                <td>'.$i.'</td>
                <td>'.formatDate($row->trans_date).'</td>
                <td class="text-left">'.$row->item_name.'</td>
                <td>'.$row->process_name.'</td>
                <td>'.$row->machine_name.'</td>
                <td>'.$row->cycle_time.'</td>
                <td>'.$workingHour.'</td>
                <td>'.$breakdownHour.'</td>
                <td>'.(($ideal_ph > 0) ? $ideal_ph : 0).'</td>
                <td>'.(($ideal_total > 0) ? $ideal_total : 0).'</td>
                <td>'.(($actual_ph > 0) ? $actual_ph : 0).'</td>
                <td>'.(($totalProd > 0) ? $totalProd : 0).'</td>
                <td>'.(($lost_ph > 0) ? $lost_ph : 0).'</td>
                <td>'.(($lost_total > 0) ? $lost_total : 0).'</td>
                <td>
					<a href="'.base_url("reports/productionReport/productionDetail/".$row->trans_date.'/'.$row->item_id.'/'.$row->process_id.'/'.$row->processor_id).'" target="_blank" datatip="Production Detail" flow="left">'.(($lost_total > 0) ? $lost_total + ($breakdownHour * $ideal_ph) : ($breakdownHour * $ideal_ph)).'</a>
				</td>
            </tr>';
            $i++;
            $totalIdeal += (($ideal_total > 0) ? $ideal_total : 0);
            $totalActual += (($totalProd > 0) ? $totalProd : 0);
            $totalLost += (($lost_total > 0) ? $lost_total : 0);
        endforeach;

            $tfoot .= '<tr>
                <th colspan="8" class="text-right">Total</th>
                <th></th>
                <th class="text-center">'.$totalIdeal.'</th>
                <th></th>
                <th class="text-center">'.$totalActual.'</th>
                <th></th>
                <th class="text-center">'.$totalLost.'</th>
                <th></th>

            </tr>';
        $this->printJson(['status' => 1, 'tbody' => $tbody,'tfoot' => $tfoot]);
    }
    
    public function productionDetail($trans_date = "",$item_id="",$process_id="",$processor_id=""){
		$this->data['headData']->pageTitle = "Production Detail";
        
        $result = $this->sop->getProcessLogList(['item_id'=>$item_id,'process_id'=>$process_id,'processor_id'=>$processor_id,'trans_date'=>$trans_date,'rejection_review_data'=>1]);
        $tbodyData = '';$i=1;
        foreach($result as $row):
            $idealQty = (!empty($row->cycle_time) ? round($row->production_time / $row->cycle_time ,2) : 0);
            $actualQty = ($row->qty + $row->rej_qty + $row->rw_qty + $row->pending_qty);
            $tbodyData .= '<tr>
                <td class="text-center">'.$i++.'</td>
                <td>'.date("d-m-Y H:i:s",strtotime($row->start_time)).'</td>
                <td>'.date("d-m-Y H:i:s",strtotime($row->end_time)).'</td>
                <td>'.$row->prc_number.'</td>
                <td>'.$row->process_name.'</td>
                <td>'.$row->machine_name.'</td>
                <td>'.$row->emp_name.'</td>
                <td>'.$row->shift_name.'</td>
                <td>'.$row->production_time.'</td>
                <td>'.$idealQty.'</td>
                <td>'.$actualQty.'</td>
                <td>'.$row->qty.'</td>
                <td>'.$row->rej_qty.'</td>
                <td></td>
            </tr>';
        endforeach;
        $this->data['tbodyData'] = $tbodyData;
        $this->load->view('reports/production_report/production_detail',$this->data);
    }

    /* Production Log Sheet Report*/
    public function productionLogSheet(){
        $this->data['pageHeader'] = 'PRODUCTION LOG SHEET';
        $this->data['startDate'] = date('Y-m-01');
        $this->data['endDate'] = getFyDate(date("Y-m-d"));
		$this->data['processList'] = $this->process->getProcessList();

        $this->load->view("reports/production_report/production_log_sheet", $this->data);
    }

    public function getProductionLogSheet(){
        $data = $this->input->post();
        $productionData = $this->productionReport->getProductionLogSheet($data);

        $tbody = '';$i=1; $tfoot=''; $totalOkQty = 0;$totalRejQty = 0;
        foreach($productionData as $row):
            $machine_name = $row->process_by == 3 ? $row->party_name : $row->machine_name;

            $tbody .= '<tr class="text-center">
                <td class="text-left">'.$i.'</td>
                <td class="text-left">'.formatDate($row->trans_date).'</td>
                <td class="text-left">'.$row->emp_name.'</td>
                <td class="text-left">'.$machine_name.'</td>
                <td>'.$row->shift_name.'</td>
                <td>'.$row->item_name.'</td>
                <td>'.$row->prc_number.' </td>
                <td>'.$row->process_name.'</td>
                <td>'.$row->cycle_time.'</td>
                <td>'.$row->production_time.' </td>
                <td>'.$row->ok_qty.'</td>
                <td>'.$row->rej_qty.'</td>
                <td> '.$row->rej_reason.' </td>
            </tr>';
            $i++;

            $totalOkQty += floatval($row->ok_qty);
            $totalRejQty += floatval($row->rej_qty);
 
        endforeach;
        $tfoot .= '<tr>
             <th colspan="10" class="text-right">Total</th>
             <th class="text-center">'.$totalOkQty.'</th>
             <th class="text-center">'.$totalRejQty.'</th>
             <th></th>
 
         </tr>';
        $this->printJson(['status'=>1,'tbody'=>$tbody,'tfoot'=>$tfoot]);
    }
    
    /* Production Analysis Report*/
    public function productionReview(){
		$this->data['headData']->pageTitle = "PRODUCTION REVIEW";
        $this->data['pageHeader'] = 'PRODUCTION REVIEW';
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>1]);
        $this->load->view("reports/production_report/production_review",$this->data);
    }
    
    public function getProductionReviewData(){
        $data = $this->input->post();
        // $productProcessData = $this->item->getProductProcessList(['item_id'=>$data['item_id']]);
        
        //Batch Wise
        if($data['report_type'] == 1){
             $thead = '<tr>
                            <th rowspan="2">#</th>
                            <th rowspan="2">Item</th>
                            <th rowspan="2">Batch No</th>
                            <th rowspan="2">Batch Qty</th>
                            <th colspan="4" class="text-center">Forging Process</th>
                            <th colspan="4" class="text-center">Machining Process</th>
                            <th colspan="4" class="text-center">Other Process</th>
                        </tr>
                        <tr>
                            <th>In Transit</th>
                            <th>Pending</th>
                            <th>Stock</th>
                            <th>Rejection</th>
                            
                            <th>In Transit</th>
                            <th>Pending</th>
                            <th>Stock</th>
                            <th>Rejection</th>
                            
                            <th>In Transit</th>
                            <th>Pending</th>
                            <th>Stock</th>
                            <th>Rejection</th>
                        </tr>';
        }else{
            //Item Wise
             $thead = '<tr>
                            <th rowspan="2">#</th>
                            <th rowspan="2">Item</th>
                            <th rowspan="2">Total Batch Qty</th>
                            <th colspan="4" class="text-center">Forging Process</th>
                            <th colspan="4" class="text-center">Machining Process</th>
                            <th colspan="4" class="text-center">Other Process</th>
                        </tr>
                        <tr>
                            <th>In Transit</th>
                            <th>Pending</th>
                            <th>Stock</th>
                            <th>Rejection</th>
                            
                            <th>In Transit</th>
                            <th>Pending</th>
                            <th>Stock</th>
                            <th>Rejection</th>
                            
                            <th>In Transit</th>
                            <th>Pending</th>
                            <th>Stock</th>
                            <th>Rejection</th>
                        </tr>';
        }
        $prcList = $this->sop->getPRCList(['status'=>'2','item_id'=>$data['item_id']]);
        $prcData = $this->productionReport->getPRCProcessList(['prc_id'=>implode(",",array_column($prcList,'id')),'item_id'=>$data['item_id'],'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1,'group_by'=>'prc_process.prc_id,prc_process.current_process_id']);
        $tbody = '';$i=1;$tfoot = "";
        if(!empty($prcData)){
            $prcArray = [];
            foreach($prcData as $row){
                $in_qty = (!empty($row->in_qty)?$row->in_qty:'0');
                $ok_qty = !empty($row->ok_qty)?$row->ok_qty:0;
                $rej_found_qty = !empty($row->rej_found)?$row->rej_found:0;
                $rej_qty = !empty($row->rej_qty)?$row->rej_qty:0;
                $rw_qty = !empty($row->rw_qty)?$row->rw_qty:0;
                $pendingReview = $rej_found_qty - (!empty($row->review_qty)?$row->review_qty:0);
                $pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview);
                $movement_qty =!empty($row->movement_qty)?$row->movement_qty:0;
                $pending_movement = $ok_qty - ($movement_qty);
                $pending_accept =(!empty($row->pending_accept) && $row->pending_accept > 0)?$row->pending_accept:0;
                if($data['report_type'] == 1)
                {
                    if(!isset($prcArray[$row->prc_id][$row->process_type]->pending_production)){ $prcArray[$row->prc_id][$row->process_type] = new stdClass();$prcArray[$row->prc_id][$row->process_type]->pending_production = 0;}
                    if(!isset($prcArray[$row->prc_id][$row->process_type]->stock_qty)){$prcArray[$row->prc_id][$row->process_type]->stock_qty = 0;}
                    if(!isset($prcArray[$row->prc_id][$row->process_type]->rej_qty)){$prcArray[$row->prc_id][$row->process_type]->rej_qty = 0;}
                    if(!isset($prcArray[$row->prc_id][$row->process_type]->in_transit_qty)){$prcArray[$row->prc_id][$row->process_type]->in_transit_qty = 0;}
                    $prcArray[$row->prc_id]['prc_number'] = $row->prc_number;
                    $prcArray[$row->prc_id]['prc_date'] = $row->prc_date;
                    $prcArray[$row->prc_id]['prc_qty'] = $row->prc_qty;
                    $prcArray[$row->prc_id]['item_name'] = ((!empty($row->item_code))?'['.$row->item_code.'] ':'').$row->item_name;
                    //$prcArray[$row->prc_id][$row->process_type] = $row;
                    
                    $prcArray[$row->prc_id][$row->process_type]->pending_production += $pending_production;
                    $prcArray[$row->prc_id][$row->process_type]->stock_qty += $pending_movement;
                    $prcArray[$row->prc_id][$row->process_type]->rej_qty += $rej_qty;
                    $prcArray[$row->prc_id][$row->process_type]->in_transit_qty += $pending_accept;
                }else{
                    if(!isset( $prcArray[$row->item_id]['prc_qty'])){ $prcArray[$row->item_id]['prc_qty'] = 0; }
                    if(!isset($prcArray[$row->item_id][$row->process_type]->pending_production)){ $prcArray[$row->item_id][$row->process_type] = new stdClass();$prcArray[$row->item_id][$row->process_type]->pending_production = 0;}
                    if(!isset($prcArray[$row->item_id][$row->process_type]->stock_qty)){$prcArray[$row->item_id][$row->process_type]->stock_qty = 0;}
                    if(!isset($prcArray[$row->item_id][$row->process_type]->rej_qty)){$prcArray[$row->item_id][$row->process_type]->rej_qty = 0;}
                    if(!isset($prcArray[$row->item_id][$row->process_type]->in_transit_qty)){$prcArray[$row->item_id][$row->process_type]->in_transit_qty = 0;}
                    $prcArray[$row->item_id]['prc_date'] = $row->prc_date;
                    $prcArray[$row->item_id]['prc_qty'] += $row->prc_qty;
                    $prcArray[$row->item_id]['item_name'] = ((!empty($row->item_code))?'['.$row->item_code.'] ':'').$row->item_name;
                    //$prcArray[$row->prc_id][$row->process_type] = $row;
                    
                    $prcArray[$row->item_id][$row->process_type]->pending_production += $pending_production;
                    $prcArray[$row->item_id][$row->process_type]->stock_qty += $pending_movement;
                    $prcArray[$row->item_id][$row->process_type]->rej_qty += $rej_qty;
                    $prcArray[$row->item_id][$row->process_type]->in_transit_qty += $pending_accept;
                }
                
            }
           
            foreach($prcArray as $prc){
               
                $tbody .= '<tr>
                            <td>'.$i++.'</td>
                            <td>'.$prc['item_name'].'</td>';
                if($data['report_type'] == 1){
                 $tbody .= '<td>'.$prc['prc_number'].'</td>';
                }        
                $tbody .= '<td>'.$prc['prc_qty'].'</td>';
                            
                //Forging Process
                $tbody .= '<td>'.(!empty($prc[2]->in_transit_qty)?$prc[2]->in_transit_qty:0).'</td>
                           <td>'.(!empty($prc[2]->pending_production)?$prc[2]->pending_production:0).'</td>
                           <td>'.(!empty($prc[2]->stock_qty)?$prc[2]->stock_qty:0).'</td>
                           <td>'.(!empty($prc[2]->rej_qty)?$prc[2]->rej_qty:0).'</td>';
                           
                //Maching Process
                $tbody .= '<td>'.(!empty($prc[1]->in_transit_qty)?$prc[1]->in_transit_qty:0).'</td>
                           <td>'.(!empty($prc[1]->pending_production)?$prc[1]->pending_production:0).'</td>
                           <td>'.(!empty($prc[1]->stock_qty)?$prc[1]->stock_qty:0).'</td>
                           <td>'.(!empty($prc[1]->rej_qty)?$prc[1]->rej_qty:0).'</td>';
                           
                //Other Process
                $tbody .= '<td>'.(!empty($prc[3]->in_transit_qty)?$prc[3]->in_transit_qty:0).'</td>
                           <td>'.(!empty($prc[3]->pending_production)?$prc[3]->pending_production:0).'</td>
                           <td>'.(!empty($prc[3]->stock_qty)?$prc[3]->stock_qty:0).'</td>
                           <td>'.(!empty($prc[3]->rej_qty)?$prc[3]->rej_qty:0).'</td>';
                $tbody .= '</tr>';
                           
                $inTransitQty[1][] =(!empty($prc[1]->in_transit_qty)?$prc[1]->in_transit_qty:0);
                $inTransitQty[2][] =(!empty($prc[2]->in_transit_qty)?$prc[2]->in_transit_qty:0);
                $inTransitQty[3][] =(!empty($prc[3]->in_transit_qty)?$prc[3]->in_transit_qty:0);
                
                $pendingProduction[1][] = (!empty($prc[1]->pending_production) ? $prc[1]->pending_production : 0);
                $pendingProduction[2][] = (!empty($prc[2]->pending_production) ? $prc[2]->pending_production : 0);
                $pendingProduction[3][] = (!empty($prc[3]->pending_production) ? $prc[3]->pending_production : 0);
                
                $stockQty[1][] =(!empty($prc[1]->stock_qty)?$prc[1]->stock_qty:0);
                $stockQty[2][] =(!empty($prc[2]->stock_qty)?$prc[2]->stock_qty:0);
                $stockQty[3][] =(!empty($prc[3]->stock_qty)?$prc[3]->stock_qty:0);
                
                $rejQty[1][] =(!empty($prc[1]->rej_qty)?$prc[1]->rej_qty:0);
                $rejQty[2][] =(!empty($prc[2]->rej_qty)?$prc[2]->rej_qty:0);
                $rejQty[3][] =(!empty($prc[3]->rej_qty)?$prc[3]->rej_qty:0);
            }
            
            $tfoot = '<tr>
                        <th colspan="'.(($data['report_type'] == 1)?4:3).'" class="text-right">Total</th>';
            $tfoot .= ' <th>'.array_sum($inTransitQty[2]).'</th>
                        <th>'.array_sum($pendingProduction[2]).'</th>
                        <th>'.array_sum($stockQty[2]).'</th>
                        <th>'.array_sum($rejQty[2]).'</th>';
                        
            $tfoot .= ' <th>'.array_sum($inTransitQty[1]).'</th>
                        <th>'.array_sum($pendingProduction[1]).'</th>
                        <th>'.array_sum($stockQty[1]).'</th>
                        <th>'.array_sum($rejQty[1]).'</th>';
            
            $tfoot .= ' <th>'.array_sum($inTransitQty[3]).'</th>
                        <th>'.array_sum($pendingProduction[3]).'</th>
                        <th>'.array_sum($stockQty[3]).'</th>
                        <th>'.array_sum($rejQty[3]).'</th>';
            $tfoot .= '</tr>';
            
        }
        
        $this->printJson(['status'=>1,'tbody'=>$tbody,'thead'=>$thead,'tfoot'=>$tfoot]);
    }
}
?>