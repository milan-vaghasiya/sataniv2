<?php
class RejectionParameterModel extends MasterModel{
    private $rejection_parameter = "rejection_parameter";

    public function getDTRows($data){
        $data['tableName'] = $this->rejection_parameter;

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "parameter";

		$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;

		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        return $this->pagingRows($data);
    }

    public function getRejectionParameterList($data=array()){
        $queryData['tableName'] = $this->rejection_parameter;
        
        if(!empty($data['id'])){ 
            $queryData['where_in']['rejection_parameter.id'] = $data['id']; 
        }
        return $this->rows($queryData);
    }

    public function getRejectionParameter($data){
        $queryData['tableName'] = $this->rejection_parameter;
        $queryData['where']['id'] = $data['id'];
        return $this->row($queryData);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            if($this->checkDuplicate($data) > 0):
                $errorMessage['parameter'] = " Rejection Parameter Is sDuplicate.";
                return ['status'=>0,'message'=>$errorMessage];
            endif;
            
            $result = $this->store($this->rejection_parameter,$data,'rejection_parameter');
            
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
        $queryData['tableName'] = $this->rejection_parameter;
        $queryData['where']['parameter'] = $data['parameter'];
        
        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];
        
        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

    public function delete($id){
        try{
            $this->db->trans_begin();

            $result = $this->trash($this->rejection_parameter,['id'=>$id],'rejection_parameter');

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