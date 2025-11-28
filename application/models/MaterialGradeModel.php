<?php
class MaterialGradeModel extends MasterModel{
    private $materialMaster = "material_master";

    public function getDTRows($data){
        $data['tableName'] = $this->materialMaster;
        $data['select'] = "material_master.*, GROUP_CONCAT(group_master.item_name SEPARATOR ', ') as group_name";
		$data['leftJoin']['item_master as group_master'] = "FIND_IN_SET(group_master.id, material_master.scrap_group) > 0";
        $data['order_by']['material_master.id'] = "ASC";
        $data['group_by'][] = 'material_master.id';
        
        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "material_master.material_grade";
        $data['searchCol'][] = "item_master.item_name"; 
        $data['searchCol'][] = "material_master.color_code";

		$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;

		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        return $this->pagingRows($data);
    }

    public function getMaterial($data){
        $data['tableName'] = $this->materialMaster;
        if(!empty($data['id'])):
            $data['where']['id'] = $data['id'];
        endif;
        return $this->row($data);
    }

    public function getMaterialGrades(){
        $data['tableName'] = $this->materialMaster;
        return $this->rows($data);
    }

    public function getStandardName(){
        $data['tableName'] = $this->materialMaster;
        $data['select'] = "DISTINCT(standard)";
        return $this->rows($data);
    }

    public function standardSearch(){
        $data['tableName'] = $this->materialMaster;
		$data['select'] = 'standard';
		$result = $this->rows($data);
		$searchResult = array();
		foreach ($result as $row) {
			$searchResult[] = $row->standard;
		}
		return  $searchResult;
	}

    public function save($data){
        try{
            $this->db->trans_begin();
            $data['material_grade'] = trim($data['material_grade']);
            if($this->checkDuplicate($data['material_grade'],$data['id']) > 0):
                $errorMessage['material_grade'] = "Material Grade is duplicate.";
                $result = ['status'=>0,'message'=>$errorMessage];
            else:
                $result = $this->store($this->materialMaster,$data,'Material Grade');
            endif;

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function checkDuplicate($materialGrade,$id=""){
        $data['tableName'] = $this->materialMaster;
        $data['where']['material_grade'] = $materialGrade;
        
        if(!empty($id))
            $data['where']['id !='] = $id;
        return $this->numRows($data);
    }

    public function delete($id){
        try{
            $this->db->trans_begin();
            $result = $this->trash($this->materialMaster,['id'=>$id],'Material Grade');
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