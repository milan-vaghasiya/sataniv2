<?php
class RejectionParameter extends MY_Controller{
    private $indexPage = "rejection_parameter/index";
    private $formPage = "rejection_parameter/form";
	    
	public function __construct(){
		parent::__construct();
		$this->data['headData']->pageTitle = "Rejection Parameter";
		$this->data['headData']->controller = "rejectionParameter";
		$this->data['headData']->pageUrl = "rejectionParameter";
	}
	
	public function index(){
        $this->data['tableHeader'] = getConfigDtHeader('rejectionParameter');
        $this->load->view($this->indexPage,$this->data);
    }
	
	public function getDTRows(){
        $data = $this->input->post();
        $result = $this->rejectionParameter->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):          
            $row->sr_no = $i++;
            $sendData[] = getRejectionParameterData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }
	
    public function addRejectionParameter(){
        $this->load->view($this->formPage,$this->data);
    }
    
    public function save(){
        $data = $this->input->post();
        $errorMessage = array();
        if(empty($data['parameter']))
            $errorMessage['parameter'] = "Rejection Parameter is required.";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $this->printJson($this->rejectionParameter->save($data));
        endif;
    }

    public function edit(){
        $data = $this->input->post();
        $this->data['dataRow'] = $this->rejectionParameter->getRejectionParameter($data);
        $this->load->view($this->formPage,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->rejectionParameter->delete($id));
        endif;
    }
    
}
?>