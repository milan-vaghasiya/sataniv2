<?php
class RejectionType extends MY_Controller{
    private $indexPage = "rejection_type/index";
    private $rejectionTypeForm = "rejection_type/form";
	    
	public function __construct(){
		parent::__construct();
		$this->data['headData']->pageTitle = "Rejection TYpe";
		$this->data['headData']->controller = "rejectionType";
		$this->data['headData']->pageUrl = "rejectionType";
	}
	
	public function index(){
        $this->data['tableHeader'] = getConfigDtHeader('rejectionType');
        $this->load->view($this->indexPage,$this->data);
    }
	
	public function getDTRows(){
        $data = $this->input->post();
        $result = $this->rejectionType->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):          
            $row->sr_no = $i++;
            $sendData[] = getRejectionTypeData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }
	
    public function addRejectionType(){
        $this->data['categoryData'] = $this->deptCategory;
        $this->load->view($this->rejectionTypeForm,$this->data);
    }
    
    public function save(){
        $data = $this->input->post();
        $errorMessage = array();
        if(empty($data['rejection_type']))
            $errorMessage['rejection_type'] = "Rejection Type is required.";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $this->printJson($this->rejectionType->save($data));
        endif;
    }

    public function edit(){
        $data = $this->input->post();
        $this->data['dataRow'] = $this->rejectionType->getRejectionType($data);
        $this->load->view($this->rejectionTypeForm,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->rejectionType->delete($id));
        endif;
    }
    
}
?>