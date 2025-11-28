<?php
class RejectionReviewModel extends MasterModel
{
    private $rejection_log = "rejection_log";
   

    public function getDTRows($data)
    {
        $data['tableName'] = "prc_log";
		$data['select'] = "prc_log.*,prc_master.prc_date,prc_master.prc_number,process_master.process_name,item_master.item_name,item_master.item_code,employee_master.emp_name,empMaster.emp_name as empName";
		$data['select'] .=', IF(prc_log.process_by = 1, machine.item_code,
									IF(prc_log.process_by = 2,department_master.name,
										IF(prc_log.process_by = 3,party_master.party_name,""))) as processor_name,IFNULL(rejection_log.review_qty,0) as review_qty,(prc_log.rej_found-IFNULL(rejection_log.review_qty,0)) as pending_qty';
		$data['leftJoin']['prc_master'] = "prc_master.id = prc_log.prc_id";
		$data['leftJoin']['process_master'] = "process_master.id = prc_log.process_id";
		$data['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
		$data['leftJoin']['item_master machine'] = "machine.id = prc_log.processor_id";
		$data['leftJoin']['department_master'] = "department_master.id = prc_log.processor_id";
		$data['leftJoin']['party_master'] = "party_master.id = prc_log.processor_id";
		$data['leftJoin']['employee_master'] = "employee_master.id = prc_log.operator_id";
		$data['leftJoin']['employee_master empMaster'] = "empMaster.id = prc_log.accepted_by";
        $data['leftJoin']['(SELECT SUM(qty) as review_qty,log_id,prc_id FROM rejection_log WHERE is_delete = 0 AND decision_type != 2 GROUP BY rejection_log.log_id,prc_id) rejection_log'] = "rejection_log.log_id = prc_log.id AND prc_log.prc_id = rejection_log.prc_id";
        
        $data['where']['prc_log.rej_found >'] = 0;
        $data['having'][] = "pending_qty > 0";
       
        $data['order_by']['prc_log.trans_date'] = 'DESC';
       
        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "prc_master.prc_number";
        $data['searchCol'][] = "CONCAT('[',item_master.item_code,'] ',item_master.item_name)";
        $data['searchCol'][] = "DATE_FORMAT(prc_log.trans_date,'%d-%m-%Y')";
        $data['searchCol'][] = "process_master.process_name";
        $data['searchCol'][] = "IF(prc_log.process_by = 1, machine.item_code, IF(prc_log.process_by = 2, department_master.name, party_master.party_name))";
        $data['searchCol'][] = "employee_master.emp_name";
        $data['searchCol'][] = "prc_log.rej_found";
        $data['searchCol'][] = "rejection_log.review_qty";
        $data['searchCol'][] = "(prc_log.rej_found - rejection_log.review_qty)";
        $data['searchCol'][] = "empMaster.emp_name";

        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
		$result = $this->pagingRows($data);
        return $result;
    }

    public function getReviewDTRows($data)
    {
        $data['tableName'] = "rejection_log";
		$data['select'] = "rejection_log.*,prc_master.prc_date,prc_master.prc_number,process_master.process_name,item_master.item_name,item_master.item_code,IF(rejection_log.decision_type = 1,'Rejection',IF(decision_type = 2,'Rework',IF(decision_type = 5,'OK',''))) as decision,rejection_comment.remark as reason,rejection_parameter.parameter,rrStg.process_name as rr_stage_name,(CASE WHEN rr_by > 0 THEN party_master.party_name ELSE 'Inhouse' END) as rr_by_name,employee_master.emp_name,prc_log.accepted_by,prc_log.accepted_at";
		$data['leftJoin']['prc_log'] = "prc_log.id = rejection_log.log_id";
		$data['leftJoin']['prc_master'] = "prc_master.id = rejection_log.prc_id";
		$data['leftJoin']['process_master'] = "process_master.id = prc_log.process_id";
		$data['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
		$data['leftJoin']['rejection_comment'] = "rejection_comment.id = rejection_log.rr_reason";
		$data['leftJoin']['rejection_parameter'] = "rejection_parameter.id = rejection_log.rej_param";
		$data['leftJoin']['process_master rrStg'] = "rrStg.id = rejection_log.rr_stage";
		$data['leftJoin']['party_master'] = "party_master.id = rejection_log.rr_by";
		$data['leftJoin']['employee_master'] = "employee_master.id = prc_log.accepted_by";
        

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "prc_master.prc_number";
        $data['searchCol'][] = "CONCAT(item_master.item_code,' ',item_master.item_name)";
        $data['searchCol'][] = "process_master.process_name";
        $data['searchCol'][] = "DATE_FORMAT(rejection_log.created_at,'%d-%m-%Y')";
        $data['searchCol'][] = "IF(rejection_log.decision_type = 1,'Rejection',IF(decision_type = 2,'Rework',IF(decision_type = 5,'OK','')))";
        $data['searchCol'][] = "rejection_log.qty";
        $data['searchCol'][] = "rejection_comment.remark";
        $data['searchCol'][] = "rejection_parameter.parameter";
        $data['searchCol'][] = "rrStg.process_name";
        $data['searchCol'][] = "(CASE WHEN rr_by > 0 THEN party_master.party_name ELSE 'Inhouse' END)";
        $data['searchCol'][] = "rejection_log.rr_comment";
        $data['searchCol'][] = "employee_master.emp_name";

        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
		$result = $this->pagingRows($data);
        return $result;
    }

    public function getReviewData($param = []){
        $queryData['tableName'] = "rejection_log";
        $queryData['select'] = "rejection_log.*,prc_log.prc_process_id,prc_master.prc_number,process_master.process_name,item_master.item_name,item_master.item_code,employee_master.emp_name,prc_log.process_by,rw.process_name AS rw_process_name";
        $queryData['select'] .=',prc_log.rej_found,prc_log.qty as ok_qty,IF(prc_log.process_by = 1, machine.item_code, IF(prc_log.process_by = 2,department_master.name, IF(prc_log.process_by = 3,party_master.party_name,""))) as processor_name,creator.emp_name as created_name,rejection_comment.remark as reason,rejection_parameter.parameter';
        $queryData['leftJoin']['prc_log'] = "prc_log.id = rejection_log.log_id";
        $queryData['leftJoin']['prc_master'] = "prc_master.id = rejection_log.prc_id";
		$queryData['leftJoin']['process_master'] = "process_master.id = prc_log.process_id";
		$queryData['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
		$queryData['leftJoin']['item_master machine'] = "machine.id = prc_log.processor_id";
		$queryData['leftJoin']['department_master'] = "department_master.id = prc_log.processor_id";
		$queryData['leftJoin']['party_master'] = "party_master.id = prc_log.processor_id";
		$queryData['leftJoin']['employee_master'] = "employee_master.id = prc_log.operator_id";
		$queryData['leftJoin']['employee_master creator'] = "creator.id = rejection_log.created_by";
		$queryData['leftJoin']['rejection_comment'] = "rejection_comment.id = rejection_log.rr_reason";
		$queryData['leftJoin']['rejection_parameter'] = "rejection_parameter.id = rejection_log.rej_param";
		$queryData['leftJoin']['process_master rw'] = "rw.id = rejection_log.rw_process";
        
        if(!empty($param['id'])){ $queryData['where']['rejection_log.id'] = $param['id']; }
        if(!empty($param['single_row'])){
            return $this->row($queryData);
        }else{
            return $this->rows($queryData);
        }
    }
    public function saveReview($data){
        try {
			$this->db->trans_begin();
			$result = $this->store('rejection_log', $data, 'Decision');
            if($data['decision_type'] == 5){
                $setData = array();
                $setData['tableName'] = 'prc_log';
                $setData['where']['id'] = $data['log_id'];
                $setData['set']['qty'] = 'qty, + ' . $data['qty'];
                $this->setValue($setData);
            }

            if($data['decision_type'] == 1){
                $setData = array();
                $setData['tableName'] = 'prc_log';
                $setData['where']['id'] = $data['log_id'];
                $setData['set']['rej_qty'] = 'rej_qty, + ' . $data['qty'];
                $this->setValue($setData);
            }

            if($data['decision_type'] == 2){
                $setData = array();
                $setData['tableName'] = 'prc_log';
                $setData['where']['id'] = $data['log_id'];
                $setData['set']['rej_found'] = 'rej_found, - ' . $data['qty'];
                $this->setValue($setData);
                $logData = $this->sop->getProcessLogList(['id'=>$data['log_id'],'single_row'=>1]);

                if($data['rework_type'] == 1){
                    $processArray = explode(",",$logData->process_ids);
                    $in_process_key = array_keys($processArray, $logData->process_id)[0];
                    $rw_process_key = array_keys($processArray, $data['rw_process'])[0];

                    $prvPrsData = $this->sop->getPRCProcessList(['next_process_id'=>$logData->process_id,'prc_id'=>$data['prc_id'],'single_row'=>1]);
                    if(!empty($prvPrsData)){
                        $acceptData = [
                            'id'=>'',
                            'prc_id'=>$data['prc_id'],
                            'review_id' => $result['id'],
                            'prc_process_id'=>(!empty($prvPrsData->id)?$prvPrsData->id:0),
                            'accepted_process_id'=>$logData->prc_process_id,
                            'accepted_qty'=>'-'.$data['qty'],
                            'trans_date'=>date('Y-m-d'),
                            'created_by'=>$this->loginId,
                            'created_at'=>date('Y-m-d H:i:s'),
                        ];
                        $this->sop->saveAcceptedQty($acceptData);
                    }
                
                    if($logData->process_id != $data['rw_process']){
                        for($key = ($in_process_key-1); $key >= $rw_process_key; $key-- ){
                            // print_r($processArray[$key]);
                            $processData = $this->sop->getPRCProcessList(['current_process_id'=>$processArray[$key],'prc_id'=>$data['prc_id'],'single_row'=>1]);
                            $prcProcess = explode(",",$processData->process_ids);
                            /** Minus Movement Log  */
                            $movementData = [
                                'id'=>'',
                                'prc_id' => $data['prc_id'],
                                'review_id' => $result['id'],
                                'prc_process_id' =>$processData->id,
                                'process_id' => $processData->current_process_id,
                                'next_process_id' => $processData->next_process_id,
                                'send_to' =>1,
                                'processor_id' =>0,
                                'trans_date' => date("Y-m-d"),
                                'qty' => '-'.$data['qty'],
                            ];
                            $this->sop->savePRCMovement($movementData);
                            
                            /********** END ***********/
                            /** Minus Log  Entry*/
                            $logData = [
                                'id'=>'',
                                'prc_id' => $data['prc_id'],
                                'review_id' => $result['id'],
                                'prc_process_id' => $processData->id,
                                'process_id' => $processData->current_process_id,
                                'ref_id' => 0,
                                'ref_trans_id' => 0,
                                'trans_date' => date("Y-m-d"),
                                'qty' => '-'.$data['qty'],
                                'rej_found' =>  0,
                                'production_time' =>0,
                                'in_challan_no' => '',
                                'process_by' => '',
                                'processor_id' =>'',
                                'shift_id' => '',
                                'operator_id' =>'',
                                'wt_nos' => 0
                            ];
                            $this->sop->savePRCLog($logData);
                            /********** END ***********/
                            if($processData->current_process_id != $prcProcess[0]){
                                /** Minus Accept Log  */
                                $prvPrsData = $this->sop->getPRCProcessList(['next_process_id'=>$processArray[$key],'prc_id'=>$data['prc_id'],'single_row'=>1]);
                                // print_r($this->db->last_query());
                                $acceptData = [
                                    'id'=>'',
                                    'prc_id'=>$data['prc_id'],
                                    'review_id' => $result['id'],
                                    'prc_process_id'=>(!empty($prvPrsData->id)?$prvPrsData->id:''),
                                    'accepted_process_id'=>$processData->id,
                                    'accepted_qty'=>'-'.$data['qty'],
                                    'trans_date'=>date('Y-m-d'),
                                    'created_by'=>$this->loginId,
                                    'created_at'=>date('Y-m-d H:i:s'),
                                ];
                                $this->sop->saveAcceptedQty($acceptData);
                                /********** END ***********/
                            }   
                        }
                    }
                }else{
                    if($data['rw_job_id'] == -1){
                        $prc_no = $this->sop->getNextPRCNo(3);
                        $prc_number = $logData->prc_number.'/RW/'.$prc_no;
                        $masterData = [
                            'id'=>'',
                            'prc_no'=>$prc_no,
                            'prc_type'=>3,
                            'prc_date'=>date("Y-m-d"),
                            'ref_job_id'=>$logData->prc_id,
                            'item_id'=>$logData->item_id,
                            'mfg_type'=>'Rework',
                            'prc_qty'=>$data['qty'],
                            'prc_number'=>$prc_number,
                            'created_by'=>$this->loginId,
                            'created_at'=>date("Y-m-d H:is"),
                        ];
                        $prcResult = $this->store('prc_master', $masterData, 'PRC');
                        $prcDetail = [
                            'id'=>'',
                            'prc_id'=>$prcResult['id'],
                            'process_ids'=>$data['rw_process'],
                            'created_by'=>$this->loginId,
                            'created_at'=>date("Y-m-d H:is"),
                        ];
                        $this->store('prc_detail', $prcDetail, 'PRC');
                        $this->store('rejection_log', ['id'=>$result['id'],'rw_job_id'=>$prcResult['id']]);
                    }else{
                        $updatePRC = [
                            'log_type' => 1,
                            'id' => '',
                            'prc_id' => $data['rw_job_id'],
                            'log_date' => date("Y-m-d"),
                            'qty' => $data['qty']
                        ];
                        $this->sop->savePrcQty($updatePRC);
                    }
                }
                
            }
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return ['status'=>1,'message'=>'Record Updated Successfully'];
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
    }

    public function deleteReview($data){
        try{
            $this->db->trans_begin();
            $reviewData = $this->getReviewData(['id'=>$data['id'],'single_row'=>1]);
            if($reviewData->decision_type == 5){
                $prcProcessData = $this->sop->getPRCProcessList(['id'=>$reviewData->prc_process_id,'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
                $ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
                $movement_qty =!empty($prcProcessData->movement_qty)?$prcProcessData->movement_qty:0;
                $pending_movement = $ok_qty - $movement_qty;
                if($reviewData->qty > $pending_movement){
                    return ['status'=>0,'message'=>'You can not delete this Log. Qty is sent to next process'];
                }
                $setData = array();
                $setData['tableName'] = 'prc_log';
                $setData['where']['id'] = $reviewData->log_id;
                $setData['set']['qty'] = 'qty, - ' . $reviewData->qty;
                $this->setValue($setData);
            }

            if($reviewData->decision_type == 1){
                $setData = array();
                $setData['tableName'] = 'prc_log';
                $setData['where']['id'] =$reviewData->log_id;
                $setData['set']['rej_qty'] = 'rej_qty, - ' . $reviewData->qty;
                $this->setValue($setData);
            }

            if($reviewData->decision_type == 2){
                if($reviewData->rework_type == 1){
                    $prcProcessData = $this->sop->getPRCProcessList(['current_process_id'=> $reviewData->rw_process,'prc_id'=> $reviewData->prc_id,'pending_accepted'=>1,'single_row'=>1]); 
                    $pending_accept =!empty($prcProcessData->pending_accept)?$prcProcessData->pending_accept:0;
                    if($reviewData->qty > $pending_accept){
                        return ['status'=>0,'message'=>'You can not delete this review'];
                    }
                    $setData = array();
                    $setData['tableName'] = 'prc_log';
                    $setData['where']['id'] =$reviewData->log_id;
                    $setData['set']['rej_found'] = 'rej_found, + ' . $reviewData->qty;
                    $this->setValue($setData);
                    $this->trash("prc_accept_log",['review_id'=>$data['id']]);
                    $this->trash("prc_log",['review_id'=>$data['id']]);
                    $this->trash("prc_movement",['review_id'=>$data['id']]);
                }else{
                    $prcData = $this->sop->getPRC(['id'=>$reviewData->rw_job_id]);
                    if($prcData->prc_qty == $reviewData->qty && $prcData->status > 1){
                            return ['status'=>0,'message'=>'You can not delete this review'];
                    }
                    elseif($prcData->prc_qty > $reviewData->qty && $prcData->status > 1){
                        $prcProcessData = $this->sop->getPRCProcessList(['current_process_id'=>0,'prc_id'=>$reviewData->rw_job_id,'single_row'=>1,'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1]);
			
                        $in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
                        $ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
                        $rej_found = !empty($prcProcessData->rej_found)?$prcProcessData->rej_found:0;
                        $rw_qty = !empty($prcProcessData->rw_qty)?$prcProcessData->rw_qty:0;
                        $rej_qty = !empty($prcProcessData->rej_qty)?$prcProcessData->rej_qty:0;
                        $pendingReview = $rej_found - $prcProcessData->review_qty;
                        $pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);

                        if ($pending_production < $reviewData->qty) :
                             return ['status'=>0,'message'=>'You can not delete this review'];
                        endif;
                    }
                    if($prcData->prc_qty == $reviewData->qty){
                        $this->sop->deletePRC($reviewData->rw_job_id);
                    }elseif($prcData->prc_qty > $reviewData->qty){
                        $updatePRC = [
                            'log_type' => -1,
                            'id' => '',
                            'prc_id' => $reviewData->rw_job_id,
                            'log_date' => date("Y-m-d"),
                            'qty' => $reviewData->qty
                        ];
                        $this->sop->savePrcQty($updatePRC);
                    }

                    $setData = array();
                    $setData['tableName'] = 'prc_log';
                    $setData['where']['id'] =$reviewData->log_id;
                    $setData['set']['rej_found'] = 'rej_found, + ' . $reviewData->qty;
                    $this->setValue($setData);
                }  
            }
            $result = $this->trash('rejection_log',['id'=>$data['id']]);

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }
    
    
    public function acceptRejectionReview($data) {
        try{
            $this->db->trans_begin();

            $date = ($data['accepted_by'] == 1) ? date('Y-m-d H:i:s') : NULL;
            $isAccept = ($data['accepted_by'] == 1) ? $this->loginId : 0;
            
            $this->store("prc_log", ['id'=> $data['id'],'accepted_by' => $isAccept, 'accepted_at'=>$date]);

            $result = ['status' => 1, 'message' => 'Rejection Review  ' . $data['msg'] . ' successfully.'];

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	    
    }

    public function getReworkData($param = []){
        $queryData['tableName'] = "rejection_log";
        $queryData['select'] = 'SUM(qty) As rw_qty';
        $queryData['where']['rw_process'] = $param['rw_process'];
        $queryData['where']['decision_type'] = $param['decision_type'];
        $queryData['where']['prc_id'] = $param['prc_id'];
        return $this->row($queryData);
    }

}
