<?php
class SopModel extends MasterModel{
    private $last_prc_no = 0;
	/***
		NOTES : To Get Year Prefix Call getYearPrefix($format,$date="") from Format Helper
		Formats : NUMERIC_YEAR, ALPHA_YEAR, SHORT_YEAR, LONG_YEAR
		Date : Optional
	***/	
	// 26-10-2024
	public function getNextPRCNo($prc_type=1,$mfg_type="",$ref_batch =""){
		$queryData = array(); 
		$queryData['tableName'] = 'prc_master';
		$queryData['select'] = "MAX(prc_no ) as prc_no";
        
		if(!empty($mfg_type)):
			$queryData['where']['mfg_type'] = $mfg_type;
		endif;
		if(!empty($ref_batch)):
			$queryData['where']['ref_batch'] = $ref_batch;
		endif;
		if(!empty($prc_type)):
			$queryData['where']['prc_type'] = $prc_type;
		endif;		
		$queryData['where']['prc_master.prc_date >='] = $this->startYearDate;
		$queryData['where']['prc_master.prc_date <='] = $this->endYearDate;

		$prc_no = $this->specificRow($queryData)->prc_no;
		$prc_no = (empty($this->last_prc_no))?($prc_no + 1):$prc_no;
		return $prc_no;
    }
	
	public function savePRC($param){ 
		try {
			$this->db->trans_begin();

            $opBalance = array();
			
			if(empty($param['masterData']['id'])){
				$prc_type = !empty($param['masterData']['prc_type'])?$param['masterData']['prc_type']:1;
				if($prc_type == 1){
					$itemData = $this->item->getItem(['id'=>$param['masterData']['item_id']]);
					if($param['masterData']['mfg_type'] == 'Forging'){
						$param['masterData']['prc_number'] = $param['masterData']['prc_no'].'/'.date("dmy",strtotime($param['masterData']['prc_date'])).'/'.$itemData->parent_category;
					}else{
						$prcNo = $this->getNextPRCNo(1,'Machining',$param['masterData']['ref_batch']);
						$param['masterData']['prc_number'] = $param['masterData']['ref_batch'].'/MC'.LPAD($prcNo, 3, "0");
					}
				}else{
					$prc_no = $this->getNextPRCNo($prc_type);
					$prc_prefix = 'CUT/'.getYearPrefix('SHORT_YEAR').'/';
					$param['masterData']['prc_number'] = $prc_prefix.$prc_no;
				}
			}
			
			if($param['masterData']['mfg_type'] == 'Forging'){
				$itemData = $this->item->getItem(['id'=>$param['masterData']['item_id']]);
				$param['masterData']['prc_number'] = $param['masterData']['prc_no'].'/'.date("dmy",strtotime($param['masterData']['prc_date'])).'/'.$itemData->parent_category;
			}
            $result = $this->store('prc_master', $param['masterData'], 'PRC');
			
			if(!empty($result['id']))
			{
				$param['prcDetail']['prc_id'] = $result['id'];
				$param['prcDetail']['id'] = (!empty($param['prcDetail']['id'])?$param['prcDetail']['id']:'');
				$prcDetail = $this->store('prc_detail', $param['prcDetail'], 'PRC Detail');
			}

			if(!empty($param['masterData']['mfg_type']) && $param['masterData']['mfg_type'] == 'Machining'){

				if(!empty($param['masterData']['id'])){	
					$this->trash('issue_register', ['prc_id'=>$param['masterData']['id']]);
					$this->trash('stock_transaction', ['child_ref_id'=>$param['masterData']['id'], 'entry_type'=>135]);
				}

				$location_id = [ $this->MACHINING_STORE->id ];
				$batch_no = [ $param['masterData']['ref_batch'] ];
				$heat_no = [ $param['masterData']['heat_no'] ];
				$batch_qty = [ $param['masterData']['prc_qty'] ];

				$issueData = [
					'req_id' => '',
					'issue_date' => date('Y-m-d'),
					'item_id' => $param['masterData']['item_id'],
					'entry_type' => '135',
					'location_id' => $location_id,
					'batch_no' => $batch_no,
					'heat_no' => $heat_no,
					'batch_qty' => $batch_qty,
					'prc_id' => $result['id'],
					'issued_to' => $this->loginId,
					'created_by' => $this->loginId
				];
				$this->store->saveIssueRequisition($issueData);
			}
			
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}
	
	public function getPRCList($param=[]){
        $queryData = array();          
		$queryData['tableName'] = "prc_master";
		
		$queryData['select'] = "prc_master.id,prc_master.prc_type, prc_master.prc_number,prc_master.item_id, prc_master.mfg_type, DATE_FORMAT(prc_master.prc_date,'%d-%m-%Y') as prc_date, DATE_FORMAT(prc_master.target_date,'%d-%m-%Y') as target_date, prc_master.status, prc_master.prc_qty";
		$queryData['select'] .= ", IFNULL(im.item_name,'') as item_name,im.item_code, IFNULL(unit_master.unit_name,'') as uom, IFNULL(pm.party_name,'') as party_name, IFNULL(pd.remark,'') as job_instruction,pd.process_ids";
        
        $queryData['leftJoin']['item_master im'] = "im.id = prc_master.item_id";
        $queryData['leftJoin']['unit_master'] = "unit_master.id = im.unit_id";
        $queryData['leftJoin']['party_master pm'] = "pm.id = prc_master.party_id";
        $queryData['leftJoin']['prc_detail pd'] = "pd.prc_id = prc_master.id";
		
		if(!empty($param['status'])){
			if($param['status'] != 'ALL'){
				$queryData['where']['prc_master.prc_type'] = 1;
				if(in_array($param['status'],[1,4,5])){
					if(!empty($param['status'])){ $queryData['where_in']['prc_master.status'] = $param['status']; }
				}else{
					$queryData['select'] .= ',IFNULL(rejection_log.rej_qty,0) as rej_qty,IFNULL(prc_movement.stored_qty,0) as stored_qty,IFNULL(rework_log.rw_qty,0) as rw_qty,(prc_master.prc_qty - (rejection_log.rej_qty + prc_movement.stored_qty)) as pending_qty';
					$queryData['leftJoin']['(SELECT SUM(qty) as rej_qty,prc_id FROM rejection_log WHERE decision_type = 1 AND is_delete = 0 GROUP BY prc_id) rejection_log'] = "prc_master.id = rejection_log.prc_id";
					$queryData['leftJoin']['(SELECT SUM(qty) as rw_qty,prc_id FROM rejection_log WHERE decision_type = 2 AND is_delete = 0 AND rework_type = 2 GROUP BY prc_id) rework_log'] = "prc_master.id = rework_log.prc_id";
					$queryData['leftJoin']['(SELECT SUM(qty) as stored_qty,prc_id FROM prc_movement WHERE next_process_id = 0 AND is_delete = 0 GROUP BY prc_id) prc_movement'] = "prc_master.id = prc_movement.prc_id";
					if($param['status'] == 2){
						$queryData['having'][] = "(prc_master.prc_qty - (stored_qty + rej_qty+rw_qty)) > 0";
					}elseif($param['status'] == 3){
						$queryData['having'][] = "(prc_master.prc_qty - (stored_qty + rej_qty+rw_qty)) <= 0";
					}
					$queryData['where_in']['prc_master.status'] = 2;
				}
			}
		}
		
		//07-12-2024
		if(!empty($param['prc_stock'])){
			$queryData['select'] .= ',count(stock_trans.id) as stock_id';
			$queryData['leftJoin']['(SELECT id,main_ref_id FROM stock_transaction WHERE entry_type = '.$param['entry_type'].' AND is_delete = 0  GROUP BY main_ref_id) stock_trans'] = "prc_master.id = stock_trans.main_ref_id";
		}
		
		if(!empty($param['mfg_type'])){ $queryData['where']['prc_master.mfg_type'] = $param['mfg_type']; }
		
		if(!empty($param['mfg_route'])){ $queryData['where']['prc_master.mfg_route'] = $param['mfg_route']; }
		
		if(!empty($param['item_id'])){ $queryData['where']['prc_master.item_id'] = $param['item_id']; }
		
		if(!empty($param['so_trans_id'])){ $queryData['where']['prc_master.so_trans_id'] = $param['so_trans_id']; }
		
		if(!empty($param['party_id'])){ $queryData['where']['prc_master.party_id'] = $param['party_id']; }
		
		if(!empty($param['target_date'])){ $queryData['where']['prc_master.target_date'] = $param['target_date']; }

		
		if(!empty($param['ref_batch'])){ $queryData['where']['prc_master.ref_batch'] = $param['ref_batch']; }
		
		if(!empty($param['heat_no'])){ $queryData['where']['prc_master.heat_no'] = $param['heat_no']; }
		
		if(!empty($param['overdue'])){ $queryData['where']['prc_master.target_date < '] = $param['overdue']; }
		
		if(!empty($param['ref_job_id'])){ $queryData['where']['prc_master.ref_job_id'] = $param['ref_job_id']; }
		
		if(!empty($param['prc_type'])){ $queryData['where']['prc_master.prc_type'] = $param['prc_type']; }
		
		if(!empty($data['customWhere'])){ $queryData['customWhere'][] = $data['customWhere']; }
		
        if(!empty($param['skey'])){
			$queryData['like']['prc_master.prc_number'] = str_replace(" ", "%", $param['skey']);
			$queryData['like']['prc_master.mfg_type'] = str_replace(" ", "%", $param['skey']);
			$queryData['like']['prc_master.prc_date'] = str_replace(" ", "%", $param['skey']);
			$queryData['like']['im.item_name'] = str_replace(" ", "%", $param['skey']);
			$queryData['like']['pm.party_name'] = str_replace(" ", "%", $param['skey']);
        }

        if(!empty($param['limit'])){ $queryData['limit'] = $param['limit']; }
        
		if(isset($param['start'])){ $queryData['start'] = $param['start']; }
		
		if(!empty($param['length'])){ $queryData['length'] = $param['length']; }
		
		$queryData['order_by']['prc_master.prc_date'] = 'DESC';
		$queryData['order_by']['prc_master.id'] = 'DESC';
		$queryData['group_by'][] = "prc_master.id";
        $result = $this->rows($queryData);
        // $this->printQuery();
        return $result;  
    } 
    
	/** GET PRC DETAIL */
	public function getPRCDetail($param=[]){
        $queryData = array();$result = new stdClass();
		$queryData['tableName'] = "prc_detail";
		
		$queryData['select'] = "prc_detail.*,prc_master.ref_job_id, prc_master.prc_number,prc_master.prc_qty, prc_master.mfg_type,prc_master.item_id, DATE_FORMAT(prc_master.prc_date,'%d-%m-%Y') as prc_date, DATE_FORMAT(prc_master.target_date,'%d-%m-%Y') as target_date, prc_master.status, prc_master.prc_qty,prc_master.prc_type,employee_master.emp_name";
		$queryData['select'] .= ", IFNULL(im.item_name,'') as item_name,IFNULL(im.item_code,'') as item_code, IFNULL(unit_master.unit_name,'') as uom, IFNULL(pm.party_name,'') as party_name";
       	$queryData['select'] .= ", IFNULL(so_master.trans_number,'') as so_number,so_master.doc_no";
        
        $queryData['leftJoin']['prc_master'] = "prc_master.id = prc_detail.prc_id";
        $queryData['leftJoin']['item_master im'] = "im.id = prc_master.item_id";
        $queryData['leftJoin']['unit_master'] = "unit_master.id = im.unit_id";
        $queryData['leftJoin']['party_master pm'] = "pm.id = prc_master.party_id";
        $queryData['leftJoin']['employee_master'] = "employee_master.id = prc_master.created_by";
        $queryData['leftJoin']['so_trans'] = "so_trans.id = prc_master.so_trans_id";
        $queryData['leftJoin']['so_master'] = "so_master.id = so_trans.trans_main_id";
		
		if(!empty($param['prc_id'])){ $queryData['where']['prc_detail.prc_id'] = $param['prc_id']; }
		
		if(!empty($param['id'])){ $queryData['where']['prc_master.id'] = $param['id']; }
		
		if(!empty($param['customWhere'])){ $queryData['customWhere'][] = $param['customWhere']; }
		
        $result = $this->row($queryData);
        
        if(!empty($result->prc_id))
        {
            $result->prcProcessData = [];
            if($result->status > 1){ $result->prcProcessData = $this->getPRCProcessList(['prc_id'=>$result->prc_id,'log_data'=>1,'movement_data'=>1,'pending_accepted'=>1]); }
            elseif(!empty($result->process_ids)){ $result->prcProcessData = $this->getProcessFromPRC(['process_ids'=>$result->process_ids,'item_id'=>$result->item_id]); }
        }
        //$this->printQuery();
        return $result;  
    }
    
	/**
	 * GET SINGLE & MULTI PRC PROCESS DATA 
	 * PRODUCTION LOG QTY, MOVEMENT QTY, ACCEPTED QTY
	 * PENDING PRODUCTION QTY = INQTY -(OK QTY+REJ+RW+PENDING REVIEW)
	 * PENDING ACCEPT = PREVIOUS PROCESS'S MOVEMENT QTY - CURRENT ACCEPT)
	 * PENDING MOVEMENT = TOTAL OK QTY -(TOTAL MOVEMENT - NEXT PROCESS'S SHORT QTY)
	*/
	public function getPRCProcessList($param=[]){
        $queryData = array();          
		$queryData['tableName'] = "prc_process";
		$queryData['select'] = "prc_process.*";
		$queryData['select'] .= ", IFNULL(cp.process_name,'') as current_process, IFNULL(np.process_name,'') as next_process,prc_master.prc_number,prc_master.prc_qty,prc_master.prc_date,prc_master.item_id,prc_detail.process_ids,item_master.item_name,item_master.item_code,product_process.finish_wt,prc_master.mfg_type";
        
        $queryData['leftJoin']['process_master cp'] = "cp.id = prc_process.current_process_id";
        $queryData['leftJoin']['process_master np'] = "np.id = prc_process.next_process_id";
        $queryData['leftJoin']['prc_master'] = "prc_master.id = prc_process.prc_id";
        $queryData['leftJoin']['prc_detail'] = "prc_detail.prc_id = prc_process.prc_id";
        $queryData['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
		$queryData['leftJoin']['product_process'] = "product_process.process_id = prc_process.current_process_id AND product_process.item_id = prc_master.item_id"; 

		if(!empty($param['rm_item_id'])) { 
			$queryData['select'] .= ",(CASE WHEN product_process.finish_wt > 0 THEN product_process.finish_wt ELSE item_kit.qty END) as finish_wt,item_kit.id as kit_id";
			$queryData['leftJoin']['item_kit'] = "item_kit.item_id = prc_master.item_id AND item_kit.ref_item_id = '".$param['rm_item_id']."' AND item_kit.is_delete = 0";
		}
		
		/** IF LOG DATA  GET (Total production ok qty, rejection found qty, review qty,challan qty) */
		if(!empty($param['log_data'])){
			$customWh = "";
			if(!empty($param['log_process_by'])){
				$customWh = " AND process_by != 3";
			}
			$queryData['select'] .= ",IFNULL(prcLog.ok_qty,0) as ok_qty, IFNULL(prcLog.rej_qty,0) as rej_qty, IFNULL(prcLog.rw_qty,0) as rw_qty,IFNULL(prcLog.rej_found,0) as rej_found, IFNULL(prc_challan_request.ch_qty,0) as ch_qty,IFNULL(rejection_log.review_qty,0) as review_qty,IFNULL(rw_log.review_rw_qty,0) as review_rw_qty ";

			$queryData['leftJoin']['(SELECT SUM(prc_log.qty) as ok_qty, SUM((prc_log.rej_qty)) as rej_qty, SUM((prc_log.rw_qty)) as rw_qty, SUM(prc_log.rej_found) as rej_found,prc_process_id FROM prc_log WHERE is_delete = 0 '.$customWh.' GROUP BY prc_process_id) prcLog'] =  "prcLog.prc_process_id = prc_process.id";

			$queryData['leftJoin']['(SELECT SUM(prc_challan_request.qty - prc_challan_request.without_process_qty) as ch_qty,prc_process_id FROM prc_challan_request WHERE is_delete = 0 '.((!empty($param['trans_type']))?' AND prc_challan_request.trans_type = '.$param['trans_type']:'').' GROUP BY prc_process_id) prc_challan_request'] =  "prc_challan_request.prc_process_id = prc_process.id";

			$queryData['leftJoin']['(SELECT SUM(rejection_log.qty) as review_qty,rejection_log.log_id,rejection_log.prc_id,prc_log.prc_process_id FROM rejection_log LEFT JOIN prc_log ON prc_log.id = rejection_log.log_id WHERE rejection_log.is_delete = 0 AND rejection_log.decision_type != 2 GROUP BY prc_log.prc_process_id,prc_log.prc_id) rejection_log'] = "rejection_log.prc_id = prc_process.prc_id AND rejection_log.prc_process_id = prc_process.id";
			
			$queryData['leftJoin']['(SELECT SUM(rejection_log.qty) as review_rw_qty,rejection_log.log_id,rejection_log.prc_id,prc_log.prc_process_id FROM rejection_log LEFT JOIN prc_log ON prc_log.id = rejection_log.log_id WHERE rejection_log.is_delete = 0 AND rejection_log.decision_type = 2 GROUP BY prc_log.prc_process_id,prc_log.prc_id) rw_log'] = "rw_log.prc_id = prc_process.prc_id AND rw_log.prc_process_id = prc_process.id";
		}
		/** MOVEMENT GET (Total movement_qty) */
		if(!empty($param['movement_data'])){
			$queryData['select'] .= ",(IFNULL(prc_movement.movement_qty,0)-IFNULL(current_accept_log.short_qty,0)) as movement_qty,IFNULL(current_accept_log.short_qty,0) as short_qty";
			$queryData['leftJoin']['(SELECT SUM(prc_movement.qty) as movement_qty,prc_process_id FROM prc_movement WHERE prc_movement.is_delete=0 GROUP BY prc_process_id) prc_movement']="prc_movement.prc_process_id = prc_process.id";
			
			$queryData['leftJoin']['(SELECT SUM(IFNULL(prc_accept_log.accepted_qty,0)) as accepted_qty,SUM(IFNULL(prc_accept_log.short_qty,0)) as short_qty,prc_process_id FROM prc_accept_log WHERE prc_accept_log.is_delete=0 GROUP BY prc_process_id) current_accept_log']="current_accept_log.prc_process_id = prc_process.id";	
		}

		/** GET INWARD QTY (in_qty,pending accept)*/
		if(!empty($param['pending_accepted'])){
			$queryData['select'] .= ",((IFNULL(prevMovement.move_qty,0)-IFNULL(prc_accept_log.short_qty,0)) - IFNULL(prc_accept_log.accepted_qty,0)) as pending_accept,IFNULL(prc_accept_log.accepted_qty,0) as in_qty,prevProcess.id as prev_prc_process_id,(IFNULL(prevMovement.move_qty,0)-IFNULL(prc_accept_log.short_qty,0)) as inward_qty,prevProcess.next_process_id as prev_id";

			$queryData['leftJoin']['prc_process prevProcess'] = "prevProcess.next_process_id = prc_process.current_process_id AND prc_process.work_type  =  prevProcess.work_type AND prevProcess.is_delete = 0 AND prevProcess.prc_id = prc_process.prc_id";

			$queryData['leftJoin']['(SELECT SUM(prc_accept_log.accepted_qty) as accepted_qty,SUM(prc_accept_log.short_qty) as short_qty,accepted_process_id FROM prc_accept_log WHERE prc_accept_log.is_delete=0 GROUP BY accepted_process_id) prc_accept_log']="prc_accept_log.accepted_process_id = prc_process.id";

			$queryData['leftJoin']['(SELECT SUM(prc_movement.qty) as move_qty,prc_process_id FROM prc_movement WHERE prc_movement.is_delete=0 AND send_to = 1 GROUP BY prc_process_id) prevMovement']="prevMovement.prc_process_id = prevProcess.id";
		}
		if(!empty($param['prc_id'])){ $queryData['where']['prc_process.prc_id'] = $param['prc_id']; }
		
		if(!empty($param['current_process_id'])){ $queryData['where']['prc_process.current_process_id'] = $param['current_process_id']; }

		if(!empty($param['next_process_id'])){ $queryData['where']['prc_process.next_process_id'] = $param['next_process_id']; }

		if(!empty($param['work_type'])){ $queryData['where']['prc_process.work_type'] = $param['work_type']; }

		if(!empty($param['id'])){ $queryData['where']['prc_process.id'] = $param['id']; }
		
		if(!empty($param['item_id'])){ $queryData['where']['prc_master.item_id'] = $param['item_id']; }
		
		if(!empty($param['customWhere'])){ $queryData['customWhere'][] = $param['customWhere']; }
		
		$queryData['group_by'][]="prc_process.id";

		if(!empty($param['single_row'])){ $result = $this->row($queryData); }
		else{ $result = $this->rows($queryData); }
        return $result;  
    }
    
	public function getProcessFromPRC($param=[]){
        $queryData = array();          
		$queryData['tableName'] = "product_process";
		$queryData['select'] = "process_master.process_name,process_master.id";
		$queryData['leftJoin']['process_master'] = 'process_master.id = product_process.process_id';
		if(!empty($param['process_ids'])){ $queryData['where_in']['process_master.id'] = $param['process_ids']; } 
		if(!empty($param['item_id'])){ $queryData['where']['product_process.item_id'] = $param['item_id']; } 
		$queryData['order_by']['product_process.sequence'] = 'ASC';
        $result = $this->rows($queryData);
        return $result;
    }
    
	// 26-10-2024
	public function deletePRC($id){
        try{
            $this->db->trans_begin();

            // $checkData['columnName'] = ["prc_id"];
            // $checkData['value'] = $id;
            // $checkUsed = $this->checkUsage($checkData);
            
            // if($checkUsed == true):
            //     return ['status'=>0,'message'=>'The PRC is currently in use. you cannot delete it.'];
            // endif;

			$prcData = $this->getPRC(['id'=>$id]);
			if($prcData->mfg_type == 'Machining'){
				$this->trash('issue_register', ['prc_id'=>$id]);
				$this->trash('stock_transaction', ['child_ref_id'=>$id, 'entry_type'=>135]);
			}

            $this->trash('prc_detail',['prc_id'=>$id],'PRC Detail');
            $result = $this->trash('prc_master',['id'=>$id],'PRC');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

	public function savePRCLog($param){
		try {
			$this->db->trans_begin();
          
			/*** Check Required Material For Production */
			if($param['process_id'] > 0){
				$prcData = $this->getPRC(['id'=>$param['prc_id']]);
				if($prcData->prc_type == 1){
				$production_qty = ($param['qty']+$param['rej_found']);
				$wt_nos = !empty($param['wt_nos'])?$param['wt_nos']:0;
				$mtResult = $this->checkIssueMaterialForPrc(['prc_id'=>$param['prc_id'],'process_id'=>$param['process_id'],'production_qty'=>$production_qty,'wt_nos'=>$wt_nos]);
				if($mtResult['status'] == 0){
					return $mtResult;
				}
				}
				
			}
			$logDetail = (!empty($param['logDetail']))?$param['logDetail']:[];
			$without_process_qty = (!empty($param['without_process_qty']))?$param['without_process_qty']:0;
			unset($param['logDetail'],$param['without_process_qty']);
            $result = $this->store('prc_log', $param, 'PRC Log');
			if(!empty($logDetail)){
				$logDetail['log_id'] = $result['id'];	
				$this->store('prc_log_detail', $logDetail, 'PRC Log Detail');
			}
			
			// IF Vendor Return Log Without process
			if($param['process_by'] == 3 && !empty($without_process_qty)){
				//Set Without Process Qty in prc challan request table
				$setData = array();
                $setData['tableName'] = 'prc_challan_request';
                $setData['where']['id'] = $param['ref_trans_id'];
                $setData['set']['without_process_qty'] = 'without_process_qty, + ' . $without_process_qty;
                $this->setValue($setData);

				//Without process Log
				$logData = [
					'id'=>'',
					'prc_id'=>$param['prc_id'],
					'challan_id'=>$param['ref_id'],
					'challn_req_id'=>$param['ref_trans_id'],
					'log_id'=>$result['id'],
					'in_challan_no'=>$param['in_challan_no'],
					'qty'=>$without_process_qty,
					'created_by'=>$this->loginId,
					'created_at'=>date("Y-m-d H:i:s"),
				];
				$this->store('without_process_log',$logData);
			}
			 
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function deletePRCLog($param){
		try {
			$this->db->trans_begin();

			$logData = $this->getProcessLogList(['id'=>$param['id'],'rejection_review_data'=>1,'single_row'=>1,'outsource_without_process'=>1]);
			if(!empty($logData)){
				$movementData =  $this->sop->getPRCProcessList(['id'=>$logData->prc_process_id,'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
				$pending_movement = $movementData->ok_qty - $movementData->movement_qty;

				if ($logData->review_qty > 0){
					return ['status'=>0,'message'=>'You can not delete this Log. You have to delete rejection review first'];
				}
				if(($logData->qty > $pending_movement) ){
					return ['status'=>0,'message'=>'You can not delete this Log. Qty is sent to next process'];
				}

				//Check If without Process Qty
				if($logData->process_by == 3 && $logData->without_process_qty > 0){
					$prcProcessData = $this->sop->getPRCProcessList(['id'=>$logData->prc_process_id,'log_data'=>1,'pending_accepted'=>1,'log_process_by'=>1,'single_row'=>1]);
					$in_qty = (!empty($prcProcessData->current_process_id))?(!empty($prcProcessData->in_qty)?$prcProcessData->in_qty:0):$prcProcessData->ok_qty;
					$ok_qty = !empty($prcProcessData->ok_qty)?$prcProcessData->ok_qty:0;
					$rej_found = !empty($prcProcessData->rej_found)?$prcProcessData->rej_found:0;
					$rw_qty = !empty($prcProcessData->rw_qty)?$prcProcessData->rw_qty:0;
					$rej_qty = !empty($prcProcessData->rej_qty)?$prcProcessData->rej_qty:0;
					$pendingReview = $rej_found - $prcProcessData->review_qty;
					$pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$prcProcessData->ch_qty);

					if($logData->without_process_qty > $pending_production){
						return ['status'=>0,'message'=>'You can not delete this Log. Production of returned qty without process has been done. '];
					}
					$setData = array();
					$setData['tableName'] = 'prc_challan_request';
					$setData['where']['id'] = $logData->ref_trans_id;
					$setData['set']['without_process_qty'] = 'without_process_qty, - ' . $logData->without_process_qty;
					$this->setValue($setData);
					$this->trash('without_process_log',['log_id'=>$param['id']]);
				}
				
				$this->trash('prc_log_detail',['log_id'=>$param['id']]);
				$result = $this->trash('prc_log',['id'=>$param['id']]);
				
			}else{
				$result = ['status'=>0,'message'=>'Log already deleted'];
			}

			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}
	
	public function getProcesStates($param=[]){
        $queryData = array();          
		$queryData['tableName'] = "prc_log";
		
		$queryData['select'] = "SUM(qty) as ok_qty, SUM((rej_qty)) as rej_qty, SUM((rw_qty)) as rw_qty, SUM(rej_found) as rej_found";
        
		if(!empty($param['rejection_review_data'])){
			$queryData['select'] .=',SUM(IFNULL(rejection_log.review_qty,0)) as review_qty,SUM((prc_log.rej_found-IFNULL(rejection_log.review_qty,0))) as pending_qty';
			$queryData['leftJoin']['(SELECT SUM(qty) as review_qty,SUM(CASE WHEN decision_type = 5 THEN qty ELSE 0 END) as ok_qty,log_id,prc_id FROM rejection_log WHERE is_delete = 0 AND decision_type != 2 GROUP BY rejection_log.log_id,prc_id) rejection_log'] = "rejection_log.log_id = prc_log.id AND prc_log.prc_id = rejection_log.prc_id";
		}
		if(!empty($param['id'])){ $queryData['where']['prc_log.id'] = $param['id']; }

		if(!empty($param['prc_process_id'])){ $queryData['where']['prc_log.prc_process_id'] = $param['prc_process_id']; }

		if(!empty($param['trans_type'])){ $queryData['where']['prc_log.trans_type'] = $param['trans_type']; }
		
		if(!empty($param['prc_id'])){ $queryData['where']['prc_log.prc_id'] = $param['prc_id']; }	
		
		if(!empty($param['trans_date'])){ $queryData['where']['prc_log.trans_date'] = $param['trans_date']; }	
		
		if(!empty($param['process_id'])){ $queryData['where']['prc_log.process_id'] = $param['process_id']; }
		
		if(!empty($param['process_by'])){ $queryData['where_in']['prc_log.process_by'] = $param['process_by']; }
		
		if(!empty($param['processor_id'])){ $queryData['where']['prc_log.processor_id'] = $param['processor_id']; }		
			
		if(!empty($param['operator_id'])){ $queryData['where']['prc_log.operator_id'] = $param['operator_id']; }
		
		if(!empty($param['machine_id'])){ $queryData['where']['prc_log.machine_id'] = $param['machine_id']; }
		
		if(!empty($data['customWhere'])){ $queryData['customWhere'][] = $data['customWhere']; }
		
		if(!empty($param['machine_id'])){ $queryData['where']['prc_log.machine_id'] = $param['machine_id']; }
		
		$result = $this->row($queryData);
        

        return $result;  
    }

	public function getProcessLogList($param=[]){
        $queryData = array();          
		$queryData['tableName'] = "prc_log";
		
		$queryData['select'] = "prc_log.*,employee_master.emp_name,shift_master.shift_name,prc_log_detail.remark,prc_log_detail.rej_reason,prc_log_detail.rej_param,prc_detail.process_ids,prc_master.item_id,prc_master.prc_number,item_master.item_name,process_master.process_name,product_process.cycle_time,prc_log_detail.start_time,prc_log_detail.end_time,prc_process.next_process_id";
		$queryData['select'] .=', IF(prc_log.process_by = 1, machine.item_code, IF(prc_log.process_by = 2,department_master.name, IF(prc_log.process_by = 3,party_master.party_name,""))) as processor_name,machine.item_name as machine_name';
		$queryData['leftJoin']['item_master machine'] = "machine.id = prc_log.processor_id";
		$queryData['leftJoin']['department_master'] = "department_master.id = prc_log.processor_id";
		$queryData['leftJoin']['party_master'] = "party_master.id = prc_log.processor_id";
		$queryData['leftJoin']['employee_master'] = "employee_master.id = prc_log.operator_id";
        $queryData['leftJoin']['shift_master'] = "shift_master.id = prc_log.shift_id";
        $queryData['leftJoin']['prc_log_detail'] = "prc_log_detail.log_id = prc_log.id";
        $queryData['leftJoin']['prc_master'] = "prc_master.id = prc_log.prc_id";
        $queryData['leftJoin']['prc_detail'] = "prc_detail.prc_id = prc_log.prc_id";
		$queryData['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
		$queryData['leftJoin']['process_master'] = "process_master.id = prc_log.process_id";
		$queryData['leftJoin']['prc_process'] = "prc_process.id = prc_log.prc_process_id";
		$queryData['leftJoin']['product_process'] = "product_process.process_id = prc_log.process_id AND product_process.item_id = prc_master.item_id AND product_process.is_delete = 0";
        
		if(!empty($param['nextProcess'])){
			$queryData['select'] .= ',nxtProcess.process_name AS next_process';
			$queryData['leftJoin']['process_master nxtProcess'] = 'nxtProcess.id  = prc_process.next_process_id';
		}

		if(!empty($param['outsource_without_process'])){
			$queryData['select'] .= ',without_process_log.qty AS without_process_qty';
			$queryData['leftJoin']['without_process_log'] = 'without_process_log.log_id  = prc_log.id';
		}

		if(!empty($param['rejection_review_data'])){
			$queryData['select'] .=',IFNULL(rejection_log.review_qty,0) as review_qty,(prc_log.rej_found-IFNULL(rejection_log.review_qty,0)) as pending_qty,rejection_log.ok_qty';
			$queryData['leftJoin']['(SELECT SUM(qty) as review_qty,SUM(CASE WHEN decision_type = 5 THEN qty ELSE 0 END) as ok_qty,log_id,prc_id FROM rejection_log WHERE is_delete = 0 AND decision_type != 2 GROUP BY rejection_log.log_id,prc_id) rejection_log'] = "rejection_log.log_id = prc_log.id AND prc_log.prc_id = rejection_log.prc_id";
		}

		if(!empty($param['log_used_material'])){
			$queryData['select'] .= ",SUM((prc_log.qty+(prc_log.rej_found - IFNULL(rejection_log.ok_qty,0))) * wt_nos) as used_material,SUM(prc_log.qty+(prc_log.rej_found - IFNULL(rejection_log.ok_qty,0))) as production_qty";
		}
		if(!empty($param['id'])){ $queryData['where']['prc_log.id'] = $param['id']; }

		if(!empty($param['prc_process_id'])){ $queryData['where']['prc_log.prc_process_id'] = $param['prc_process_id']; }
		
		if(!empty($param['prc_id'])){ $queryData['where']['prc_log.prc_id'] = $param['prc_id']; }	
		
		if(!empty($param['trans_date'])){ $queryData['where']['prc_log.trans_date'] = $param['trans_date']; }	
		
		if(isset($param['process_id'])){ $queryData['where']['prc_log.process_id'] = $param['process_id']; }
		
		if(!empty($param['process_by'])){ $queryData['where']['prc_log.process_by'] = $param['process_by']; }
		
		if(!empty($param['processor_id'])){ $queryData['where']['prc_log.processor_id'] = $param['processor_id']; }		
			
		if(!empty($param['operator_id'])){ $queryData['where']['prc_log.operator_id'] = $param['operator_id']; }
		
		if(!empty($param['machine_id'])){ $queryData['where']['prc_log.machine_id'] = $param['machine_id']; }
		
		if(!empty($param['customWhere'])){ $queryData['customWhere'][] = $param['customWhere']; }
		
		if(!empty($param['machine_id'])){ $queryData['where']['prc_log.machine_id'] = $param['machine_id']; }

		if(!empty($param['ref_id'])){ $queryData['where']['prc_log.ref_id'] = $param['ref_id']; }
 		/*if(!empty($param['created_by'])){ $queryData['where']['prc_log.created_by'] = $param['created_by']; } */ //30-12-2024

		if(isset($param['ref_trans_id'])){ $queryData['where']['prc_log.ref_trans_id'] = $param['ref_trans_id']; }
		if(!empty($param['group_by'])){
			$queryData['group_by'][] = $param['group_by'];
		}
		$queryData['order_by']['prc_log.id'] = 'ASC';
		if(!empty($param['single_row'])){
			$result = $this->row($queryData);
		}else{
			$result = $this->rows($queryData);
		}
        return $result;  
    }

	public function getProcessMovementList($param=[]){
        $queryData = array();          
		$queryData['tableName'] = "prc_movement";
		
		$queryData['select'] = "prc_movement.*,prc_process.work_type,prc_master.item_id,prc_master.prc_number,prc_master.prc_type,location_master.store_name,prc_master.prc_qty, item_master.item_name, item_master.item_code, process_master.process_name as next_process_name,current_process.process_name as current_process_name,prc_master.ref_job_id";
		$queryData['select'] .=', IF(prc_movement.send_to = 1, machine.item_code, IF(prc_movement.send_to = 2,department_master.name, IF(prc_movement.send_to = 3,party_master.party_name,""))) as processor_name,
								IF(prc_movement.send_to = 1, "Inhouse", IF(prc_movement.send_to = 2,"Department", IF(prc_movement.send_to = 3,"Vendor", IF(prc_movement.send_to = 4,"Stored","")))) as send_to_name';
		$queryData['leftJoin']['item_master machine'] = "machine.id = prc_movement.processor_id";
		$queryData['leftJoin']['department_master'] = "department_master.id = prc_movement.processor_id";
		$queryData['leftJoin']['party_master'] = "party_master.id = prc_movement.processor_id";
		$queryData['leftJoin']['prc_process'] = "prc_process.id = prc_movement.prc_process_id";
		$queryData['leftJoin']['prc_master'] = "prc_master.id = prc_movement.prc_id";
		$queryData['leftJoin']['location_master'] = "location_master.id = prc_movement.processor_id";
		$queryData['leftJoin']['process_master'] = "process_master.id = prc_movement.next_process_id";
		$queryData['leftJoin']['process_master current_process'] = "current_process.id = prc_movement.process_id"; // 30-12-2024
		$queryData['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
		
		if(!empty($param['nextPrcProcessData'])){
			$queryData['select'] .= ',nextProcess.id AS next_prc_process';
			$queryData['leftJoin']['prc_process nextProcess'] = "nextProcess.current_process_id = prc_movement.next_process_id AND nextProcess.prc_id = prc_movement.prc_id";
		}
        
		if(!empty($param['convert_item'])){
			$queryData['select'] .= ",convert.item_name AS convert_item_name";
			$queryData['leftJoin']['item_master convert'] = "convert.id = prc_movement.convert_item";
		}
        		
		if(!empty($param['id'])){ $queryData['where']['prc_movement.id'] = $param['id']; }

		if(!empty($param['prc_process_id'])){ $queryData['where']['prc_movement.prc_process_id'] = $param['prc_process_id']; }
		
		if(!empty($param['prc_id'])){ $queryData['where']['prc_movement.prc_id'] = $param['prc_id']; }	
		
		if(!empty($param['trans_date'])){ $queryData['where']['prc_movement.trans_date'] = $param['trans_date']; }	
		
		if(!empty($param['process_id'])){ $queryData['where']['prc_movement.process_id'] = $param['process_id']; }

		if(!empty($param['next_process_id'])){ $queryData['where']['prc_movement.next_process_id'] = $param['next_process_id']; }
		
		if(!empty($param['send_to'])){ $queryData['where']['prc_movement.send_to'] = $param['send_to']; }
		
		if(!empty($param['processor_id'])){ $queryData['where']['prc_movement.processor_id'] = $param['processor_id']; }

		if(!empty($param['created_by'])){ $queryData['where']['prc_movement.created_by'] = $param['created_by']; } //30-12-2024

		if(!empty($param['work_type'])){ $queryData['where']['prc_process.work_type'] = $param['work_type']; }		

		if(!empty($param['next_processor_id'])){ $queryData['where']['prc_movement.next_processor_id'] = $param['next_processor_id']; }	

		if(!empty($param['customWhere'])){ $queryData['customWhere'][] = $param['customWhere']; }
		
		if(!empty($param['group_by'])){
			$queryData['group_by'][] = $param['group_by'];
		}
		if(!empty($param['single_row'])){
			$result = $this->row($queryData);
		}else{
			$result = $this->rows($queryData);
		}
		
		
        return $result;  
    }

	public function startPRC($data){
		try {
			$this->db->trans_begin();
            $prcData = $this->getPRCDetail(['prc_id'=>$data['id']]);
			if($prcData->status == 1){
				if($prcData->prc_type == 1 OR $prcData->prc_type == 3){

					$processArray = explode(",",$prcData->process_ids);
					$i=0;
					/*** Save Process IN prc_process Table */
					foreach($processArray as $process_id){
						$finish_weight = 0;
						if($i > 0){
							$prsData = $this->item->getProductProcessList(['item_id'=>$prcData->item_id,'process_id'=>$process_id,'single_row'=>1]);
							$finish_weight = !empty($prsData->finish_wt)?$prsData->finish_wt:0;
						}
						
						$prcProcessData = [
							'id'=>'',
							'prc_id'=>$data['id'],
							'current_process_id'=>$process_id,
							'next_process_id'=>(!empty($processArray[$i+1])?$processArray[$i+1]:0),
							'finish_weight'=>$finish_weight,
							'output_qty'=>1,
							'created_by'=>$this->loginId,
							'created_at'=>date("Y-m-d H:m:i")
						];
						$result = $this->store('prc_process',$prcProcessData);
						/** If Initial Stage then auto production log */
						if($i == 0){
							/*$logData = [
								'id'=>'',
								'prc_id'=>$data['id'],
								'prc_process_id'=>$result['id'],
								'trans_date'=>date("Y-m-d"),
								'process_id'=>0,
								'process_by'=>1,
								'processor_id'=>0,
								'qty'=>$prcData->prc_qty,
								'created_by'=>$this->loginId,
								'created_at'=>date("Y-m-d H:i:s")
							];
							$this->savePRCLog($logData);*/
							$acceptData = [
        						'id'=>'',
        						'prc_id'=>$data['id'],
        						'prc_process_id'=>0,
        						'accepted_process_id'=>$result['id'],
        						'accepted_qty'=>$prcData->prc_qty,
        						'trans_date'=>date('Y-m-d'),
        						'created_by'=>$this->loginId,
        						'created_at'=>date('Y-m-d H:i:s'),
        					];
        					$this->saveAcceptedQty($acceptData);
						}
						$i++;
					}
				}
				/** Inprogress Prc */
				$result = $this->store("prc_master",['id'=>$data['id'],'status'=>2]);
			}else{
				$result = ['status' => 2, 'message' => "You have already started this job card"];
			}
			
			
						
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function getNextTagno(){
		$queryData = array(); 
		$queryData['tableName'] = 'prc_movement';
		$queryData['select'] = "MAX(tag_no ) as tag_no";
		$queryData['where']['prc_movement.trans_date >='] = $this->startYearDate;
		$queryData['where']['prc_movement.trans_date <='] = $this->endYearDate;

		$tag_no = $this->specificRow($queryData)->tag_no;
		$tag_no = (!empty($tag_no))?($tag_no + 1):1;
		return $tag_no;
	}
	
	public function savePRCMovement($param){
		try {
			$this->db->trans_begin();
			/** If Initial Stage then check requred material */
			$prcData = $this->getPRCDetail(['id'=>$param['prc_id']]);
 			//$location_id = ($prcData->mfg_type == 'Forging')?$this->FORGE_STORE->id:$this->FIR_STORE->id;
			$location_id =$this->FIR_STORE->id;
			
			$param['processor_id'] = $location_id;
			$param['tag_no'] = $this->getNextTagno();
            $result = $this->store('prc_movement', $param, 'PRC Log');
			/** If Last Process then add to stock */
			if($param['next_process_id'] == 0){
				
				$entryData = $this->transMainModel->getEntryType(['controller'=>'sopDesk']);
				
				if($prcData->prc_type == 3){
					$refJob = $this->getPRC(['id'=>$prcData->ref_job_id]);
					$batch_no = $refJob->prc_number;
					$materialData = $this->getMaterialIssueData(['prc_id'=>$prcData->ref_job_id,'single_row'=>1]);
					$heat_no = !empty($materialData->heat_no)?$materialData->heat_no:$materialData->stock_trans_heat;
				}else{
					$batch_no = $prcData->prc_number;//($prcData->mfg_type == 'Forging')?$prcData->prc_number:$materialData->batch_no;
				$materialData = $this->getMaterialIssueData(['prc_id'=>$param['prc_id'],'single_row'=>1]);
				$heat_no = !empty($materialData->heat_no)?$materialData->heat_no:$materialData->stock_trans_heat;
				}
				
				$stockData = [
					'id' => "",
					'entry_type' => $entryData->id,
					'ref_date' => $param['trans_date'],
					'ref_no' => $prcData->prc_number,
					'main_ref_id' => $param['prc_id'],
					'child_ref_id' => $result['id'],
					'location_id' => $location_id,
					'batch_no' =>$batch_no,
					'heat_no' =>$heat_no,
					'item_id' => $prcData->item_id,
					'p_or_m' => 1,
					'qty' => $param['qty'],
					'created_by'=>$this->loginId
				];

				$this->store('stock_transaction',$stockData);
			}
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function saveReceiveStoredMaterial($data){
		try {
			$this->db->trans_begin();
            foreach($data['qty'] as $key=>$qty){
				$setData = array();
                $setData['tableName'] = 'prc_movement';
                $setData['where']['id'] = $data['trans_id'][$key];
                $setData['set']['received_qty'] = 'received_qty, + ' . $qty;
                $setData['set']['qty'] = 'qty, - ' . $qty;
                $this->setValue($setData);
				$movementData = [
					'id'=>'',
					'prc_id' => $data['prc_id'],
					'prc_process_id' => $data['prc_process_id'],
					'process_id' => $data['process_id'],
					'next_process_id' => $data['next_process_id'],
					'send_to' =>1,
					'processor_id' =>0,
					'trans_date' => date("Y-m-d"),
					'qty' =>$qty,
					'wt_nos' => 0,
					'remark' => '',
				];
				$result = $this->store("prc_movement",$movementData);
			}
			
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function deletePRCMovement($param){
		try {
			$this->db->trans_begin();
			$movementData = $this->getProcessMovementList(['id'=>$param['id'],'single_row'=>1]);
			if(!empty($movementData)){
				if(!empty($movementData->next_process_id)){
					$nextProcessData =  $this->sop->getPRCProcessList(['current_process_id'=>$movementData->next_process_id,'prc_id'=>$movementData->prc_id,'work_type'=>$movementData->work_type,'pending_accepted'=>1,'single_row'=>1]); 
					if($movementData->qty > $nextProcessData->pending_accept ){
						return ['status'=>0,'message'=>'You can not delete this movement. This movement accepted by next process'];
					}
				}else{
					$batch_no = $movementData->prc_number;
					if($movementData->prc_type == 3){
						$refJob = $this->getPRC(['id'=>$movementData->ref_job_id]);
						$batch_no = $refJob->prc_number;
					}
					$stockData = $this->itemStock->getItemStockBatchWise(['item_id'=>$movementData->item_id,'location_id'=>$movementData->processor_id,'batch_no'=>$batch_no,'single_row'=>1]); //$this->printQuery();
					if($movementData->qty > $stockData->qty){
						return ['status'=>0,'message'=>'You can not delete this movement. '.$movementData->qty.' > '.$stockData->qty];
					}
					$entryData = $this->transMainModel->getEntryType(['controller'=>'sopDesk']);
					$this->remove('stock_transaction',['main_ref_id'=>$movementData->prc_id,'child_ref_id'=>$movementData->id,'entry_type'=>$entryData->id]);
				}
				
				$result = $this->trash('prc_movement',['id'=>$param['id']]);
			}else{
				$result = ['status'=>0,'message'=>'movement already deleted'];
			}
			

			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function saveAcceptedQty($data){
		try {
			$this->db->trans_begin();
			$result = $this->store('prc_accept_log', $data, 'Acceped');
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	//30-12-2024
	public function getPrcAcceptData($param){
		$queryData = array();          
		$queryData['tableName'] = "prc_accept_log";
		$queryData['select'] = "prc_accept_log.*,prc_master.prc_number,prc_master.mfg_type,prc_master.item_id,item_master.item_name,process_master.process_name,prc_detail.process_ids,prc_process.current_process_id,nxtProcess.process_name AS next_process_name";
		$queryData['leftJoin']['prc_master'] = "prc_accept_log.prc_id = prc_master.id";
		$queryData['leftJoin']['prc_detail'] = "prc_accept_log.prc_id = prc_detail.prc_id";
		$queryData['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
		$queryData['leftJoin']['prc_process'] = "prc_process.id = prc_accept_log.accepted_process_id";
		$queryData['leftJoin']['process_master'] = "process_master.id = prc_process.current_process_id";
		$queryData['leftJoin']['process_master nxtProcess'] = "nxtProcess.id = prc_process.next_process_id";

		if(!empty($param['id'])){ $queryData['where']['prc_accept_log.id'] = $param['id']; }
		if(!empty($param['prc_process_id'])){ $queryData['where']['prc_accept_log.prc_process_id'] = $param['prc_process_id']; }
		if(!empty($param['accepted_process_id'])){ $queryData['where']['prc_accept_log.accepted_process_id'] = $param['accepted_process_id']; }	
		if(!empty($param['trans_date'])){ $queryData['where']['prc_accept_log.trans_date'] = $param['trans_date']; }	
		if(!empty($param['created_by'])){ $queryData['where']['prc_accept_log.created_by'] = $param['created_by']; }	
		if(!empty($param['customWhere'])){ $queryData['customWhere'][] = $param['customWhere']; }

		if(!empty($param['single_row'])){ $result = $this->row($queryData); }
		else{ $result = $this->rows($queryData); }
		
        return $result;  
	}

	public function deletePrcAccept($param){
		try {
			$this->db->trans_begin();
			$acceptData = $this->getPrcAcceptData(['id'=>$param['id'],'single_row'=>1]);
			if(!empty($acceptData)){
				$logData =  $this->sop->getPRCProcessList(['id'=>$acceptData->accepted_process_id,'pending_accepted'=>1,'log_data'=>1,'log_process_by'=>1,'single_row'=>1]); 
				
				$in_qty = (!empty($logData->current_process_id))?(!empty($logData->in_qty)?$logData->in_qty:0):$logData->ok_qty;
				$ok_qty = !empty($logData->ok_qty)?$logData->ok_qty:0;
				$rej_found = !empty($logData->rej_found)?$logData->rej_found:0;
				$rw_qty = !empty($logData->rw_qty)?$logData->rw_qty:0;
				$rej_qty = !empty($logData->rej_qty)?$logData->rej_qty:0;
                $pendingReview = $rej_found - $logData->review_qty;
                $pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview+$logData->ch_qty);
               
				if($acceptData->accepted_qty > $pending_production ){ return ['status'=>0,'message'=>'You can not unaccept this qty'.$acceptData->accepted_qty .'>' .$pending_production ]; }
				if($acceptData->short_qty > 0){
					$prvProcessData =  $this->sop->getPRCProcessList(['id'=>$acceptData->prc_process_id,'log_data'=>1,'movement_data'=>1,'single_row'=>1]); 
					$ok_qty = !empty($prvProcessData->ok_qty)?$prvProcessData->ok_qty:0;
					$movement_qty =!empty($prvProcessData->movement_qty)?$prvProcessData->movement_qty:0;
					$pending_movement = $ok_qty - $movement_qty;
					if($acceptData->short_qty > $pending_movement){ return ['status'=>0,'message'=>'You can not unaccept this qty']; }
				}
				$result = $this->trash('prc_accept_log',['id'=>$param['id']]);
			}else{
				$result = ['status'=>0,'message'=>'Log already deleted'];
			}
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function saveChallanRequest($data){
		try {
			$this->db->trans_begin();
			$result = $this->store('prc_challan_request', $data, 'Challan Request');
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return ['status'=>1,'message'=>'Record Updated Successfully'];
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function deleteChallanRequest($data){
		try {
			$this->db->trans_begin();
			$result = $this->trash('prc_challan_request', ['id'=>$data['id']], 'Challan Request');
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return ['status'=>1,'message'=>'Record Updated Successfully'];
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function getChallanRequestData($param = []){
		$queryData = array();          
		$queryData['tableName'] = "prc_challan_request";
		$queryData['select'] = "prc_challan_request.*,prc_master.prc_date,prc_master.prc_number,prc_master.item_id,process_master.process_name,item_master.item_name,item_master.item_code,outsource.party_id,prc_detail.process_ids";
		$queryData['leftJoin']['prc_master'] = "prc_master.id = prc_challan_request.prc_id";
		$queryData['leftJoin']['process_master'] = "process_master.id = prc_challan_request.process_id";
		$queryData['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
		$queryData['leftJoin']['outsource'] = "outsource.id = prc_challan_request.challan_id";
		$queryData['leftJoin']['prc_detail'] = "prc_detail.prc_id = prc_challan_request.prc_id";

		if(!empty($param['challan_receive'])){
			$queryData['select'] .= ',IFNULL(receiveLog.ok_qty,0) as ok_qty,IFNULL(receiveLog.rej_qty,0) as rej_qty';
			$queryData['leftJoin']['(SELECT sum(qty) as ok_qty,SUM(rej_found) as rej_qty,prc_process_id,ref_trans_id FROM prc_log WHERE is_delete = 0 AND process_by = 3 GROUP BY prc_process_id,ref_trans_id) as receiveLog'] = "receiveLog.ref_trans_id = prc_challan_request.id AND prc_challan_request.prc_process_id = receiveLog.prc_process_id";
		}
		if(!empty($param['id'])){ $queryData['where']['prc_challan_request.id'] = $param['id']; }
		if(!empty($param['challan_id'])){ $queryData['where']['prc_challan_request.challan_id'] = $param['challan_id']; }
		if(!empty($param['prc_process_id'])){ $queryData['where']['prc_challan_request.prc_process_id'] = $param['prc_process_id']; }
		if(!empty($param['prc_id'])){ $queryData['where']['prc_challan_request.prc_id'] = $param['prc_id']; }	
		if(!empty($param['trans_date'])){ $queryData['where']['prc_challan_request.trans_date'] = $param['trans_date']; }	
		if(!empty($param['customWhere'])){ $queryData['customWhere'][] = $param['customWhere']; }
		if(!empty($param['pending_challan'])){ $queryData['where']['prc_challan_request.challan_id'] = 0; }
		if(!empty($param['party_id'])){ $queryData['where']['outsource.party_id'] = $param['party_id']; }
		if(!empty($param['single_row'])){ $result = $this->row($queryData); }
		else{ $result = $this->rows($queryData); }
		
        return $result; 
	}

	public function getPRC($param){
		$data['tableName'] = 'prc_master';
		$data['select'] = 'prc_master.*,prc_detail.remark,prc_detail.process_ids,prc_detail.id as prc_detail_id,prc_detail.cutting_length,prc_detail.cut_weight,prc_detail.cutting_dia,item_master.item_name';
		$data['leftJoin']['prc_detail'] = 'prc_detail.prc_id = prc_master.id';
		$data['leftJoin']['item_master'] = 'item_master.id = prc_master.item_id';
		$data['where']['prc_master.id'] = $param['id'];
		return $this->row($data);
	}

	public function clearPrcData($data){
		try {
			$this->db->trans_begin();
			$this->trash('prc_log',['prc_id'=>$data['id']]);
			$this->trash('prc_process',['prc_id'=>$data['id']]);
			$this->trash('prc_bom',['prc_id'=>$data['id']]);
			$result = $this->store('prc_master',['id'=>$data['id'],'status'=>1]);

			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function getMaterialIssueData($param){
        $issueEntry= $this->transMainModel->getEntryType(['controller'=>'store/issueRequisition']);
		$data['tableName'] = "stock_transaction";
		$data['select'] = 'SUM(stock_transaction.qty) as issue_qty,stock_transaction.item_id, GROUP_CONCAT(DISTINCT party_master.party_name) as supplier_name, GROUP_CONCAT(DISTINCT stock_transaction.batch_no) as batch_no,prc_master.id as prc_id,item_kit.qty,item_kit.process_id,item_kit.group_name,item_master.item_name,prc_master.prc_number,item_master.grade_id,scrapTrans.scrap_qty,(IFNULL(scrapTrans.used_scrap,0) + IFNULL(byPrdTrans.used_by_prd,0)) AS used_scrap,GROUP_CONCAT(DISTINCT batch_history.heat_no) as heat_no,GROUP_CONCAT(DISTINCT stock_transaction.heat_no) as stock_trans_heat,item_master.uom,item_master.category_id';
		
		$data['join']['prc_master'] = 'prc_master.id= stock_transaction.child_ref_id';
		$data['join']['prc_detail'] = 'prc_master.id= prc_detail.prc_id'; 

		$data['leftJoin']['(SELECT item_id,ref_item_id,process_id,qty,group_name FROM item_kit GROUP BY item_kit.item_id,item_kit.ref_item_id,item_kit.process_id )item_kit'] = 'item_kit.item_id= prc_master.item_id AND item_kit.ref_item_id = stock_transaction.item_id AND FIND_IN_SET(item_kit.process_id,prc_detail.process_ids) > 0'; 
		
		$data['leftJoin']['(SELECT heat_no,party_id,batch_no,item_id FROM batch_history WHERE is_delete = 0 GROUP BY batch_no) as batch_history '] = " batch_history.batch_no = stock_transaction.batch_no AND stock_transaction.item_id = batch_history.item_id";
		$data['leftJoin']['party_master'] = 'batch_history.party_id = party_master.id';
		$data['leftJoin']['item_master'] = 'item_master.id = stock_transaction.item_id';

		//End pc Scrap return Join
        $data['leftJoin']['(SELECT SUM(CASE WHEN item_master.stock_type = 2 THEN stock_transaction.qty ELSE 0 END) as scrap_qty,SUM(CASE WHEN item_master.stock_type = 1 THEN stock_transaction.qty ELSE 0 END) as used_scrap,main_ref_id,ref_no FROM stock_transaction LEFT JOIN item_master ON item_master.id = stock_transaction.item_id WHERE entry_type = 1002 AND stock_transaction.is_delete = 0 GROUP BY main_ref_id,ref_no) scrapTrans'] = 'scrapTrans.main_ref_id = stock_transaction.child_ref_id AND scrapTrans.ref_no = stock_transaction.item_id';
		//End Pc By Product
		$data['leftJoin']['(SELECT SUM(stock_transaction.size) as used_by_prd,main_ref_id,ref_no FROM stock_transaction  WHERE entry_type = 1003 AND stock_transaction.is_delete = 0 GROUP BY main_ref_id,ref_no) byPrdTrans'] = 'byPrdTrans.main_ref_id = stock_transaction.child_ref_id AND byPrdTrans.ref_no = stock_transaction.item_id';
		if(!empty($param['production_data'])){
			$customWhere = (!empty($param['prc_id']))?' AND prc_id ="'.$param['prc_id'].'"':'';
			
			$data['select'] .= ",IFNULL(prcLog.production_qty,0) as production_qty,(CASE WHEN item_kit.group_name = 'RM GROUP' THEN IFNULL(prcLog.used_material,0) ELSE (IFNULL(prcLog.production_qty,0) * item_kit.qty) END) as used_material";
			
			$data['leftJoin']['(SELECT SUM(qty+rej_found) as production_qty, SUM((qty+rej_found)*wt_nos) as used_material,prc_id,process_id FROM prc_log WHERE  is_delete = 0 '.$customWhere.' GROUP BY prc_id,process_id) prcLog'] = "prc_master.id = prcLog.prc_id AND item_kit.process_id = prcLog.process_id";
		}

		if(!empty($param['return_data'])){
			$customWhere = (!empty($param['prc_id']))?' AND child_ref_id ="'.$param['prc_id'].'"':'';
			$customWhere = (!empty($param['item_id']))?' AND item_id ="'.$param['item_id'].'"':'';
			
			$data['select'] .= ",IFNULL(returnTrans.return_qty,0) as return_qty";
			$data['leftJoin']['(SELECT SUM(qty) as return_qty, child_ref_id,item_id
								FROM stock_transaction
								WHERE stock_transaction.is_delete=0 AND stock_transaction.entry_type=1001 '.$customWhere.'
								GROUP BY stock_transaction.child_ref_id,stock_transaction.item_id) returnTrans']="returnTrans.child_ref_id = stock_transaction.child_ref_id AND stock_transaction.item_id = returnTrans.item_id";
		}

		$data['where']['stock_transaction.entry_type']  = $issueEntry->id;
		$data['where']['stock_transaction.child_ref_id'] = $param['prc_id'];
		if(!empty($param['item_id'])){ $data['where']['stock_transaction.item_id'] = $param['item_id']; }
		if(!empty($param['group_name'])){ $data['where']['item_kit.group_name'] = $param['group_name']; }
		if(!empty($param['group_by'])){
			$data['group_by'][] = $param['group_by'];
		}else{
			$data['group_by'][] = 'stock_transaction.item_id';
		}
		
		$data['order_by']['stock_transaction.created_at'] = 'ASC';
		$data['order_by']['item_kit.group_name'] = 'ASC';
		if(!empty($param['single_row'])){
			$result = $this->row($data);
		}else{
			$result = $this->rows($data);
		}
		// $this->printQuery();
		return $result;
	}
	
	public function getBatchData($param = []){
        $issueEntry= $this->transMainModel->getEntryType(['controller'=>'store/issueRequisition']);
    	$data['tableName'] = "stock_transaction";
		$data['select'] = 'SUM(stock_transaction.qty) AS issue_qty,stock_transaction.batch_no,(CASE WHEN prc_master.mfg_type = "Forging" THEN batch_history.heat_no ELSE stock_transaction.heat_no END) AS heat_no,stock_transaction.item_id,prc_master.id as prc_id,item_kit.qty,item_kit.process_id,item_kit.group_name,item_master.item_name,item_master.uom,prc_master.prc_number,process_master.process_name';
		$data['join']['prc_master'] = 'prc_master.id= stock_transaction.child_ref_id';
		$data['join']['prc_detail'] = 'prc_master.id= prc_detail.prc_id'; 
		$data['leftJoin']['item_kit'] = 'item_kit.item_id= prc_master.item_id AND item_kit.ref_item_id = stock_transaction.item_id AND item_kit.is_delete=0 AND FIND_IN_SET(item_kit.process_id,prc_detail.process_ids) > 0'; 
		$data['leftJoin']['(SELECT heat_no,party_id,batch_no,item_id FROM batch_history WHERE is_delete = 0 GROUP BY item_id,batch_no) as batch_history '] = " batch_history.batch_no = stock_transaction.batch_no AND stock_transaction.item_id = batch_history.item_id";
		$data['leftJoin']['process_master'] = 'process_master.id = item_kit.process_id';
		$data['leftJoin']['item_master'] = 'item_master.id = stock_transaction.item_id';
		$data['where']['stock_transaction.entry_type']  = $issueEntry->id;
		$data['where']['stock_transaction.child_ref_id'] = $param['prc_id'];
		
		if(!empty($param['id'])){
		    $data['select'] .= ',GROUP_CONCAT(DISTINCT stock_transaction.ref_no) as ref_no,stock_transaction.ref_date';
		    $data['where']['stock_transaction.main_ref_id'] = $param['id'];
		}
		
    	if(!empty($param['item_id'])){ $data['where']['stock_transaction.item_id'] = $param['item_id']; }
		if(!empty($param['group_name'])){ $data['where']['item_kit.group_name'] = $param['group_name']; }
		
		$data['group_by'][]='batch_no';
		if(!empty($param['single_row'])){
			$result = $this->row($data);
		}else{
			$result = $this->rows($data);
		}
        
        return $result;
    }

	public function savePrcMaterial($data){
		try {
			$this->db->trans_begin();
			// print_r($data);exit;
			foreach($data['item_id'] as $key=>$item_id){
				$bomData = [
					'id'=>$data['id'][$key],
					'prc_id'=>$data['prc_id'],
					'item_id'=>$data['item_id'][$key],
					'ppc_qty'=>$data['ppc_qty'][$key],
					'process_id'=>$data['process_id'][$key],
					'bom_group'=>$data['bom_group'][$key],
					'multi_heat'=>'Yes',
					'batch_no'=>'',
				];
				$result = $this->store('prc_bom',$bomData);
			}

			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function checkIssueMaterialForPrc($data){
		try {
			$this->db->trans_begin();
			$prcData = $this->getPRC(['id'=>$data['prc_id']]);
			$prevLogData = $this->sop->getProcessLogList(['prc_id'=>$data['prc_id'],'process_id'=>$data['process_id'],'rejection_review_data'=>1,'log_used_material'=>1,'single_row'=>1]);
			if($prcData->mfg_type == 'Forging'){
				
				// $kitData = $this->item->getProductKitData(['item_id'=>$prcData->item_id,'process_id'=>$data['process_id']]);


				// if(!empty($kitData)){
				//     $groupedkit = array_reduce($kitData, function($group, $kit) { $group[$kit->group_name][] = $kit;  return $group; }, []);
				// }else{
				// 	$kitData = $this->item->getProductKitData(['item_id'=>$prcData->item_id,'process_id'=>$data['process_id'],'is_delete'=>'all','group_by'=>'item_kit.item_id,item_kit.ref_item_id,item_kit.process_id']);
				// 	$groupedkit = array_reduce($kitData, function($group, $kit) { $group[$kit->group_name][] = $kit;  return $group; }, []);
				// }

                $kitData = $this->item->getProductKitData(['item_id'=>$prcData->item_id,'process_id'=>$data['process_id'],'is_delete'=>'all','group_by'=>'item_kit.item_id,item_kit.ref_item_id,item_kit.process_id']);
				$groupedkit = array_reduce($kitData, function($group, $kit) { $group[$kit->group_name][] = $kit;  return $group; }, []);
				if(!empty($groupedkit)){
    				foreach ($groupedkit as $group => $bomData){
    					/** FOR RM GROUP */
    					if($group == 'RM GROUP'){
    						$reqQty = ($data['production_qty'] * $data['wt_nos']) +  (!empty($prevLogData->used_material) ? $prevLogData->used_material:0);
    						$issue_qty = 0; 
    						foreach($bomData as $row){
								
								$pram = ['prc_id'=>$data['prc_id'],'item_id'=> $row->ref_item_id,'entry_type'=>'135,1001','single_row'=>1,'return_data'=>1];
								$issueData = $this->sop->getMaterialIssueData($pram);
    							
    							$bqty = 0;
    							$pending_issue = $reqQty - $issue_qty;
    							if($pending_issue > 0 && !empty($issueData->issue_qty)){
    								$issueData->scrap_qty  = ($issueData->category_id  != 55)?$issueData->scrap_qty :0;
    								$issueData->qty = ($issueData->issue_qty - ($issueData->scrap_qty + $issueData->return_qty));
    								// print_r("->".$issueData->qty );
    								$bqty = ($issueData->qty > $pending_issue)?$pending_issue:$issueData->qty; 
    								$issue_qty += round($bqty,3);
    							}
    						}
    						if(round($reqQty,0) > round($issue_qty,0)){
    							return ['status'=>0,'message'=>'Material Not Available For Bom Group - '.$group.' - '.round($reqQty,0).' > '.round($issue_qty,0)];
    						}
    					}else{ /** FOR OTHER BOM ITEM */
    						$minusQty = 0;$pending= 0;
    						foreach($bomData as $row){
    							$mQty = 0;  $location_id ="";
    							if($prcData->item_id == $row->ref_item_id){$location_id = $this->CUT_STORE->id;}
    							$pram = ['child_ref_id'=>$data['prc_id'],'item_id'=> $row->ref_item_id,'entry_type'=>'135,1001','location_id'=>$location_id,'single_row'=>1];
    							$issueData = $this->itemStock->getItemStockBatchWise($pram);
    							if(!empty($issueData)){
    								$mtQty = round(abs($issueData->qty),3)/ $row->qty;
    								$minusQty += floor($mtQty);
    							}
    							$pending =( $data['production_qty'] +  (!empty($prevLogData->production_qty)?$prevLogData->production_qty:0)) - $minusQty;
    						}
    						
    						if($pending > 0){
    							return ['status'=>0,'message'=>'Material Not Available For Bom Group - '.$group.' - '.$pending];
    						}
    					}
    				}
				}
			}else{
				$prcProcess = explode(",",$prcData->process_ids);
				
				if($data['process_id'] == $prcProcess[0]){
					
					$batchWise = $this->itemStock->getItemStockBatchWise(['child_ref_id'=>$data['prc_id'],'item_id'=> $prcData->item_id,'entry_type'=>'135,1001','location_id'=>$this->MACHINING_STORE->id,'group_by'=>'batch_no']);
					
					if(empty($batchWise)){
						return ['status'=>0,'message'=>'Material Not Available...'];
					}elseif(count($batchWise) > 1){
						return ['status'=>0,'message'=>'Multiple batch Found'];
					}else{
						$pram = ['child_ref_id'=>$data['prc_id'],'item_id'=> $prcData->item_id,'entry_type'=>'135,1001','location_id'=>$this->MACHINING_STORE->id,'single_row'=>1];
						$issueData = $this->itemStock->getItemStockBatchWise($pram);
						$issueData = $batchWise[0];
						$reqQty =( $data['production_qty'] +  (!empty($prevLogData->production_qty)?$prevLogData->production_qty:0));
						if($reqQty > abs($issueData->qty)){
							return ['status'=>0,'message'=>'Material Not Available...  '.$reqQty.' > '.$issueData->qty];
						}
					}
					
					
					
				}
			}
			
			
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return ['status'=>1];
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function storeReturnedMaterial($data){
		try {
			$this->db->trans_begin();
			
			$batchData = $this->gateInward->getBatchWiseItemList(['batch_no'=>$data['batch_no'],'item_id'=>$data['item_id'],]);
			$stockData = [
                'id'=>'',
                'entry_type'=>1001,
                'ref_date'=>date("Y-m-d"),
                'ref_no'=>$data['prc_number'],
                'main_ref_id'=>$data['prc_bom_id'],
                'child_ref_id'=>$data['prc_id'],
                'location_id '=>$data['location_id'],
                'batch_no'=>$data['batch_no'],
                'heat_no'=>$batchData->heat_no,
                'item_id'=>$data['item_id'],
                'p_or_m'=>1,
                'qty'=>$data['qty'],
                'remark'=>$data['remark'],
                'created_by'=>$this->loginId,
                'created_at' => date("Y-m-d H:i:s")
            ];
            $result = $this->store("stock_transaction",$stockData);

			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}


	public function deleteReturn($id){
		try {
			$this->db->trans_begin();
			$returnData = $this->itemStock->getStockTrans(['id'=>$id]);
			$stock = $this->itemStock->getItemStockBatchWise(['location_id'=>$returnData->location_id,'batch_no'=>$returnData->batch_no,'item_id'=> $returnData->item_id,'single_row'=>1]);
			if($returnData->qty > $stock->qty){ 
				return ['status'=>0,'message'=>'You can not delete this record']; 
			}

			$result = $this->remove('stock_transaction',['id'=>$id]);

			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	/*** Cutting PRC DATA */
		public function getCuttingDTRows($data){
			$data['tableName'] = "prc_master";
			
			$data['select'] = "prc_master.id, prc_master.prc_number,prc_master.item_id, DATE_FORMAT(prc_master.prc_date,'%d-%m-%Y') as prc_date, DATE_FORMAT(prc_master.target_date,'%d-%m-%Y') as target_date, prc_master.status, prc_master.prc_qty";
			$data['select'] .= ", IFNULL(im.item_name,'') as item_name, IFNULL(unit_master.unit_name,'') as uom, IFNULL(pd.remark,'') as job_instruction,pd.cutting_length,pd.cutting_dia,pd.cut_weight";
			
			$data['leftJoin']['item_master im'] = "im.id = prc_master.item_id";
			$data['leftJoin']['unit_master'] = "unit_master.id = im.unit_id";
			$data['leftJoin']['prc_detail pd'] = "pd.prc_id = prc_master.id";
			$data['where']['prc_master.prc_type'] = 2;
			if(in_array($data['status'],[1,4,5])){
				if(!empty($data['status'])){ $data['where_in']['prc_master.status'] = $data['status']; }
			}else{
				$data['select'] .= ',IFNULL(prc_log.production_qty,0) as production_qty';
				$data['leftJoin']['(SELECT SUM(qty) as production_qty,prc_id FROM prc_log WHERE  is_delete = 0  GROUP BY prc_id) prc_log'] = "prc_master.id = prc_log.prc_id";
				if($data['status'] == 2){
					$data['having'][] = "(prc_master.prc_qty - production_qty) > 0";
				}elseif($data['status'] == 3){
					$data['having'][] = "(prc_master.prc_qty - production_qty) <= 0";
				}
				$data['where_in']['prc_master.status'] = 2;
			}
			
			$data['order_by']['prc_master.id'] = 'DESC';
			
			$data['searchCol'][] = "";
			$data['searchCol'][] = "";
			$data['searchCol'][] = "prc_master.prc_number";
			$data['searchCol'][] = "DATE_FORMAT(prc_master.prc_date,'%d-%m-%Y')";
			$data['searchCol'][] = "CONCAT(im.item_code,' ',im.item_name)";
			$data['searchCol'][] = "prc_master.prc_qty";
			$data['searchCol'][] = "pd.cutting_length";
			$data['searchCol'][] = "pd.cutting_dia";
			$data['searchCol'][] = "pd.cut_weight";
			$data['searchCol'][] = "pd.remark";
	
			$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
			if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
			$result = $this->pagingRows($data);
			return $result;
		}

		public function getCuttingPrcData($param = []){
			$data['tableName'] = 'prc_master';
			$data['select'] = 'prc_master.*,prc_detail.remark,prc_detail.process_ids,prc_detail.id as prc_detail_id';
			$data['leftJoin']['prc_detail'] = 'prc_detail.prc_id = prc_master.id';
			if(!empty($param['id'])){ $data['where']['prc_master.id'] = $param['id']; }
			if(!empty($param['production_data'])){
				$data['select'] .= ',IFNULL(prc_log.production_qty,0) as production_qty';
				$data['leftJoin']['(SELECT SUM(qty) as production_qty,prc_id FROM prc_log WHERE  is_delete = 0  GROUP BY prc_id) prc_log'] = "prc_master.id = prc_log.prc_id";
			}
			if(!empty($param['single_row'])){
				return $this->row($data);
			}else{
				return $this->rows($data);
			}
			
		}

		public function saveCuttingLog($param){
			try {
				$this->db->trans_begin();
			  
				/*** Check Required Material For Production */
				$prcData = $this->getCuttingPrcData(['id'=>$param['prc_id'],'production_data'=>1,'single_row'=>1]); 
				$total_qty =$prcData->production_qty + $param['qty'];
				$mtResult = $this->checkIssueMaterialForPrc(['prc_id'=>$param['prc_id'],'process_id'=>'','check_qty'=>$total_qty]);
				if($mtResult['status'] == 0){
					return $mtResult;
				}elseif(!empty($mtResult['bomUpdate'])){
					foreach($mtResult['bomUpdate'] as $bom){
						$this->store('prc_bom',$bom);
					}
				}

				/** Save prc_log */
				$logDetail = (!empty($param['logDetail']))?$param['logDetail']:[]; unset($param['logDetail']);
				$result = $this->store('prc_log', $param, 'PRC Log');
				if(!empty($logDetail)){
					$logDetail['log_id'] = $result['id'];	
					$this->store('prc_log_detail', $logDetail, 'PRC Log Detail');
				}

				/** Save Stock */
				$entryData = $this->transMainModel->getEntryType(['controller'=>'sopDesk/cuttingIndex']);
				$stockData = [
					'id' => "",
					'entry_type' => $entryData->id,
					'ref_date' => $param['trans_date'],
					'ref_no' => $prcData->prc_number,
					'main_ref_id' => $param['prc_id'],
					'child_ref_id' => $result['id'],
					'location_id' => $this->CUT_STORE->id,
					'batch_no' =>$prcData->prc_number,
					'item_id' => $prcData->item_id,
					'p_or_m' => 1,
					'qty' => $param['qty'],
				];

				$this->store('stock_transaction',$stockData);
				 
				if($this->db->trans_status() !== FALSE) :
					$this->db->trans_commit();
					return $result;
				endif;
			}catch (\Exception $e) {
				$this->db->trans_rollback();
				return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
			}
		}

		public function deleteCuttingLog($param){
			try {
				$this->db->trans_begin();
	
				$logData = $this->getProcessLogList(['id'=>$param['id'],'single_row'=>1]);
				if(!empty($logData)){
					$stockData = $this->itemStock->getItemStockBatchWise(['item_id'=>$logData->item_id,'location_id'=>$this->CUT_STORE->id,'batch_no'=>$logData->prc_number,'single_row'=>1]);
					if($logData->qty > $stockData->qty){
						return ['status'=>0,'message'=>'You can not delete this log'];
					}
					$entryData = $this->transMainModel->getEntryType(['controller'=>'sopDesk/cuttingIndex']);
					$this->remove('stock_transaction',['main_ref_id'=>$logData->prc_id,'child_ref_id'=>$logData->id,'entry_type'=>$entryData->id]);
					$this->trash('prc_log_detail',['log_id'=>$param['id']]);
					$result = $this->trash('prc_log',['id'=>$param['id']]);
					$bomData = $this->getPrcBomData(['prc_id'=>$logData->prc_id,'process_id'=>'','production_data'=>1,'single_row'=>1]);
						if(!empty($bomData->item_id) && $bomData->production_qty == 0){
							$this->edit("prc_bom",['prc_id'=>$logData->prc_id,'process_id'=>$logData->process_id],['batch_no'=>'']);
						}
				}else{
					$result = ['status'=>0,'message'=>'Log already deleted'];
				}
	
				if($this->db->trans_status() !== FALSE) :
					$this->db->trans_commit();
					return $result;
				endif;
			}catch (\Exception $e) {
				$this->db->trans_rollback();
				return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
			}
		}
	/*** END Cutting */
	
	/* End Piece Return */
	public function saveEndPcsReturn($data){
		try {
			$this->db->trans_begin();
			
			$prcData = $this->getPRCDetail(['id'=>$data['prc_id']]);
			
			$qty = (($data['entry_type'] == 1003)?$data['qty_pcs']:$data['qty']);
			$size = (($data['entry_type'] == 1003)?$data['qty']:"");
			$location_id = (($data['entry_type'] == 1003)?$data['location_id']:$this->SCRAP_STORE->id);
			$stockData = [
                'id' => '',
                'entry_type' => $data['entry_type'],
                'ref_date' => date("Y-m-d"),
                'ref_no' => $data['rm_item_id'],
                'main_ref_id' => $data['prc_id'],
                'child_ref_id' => $data['prc_process_id'],
                'location_id' => $location_id,
                'batch_no' => $prcData->prc_number,
                'item_id' => $data['scrap_item_id'],
                'p_or_m' => 1,
                'qty' => $qty,
                'size' => $size,
                'created_by' => $this->loginId,
                'created_at' => date("Y-m-d H:i:s")
            ];
            $result = $this->store("stock_transaction",$stockData);

			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function getEndPcsStock($data){
        $queryData['tableName'] = 'stock_transaction';
        $queryData['select'] = "stock_transaction.*,rm.item_name as rm_item_name,scrap.item_name as scrap_item,SUM(CASE WHEN stock_transaction.entry_type = 1002 THEN stock_transaction.qty ELSE stock_transaction.size END) as stock_qty";
        $queryData['leftJoin']['item_master rm'] = "stock_transaction.ref_no = rm.id";
        $queryData['leftJoin']['item_master scrap'] = "stock_transaction.item_id = scrap.id";

        if(!empty($data['entry_type'])):
            $queryData['where_in']['stock_transaction.entry_type'] = $data['entry_type'];
        endif;

        if(!empty($data['main_ref_id'])):
            $queryData['where']['stock_transaction.main_ref_id'] = $data['main_ref_id'];
        endif;

		if(!empty($data['child_ref_id'])):
            $queryData['where']['stock_transaction.child_ref_id'] = $data['child_ref_id'];
        endif;

		if(!empty($data['group_by'])):
            $queryData['group_by'][] = $data['group_by'];
        endif;

		if(!empty($data['single_row'])):
			return $this->row($queryData);
		else:
			return $this->rows($queryData);
		endif;
	}

	public function deleteEndPcsReturn($id){
		try {
			$this->db->trans_begin();

			$result = $this->remove('stock_transaction',['id'=>$id]);

			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}
	
	
	/* Update PRC Qty */
	public function getPRCProcess($param){
		$data['tableName'] = "prc_process";
		$data['select'] = "prc_process.*";
		if(!empty($param['prc_id'])) { $data['where']['prc_id'] = $param['prc_id']; }
		if(isset($param['current_process_id'])) { $data['where']['current_process_id'] = $param['current_process_id']; }
		return $this->row($data);
	}

	public function savePrcQty($data){
        try {
            $this->db->trans_begin();

            $operation = ($data['log_type'] == 1) ? '+' : '-';
            $result = $this->store('prc_update', $data, 'PRC Qty');

			
            $prcData = $this->getPRC(['id'=>$data['prc_id']]);
            $firstProcess = explode(",",$prcData->process_ids)[0];
            $prcProcessData = $this->getPRCProcess(['prc_id'=>$data['prc_id'], 'current_process_id'=>$firstProcess]);
			
			/* Update Log Qty */
			if(!empty($prcProcessData->id)){
				$setData = array();
				$setData['tableName'] = 'prc_accept_log';
				$setData['where']['prc_id'] = $data['prc_id'];
				$setData['where']['accepted_process_id'] = $prcProcessData->id;
				$setData['set']['accepted_qty'] = 'accepted_qty,' . $operation . $data['qty'];
				$this->setValue($setData);
			}
            

			/* Update PRC Qty */
            $updateQuery = array();
            $updateQuery['tableName'] = 'prc_master';
            $updateQuery['where']['id'] = $data['prc_id'];
            $updateQuery['set']['prc_qty'] = 'prc_qty,' . $operation . $data['qty'];
            $this->setValue($updateQuery);

			// IF Machining JObs
			if(!empty($prcData->mfg_type) && $prcData->mfg_type == 'Machining' && $data['log_type'] == 1){

				$location_id = [ $this->MACHINING_STORE->id ];
				$batch_no = [$prcData->ref_batch];
				$heat_no = [$prcData->heat_no];
				$batch_qty =[$data['qty']];

				$issueData = [
					'req_id' => '',
					'issue_date' => date('Y-m-d'),
					'item_id' => $prcData->item_id,
					'entry_type' => '135',
					'location_id' => $location_id,
					'batch_no' => $batch_no,
					'heat_no' => $heat_no,
					'batch_qty' => $batch_qty,
					'prc_id' => $data['prc_id'],
					'issued_to' => $this->loginId,
					'created_by' => $this->loginId
				];
				$this->store->saveIssueRequisition($issueData);
			}
            if ($this->db->trans_status() !== FALSE) :
                $this->db->trans_commit();
                return $result;
            endif;
        } catch (\Exception $e) {
            $this->db->trans_rollback();
            return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
        }
    }

	public function getPRCLogData($param){
		$data['tableName'] = "prc_update";
		if(!empty($param['prc_id'])) { $data['where']['prc_id'] = $param['prc_id']; }
		if(!empty($param['id'])) { $data['where']['id'] = $param['id']; }
		
		if(!empty($param['single_row'])):
			return $this->row($data);
		else:
			return $this->rows($data);
		endif;
	}

    public function deletePrcUpdateQty($id){
        try {
            $this->db->trans_begin();

			$logData = $this->getPrcLogData(['id'=>$id,'single_row'=>1]);
            $operation = ($logData->log_type == 1) ? '-' : '+';
            $result = $this->trash('prc_update', ['id' => $id], 'PRC Qty');

            $prcData = $this->getPRC(['id'=>$logData->prc_id]);
            $firstProcess = explode(",",$prcData->process_ids)[0];
            $prcProcessData = $this->getPRCProcess(['prc_id'=>$logData->prc_id, 'current_process_id'=>$firstProcess]);
            
			/* Update Log Qty */
            $setData = array();
            $setData['tableName'] = 'prc_accept_log';
            $setData['where']['prc_id'] = $logData->prc_id;
            $setData['where']['accepted_process_id'] = $prcProcessData->id;
            $setData['set']['accepted_qty'] = 'accepted_qty,' . $operation . $logData->qty;
            $this->setValue($setData);


			/* Update PRC Qty */
            $updateQuery = array();
            $updateQuery['tableName'] = 'prc_master';
            $updateQuery['where']['id'] = $logData->prc_id;
            $updateQuery['set']['prc_qty'] = 'prc_qty,' . $operation . $logData->qty;
            $this->setValue($updateQuery);			

            if ($this->db->trans_status() !== FALSE) :
                $this->db->trans_commit();
                return $result;
            endif;
        } catch (\Exception $e) {
            $this->db->trans_rollback();
            return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
        }
    }
    
    public function changePrcStatus($data){
        try{
            $this->db->trans_begin();

            $result = $this->store('prc_master',$data,'PRC');
            if($data['status'] == 5){
                $this->edit('store_request',['prc_id'=>$data['id']],['status'=>2]);
            }
            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

	public function getMachiningStockData($param){
		$queryData['tableName'] = 'stock_transaction';
        $queryData['select'] = "stock_transaction.id,stock_transaction.ref_date,stock_transaction.item_id, item_master.item_code, item_master.item_name, SUM(stock_transaction.qty * stock_transaction.p_or_m) as qty, stock_transaction.unique_id, stock_transaction.batch_no,stock_transaction.heat_no ";
        $queryData['leftJoin']['item_master'] = "stock_transaction.item_id = item_master.id";

		$queryData['where']['stock_transaction.location_id'] = $this->MACHINING_STORE->id;

		$queryData['having'][] = 'SUM(stock_transaction.qty * stock_transaction.p_or_m) > 0';
		$queryData['group_by'][] = 'stock_transaction.item_id,stock_transaction.batch_no';
		if(!empty($param['skey'])){
			$queryData['like']['stock_transaction.batch_no'] = str_replace(" ", "%", $param['skey']);
			$queryData['like']['stock_transaction.heat_no'] = str_replace(" ", "%", $param['skey']);
			$queryData['like']['stock_transaction.ref_date'] = str_replace(" ", "%", $param['skey']);
			$queryData['like']['item_master.item_name'] = str_replace(" ", "%", $param['skey']);
			$queryData['like']['item_master.item_code'] = str_replace(" ", "%", $param['skey']);
        }

        if(!empty($param['limit'])){ $queryData['limit'] = $param['limit']; }
        
		if(isset($param['start'])){ $queryData['start'] = $param['start']; }
		
		if(!empty($param['length'])){ $queryData['length'] = $param['length']; }
		$stockData = $this->rows($queryData); //$this->printQuery();
        return $stockData;
	}
	
	/* Update PRC Process Sequence & Remove Process */
	public function saveJobProcessSequence($data){
        try {
            $this->db->trans_begin();

            $queryData = array();
            $queryData['tableName'] = 'prc_process';		
			$queryData['select'] = "prc_process.current_process_id,(IFNULL(prevMovement.move_qty,0)-IFNULL(prc_accept_log.short_qty,0)) as inward_qty,prevProcess.next_process_id";
			$queryData['leftJoin']['prc_process prevProcess'] = "prevProcess.next_process_id = prc_process.current_process_id AND prc_process.work_type  =  prevProcess.work_type AND prevProcess.is_delete = 0 AND prevProcess.prc_id = prc_process.prc_id";
			$queryData['leftJoin']['(SELECT SUM(prc_accept_log.short_qty) as short_qty,accepted_process_id FROM prc_accept_log WHERE prc_accept_log.is_delete=0 GROUP BY accepted_process_id) prc_accept_log']="prc_accept_log.accepted_process_id = prc_process.id";
			$queryData['leftJoin']['(SELECT SUM(prc_movement.qty) as move_qty,prc_process_id FROM prc_movement WHERE prc_movement.is_delete=0 AND send_to = 1 GROUP BY prc_process_id) prevMovement']="prevMovement.prc_process_id = prevProcess.id";
            $queryData['where']['prc_process.prc_id'] = $data['prc_id'];
            $queryData['where']['prc_process.current_process_id >'] = 0;
            $queryData['having'][] = 'inward_qty > 0 OR prevProcess.next_process_id IS NULL';
            $rnAprvData = $this->rows($queryData);

            $rnstages ='';
            if(!empty($rnAprvData)){
                $rnstages = implode(",",array_column($rnAprvData,'current_process_id'));
            }
            $newProcesses ='';
            if (!empty($rnstages) && !empty($data['current_process_id'])) {
                $newProcesses = $rnstages . ',' . implode(",",$data['current_process_id']);
            }elseif(!empty($data['current_process_id'])){
                $newProcesses = implode(",",$data['current_process_id']);
            }else{
                $newProcesses = $rnstages;
            }
            /*** Set job_card - process */
            
            $result = $this->edit('prc_detail', ['prc_id' => $data['prc_id']], ['process_ids' => $newProcesses], 'PRC');

            $queryData = array();
            $queryData['tableName'] = 'prc_process';
            $queryData['where']['prc_id'] = $data['prc_id'];
            $prcProcessData = $this->rows($queryData);
            if (!empty($prcProcessData)) :
                $rnStage = (!empty($rnstages)) ? explode(",",$rnstages) : [0];
                $newProcessesStage = !empty($data['current_process_id'])?$data['current_process_id']:"";

                $countRnStage = count($rnStage);
                $i = 0; $j = 0; $previusSatge = 0; $previusSatgeId = 0;

                $newCount = count(explode(",",$newProcesses)); $rowCount = 1;
                foreach ($prcProcessData as $row) :
                    /** SetNew in out Process As per Sequence  */
                    if ($i > $previusSatge) :
                        $this->store('prc_process', ['id' => $row->id, 'current_process_id' => $previusSatgeId, 'next_process_id' => (isset($newProcessesStage[$i])) ? $newProcessesStage[$i] : 0]);
                        $previusSatgeId = (isset($newProcessesStage[$i])) ? $newProcessesStage[$i] : 0;
                        $previusSatge = $i;
                        $i++;
                    endif;
                    /** If Process is Running Process Last Process then Updates New first process AS Running Last Process's Out Process */
                    if ($row->current_process_id == $rnStage[($countRnStage - 1)]) :
                        $newProcessesStage[$i] = !empty($newProcessesStage[$i])?$newProcessesStage[$i]:0;
                        $this->store('prc_process', ['id' => $row->id, 'next_process_id' => $newProcessesStage[$i]]);
                        $previusSatgeId = $newProcessesStage[$i];
                        $previusSatge = $i;
                        $i++;
                    endif;

                    /** Remove deleted Process row */
                    if($rowCount > $newCount){
                        $this->trash('prc_process',['id'=>$row->id]);
                    }
                    $rowCount++;
                endforeach;
            endif;

            if ($this->db->trans_status() !== FALSE) :
                $this->db->trans_commit();
                return $result;
            endif;
        } catch (\Exception $e) {
            $this->db->trans_rollback();
            return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
        }
    }

	public function addJobStage($data){
        try {
            $this->db->trans_begin();

            if (!empty($data['prc_id'])) :
                $prcData = $this->getPRC(['id'=>$data['prc_id']]);
                $process = explode(",", $prcData->process_ids);
                $process[] = $data['process_id'];
                $newProcesses = implode(',', $process);

                $result = $this->edit('prc_detail', ['id' => $data['prc_id']], ['process_ids' => $newProcesses], 'PRC');

                $queryData = array();
                $queryData['tableName'] = 'prc_process';
                $queryData['where']['prc_id'] = $data['prc_id'];
                $queryData['order_by']['id'] = "DESC";
                $prcProcessData = $this->row($queryData);

                if (!empty($prcProcessData)) :
                    $this->store('prc_process', ['id' => $prcProcessData->id, 'next_process_id' => $data['process_id']]);
                    $processResult = $this->store('prc_process', ['id' => "", 'prc_id' => $data['prc_id'],  'current_process_id' => $data['process_id'], 'next_process_id' => 0, 'created_by' => $this->loginId]);
                endif;
            endif;

            if ($this->db->trans_status() !== FALSE) :
                $this->db->trans_commit();
                return $result;
            endif;
        } catch (\Exception $e) {
            $this->db->trans_rollback();
            return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
        }
    }
    
    public function getIssueDetailData($param = []){
        $issueEntry = $this->transMainModel->getEntryType(['controller'=>'store/issueRequisition']);
    	$data['tableName'] = "stock_transaction";
		$data['where']['stock_transaction.entry_type']  = $issueEntry->id;
    	if(!empty($param['item_id'])){ $data['where']['stock_transaction.item_id'] = $param['item_id']; }
    	if(!empty($param['prc_id'])){ $data['where']['stock_transaction.child_ref_id'] = $param['prc_id']; }
				
        $result = $this->rows($data);
        return $result;
    }

	public function getLastLogDate($data){
		$queryData = array();          
		$queryData['tableName'] = "prc_log";
		$queryData['select'] = 'MAX(created_at) AS last_log_date';
		$queryData['where']['prc_id'] = $data['prc_id'];
		$queryData['where']['prc_process_id'] = $data['prc_process_id'];
		$result = $this->row($queryData);
		return $result;
	}

	public function getLastAcceptDate($data){
		$queryData = array();          
		$queryData['tableName'] = "prc_accept_log";
		$queryData['select'] = 'MAX(created_at) AS last_accept_date';
		$queryData['where']['prc_id'] = $data['prc_id'];
		$queryData['where']['accepted_process_id'] = $data['prc_process_id'];
		$result = $this->row($queryData);
		return $result;
	}

	public function getPrcListFromBom($data){
		$queryData['tableName'] = 'prc_master';
		$queryData['select'] = "prc_master.id,prc_master.prc_number";
		$queryData['leftJoin']['item_kit'] = "item_kit.item_id = prc_master.item_id AND item_kit.is_delete = 0";
		$queryData['where_in']['prc_master.status'] = '1,2';
		if(!empty($data['bom_item_id'])){
			$queryData['where']['item_kit.ref_item_id'] = $data['bom_item_id'];
		}
		return $this->rows($queryData);
	}

	public function saveConversion($param){
		try {
			$this->db->trans_begin();

			$prcData = $this->getPRC(['id'=>$param['prc_id']]);
			if(!in_array($prcData->status,[1,2,3])){
				return ['status'=>0,'message'=>'PRC is not in Inprogress state'];
			}
			
            $result = $this->store('prc_movement', $param, 'PRC Log');
			/** STOCK EFFECT */
			$prcData = $this->getPRCDetail(['id'=>$param['prc_id']]);
			if($prcData->prc_type == 3){
					$refJob = $this->getPRC(['id'=>$prcData->ref_job_id]);
					$batch_no = $refJob->prc_number;
					$materialData = $this->getMaterialIssueData(['prc_id'=>$prcData->ref_job_id,'single_row'=>1]);
					$heat_no = !empty($materialData->heat_no)?$materialData->heat_no:$materialData->stock_trans_heat;
				}else{
					$batch_no = $prcData->prc_number;
					$materialData = $this->getMaterialIssueData(['prc_id'=>$param['prc_id'],'single_row'=>1]);
					$heat_no = !empty($materialData->heat_no)?$materialData->heat_no:$materialData->stock_trans_heat;
				}
			$stockData = [
							'id' => "",
							'entry_type' => 1003,
							'ref_date' => $param['trans_date'],
							'ref_no' => $prcData->prc_number,
							'main_ref_id' => $param['prc_id'],
							'child_ref_id' => $result['id'],
							'location_id' => $this->SEMI_FG_STORE->id,
							'batch_no' =>$batch_no,
							'heat_no' =>$heat_no,
							'item_id' => $param['convert_item'],
							'p_or_m' => 1,
							'qty' => $param['qty'],
							'created_by'=>$this->loginId
						];

			$this->store('stock_transaction',$stockData);
			/* $stockData = [
				'id' => "",
				'trans_type' => 'CON',
				'trans_date' => $param['trans_date'],
				'ref_no' => $prcData->prc_number,
				'main_ref_id' => $param['prc_id'],
				'child_ref_id' => $result['id'],
				'location_id' => $this->SEMI_FG_STORE->id,
				'batch_no' =>$prcData->prc_number,
				'ref_batch' =>$prcData->batch_no, //For TC Purpose Save rm batch no.
				'item_id' => $param['convert_item'],
				'p_or_m' => 1,
				'qty' => $param['qty'],
			];

			$this->store('stock_trans',$stockData);
			$this->changePrcStatus(['prc_id'=>$param['prc_id']]); */
			
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function deleteItemConversion($param){
		try {
			$this->db->trans_begin();
			$movementData = $this->getProcessMovementList(['id'=>$param['id'],'single_row'=>1]);
			$prcData = $this->getPRC(['id'=>$movementData->prc_id]);
			if(!in_array($prcData->status,[2,3])){
				return ['status'=>0,'message'=>'PRC is not in Inprogress state'];
			}
			if(!empty($movementData)){
				$stockData = $this->itemStock->getItemStockBatchWise(['item_id'=>$movementData->convert_item,'location_id'=>$this->SEMI_FG_STORE->id,'batch_no'=>$movementData->prc_number,'single_row'=>1]);
				if($movementData->qty > $stockData->qty){
					return ['status'=>0,'message'=>'You can not delete this movement'];
				}
				
				$this->remove('stock_transaction',['main_ref_id'=>$movementData->prc_id,'child_ref_id'=>$movementData->id,'entry_type'=>1003]);

				$result = $this->trash('prc_movement',['id'=>$param['id']]);
				
			}else{
				$result = ['status'=>0,'message'=>'movement already deleted'];
			}
			

			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}
}
?>