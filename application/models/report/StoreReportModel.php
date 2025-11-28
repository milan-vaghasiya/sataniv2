<?php
class StoreReportModel extends MasterModel{
    private $itemMaster = "item_master";
    private $stockTrans = "stock_transaction";

    public function getStockRegisterData($data){
        $queryData = array();
        $queryData['tableName'] = $this->itemMaster;
        $queryData['select'] = "item_master.id as item_id,item_master.item_code,item_master.item_name,ifnull(st.stock_qty,0) as stock_qty";

        if(!empty($data['stock_where'])){
            $queryData['leftJoin']['(SELECT SUM(qty * p_or_m) as stock_qty,item_id FROM stock_transaction WHERE is_delete = 0 AND '.$data['stock_where'].' GROUP BY item_id) as st'] = "item_master.id = st.item_id";
        }else{
            $queryData['leftJoin']['(SELECT SUM(qty * p_or_m) as stock_qty,item_id FROM stock_transaction WHERE is_delete = 0 GROUP BY item_id) as st'] = "item_master.id = st.item_id";
        }

        $queryData['where']['item_master.item_type'] = $data['item_type'];
        if(!empty($data['stock_type'])):
            if($data['stock_type'] == 1):
                $queryData['where']['ifnull(st.stock_qty,0) > '] = "ifnull(st.stock_qty,0) > 0";
            else:
                $queryData['where']['ifnull(st.stock_qty,0) <= '] = "0";
            endif;
        endif;

        $result = $this->rows($queryData);
		
        return $result;
    }
    
    public function getStockTransaction($data){
        $queryData = array();
        $queryData['tableName'] = $this->stockTrans;
        $queryData['select'] = "stock_transaction.batch_no,SUM(stock_transaction.qty * stock_transaction.p_or_m) as stock_qty";
        $queryData['where']['stock_transaction.item_id'] = $data['item_id'];
        $queryData['having'][] = "SUM(stock_transaction.qty * stock_transaction.p_or_m) > 0";
        $queryData['group_by'][] = "stock_transaction.batch_no";
        return $this->rows($queryData);
    }

    public function getItemSummary($data){
        $unique_id = "";
        if(!empty($data['unique_id'])):
            $unique_id = " AND unique_id = ".$data['unique_id'];
        endif;

        $queryData = array();
        $queryData['tableName'] = $this->itemMaster;
        $queryData['select'] = "item_master.id,item_master.item_code,item_master.item_name,ifnull(st.op_stock_qty,0) as op_stock_qty,ifnull(st.in_stock_qty,0) as in_stock_qty,ifnull(st.out_stock_qty,0) as out_stock_qty,ifnull(st.cl_stock_qty,0) as cl_stock_qty";

        $queryData['leftJoin']['(SELECT 
        item_id,
        SUM((CASE WHEN ref_date < "'.$data['from_date'].'" THEN (qty * p_or_m) ELSE 0 END)) as op_stock_qty,
        
        SUM((CASE WHEN ref_date >= "'.$data['from_date'].'" AND ref_date <= "'.$data['to_date'].'" AND p_or_m = 1 THEN qty ELSE 0 END)) as in_stock_qty,
        
        SUM((CASE WHEN ref_date >= "'.$data['from_date'].'" AND ref_date <= "'.$data['to_date'].'" AND p_or_m = -1 THEN qty ELSE 0 END)) as out_stock_qty,
        
        SUM((CASE WHEN ref_date <= "'.$data['to_date'].'" THEN (qty * p_or_m) ELSE 0 END)) as cl_stock_qty

        FROM stock_transaction WHERE is_delete = 0 '.$unique_id.' GROUP BY item_id) as st'] = "item_master.id = st.item_id";

        if(!empty($data['item_id'])):
            $queryData['where']['item_master.id'] = $data['item_id'];
            $result = $this->row($queryData);
        else:
            $result = $this->rows($queryData);
        endif;
        return $result;
    }
	
	public function getItemHistory($data){
        $queryData['tableName'] = $this->stockTrans;
        $queryData['select'] = 'item_master.item_code,item_master.item_name,stock_transaction.*,sub_menu_master.sub_menu_name,(CASE WHEN stock_transaction.p_or_m = 1 THEN stock_transaction.qty ELSE 0 END) as in_qty,(CASE WHEN stock_transaction.p_or_m = -1 THEN stock_transaction.qty ELSE 0 END) as out_qty,party_master.party_name,location_master.store_name,location_master.location';

        $queryData['leftJoin']['item_master'] = "item_master.id = stock_transaction.item_id";
		$queryData['leftJoin']['sub_menu_master'] = "sub_menu_master.id = stock_transaction.entry_type";
        $queryData['leftJoin']['party_master'] = "party_master.id = stock_transaction.party_id";
        $queryData['leftJoin']['location_master'] = "location_master.id = stock_transaction.location_id"; 

        $queryData['where']['stock_transaction.item_id'] = $data['item_id'];
        $queryData['where']['stock_transaction.ref_date >='] = $data['from_date'];
        $queryData['where']['stock_transaction.ref_date <='] = $data['to_date'];
        if(!empty($data['unique_id'])):
            $queryData['where']['stock_transaction.unique_id'] = $data['unique_id'];
        endif;

        $queryData['order_by']['stock_transaction.id'] = 'ASC';

		$result = $this->rows($queryData);
		return $result;
    }
    
    /* INVENTORY MONITORING REPORT CREATE BY RASHMI 20/05/2024*/
    public function getInventoryMonitor($postData){
        $data['tableName'] =  $this->itemMaster;
		$data['select'] = 'item_master.id, item_master.item_name, item_master.item_code,item_master.item_type, item_master.price';
		
		if($postData['item_type'] != 1):
    		$data['select'] .= ',SUM(CASE WHEN stock_transaction.created_at <="'.date('Y-m-d', strtotime('+1 day',strtotime($postData['to_date']))).'" AND stock_transaction.p_or_m = 1 AND stock_transaction.is_delete = 0 THEN stock_transaction.qty ELSE 0 END) AS rqty';
    		$data['select'] .= ',SUM(CASE WHEN stock_transaction.created_at <="'.date('Y-m-d', strtotime('+1 day',strtotime($postData['to_date']))).'" AND stock_transaction.p_or_m = -1 AND stock_transaction.is_delete = 0 THEN stock_transaction.qty ELSE 0 END) AS iqty';
    		$data['select'] .= ',SUM(CASE WHEN stock_transaction.entry_type = -1 AND stock_transaction.is_delete = 0 THEN stock_transaction.qty ELSE 0 END) AS opening_qty';
		else:
    		$data['select'] .= ',SUM(CASE WHEN stock_transaction.created_at <="'.date('Y-m-d', strtotime('+1 day',strtotime($postData['to_date']))).'" AND stock_transaction.p_or_m = 1 AND stock_transaction.is_delete = 0 AND  stock_transaction.location_id = "'.$this->RTD_STORE->id.'" THEN stock_transaction.qty ELSE 0 END) AS rqty';
    		$data['select'] .= ',SUM(CASE WHEN stock_transaction.created_at <="'.date('Y-m-d', strtotime('+1 day',strtotime($postData['to_date']))).'" AND stock_transaction.p_or_m = -1 AND stock_transaction.is_delete = 0 AND stock_transaction.location_id = "'.$this->RTD_STORE->id.'" THEN stock_transaction.qty ELSE 0 END) AS iqty';
    		$data['select'] .= ',SUM(CASE WHEN stock_transaction.entry_type = -1 AND stock_transaction.is_delete = 0 AND stock_transaction.location_id = "'.$this->RTD_STORE->id.'" THEN stock_transaction.qty ELSE 0 END) AS opening_qty';
		endif;

		$data['leftJoin']['stock_transaction'] = 'stock_transaction.item_id = item_master.id';
	    $data['where']['item_master.item_type'] = $postData['item_type'];
		$data['where']['stock_transaction.is_delete'] = 0;
		$data['order_by']['item_master.item_code'] = 'ASC';
		$data['group_by'][] = 'stock_transaction.item_id';
        $result = $this->rows($data);
		return $result;
    }

    /* Raw Material Stock Register Report */
    public function getRMStockRegister(){
		$queryData = array();
		$queryData['tableName'] = $this->itemMaster;
		$queryData['select'] = 'item_master.id,item_master.item_code,item_master.item_name,item_master.size,material_master.material_grade,IFNULL(SUM(stock_transaction.qty), 0) AS stock_qty';
		$queryData['leftJoin']['stock_transaction'] = 'item_master.id = stock_transaction.item_id';
		$queryData['leftJoin']['material_master'] = 'material_master.id = item_master.grade_id';
		$queryData['where']['item_master.item_type'] = 3;
		$queryData['where']['stock_transaction.stock_effect'] = 1;
		$queryData['where']['stock_transaction.is_delete'] = 0;
		$queryData['group_by'][] = 'item_master.id';
		return $this->rows($queryData);
	}
	
	public function getLocationWiseStock($data=[]){
        $queryData['tableName'] = $this->stockTrans;
        $queryData['select'] = "item_master.item_name,SUM(stock_transaction.qty * stock_transaction.p_or_m) as qty, stock_transaction.batch_no,location_master.location, location_master.store_name,stock_transaction.heat_no";
        
        $queryData['leftJoin']['location_master'] = "location_master.id = stock_transaction.location_id";
        $queryData['leftJoin']['item_master'] = "stock_transaction.item_id = item_master.id";
        $queryData['select'] .= ",party_master.party_name";
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
        if(!empty($data['location_id'])):
            $queryData['where']['stock_transaction.location_id'] = $data['location_id'];
        endif;
        if(!empty($data['item_type'])):
            $queryData['where']['item_master.item_type'] = $data['item_type'];
        endif;
        if(!empty($data['item_id'])):
            $queryData['where']['stock_transaction.item_id'] = $data['item_id'];
        endif;
            if(!empty($data['stock_required'])):
            $queryData['having'][] = 'SUM(stock_transaction.qty * stock_transaction.p_or_m) > 0';
                endif;
            if(!empty($data['group_by'])){
            $queryData['group_by'][] = $data['group_by'];
                }else{
            $queryData['group_by'][] = "stock_transaction.unique_id";
        }
        $queryData['order_by']['location_master.location'] = "ASC";

        if(isset($data['single_row']) && $data['single_row'] == 1):
            $stockData = $this->row($queryData);
        else:
            $stockData = $this->rows($queryData);
        endif;
        
        return $stockData;
    }
}
?>