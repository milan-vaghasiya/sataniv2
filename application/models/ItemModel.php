<?php
class ItemModel extends MasterModel{
    private $itemMaster = "item_master";
    private $unitMaster = "unit_master";
    private $item_udf = "item_udf";
    private $productProcess = "product_process";
	private $processMaster = "process_master";
    private $inspectionParam = "inspection_param";
	private $item_revision = "item_revision";
    private $itemKit = "item_kit";

    public function getItemCode($item_type = 1){
        $queryData['tableName'] = $this->itemMaster;
        $queryData['select'] = "ifnull((MAX(CAST(REGEXP_SUBSTR(item_code,'[0-9]+') AS UNSIGNED)) + 1),1) as item_code";
        $queryData['where']['item_type'] = $item_type;
        $result = $this->row($queryData)->item_code;
        return $result;
    }
    
    public function getDTRows($data){
        $data['tableName'] = $this->itemMaster;
        $data['select'] = "item_master.*,CAST(item_master.gst_per AS FLOAT) as gst_per,item_category.category_name,item_category.is_inspection,unit_master.unit_name";
        
        $data['leftJoin']['item_category'] = "item_category.id  = item_master.category_id";
        $data['leftJoin']['unit_master'] = "unit_master.id  = item_master.unit_id";

        $data['where']['item_master.item_type'] = $data['item_type'];
        $data['where']['item_master.active'] = 1;

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "item_master.item_code";
        $data['searchCol'][] = "item_master.item_name";
        $data['searchCol'][] = "item_category.category_name";
        $data['searchCol'][] = "unit_master.unit_name";
        $data['searchCol'][] = "item_master.hsn_code";
        $data['searchCol'][] = "item_master.gst_per";
        $data['searchCol'][] = "item_master.price";

		$columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;

		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        return $this->pagingRows($data);
    }

    public function getItemList($data=array()){
        $queryData['tableName'] = $this->itemMaster;
        $queryData['select'] = "item_master.*,item_master.id as item_id,unit_master.unit_name,item_category.category_name,item_category.batch_stock as stock_type";

        $queryData['leftJoin']['item_category'] = "item_category.id  = item_master.category_id";
        $queryData['leftJoin']['unit_master'] = "item_master.unit_id = unit_master.id";
        
        if(!empty($data['item_type'])):
            $queryData['where_in']['item_master.item_type'] = $data['item_type'];
        endif;

        if(!empty($data['category_id'])):
            $queryData['where_in']['item_master.category_id'] = $data['category_id'];
        endif;

        if(!empty($data['ids'])):
            $queryData['where_in']['item_master.id'] = $data['ids'];
        endif;

        if(!empty($data['not_ids'])):
            $queryData['where_not_in']['item_master.id'] = $data['not_ids'];
        endif;
        
        if(!empty($data['active_item'])):
            $queryData['where_in']['item_master.active'] = $data['active_item'];
        else:
            $queryData['where']['item_master.active'] = 1;
        endif;

        return $this->rows($queryData);
    }

    public function getItem($data){
        $queryData['tableName'] = $this->itemMaster;
        $queryData['select'] = "item_master.*,unit_master.unit_name,item_category.category_name,item_category.batch_stock as stock_type,itemCat.category_name as parent_category";

        $queryData['leftJoin']['unit_master'] = "item_master.unit_id = unit_master.id";
        $queryData['leftJoin']['item_category'] = "item_category.id  = item_master.category_id";
        $queryData['leftJoin']['item_category itemCat'] = "itemCat.id  = item_category.ref_id";
        
        if(!empty($data['id'])):
            $queryData['where']['item_master.id'] = $data['id'];
        endif;

        if(!empty($data['item_code'])):
            $queryData['where']['item_master.item_code'] = trim($data['item_code']);
        endif;

        if(!empty($data['item_types'])):
            $queryData['where_in']['item_master.item_type'] = $data['item_types'];
        endif;
        
        if(!empty($data['customWhere'])):
            $queryData['customWhere'][] = $data['customWhere'];
        endif;

        if(!empty($data['item_name'])):
            $queryData['where']['item_master.item_name'] = $data['item_name'];
        endif;

        return $this->row($queryData);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            $customField = (!empty($data['customField']))?$data['customField']:array(); 
            unset($data['customField']);

            if($this->checkDuplicate($data) > 0):
                $errorMessage['item_name'] = "Item Name is duplicate.";
                return ['status'=>0,'message'=>$errorMessage];
            endif;
            if($this->checkDuplicateItemCode($data) > 0):
                $errorMessage['item_code'] = "Item Code is duplicate.";
                return ['status'=>0,'message'=>$errorMessage];
            endif;
            
			if($data['unit_id']){
				$untData = $this->itemUnit($data['unit_id']);
				$data['uom'] = (!empty($untData->unit_name))?$untData->unit_name:'NOS';
			}

            $result = $this->store($this->itemMaster,$data,"Item");

            if(!empty($customField)):
                $itemUdfData = $this->getItemUdfData(['item_id'=>$result['id']]); 
                $customField['item_id'] =$result['id'];       
                $customField['id'] = !empty($itemUdfData->id)?$itemUdfData->id :'';
                $this->store($this->item_udf,$customField);
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
        $queryData['tableName'] = $this->itemMaster;

        /*if(!empty($data['item_code']))
            $queryData['where']['item_code'] = $data['item_code'];*/
        if(!empty($data['item_name']))
            $queryData['where']['item_name'] = $data['item_name'];
        if(!empty($data['item_type']))
            $queryData['where']['item_type'] = $data['item_type'];
        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];

        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }
    
    public function checkDuplicateItemCode($data){
        $queryData['tableName'] = $this->itemMaster;

        if(!empty($data['item_code']))
            $queryData['where']['item_code'] = $data['item_code'];
        if(!empty($data['item_type']))
            $queryData['where']['item_type'] = $data['item_type'];
        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];

        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

    public function getItemUdfData($param = []){
		$queryData['tableName'] = $this->item_udf;
        if(!empty($param['item_id'])):
            $queryData['where']['item_udf.item_id'] = $param['item_id'];
        endif;
        return $this->row($queryData);
	}

    public function delete($id){
        try{
            $this->db->trans_begin();

            $checkData['columnName'] = ["item_id","scrap_group","ref_item_id"];
            $checkData['ignoreTable'] = ["product_process","item_kit"];
            $checkData['value'] = $id;
            $checkUsed = $this->checkUsage($checkData);
            
            if($checkUsed == true):
                return ['status'=>0,'message'=>'The Item is currently in use. you cannot delete it.'];
            endif;
            
            $this->trash("item_kit",['item_id'=>$id],'Item');
            $this->trash("product_process",['item_id'=>$id],'Item');
            
            $result = $this->trash($this->itemMaster,['id'=>$id],'Item');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function itemUnits(){
        $queryData['tableName'] = $this->unitMaster;
		return $this->rows($queryData);
	}

    public function itemUnit($id){
        $queryData['tableName'] = $this->unitMaster;
		$queryData['where']['id'] = $id;
		return $this->row($queryData);
	}
	
    public function getUnitNameWiseId($data=array()){
        $data['tableName'] = $this->unitMaster;
        if(!empty($data['unit_name'])){
            $data['where']['unit_name'] = $data['unit_name'];
        }
        return $this->row($data); 
    }

    public function getItemProcess($id){
		$data['tableName'] = $this->productProcess;
		$data['select'] = "product_process.*,process_master.process_name,item_master.item_code";
		$data['join']['process_master'] = "process_master.id = product_process.process_id";
		$data['leftJoin']['item_master'] = "item_master.id = product_process.item_id";
		$data['where']['product_process.item_id'] = $id;
		$data['order_by']['product_process.sequence'] = "ASC";
		return $this->rows($data);
	}
	
	/* Start Item Revision 
    Created By Rashmi @24-04-2024 */
	public function getItemRevision($data){
		$data['tableName'] = "item_revision";
        $data['select'] = "item_revision.*,";
        $data['leftJoin']['item_master'] = "item_master.id = item_revision.item_id";		
        if(!empty($data['item_id'])){
            $data['where']['item_revision.item_id'] = $data['item_id'];
        }
        if(!empty($data['is_active'])){
            $data['where']['item_revision.is_active'] = $data['is_active'];
        }
        $data['order_by']['item_revision.rev_no'] = 'ASC';
        if(!empty($data['single_row'])){
            return $this->row($data);
        }else{
            return $this->rows($data);
        }
		return $result;
	}

	public function saveItemRevision($data){
		try{
            $this->db->trans_begin();
            if($this->checkDuplicateRevNo($data) > 0):
				$errorMessage['rev_no'] = "Revision No. is duplicate.";
				return ['status'=>0,'message'=>$errorMessage];
			endif;
            $itemData = [
				'id'=>$data['id'],
				'item_id'=>$data['item_id'],
				'rev_no'=>$data['rev_no'],
				'rev_date'=>$data['rev_date'],
				'drawing_file'=>$data['drawing_file']
            ];
            $result = $this->store($this->item_revision,$itemData,'');
            $result = ['status'=>1,'message'=>'Item Revision saved successfully.'];
			if ($this->db->trans_status() !== FALSE):
				$this->db->trans_commit();
				return $result;
			endif;
		}catch(\Exception $e){
			$this->db->trans_rollback();
		   return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
		}	
	}

    public function checkDuplicateRevNo($data){
        $queryData['tableName'] = $this->item_revision;

        if(!empty($data['rev_no']))
            $queryData['where']['rev_no'] = $data['rev_no'];
        if(!empty($data['item_id']))
            $queryData['where']['item_id'] = $data['item_id'];
        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];

        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

    public function deleteItemRevision($id){
        try{
            $this->db->trans_begin();
		$result =  $this->trash('item_revision',['id'=>$id],'');
        if ($this->db->trans_status() !== FALSE):
            $this->db->trans_commit();
            return $result;
        endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
        return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
	}
	
	public function activeInactive($postData){
        try{
            $this->db->trans_begin();
            $revData = $this->getItemRevision(['item_id'=>$postData['item_id'],'is_active'=>1,'single_row'=>1]);

            if(!empty($revData)){
                $this->edit($this->item_revision, ['item_id' => $revData->item_id],['is_active' => 0]);
            }
            $result = $this->store($this->item_revision,$postData,'');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return['status'=>1,'message'=>"Item Revision ".(($postData['is_active'] == 1)?"Activated":"De-activated")." successfully."];
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }
    // End Item Revision

    /* Start Inspection 
    Created By Rashmi @24-04-2024 */
    public function getInspectionParam($id){ 
		$data['tableName'] = $this->inspectionParam;
		$data['select'] = "inspection_param.*,process_master.process_name,item_master.item_type";	
		$data['leftJoin']['item_master'] = "item_master.id = inspection_param.item_id";
		$data['leftJoin']['process_master'] = "process_master.id = inspection_param.process_id";
		$data['where']['inspection_param.item_id'] = $id;
		return $this->rows($data);
	}
	
	public function saveInspection($data){
		try{
            $this->db->trans_begin();
			$result = $this->store($this->inspectionParam,$data,'');
			if ($this->db->trans_status() !== FALSE):
				$this->db->trans_commit();
				return $result;
			endif;
		}catch(\Exception $e){
			$this->db->trans_rollback();
			return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
		}	
	}

	public function deleteInspection($id){
		try{
			$this->db->trans_begin();
			$result = $this->trash($this->inspectionParam,['id'=>$id],"Record");
			if ($this->db->trans_status() !== FALSE):
				$this->db->trans_commit();
				return $result;
			endif;
		}catch(\Exception $e){
			$this->db->trans_rollback();
			return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
		}	
	}
    /*End Inspection */

    /* Product Option */
        public function getProductProcessList($param = []){
    		$queryData['tableName'] = $this->productProcess;
    		$queryData['select'] = "product_process.id,product_process.item_id,product_process.process_id,product_process.cycle_time,product_process.finish_wt,process_master.process_name";
    		$queryData['leftJoin']['process_master'] = "process_master.id = product_process.process_id";
            $queryData['order_by']['product_process.sequence'] = 'ASC';
    		if(!empty($param['item_id'])){ $queryData['where']['product_process.item_id'] = $param['item_id']; }
    		if(!empty($param['process_id'])){ $queryData['where']['product_process.process_id'] = $param['process_id']; }
            if(!empty($param['single_row'])){
                return $this->row($queryData);
            }else{
                return $this->rows($queryData);
            }
    	}
    
        public function groupSearch($data){
    		$data['tableName'] = $this->itemKit;
    		$data['select'] = 'group_name';
    		$data['where']['item_id'] = $data['item_id'];
            $data['group_by'][]="group_name";
    		$result = $this->rows($data);
    		$searchResult = array();
			$searchResult[] = 'RM GROUP';
    		foreach ($result as $row) {
				if($row->group_name != 'RM GROUP'){ $searchResult[] = $row->group_name; }
    		}
    		return $searchResult;
    	}

        public function saveProductKit($data){
    		try{
                $this->db->trans_begin();
    
    			if($this->checkDuplicateBom($data) > 0):  
    				$errorMessage['kit_item_id'] = "Item Bom is duplicate.";
    				return ['status'=>0,'message'=>$errorMessage];
    			endif;
    
                $itemKitData = [
                    'id'=>$data['id'],
                    'group_name'=>$data['group_name'],
                    'item_id'=>$data['item_id'],
                    'ref_item_id'=>$data['kit_item_id'],
                    'process_id'=>$data['process_id'],
                    'qty'=>$data['kit_item_qty']
                ];
                $result = $this->store($this->itemKit,$itemKitData,'Product Bom');
    			
    			if ($this->db->trans_status() !== FALSE):
    				$this->db->trans_commit();
    				return $result;
    			endif;
    		}catch(\Exception $e){
    			$this->db->trans_rollback();
    		   return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
    		}	
    	}
    
        public function checkDuplicateBom($data){
            $queryData['tableName'] = $this->itemKit;
    
            if(!empty($data['kit_item_id']))
                $queryData['where']['ref_item_id'] = $data['kit_item_id'];
                
            if(!empty($data['process_id']))
                $queryData['where']['process_id'] = $data['process_id'];
    		
    		if(!empty($data['item_id']))
    			$queryData['where']['item_id'] = $data['item_id'];
    
            if(!empty($data['id']))
                $queryData['where']['id !='] = $data['id'];
    
            $queryData['resultType'] = "numRows";
            return $this->specificRow($queryData);
        }
    
        public function getProductKitData($param = []){
    		$data['tableName'] = $this->itemKit;
    		$data['select'] = "item_kit.*,item_master.item_name,item_master.item_code,IFNULL(process_master.process_name,'Initial Stage') as process_name,item_master.item_type,fg.item_name as product_name";
    		$data['leftJoin']['item_master'] = "item_master.id = item_kit.ref_item_id";
    		$data['join']['process_master'] = "process_master.id = item_kit.process_id";
    		$data['leftJoin']['item_master fg'] = "fg.id = item_kit.item_id";
            if(!empty($param['item_id'])){$data['where']['item_kit.item_id'] = $param['item_id'];}
            if(!empty($param['group_name'])){$data['where']['item_kit.group_name'] = $param['group_name'];}
            if(!empty($param['id'])){$data['where']['item_kit.id'] = $param['id'];}
            if(!empty($param['ref_item_id'])){$data['where']['item_kit.ref_item_id'] = $param['ref_item_id'];}
            if(!empty($param['process_id'])){$data['where_in']['item_kit.process_id'] = $param['process_id'];}
            
            if(!empty($param['is_delete']) && $param['is_delete'] == 'all'){
                $data['all']['item_kit.is_delete'] = '0,1';
            }
            if(!empty($param['group_by'])){
                $data['group_by'][] = $param['group_by'];
            }
            if(!empty($param['single_row'])){
                return $this->row($data);
            }else{
                return $this->rows($data);
            }
    		
    	}
    
        public function deleteProductKit($id){
            try{
    			$this->db->trans_begin();
    
    			$result = $this->trash($this->itemKit,['id'=>$id],'Product Bom');
    
    			if ($this->db->trans_status() !== FALSE):
    				$this->db->trans_commit();
    				return $result;
    			endif;
    		}catch(\Exception $e){
    			$this->db->trans_rollback();
    			return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
    		}	
    	}
    
        public function saveProductProcess($data){
    		try{
                $this->db->trans_begin();
    
        		$queryData['select'] = "process_id,id,sequence";
        		$queryData['where']['item_id'] = $data['item_id'];
        		$queryData['tableName'] = $this->productProcess;
        		$process_ids =  $this->rows($queryData);
        
        		$process = '';
        		if(!empty($data['process_id'])):
        			$process = explode(',',$data['process_id']);
        		endif;
        		$z=0;
        		foreach($process_ids as $key=>$value):
        			if(!in_array($value->process_id,$process)):
        			
        				$upProcess['tableName'] = $this->productProcess;
        				$upProcess['where']['item_id']=$data['item_id'];
        				$upProcess['where']['sequence > ']=($value->sequence - $z++);
        				$upProcess['where']['is_delete']=0;
        				$upProcess['set']['sequence']='sequence, - 1';
        				$q = $this->setValue($upProcess);
        				$this->remove($this->productProcess,['id'=>$value->id],'');
        			endif;
        		endforeach;
        		foreach($process as $key=>$value):			
        			if(!in_array($value,array_column($process_ids,'process_id'))):
        				$queryData = array();
        				$queryData['select'] = "MAX(sequence) as value";
        				$queryData['where']['item_id'] = $data['item_id'];
        				$queryData['where']['is_delete'] = 0;
        				$queryData['tableName'] = $this->productProcess;
        				$sequence = $this->specificRow($queryData)->value;
        				
        				$productProcessData = [
        					'id'=>"",
        					'item_id'=>$data['item_id'],
        					'process_id'=>$value,
        					'sequence'=>(!empty($sequence))?($sequence + 1):1,
        					'created_by' => $this->session->userdata('loginId')
        				];
        				$this->store($this->productProcess,$productProcessData,'');
        			endif;
        		endforeach;    
        
        		$result = ['status'=>1,'message'=>'Product process saved successfully.'];
    
        		if ($this->db->trans_status() !== FALSE):
        			$this->db->trans_commit();
        			return $result;
        		endif;
        	}catch(\Exception $e){
        		$this->db->trans_rollback();
        	    return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        	}	
    	}
    
        public function updateProductProcessSequance($data){
    		try{
                $this->db->trans_begin();
                
        		$ids = explode(',', $data['id']);
        		$i=1;
        		foreach($ids as $pp_id):
        			$seqData=Array("sequence"=>$i++);
        			$this->edit($this->productProcess,['id'=>$pp_id],$seqData);
        		endforeach;
    
        		$result = ['status'=>1,'message'=>'Process Sequence updated successfully.'];
    
        		if ($this->db->trans_status() !== FALSE):
        			$this->db->trans_commit();
        			return $result;
        		endif;
        	}catch(\Exception $e){
        		$this->db->trans_rollback();
        	    return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        	}	
    	}
    	
    	public function saveProductProcessCycleTime($data){
    		try{
                $this->db->trans_begin();
    
        		foreach($data['id'] as $key=>$value):
    				$productProcessData = ['id'=>$value,'cycle_time'=>$data['cycle_time'][$key],'finish_wt'=>$data['finish_wt'][$key],'updated_by'=>$data['loginId']];
    				$this->store($this->productProcess,$productProcessData,'');
        		endforeach;
        
        		$result = ['status'=>1,'message'=>'Cycle Time Updated successfully.'];
    			
        		if ($this->db->trans_status() !== FALSE):
        			$this->db->trans_commit();
        			return $result;
        		endif;
        	}catch(\Exception $e){
        		$this->db->trans_rollback();
        	   return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        	}	
    	}
    
        public function getProductProcessForSelect($id){
    		$data['select'] = "process_id";
    		$data['where']['item_id'] = $id;
    		$data['tableName'] = $this->productProcess;
    		$result = $this->rows($data);
    		$process = array();
    		if($result){foreach($result as $row){$process[] = $row->process_id;}}
    		return $process;
    	}
    	
    	public function saveStandards($data){
    		try{
                $this->db->trans_begin();
    
                if(!empty($data['attachment'])):
                    foreach($data['attachment'] as $key=>$value):
                        $standardData = ['id'=>$data['id'][$key],'attachment'=>$value,'updated_by'=>$data['loginId']];
                        $this->store($this->productProcess,$standardData,'');
                    endforeach;
                endif;
        
        		$result = ['status'=>1,'message'=>'Standards Updated successfully.'];
    			
        		if ($this->db->trans_status() !== FALSE):
        			$this->db->trans_commit();
        			return $result;
        		endif;
        	}catch(\Exception $e){
        		$this->db->trans_rollback();
        	   return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        	}	
    	}
    /* End Product Option */
    
    public function getPreInspectionParam($postData=[]){
		$data['tableName'] = $this->inspectionParam;
		$data['select'] = "inspection_param.*,item_master.item_name";
		$data['leftJoin']['item_master'] = "item_master.id = inspection_param.item_id";

		if(!empty($postData['item_id'])){ $data['where']['item_id'] = $postData['item_id']; }
        if(!empty($postData['control_method'])){  $data['where']['find_in_set("'.$postData['control_method'].'",REPLACE(control_method, " ", "")) > '] = 0;  }
		if(!empty($postData['id'])){ $data['where']['inspection_param.id'] = $postData['id']; } 
        if(!empty($postData['process_id'])){ $data['where']['inspection_param.process_id'] = $postData['process_id']; }
        $data['group_by'][] = "inspection_param.id";

        if(!empty($postData['single_row'])):
            return $this->row($data);
        else:
            return $this->rows($data);
        endif;
	}
	
	public function checkDuplicateParameter($data){
        $queryData['tableName'] = $this->inspectionParam;
        if(!empty($data['item_id']))
            $queryData['where']['item_id'] = $data['item_id'];
		if(!empty($data['rev_no']))
            $queryData['where']['rev_no'] = $data['rev_no'];
        if(isset($data['process_id']))
            $queryData['where']['process_id'] = $data['process_id'];
		if(!empty($data['parameter']))
            $queryData['where']['parameter'] = $data['parameter'];
		if(!empty($data['specification']))
            $queryData['where']['specification'] = $data['specification'];

        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];

        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

	public function saveInspectionParamExcel($postData){
		try{
            $this->db->trans_begin();
			
			foreach($postData as $data){
				if($this->checkDuplicateParameter($data) > 0){}else{
					$this->store($this->inspectionParam,$data,'Parameter');
				}
			}
			$result = ['status'=>1,'message'=>'Product process saved successfully.'];

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