<?php
class IncrementPolicy extends MY_Controller
{
    private $indexPage = "hr/increment_policy/index";
    private $formPage = "hr/increment_policy/form";
    
	public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "IncrementPolicy";
		$this->data['headData']->controller = "hr/incrementPolicy";
	}
	
	public function index(){
        $this->data['tableHeader'] = getHrDtHeader('incrementPolicy');
        $this->load->view($this->indexPage,$this->data);
    }

    public function getDTRows($status=0){
        $data = $this->input->post(); $data['status'] = $status;
        $result = $this->incrementPolicy->getDTRows($data);
        $sendData = array();$i=1;$count=0;
		foreach($result['data'] as $row):
			$row->sr_no = $i++;       
			$sendData[] = getIncrementPolicyData($row);
		endforeach;
		
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function addIncrementPolicy(){
        $this->data['nextPolicyNo'] = $this->incrementPolicy->nextPolicyNo();
		$this->data['cmList'] = $this->employee->getCompanyList($cm_id='');	
        $this->data['deptData'] = $this->department->getDepartmentList();
        $this->data['tbody'] = '';

        $this->load->view($this->formPage,$this->data);
    }

    public function getEmpSalaryDetails(){
		$data = $this->input->post();
		$empData = $this->employee->getEmployeeList(['emp_unit_id'=>$data['emp_unit_id'],'emp_dept_id'=>$data['dept_id']]);	

		$month = date('Y-m-d',strtotime($data['ref_month']));		
		$attendanceData = $this->biometric->getSalaryHours(['from_date'=>date('Y-m-01',strtotime($month)),'to_date'=>date('Y-m-t',strtotime($month)),'datewise'=>1]);	
			
		$i=1; $tbody="";
		if(!empty($empData)):
			foreach($empData as $row): 
				$twh = (!empty($attendanceData[$row->id]['twh']))?$attendanceData[$row->id]['twh']:0;
				$twh = sprintf('%0.2f',round(($twh/3600),2));
                $monthSalary = sprintf('%0.2f',round(($row->sal_amount * $row->hrs_day),2));
				$tbody .= '<tr>
					<td>'.$i.'</td>
					<td>'.$row->emp_name.'</td>
					<td>'.$row->emp_code.'</td>
					<td>'.$row->department_name.'</td>
					<td>'.$row->sal_amount.'</td>
					<td>'.$row->hrs_day.'</td>
					<td>'. $monthSalary.'</td>
					<td>'.$twh.'</td>
					<td>'.sprintf('%0.2f',round(($row->sal_amount * $twh),2)).'</td>
                    <td>
                        <input type="hidden" name="id[]" value="">
                        <input type="hidden" name="emp_id[]" value="'.$row->id.'">
                        <input type="hidden" name="hrs_day[]" value="'.$row->hrs_day.'">
                        <input type="hidden" name="month_salary[]" value="'.$monthSalary.'">
                        <input type="hidden" name="sal_amount[]" value="'.$row->sal_amount.'">
                        <input type="text" name="monthly_hours[]" id="monthly_hours'.$i.'" data-row_id="'.$i.'" class="form-control rate_calc floatOnly" value="">
                    </td>
                    <td><input type="text" name="monthly_salary[]" id="monthly_salary'.$i.'" data-row_id="'.$i.'" class="form-control rate_calc floatOnly" value=""></td>
                    <td><input type="text" name="rate_hour[]" id="rate_hour'.$i.'" class="form-control floatOnly" value="" readonly></td>
				</tr>'; $i++;
			endforeach;
		endif;
		
		$this->printJson(['status'=>1, 'tbody'=>$tbody]);
	}

    public function save(){
        $data = $this->input->post();
        $errorMessage = array();
        if(empty($data['policy_name']))
            $errorMessage['policy_name'] = "Policy name is required.";
    
        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
			$data['effect_date'] = date('Y-m-d',strtotime($data['effect_date']));
			$data['ref_month'] = date('Y-m-d',strtotime($data['ref_month']));
            $data['created_by'] = $this->session->userdata('loginId');
            
            $this->printJson($this->incrementPolicy->save($data));
        endif;
    }

    public function edit($policy_no){ 
        $this->data['nextPolicyNo'] = $this->incrementPolicy->nextPolicyNo();
		$this->data['cmList'] = $this->employee->getCompanyList($cm_id='');	
        $this->data['deptData'] = $this->department->getDepartmentList();
        $this->data['dataRow']= $dataRow = $this->incrementPolicy->getIncrementPolicy($policy_no);

        $i=1; $tbody="";
		if(!empty($dataRow)):
			foreach($dataRow as $row): 
                $month = date('Y-m-d',strtotime($row->ref_month));		
                $attendanceData = $this->biometric->getSalaryHours(['from_date'=>date('Y-m-01',strtotime($month)),'to_date'=>date('Y-m-t',strtotime($month)),'datewise'=>1]);	
                    
				$twh = (!empty($attendanceData[$row->emp_id]['twh']))?$attendanceData[$row->emp_id]['twh']:0;
				$twh = sprintf('%0.2f',round(($twh/3600),2));
				$tbody .= '<tr>
					<td>'.$i.'</td>
					<td>'.$row->emp_name.'</td>
					<td>'.$row->emp_code.'</td>
					<td>'.$row->name.'</td>
					<td>'.$row->old_rate_hour.'</td>
					<td>'.$row->old_monthly_hours.'</td>
					<td>'. $row->old_monthly_salary.'</td>
					<td>'.$twh.'</td>
					<td>'.sprintf('%0.2f',round(($row->old_rate_hour * $twh),2)).'</td>
                    <td>
                        <input type="hidden" name="id[]" value="'.$row->id.'">
                        <input type="hidden" name="emp_id[]" value="'.$row->emp_id.'">
                        <input type="hidden" name="hrs_day[]" value="'.$row->old_monthly_hours.'">
                        <input type="hidden" name="month_salary[]" value="'.$row->old_monthly_salary.'">
                        <input type="hidden" name="sal_amount[]" value="'.$row->old_rate_hour.'">
                        <input type="text" name="monthly_hours[]" id="monthly_hours'.$i.'" data-row_id="'.$i.'" class="form-control rate_calc floatOnly" value="'.$row->monthly_hours.'">
                    </td>
                    <td><input type="text" name="monthly_salary[]" id="monthly_salary'.$i.'" data-row_id="'.$i.'" class="form-control rate_calc floatOnly" value="'.$row->monthly_salary.'"></td>
                    <td><input type="text" name="rate_hour[]" id="rate_hour'.$i.'" class="form-control floatOnly" value="'.$row->rate_hour.'" readonly></td>
				</tr>'; $i++;
			endforeach;
        $this->data['tbody'] = $tbody;

		endif;

        $this->load->view($this->formPage,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->incrementPolicy->delete($id));
        endif;
    }

    public function policyApply(){
		$data = $this->input->post(); 
		if(empty($data['id'])):
			$this->printJson(['status'=>0,'message'=>'Something went wrong...Please try again.']);
		else:
			$this->printJson($this->incrementPolicy->policyApply($data));
		endif;
	}

}
?>