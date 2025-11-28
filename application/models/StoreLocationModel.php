<?php
class StoreLocationModel extends MasterModel{
    private $locationMaster = "location_master";

    public function getDTRows($data){
        $data['tableName'] = $this->locationMaster;

        $data['where']['ref_id'] = $data['ref_id'];
        $data['where_in']['store_type'] = '0,15,99';

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "store_name";
        $data['searchCol'][] = "location";
        $data['serachCol'][] = "remark";

		$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
		return $this->pagingRows($data);
    }

    public function getStoreLocation($data){
        $queryData = array();
        $queryData['tableName'] = $this->locationMaster;

        if(!empty($data['id']))
            $queryData['where']['id'] = $data['id'];

        if(!empty($data['store_level']))
            $queryData['where']['store_level'] = $data['store_level'];

        return $this->row($queryData);
    }

    public function getStoreLocationList($data = array()){
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

        return $this->rows($queryData);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            $data['store_name'] = trim($data['store_name']);
            $data['location'] = trim($data['location']);

            if($this->checkDuplicate($data) > 0):
                $errorMessage['location'] = "Location is duplicate.";
                return ['status'=>0,'message'=>$errorMessage];
            endif;

            $result = $this->store($this->locationMaster,$data,'Store Location');

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
        $queryData['tableName'] = $this->locationMaster;
        $queryData['where']['store_name'] = $data['store_name'];
        $queryData['where']['location'] = $data['location'];
        
        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];
        
        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

    public function delete($id){
        try{
            $this->db->trans_begin();

            $checkData['columnName'] = ["location_id"];
            $checkData['value'] = $id;
            $checkUsed = $this->checkUsage($checkData);

            if($checkUsed == true):
                return ['status'=>0,'message'=>'The Store Location is currently in use. you cannot delete it.'];
            endif;

            $result = $this->trash($this->locationMaster,['id'=>$id],'Store Location');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }
    
    public function getNextStoreLevel($ref_id){
        $data['tableName'] = $this->locationMaster;
        $data['where']['ref_id'] = $ref_id;
        return $this->rows($data);
    }
    
    public function editLocation($data = array()){
		try{
            $this->db->trans_begin();
			$result = array();
			
			if(!empty($data['id'])):
				$queryData['tableName'] = $this->locationMaster;
				$queryData['where']['ref_id'] = $data['id'];
				$locationData = $this->rows($queryData);
			
				$result = $this->store($this->locationMaster,$data,'Store Location');
			
				if(!empty($locationData)):
					foreach($locationData as $row):
						$setData = [
								'id' => $row->id,
								'store_name' => $data['location'],
								'remark' => $data['remark'],
								'updated_by' => $this->loginId,
								'updated_by' => date('Y-m-d H:i:s')
							];
						$result = $this->store($this->locationMaster,$setData,'Store Location');
					endforeach;
				endif;
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

    public function getLocationIds($data){
        $queryData['tableName'] = $this->locationMaster;
        $queryData['where']['store_name'] = $data['store_name'];
        $queryData['where']['final_location'] = 1;
        $queryData['where']['store_type'] = 0;
        $locationData = $this->rows($queryData);

        return !empty($locationData) ? array_column($locationData, 'id') : [];
    }
}
?>