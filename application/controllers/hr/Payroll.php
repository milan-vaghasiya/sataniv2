<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class Payroll extends MY_Controller
{
    private $indexPage = "hr/payroll/index";
    private $payrollForm = "hr/payroll/form";
    private $editEmpSalaryForm = "hr/payroll/edit_emp_salary_form";
    private $payrollView = "hr/payroll/view";
    private $payrollDataPage = "hr/payroll/payroll_data";
    private $importForm = "hr/payroll/import_form";
	
	public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "Payroll";
		$this->data['headData']->controller = "hr/payroll";
		$this->data['headData']->pageUrl = "hr/payroll";
	}
	
	public function index(){
        $this->data['tableHeader'] = getHrDtHeader('payroll');
        $this->load->view($this->indexPage,$this->data);
    }
	
	public function getDTRows(){
        $result = $this->payroll->getDTRows($this->input->post());
		$sendData = array();$i=1;
        foreach($result['data'] as $row):      
			$row->sr_no = $i++;
			//$row->salary_sum = $this->payroll->getSalarySumByMonth($row->month)->salary_sum;
            $sendData[] = getPayrollData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }
	
    public function loadSalaryForm(){
        $start = new DateTime($this->startYearDate);
        $start->modify('first day of this month');
        $end = new DateTime($this->endYearDate);
        $end->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $monthList = array();
        foreach ($period as $dt): $monthList[] = $dt->format("Y-m-t"); endforeach;

        $this->data['monthList'] = (object) $monthList;
        $this->data['deptRows'] = $this->department->getDepartmentList();
        $this->data['ctcFormat'] = $this->salaryStructure->getCtcFormats();
        //$this->data['empData'] = $this->payroll->getEmpSalary();
        $this->data['companyList'] = $this->employee->getCompanyList();
        $this->load->view($this->payrollForm,$this->data);
    }
    
    public function getPayrollData($month){
        $this->data['empData'] = $this->payroll->getPayrollData($month);
        $this->load->view($this->payrollDataPage,$this->data);
    }

    public function makeSalary(){
        $this->data['empData'] = $this->payroll->getEmpSalary();
        $this->load->view($this->payrollForm,$this->data);
    }
    
    public function save(){
        $data = $this->input->post(); 
        $errorMessage = array();
        if(empty($data['month']))
            $errorMessage['month'] = "Month is required.";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
			//unset($data['month']);
            $data['created_by'] = $this->session->userdata('loginId');
            $this->printJson($this->payroll->save($data));
        endif;
    }

    public function edit($month){
        $start = new DateTime($this->startYearDate);
        $start->modify('first day of this month');
        $end = new DateTime($this->endYearDate);
        $end->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $monthList = array();
        foreach ($period as $dt): $monthList[] = $dt->format("Y-m-t"); endforeach;

        $this->data['monthList'] = (object) $monthList;
        $this->data['deptRows'] = $this->department->getDepartmentList();
        $this->data['ctcFormat'] = $this->salaryStructure->getCtcFormats();
        
        $salaryData = $this->payroll->getPayrollData($month);
        $ctcFormat = $this->salaryStructure->getCtcFromat($salaryData[0]->format_id);
        $this->data['earningHeads'] = $this->salaryStructure->getSalaryHeadList(['type'=>1,'ids'=>$ctcFormat->eh_ids]);
        $this->data['deductionHeads'] = $this->salaryStructure->getSalaryHeadList(['type'=>-1,'ids'=>$ctcFormat->dh_ids]);
        $this->data['salaryData'] = $salaryData;
        //print_r($salaryData);exit;
        $this->load->view($this->payrollForm,$this->data);
    }

    public function delete(){
        $data = $this->input->post();
        if(empty($data['sal_month'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->payroll->delete($data['sal_month'],$data['dept_id']));
        endif;
    }

    /*************************** Load Salary Data***************************************/    
    public function getEmployeeSalaryData($dept_id="",$month="",$cm_id="",$file_type="pdf"){
        if($_SERVER['REQUEST_METHOD'] === 'POST'):
            $data = $this->input->post();
        else:
            $data['dept_id'] = $dept_id;
            $data['month'] = $month;
            $data['cm_id'] = $cm_id;
            $data['file_type'] = $file_type;
            $data['view'] = 1;
        endif;
		$data['dates'] = date('Y-m-01',strtotime($data['month'])) .'~'.$data['month'];
		$sal_month = $data['from_date'] = date('Y-m-01',strtotime($data['month']));
		$data['to_date'] = date('Y-m-t',strtotime($data['month']));
		
		
        $headCount = (empty($data['view']))?12:11;
        $eth = '';$betd = '';
        $dth = '';$bdtd = '';
        $thead = '<tr class="text-center">
						<th>Emp Code</th>
						<th>Emp Name</th>
						<th>Hours</th>
						<th>Wages/Hour</th>
						<th>Total<br>Earning</th>
						<th>Other<br>Allowances</th>
						<th>Gross Salary</th>
						<th>Advance</th>
						<th>Canteen</th>
						<th>PT</th>
						<th>PF</th>
						<th>Loan EMI</th>
						<th>Other<br>Deduction</th>
						<th>Gross<br>Deduction</th>
						<th>Net Salary</th>
						<!--<th>Action</th>-->
					</tr>';
		
        $empData = $this->employee->getEmpListForAttendance($data);
        $empAttendanceData = $this->biometric->getSalaryHours(['from_date'=>$data['month'],'dept_id'=>$data['dept_id'],'payroll'=>1]);
		
        $html = '';$sr_no=1; $empTable='';$hiddenInputs='';$saveButton='';
        $totalDays = date("t",strtotime($data['month'])); 
        if(!empty($empData))
		{
            foreach($empData as $row)
			{
				$row = (Array) $row;
				$row['total_days'] = $totalDays;
				$row['present'] = 0;
				$row['absent'] = 0;
				$row['week_off'] = $row['total_days'] - ($row['present'] + $row['absent']);
				$row['sal_month'] = $sal_month;
				$row['advance_salary'] = (!empty($row['advance_salary'])) ? $row['advance_salary'] : 0;
				$row['loan_emi'] = (!empty($row['pending_loan'])) ? $row['pending_loan'] : 0;
				$row['total_hrs'] = 0;
				$row['ot_hrs'] = 0;
				$row['wh_hrs'] = $row['total_hrs'] - $row['ot_hrs'];
				
				
				if(!empty($empAttendanceData[$row['id']]))
				{
					$shData = $empAttendanceData[$row['id']];
					$row['present'] = (!empty($shData['tpd'])) ? $shData['tpd'] : 0;
					$row['absent'] = (!empty($shData['tad'])) ? $shData['tad'] : 0;
					$row['total_hrs'] = (!empty($shData['twh'])) ? round((abs($shData['twh'])/3600),2) : 0;
					$row['ot_hrs'] = (!empty($shData['tot'])) ? round((abs($shData['tot'])/3600),2) : 0;
				}
				
				$salData = $this->countSalary($row);
				
				if(!empty($salData))
				{
					$empTable .= '<tr class="text-center emp_line'.$row['id'].'">';
						$empTable .= '<td style="text-align:center;">'.$salData['emp_code'].'</td>';
						$empTable .= '<td>'.$salData['emp_name'].'</td>';
						$empTable .= '<td>'.$salData['total_hrs'].'</td>';
						$empTable .= '<td>'.$salData['wages'].'</td>';
						$empTable .= '<td>'.$salData['basic_salary'].'</td>';
						$empTable .= '<td>'.$salData['other_allowance'].'</td>';
						$empTable .= '<th id="gross_sal'.$salData['emp_id'].'" class="bg-light-green">'.$salData['gross_sal'].'</th>';
						$empTable .= '<td>'.$salData['advance_salary'].'</td>';
						$empTable .= '<td>'.$salData['food'].'</td>';
						$empTable .= '<td>'.$salData['pt'].'</td>';
						$empTable .= '<td>'.$salData['emp_pf'].'</td>';
						$empTable .= '<td>'.$salData['loan_emi'].'</td>';
						$empTable .= '<td>'.$salData['other_deduction'].'</td>';
						$empTable .= '<th id="gross_deduction'.$salData['emp_id'].'" class="bg-light-green">'.$salData['gross_deduction'].'</th>';
						$empTable .= '<th id="net_salary'.$salData['emp_id'].'" class="bg-warning">'.$salData['net_salary'].'</th>';
						//$empTable .= '<th><button type="button" class="btn btn-primary float-right reCalculatecSal" data-id="'.$row['id'].'" >Save</button></th>';
					$empTable .= '</tr>';
				}
				unset($salData['present'],$salData['emp_code'],$salData['emp_name']);
				$hiddenInputs.="<div class='hiddenDiv".$row['id']."'>";
				foreach($salData as $key=>$val)
				{
					$name = 'salary_data['.$row['id'].']['.$key.']';
					$id = $key.$row['id'];
					$hiddenInputs.="<input type='hidden' name='".$name."' id='".$id."' class='salData".$row['id']."' alt='".$key."' value='".$val."'>";
				}
				$hiddenInputs.="</div>";
                $sr_no++;
            }
			$hiddenInputs.="<input type='hidden' name='salary_month' id='salary_month' value='".$sal_month."'>";
			$saveParam = "'savePayRoll'";
			$saveButton = '<button type="button" class="btn btn-success btn-block save-form float-right" onclick="savePayRoll('.$saveParam.');"><i class="fa fa-check"></i> Save</button>';
        }
		else
		{
            if(empty($data['view'])):
                /*$html = '<tr>
                    <td id="noData" class="text-center" colspan="'.$headCount.'">No data available in table</td>
                </tr>';*/
            endif;
        }
        
        if($_SERVER['REQUEST_METHOD'] === 'POST')
		{
            $this->printJson(['status'=>1,'emp_salary_head'=>$thead,'emp_salary_html'=>$empTable,'hidden_inputs'=>$hiddenInputs,"save_button"=>$saveButton]);
        }
		else
		{
            $response = '<table class="table-bordered jpExcelTable" border="1" repeat_header="1">';
            $response .= '<thead>'.$thead.'</thead><tbody>'.$empTable.'</tbody></table>';
            if($data['file_type'] == 'excel')
			{
				$xls_filename = 'payroll-'.date("m-Y",strtotime($data['month'])).'.xls';
				
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment; filename='.$xls_filename);
				header('Pragma: no-cache');
				header('Expires: 0');
				
				echo $response;
			}
			else
			{
			    $companyData = $this->attendance->getCompanyInfo();
				$htmlHeader = '<div class="table-wrapper">
                    <table class="table txInvHead">
                        <tr class="txRow">
                            <td class="fs-17 text-left" style="letter-spacing: 1px;font-weight:bold;">'.$companyData->company_name.'</td>
                            <td class="text-right pad-right-10"><b>Report Month : '.date("F-Y",strtotime($data['month'])).'</td>
                        </tr>
                    </table>
                </div>';
				$htmlFooter = '<table style="border-top:1px solid #000;padding:3px;">
                    <tr>
                        <td style="width:50%;text-align:left;">Printed On {DATE j-m-Y}</td>
                        <td style="width:50%;text-align:right;">Page No :- {PAGENO}</td>
                    </tr>
                </table>';
				
				$mpdf = new \Mpdf\Mpdf();
				$pdfFileName='payroll-'.date("m-Y",strtotime($data['month'])).'.xls';
				$stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
				$mpdf->WriteHTML($stylesheet,1);
				$mpdf->SetDisplayMode('fullpage');
				$mpdf->SetProtection(array('print'));
				
				$mpdf->SetHTMLHeader($htmlHeader);
				$mpdf->SetHTMLFooter($htmlFooter);
				
				$mpdf->AddPage('L','','','','',5,5,17,10,5,0,'','','','','','','','','','A4-L');
				$mpdf->WriteHTML($response);
				$mpdf->Output($pdfFileName,'I');
			}
        }
    }
    
	/**** Created By JP@01.09.2023 ***/
	public function countSalary($postData){
		
        $salData = Array();$html = '';$strData = '';
        if(!empty($postData['id']))
		{
			$salConfig = new stdClass();$oldStructureId = 0;
			$actStr = $this->employee->getActiveSalaryStructure($postData['id']);
			
			if(!empty($actStr))
			{
				$salConfig = $actStr;
			
				$salAmount = (!empty($salConfig->sal_amount)) ? $salConfig->sal_amount : 0;
				$hrs_day = (!empty($salConfig->hrs_day)) ? $salConfig->hrs_day : 0;
				$other_allowance = (!empty($salConfig->other_allowance)) ? $salConfig->other_allowance : 0;
				$other_deduction = (!empty($salConfig->other_deduction)) ? $salConfig->other_deduction : 0;
				$food = (!empty($salConfig->food)) ? $salConfig->food : 0;
				$salConfig->pt_limit = 12000; // IF GROSS SALARY > 12000 THEN 200 ELSE 0
				
				if(empty($postData['attendance_type'])){$postData['present']=$postData['total_days'] - $postData['week_off'];}
				$workingDays = $postData['total_days'] - $postData['week_off'];
				
				$basic_salary = 0; $dailyBasic = 0; $rate_hour = 0; $grossSal = 0; $grossDeduction = 0; $ot_amt=0; $emp_pf = 0;
				
				if($salConfig->salary_type == 2)
				{
					$dailyBasic = (!empty($salAmount)) ? round(($salAmount * $hrs_day),2) : 0 ;
					$rate_hour = $salAmount ;
					$basic_salary = round($salAmount * $postData['total_hrs']);
				}
				else
				{
					$dailyBasic = (!empty($workingDays)) ? round((floor($salAmount / $workingDays)),2) : 0 ;
					$rate_hour = (!empty($dailyBasic)) ? round(($dailyBasic / $hrs_day),2) : 0 ;
					$basic_salary = ($workingDays == $postData['present']) ? $salAmount : round($dailyBasic * $postData['present']);
				}
				
				// OT
				$ot_amt = round($rate_hour * $postData['ot_hrs']);
				
				$gross_sal = $basic_salary + $other_allowance;
				
				// If PF LIMIT SET THEN CHECK
				if($salConfig->pf_status == 1)
				{
					if(empty($salConfig->fix_pf) OR $salConfig->fix_pf == 0)
					{
						$pfCalcOn = ($salConfig->pf_on == 1) ? ($basic_salary + $other_allowance) : $basic_salary;
						if(!empty($salConfig->pf_limit) AND $salConfig->pf_limit > 0)
						{
							$pfCalcOn = ($pfCalcOn < $salConfig->pf_limit) ? $pfCalcOn : $salConfig->pf_limit;
						}
						
						$emp_pf = round((($pfCalcOn * $salConfig->pf_per)/100),0);						
					}
					else{$emp_pf = $salConfig->fix_pf;}
				}
				$cmp_pf = $emp_pf;				
				
				$pt = ($gross_sal > $salConfig->pt_limit)  ? 200 : 0;
				$gross_deduction = $emp_pf + $pt + $postData['advance_salary'] + $postData['loan_emi'] + $other_deduction;				
									
				$net_salary = $gross_sal - $gross_deduction;				
				
				$salData['id'] = "";
				$salData['ss_id'] = $salConfig->id;
				$salData['sal_month'] = $postData['sal_month'];
				$salData['emp_id'] = $postData['id'];
				$salData['emp_name'] = $postData['emp_name'];
				$salData['emp_code'] = $postData['emp_code'];
				$salData['total_days'] = $postData['total_days'];
				$salData['present'] = $postData['present'];
				$salData['absent'] = $postData['absent'];
				$salData['week_off'] = $postData['week_off'];
				$salData['total_hrs'] = $postData['total_hrs'];
				$salData['ot_hrs'] = $postData['ot_hrs'];
				$salData['salary_type'] = $salConfig->salary_type;
				$salData['wages'] = $salAmount;
				$salData['basic_salary'] = $basic_salary;
				$salData['other_allowance'] = $other_allowance;
				$salData['gross_sal'] = $gross_sal;
				$salData['emp_pf'] = $emp_pf;
				$salData['cmp_pf'] = $cmp_pf;
				$salData['pt'] = $pt;
				$salData['food'] = $food;
				$salData['advance_salary'] = $postData['advance_salary'];
				$salData['loan_emi'] = $postData['loan_emi'];
				$salData['other_deduction'] = $other_deduction;
				$salData['gross_deduction'] = $gross_deduction;
				$salData['net_salary'] = $net_salary;
			}
		}
		return $salData;
    }

	public function reCalculatecSal(){
		$postData = $this->input->post();
		$hiddenInputs = '';$empTable ='';//print_r($postData);exit;
        $salData = Array();$html = '';$strData = '';//print_r($postData);exit;
        if(!empty($postData['id']))
		{
			$salConfig = new stdClass();$oldStructureId = 0;
			$actStr = $this->employee->getActiveSalaryStructure($postData['id']);
			
			if(!empty($actStr))
			{
				$salConfig = $actStr;
			
				$salAmount = (!empty($salConfig->sal_amount)) ? $salConfig->sal_amount : 0;
				$basicSalary = (!empty($salConfig->basic_salary)) ? $salConfig->basic_salary : 0;
				$grossSal = (!empty($salConfig->gross_sal)) ? $salConfig->gross_sal : 0;
				$transport_charge = (!empty($actStr)) ? $salConfig->transport_charge : 0;
				$adv_bonus = (!empty($salConfig->adv_bonus)) ? $salConfig->adv_bonus : 0;
				$tds = (!empty($salConfig->tds)) ? $salConfig->tds : 0;			
				$salConfig->pt_limit = 12000; // IF GROSS SALARY > 12000 THEN 200 ELSE 0
				$postData['total_days'] = date("t",strtotime($postData['sal_month'])); 
				$food = $postData['food'];$hourly_wage=0;$wh_day=8;$ot_amt=0;
							
				if($salConfig->salary_type == 1)
				{
					$other_all = 0;$emp_pf = 0;$emp_esic = 0;$cmp_esic = 0;$pl_used=0;$cl_used=0;$bonus = 1800;
					//$postData['present']=$postData['total_days'] - $postData['wo'];
					$totalDays = $postData['present'] + $postData['wo'];
					
					$totalDays += $postData['pl'];$pl_used=$postData['pl'];
					$totalDays += $postData['cl'];$cl_used=$postData['cl'];
											
					$dailyBasic = (!empty($postData['total_days'])) ? round((floor($basicSalary / $postData['total_days'])),2) : 0 ;
					$basic_salary = ($totalDays == $postData['total_days']) ? $basicSalary : round($dailyBasic * $totalDays);
					
					$dailyGross = (!empty($postData['total_days'])) ? round($grossSal / $postData['total_days']) : 0 ;
					$gross_sal = ($totalDays == $postData['total_days']) ? $grossSal : round($dailyGross * $totalDays);
					
					$hra_amt = round((($basic_salary * $salConfig->hra_per)/100),0);
					$other_all = $gross_sal - $basic_salary - $hra_amt;
					
					// OT
					$hourly_wage = round($dailyGross / $wh_day);
					$ot_amt = round($hourly_wage * $postData['ot_hrs']);
					
					$gross_sal += $ot_amt;
					
					// If PF LIMIT SET THEN CHECK
					$pfCalcOn = ($salConfig->pf_on == 1) ? ($basic_salary + $other_all) : $basic_salary;
					if(!empty($salConfig->pf_limit) AND $salConfig->pf_limit > 0)
					{
						$pfCalcOn = ($pfCalcOn < $salConfig->pf_limit) ? $pfCalcOn : $salConfig->pf_limit;
					}
					else
					{
						$pfCalcOn = $pfCalcOn;
					}
					if($salConfig->pf_status == 1){$emp_pf = round((($pfCalcOn * $salConfig->pf_per)/100),0);}
					$cmp_pf = $emp_pf;
					
					// Calc ESIC
					if(!empty($salConfig->esic_status)){$emp_esic = round((($gross_sal*$salConfig->emp_esic_per)/100),0);}
					if(!empty($salConfig->esic_status)){$cmp_esic = round((($gross_sal*$salConfig->cmp_esic_per)/100),0);}
					
					$pt = ($gross_sal > $salConfig->pt_limit)  ? 200 : 0;
					$gross_deduction = $emp_pf + $emp_esic + $pt + $tds + $salConfig->transport_charge + $food + $postData['advance_salary'] + $postData['loan_emi'];				
					
					$gratuity = (!empty($salConfig->gratuity)) ? round((($basic_salary*$salConfig->gratuity)/100),0) : 0;
					
					$net_salary = $gross_sal - $gross_deduction +  $adv_bonus;
					
					
				}
				if(in_array($salConfig->salary_type,[2,3]))
				{
					$other_all = 0;$emp_pf = 0;$emp_esic = 0;$cmp_esic = 0;$pl_used=0;$cl_used=0;$bonus = 1800;$basic_salary = 0;$hra_amt = 0;
					//$postData['present']=$postData['total_days'] - $postData['wo'];
					$totalDays = $postData['present'];
					
					if($salConfig->salary_type == 3)
					{
						$postData['total_days'] = date("t",strtotime($postData['sal_month'])); 
						$totalDays = $postData['present'] + $postData['wo'];
						
						$totalDays += $postData['pl'];$pl_used=$postData['pl'];
						$totalDays += $postData['cl'];$cl_used=$postData['cl'];
						
						$otherBasic = (!empty($postData['total_days'])) ? round((floor($other_all / $postData['total_days'])),2) : 0 ;
						$other_all = (!empty($salConfig->other_all)) ? $salConfig->other_all : 0;
						
						$dailyBasic = (!empty($postData['total_days'])) ? round((floor($basicSalary / $postData['total_days'])),2) : 0 ;
						$basic_salary = ($totalDays == $postData['total_days']) ? $basicSalary : round($dailyBasic * $totalDays);
						
						$dailyHRA = (!empty($postData['total_days'])) ? round($salConfig->hra_amt / $postData['total_days']) : 0 ;
						$hra_amt = ($totalDays == $postData['total_days']) ? $salConfig->hra_amt : round($dailyHRA * $totalDays);
						
						$hourly_wage = round(($dailyBasic + $dailyHRA + $otherBasic)  / $wh_day);
						$ot_amt = round($hourly_wage * $postData['ot_hrs']);
						
					}
					else
					{
						$dailyBasic = $basic_salary = (round($basicSalary * $totalDays));
						$hra_amt = (round($salConfig->hra_amt * $totalDays));
						$hourly_wage = round(($basicSalary + $salConfig->hra_amt)  / $wh_day);
						$ot_amt = round($hourly_wage * $postData['ot_hrs']);
					}
					
					
					$gross_sal = $basic_salary + $hra_amt + $ot_amt + $other_all;
					
					// If PF LIMIT SET THEN CHECK
					$pfCalcOn = ($salConfig->pf_on == 1) ? ($basic_salary + $other_all) : $basic_salary;
					if(!empty($salConfig->pf_limit) AND $salConfig->pf_limit > 0)
					{
						$pfCalcOn = ($pfCalcOn < $salConfig->pf_limit) ? $pfCalcOn : $salConfig->pf_limit;
					}
					else
					{
						$pfCalcOn = $pfCalcOn;
					}
					if($salConfig->pf_status == 1){$emp_pf = round((($pfCalcOn * $salConfig->pf_per)/100),0);}
					$cmp_pf = $emp_pf;
					
					// Calc ESIC
					if(!empty($salConfig->esic_status)){$emp_esic = round((($gross_sal*$salConfig->emp_esic_per)/100),0);}
					if(!empty($salConfig->esic_status)){$cmp_esic = round((($gross_sal*$salConfig->cmp_esic_per)/100),0);}
					
					$pt = ($gross_sal > $salConfig->pt_limit)  ? 200 : 0;
					$gross_deduction = $emp_pf + $emp_esic + $pt + $tds + $salConfig->transport_charge + $food + $postData['advance_salary'] + $postData['loan_emi'];				
					
					$gratuity = (!empty($salConfig->gratuity)) ? round((($basic_salary*$salConfig->gratuity)/100),0) : 0;
					
					$net_salary = $gross_sal - $gross_deduction +  $adv_bonus;
				}
				$salData['id'] = $postData['total_days'];
				$salData['ss_id'] = $salConfig->id;
				$salData['sal_month'] = $postData['sal_month'];
				$salData['emp_id'] = $postData['id'];
				$salData['emp_name'] = $postData['emp_name'];
				$salData['emp_code'] = $postData['emp_code'];
				$salData['wh_hrs'] = $postData['wh_hrs'];
				$salData['ot_hrs'] = $postData['ot_hrs'];
				$salData['total_days'] = $totalDays;
				$salData['present'] = $postData['present'];
				$salData['week_off'] = $postData['wo'];
				$salData['paid_holiday'] = 0;
				$salData['pl'] = $pl_used;
				$salData['cl'] = $cl_used;
				$salData['salary_type'] = $salConfig->salary_type;
				$salData['basic_salary'] = $basic_salary;
				$salData['hra_amt'] = $hra_amt;
				$salData['ot_amt'] = $ot_amt;
				$salData['adv_bonus'] = $salConfig->adv_bonus;
				$salData['other_all'] = $other_all;
				$salData['gross_sal'] = $gross_sal;
				$salData['emp_pf'] = $emp_pf;
				$salData['cmp_pf'] = $cmp_pf;
				$salData['emp_esic'] = $emp_esic;
				$salData['cmp_esic'] = $cmp_esic;
				$salData['tds'] = $tds;
				$salData['pt'] = $pt;
				$salData['transport_charge'] = $salConfig->transport_charge;
				$salData['food'] = $food;
				$salData['advance_salary'] = $postData['advance_salary'];
				$salData['loan_emi'] = $postData['loan_emi'];
				$salData['bonus'] = $bonus;
				$salData['gratuity'] = $gratuity;
				$salData['gross_deduction'] = $gross_deduction;
				$salData['net_salary'] = $net_salary;
			}
		}
		
		
		if(!empty($salData))
		{
			$otAmt = ($salData['ot_amt'] > 0 ) ? $salData['ot_amt'].'<br><small>(Hrs. : '.$postData['ot_hrs'].')</small>' : 0;
			$empTable = '<td style="text-align:center;">'.$salData['emp_code'].'</td>';
			$empTable .= '<td>'.$salData['emp_name'].'</td>';
			$empTable .= '<td>
							<input type="text" name="salary_data['.$postData['id'].'][present]" id="present'.$postData['id'].'" data-id="'.$postData['id'].'" class="floatOnly salData'.$postData['id'].'" alt="present" value="'.$salData['present'].'" style="width:100px;">
						</td>';
			$empTable .= '<td>
							<input type="text" name="salary_data['.$postData['id'].'][week_off]" id="week_off'.$postData['id'].'" data-id="'.$postData['id'].'" class="floatOnly salData'.$postData['id'].'" alt="week_off" value="'.$salData['week_off'].'" style="width:100px;">
						</td>';
			$empTable .= '<td>'.$salData['paid_holiday'].'</td>';
			$empTable .= '<td>
							<input type="text" name="salary_data['.$postData['id'].'][pl]" id="pl'.$postData['id'].'" data-id="'.$postData['id'].'" class="floatOnly salData'.$postData['id'].'" alt="pl" value="'.$salData['pl'].'" style="width:100px;">
						</td>';
			$empTable .= '<td>
							<input type="text" name="salary_data['.$postData['id'].'][cl]" id="cl'.$postData['id'].'" data-id="'.$postData['id'].'" class="floatOnly salData'.$postData['id'].'" alt="cl" value="'.$salData['cl'].'" style="width:100px;">
						</td>';
			$empTable .= '<td>'.$salData['total_days'].'</td>';
			$empTable .= '<td>'.$salData['basic_salary'].'</td>';
			$empTable .= '<td>'.$salData['hra_amt'].'</td>';
			$empTable .= '<td>'.$salData['adv_bonus'].'</td>';
			$empTable .= '<td>'.$salData['other_all'].'</td>';
			$empTable .= '<td>'.$otAmt.'</td>';
			$empTable .= '<th id="gross_sal'.$salData['emp_id'].'" class="bg-light-green">'.$salData['gross_sal'].'</th>';
			$empTable .= '<td>'.$salData['emp_pf'].'</td>';
			$empTable .= '<td>'.$salData['emp_esic'].'</td>';
			$empTable .= '<td>'.$salData['pt'].'</td>';
			$empTable .= '<td>'.$salData['tds'].'</td>';
			$empTable .= '<td>
							<input type="text" name="salary_data['.$postData['id'].'][advance_salary]" id="advance_salary'.$postData['id'].'" data-id="'.$postData['id'].'" class="floatOnly salData'.$postData['id'].'" alt="advance_salary" value="'.$salData['advance_salary'].'" style="width:100px;">
						</td>';
			$empTable .= '<td>'.$salData['loan_emi'].'</td>';
			$empTable .= '<td>'.$salData['transport_charge'].'</td>';
			$empTable .= '<td>
							<input type="text" name="salary_data['.$postData['id'].'][food]" id="food'.$postData['id'].'" data-id="'.$postData['id'].'" class="floatOnly salData'.$postData['id'].'" alt="food" value="'.$salData['food'].'" style="width:100px;">
						</td>';
			$empTable .= '<th id="gross_deduction'.$salData['emp_id'].'" class="bg-light-green">'.$salData['gross_deduction'].'</th>';
			$empTable .= '<th id="net_salary'.$salData['emp_id'].'" class="bg-warning">'.$salData['net_salary'].'</th>';
			$empTable .= '<th><button type="button" class="btn btn-primary float-right reCalculatecSal" data-id="'.$postData['id'].'" >Save</button></th>';
						
			unset($salData['present'],$salData['week_off'],$salData['pl'],$salData['cl'],$salData['advance_salary'],$salData['food']);
			foreach($salData as $key=>$val)
			{
				$name = 'salary_data['.$postData['id'].']['.$key.']';
				$id = $key.$postData['id'];
				$hiddenInputs.="<input type='hidden' name='".$name."' id='".$id."' class='salData".$postData['id']."' alt='".$key."' value='".$val."'>";
			}
		}
		$this->printJson(['status'=>1,'empLine'=>$empTable,'hidden_inputs'=>$hiddenInputs]);
    }
	
    public function viewSalary(){
        $start = new DateTime($this->startYearDate);
        $start->modify('first day of this month');
        $end = new DateTime($this->endYearDate);
        $end->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $monthList = array();
        foreach ($period as $dt): $monthList[] = $dt->format("Y-m-t"); endforeach;

        $this->data['monthList'] = (object) $monthList;
        $this->data['deptRows'] = $this->department->getDepartmentList();
        $this->data['ctcFormat'] = $this->salaryStructure->getCtcFormats();
        $this->data['companyList'] = $this->employee->getCompanyList();
        $this->load->view($this->payrollView,$this->data);   
    }
    
    public function getEmployeeActualSalaryData($dept_id="",$format_id="",$month="",$file_type="pdf"){
        $data['dept_id'] = $dept_id;
        $data['format_id'] = $format_id;
        $data['month'] = $month;
        
        $ctcFormat = $this->salaryStructure->getCtcFromat($data['format_id']);
        $postData = ['type'=>1,'ids'=>$ctcFormat->eh_ids];
        //if($ctcFormat->salary_duration == "H"): $postData['is_system'] = 0; endif;
        $earningHeads = $this->salaryStructure->getSalaryHeadList($postData);

        $postData['type'] = -1;
        $postData['ids'] = $ctcFormat->dh_ids;
        $deductionHeads = $this->salaryStructure->getSalaryHeadList($postData);

        $headCount = 9;
        $eth = '';$betd = '';
        foreach($earningHeads as $row):
            $eth .= '<th>'.$row->head_name.'</th>';
            $betd .= '<td>0</td>';
            $headCount++;
        endforeach;

        $dth = '';$bdtd = '';
        foreach($deductionHeads as $row):
            $dth .= '<th>'.$row->head_name.'</th>';
            $bdtd .= '<td>0</td>';
            $headCount++;
        endforeach;

        
        $thead = '<tr>
            <th>#</th>
            <th>Employee Name</th>
            <th>'.(($ctcFormat->salary_duration == "H")?"Wage":"Total Days").'</th>
            <th>'.(($ctcFormat->salary_duration == "H")?"Rate Hour":"Present").'</th>
            <th>'.(($ctcFormat->salary_duration == "H")?"Working Hour":"Absent").'</th>
            '.$eth.'
            <th>Gross Salary</th>
            '.$dth.'
            <th>Advance</th>
            <th>Loan</th>
            <th>Actual Salary</th>
        </tr>';
        
        $empData = $this->payroll->getEmployeeListForSalary($data);
        $empAttendanceData = $this->biometric->getSalaryHours(['from_date'=>$data['month'],'dept_id'=>$data['dept_id'],'payroll'=>1]);
        $canteenCharges = $this->masterModel->getMasterOptions();
        $empAttendanceData['cl_charge'] = $canteenCharges->cl_charge;
        $empAttendanceData['cd_charge'] = $canteenCharges->cd_charge;      
        $empAttendanceData['month'] = $data['month'];  

        $empAttendanceData['totalDays'] = date("t",strtotime($data['month'])); 
        $holiday = countDayInMonth("Wednesday",$data['month']);
        $empAttendanceData['totalDays'] -= $holiday; $sr_no = 1;
        $html = "";
        if(!empty($empData)):
            foreach($empData as $row):
                $empSalaryData =  $this->calculateEmpSalaryData($sr_no,$row,$empAttendanceData,$earningHeads,$deductionHeads);

                $empSalaryData['betd'] = $betd;
                $empSalaryData['bdtd'] = $bdtd;

                $etd = "";
                foreach($empSalaryData['earning_data'] as $row):
                    $etd .= "<td>".$row['org_amount']."</td>";
                endforeach;

                $dtd = "";
                foreach($empSalaryData['deduction_data'] as $row):
                    $dtd .= "<td>".$row['org_amount']."</td>";
                endforeach;

                $empSalaryData['etd'] = $etd;
                $empSalaryData['dtd'] = $dtd;

                $row = (object) $empSalaryData;
                $html .= "<tr>
                    <td>".$row->sr_no."</td>
                    <td>
                        ".$row->emp_name."
                    </td>
                    <td>
                        ".(($row->salary_basis == "H")?$row->wage:$row->working_days)."
                    </td>                                                                    
                    <td>
                        ".(($row->salary_basis == "H")?$row->r_hr:$row->present_days)."
                    </td>
                    <td>
                        ".(($row->salary_basis == "H")?$row->total_wh:$row->absent_days)."
                    </td>
                    ".((!empty($row->etd))?$row->etd:$row->betd)."
                    <td>
                        ".$row->org_total_earning."            
                    </td>
                    ".((!empty($row->dtd))?$row->dtd:$row->bdtd)."
                    <td>".$row->org_advance_deduction."</td>
                    <td>".$row->org_emi_amount."</td>
                    <td>
                        ".$row->actual_sal."
                    </td>
                </tr>";
                $sr_no++;
            endforeach;
        else:
            $html = '<tr>
                <td id="noData" class="text-center" colspan="'.$headCount.'">No data available in table</td>
            </tr>';
        endif;
        
        $response = '<table class="table-bordered itemList" border="1" repeat_header="1">';
        $response .= '<thead>'.$thead.'</thead><tbody>'.$html.'</tbody></table>';
        $xls_filename = 'actual-payroll-'.date("m-Y",strtotime($data['month'])).'.xls';
				
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename='.$xls_filename);
		header('Pragma: no-cache');
		header('Expires: 0');
		
		echo $response;
    }

    public function editEmployeeSalaryData(){
        $data = $this->input->post();
        $salaryData = $data['salary_data'][$data['key_value']];
        $sr_no = $data['key_value'];
        $salaryData = (object) $salaryData;    
        $salaryData->earning_data = (!empty($salaryData->earning_data))?json_decode($salaryData->earning_data):array();
        $salaryData->deduction_data = (!empty($salaryData->deduction_data))?json_decode($salaryData->deduction_data):array();

        $ctcFormat = $this->salaryStructure->getCtcFromat($data['format_id']);
        $earningHeads = $this->salaryStructure->getSalaryHeadList(['type'=>1,'ids'=>$ctcFormat->eh_ids]);
        $deductionHeads = $this->salaryStructure->getSalaryHeadList(['type'=>-1,'ids'=>$ctcFormat->dh_ids]);

        $empData = $this->payroll->getEmployeeSalaryStructure($salaryData->structure_id);
        $empAttendanceData = $this->biometric->getSalaryHours(['from_date'=>$data['month'],'dept_id'=>$data['dept_id'],'emp_id'=>$salaryData->emp_id,'payroll'=>1]);
       
        $canteenCharges = $this->masterModel->getMasterOptions();
        $empAttendanceData['cl_charge'] = $canteenCharges->cl_charge;
        $empAttendanceData['cd_charge'] = $canteenCharges->cd_charge;      
        $empAttendanceData['month'] = $data['month'];  

        $empAttendanceData['totalDays'] = date("t",strtotime($data['month'])); 
        $holiday = countDayInMonth("Wednesday",$data['month']);
        $empAttendanceData['totalDays'] -= $holiday;      
        if(!empty($empData)):
            $empSalaryData = $this->calculateEmpSalaryData($sr_no,$empData,$empAttendanceData,$earningHeads,$deductionHeads,$salaryData);           
        endif;

        $this->data['salaryData'] = (object) $empSalaryData;
        $this->load->view($this->editEmpSalaryForm,$this->data);
    }

    public function saveEmployeeSalaryData(){
        $data = $this->input->post();
        $data['sr_no'] = $data['row_index'];
        $etd = "";
        foreach($data['earning_data'] as $row):
            $etd .= "<td>".$row['amount']."</td>";
        endforeach;

        $dtd = "";
        foreach($data['deduction_data'] as $row):
            $dtd .= "<td>".$row['amount']."</td>";
        endforeach;

        $data['etd'] = $etd;
        $data['dtd'] = $dtd;
        $data['view'] = 0;

        $html = $this->getEmployeeSalaryHtml($data);
        $this->printJson(['status'=>1,'salary_data'=>$html]);
    }

    public function calculateEmpSalaryData($sr_no,$empData,$empAttendanceData,$earningHeads,$deductionHeads,$salaryData = array()){
        $cl_charge = $empAttendanceData['cl_charge'];
        $cd_charge = $empAttendanceData['cd_charge'];  
        $empSalarayHeads = (!empty($empData->salary_head_json))?json_decode($empData->salary_head_json):array();

        $totalDays =  $empAttendanceData['totalDays'];
        $total_wh = (isset($empAttendanceData[$empData->emp_id]))?round(($empAttendanceData[$empData->emp_id]['twh']/3600),2):0;
        $tot = (isset($empAttendanceData[$empData->emp_id]))?round(($empAttendanceData[$empData->emp_id]['tot']/3600),2):0;
        $present = (isset($empAttendanceData[$empData->emp_id]))?($empAttendanceData[$empData->emp_id]['tpd']):0;
        $absent = $totalDays - $present; 

        $empEarningData = array();$empDeductionData = array();$etd = '';$dtd = '';
        $actual_wage=0;$r_hr = 0;$actual_salary = 0;
        $grossSalary = 0; $orgGrossSalary = 0;
        $totalDeduction = 0; $orgDeduction=0;
        $netSalary = 0; $orgNetSalary = 0; 

        if($empData->salary_duration == "H"):
            $actual_wage = ((!empty($empData->ctc_amount))?$empData->ctc_amount:0);
            $r_hr = ($actual_wage / 8);
            $actual_salary = round(($r_hr * $total_wh),0);
        endif;

        $basicAmount = 0;$hraAmount = 0;$orgBasicAmt = 0;$orgHraAmount = 0;
        
        if(!empty($salaryData->earning_data) && $salaryData->total_wh == $total_wh):
            $empEarningData = (array)$salaryData->earning_data;
            $grossSalary = $salaryData->total_earning;
            $orgGrossSalary = $salaryData->org_total_earning;
        else:
            foreach($earningHeads as $erow):
                $amount = 0;$value = 0;$orgAmount = 0;$orgValue = 0;
                if((!empty($empSalarayHeads->{$erow->id}->cal_method) && $empSalarayHeads->{$erow->id}->cal_method == 1)):
                    $value = ((!empty($empSalarayHeads->{$erow->id}->cal_value))?$empSalarayHeads->{$erow->id}->cal_value:0);
                    $amount = round((($value/$totalDays)*$present),0);  
                    
                    $orgValue = ((!empty($empSalarayHeads->{$erow->id}->org_cal_value))?$empSalarayHeads->{$erow->id}->org_cal_value:0);
                    $orgAmount = round((($orgValue/$totalDays)*$present),0);  
                else:
                    $value = ((!empty($empSalarayHeads->{$erow->id}->cal_value))?$empSalarayHeads->{$erow->id}->cal_value:0);
                    $value = round((($basicAmount * $value)/100),0);
                    $amount = round((($value/$totalDays)*$present),0);

                    $orgValue = ((!empty($empSalarayHeads->{$erow->id}->org_cal_value))?$empSalarayHeads->{$erow->id}->org_cal_value:0);
                    $orgValue = round((($orgBasicAmt * $orgValue)/100),0);
                    $orgAmount = round((($orgValue/$totalDays)*$present),0);
                endif;

                if($empData->salary_duration == "H" && $erow->system_code == "basic"):
                    $orgAmount = $actual_salary;
                endif;

                $cal_method = (!empty($empSalarayHeads->{$erow->id}->cal_method))?$empSalarayHeads->{$erow->id}->cal_method:0;
                $cal_value = (!empty($empSalarayHeads->{$erow->id}->cal_value))?$empSalarayHeads->{$erow->id}->cal_value:0;

                if($erow->system_code == "ca" && !empty($empData->traveling_charge)):
                    $orgAmount += round(($present * $empData->traveling_charge),0);
                endif;

                $amount = round($amount,0);
                $orgAmount = round($orgAmount,0);
                $empEarningData[$erow->id]['head_name'] = $erow->head_name;
                $empEarningData[$erow->id]['system_code'] = $erow->system_code;
                $empEarningData[$erow->id]['cal_method'] = $cal_method;
                $empEarningData[$erow->id]['cal_value'] = $cal_value;
                $empEarningData[$erow->id]['amount'] = $amount;
                $empEarningData[$erow->id]['org_amount'] = $orgAmount;

                if($erow->system_code == "basic"): $basicAmount = round($amount,0); $orgBasicAmt = round($orgAmount,0); endif;       
                if($erow->system_code == "hra"): $hraAmount = round($amount,0); $orgHraAmount = round($orgAmount,0); endif;    
                $grossSalary += $amount;
                $orgGrossSalary += $orgAmount;
                $etd .= '<td>'.$amount.'</td>';
            endforeach;
        endif;

        if(!empty($salaryData->deduction_data) && $salaryData->total_wh == $total_wh):
            $empDeductionData = (array)$salaryData->deduction_data;
            $totalDeduction = $salaryData->total_deduction;
            $orgDeduction = $salaryData->org_total_deduction;
        else:
            foreach($deductionHeads as $drow):
                $amount = 0;$value = 0;$orgAmount = 0;$orgValue = 0;

                if(in_array($drow->system_code,["pf","pt","lwf","ccl","ccd"])):
                    if($drow->system_code == "pf" && !empty($empSalarayHeads->{$drow->id}->cal_value) && $empData->pf_applicable == 1):
                        $pfValuation = ($grossSalary - $hraAmount);
                        if($pfValuation >= 15000):
                            $orgAmount = $amount = ((15000 * $empSalarayHeads->{$drow->id}->cal_value) / 100);
                        else:
                            $orgAmount = $amount = (($pfValuation * $empSalarayHeads->{$drow->id}->cal_value) / 100);
                        endif;

                        /*$orgPfValuation = ($orgGrossSalary - $orgHraAmount);
                        if($orgPfValuation >= 15000):
                            $orgAmount = ((15000 * $empSalarayHeads->{$drow->id}->cal_value) / 100);
                        else:
                            $orgAmount = (($orgPfValuation * $empSalarayHeads->{$drow->id}->cal_value) / 100);
                        endif;*/
                    endif;

                    if($drow->system_code == "pt" && !empty($empSalarayHeads->{$drow->id}->cal_value)):
                        if($grossSalary >= 12000):
                            $orgAmount = $amount = $empSalarayHeads->{$drow->id}->cal_value;
                        endif;
                        /*if($orgGrossSalary >= 12000):
                            $orgAmount = $empSalarayHeads->{$drow->id}->cal_value;
                        endif;*/
                    endif;

                    if($drow->system_code == "lwf" && !empty($empSalarayHeads->{$drow->id}->cal_value)):
                        if(in_array(date("m",strtotime($empAttendanceData['month'])),["06","12"])):
                            $orgAmount = $amount = $empSalarayHeads->{$drow->id}->cal_value;
                        endif;
                    endif;

                    if($drow->system_code == "ccl" && !empty($cl_charge)):
                        $orgAmount = $amount = round(($present * $cl_charge),0);
                    endif;

                    if($drow->system_code == "ccd" && !empty($cd_charge)):
                        $orgAmount = $amount = round(($present * $cd_charge),0);
                    endif;                          
                else:
                    if((!empty($empSalarayHeads->{$drow->id}->cal_method) && $empSalarayHeads->{$drow->id}->cal_method == 1)):
                        $value = ((!empty($empSalarayHeads->{$drow->id}->cal_value))?$empSalarayHeads->{$drow->id}->cal_value:0);
                        $amount = round((($value/$totalDays)*$present),0); 
                        
                        $orgValue = ((!empty($empSalarayHeads->{$erow->id}->org_cal_value))?$empSalarayHeads->{$erow->id}->org_cal_value:0);
                        $orgAmount = round((($orgValue/$totalDays)*$present),0);  
                    else:
                        $value = ((!empty($empSalarayHeads->{$drow->id}->cal_value))?$empSalarayHeads->{$drow->id}->cal_value:0);
                        $value = round((($grossSalary * $value)/100),0);
                        $amount = round((($value/$totalDays)*$present),0);

                        $orgValue = ((!empty($empSalarayHeads->{$erow->id}->org_cal_value))?$empSalarayHeads->{$erow->id}->org_cal_value:0);
                        $orgValue = round((($orgGrossSalary * $orgValue)/100),0);
                        $orgAmount = round((($orgValue/$totalDays)*$present),0);
                    endif;
                endif;

                $cal_method = (!empty($empSalarayHeads->{$drow->id}->cal_method))?$empSalarayHeads->{$drow->id}->cal_method:0;
                $cal_value = (!empty($empSalarayHeads->{$drow->id}->cal_value))?$empSalarayHeads->{$drow->id}->cal_value:0;

                $amount = round($amount,0);
                $orgAmount = round($orgAmount,0);
                $empDeductionData[$drow->id]['head_name'] = $drow->head_name;
                $empDeductionData[$drow->id]['system_code'] = $drow->system_code;
                $empDeductionData[$drow->id]['cal_method'] = $cal_method;
                $empDeductionData[$drow->id]['cal_value'] = $cal_value;
                $empDeductionData[$drow->id]['amount'] = $amount;
                $empDeductionData[$drow->id]['org_amount'] = $orgAmount;

                $totalDeduction += $amount;
                $orgDeduction += $orgAmount;
                $dtd .= '<td>'.$amount.'</td>';
            endforeach; 
        endif;

        // Advance Salary
        $adsData = (!empty($empAttendanceData[$empData->emp_id]['advance_data']))?$empAttendanceData[$empData->emp_id]['advance_data']:array();
        $adSalary=0;$orgAdSalary=0;
        $a=0;$adsHtml='';$adSalaryData=array();
        if(!empty($salaryData->advance_data)):
            $adSalaryData = $salaryData->advance_data;
            $adSalary = $salaryData->advance_deduction;
            $orgAdSalary = $salaryData->org_advance_deduction;
        else:
            foreach($adsData as $adsRow):
                $adSalaryData[$a] = [
                    'id'=>$adsRow->id,
                    'entry_date' => $adsRow->entry_date,
                    'payment_mode' => $adsRow->payment_mode,
                    'amount'=> ($adsRow->payment_mode != "CS")?$adsRow->pending_amount:0,
                    'org_amount' => ($adsRow->payment_mode == "CS")?$adsRow->pending_amount:0
                ];
                $adSalary += ($adsRow->payment_mode != "CS")?$adsRow->pending_amount:0;
                $orgAdSalary += ($adsRow->payment_mode == "CS")?$adsRow->pending_amount:0;
                $a++;
            endforeach;
        endif;

        // Employee Loans
        $l=0;$loanEmi=0;$orgLoanEmi=0;$pendingLoan=0;$emiAmount=0;$loanHtml = '';
        $loanData = (!empty($empAttendanceData[$empData->emp_id]['loan_data']))?$empAttendanceData[$empData->emp_id]['loan_data']:array();
        $loanDataRows = array();        
        if(!empty($salaryData->loan_data)):
            foreach($salaryData->loan_data as $loanRow):
                $loanRow = (object) $loanRow;
                $loanDataRows[$l] = [
                    'id'=>$loanRow->id,
                    'payment_mode' => $loanRow->payment_mode,
                    'loan_no'=>$loanRow->loan_no,
                    'amount'=> $loanRow->amount,
                    'org_amount'=> $loanRow->org_amount,
                    'loan_amount'=> $loanRow->loan_amount
                ];
                $loanEmi += $loanRow->amount;
                $orgLoanEmi += $loanRow->org_amount;
                $pendingLoan += ($loanRow->loan_amount - ($loanRow->amount + $loanRow->org_amount));
                $l++;
            endforeach;
        else:
            foreach($loanData as $loanRow):
                $emiAmount = ($loanRow->pending_amount > $loanRow->emi_amount)?$loanRow->emi_amount:$loanRow->pending_amount;
                
                $loanDataRows[$l] = [
                    'id'=>$loanRow->id,
                    'payment_mode' => $loanRow->payment_mode,
                    'loan_no'=>$loanRow->loan_no,
                    'amount'=> ($loanRow->payment_mode != "CS")?$emiAmount:0,
                    'org_amount'=> ($loanRow->payment_mode == "CS")?$emiAmount:0,
                    'loan_amount'=>$loanRow->pending_amount
                ];
                $loanEmi += ($loanRow->payment_mode != "CS")?$emiAmount:0;
                $orgLoanEmi += ($loanRow->payment_mode == "CS")?$emiAmount:0;
                $pendingLoan += ($loanRow->pending_amount - $emiAmount);
                $l++;
            endforeach;
        endif;
        

        $orgDeduction += $orgAdSalary;
        $orgDeduction += $orgLoanEmi;
        
        $totalDeduction += $adSalary;
        $totalDeduction += $loanEmi;

        $orgNetSalary = round($orgGrossSalary -  $orgDeduction,0); // Actual Net Pay
        $netSalary = round($grossSalary - $totalDeduction,0); // On Paper Net Pay
        $sal_diff = round($orgNetSalary - $netSalary,0); // Salary Difference [Actual - On Parer]
        
        $dataRow = [
            'sr_no' => $sr_no,
            'id' => (!empty($salaryData->id))?$salaryData->id:"",
            'structure_id' => $empData->id,
            'emp_id' => $empData->emp_id,
            'emp_code' => $empData->emp_code,
            'emp_name' => $empData->emp_name,
            'emp_type' => $empData->emp_type,
            'salary_code' => $empData->salary_code,
            'salary_basis' => $empData->salary_duration,
            'pf_applicable' => $empData->pf_applicable,
            'total_wh' => $total_wh,
            'tot' => $tot,
            'wage' => $actual_wage,
            'r_hr' => $r_hr,
            'actual_sal' => $orgNetSalary,
            'sal_diff' => $sal_diff,
            'present_days' => $present,
            'working_days' => $totalDays,
            'absent_days' => $absent,
            'etd' => $etd,
            'org_total_earning' => $orgGrossSalary,
            'total_earning' => $grossSalary,
            'earning_data' => $empEarningData,
            'dtd' => $dtd,
            'org_total_deduction' => $orgDeduction,
            'total_deduction' => $totalDeduction,
            'deduction_data' => $empDeductionData,
            'advance_deduction' => $adSalary,
            'org_advance_deduction' => $orgAdSalary,
            'advance_data' => $adSalaryData,
            'emi_amount' => $loanEmi,
            'org_emi_amount' => $orgLoanEmi,
            'loan_data' => $loanDataRows,
            'pending_loan' => $pendingLoan,
            'net_salary' => $netSalary
        ];

        return $dataRow;
    }

    public function getEmployeeSalaryHtml($row){
        $row = (object) $row;
        $salaryCode = '"'.$row->salary_code.'"';
        $editButton = "<button type='button' class='btn btn-outline-warning' title='Edit' onclick='Edit(".$row->sr_no.", ".$salaryCode.");'><i class='ti-pencil-alt'></i></button>";

        $hiddenInputs = "";
        if(empty($row->view)):
            $hiddenInputs = "<input type='hidden' name='salary_data[".$row->sr_no."][id]' value='".$row->id."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][structure_id]' value='".$row->structure_id."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][emp_id]' value='".$row->emp_id."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][emp_name]' value='".$row->emp_name."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][emp_type]' value='".$row->emp_type."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][pf_applicable]' value='".$row->pf_applicable."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][salary_code]' value='".$row->salary_code."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][salary_basis]' value='".$row->salary_basis."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][total_wh]' value='".$row->total_wh."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][tot]' value='".$row->tot."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][wage]' value='".$row->wage."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][r_hr]' value='".$row->r_hr."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][actual_sal]' value='".$row->actual_sal."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][sal_diff]' value='".$row->sal_diff."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][present_days]' value='".$row->present_days."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][working_days]' value='".$row->working_days."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][absent_days]' value='".$row->absent_days."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][total_earning]' value='".$row->total_earning."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][org_total_earning]' value='".$row->org_total_earning."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][earning_data]' value='".json_encode($row->earning_data)."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][net_salary]' value='".$row->net_salary."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][total_deduction]' value='".$row->total_deduction."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][org_total_deduction]' value='".$row->total_deduction."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][deduction_data]' value='".json_encode($row->deduction_data)."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][advance_deduction]' value='".$row->advance_deduction."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][org_advance_deduction]' value='".$row->org_advance_deduction."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][emi_amount]' value='".$row->emi_amount."'>
            <input type='hidden' name='salary_data[".$row->sr_no."][org_emi_amount]' value='".$row->org_emi_amount."'>";

            $a=0;
            if(!empty($row->advance_data)):
                foreach($row->advance_data as $adsRow):
                    $adsRow = (object) $adsRow;
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][advance_data][".$a."][id]' value='".$adsRow->id."'>";
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][advance_data][".$a."][entry_date]' value='".$adsRow->entry_date."'>";
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][advance_data][".$a."][payment_mode]' value='".$adsRow->payment_mode."'>";
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][advance_data][".$a."][amount]' value='".$adsRow->amount."'>";
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][advance_data][".$a."][org_amount]' value='".$adsRow->org_amount."'>";
                    $a++;
                endforeach;
            endif;

            $l=0;
            if(!empty($row->loan_data)):
                foreach($row->loan_data as $loanRow):
                    $loanRow = (object) $loanRow;
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][loan_data][".$l."][id]' value='".$loanRow->id."'>";
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][loan_data][".$l."][payment_mode]' value='".$loanRow->payment_mode."'>";
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][loan_data][".$l."][loan_no]' value='".$loanRow->loan_no."'>";
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][loan_data][".$l."][amount]' value='".$loanRow->amount."'>";
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][loan_data][".$l."][org_amount]' value='".$loanRow->org_amount."'>";
                    $hiddenInputs .= "<input type='hidden' name='salary_data[".$row->sr_no."][loan_data][".$l."][loan_amount]' value='".$loanRow->loan_amount."'>";
                    $l++;
                endforeach;
            endif;
        endif;

        $html = "<td>".$row->emp_code."</td>
        <td>
            ".$row->emp_name."
            ".((empty($row->view))?$hiddenInputs:"")."
        </td>
        <td>
            ".$row->working_days."
        </td>                                                                    
        <td>
            ".$row->present_days."
        </td>
        <td>
            ".$row->absent_days."
        </td>
        ".((!empty($row->etd))?$row->etd:$row->betd)."
        <td>
            ".$row->total_earning."            
        </td>
        ".((!empty($row->dtd))?$row->dtd:$row->bdtd)."
        <td>".$row->advance_deduction."</td>
        <td>".$row->emi_amount."</td>
        <td>
            ".$row->net_salary."
        </td>
        <td>
            ".$row->actual_sal."
        </td>
        <td>
            ".$row->sal_diff."
        </td>";

        $html .= (empty($row->view))?"<td>".$editButton."</td>":"";
        return $html;
    }

    public function importSalary(){
		$start = new DateTime($this->startYearDate);
        $start->modify('first day of this month');
        $end = new DateTime($this->endYearDate);
        $end->modify('first day of next month');
        $interval = DateInterval::createFromDateString('1 month');
        $period = new DatePeriod($start, $interval, $end);
        $monthList = array();
        foreach ($period as $dt): $monthList[] = $dt->format("Y-m-d"); endforeach;

        $this->data['monthList'] = (object) $monthList;
		$this->load->view($this->importForm,$this->data);
    }

    public function downloadSalarySheet(){
		$spreadsheet = new Spreadsheet();
		$attendSheet = $spreadsheet->getActiveSheet();
		$attendSheet = $attendSheet->setTitle('EmpSalary');
		$xlCol = 'A';
		$rows = 1;
		$table_column = array('Emp Code','Advance','Canteen','PT','PF','Loan EMI');

		foreach ($table_column as $tCols) {
			$attendSheet->setCellValue($xlCol . $rows, $tCols);
			$xlCol++;
		}

		/*$rows++;$i=1;
		$empData = $this->employee->getEmpList();
		foreach($empData as $emp):
			if(!empty($emp->emp_code)):
				$attendSheet->setCellValue('A' . $rows, $emp->emp_code);
				$rows++;
			endif;
		endforeach;*/

		$fileDirectory = realpath(APPPATH . '../assets/uploads/manual_salary');
		$fileName = '/EmpSalary.xlsx';
		$writer = new Xlsx($spreadsheet);

		if(is_dir($fileDirectory) === false) {
			mkdir($fileDirectory, 0755);
		}

		$writer->save($fileDirectory . $fileName);
		header("Content-Type: application/vnd.ms-excel");
		redirect(base_url('assets/uploads/manual_salary') . $fileName);
    }

    public function saveImportSalary(){
		$postData = $this->input->post();
		
        $errorMessage = array();
        if(!isset($_FILES['emp_salary']['name']) || empty($_FILES['emp_salary']['name'])){
            $errorMessage['emp_salary'] = "Please Select File!";
		}
		if(empty($postData['month'])){
            $errorMessage['month'] = "Month is required.";
		}
		
		if(!empty($errorMessage)){
			$this->printJson(['status' => 0, 'message' => $errorMessage]);
		}else{
			$this->load->library('upload');
			$_FILES['userfile']['name']     = $_FILES['emp_salary']['name'];
			$_FILES['userfile']['type']     = $_FILES['emp_salary']['type'];
			$_FILES['userfile']['tmp_name'] = $_FILES['emp_salary']['tmp_name'];
			$_FILES['userfile']['error']    = $_FILES['emp_salary']['error'];
			$_FILES['userfile']['size']     = $_FILES['emp_salary']['size'];

			$filePath = realpath(APPPATH . '../assets/uploads/manual_salary');
			$config = ['file_name' => "manual_salary".time(), 'allowed_types' => '*', 'max_size' => 10240, 'overwrite' => FALSE, 'upload_path' => $filePath];

			$this->upload->initialize($config);
			if (!$this->upload->do_upload()){
				$errorMessage['emp_salary'] = $this->upload->display_errors();
				$this->printJson(["status" => 0, "message" => $errorMessage]);
			}else{
				$uploadData = $this->upload->data();
				$file_name = $uploadData['file_name'];
			}

			if(!empty($file_name)){
				$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath . '/' . $file_name);
				$fileData = array($spreadsheet->getSheetByName('EmpSalary')->toArray(null, true, true, true));

				if(!empty($fileData)){
					$inserted = 0;
					for($i=2;$i<=count($fileData[0]);$i++){
						if(!empty($fileData[0][$i]['A'])){
							$empData = $this->employee->getEmpByCode($fileData[0][$i]['A']);
							
							if(!empty($empData)){
								$c = 'A'; 
								$dataRow = [
									'id' => '',
									'month' => date('Y-m-d',strtotime($postData['month'])),
									'emp_id' => $empData->id,
									'emp_code' => $fileData[0][$i]['A'],
									'advance' => (!empty($fileData[0][$i]['B']) ? $fileData[0][$i]['B'] : ''),
									'canteen' => (!empty($fileData[0][$i]['C']) ? $fileData[0][$i]['C'] : ''),
									'pt' => (!empty($fileData[0][$i]['D']) ? $fileData[0][$i]['D'] : ''),
									'pf' => (!empty($fileData[0][$i]['E']) ? $fileData[0][$i]['E'] : ''),
									'loan_emi' => (!empty($fileData[0][$i]['F']) ? $fileData[0][$i]['F'] : ''),
									'created_at' => date('Y-m-d H:i:s'),
									'created_by' => $this->session->userdata('loginId')
								];
								
								$this->payroll->saveImportSalary($dataRow);                      
								$inserted++;
							}
						}			  
					}
                    unlink($filePath . '/' . $file_name);
					$this->printJson(['status'=>1,'message'=>$inserted." Record Inserted Successfully."]);
				}else{
                    unlink($filePath . '/' . $file_name);
					$this->printJson(['status' => 2, 'message' => 'Data not found...!']);
				}
			}else{
                unlink($filePath . '/' . $file_name);
				$this->printJson(['status' => 2, 'message' => 'Data not found...!']);
			}
		}
    }
}
?>