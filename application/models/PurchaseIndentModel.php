<?php
class PurchaseIndentModel extends MasterModel
{
    private $purchase_indent = "purchase_indent";

    public function getDTRows($data){
        $data['tableName'] = $this->purchase_indent;
        $data['select'] = "purchase_indent.*,item_master.item_name,unit_master.unit_name";
        $data['leftJoin']['item_master'] = "item_master.id = purchase_indent.item_id";
        $data['leftJoin']['unit_master'] = "unit_master.id = item_master.unit_id";

        $data['where']['purchase_indent.order_status'] = $data['status'];
        $data['where']['purchase_indent.entry_type'] = $data['entry_type'];

        $data['where']['purchase_indent.trans_date >='] = $this->startYearDate;
        $data['where']['purchase_indent.trans_date <='] = $this->endYearDate;

        $data['order_by']['purchase_indent.trans_date'] = "DESC";
        $data['order_by']['purchase_indent.id'] = "DESC";

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "purchase_indent.trans_number";
        $data['searchCol'][] = "DATE_FORMAT(purchase_indent.trans_date,'%d-%m-%Y')";
        $data['searchCol'][] = "item_master.item_name";
        $data['searchCol'][] = "purchase_indent.qty";
        $data['searchCol'][] = "DATE_FORMAT(purchase_indent.delivery_date,'%d-%m-%Y')";
        $data['searchCol'][] = "purchase_indent.remark";

        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
        if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        
        return $this->pagingRows($data);
    }

    public function save($data){
        try{
            $this->db->trans_begin();
            
            $result = $this->store($this->purchase_indent,$data,'purchase Request');
            
            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function getPurchaseRequest($data){
        $data['tableName'] = $this->purchase_indent;
        if(!empty($data['id'])):
            $data['where']['id'] = $data['id'];
        endif;
        return $this->row($data);
    }

    public function delete($id){
        try{
            $this->db->trans_begin();

            $result = $this->trash($this->purchase_indent,['id'=>$id],'Purchase Request');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

    public function changeReqStatus($postData){
        try{
            $this->db->trans_begin();

            $result = $this->store($this->purchase_indent,$postData,'Purchase Request');
            
            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function getPurchaseRequestForOrder($id)
    {
        $data['tableName'] = $this->purchase_indent;
        $data['select'] = "purchase_indent.*,item_master.item_name,item_master.item_code,item_master.gst_per,item_master.price,item_master.unit_id,item_master.hsn_code,item_master.item_type, unit_master.unit_name,item_category.category_name";
        $data['leftJoin']['item_master'] = "item_master.id = purchase_indent.item_id";
        $data['leftJoin']['unit_master'] = "unit_master.id = item_master.unit_id";
        $data['leftJoin']['item_category'] = "item_category.id = item_master.category_id";
        $data['where_in']['purchase_indent.id'] = str_replace("~", ",", $id);
        $result = $this->rows($data);
        return $result;
    }


}
