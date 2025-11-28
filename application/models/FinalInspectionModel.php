<?php
class FinalInspectionModel extends MasterModel{
    private $productionInspection = "production_inspection";
    private $stockTrans = "stock_transaction";

    public function getDTRows($data){
        $data['tableName'] = $this->stockTrans;
        $data['select'] = "stock_transaction.*,SUM(stock_transaction.qty * stock_transaction.p_or_m) as qty,item_master.item_code,item_master.item_name,prc_master.prc_number";
        $data['leftJoin']['item_master'] = "item_master.id = stock_transaction.item_id";
        $data['leftJoin']['prc_master'] = "prc_master.id = stock_transaction.main_ref_id";
        $data['having'][] = 'SUM(stock_transaction.qty * stock_transaction.p_or_m) > 0';
        $data['where']['stock_transaction.location_id'] = $this->FIR_STORE->id;
        $data['group_by'][] = "stock_transaction.batch_no,stock_transaction.item_id";
  
        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "prc_master.prc_number";
        $data['serachCol'][] = "item_master.item_name";
        $data['serachCol'][] = "stock_transaction.qty";

		$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
        if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        
        return $this->pagingRows($data);
    }

    public function getFirDTRows($data){
        $data['tableName'] = "production_inspection";
		$data['select'] = "production_inspection.*,prc_master.prc_number,item_master.item_name";
        $data['leftJoin']['prc_master'] = "prc_master.id = production_inspection.prc_id";
        $data['leftJoin']['item_master'] = "item_master.id = production_inspection.item_id";
        $data['where']['production_inspection.report_type'] = 2;

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "production_inspection.insp_date";
        $data['searchCol'][] = "production_inspection.trans_number"; 
        $data['searchCol'][] = "prc_master.prc_number";
        $data['searchCol'][] = "item_master.item_name";
        $data['searchCol'][] = "production_inspection.ok_qty";
        $data['searchCol'][] = "production_inspection.rej_found";
        $data['searchCol'][] = "";

		$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;

		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        return $this->pagingRows($data);
    }

    public function saveFinalInspection($data){
		try{
            $this->db->trans_begin();
            $prcNo = $data['prc_number'];
            $heat_no = $data['heat_no'];
            unset($data['prc_number'],$data['heat_no']);
    		$result = $this->store($this->productionInspection,$data,'Final Inspection');

            $entryData = $this->transMainModel->getEntryType(['controller'=>'finalInspection']);
            //Stock Minus Entry
            $stockData = [
                'id' => "",
                'entry_type' => $entryData->id,
                'ref_date' => $data['insp_date'],
                'ref_no' =>$data['trans_number'],
                'main_ref_id' => $data['prc_id'],
                'child_ref_id' => $result['id'],
                'location_id' => $this->FIR_STORE->id,
                'batch_no' =>$prcNo,
                'heat_no' =>$heat_no,
                'item_id' => $data['item_id'],
                'p_or_m' => -1,
                'qty' => ($data['ok_qty'] + $data['rej_found']),
            ];
            $this->store('stock_transaction',$stockData);

            //Stock Plus Entry
            if($data['ok_qty'] > 0){
                $stockPlusData = [
                    'id' => "",
                    'entry_type' => $entryData->id,
                    'ref_date' => $data['insp_date'],
                    'ref_no' =>$data['trans_number'],
                    'main_ref_id' => $data['prc_id'],
                    'child_ref_id' => $result['id'],
                    'location_id' => $this->RTD_STORE->id,
                    'batch_no' =>$prcNo,
                    'heat_no' =>$heat_no,
                    'item_id' => $data['item_id'],
                    'p_or_m' => 1,
                    'qty' => $data['ok_qty'],
                ];
                $this->store('stock_transaction',$stockPlusData);
            }
    		if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
	}

    public function getFinalInspectData($data) {
        $queryData = array();
        $queryData['tableName'] = $this->productionInspection;
		$queryData['select'] = "production_inspection.*,prc_master.prc_number,item_master.item_name,employee_master.emp_name,item_master.item_code";
        $queryData['leftJoin']['prc_master'] = "prc_master.id = production_inspection.prc_id";
        $queryData['leftJoin']['item_master'] = "item_master.id = production_inspection.item_id";
        $queryData['leftJoin']['employee_master'] = "employee_master.id = production_inspection.created_by";
		$queryData['where']['production_inspection.id'] = $data['id']; 
		return $this->row($queryData);
		
    }

    public function delete($id){
		try {
			$this->db->trans_begin();
			$firData = $this->getFinalInspectData(['id'=>$id]);
            $entryData = $this->transMainModel->getEntryType(['controller'=>'finalInspection']);
			$stock = $this->itemStock->getItemStockBatchWise(['location_id'=>$this->RTD_STORE->id,'batch_no'=>$firData->prc_number,'item_id'=> $firData->item_id,'entry_type'=>$entryData->id,'single_row'=>1]);
            
			if($firData->ok_qty > $stock->qty){ 
				return ['status'=>0,'message'=>'You can not delete this record']; 
			}
            $result = $this->trash($this->productionInspection,['id'=>$id],'Final Inspection');
            $this->remove('stock_transaction',['main_ref_id'=>$firData->prc_id,'child_ref_id'=>$firData->id]);
					
			if($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return $result;
			endif;
		}catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

    public function getFirNextNo($type = 2){
        $queryData['tableName'] = $this->productionInspection;
        $queryData['select'] = "ifnull(MAX(trans_no + 1),1) as next_no";
        $queryData['where']['report_type'] = $type;
        $queryData['where']['insp_date >='] = $this->startYearDate;
        $queryData['where']['insp_date <='] = $this->endYearDate;
        return $this->row($queryData)->next_no;
    }
}
?>