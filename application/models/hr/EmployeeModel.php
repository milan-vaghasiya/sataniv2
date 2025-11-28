<?php
class EmployeeModel extends MasterModel{
    private $empMaster = "employee_master";
    private $empDocuments = "emp_docs";
    private $empNom = "emp_nomination_detail";
    private $empEdu = "emp_education_detail";
    private $canteen_trans = "canteen_trans";
    private $canteen_appointment = "canteen_appointment";
    private $leaveMaster = 'leave_master';
	private $advance_salary = 'advance_salary';

    // Created By JP@30.10.2023
    public function getNewEmpCode($postData){
        
        $data['tableName'] = $this->empMaster;
        $data['select'] = "MAX(emp_code) as emp_code";
        $data['where']['unit_id'] = $postData['unit_id'];
        $data['where']['employee_master.is_delete != '] = 1;
        $maxEmpCode = $this->specificRow($data)->emp_code;
		
		if(empty($maxEmpCode))
		{
			// IF Office Staff OR Trainee
			if($postData['unit_id'] == 1){$nextEmpCode = 1;} // Satani Forge & Turn (Forging Unit)
			if($postData['unit_id'] == 2){$nextEmpCode = 3001;} // Satani Industries
			if($postData['unit_id'] == 3){$nextEmpCode = 5001;} // Satani Hot Former
			if($postData['unit_id'] == 4){$nextEmpCode = 4001;} // Satani Techno Rings
			if($postData['unit_id'] == 5){$nextEmpCode = 2001;} // Satani Forge & Turn (CNC Unit)
		}else{
			$nextEmpCode = (intVal($maxEmpCode) + 1);
		}
        return $nextEmpCode;
    }

    public function getDTRows($data){
        $data['tableName'] = $this->empMaster;

        $data['select'] = "employee_master.*,department_master.name as dept_name,emp_designation.title as emp_designation,emp_category.category as emp_category, IFNULL(company_info.company_name,' - ') as cmp_name, IFNULL(company_info.company_alias,' - ') as cmp_alias";

        $data['leftJoin']['department_master'] = "employee_master.emp_dept_id = department_master.id";
        $data['leftJoin']['emp_designation'] = "employee_master.emp_designation = emp_designation.id";
        $data['leftJoin']['emp_category'] = "employee_master.emp_category = emp_category.id";
        $data['leftJoin']['company_info'] = "company_info.id = employee_master.unit_id";
        
        $data['where']['employee_master.emp_role !='] = "-1";

		if($data['status']==0):
            $data['where']['employee_master.is_active']=1;
        else:
            $data['where']['employee_master.is_active']=0;
        endif;
        
         if(!empty($data['cm_id'])){ $data['where']['employee_master.unit_id'] = $data['cm_id']; }
         
        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "employee_master.emp_name";
        $data['searchCol'][] = "employee_master.emp_code";
        $data['searchCol'][] = "company_info.company_alias";
        $data['searchCol'][] = "department_master.name";
        $data['searchCol'][] = "emp_designation.title";
        $data['searchCol'][] = "emp_category.category";
        $data['searchCol'][] = "employee_master.emp_contact";
        
        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        
        return $this->pagingRows($data);
    }

    public function getNextEmpNo(){
        $queryData['tableName'] = $this->empMaster;
        $queryData['select'] = "ifnull((MAX(biomatric_id) + 1),1) as biomatric_id";
        $nextNo = $this->specificRow($queryData)->biomatric_id;
		return $nextNo;
    }

    public function getEmployeeList($data=array()){
        $queryData['tableName'] = $this->empMaster;
        $queryData['select'] = "employee_master.*,department_master.name as department_name,emp_designation.title as designation_name,company_info.company_name,company_info.company_address,company_info.company_phone,emp_category.category";
        $queryData['leftJoin']['department_master'] = "employee_master.emp_dept_id = department_master.id";
        $queryData['leftJoin']['emp_designation'] = "employee_master.emp_designation = emp_designation.id";
        $queryData['leftJoin']['emp_category'] = "employee_master.emp_category = emp_category.id";
        $queryData['leftJoin']['company_info'] = "company_info.id = employee_master.unit_id";
		
        if(!empty($data['emp_role'])):
            $queryData['where_in']['employee_master.emp_role'] = $data['emp_role'];
        endif;
        
        if(!empty($data['emp_not_role'])):
            $queryData['where_not_in']['employee_master.emp_role'] = $data['emp_not_role'];
        endif;

        if(!empty($data['emp_sys_desc_id'])):
            $queryData['where']['find_in_set("'.$data['emp_sys_desc_id'].'", emp_sys_desc_id) >'] = 0;
        endif;

        if(!empty($data['emp_designation'])):
            $queryData['where']['employee_master.emp_designation'] = $data['emp_designation'];
        endif;

        if(!empty($data['is_active'])):
            $queryData['where_in']['employee_master.is_active'] = $data['is_active'];
        endif;

        if(!empty($data['biomatric_id'])){$queryData['where']['employee_master.biomatric_id'] = $data['biomatric_id'];}
        if(!empty($data['emp_grade'])){$queryData['where_in']['employee_master.emp_grade'] = $data['emp_grade'];}
        if(!empty($data['id'])){ $queryData['where_in']['employee_master.id'] = $data['id']; }
        if(!empty($data['emp_dept_id'])){ $queryData['where']['employee_master.emp_dept_id'] = $data['emp_dept_id']; }
        if(!empty($data['cm_id'])){ $queryData['where']['employee_master.cm_id'] = $data['cm_id']; }
        if(!empty($data['emp_unit_id'])){ $queryData['where']['employee_master.unit_id'] = $data['emp_unit_id']; }
        if(empty($data['all'])):
            $queryData['where']['employee_master.emp_role !='] = "-1";
        endif;

        return $this->rows($queryData);
    }

    //12-04-2024
    public function getEmployee($data){
        $queryData['tableName'] = $this->empMaster;
        $queryData['select'] = "employee_master.*,department_master.name as department_name,emp_designation.title as designation_name";
        $queryData['leftJoin']['department_master'] = "employee_master.emp_dept_id = department_master.id";
        $queryData['leftJoin']['emp_designation'] = "employee_master.emp_designation = emp_designation.id";
        if(!empty($data['id'])){
            $queryData['where']['employee_master.id'] = $data['id'];
        }
        if(!empty($data['emp_name'])){
            $data['where']['emp_name'] = $data['emp_name'];
        }
        return $this->row($queryData);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            if($this->checkDuplicate($data) > 0):
                $errorMessage['emp_contact'] = "Contact no. is duplicate.";
                return ['status'=>0,'message'=>$errorMessage];
            endif;

            if(empty($data['id'])):
                $data['emp_psc'] = $data['emp_password'];
                $data['emp_password'] = md5($data['emp_password']); 
            endif;

            $result =  $this->store($this->empMaster,$data,'Employee');
            
            $emp_id = (!empty($data['id'])) ? $data['id'] : $result['insert_id'];
				
            // Add Shift log if New Employee
            if(empty($data['id']) AND !empty($emp_id))
            {
                $shiftLog = $this->biometric->addShiftLog($emp_id);
            }

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
        $queryData['tableName'] = $this->empMaster;
        $queryData['where']['emp_contact'] = $data['emp_contact'];
        
        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];
        
        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

    public function delete($id){
        try{
            $this->db->trans_begin();

            $checkData['columnName'] = ['created_by','updated_by'];
            $checkData['value'] = $id;
            $checkUsed = $this->checkUsage($checkData);

            if($checkUsed == true):
                return ['status'=>0,'message'=>'The Shift is currently in use. you cannot delete it.'];
            endif;

            $this->trash($this->empDocuments,['emp_id'=>$id],'Employee');
            $this->trash($this->empNom,['emp_id'=>$id],'Employee');
            $this->trash($this->empEdu,['emp_id'=>$id],'Employee');

            $result = $this->trash($this->empMaster,['id'=>$id],'Employee');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function activeInactive($postData){
        try{
            $this->db->trans_begin();

            $result = $this->store($this->empMaster,$postData,'');
            
            if($postData['is_active'] == 1 && !empty($postData['id'])){
                $shiftLog = $this->biometric->addShiftLog($postData['id']);
            }
            
            $result['message'] = "Employee ".(($postData['is_active'] == 1)?"Activated":"De-activated")." successfully.";
            
            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function changePassword($data){
        try{
            $this->db->trans_begin();

            if(empty($data['id'])):
                return ['status'=>2,'message'=>'Somthing went wrong...Please try again.'];
            endif;

            $empData = $this->getEmployee(['id'=>$data['id']]);
            if(md5($data['old_password']) != $empData->emp_password):
                $result = ['status'=>0,'message'=>['old_password'=>"Old password not match."]];
            endif;

            $postData = ['id'=>$data['id'],'emp_password'=>md5($data['new_password']),'emp_psc'=>$data['new_password']];
            $result = $this->store($this->empMaster,$postData);
            $result['message'] = "Password changed successfully.";

            if($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function resetPassword($id){
        try{
            $this->db->trans_begin();

            $data['id'] = $id;
            $data['emp_psc'] = '123456';
            $data['emp_password'] = md5($data['emp_psc']); 
            
            $result = $this->store($this->empMaster,$data);
            $result['message'] = 'Password Reset successfully.';

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
	}

    public function editProfile($data){
        try{
            $this->db->trans_begin();
            $form_type = $data['form_type']; unset($data['form_type'], $data['designationTitle']);
            $empData = $this->getEmployee(['id'=>$data['id']]);

            if(in_array($form_type,['updateProfilePic','personalDetail','workprofile'])):
                $result = $this->store($this->empMaster,$data,'Employee');
            endif;
            
            if($form_type == 'workprofile'):
                $relieveData = array();
                $relieveData['emp_id'] = (!empty($data['id'])) ? $data['id'] : $result['id'];
                $relieveData['emp_joining_date'] = $data['emp_joining_date'];
                $relieveData['emp_relieve_date'] = '';
                $relieveData['reason'] = '';
                $relieveData['is_delete'] = 0;
                if (empty($data['id'])):
                    $this->saveReliveDetailJson($relieveData);
                else:
                    if (!empty($empData->relieve_detail)):
                        $jsonData = json_decode($empData->relieve_detail);
                        $relieveArr = array();
        
                        $joiningDate = array();
                        foreach ($jsonData as $row):
                            $joiningDate[] = $row->emp_relieve_date;
                            if ($empData->emp_joining_date == $row->emp_joining_date):
                                $relieveArr[] = [
                                    'emp_joining_date' => $data['emp_joining_date'],
                                    'emp_relieve_date' => '',
                                    'reason' => ''
                                ];
                            else :
                                $relieveArr[] = $row;
                            endif;
                        endforeach;
                        $max = max(array_map('strtotime', $joiningDate));
                        if ($data['emp_joining_date'] > date('Y-m-d', $max)) :
                            $this->edit($this->empMaster, ['id' => $data['id']], ['relieve_detail' => json_encode($relieveArr), 'emp_joining_date' => $data['emp_joining_date']]);
                        else :
                            $errorMessage['emp_joining_date'] = "Sorry you can not edit joining date beacuse joining date is less then last relieve date";
                            return ['status' => 0, 'message' => $errorMessage];
                        endif;
                    else :
                        $this->saveReliveDetailJson($relieveData);
                    endif;
                endif;
            endif;

            if($form_type == "empDocs"):
                $result = $this->store($this->empDocuments,$data,'Employee Document');
            endif;

            if($form_type == "empNomination"):
                $result = $this->store($this->empNom,$data,'Employee Nomination');
            endif;

            if($form_type == "empEdu"):
                $result = $this->store($this->empEdu,$data,'Employee Education');
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

    public function removeProfileDetails($data){
        try{
            $this->db->trans_begin();

            if($data['type'] == "empDocs"):
                if(empty($data['id'])):
                    return ['status'=>0,'message'=>'Somthing went wrong...Please try again.'];
                else:
                    $queryData = array();
                    $queryData['tableName'] = $this->empDocuments;
                    $queryData['where']['id'] = $data['id'];
                    $docDetail = $this->row($queryData);

                    $filePath = realpath(APPPATH . '../assets/uploads/emp_documents/');
                    if(!empty($docDetail->doc_file) && file_exists($filePath.'/'.$docDetail->doc_file)):
                        unlink($filePath.'/'.$docDetail->doc_file);
                    endif;

                    $result = $this->trash($this->empDocuments,['id'=>$data['id'],'emp_id'=>$data['emp_id']],"Employee Document");
                endif;
            endif;

            if($data['type'] == "empNomination"):
                $result = $this->trash($this->empNom,['id'=>$data['id']],"Employee Nomination");
            endif;

            if($data['type'] == "empEdu"):
                $result = $this->trash($this->empEdu,['id'=>$data['id']],"Employee Education");
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

    public function getEmpDocuments($data){
        $queryData['tableName'] = $this->empDocuments;
        $queryData['select'] = 'emp_docs.*,(CASE WHEN doc_type = 1 THEN "Extra Documents" WHEN doc_type = 2 THEN "Aadhar Card"  WHEN doc_type = 3 THEN "Basic Rules" ELSE "" END) as doc_type_name';
        $queryData['where']['emp_id']=$data['emp_id'];		
        return $this->rows($queryData);
    }

    public function getNominationData($data){
		$queryData['where']['emp_id'] = $data['emp_id'];
		$queryData['tableName'] = $this->empNom;
		return $this->rows($queryData);
	}

    public function getEducationData($data){
		$queryData['where']['emp_id'] = $data['emp_id'];
		$queryData['tableName'] = $this->empEdu;
		return $this->rows($queryData);
	}

    public function saveReliveDetailJson($data){
        try{
            $this->db->trans_begin();

            $empQuery['tableName'] = $this->empMaster;
            $empQuery['where']['id'] = $data['emp_id'];
            $empQuery['where']['employee_master.is_delete'] = $data['is_delete'];
            $empData = $this->row($empQuery);

            if (!empty($empData->emp_relieve_date) && !empty($data['emp_joining_date']) && $empData->emp_relieve_date > $data['emp_joining_date']) :
                $errorMessage['emp_joining_date'] = "Your joining date is less then last relieve date";
                return ['status' => 0, 'message' => $errorMessage];
            endif;

            $relieveArr = array();
            if (!empty($empData->relieve_detail)):
                $relieveArr = json_decode($empData->relieve_detail);
            endif;

            $relieveArr[] = [
                'emp_joining_date' => $data['emp_joining_date'],
                'emp_relieve_date' => $data['emp_relieve_date'],
                'reason' => $data['reason']
            ];
            $joining_date = '';

            if (!empty($data['emp_joining_date'])):
                $joining_date = $data['emp_joining_date'];
            else:
                $joining_date = $empData->emp_joining_date;
            endif;

            $result = $this->edit($this->empMaster, ['id' => $data['emp_id']], ['relieve_detail' => json_encode($relieveArr), 'emp_joining_date' => $joining_date]);

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function saveEmpReliveJoinData($data){
        try{
            $this->db->trans_begin();

            if (!empty($data['emp_joining_date'])) {
                $empQuery['tableName'] = $this->empMaster;
                $empQuery['where']['id'] = $data['id'];
                $empQuery['where']['is_delete'] = 2;
                
                $empData = $this->row($empQuery);

                if (!empty($empData->emp_relieve_date) && $empData->emp_relieve_date > $data['emp_joining_date']) {
                    $errorMessage['emp_joining_date'] = "Your joining date is less then last relieve date";
                    return ['status' => 0, 'message' => $errorMessage];
                }
            }
            
            // Add Shift log
            if(!empty($data['id'])){
                $this->biometric->addShiftLog($data['id']);
            }
            
            $this->edit($this->empMaster, ['id' => $data['id']], ['is_delete' => $data['is_delete'], 'emp_relieve_date' => $data['emp_relieve_date']]);
            $relieveData = array();
            $relieveData['emp_id'] = $data['id'];
            $relieveData['emp_joining_date'] = $data['emp_joining_date'];
            $relieveData['emp_relieve_date'] = $data['emp_relieve_date'];
            $relieveData['reason'] = $data['reason'];
            $relieveData['is_delete'] = $data['is_delete'];
            $result = $this->saveReliveDetailJson($relieveData);

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
    }

    public function getRelievedEmpDTRows($data){
        $data['tableName'] = $this->empMaster;
        $data['select'] = "employee_master.*,department_master.name,emp_designation.title";
        $data['where']['employee_master.emp_name!='] = "Admin";
        $data['where']['employee_master.is_delete'] = 2;
        $data['leftJoin']['department_master'] = "employee_master.emp_dept_id = department_master.id";
        $data['leftJoin']['emp_designation'] = "employee_master.emp_designation = emp_designation.id";
        $data['order_by']['employee_master.emp_code'] = "ASC";
		
        $data['searchCol'][] = "employee_master.emp_name";
        $data['searchCol'][] = "employee_master.emp_contact";
        $data['searchCol'][] = "department_master.name";
        $data['searchCol'][] = "emp_designation.title";
        $data['searchCol'][] = "employee_master.emp_code";
        $data['searchCol'][] = "DATE_FORMAT(employee_master.emp_relieve_date,'%d-%m-%Y')";
		
		$columns = array('', '', 'employee_master.emp_name', 'employee_master.emp_code', 'employee_master.emp_contact', 'department_master.name', 'emp_designation.title', 'employee_master.emp_relieve_date');
        if (isset($data['order'])) {
            $data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];
        }
        return $this->pagingRows($data);
    }

    public function getDeviceForEmployee(){
        $data['tableName'] ="device_master";
        $data['select'] = "device_master.*";
        // $data['leftJoin']['emp_designation'] = "employee_master.emp_designation = emp_designation.id";
        // $data['where']['employee_master.emp_role !='] = "-1";
        // $data['where']['is_active'] = 1;
        return $this->rows($data);
    }

    public function addEmployeeInDevice($id,$empId){
        try{
            $this->db->trans_begin();
            $empData = $this->getEmployee(['id'=>$empId]);
            $data['tableName'] ="device_master";
            $data['select'] = "device_master.*";
            $data['where']['id'] = $id;
            $deviceData=$this->row($data);
            $empDevice="";
            if(!empty($empData->device_id))
            {
                $empDevice=$empData->device_id.','.$id;
            }
            else
            {
                $empDevice=$id;
            }
            if(empty($empData->emp_code)):
                return ['status'=>0,'message'=>'Employee code not found.'];
            endif;
            $empCode = $deviceData->Empcode = trim($empData->emp_code);
            $empName = $deviceData->emp_name = str_replace(" ","%20",$empData->emp_name);
            //print_r($deviceData); exit;
            $deviceResponse = $this->biometric->addEmpDevice($deviceData);
            //print_r($deviceResponse); exit;
            if($deviceResponse['status'] == 0):
                $result = ['status'=>0,'message'=>'cURL Error #: ' . $deviceResponse['result']]; 
            else:
                $responseData = json_decode($deviceResponse['result']);
            
                if(!empty($responseData)):
                    if($responseData->Error == false):
                        $this->edit($this->empMaster,['id'=>$empData->id],['biomatric_id'=>$empData->emp_code,'device_id'=>$empDevice]);
                        $result = ['status'=>1,'message'=>'Employee added scucessfully.','CURLResponse'=>$responseData]; 
                    else:
                        $result = ['status'=>0,'message'=>'Somthing is wrong or Device is offline. Employee can not added.','CURLResponse'=>$responseData];
                    endif;
                else:
                    $result = ['status'=>0,'message'=>'cURL Error #: Device is offline or somthing else. Employee can not added.','CURLResponse'=>$responseData];
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
    
    public function removeEmployeeInDevice($id,$empId){
        try{
            $this->db->trans_begin();
            $empData = $this->getEmp($empId);
            $data['tableName'] ="device_master";
            $data['select'] = "device_master.*";
            $data['where']['id'] = $id;
            $deviceData=$this->row($data);
            $empDeviceArr=Array();$empDevice="";
            if(!empty($empData->device_id))
            {
                $ed = explode(',',$empData->device_id);
                foreach($ed as $e){if($e != $id){$empDeviceArr[]=$e;}}
            }
            else
            {
                $empDeviceArr[]=$id;
            }
            $empDevice=implode(',',$empDeviceArr);
            //if(empty($empData->emp_code)):
            if(empty($empData->old_emp_code)):
                return ['status'=>0,'message'=>'Employee code not found.'];
            endif;
            //$empCode = $deviceData->Empcode = trim($empData->emp_code);
            $empCode = $deviceData->Empcode = trim($empData->old_emp_code);
            $empName = $deviceData->emp_name = str_replace(" ","%20",$empData->emp_name);
            
            $deviceResponse = $this->biometric->removeEmpDevice($deviceData);
            
            if($deviceResponse['status'] == 0):
                $result = ['status'=>0,'message'=>'cURL Error #: ' . $deviceResponse['result']]; 
            else:
                $responseData = json_decode($deviceResponse['result']);
            
                if(!empty($responseData)):
                    if($responseData->Error == false):
                        $this->edit($this->empMaster,['id'=>$empData->id],['device_id'=>(!empty($empDevice)?$empDevice:NULL)]);
                        $result = ['status'=>1,'message'=>'Employee deleted scucessfully.','CURLResponse'=>$responseData]; 
                    else:
                        $result = ['status'=>0,'message'=>'Somthing is wrong or Device is offline. Employee can not added.','CURLResponse'=>$responseData];
                    endif;
                else:
                    $result = ['status'=>0,'message'=>'cURL Error #: Device is offline or somthing else. Employee can not added.','CURLResponse'=>$responseData];
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

    public function saveEmpSalaryStructure($data){
        try{
            $this->db->trans_begin();
			
            $structure = Array('salary_type'=>$data['salary_type'],'monthly_salary'=>$data['monthly_salary'],'sal_amount'=>$data['sal_amount'],'pf_on'=>$data['pf_on'],'pf_limit'=>$data['pf_limit'],'fix_pf'=>$data['fix_pf'],'hrs_day'=>$data['hrs_day']);
            $this->edit('employee_master',['id'=>$data['ctc_emp_id']],$structure);            
            
            $structure['id']="";
            $qData['tableName'] = "salary_structure";
            $qData['where']['emp_id'] = $data['ctc_emp_id'];
            $oldRec = $this->row($qData);
            if(!empty($oldRec->id)){$structure['id']=$oldRec->id;}else{$structure['id']="";}
            
            $structure['emp_id'] = $data['ctc_emp_id'];
            $structure['emp_type'] = $data['ctc_emp_type'];
            $structure['pf_per'] = $data['pf_per'];
            $structure['pf_status'] = $data['pf_status'];
            $structure['cm_id'] = $data['cm_id'];
            
            $result = $this->store("salary_structure",$structure,'Employee Structure');
            
            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return ['status'=>1,'message'=>'Salary updated successfully'];
            endif;
            
        }catch(\Exception $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

    public function getEmpIdByCode($emp_code){
        $data['tableName'] = $this->empMaster;
        $data['select'] = "employee_master.id";
        $data['where']['employee_master.emp_code'] = $emp_code;
        return $this->row($data);
    }

    public function getEmpListV1($postData=[]){
        $data['tableName'] = $this->empMaster;
        $data['where_in']['is_active'] = '0,1';
        $data['where']['attendance_status'] = 1;
        $data['where']['employee_master.id !='] = 1;
        if(!empty($postData['emp_id'])){
            $data['where']['employee_master.id'] = $postData['emp_id'];
            $data['where_in']['is_active'] = '1';
        }
        if(!empty($postData['is_active'])){$data['where_in']['is_active'] = $postData['is_active'];}
        return $this->rows($data);
    }

    public function getEmpCanteenData($postData){
		$queryData['tableName'] = $this->canteen_trans;
		$queryData['select'] = 'canteen_trans.*,employee_master.emp_code,employee_master.emp_name';
		$queryData['leftJoin']['employee_master'] = 'employee_master.id = canteen_trans.emp_id';
		if(!empty($postData['trans_type'])){ $queryData['where']['trans_type'] = $postData['trans_type']; }
		if(!empty($postData['emp_id'])){ $queryData['where']['emp_id'] = $postData['emp_id']; }
		if(!empty($postData['emp_unit_id'])){ $queryData['where']['employee_master.unit_id'] = $postData['emp_unit_id']; }
        $queryData['customWhere'][] = "DATE(canteen_trans.created_at) BETWEEN '".$postData['from_date']."' AND '".$postData['to_date']."'";
		$queryData['order_by']['canteen_trans.created_at'] = 'DESC';
        if(isset($postData['single_row']) && $postData['single_row'] == 1){
            return $this->row($queryData);
        }else{
		    return $this->rows($queryData);
        }
    }

    /*** Created By JP @09-12-2022 ***/
	public function getEmpListForReport($postData = []){
		$empQuery['select'] = "employee_master.id, employee_master.emp_code,employee_master.emp_name, employee_master.shift_id, department_master.name as dept_name, emp_designation.title as emp_dsg, emp_category.category, IFNULL(company_info.company_alias,'-') as cmp_name";
        $empQuery['leftJoin']['emp_designation'] = "emp_designation.id = employee_master.emp_designation";
        $empQuery['leftJoin']['department_master'] = "employee_master.emp_dept_id = department_master.id";
        $empQuery['leftJoin']['emp_category'] = "employee_master.emp_category = emp_category.id";
        $empQuery['leftJoin']['company_info'] = "company_info.id = employee_master.cm_id";
        
        if(!empty($postData['currentDate']))
        {
            $day = date('d',strtotime($postData['currentDate']));$month = date('m',strtotime($postData['currentDate']));$year = date('Y',strtotime($postData['currentDate']));
            $empQuery['select'] .= ",emp_shiftlog.d".intval($day)." as currentShift,ifnull(shift_master.shift_name,'') as lastShift";
            $empQuery['leftJoin']['emp_shiftlog'] = "employee_master.id = emp_shiftlog.emp_id";
            $empQuery['leftJoin']['shift_master'] = "emp_shiftlog.d".intval($day)." = shift_master.id";
            $empQuery['where']['MONTH(emp_shiftlog.month)'] = $month;
            $empQuery['where']['YEAR(emp_shiftlog.month)'] = $year;
        }
		//$empQuery['where']['employee_master.id!='] = 1;
		if(!empty($postData['biomatric_id'])){$empQuery['where']['employee_master.biomatric_id'] = $postData['biomatric_id'];}
		else{$empQuery['where']['employee_master.biomatric_id > '] = 0;}
		
		if(!in_array($this->userRole,[1,-1,7])) { $empQuery['where']['employee_master.id'] = $this->loginId; }
		
		if(!empty($postData['emp_unit_id'])){ $empQuery['where']['employee_master.unit_id'] = $postData['emp_unit_id']; }
		
		$empQuery['where']['employee_master.shift_id > '] = 0;
		$empQuery['where']['employee_master.is_active'] = 1;
        if(!empty($postData['deleted'])){$data['all']['employee_master.is_delete'] = [0,1];}
		$empQuery['order_by']['employee_master.emp_code'] = 'ASC';
        $empQuery['tableName'] = $this->empMaster;
		$empData = $this->rows($empQuery);
		//$this->printQuery();
		return $empData;
	}

    /* Leave Report */
    public function getLeaveReportData($postData){
        $data['tableName'] = $this->leaveMaster;
        $data['select'] = "leave_master.*,employee_master.emp_code,employee_master.emp_name,employee_master.emp_dept_id,employee_master.unit_id,department_master.name,company_info.company_name";
        $data['leftJoin']['employee_master'] = "employee_master.id = leave_master.emp_id";
        $data['leftJoin']['department_master'] = "department_master.id = employee_master.emp_dept_id";
        $data['leftJoin']['company_info'] = "company_info.id = employee_master.unit_id";
        
		if(!empty($postData['emp_id'])){ $data['where']['leave_master.emp_id'] = $postData['emp_id']; }
		if(!empty($postData['dept_id'])){ $data['where']['employee_master.emp_dept_id'] = $postData['dept_id']; }
		if(!empty($postData['unit_id'])){ $data['where']['employee_master.unit_id'] = $postData['unit_id']; }

        $data['customWhere'][] = "DATE(leave_master.start_date) BETWEEN '".$postData['from_date']."' AND '".$postData['to_date']."'";

        return $this->rows($data);
    }

    /* Facility Report */
    public function getFacilityReportData($postData){
        $data['tableName'] = $this->advance_salary;
        $data['select'] = "advance_salary.*,employee_master.emp_name,employee_master.emp_code,sanction.emp_name as sanctioned_by_name,(advance_salary.sanctioned_amount - advance_salary.deposit_amount) as pending_amount,facility_master.ficility_type,company_info.company_name,department_master.name";
        $data['leftJoin']['employee_master'] = "advance_salary.emp_id = employee_master.id";
        $data['leftJoin']['employee_master as sanction'] = "advance_salary.sanctioned_by = sanction.id";
        $data['leftJoin']['facility_master'] = "advance_salary.facility_id = facility_master.id";
        $data['leftJoin']['company_info'] = "company_info.id = employee_master.unit_id";
        $data['leftJoin']['department_master'] = "department_master.id = employee_master.emp_dept_id";
        $data['where']['advance_salary.type'] = 3;
        
		if(!empty($postData['emp_id'])){ $data['where']['advance_salary.emp_id'] = $postData['emp_id']; }
		if(!empty($postData['dept_id'])){ $data['where']['employee_master.emp_dept_id'] = $postData['dept_id']; }
		if(!empty($postData['unit_id'])){ $data['where']['employee_master.unit_id'] = $postData['unit_id']; }

        $data['customWhere'][] = "DATE(advance_salary.entry_date) BETWEEN '".$postData['from_date']."' AND '".$postData['to_date']."'";

        return $this->rows($data);
    }
    
    public function getEmpListForAttendance($postData = []){
		$queryData['tableName'] = $this->empMaster;
		$queryData['select'] = "employee_master.id, employee_master.emp_role, employee_master.emp_code, employee_master.emp_category, employee_master.biomatric_id, employee_master.cm_id, employee_master.emp_name, employee_master.emp_dept_id, employee_master.pf_applicable, employee_master.salary_type, employee_master.hrs_day, employee_master.sal_amount, employee_master.fix_pf, employee_master.pf_on, employee_master.pf_limit, employee_master.attendance_type, department_master.name as dept_name, emp_designation.title as emp_designation";
		$queryData['leftJoin']['department_master'] = "employee_master.emp_dept_id = department_master.id";
		$queryData['leftJoin']['emp_designation'] = "employee_master.emp_designation = emp_designation.id";
		$queryData['where_in']['employee_master.is_active'] = '0,1';
		if(!empty($postData['from_date'])){$queryData['where']['emp_joining_date <= '] = $postData['to_date'];}
		$queryData['where']['employee_master.id !='] = 1;
		if(!empty($postData['deleted'])){$queryData['all']['employee_master.is_delete'] = '0,1';}
		if(!empty($postData['biomatric_id'])){$queryData['where']['employee_master.biomatric_id'] = $postData['biomatric_id'];}
		if(!empty($postData['dept_id'])){$queryData['where']['employee_master.emp_dept_id'] = $postData['dept_id'];}
		if(!empty($postData['cm_id'])){$queryData['where']['employee_master.cm_id'] = $postData['cm_id'];}
		if(!empty($postData['designation_id'])){$queryData['where']['employee_master.emp_designation'] = $postData['designation_id'];}
		
		// Get Advance/Loan/Canteen Charge
		if(!empty($postData['month']))
		{
			$loanMonth = date('Ym',strtotime($postData['month']));
			$queryData['select'] .= ", IFNULL(advance.advance_sal,0) as advance_salary, IFNULL(empLoan.pendingLoan,0) as pending_loan";
    		$queryData['leftJoin']['(SELECT SUM(advance_salary.sanctioned_amount - advance_salary.deposit_amount) as advance_sal, emp_id FROM advance_salary WHERE is_delete = 0 AND  entry_date <= "'.$postData['month'].'" group by emp_id) as advance'] = "advance.emp_id = employee_master.id";
    		$queryData['leftJoin']["(SELECT emp_id,SUM(CASE WHEN (((PERIOD_DIFF('".$loanMonth."',DATE_FORMAT(emp_loan.sanctioned_at, '%Y%m'))+1) * emp_loan.emi_amount) > emp_loan.deposit_amount) THEN (((PERIOD_DIFF('".$loanMonth."',DATE_FORMAT(emp_loan.sanctioned_at, '%Y%m'))+1) * emp_loan.emi_amount) - emp_loan.deposit_amount) ELSE 0 END) AS pendingLoan FROM emp_loan WHERE is_delete=0 AND trans_status=2 GROUP BY emp_id) as empLoan"] = "empLoan.emp_id = employee_master.id";
			$queryData['leftJoin']['salary_structure'] = "employee_master.id = salary_structure.emp_id";
			$queryData['where']['salary_structure.emp_id != '] = 0;
	
		}
		$result = $this->rows($queryData);
		return $result;
	}
	
	public function getActiveSalaryStructure($emp_id){
        $queryData = array();
        $queryData['tableName'] = "salary_structure";
        $queryData['where']['emp_id'] = $emp_id;
        $queryData['where']['is_active'] = 1;
        $queryData['order_by']['id'] = "DESC";
        $result = $this->row($queryData);
        return $result;
    }
	
	public function getEmpByCode($emp_code,$is_active='1'){
        $data['tableName'] = $this->empMaster;
        $data['select'] = "employee_master.id, employee_master.emp_code, employee_master.emp_name, employee_master.emp_profile, department_master.name as dept_name, emp_designation.title as emp_designation, IFNULL(company_info.company_alias,' - ') as cmp_alias";
        $data['leftJoin']['department_master'] = "employee_master.emp_dept_id = department_master.id";
        $data['leftJoin']['emp_designation'] = "employee_master.emp_designation = emp_designation.id";
        $data['leftJoin']['company_info'] = "company_info.id = employee_master.cm_id";
        $data['where']['employee_master.emp_code'] = $emp_code;
        $data['where_in']['is_active'] = $is_active;
        return $this->row($data);
    }
}
?>