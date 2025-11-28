<?php
class StockTransModel extends MasterModel{
    private $stockTrans = "stock_transaction";
    private $locationMaster = "location_master";

    public function getDTRows($data){
        $data['tableName'] = $this->stockTrans;
        $data['select'] = "stock_transaction.*,item_master.item_code,item_master.item_name";

        $data['leftJoin']['item_master'] = "item_master.id = stock_transaction.item_id";

        $data['where']['stock_transaction.p_or_m'] = 1;

        if(!empty($data['item_type']))$data['where']['item_master.item_type'] = $data['item_type'];
        $data['where']['stock_transaction.ref_date >='] = $this->startYearDate;
        $data['where']['stock_transaction.ref_date <='] = $this->endYearDate;
        
        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['serachCol'][] = "DATE_FORMAT(stock_transaction.ref_date,'%d-%m-%Y')";
        $data['searchCol'][] = "item_master.item_code";
        $data['serachCol'][] = "item_master.item_name";
        $data['serachCol'][] = "stock_transaction.qty";
        $data['serachCol'][] = "stock_transaction.size";
        $data['serachCol'][] = "stock_transaction.remark";

		$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
        if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        
        return $this->pagingRows($data);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            $result = $this->store($this->stockTrans,$data,'Stock');
        
            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

    public function delete($id){
        try{
            $this->db->trans_begin();

            $transData = $this->getStockTrans(['id'=>$id]);
            $itemStock = $this->getItemCurrentStock(['item_id'=>$transData->item_id]);
            if($transData->qty > $itemStock->qty):
                return ['status'=>0,'message'=>'Item Stock Used. You cant delete this record.'];
            endif;

            $result = $this->trash($this->stockTrans,['id'=>$id],'Stock');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

    public function getStockTrans($data){
        $queryData['tableName'] = $this->stockTrans;
        $queryData['where']['id'] = $data['id'];
        return $this->row($queryData);
    }

    // Get Single Item Stock From Stock Transaction
    public function getItemCurrentStock($data){
        $queryData['tableName'] = $this->stockTrans;
        $queryData['select'] = "SUM(qty * p_or_m) as qty";
        $queryData['where']['item_id'] = $data['item_id'];
        return $this->row($queryData);
    }

    /* Created At : 09-12-2022 [Milan Chauhan] */
    public function getItemStockBatchWise($data){
        $queryData['tableName'] = $this->stockTrans;
        $queryData['select'] = "stock_transaction.id,stock_transaction.ref_date,stock_transaction.item_id, item_master.item_code, item_master.item_name, item_master.item_type, SUM(stock_transaction.qty * stock_transaction.p_or_m) as qty, stock_transaction.unique_id, stock_transaction.batch_no,  stock_transaction.location_id, lm.location, lm.store_name,stock_transaction.stock_type,stock_transaction.heat_no,stock_transaction.remark";
        
        $queryData['leftJoin']['location_master as lm'] = "lm.id=stock_transaction.location_id";
        $queryData['leftJoin']['item_master'] = "stock_transaction.item_id = item_master.id";
        
         if(!empty($data['supplier'])){
			$queryData['select'] .= ",party_master.party_name";
			//$queryData['leftJoin']['batch_history'] = "batch_history.batch_no = stock_transaction.batch_no AND batch_history.item_id = stock_transaction.item_id";
			$queryData['leftJoin']['(SELECT 
			                            heat_no,party_id,batch_no,item_id 
			                        FROM 
			                            batch_history 
			                        WHERE 
			                            is_delete = 0 AND
			                            (heat_no IS NOT NULL OR heat_no != "") AND
			                            (batch_no IS NOT NULL OR batch_no != "")
			                        GROUP BY 
			                            batch_no,heat_no) as batch_history'] = "batch_history.batch_no = stock_transaction.batch_no AND batch_history.heat_no = stock_transaction.heat_no AND batch_history.item_id = stock_transaction.item_id";
		    $queryData['leftJoin']['party_master'] = "batch_history.party_id = party_master.id";			
		}

        if(!empty($data['item_id'])): 
            $queryData['where']['stock_transaction.item_id'] = $data['item_id'];           
        endif;

        if(!empty($data['location_id'])):
            $queryData['where']['stock_transaction.location_id'] = $data['location_id'];
        endif;

        if(!empty($data['location_not_in'])):
            $queryData['where_not_in']['stock_transaction.location_id'] = $data['location_not_in'];
        endif;

        if(!empty($data['batch_no'])):
            $queryData['where']['stock_transaction.batch_no'] = $data['batch_no'];
        endif;
        
        if(!empty($data['heat_no'])):
            $queryData['where']['stock_transaction.heat_no'] = $data['heat_no'];
        endif;
        
        if(!empty($data['p_or_m'])):
            $queryData['where']['stock_transaction.p_or_m'] = $data['p_or_m'];
        endif;

        if(!empty($data['entry_type'])):
            $queryData['where_in']['stock_transaction.entry_type'] = $data['entry_type'];
        endif;

        if(!empty($data['main_ref_id'])):
            $queryData['where']['stock_transaction.main_ref_id'] = $data['main_ref_id'];
        endif;

        if(!empty($data['child_ref_id'])):
            $queryData['where']['stock_transaction.child_ref_id'] = $data['child_ref_id'];
        endif;

        if(!empty($data['ref_no'])):
            $queryData['where']['stock_transaction.ref_no'] = $data['ref_no'];
        endif;

        if(!empty($data['customWhere'])):
            $queryData['customWhere'][] = $data['customWhere'];
        endif;
        
        if(!empty($data['stock_required'])):
            $queryData['having'][] = 'SUM(stock_transaction.qty * stock_transaction.p_or_m) > 0';
        endif;

        //$queryData['where']['lm.final_location'] = 0;
        if(!empty($data['group_by'])){
            $queryData['group_by'][] = $data['group_by'];
        }else{
            $queryData['group_by'][] = "stock_transaction.unique_id";
        }
        if(isset($data['semi_stock']) && $data['semi_stock'] ==1):
            $queryData['where']['stock_transaction.location_id !='] = $this->RTD_STORE->id;
        endif;
    
      
        $queryData['order_by']['lm.location'] = "ASC";

        if(isset($data['single_row']) && $data['single_row'] == 1):
            $stockData = $this->row($queryData);
        else:
            $stockData = $this->rows($queryData);
        endif;
        //$this->printQuery();
        return $stockData;
    }

    /** For App */
    public function getStockDataForApp(){
        $data['tableName'] = $this->stockTrans;
        $data['select'] = "stock_transaction.*,item_master.item_code,item_master.item_name";
        $data['leftJoin']['item_master'] = "item_master.id = stock_transaction.item_id";
        $data['where']['stock_transaction.p_or_m'] = 1;

        $data['where']['stock_transaction.ref_date >='] = ("2023-10-18" < $this->startYearDate)?$this->startYearDate:"2023-10-18";
        //$data['where']['stock_transaction.ref_date >='] = $this->startYearDate;
        $data['where']['stock_transaction.ref_date <='] = $this->endYearDate;
        
        $data['order_by']['stock_transaction.ref_date'] = 'DESC';
        return $this->rows($data);
    }
    
    public function getItemInwardDTRows($data){
        $data['tableName'] = $this->stockTrans;
        $data['select'] = "stock_transaction.*,item_master.item_code,item_master.item_name,item_master.item_type,location_master.location";
        $data['leftJoin']['item_master'] = "item_master.id = stock_transaction.item_id";
        $data['leftJoin']['location_master'] = "location_master.id = stock_transaction.location_id";

        $data['where']['stock_transaction.p_or_m'] = 1;
        $data['where_in']['item_master.item_type'] = '1,2,3';
		if(!empty($data['entry_type'])){ $data['where']['stock_transaction.entry_type'] = $data['entry_type']; }

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "item_master.item_name";
        $data['searchCol'][] = "location_master.location";
        $data['searchCol'][] = "stock_transaction.qty";

		$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
        if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        
        return $this->pagingRows($data);
    }
    
    public function deleteOpeningStock($id){
        try{
            $this->db->trans_begin();

            $transData = $this->getStockTrans(['id'=>$id]);
            $itemStock = $this->getItemStockBatchWise(['item_id'=>$transData->item_id,'location_id'=>$transData->location_id,'batch_no'=>$transData->batch_no,'heat_no'=>$transData->heat_no,'single_row'=>1]);

            if($transData->qty > $itemStock->qty):
                return ['status'=>0,'message'=>'Item Stock Used. You cant delete this record.'];
            endif;

            $result = $this->trash($this->stockTrans,['id'=>$id],'Stock');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }
    
    public function getStockLocationList($data = array()){
        $queryData = array();
        $queryData['tableName'] = $this->locationMaster;

        if(isset($data['store_type']))
            $queryData['where_in']['store_type'] = $data['store_type'];

        if(isset($data['final_location']))
            $queryData['where']['final_location'] = $data['final_location'];

        if(!empty($data['ref_id']))
            $queryData['where']['ref_id'] = $data['ref_id'];

        $queryData['order_by']['store_name'] = "ASC";
        $queryData['order_by']['location'] = "ASC";

        $storeList = $this->rows($queryData);
         if(!empty($storeList))
        {
            $i=0;
            foreach($storeList as $store)
            {
                $locationList[$i]['store_name'] = $store->store_name;
                $data['tableName'] = $this->locationMaster;
                $data['where']['store_name'] = $store->store_name;
                $data['where_in']['store_type'] = $data['store_type'];
                $data['where']['final_location'] = $data['final_location'];
                $locationList[$i++]['location'] =  $this->rows($data);
            }
        }
        return $locationList;
    }

    public function saveStockTransfer($data){
        try{
            $this->db->trans_begin();
            $fromTrans = [
                'id' => "",
                "location_id" => $data['from_location_id'],
                "batch_no" => $data['batch_no'],
                "heat_no" =>$data['heat_no'],
                "p_or_m" => -1,
                "item_id" => $data['item_id'],
                "qty" => $data['transfer_qty'],
                "entry_type" => "57", //sub menu id stock regiseter
                "ref_date" => date("Y-m-d"),
                "created_by" => $data['created_by'],
                "created_at" => date('Y-m-d H:i:s')
            ];
            $this->store('stock_transaction',$fromTrans);
    
            $toTrans = [
                'id' => "",
                "location_id" => $data['to_location_id'],
                "batch_no" => $data['batch_no'],
                "heat_no" =>$data['heat_no'],
                "p_or_m" => 1,
                "item_id" => $data['item_id'],
                "qty" => $data['transfer_qty'],
                "entry_type" => "57", //sub menu id stock regiseter
                "ref_date" => date("Y-m-d"),
                "created_by" => $data['created_by'],
                "created_at" => date('Y-m-d H:i:s')
            ];
            $this->store('stock_transaction',$toTrans);
    
            $result = ['status'=>1,'message'=>"Stock Transfer successfully."];
            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
           return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }   
    }
}
?>