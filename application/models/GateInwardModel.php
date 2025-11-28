<?php
class GateInwardModel extends masterModel{
    private $grn_master = "grn_master";
    private $grn_trans = "grn_trans";
    private $po_master = "po_master";
    private $po_trans = "po_trans";
    private $stockTrans = "stock_transaction";
    private $icInspection = "ic_inspection";
    private $inspectParam = "inspection_param";
    private $testReport = "grn_test_report";
    private $batch_history = "batch_history";

    public function getDTRows($data){
        if($data['trans_type'] == 1):
            $data['tableName'] = $this->grn_master;

            $data['select'] = "grn_master.id,grn_master.trans_number,DATE_FORMAT(grn_master.trans_date,'%d-%m-%Y') as trans_date,party_master.party_name,grn_master.inv_no,ifnull(DATE_FORMAT(grn_master.inv_date,'%d-%m-%Y'),'') as inv_date,grn_master.doc_no,ifnull(DATE_FORMAT(grn_master.doc_date,'%d-%m-%Y'),'') as doc_date,grn_master.trans_status,grn_master.trans_type";

            $data['where']['grn_master.trans_status'] = $data['trans_status'];
            $data['where']['grn_master.entry_type'] = $this->data['entryData']->id;
        else:
            $data['tableName'] = $this->grn_trans;

            $data['select'] = "grn_master.id,grn_master.trans_number,DATE_FORMAT(grn_master.trans_date,'%d-%m-%Y') as trans_date,party_master.party_name,item_master.item_name,grn_master.inv_no,ifnull(DATE_FORMAT(grn_master.inv_date,'%d-%m-%Y'),'') as inv_date,grn_master.doc_no,ifnull(DATE_FORMAT(grn_master.doc_date,'%d-%m-%Y'),'') as doc_date,po_master.trans_number as po_number,grn_trans.trans_status,grn_master.trans_type,grn_trans.qty,grn_trans.ok_qty,grn_trans.reject_qty,grn_trans.short_qty,grn_trans.id as mir_trans_id,item_master.item_type,grn_trans.iir_status,grn_trans.batch_no,grn_trans.heat_no,item_category.category_name,item_category.is_inspection,batch_history.batch_no as batchNo,grn_trans.item_id,grn_trans.location_id,location_master.store_name,location_master.location"; // 02-05-2024

            $data['leftJoin']['grn_master'] = "grn_master.id = grn_trans.mir_id";
            $data['leftJoin']['item_master'] = "item_master.id = grn_trans.item_id";
            $data['leftJoin']['po_master'] = "po_master.id = grn_trans.po_id";        
			$data['leftJoin']['item_category'] = "item_category.id  = item_master.category_id";
			$data['leftJoin']['location_master'] = "location_master.id  = grn_trans.location_id";
			$data['leftJoin']['batch_history'] = "batch_history.heat_no = grn_trans.heat_no AND batch_history.item_id = grn_trans.item_id AND grn_master.party_id = batch_history.party_id";

            $data['where']['grn_trans.trans_status'] = $data['trans_status'];
            $data['where']['grn_trans.entry_type'] = $this->data['entryData']->id;
        endif;

        $data['leftJoin']['party_master'] = "party_master.id = grn_master.party_id";
		
        $data['where']['grn_master.trans_type'] = $data['trans_type'];
            
        $data['order_by']['grn_master.id'] = "DESC";

        $data['group_by'][] = "grn_trans.id";

        if($data['trans_type'] == 1):
            $data['searchCol'][] = "";
            $data['searchCol'][] = "";
            $data['searchCol'][] = "grn_master.trans_number";
            $data['searchCol'][] = "DATE_FORMAT(grn_master.trans_date,'%d-%m-%Y')";
            $data['searchCol'][] = "party_master.party_name";
            $data['searchCol'][] = "grn_master.inv_no";
            $data['searchCol'][] = "ifnull(DATE_FORMAT(grn_master.inv_date,'%d-%m-%Y'),'')";
            $data['searchCol'][] = "grn_master.doc_no";
            $data['searchCol'][] = "ifnull(DATE_FORMAT(grn_master.doc_date,'%d-%m-%Y'),'')";
        else:
            $data['searchCol'][] = "";
            $data['searchCol'][] = "";
            $data['searchCol'][] = "";
            $data['searchCol'][] = "grn_master.trans_number";
            $data['searchCol'][] = "DATE_FORMAT(grn_master.trans_date,'%d-%m-%Y')";
            $data['searchCol'][] = "party_master.party_name";
            $data['searchCol'][] = "item_master.item_name";
            $data['searchCol'][] = "grn_trans.heat_no";
            $data['searchCol'][] = "batch_history.batch_no";
            $data['searchCol'][] = "grn_trans.qty";
            $data['searchCol'][] = "grn_trans.ok_qty";
            $data['searchCol'][] = "grn_trans.reject_qty";
            $data['searchCol'][] = "grn_trans.short_qty";
            $data['searchCol'][] = "po_master.trans_number";
        endif;

        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
		if (isset($data['order'])) {
			$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];
		}

		return $this->pagingRows($data);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            if(!empty($data['id'])):
                $gateInwardData = $this->getGateInward($data['id']);

                if(!empty($gateInwardData->ref_id)):
                    $this->store($this->grn_master,['id'=>$gateInwardData->ref_id,'trans_status'=>0]);
                endif;

                foreach($gateInwardData->itemData as $row):
                    if(!empty($row->po_trans_id)):
                        
                        $poData = $this->purchaseOrder->getPurchaseOrderItems(['po_trans_id'=>$row->po_trans_id, 'single_row'=>1]);
                        if(!empty($poData->unit_id) && $poData->unit_id == 39){
                            $row->qty = $row->qty / 1000;
                        }
                        
                        $setData = array();
                        $setData['tableName'] = $this->po_trans;
                        $setData['where']['id'] = $row->po_trans_id;
                        $setData['set']['dispatch_qty'] = 'dispatch_qty, - '.$row->qty;
                        $setData['update']['trans_status'] = 0;
                        $this->setValue($setData);

                        $setData = array();
                        $setData['tableName'] = $this->po_master;
                        $setData['where']['id'] = $row->po_id;
                        $setData['update']['trans_status'] = 0;
                        $this->setValue($setData);
                    endif;

                    $this->trash($this->grn_trans,['id'=>$row->id]);
                endforeach;
            endif;

            $itemData = $data['batchData'];unset($data['batchData']);

            $data['trans_type'] = 2;$data['entry_type'] = $this->data['entryData']->id;
            $result = $this->store($this->grn_master,$data,'Gate Inward');

            foreach($itemData as $row):         
                $itemData = $this->item->getItem($row['item_id']);

                $row['mir_id'] = $result['id'];
                $row['entry_type'] = $this->data['entryData']->id;
                $row['type'] = 1;
                $row['is_delete'] = 0;

                if($row['item_stock_type'] == 1):
                    $nextBatchNo = $this->gateReceipt->getNextBatchOrSerialNo(['trans_id'=>$row['id'],'item_id'=>$row['item_id'],'heat_no'=>$row['heat_no']]);

                    $row['batch_no'] = $nextBatchNo['batch_no'];                    
                    $row['serial_no'] = $nextBatchNo['serial_no'];
                elseif($row['item_stock_type'] == 2):
                    $row['batch_no'] = $itemData->item_code.sprintf(n2y(date('Y'))."%03d",$data['trans_no']);
                else:
                    $row['batch_no'] = NULL;
                    $row['serial_no'] = 0;
                endif;

                $this->store($this->grn_trans,$row);

                if(!empty($row['po_trans_id'])):
                    
                    $grn_qty = $row['qty'];
                    $poData = $this->purchaseOrder->getPurchaseOrderItems(['po_trans_id'=>$row['po_trans_id'], 'single_row'=>1]);
                    if(!empty($poData->unit_id) && $poData->unit_id == 39){
                        $grn_qty = $grn_qty / 1000;
                    }
                        
                    $setData = array();
                    $setData['tableName'] = $this->po_trans;
                    $setData['where']['id'] = $row['po_trans_id'];
                    $setData['set']['dispatch_qty'] = 'dispatch_qty, + '.$grn_qty;
                    $setData['update']['trans_status'] = "(CASE WHEN dispatch_qty >= qty THEN 1 ELSE 0 END)";
                    $this->setValue($setData);

                    $setData = array();
                    $setData['tableName'] = $this->po_master;
                    $setData['where']['id'] = $row['po_id'];
                    $setData['update']['trans_status'] = "(SELECT IF( COUNT(id) = SUM(IF(trans_status != 0, 1, 0)) ,1 , 0 ) as trans_status FROM po_trans WHERE trans_main_id = ".$row['po_id']." AND is_delete = 0)";
                    $this->setValue($setData);
                endif;
                
            endforeach;

            //Update GI Status
            if(!empty($data['ref_id'])):
                $this->store($this->grn_master,['id'=>$data['ref_id'],'trans_status'=>1]);
            endif;        

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function getNextBatchOrSerialNo($data){
		$result = array(); $code = "";

        $itemData = $this->item->getItem($data['item_id']);
        $code = (!empty($itemData->stock_type) && $itemData->stock_type == 2)?$itemData->item_code:"";
        
        $itemTypes = [5,6,7];
        
		if(!empty($data['trans_id'])):
            $queryData = array();
			$queryData['select'] = "serial_no,heat_no";
			$queryData['tableName'] = $this->grn_trans;
            $queryData['where']['type'] = 1;
			$queryData['where']['id'] = $data['trans_id'];
			$result = $this->row($queryData);

			if(!empty($result->serial_no) && $data['heat_no'] == $result->heat_no):
                if(in_array($itemData->item_type,$itemTypes)):
			        $code .= sprintf("-%03d",$result->serial_no);
			    else:
			        $code .= sprintf(n2y(date('Y'))."%03d",$result->serial_no);    
			    endif;
				return ['status'=>1,'batch_no'=>$code,'serial_no'=>$result->serial_no];
			endif;			
		endif;
		
		if(!empty($itemData->stock_type) && $itemData->stock_type == 1):
            $queryData = array();
            $queryData['select'] = "serial_no,heat_no";
			$queryData['tableName'] = $this->grn_trans;
			$queryData['where']['item_id'] = $data['item_id'];
            $queryData['where']['type'] = 1;
			$queryData['where']['heat_no'] = $data['heat_no'];
			$result = $this->row($queryData);
			
			if(!empty($result->serial_no)):
                if(in_array($itemData->item_type,$itemTypes)):
			        $code .= sprintf("-%03d",$result->serial_no);
			    else:
			        $code .= sprintf(n2y(date('Y'))."%03d",$result->serial_no);    
			    endif;
				return ['status'=>1,'batch_no'=>$code,'serial_no'=>$result->serial_no];
			endif;
		endif;

		$queryData = array();
		$queryData['select'] = "ifnull(MAX(serial_no) + 1,1) as serial_no";
		$queryData['tableName'] = $this->grn_trans;
        $queryData['where']['type'] = 1;
		$queryData['where']['item_id'] = $data['item_id'];
		$queryData['where']['is_delete'] = 0;
		$queryData['where']['YEAR(created_at)'] = date("Y");
		$serial_no = $this->specificRow($queryData)->serial_no;
		
		if(in_array($itemData->item_type,$itemTypes)):
	        $code .= sprintf("-%03d",$serial_no);
	    else:
	        $code .= sprintf(n2y(date('Y'))."%03d",$serial_no);    
	    endif;
		return ['status'=>1,'batch_no'=>$code,'serial_no'=>$serial_no];
	}

    public function getGateInward($id){
        $queryData['tableName'] = $this->grn_master;
        $queryData['select'] = "grn_master.*,party_master.party_name,party_master.party_mobile,party_master.gstin,party_master.contact_person";
        $queryData['leftJoin']['party_master'] = "grn_master.party_id = party_master.id";
        $queryData['where']['grn_master.id'] = $id;
        $result = $this->row($queryData);

        $result->itemData = $this->getGateInwardItems($id);
        return $result;
    }
    
    public function getGateInwardItems($id){
        $queryData['tableName'] = $this->grn_trans;
        $queryData['select'] = "grn_trans.*,item_master.item_code,item_master.item_name,location_master.location as location_name,po_master.trans_number as po_number";
        $queryData['leftJoin']['item_master'] = "item_master.id = grn_trans.item_id";
        $queryData['leftJoin']['location_master'] = "location_master.id = grn_trans.location_id";
        $queryData['leftJoin']['po_master'] = "po_master.id = grn_trans.po_id";
        $queryData['where']['grn_trans.mir_id'] = $id;
        return $this->rows($queryData);
    }

    public function getInwardItem($data){
        $queryData['tableName'] = $this->grn_trans;
		$queryData['select'] = "grn_trans.*,item_master.item_code,item_master.item_name,item_master.stock_type,location_master.location as location_name,trans_main.trans_number as po_no,grn_master.trans_number,grn_master.trans_date,party_master.party_name,grn_master.inv_no,grn_master.inv_date,grn_master.trans_prefix,grn_master.trans_no,unit_master.unit_name,location_master.store_name";
        $queryData['leftJoin']['item_master'] = "item_master.id = grn_trans.item_id";
        $queryData['leftJoin']['unit_master'] = "item_master.unit_id = unit_master.id";
        $queryData['leftJoin']['location_master'] = "location_master.id = grn_trans.location_id";
        $queryData['leftJoin']['trans_main'] = "trans_main.id = grn_trans.po_id";
        $queryData['leftJoin']['grn_master'] = "grn_master.id = grn_trans.mir_id";
        $queryData['leftJoin']['party_master'] = "party_master.id = grn_master.party_id";
        
        if (!empty($data['id'])) { $queryData['where']['grn_trans.id'] = $data['id']; }

        if (!empty($data['ids'])) { $queryData['where_in']['grn_trans.id'] = str_replace("~", ",", $data['ids']); }

        if (!empty($data['heat_no'])) { $queryData['where']['grn_trans.heat_no'] = $data['heat_no']; }

        if (!empty($data['id_not_in'])) { $queryData['where']['grn_trans.id != '] = $data['id_not_in']; }

        if (!empty($data['multi_rows'])){
            return $this->rows($queryData);
        }else{
            return $this->row($queryData);
        }
    }

    public function delete($data){
        try{
            $this->db->trans_begin();

            $grnData = $this->getGateInward($data['mir_id']);
            $grnTransData = $this->getInwardItem(['id'=>$data['id']]);

            $stockData = $this->itemStock->getItemStockBatchWise(['item_id'=>$grnTransData->item_id, 'heat_no'=>$grnTransData->heat_no, 'group_by'=>'location_id,heat_no,item_id', 'stock_required'=>1, 'single_row'=>1]);

            /* Delete Completed GRN */
            if (!empty($grnTransData->trans_status)) {
                if (!empty($stockData) && $stockData->qty >= $grnTransData->ok_qty) {

                    $this->remove($this->stockTrans, ['entry_type' => $this->data['entryData']->id, 'main_ref_id' => $data['mir_id'], 'child_ref_id' => $data['id']]);
                    
                    /* Remove Record From Batch History */
                    $batchData = $this->getInwardItem(['heat_no'=>$grnTransData->heat_no, 'id_not_in'=>$grnTransData->id, 'multi_rows'=>1]);

                    if (empty($batchData)) {
                        $this->trash($this->batch_history, ['party_id'=>$grnData->party_id, 'item_id'=>$grnTransData->item_id, 'heat_no'=>$grnTransData->heat_no]);
                    }
                } 
                else {
                    return ['status'=>0, 'message'=>'You can not delete this GRN because the stock already used.'];
                }
            }

            if (!empty($grnTransData->po_trans_id)) {
                $poData = $this->purchaseOrder->getPurchaseOrderItems(['po_trans_id'=>$grnTransData->po_trans_id, 'single_row'=>1]);
                if(!empty($poData->unit_id) && $poData->unit_id == 39){
                    $grnTransData->qty = $grnTransData->qty / 1000;
                }
                
                $setData = array();
                $setData['tableName'] = $this->po_trans;
                $setData['where']['id'] = $grnTransData->po_trans_id;
                $setData['set']['dispatch_qty'] = 'dispatch_qty, - '.$grnTransData->qty;
                $setData['update']['trans_status'] = 0;
                $this->setValue($setData);

                $setData = array();
                $setData['tableName'] = $this->po_master;
                $setData['where']['id'] = $grnTransData->po_id;
                $setData['update']['trans_status'] = 0;
                $this->setValue($setData);
            }
            $result = $this->trash($this->grn_trans, ['id' => $grnTransData->id], 'Gate Inward');                

            $grnItemData = $this->getGateInwardItems($data['mir_id']);
            $grnItemCount = (!empty($grnItemData) ? count($grnItemData) : 0);
            if ($grnItemCount <= 1) {
                $this->trash($this->grn_master, ['id'=>$data['mir_id']]);
            }

            if (!empty($grnData->ref_id)) {
                $this->store($this->grn_master,['id'=>$grnData->ref_id,'trans_status'=>0]);
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

	public function getBatchWiseItemList($data){
        $queryData['tableName'] = $this->batch_history;
		$queryData['select'] = "batch_history.*,";
        if(isset($data['party_id'])){$queryData['where']['batch_history.party_id'] = $data['party_id'];}
        if(isset($data['item_id'])){$queryData['where']['batch_history.item_id'] = $data['item_id'];}
        if(isset($data['heat_no'])){$queryData['where']['batch_history.heat_no'] = $data['heat_no'];}
        if(!empty($data['batch_no'])){$queryData['where']['batch_history.batch_no'] = $data['batch_no'];}
        return $this->row($queryData);
    }

    public function getBatchNextNo($postData){
        $queryData['tableName'] = $this->batch_history;
        $queryData['select'] = "ifnull(MAX(batch_sr_no + 1),1) as next_no,grn_master.trans_date";
        $queryData['leftJoin']['grn_master'] = "grn_master.id = batch_history.grn_id";
        $queryData['where_in']['MONTH(grn_master.trans_date)'] = date('m',strtotime($postData['trans_date']));
        return $this->row($queryData)->next_no;
    }
	
    public function saveInspectedMaterial($data){ 
        try{
            $this->db->trans_begin();
			
                $mirData = $this->getGateInward($data['mir_id']);
                $mirItem = $this->getInwardItem(['id'=>$data['id']]);

                $batchList = $this->getBatchWiseItemList(['party_id'=>$mirData->party_id,'item_id'=>$mirItem->item_id,'heat_no'=>$mirItem->heat_no]);
                if(!empty($batchList->batch_no)){
                    $batchNo = $batchList->batch_no;
                    $nextBatchNo = $batchList->batch_sr_no;
                }else{	
                    $nextBatchNo = $this->getBatchNextNo(['trans_date'=>$mirData->trans_date]);
                    $batchNo = sprintf(n2y(date('Y')).sprintf(n2m(date('m',strtotime($mirData->trans_date)))."%03d",$nextBatchNo));
                }

                $data['ok_qty'] = (!empty($data['ok_qty']))?$data['ok_qty']:0;
                $data['reject_qty'] = (!empty($data['reject_qty']))?$data['reject_qty']:0;
                $data['short_qty'] = (!empty($data['short_qty']))?$data['short_qty']:0;
                
	            $totalQty = 0;
				$totalQty = ($data['ok_qty'] + $data['reject_qty'] + $data['short_qty']);
				
				if($mirItem->qty != $totalQty): 
					$this->db->trans_rollback();  
					return ['status'=>0,'message'=>['ok_qty' => "Invalid Qty."]];
				endif;
				
                $this->remove($this->stockTrans,['entry_type'=>$this->data['entryData']->id,'main_ref_id' => $mirData->id,'child_ref_id' => $mirItem->id]);

                $data['trans_status'] = ($totalQty >= $mirItem->qty)?1:0;

                $this->store($this->grn_trans,$data);

                if(!empty($data['ok_qty'])):
					$stockData = [
						'id' => "",
						'entry_type' => $this->data['entryData']->id,
						'unique_id' => 0,
						'ref_date' => $mirData->trans_date,
						'ref_no' => $mirData->trans_number,
						'main_ref_id' => $mirData->id,
						'child_ref_id' => $mirItem->id,
						'location_id' => $mirItem->location_id,
						'heat_no' => $mirItem->heat_no,
						'batch_no' => $batchNo,
						'party_id' => $mirData->party_id,
						'item_id' => $mirItem->item_id,
						'p_or_m' => 1,
						'qty' => $data['ok_qty'],
						'price' => $mirItem->price
					];
					$this->store($this->stockTrans,$stockData);

                    if(empty($batchList->batch_no)){
                        $batchData = [
							'id' => "",
							'grn_id' => $mirData->id,
							'grn_trans_id' => $mirItem->id,
							'heat_no' => $mirItem->heat_no,
							'batch_sr_no' => $nextBatchNo,
							'batch_no' => $batchNo,
							'party_id' => $mirData->party_id,
							'item_id' => $mirItem->item_id
						];
						$this->store($this->batch_history,$batchData);
                    }
                endif;

            $result = ['status'=>1,'message'=>"Material Inspected successfully."];

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }
	
    public function getPendingInwardItems($data){
        $queryData = array();
        $queryData['tableName'] = $this->grn_trans;
        $queryData['select'] = "grn_trans.*,(grn_trans.qty - grn_trans.inv_qty) as pending_qty,grn_master.entry_type as main_entry_type,grn_master.trans_number,grn_master.trans_date,grn_master.inv_no,grn_master.inv_date,grn_master.doc_no,grn_master.doc_date,item_master.item_code,item_master.item_name,item_master.item_type,item_master.hsn_code,item_master.gst_per,unit_master.id as unit_id,unit_master.unit_name,'0' as stock_eff";
        $queryData['leftJoin']['grn_master'] = "grn_trans.mir_id = grn_master.id";
        $queryData['leftJoin']['item_master'] = "item_master.id = grn_trans.item_id";
        $queryData['leftJoin']['unit_master'] = "item_master.unit_id = unit_master.id";
        $queryData['where']['grn_master.party_id'] = $data['party_id'];
        $queryData['where']['grn_trans.entry_type'] = $this->data['entryData']->id;
        $queryData['where']['(grn_trans.qty - grn_trans.inv_qty) >'] = 0;
        $queryData['where']['grn_trans.trans_status'] = 0;
        return $this->rows($queryData);
    }

	public function getInspectParamData($id) {
        $queryData = array();
        $queryData['tableName'] = $this->inspectParam;
        $queryData['select'] = "inspection_param.*,grn_trans.id AS mir_trans_id, grn_master.id AS mir_id";
        $queryData['leftJoin']['item_master'] = "item_master.id = inspection_param.item_id";
        $queryData['leftJoin']['grn_trans'] = "grn_trans.item_id = item_master.id";
        $queryData['leftJoin']['grn_master'] = "grn_master.id = grn_trans.mir_id";
        $queryData['where']['grn_trans.mir_id'] = $id;
        return $this->rows($queryData);
    }

    public function getInInspectData($data) {
        $queryData = array();
        $queryData['tableName'] = $this->icInspection;
        $queryData['select'] = "ic_inspection.*";
        $queryData['where']['ic_inspection.mir_trans_id'] = $data['id'];
        return $this->row($queryData);
    }

    public function saveInInspection($data) {
        try{
            $this->db->trans_begin();

            $mir_trans_id = $data['id']; unset($data['id']);
            $data['mir_trans_id'] = $mir_trans_id;

            $inInpectData = $this->getInInspectData($mir_trans_id);
            $data['id'] = (!empty($inInpectData->id))?$inInpectData->id:"";;

            $this->store($this->icInspection, $data);

            $result = ['status'=>1,'message'=>"Inspection successfully."];

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

	public function saveInwardQc($data){
		try{
            $this->db->trans_begin();

            $this->edit($this->grn_trans,['id'=>$data['mir_trans_id']],['iir_status'=>1]);
    		$result = $this->store($this->icInspection,$data,'Inward QC');

    		if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
	}

    public function getTestReport($postData){
        $data['tableName'] = $this->testReport;
        $data['where']['grn_id'] = $postData['grn_id'];
        return $this->rows($data);
    }

    public function saveTestReport($data){
        try{
            $this->db->trans_begin();
			
			$result = $this->store($this->testReport,$data);

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

    public function deleteTestReport($id){
        try{
            $this->db->trans_begin();

            $result = $this->trash($this->testReport,['id'=>$id],'Test Report');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

    //Batch No and Qty
    public function getItemWiseBatchList($id){
        $queryData['tableName'] = $this->stockTrans;
        $queryData['select'] = "stock_transaction.batch_no,stock_transaction.qty,stock_transaction.item_id,stock_transaction.location_id";
        $queryData['where']['stock_transaction.child_ref_id'] = $id;
        $queryData['where']['stock_transaction.entry_type'] = 9;
        return $this->rows($queryData);
    }
}
?>