<?php
class SalesOrderModel extends MasterModel{
    private $soMaster = "so_master";
    private $soTrans = "so_trans";
    private $transExpense = "trans_expense";
    private $transDetails = "trans_details";
    private $orderBom = "order_bom";
    private $purchseReq = "purchase_request";

    public function getDTRows($data){
        $data['tableName'] = $this->soTrans;
        $data['select'] = "so_trans.id as trans_child_id,item_master.item_name,so_trans.qty,so_trans.dispatch_qty,(so_trans.qty - so_trans.dispatch_qty) as pending_qty,so_master.id,so_master.trans_number,DATE_FORMAT(so_master.trans_date,'%d-%m-%Y') as trans_date,party_master.party_name,so_trans.trans_status,ifnull(st.stock_qty,0) as stock_qty,party_master.sales_executive,so_master.party_id,(CASE WHEN party_master.sales_executive = so_master.party_id THEN 'Client' ELSE 'Office' END) as ordered_by";

        $data['leftJoin']['so_master'] = "so_master.id = so_trans.trans_main_id";
        $data['leftJoin']['(SELECT SUM(qty * p_or_m) as stock_qty,item_id FROM stock_transaction WHERE is_delete = 0 GROUP BY item_id) as st'] = "so_trans.item_id = st.item_id";
        $data['leftJoin']['item_master'] = "item_master.id = so_trans.item_id";
        $data['leftJoin']['party_master'] = "party_master.id = so_master.party_id";

        $data['where']['so_trans.entry_type'] = $data['entry_type'];

        if($data['status'] == 0):
            $data['where']['so_trans.trans_status'] = 0;
            $data['where']['so_master.trans_date <='] = $this->endYearDate;
        elseif($data['status'] == 1):
            $data['where']['so_trans.trans_status'] = 1;
            $data['where']['so_master.trans_date >='] = $this->startYearDate;
            $data['where']['so_master.trans_date <='] = $this->endYearDate;
        endif;

        $data['order_by']['so_master.trans_date'] = "DESC";
        $data['order_by']['so_master.id'] = "DESC";

        $data['group_by'][] = "so_trans.id";

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        //$data['searchCol'][] = "(CASE WHEN so_master.sales_executive = so_master.party_id THEN 'Client' ELSE 'Office' END)";
        $data['searchCol'][] = "so_master.trans_number";
        $data['searchCol'][] = "DATE_FORMAT(so_master.trans_date,'%d-%m-%Y')";
        $data['searchCol'][] = "party_master.party_name";
        $data['searchCol'][] = "item_master.item_name";
        //$data['searchCol'][] = "so_trans.brand_name";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "so_trans.qty";
        $data['searchCol'][] = "so_trans.dispatch_qty";
        $data['searchCol'][] = "(so_trans.qty - so_trans.dispatch_qty)";

        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
        if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        
        return $this->pagingRows($data);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            if($this->checkDuplicate($data) > 0):
                $errorMessage['trans_number'] = "SO. No. is duplicate.";
                return ['status'=>0,'message'=>$errorMessage];
            endif;

            if(!empty($data['id'])):
                $dataRow = $this->getSalesOrder(['id'=>$data['id'],'itemList'=>1]);
                foreach($dataRow->itemList as $row):
                    if(!empty($row->ref_id)):
                        $setData = array();
                        $setData['tableName'] = $this->soTrans;
                        $setData['where']['id'] = $row->ref_id;
                        $setData['update']['trans_status'] = 0;
                        $this->setValue($setData);
                    endif;

                    $this->trash($this->soTrans,['id'=>$row->id]);
                endforeach;

                $this->trash($this->transExpense,['trans_main_id'=>$data['id']]);
                $this->remove($this->transDetails,['main_ref_id'=>$data['id'],'table_name'=>$this->soMaster,'description'=>"SO TERMS"]);
            endif;
            
            $itemData = $data['itemData'];

            $transExp = getExpArrayMap(((!empty($data['expenseData']))?$data['expenseData']:array()));
			$expAmount = $transExp['exp_amount'];
            $termsData = (!empty($data['conditions']))?$data['conditions']:array();

            unset($transExp['exp_amount'],$data['itemData'],$data['expenseData'],$data['conditions']);		

            $result = $this->store($this->soMaster,$data,'Sales Order');

            $expenseData = array();
            if($expAmount <> 0):				
				$expenseData = $transExp;
                $expenseData['id'] = "";
				$expenseData['trans_main_id'] = $result['id'];
                $this->store($this->transExpense,$expenseData);
			endif;

            if(!empty($termsData)):
                $termsData = [
                    'id' =>"",
                    'table_name' => $this->soMaster,
                    'description' => "SO TERMS",
                    'main_ref_id' => $result['id'],
                    't_col_1' => $termsData
                ];
                $this->store($this->transDetails,$termsData);
            endif;

            $i=1;
            foreach($itemData as $row):
                $row['entry_type'] = $data['entry_type'];
                $row['trans_main_id'] = $result['id'];
                $row['cod_date'] = (!empty($row['cod_date']))?$row['cod_date']:NULL;
                $row['is_delete'] = 0;
                $this->store($this->soTrans,$row);

                if(!empty($row['ref_id'])):
                    $this->store('sq_trans',['id'=>$row['ref_id'],'trans_status'=>1]);
                endif;
            endforeach;
            
            if(!empty($data['ref_id'])):
                $refIds = explode(",",$data['ref_id']);
                foreach($refIds as $main_id):
                    $setData = array();
                    $setData['tableName'] = $this->soMaster;
                    $setData['where']['id'] = $main_id;
                    $setData['update']['trans_status'] = "(SELECT IF( COUNT(id) = SUM(IF(trans_status <> 0, 1, 0)) ,1 , 0 ) as trans_status FROM so_trans WHERE trans_main_id = ".$main_id." AND is_delete = 0)";
                    $this->setValue($setData);
                endforeach;
                $this->store('sq_master',['id'=>$data['ref_id'],'trans_status'=>1]);
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

    public function checkDuplicate($data){
        $queryData['tableName'] = $this->soMaster;
        $queryData['where']['trans_number'] = $data['trans_number'];

        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];

        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

    public function getSalesOrder($data){
        $queryData = array();
        $queryData['tableName'] = $this->soMaster;
        $queryData['select'] = "so_master.*,trans_details.t_col_1 as contact_person,trans_details.t_col_2 as contact_no,trans_details.t_col_3 as ship_address,,trans_details.t_col_4 as ship_pincode,employee_master.emp_name as created_name,party_master.party_name";

        $queryData['leftJoin']['trans_details'] = "so_master.id = trans_details.main_ref_id AND trans_details.description = 'SO MASTER DETAILS' AND trans_details.table_name = '".$this->soMaster."'";
        $queryData['leftJoin']['employee_master'] = "employee_master.id = so_master.created_by";
        $queryData['leftJoin']['party_master'] = "party_master.id = so_master.party_id";

        $queryData['where']['so_master.id'] = $data['id'];
        $result = $this->row($queryData);

        if($data['itemList'] == 1):
            $result->itemList = $this->getSalesOrderItems($data);
        endif;

        $queryData = array();
        $queryData['tableName'] = $this->transExpense;
        $queryData['where']['trans_main_id'] = $data['id'];
        $result->expenseData = $this->row($queryData);

        $queryData = array();
        $queryData['tableName'] = $this->transDetails;
        $queryData['select'] = "t_col_1 as condition";
        $queryData['where']['main_ref_id'] = $data['id'];
        $queryData['where']['table_name'] = $this->soMaster;
        $queryData['where']['description'] = "SO TERMS";
        $result->termsConditions = $this->row($queryData);

        return $result;
    }

    public function getSalesOrderItems($data){
        $queryData = array();
        $queryData['tableName'] = $this->soTrans;
        $queryData['select'] = "so_trans.*,tmref.trans_number as ref_number,item_master.item_name,item_master.hsn_code,unit_master.unit_name";
        $queryData['leftJoin']['so_trans as tcref'] = "tcref.id = so_trans.ref_id";
        $queryData['leftJoin']['so_master as tmref'] = "tcref.trans_main_id = tmref.id";
        $queryData['leftJoin']['item_master'] = "item_master.id = so_trans.item_id";
        $queryData['leftJoin']['unit_master'] = "unit_master.id = item_master.unit_id";
        $queryData['where']['so_trans.trans_main_id'] = $data['id'];
        $result = $this->rows($queryData);
        return $result;
    }

    public function getSalesOrderItem($data){
        $queryData = array();
        $queryData['tableName'] = $this->soTrans;
        $queryData['where']['id'] = $data['id'];
        $result = $this->row($queryData);
        return $result;
    }

    public function delete($id){
        try{
            $this->db->trans_begin();

            $postData["table_name"] = $this->soMaster;
            $postData['where'] = [['column_name'=>'from_entry_type','column_value'=>$this->data['entryData']->id]];
            $postData['find'] = [['column_name'=>'ref_id','column_value'=>$id]];
            $checkRef = $this->checkEntryReference($postData);
            if($checkRef['status'] == 0):
                $this->db->trans_rollback();
                return $checkRef;
            endif;

            $dataRow = $this->getSalesOrder(['id'=>$id,'itemList'=>1]);
            foreach($dataRow->itemList as $row):
                if(!empty($row->ref_id)):
                    $setData = array();
                    $setData['tableName'] = $this->soTrans;
                    $setData['where']['id'] = $row->ref_id;
                    $setData['update']['trans_status'] = 0;
                    $this->setValue($setData);
                endif;

                $this->trash($this->soTrans,['id'=>$row->id]);
            endforeach;

            if(!empty($dataRow->ref_id)):
                $oldRefIds = explode(",",$dataRow->ref_id);
                foreach($oldRefIds as $main_id):
                    $setData = array();
                    $setData['tableName'] = $this->soMaster;
                    $setData['where']['id'] = $main_id;
                    $setData['update']['trans_status'] = "(SELECT IF( COUNT(id) = SUM(IF(trans_status <> 0, 1, 0)) ,1 , 0 ) as trans_status FROM so_trans WHERE trans_main_id = ".$main_id." AND is_delete = 0)";
                    $this->setValue($setData);
                endforeach;
            endif;

            $this->trash($this->transExpense,['trans_main_id'=>$id]);
            $this->remove($this->transDetails,['main_ref_id'=>$id,'table_name'=>$this->soMaster,'description'=>"SO TERMS"]);
            $result = $this->trash($this->soMaster,['id'=>$id],'Sales Order');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

    public function getPendingOrderItems($data){
        $queryData = array();
        $queryData['tableName'] = $this->soTrans;
        $queryData['select'] = "so_trans.*,(so_trans.qty - so_trans.dispatch_qty) as pending_qty,so_master.party_id,so_master.entry_type as main_entry_type,so_master.trans_number,so_master.trans_date,so_master.doc_no,item_master.item_name,party_master.party_name,party_master.party_code";
        $queryData['leftJoin']['so_master'] = "so_trans.trans_main_id = so_master.id";
        $queryData['leftJoin']['party_master'] = "so_master.party_id = party_master.id";
        $queryData['leftJoin']['item_master'] = "so_trans.item_id = item_master.id";
        if(!empty($this->data['entryData']->id)){
			$queryData['where']['so_trans.entry_type'] = $this->data['entryData']->id;
		}
		
		if(!empty($data['party_id'])){
			$queryData['where']['so_master.party_id'] = $data['party_id'];
		}
		
        if(!empty($data['completed_order'])){
            $queryData['where']['(so_trans.qty - so_trans.dispatch_qty) <='] = 0;
        }else{
            $queryData['where']['(so_trans.qty - so_trans.dispatch_qty) >'] = 0;
        }
        
		if(!empty($data['group_by'])){
            $queryData['group_by'][] = $data['group_by'];
        }
        return $this->rows($queryData);
    }

    /* Party Order Start */
    public function getPartyOrderDTRows($data){
        $data['tableName'] = $this->soTrans;
        $data['select'] = "so_trans.id as trans_child_id,item_master.item_name,so_trans.qty,so_trans.dispatch_qty,(so_trans.qty - so_trans.dispatch_qty) as pending_qty,so_master.id,so_master.trans_number,DATE_FORMAT(so_master.trans_date,'%d-%m-%Y') as trans_date,party_master.party_name,so_trans.trans_status,so_trans.brand_name,party_master.sales_executive,so_master.party_id,if(so_master.is_approve > 0,'Accepted','Pending') as order_status";

        $data['leftJoin']['so_master'] = "so_master.id = so_trans.trans_main_id";
        $data['leftJoin']['party_master'] = "party_master.id = so_master.party_id";

        $data['where']['so_trans.entry_type'] = $data['entry_type'];
        $data['where']['so_trans.created_by'] = $this->loginId;
        $data['customWhere'][] = "so_master.party_id = party_master.sales_executive";

        if($data['status'] == 0):
            $data['where']['so_trans.trans_status'] = 0;
            $data['where']['so_master.trans_date <='] = $this->endYearDate;
        elseif($data['status'] == 1):
            $data['where']['so_trans.trans_status'] = 1;
            $data['where']['so_master.trans_date >='] = $this->startYearDate;
            $data['where']['so_master.trans_date <='] = $this->endYearDate;
        endif;

        $data['order_by']['so_master.trans_date'] = "DESC";
        $data['order_by']['so_master.id'] = "DESC";

        $data['group_by'][] = "so_trans.id";

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "if(so_master.is_approve > 0,'Accepted','Pending')";
        $data['searchCol'][] = "so_master.trans_number";
        $data['searchCol'][] = "DATE_FORMAT(so_master.trans_date,'%d-%m-%Y')";
        $data['searchCol'][] = "item_master.item_name";
        $data['searchCol'][] = "so_trans.brand_name";
        $data['searchCol'][] = "so_trans.qty";
        $data['searchCol'][] = "so_trans.dispatch_qty";
        $data['searchCol'][] = "(so_trans.qty - so_trans.dispatch_qty)";

        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
        if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        
        return $this->pagingRows($data);
    }
    /* Party Order End */	
}
?>