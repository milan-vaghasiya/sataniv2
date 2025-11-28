<?php
class ExtraHours extends MY_Controller
{
    private $indexPage = "hr/extrahours/index";
    private $manualForm = "hr/extrahours/form";

	public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "Extra Hours";
		$this->data['headData']->controller = "hr/extraHours";
	}
	
	public function index(){
        $this->data['tableHeader'] = getHrDtHeader("extraHours");
        $this->load->view($this->indexPage,$this->data);
    }
	
    public function getDTRows($status=0){
        $data = $this->input->post();
        $data['status']=$status;
        $result = $this->extraHours->getDTRows($data); 
        $sendData = array();$i=1;
        foreach($result['data'] as $row):          
            $row->sr_no = $i++;
            $row->loginID = $this->loginId;
            $sendData[] = getExtraHoursData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function addExtraHours(){
        $this->data['empData'] = $this->leave->getEmpData($this->session->userdata('loginId'));
        $this->data['empList'] = $this->employee->getEmployeeList();
        $this->data['loginID'] = $this->session->userdata('loginId');
        $this->load->view($this->manualForm,$this->data);
    }

    public function save(){
        $data = $this->input->post();
		$errorMessage = array();		
        if(empty($data['emp_id']))
			$errorMessage['emp_id'] = "Employee is required.";
        if(empty($data['punch_date']))
			$errorMessage['punch_date'] = "Attendance Date Time is required.";
        if(empty($data['ex_hours']) && empty($data['ex_mins']))
			$errorMessage['ex_hours'] = "Extra Hours is required.";
        if(empty($data['remark']))
			$errorMessage['remark'] = "Reason is required.";
		
        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            
            $empData = $this->employee->getEmployee(['id'=>$data['emp_id']]);
            $data['created_by'] = $this->session->userdata('loginId');
            $data['emp_code'] = (!empty($empData)) ? $empData->emp_code : '';
			
            $this->printJson($this->extraHours->save($data));
        endif;
    }

    public function edit(){     
        $id = $this->input->post('id');
        $this->data['empData'] = $this->leave->getEmpData($this->session->userdata('loginId'));
        $this->data['empList'] = $this->employee->getEmployeeList();
        $this->data['dataRow'] = $this->extraHours->getExtraHours($id);
        $this->data['loginID'] = $this->session->userdata('loginId');
        $this->load->view($this->manualForm,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->extraHours->delete($id));
        endif;
    }

    public function getXHRSDetail(){     
        $id = $this->input->post('id');
        $xData = $this->extraHours->getExtraHours($id);
        $this->data['xData'] = $xData ;
        $this->load->view("hr/extrahours/approve_xhr",$this->data);
    }

    public function approveXHRS(){     
        $postData = $this->input->post();
        $postData['approved_at'] = date('Y-m-d H:i:s');
        $postData['approved_by'] = $this->loginId;
        $this->printJson($this->extraHours->approveXHRS($postData));
    }
}
?>