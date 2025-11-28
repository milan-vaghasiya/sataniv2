<?php
class RejectionTypeModel extends MasterModel{
    private $rejection_type = "rejection_type";

    public function getDTRows($data){
        $data['tableName'] = $this->rejection_type;
        $data['where_not_in']['rejection_type'] = ["'Raw Material'","'Machine'"]; //Default Rejection Type
        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "rejection_type";

		$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;

		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        return $this->pagingRows($data);
    }

    public function getRejectionTypeList($data=array()){
        $queryData['tableName'] = $this->rejection_type;
        return $this->rows($queryData);
    }

    public function getRejectionType($data){
        $queryData['where']['id'] = $data['id'];
        $queryData['tableName'] = $this->rejection_type;
        return $this->row($queryData);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            if($this->checkDuplicate($data) > 0):
                $errorMessage['rejection_type'] = "Duplicate.";
                return ['status'=>0,'message'=>$errorMessage];
            endif;
            
            $result = $this->store($this->rejection_type,$data,'rejection_type');
            
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
        $queryData['tableName'] = $this->rejection_type;
        $queryData['where']['rejection_type'] = $data['rejection_type'];
        
        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];
        
        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

    public function delete($id){
        try{
            $this->db->trans_begin();

            $result = $this->trash($this->rejection_type,['id'=>$id],'rejection_type');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }


}
?>