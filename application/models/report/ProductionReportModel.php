<?php 
class ProductionReportModel extends MasterModel{
	private $prcMaster = "prc_master";
	private $prcLog = "prc_log";
	
	public function getPrcRegisterData($data){
        $queryData = array();          
		$queryData['tableName'] = "prc_master";
		
		$queryData['select'] = "prc_master.id, prc_master.prc_number,prc_master.item_id, prc_master.mfg_type, DATE_FORMAT(prc_master.prc_date,'%d-%m-%Y') as prc_date, DATE_FORMAT(prc_master.target_date,'%d-%m-%Y') as target_date, prc_master.status, prc_master.prc_qty,employee_master.emp_name, IFNULL(item_master.item_code,'') as item_code,IFNULL(item_master.item_name,'') as item_name, IFNULL(party_master.party_name,'') as party_name, IFNULL(prc_detail.remark,'') as job_instruction,IFNULL(prc_movement.stored_qty,0) as ok_qty,IFNULL(rejection_log.rej_qty,0) as rej_qty"; 
        
        $queryData['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
        $queryData['leftJoin']['party_master'] = "party_master.id = prc_master.party_id";
        $queryData['leftJoin']['prc_detail'] = "prc_detail.prc_id = prc_master.id";
        $queryData['leftJoin']['employee_master'] = "employee_master.id = prc_master.created_by"; 
		$queryData['where']['prc_master.prc_type'] = 1;
		$queryData['leftJoin']['(SELECT SUM(qty) as rej_qty,prc_id FROM rejection_log WHERE decision_type = 1 AND is_delete = 0 GROUP BY prc_id) rejection_log'] = "prc_master.id = rejection_log.prc_id";
			$queryData['leftJoin']['(SELECT SUM(qty) as stored_qty,prc_id FROM prc_movement WHERE next_process_id = 0 AND is_delete = 0 GROUP BY prc_id) prc_movement'] = "prc_master.id = prc_movement.prc_id";

        $queryData['customWhere'][] = "prc_master.prc_date BETWEEN '".$data['from_date']."' AND '".$data['to_date']."'";

		$queryData['group_by'][] = "prc_master.id";
        $result = $this->rows($queryData);
        return $result;  
    }

	/* OutSOurce Register */
	public function getOutSourceRegister($postData=[]){
		$data['tableName'] = "prc_challan_request";
		$data['select'] = "prc_challan_request.*,outsource.id as out_id,outsource.ch_number,outsource.ch_date,outsource.party_id,prc_master.prc_date,prc_master.prc_number,process_master.process_name,item_master.item_name,party_master.party_name";
		$data['leftJoin']['outsource'] = "outsource.id = prc_challan_request.challan_id";
		$data['leftJoin']['prc_master'] = "prc_master.id = prc_challan_request.prc_id";
		$data['leftJoin']['process_master'] = "process_master.id = prc_challan_request.process_id";
		$data['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
		$data['leftJoin']['party_master'] = "party_master.id = outsource.party_id";

        $data['where']['prc_challan_request.challan_id >'] = 0;
		$data['customWhere'][] = "outsource.ch_date BETWEEN '".$postData['from_date']."' AND '".$postData['to_date']."'";
		$data['where']['outsource.party_id'] = $postData['vendor_id'];
		$result = $this->rows($data);
      
		return $result;
	}

	public function getJobInwardData($postData=[]){
		$data['tableName'] = $this->prcLog;
		$data['where']['prc_log.ref_id'] = $postData['ref_id'];
		$data['where_in']['prc_log.process_by'] = 3;
		$data['order_by']['prc_log.trans_date'] = 'ASC';
		$data['order_by']['prc_log.id'] = 'ASC';
		$result = $this->rows($data);
		return $result;
	}

	/* Rejection Monitoring Report*/
	public function getRejectionMonitoring($data){
		$queryData = array();
		$queryData['tableName'] = "rejection_log";
        $queryData['select'] = "rejection_log.*,prc_log.process_by,prc_log.trans_date,prc_master.prc_number,item_master.item_code, item_master.item_name,process_master.process_name,shift_master.shift_name,employee_master.emp_name,rejection_comment.remark,process.process_name as rejction_stage,party.party_name as vendor_name,IF(prc_log.process_by = 1, machine.item_code,
        IF(prc_log.process_by = 2,department_master.name,
        IF(prc_log.process_by = 3,party_master.party_name,''))) as processor_name";

		$queryData['leftJoin']['prc_log'] = 'prc_log.id = rejection_log.log_id';
		$queryData['leftJoin']['prc_master'] = 'prc_master.id = rejection_log.prc_id';
		$queryData['leftJoin']['item_master'] = 'item_master.id = prc_master.item_id';
		$queryData['leftJoin']['process_master'] = 'prc_log.process_id = process_master.id';
		$queryData['leftJoin']['shift_master'] = 'shift_master.id = prc_log.shift_id';        
        $queryData['leftJoin']['item_master machine'] = "machine.id = prc_log.processor_id";
        $queryData['leftJoin']['department_master'] = "department_master.id = prc_log.processor_id";
        $queryData['leftJoin']['party_master'] = "party_master.id = prc_log.processor_id";
		$queryData['leftJoin']['employee_master'] = 'employee_master.id = prc_log.operator_id';
		$queryData['leftJoin']['rejection_comment'] = 'rejection_comment.id = rejection_log.rr_reason AND rejection_comment.is_delete = 0';
        $queryData['leftJoin']['process_master process'] = 'rejection_log.rr_stage = process.id';
        $queryData['leftJoin']['party_master party'] = "party.id = rejection_log.rr_by";

		$queryData['customWhere'][] = "prc_log.trans_date BETWEEN '" . $data['from_date'] . "' AND '" . $data['to_date'] . "'";
		if (!empty($data['item_id'])) { $queryData['where']['prc_master.item_id'] = $data['item_id']; }
		if (!empty($data['process_id'])) { $queryData['where']['rejection_log.rr_stage'] = $data['process_id']; }
		
		$queryData['where']['rejection_log.decision_type'] = '1';
		$queryData['where']['rejection_comment.type'] = '1';		
		return $this->rows($queryData);
	}

	/* Production Log Sheet Report*/
	public function getProductionLogSheet($postData=[]){
        $queryData = array();          
		$queryData['tableName'] = "prc_log";
		
		$queryData['select'] = "prc_log.*,employee_master.emp_name,shift_master.shift_name,prc_master.item_id,prc_master.prc_number, machine.item_code as machine_code, machine.item_name as machine_name, item_master.item_name, item_master.item_code,process_master.process_name,product_process.cycle_time,rejection_log.rr_reason,rejection_comment.remark as rej_reason,prc_log.qty as ok_qty,prc_log.rej_qty as rej_qty,party_master.party_name"; 

		$queryData['leftJoin']['item_master machine'] = "machine.id = prc_log.processor_id";
		$queryData['leftJoin']['party_master'] = "party_master.id = prc_log.processor_id";
		$queryData['leftJoin']['employee_master'] = "employee_master.id = prc_log.operator_id";
        $queryData['leftJoin']['shift_master'] = "shift_master.id = prc_log.shift_id";
        $queryData['leftJoin']['prc_master'] = "prc_master.id = prc_log.prc_id";
		$queryData['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";
        $queryData['leftJoin']['process_master'] = "process_master.id = prc_log.process_id";
        $queryData['leftJoin']['product_process'] = "product_process.process_id = prc_log.process_id AND product_process.item_id = prc_master.item_id AND product_process.is_delete = 0";
        $queryData['leftJoin']['rejection_log'] = "rejection_log.log_id = prc_log.id";
		$queryData['leftJoin']['rejection_comment'] = "rejection_comment.id = rejection_log.rr_reason";

		if(!empty($postData['process_id'])){ 
			$queryData['where']['prc_log.process_id'] = $postData['process_id'];
		}
		if(!empty($postData['from_date']) && !empty($postData['to_date'])){ 
			$queryData['customWhere'][] = "prc_log.trans_date BETWEEN '".$postData['from_date']."' AND '".$postData['to_date']."'";
		}
        $queryData['where']['prc_log.process_id >'] = 0;

		$result = $this->rows($queryData);
		
        return $result;  
    }
	

    public function getPRCProcessList($param=[]){
        $queryData = array();          
		$queryData['tableName'] = "prc_process";
		$queryData['select'] = "prc_process.*";
		$queryData['select'] .= ", IFNULL(cp.process_name,'') as current_process,prc_master.prc_number,prc_master.prc_qty,prc_master.prc_date,prc_master.item_id,prc_detail.process_ids,item_master.item_name,item_master.item_code,cp.process_type";
        
        $queryData['leftJoin']['process_master cp'] = "cp.id = prc_process.current_process_id";
        $queryData['leftJoin']['prc_master'] = "prc_master.id = prc_process.prc_id";
        $queryData['leftJoin']['prc_detail'] = "prc_detail.prc_id = prc_process.prc_id";
        $queryData['leftJoin']['item_master'] = "item_master.id = prc_master.item_id";

	
		
		/** IF LOG DATA  GET (Total production ok qty, rejection found qty, review qty,challan qty) */
		if(!empty($param['log_data'])){
			$customWh = "";
			if(!empty($param['log_process_by'])){
				$customWh = " AND process_by != 3";
			}
			$queryData['select'] .= ",IFNULL(prcLog.ok_qty,0) as ok_qty, IFNULL(prcLog.rej_qty,0) as rej_qty, IFNULL(prcLog.rw_qty,0) as rw_qty,IFNULL(prcLog.rej_found,0) as rej_found, IFNULL(prc_challan_request.ch_qty,0) as ch_qty,IFNULL(rejection_log.review_qty,0) as review_qty,IFNULL(rw_log.review_rw_qty,0) as review_rw_qty ";

			$queryData['leftJoin']['(SELECT SUM(prc_log.qty) as ok_qty, SUM((prc_log.rej_qty)) as rej_qty, SUM((prc_log.rw_qty)) as rw_qty, SUM(prc_log.rej_found) as rej_found,prc_process_id FROM prc_log WHERE is_delete = 0 '.$customWh.' GROUP BY prc_process_id) prcLog'] =  "prcLog.prc_process_id = prc_process.id";

			$queryData['leftJoin']['(SELECT SUM(prc_challan_request.qty - prc_challan_request.without_process_qty) as ch_qty,prc_process_id FROM prc_challan_request WHERE is_delete = 0 GROUP BY prc_process_id) prc_challan_request'] =  "prc_challan_request.prc_process_id = prc_process.id";

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
		
		
		if(!empty($param['item_id'])){ $queryData['where']['prc_master.item_id'] = $param['item_id']; }
		if(!empty($param['prc_id'])){ $queryData['where_in']['prc_master.id'] = $param['prc_id']; }
		
		if(!empty($param['customWhere'])){ $queryData['customWhere'][] = $param['customWhere']; }
// 		$queryData['where_in']['prc_master.status'] = '1,2';
		
		$queryData['group_by'][]="prc_process.id";

		if(!empty($param['single_row'])){ $result = $this->row($queryData); }
		else{ $result = $this->rows($queryData); }
        return $result;  
    }
}
?>