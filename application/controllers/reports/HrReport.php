<?php
class HrReport extends MY_Controller
{
    private $indexPage = "reports/hr_report/index";
    private $emp_report = "reports/hr_report/emp_report";
    private $monthlyAttendance = "reports/hr_report/month_attendance";
    private $monthSummary = "reports/hr_report/month_summary";
    private $monthlySummary = "reports/hr_report/monthly_summary";
    private $canteenReport = "reports/hr_report/canteen";
    private $canteen_icard = "reports/hr_report/canteen_icard";
	private $salary_report = "reports/hr_report/salary_report";	
	private $leave_report = "reports/hr_report/leave_report";
	private $facility_report = "reports/hr_report/facility_report";

    public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "HR Report";
		$this->data['headData']->controller = "reports/hrReport";
		// $this->data['floatingMenu'] = $this->load->view('report/hr_report/floating_menu',[],true);
	}
	
	public function index(){
		$this->data['pageHeader'] = 'HR REPORT';
        $this->load->view($this->indexPage,$this->data);
    }

	/* Updated By :- Sweta @04-09-2023 */
	public function empReport(){
        $this->data['pageHeader'] = 'EMPLOYEE REPORT';
		$this->data['empList'] = $this->employee->getEmployeeList();
		$this->data['deptList'] = $this->department->getDepartmentList();		
		$this->data['cmList'] = $this->employee->getCompanyList($cm_id='');	
        $this->load->view($this->emp_report,$this->data);
    }

	/* Created By :- Sweta @04-09-2023 */
	public function getEmpReport(){
		$data = $this->input->post();
		$empData = $this->employee->getEmployeeList(['id'=>$data['emp_id'],'emp_dept_id'=>$data['emp_dept_id'],'cm_id'=>$data['emp_unit_id']]);		
		if(!empty($empData)):
			$i=1; $tbody="";
			foreach($empData as $row):
				$empEdu = $this->employee->getEducationData(['emp_id'=>$row->id]);
				$course = Array();
				foreach($empEdu as $edu):
					$course[] = $edu->course;
				endforeach;
				$tbody .= '<tr>
					<td>'.$i++.'</td>
					<td>'.$row->emp_name.'</td>
					<td>'.$row->emp_code.'</td>
					<td>'.$row->emp_alias.'</td>
					<td>'.$row->emp_contact.'</td>
					<td>'.$row->emp_alt_contact.'</td>
					<td>'.$row->emp_gender.'</td>
					<td>'.$row->marital_status.'</td>
					<td>'.$row->emp_birthdate.'</td>
					<td>'.$row->emp_email.'</td>
					<td>'.$row->mark_id.'</td>
					<td>'.$row->emp_address.'</td>
					<td>'.$row->permenant_address.'</td>
					<td>'.$row->father_name.'</td>
					<td>'.$row->department_name.'</td>
					<td>'.$row->designation_name.'</td>
					<td></td>
					<td>'.$row->category.'</td>
					<td>'.$row->emp_type.'</td>
					<td>'.$row->emp_grade.'</td>
					<td>'.implode(', ',$course).'</td>
					<td>'.$row->emp_experience.'</td>
					<td>'.$row->pf_applicable.'</td>
					<td>'.$row->pf_no.'</td>
					<td>'.$row->uan_no.'</td>
					<td>'.$row->sal_pay_mode.'</td>
					<td>'.$row->bank_name.'</td>
					<td>'.$row->account_no.'</td>
					<td>'.$row->ifsc_code.'</td>
					<td>'.formatDate($row->emp_joining_date).'</td>
					<td>'.formatDate($row->emp_relieve_date).'</td>
					<td>'.$row->company_name.'</td>
					<td></td>

				</tr>';
			endforeach;
		else:
			$tbody = "";
		endif;
		$this->printJson(['status'=>1, 'tbody'=>$tbody]);
	}

    public function mismatchPunch(){        
        $this->data['pageHeader'] = 'MISMATCH PUNCH REPORT';
        $this->load->view("reports/hr_report/mismatch_punch",$this->data);
    }

    public function getMismatchPunch(){
        $report_date = $this->input->post('report_date');
        $empData = $this->attendance->getMismatchPunchData($report_date);
        $html = "";
        foreach($empData as $row):
            $html .= '
                <tr>
                    <td>'.$row->emp_code.'</td>
                    <td>'.$row->emp_name.'</td>
                    <td>'.$row->department_name.'</td>
                    <td>'.$row->shift_name.'</td>
                    <td>'.$row->title.'</td>
                    <td>'.$row->category.'</td>
                    <td>'.$row->punch_time.'</td>
                    <td>'.$row->missed_punch.' <a href="#" class="float-right manualAttendance" data-empid="'.$row->id.'" data-adate="'.$report_date.'" data-button="both" data-modal_id="modal-lg" data-function="addManualAttendance" data-form_title="Add Manual Attendance"> Add</a></td>
                </tr>
            ';
        endforeach;
        $this->printJson(['status'=>1,'tbody'=>$html]);
    }

	public function monthlyAttendance(){
        $this->data['empList'] = $this->employee->getEmployeeList();
        $this->load->view($this->monthlyAttendance,$this->data);
    }

    public function printMonthlyAttendance($month,$file_type = 'excel'){
	
		set_time_limit(0);
		$empData = $this->attendance->getEmployeeList();
		$companyData = $this->attendance->getCompanyInfo();
		$current_month  = date("m");
		$year  = ((int)$month >= 1 and (int)$month < 4)?$this->session->userdata('endYear') : $this->session->userdata('startYear');;
		$FromDate = date("d/m/Y_00:01",strtotime($year.'-'.$month.'-01'));
		$ToDate  = date("t/m/Y_11:59",strtotime($year.'-'.$month.'-01'));
		
		$fdate = date("Y-m-d 00:00:01",strtotime($year.'-'.$month.'-01'));
		$tdate  = date("Y-m-t 23:59:59",strtotime($year.'-'.$month.'-01'));
		
		$first_day = 1;
		$last_day = date("t",strtotime($year.'-'.$month.'-01'));
		
		$punchData = NULL;
		$attendanceDataDB = $this->attendance->getEmployeePunchDataDB($fdate,$tdate);
		if(!empty($attendanceDataDB)){$punchData = $attendanceDataDB->punchdata;}
		$thead ='';$tbody ='';$i=1;$printData='';$empCount = 1;
		if(empty($punchData)):
			$punchData = $this->attendance->saveBiometricData($fdate,$tdate);
		else:
			$punchData = json_decode($punchData);
		endif;
		
		$emp1 = Array();$response = '';$empTable='';$pageData = Array();
		if(!empty($empData))
		{
			foreach($empData as $emp)
			{
				$ecode = sprintf("%04d", $emp->biomatric_id);
				$present = 0;$leave = 0;$absent = 0;$theadDate = '';$theadDay = '';$wo = 0;$wh = 0;$wi = 0;$oth = 0;$oti = 0;
				$inData = '';$outData = '';$lunchInData = '';$lunchOutData = '';$workHrs = '';$otData = '';$status = '';
				$empPucnhes = array_keys(array_combine(array_keys($punchData), array_column($punchData, 'Empcode')),$ecode);
				
				$inData .= '<tr><th style="border:1px solid #888;font-size:12px;">IN</th>';
				$lunchInData .= '<tr><th style="border:1px solid #888;font-size:12px;">L-START</th>';
				$lunchOutData .= '<tr><th style="border:1px solid #888;font-size:12px;">L-END</th>';
				$outData .= '<tr><th style="border:1px solid #888;font-size:12px;">OUT</th>';
				$workHrs .= '<tr><th style="border:1px solid #888;font-size:12px;">WH</th>';
				$otData .= '<tr><th style="border:1px solid #888;font-size:12px;">OT</th>';
				$status .= '<tr><th style="border:1px solid #888;font-size:12px;">STATUS</th>';
				for($d=1;$d<=$last_day;$d++)
				{
					$attend_status = false;
					$currentDate = date('d/m/Y', strtotime($year.'-'.$month.'-'.$d));$punchDates = Array();
					$day = date("D",strtotime($year.'-'.$month.'-'.$d));if($day == 'Wed'){$wo++;}
					$theadDate .= '<th style="border:1px solid #888;text-align:center;font-size:12px;">'.$d.'</th>';
					$theadDay .= '<th style="border:1px solid #888;text-align:center;font-size:12px;">'.$day.'</th>';
					
					if(!empty($empPucnhes))
					{
						foreach($empPucnhes as $punch)
						{
							$todayPunch = $punchData[$punch];
							if($currentDate == date('d/m/Y', strtotime(strtr($todayPunch->PunchDate, '/', '-')))) 
							{
								$punchDates[]=date('Y/m/d H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
							}
						}
					}
					if(!empty($punchDates))
					{
						$attend_status = true;
						$punch_in = date('d-m-Y H:i:s', strtotime(min($punchDates)));
						$shiftStart = date('d-m-Y H:i:s', strtotime($d.'-'.$month.'-'.$year.' '.$emp->shift_start));
						$shiftEnd = date('d-m-Y H:i:s', strtotime('+8 hours',strtotime($shiftStart)));
						$punch_out = date('d-m-Y H:i:s', strtotime(max($punchDates)));
						if(strtotime($punch_in) > strtotime($shiftEnd))
						{
							$shiftEnd = date('d-m-Y H:i:s', strtotime($year.'-'.$month.'-'.$d.' 23:59:59'));
						}
						if( count($punchDates) == 1 ):
							$punch_out = $shiftEnd;
						endif;
						
						$time1 = new DateTime(date('H:i:s',strtotime($punch_in)));
						$time2 = new DateTime(date('H:i:s',strtotime($punch_out)));
						$interval = $time1->diff($time2);
						$total_hours = $interval->format('%H:%I:%S');
						$total_is = $interval->format('%I:%S');
						$overtime = floatVal($total_hours) - floatVal($emp->total_shift_time);
						$wh += floatVal($interval->h);
						$wi += floatVal($interval->format('%I'));
						if(empty($overtime) or $overtime < 0){$overtime='--:--';}else{$overtime = date('H:i', strtotime($overtime.':'.$total_is));}
						
						$punch_in = date('H:i', strtotime($punch_in));
						$punch_out = date('H:i', strtotime($punch_out));
						$total_hours = date('H:i', strtotime($total_hours));
						
						if($day == 'Wed'){$total_hours = '--:--';$overtime = date('H:i', strtotime($total_hours));}
						
						$sortPunches = sortDates($punchDates);
						$lunch_in = '--:--';$lunch_out = '--:--';$totalPunches = count($sortPunches);$linIdx = $totalPunches - 1;
						if(intVal($totalPunches) > 2):
							$lunch_in = date('H:i', strtotime($sortPunches[1]));
							if(intVal($totalPunches) > 3):
								$lunch_out = date('H:i', strtotime($sortPunches[2]));
							endif;
						endif;
						
						$inData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">'.$punch_in.'</td>';
						$lunchInData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">'.$lunch_in.'</td>';
						$lunchOutData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">'.$lunch_out.'</td>';
						$outData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">'.$punch_out.'</td>';
						$workHrs .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">'.$total_hours.'</td>';
						$otData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">'.$overtime.'</td>';
						$status .= '<th style="border:1px solid #888;text-align:center;color:#00aa00;font-size:12px;width:40px;">P</th>';
						
						$present++;
					}
					else
					{
						$attend_status = false;
						$inData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">--:--</td>';
						$lunchInData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">--:--</td>';
						$lunchOutData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">--:--</td>';
						$outData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">--:--</td>';
						$workHrs .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">--:--</td>';
						$otData .= '<td style="border:1px solid #888;text-align:center;font-size:12px;width:40px;">--:--</td>';
						$status .= '<th style="border:1px solid #888;text-align:center;color:#cc0000;font-size:12px;width:40px;">A</th>';
						$absent++;
					}
				}
				
				$inData .= '</tr>';$outData .= '</tr>';$lunchInData .= '</tr>';
				$lunchOutData .= '</tr>';$workHrs .= '</tr>';$otData .= '</tr>';$status .= '</tr>';
				
				$wh = $wh + intVal($wi / 60);$wi = intVal(floatVal($wi) % 60);$wh = $wh.':'.$wi;
				
				$empTable = '<table class="table-bordered" style="border:1px solid #888;margin-bottom:10px;">';
				$empTable .='<tr style="background:#eeeeee;">';
					$empTable .='<th style="border:1px solid #888;font-size:12px;">Empcode</th>';
					$empTable .='<th style="border:1px solid #888;text-align:center;font-size:12px;" colspan="2">'.$ecode.'</th>';
					$empTable .='<th style="border:1px solid #888;font-size:12px;" colspan="2">Name</th>';
					$empTable .='<th style="border:1px solid #888;text-align:left;font-size:12px;" colspan="'.($last_day - 20).'">'.$emp->emp_name.'</th>';
					$empTable .='<th style="border:1px solid #888;color:#00aa00;font-size:12px;" colspan="2">Present</th>';
					$empTable .='<th style="border:1px solid #888;color:#00aa00;font-size:12px;">'.$present.'</th>';
					$empTable .='<th style="border:1px solid #888;color:#cc0000;font-size:12px;" colspan="2">Absent</th>';
					$empTable .='<th style="border:1px solid #888;color:#cc0000;font-size:12px;">'.$absent.'</th>';
					$empTable .='<th style="border:1px solid #888;font-size:12px;">LV</th>';
					$empTable .='<th style="border:1px solid #888;font-size:12px;">'.$leave.'</th>';
					$empTable .='<th style="border:1px solid #888;font-size:12px;">WO</th>';
					$empTable .='<th style="border:1px solid #888;font-size:12px;">'.$wo.'</th>';
					$empTable .='<th style="border:1px solid #888;font-size:12px;">WH</th>';
					$empTable .='<th style="border:1px solid #888;font-size:12px;" colspan="2">'.$wh.'</th>';
					$empTable .='<th style="border:1px solid #888;font-size:12px;" colspan="2">Total OT</th>';
					$empTable .='<th style="border:1px solid #888;font-size:12px;">'.$oth.'</th>';
				$empTable .='</tr>';
					
				$empTable .='<tr><td rowspan="2" style="border:1px solid #888;font-size:12px;text-align:center;">#</td>'.$theadDate.'</tr>';
				$empTable .='<tr>'.$theadDay.'</tr>';
				$empTable .= $inData.$lunchInData.$lunchOutData.$outData.$workHrs.$otData.$status;
				$empTable .= '</table>';
				$response .= $empTable;
				if($empCount == 4){$pageData[] = $response;$response='';$empCount=1;}else{$empCount++;}
			}
		}
		
		if($file_type == 'excel')
		{
			$xls_filename = 'monthlyAttendance.xls';
			
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename='.$xls_filename);
			header('Pragma: no-cache');
			header('Expires: 0');
			
			echo $response;
		}
		else
		{
			$htmlHeader = '<div class="table-wrapper">
								<table class="table txInvHead">
									<tr class="txRow">
										<td class="fs-17 text-left" style="letter-spacing: 1px;font-weight:bold;">'.$companyData->company_name.'</td>
										<td class="text-right pad-right-10"><b>Report Month : '.date("F-Y",strtotime($year.'-'.$month.'-01')).'</td>
									</tr>
								</table>
							</div>';
			$htmlFooter = '<table style="border-top:1px solid #000;padding:3px;">
							<tr><td style="width:50%;text-align:left;">Printed On {DATE j-m-Y}</td><td style="width:50%;text-align:right;">Page No :- {PAGENO}</td></tr>
							</table>';
			
			$mpdf = new \Mpdf\Mpdf();
			$pdfFileName='monthlyAttendance.pdf';
			$stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
			$mpdf->WriteHTML($stylesheet,1);
			$mpdf->SetDisplayMode('fullpage');
			$mpdf->SetProtection(array('print'));
			
			$mpdf->SetHTMLHeader($htmlHeader);
			$mpdf->SetHTMLFooter($htmlFooter);
			
			foreach($pageData as $page):
				$mpdf->AddPage('L','','','','',5,5,17,10,5,0,'','','','','','','','','','A4-L');
				$mpdf->WriteHTML($page);
			endforeach;
			$mpdf->Output($pdfFileName,'I');
		}
        
    }

	public function monthlyAttendanceSummary(){
        $this->data['empList'] = $this->employee->getEmployeeList(1);
        $this->load->view($this->monthSummary,$this->data);
    }

    public function printMonthlySummary1($month,$file_type = 'excel'){
	
		set_time_limit(0);
		$empData = $this->attendance->getEmployeeList('',1);
		$companyData = $this->attendance->getCompanyInfo();
		$current_month  = date("m");
		$year  = ((int)$month >= 1 and (int)$month < 4)?$this->session->userdata('endYear') : $this->session->userdata('startYear');;
		$FromDate = date("d/m/Y_00:01",strtotime($year.'-'.$month.'-01'));
		$ToDate  = date("t/m/Y_23:59",strtotime($year.'-'.$month.'-01'));
		
		$fdate = date("Y-m-d 00:00:01",strtotime($year.'-'.$month.'-01'));
		$tdate  = date("Y-m-t 23:59:59",strtotime($year.'-'.$month.'-01'));
		
		$first_day = 1;
		$last_day = date("t",strtotime($year.'-'.$month.'-01'));
		
		$punchData = NULL;
		// $attendanceDataDB = $this->attendance->getEmployeePunchDataDB($fdate,$tdate);
		// if(!empty($attendanceDataDB)){$punchData = $attendanceDataDB->punchdata;}
		$thead ='';$tbody ='';$i=1;$printData='';$empCount = 1;
		// if(empty($punchData)):
		// 	$punchData = $this->attendance->saveBiometricData($fdate,$tdate);
		// else:
		// 	$punchData = json_decode($punchData);
		// endif;
		$punchData = $this->attendance->saveBiometricData($fdate,$tdate);
        $empTable = '';
		$emp1 = Array();$response = '';$pageData = Array();
		if(!empty($empData))
		{
			foreach($empData as $emp)
			{
				$ecode = sprintf("%04d", $emp->biomatric_id);				
				$empPucnhes = array_keys(array_combine(array_keys($punchData), array_column($punchData, 'Empcode')),$ecode);
				
				for($d=1;$d<=$last_day;$d++)
				{
					$attend_status = false;$wo = 0;$wh = 0;$wi = 0;$late = '';
					$workHrs = '';$otData = '';$status = '';$exOtHrs = '';$totalWorkHrs = '';$lateStatus = '';$exHrs = '';$punches = '';
					$currentDate = date('d/m/Y', strtotime($year.'-'.$month.'-'.$d));$punchDates = Array();$punchTimes = Array();
                    $today = date("d-m-Y",strtotime($year.'-'.$month.'-'.$d));
					$day = date("D",strtotime($year.'-'.$month.'-'.$d));if($day == 'Wed'){$wo++;}
					
					// Get Device Punches
					if(!empty($empPucnhes))
					{
						foreach($empPucnhes as $punch)
						{
							$todayPunch = $punchData[$punch];							
							if($currentDate == date('d/m/Y', strtotime(strtr($todayPunch->PunchDate, '/', '-')))) 
							{
								$punchDates[]=date('Y/m/d H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
								$punchTimes[]=date('H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
							}
							
						}						
					}
					// Get Manual Punches
					$mpData = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($today)),$emp->id);
					if(!empty($mpData))
					{
						foreach($mpData as $mpRow):
							$time = explode(" ",$mpRow->punch_in);
							$punchDates[]=date('Y/m/d H:i:s',strtotime(strtr($mpRow->punch_in, '/', '-')));
							$punchTimes[] = date("H:i:s",strtotime($time[1]));
						endforeach;
					}
					
					if(!empty($punchDates))
					{
						$attend_status = true;
						$punch_in = date('d-m-Y H:i:s', strtotime(min($punchDates)));
						$shiftStart = date('d-m-Y H:i:s', strtotime($d.'-'.$month.'-'.$year.' '.$emp->shift_start));
						$shiftEnd = date('d-m-Y H:i:s', strtotime('+8 hours',strtotime($shiftStart)));
						$punch_out = date('d-m-Y H:i:s', strtotime(max($punchDates)));
						if(strtotime($punch_in) > strtotime($shiftEnd))
						{
							$shiftEnd = date('d-m-Y H:i:s', strtotime($year.'-'.$month.'-'.$d.' 23:59:59'));
						}
						if( count($punchDates) == 1 ):
							$punch_out = $shiftEnd;
						endif;

						$late_in =  date('d-m-Y H:i:s', strtotime($shiftStart.' + '.intVal($emp->late_in).' minute'));
						$late = ($punch_in > $late_in) ? 'Y' : '';
						
						$time1 = new DateTime(date('H:i:s',strtotime($punch_in)));
						$time2 = new DateTime(date('H:i:s',strtotime($punch_out)));
						$interval = $time1->diff($time2);
						$total_hours = $interval->format('%H:%I:%S');
						$total_is = $interval->format('%I:%S');
						
						$punch_in = date('H:i', strtotime($punch_in));
						$punch_out = date('H:i', strtotime($punch_out));
						$total_hours = date('H:i', strtotime($total_hours));
						
						// Total Hours Calculation
						$totalHrs = explode(':',$total_hours);
						$whrs = 0;
						if(intVal($totalHrs[0]) > 0 OR intVal($totalHrs[1]) > 0):
							$whrs = (intVal($totalHrs[0]) * 3600) + (intVal($totalHrs[1]) * 60);
						endif;

						// Shift Time Calculation
						$totalShiftTime = explode(':',$emp->total_shift_time);
						$stime = 0;
						if(intVal($totalShiftTime[0]) > 0 OR intVal($totalShiftTime[1]) > 0):
							$stime = (intVal($totalShiftTime[0]) * 3600) + (intVal($totalShiftTime[1]) * 60);
						endif;
						
						
						//if(empty($overtime) or $overtime < 0){$overtime='--:--';$ot=0;}else{$overtime = date('H:i', strtotime(($overtime * 3600)));}
						
						$all_puch = sortDates($punchTimes);
						$twh = 0;$TWHRS=0;$t=1;$wph = Array();$idx=0;
						foreach($all_puch as $punch)
						{
							$twh = 0;
							$tm = explode(':',$punch);
							if(intVal($tm[0]) > 0 OR intVal($tm[1]) > 0):
								$twh = (intVal($tm[0]) * 3600) + (floatVal($tm[1]) * 60);
							endif;
							
							$wph[$idx][]=$twh;
							if($t%2 == 0){$TWHRS += floatVal($wph[$idx][1]) - floatVal($wph[$idx][0]);$idx++;}
							$t++;
						}
						$punches = implode(', ',sortDates($punchTimes));
						
						$ot = $ot1 = (intVal($TWHRS) > intVal($stime)) ? (intVal($TWHRS) - intVal($stime)) : 0;
						
						$wh = intVal($TWHRS) - intVal($ot);
						
						$work_hours = floor($wh / 3600) .':'. floor($wh / 60 % 60);
						$ot = floor($ot / 3600) .':'. floor($ot / 60 % 60);
						$TWHRS = floor($TWHRS / 3600) .':'. floor($TWHRS / 60 % 60);
						
						$status = '<td style="text-align:center;color:#00aa00;font-size:12px;">P</td>';
						$workHrs = '<td style="text-align:center;font-size:12px;">'.$work_hours.'</td>';
						$otData = '<td style="text-align:center;font-size:12px;">'.$ot.'</td>';
						$exOtHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
						$exHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
						$totalWorkHrs = '<td style="text-align:center;font-size:12px;">'.$TWHRS.'</td>';
						$lateStatus = '<td style="text-align:center;font-size:12px;">'.$late.'</td>';	
					}
					else
					{
						$attend_status = false;
						$status = '<td style="text-align:center;color:#aa0000;font-size:12px;">A</td>';
						$workHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
						$otData = '<td style="text-align:center;font-size:12px;">--:--</td>';
						$exOtHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
						$exHrs= '<td style="text-align:center;font-size:12px;">--:--</td>';
						$totalWorkHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
						$lateStatus = '<td style="text-align:center;font-size:12px;">'.$late.'</td>';
					}
					
					$empTable .='<tr>';
						$empTable .='<td style="text-align:center;font-size:12px;">'.$ecode.'</td>';
						$empTable .='<td style="text-align:left;font-size:12px;">'.$emp->emp_name.'</td>';
						$empTable .='<td style="font-size:12px;">'.$emp->name.'</td>';
						$empTable .='<td style="font-size:12px;">'.$emp->shift_name.'</td>';
						$empTable .='<td style="font-size:12px;">'.$today.'</td>';
						$empTable .= $status.$workHrs.$otData.$exOtHrs.$exHrs.$totalWorkHrs.$lateStatus;
						$empTable .='<td style="font-size:12px;text-align:left;">'.$punches.'</td>';
					$empTable .='</tr>';
					
				}
			}
		}
		
		$response = '<table class="table-bordered itemList" border="1" repeat_header="1">';
			$response .= '<thead>
					<tr style="background:#eee;">
						<th>Emp Code</th>
						<th>Employee</th>
						<th>Department</th>
						<th>Shift</th>
						<th>Punch Date</th>
						<th>Status</th>
						<th>WH</th>
						<th>OT</th>
						<th>Ex. OT</th>
						<th>Ex. Hours</th>
						<th>TWH</th>
						<th>Late</th>
						<th>All Pucnhes</th>
					</tr></thead><tbody>'.$empTable.'</tbody></table>';
		
		// echo $response;exit;
		
		if($file_type == 'excel')
		{
			$xls_filename = 'monthlyAttendance.xls';
			
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename='.$xls_filename);
			header('Pragma: no-cache');
			header('Expires: 0');
			
			echo $response;
		}
		else
		{
			$htmlHeader = '<div class="table-wrapper">
								<table class="table txInvHead">
									<tr class="txRow">
										<td class="fs-17 text-left" style="letter-spacing: 1px;font-weight:bold;">'.$companyData->company_name.'</td>
										<td class="text-right pad-right-10"><b>Report Month : '.date("F-Y",strtotime($year.'-'.$month.'-01')).'</td>
									</tr>
								</table>
							</div>';
			$htmlFooter = '<table style="border-top:1px solid #000;padding:3px;">
							<tr><td style="width:50%;text-align:left;">Printed On {DATE j-m-Y}</td><td style="width:50%;text-align:right;">Page No :- {PAGENO}</td></tr>
							</table>';
			
			$mpdf = new \Mpdf\Mpdf();
			$pdfFileName='monthlyAttendance.pdf';
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

    public function printMonthlySummary($dates,$biomatric_id="ALL",$file_type = 'excel'){
		
		set_time_limit(0);
		if(!empty($dates))
		{
			$duration = explode('~',$dates);
			$biomatric_id = ($biomatric_id == "ALL") ? '' : $biomatric_id ;
			$empData = $this->attendance->getEmployeeList($biomatric_id);
// 			$empData = $this->attendance->getEmployeeList('20400');
			$companyData = $this->attendance->getCompanyInfo();
			$current_month  = date("m");
			$month  = date("m",strtotime($duration[0]));
			$year  = date("Y",strtotime($duration[0]));
			$FromDate = date("Y-m-d",strtotime($duration[0]));
			$ToDate  = date("Y-m-d",strtotime($duration[1]));
			
			$fdate = date("Y-m-d 00:00:01",strtotime($duration[0]));
			$tdate  = date("Y-m-d 23:59:59",strtotime($duration[1]));
			
			$first_day = date("d",strtotime($duration[0]));
			$last_day = date("d",strtotime($duration[1]));
			
			$empTable = '';
			$thead ='';$tbody ='';$i=1;$printData='';$empCount = 1;
			$begin = new DateTime($FromDate);
			$end = new DateTime($ToDate);
			$end = $end->modify( '+1 day' ); 
			
			$interval = new DateInterval('P1D');
			$daterange = new DatePeriod($begin, $interval ,$end);
			
			foreach($daterange as $date)
			{
				$currentDate =  date("Y-m-d",strtotime($date->format("Y-m-d")));
				$nextDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
				$currentDay = date('D', strtotime($currentDate));
				$punchData = New StdClass();
				$todayPunchData = $this->biometric->getPunchData($currentDate,$nextDate);
				
				$punches = Array();
				foreach($todayPunchData as $pnc)
				{
					$jarr = json_decode($pnc->punch_data);
					$punches = array_merge($punches,$jarr);
				}
				
				if(!empty($empData))
				{
					foreach($empData as $emp)
					{
    						$ecode = sprintf("%04d", $emp->biomatric_id);$punchDates = Array();$punchTimes = Array();$mflag=Array();
    						$attend_status = false;$wo = 0;$wh = 0;$wi = 0;$late = '';$allPunches ='';$dorn=1;$present_status = 'P';
    						$workHrs = '';$otData = '';$status = '';$exOtHrs = '';$totalWorkHrs = '';$lateStatus = '';$exHrs = '';
					    if(!empty($emp->emp_joining_date) AND (strtotime($emp->emp_joining_date) <= strtotime($currentDate)))
					    {
    						
    						// Get ShiftData
    						$shiftData = $this->shiftModel->getAttendanceLog($currentDate,$emp->id);
    						$emp->shift_start = $shiftData->shift_start;
    						$emp->shift_end = $shiftData->shift_end;
    						$emp->shift_id = $shiftData->shift_id;
    						$emp->shift_name = $shiftData->shift_name;
    						$emp->total_shift_time = $shiftData->total_shift_time;
    						$nextDayShiftData = $this->shiftModel->getAttendanceLog($nextDate,$emp->id);
    						$shiftStart = date('d-m-Y H:i:s', strtotime($currentDate.' '.$emp->shift_start. ' -180 minutes'));
    						if($emp->shift_end < date('H:i:s', strtotime('11:00:00'))) // Night Shift
    						{
    							if(!empty($nextDayShiftData))
    							{
    								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$nextDayShiftData->shift_start. ' -180 minutes'));
    							}
    							else
    							{
    								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$shiftData->shift_start. ' -180 minutes'));
    							}
    							//$shiftStart = date('d-m-Y H:i:s', strtotime($currentDate.' 13:15:00'));
    							//$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' 12:30:00'));
    							$dorn=2;
    						}
    						else
    						{
    							if(!empty($nextDayShiftData))
    							{
    								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$nextDayShiftData->shift_start. ' -180 minutes'));
    							}
    							else
    							{
    								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$shiftData->shift_start. ' -180 minutes'));
    							}
    							//$shiftStart = date('d-m-Y H:i:s', strtotime($currentDate.' 04:30:00'));
    							//$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' 04:00:00'));
    						}
    						//print_r($nextDayShiftData);
    						//print_r($emp->shift_end.'@@@'.$shiftStart.' *** '.$shiftEnd);
    						$empPucnhes = array_keys(array_combine(array_keys($punches), array_column($punches, 'Empcode')),$ecode);
    						if(!empty($empPucnhes))
    						{
    							foreach($empPucnhes as $punch)
    							{
    								$todayPunch = $punches[$punch];	
    								$pnchDate = date('d-m-Y H:i:s', strtotime(strtr($todayPunch->PunchDate, '/', '-')));
    								if((strtotime($pnchDate) >= strtotime($shiftStart)) AND (strtotime($pnchDate) <= strtotime($shiftEnd)))
    								//if(($pnchDate >= $shiftStart))
    								{
    									$punchDates[]=date('Y-m-d H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
    									$punchTimes[]=date('H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
    									$mflag[]='S';
    								}
    							}
    						}
    						// Get Manual Punches
    						$mpData = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($currentDate)),$emp->id);
    						$mpDataND = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($nextDate)),$emp->id);
    						$mpData = array_merge($mpData,$mpDataND);
    						if(!empty($mpData))
    						{
    							foreach($mpData as $mpRow):
    								$time = explode(" ",$mpRow->punch_in);
    								
    								$pDate = date('Y-m-d H:i:s',strtotime(strtr($mpRow->punch_in, '/', '-')));
                					if((strtotime($pDate) >= strtotime($shiftStart)) AND (strtotime($pDate) <= strtotime($shiftEnd)))
                					{
                					    $punchDates[]=$pDate;
        								$punchTimes[] = date("H:i:s",strtotime($time[1]));
        								$mflag[]=$pDate;
                					}
    							endforeach;
    						}
    						
    						if(!empty($punchDates))
    						{
    							$attend_status = true;if($currentDay == 'Wed'){$present_status = 'WP';}
    							$punch_in = date('d-m-Y H:i:s', strtotime(min($punchDates)));
    							$punch_out = date('d-m-Y H:i:s', strtotime(max($punchDates)));
    							if( count($punchDates) == 1 ):
    								$punch_out = $shiftEnd;
    							endif;
    
    							$late_in =  date('d-m-Y H:i:s', strtotime($shiftStart.' + '.intVal($emp->late_in).' minute'));
    							$late = ($punch_in > $late_in) ? 'Y' : '';
    							
    							$time1 = new DateTime(date('H:i:s',strtotime($punch_in)));
    							$time2 = new DateTime(date('H:i:s',strtotime($punch_out)));
    							$interval = $time1->diff($time2);
    							$total_hours = $interval->format('%H:%I:%S');
    							$total_is = $interval->format('%I:%S');
    							
    							$punch_in = date('H:i', strtotime($punch_in));
    							$punch_out = date('H:i', strtotime($punch_out));
    							$total_hours = date('H:i', strtotime($total_hours));
    							
    							// Total Hours Calculation
    							$totalHrs = explode(':',$total_hours);
    							// Get Extra Hours
    							$exHrsTime = '';$exTime = 0;
    							$exHrsData = $this->attendance->getExtraHours(date("Y-m-d",strtotime($currentDate)),$emp->id);
    							
    							if(!empty($exHrsData))
    							{
    								$exTime = (intVal($exHrsData->ex_hours) * 3600) + (intVal($exHrsData->ex_mins) * 60);
    								$exh = (!empty($exHrsData->ex_hours)) ? $exHrsData->ex_hours : '00';
    								$exm = (!empty($exHrsData->ex_mins)) ? $exHrsData->ex_mins : '00';
    								
    								if($exh < 0 OR $exm < 0):
    								    $exHrsTime = '<td style="text-align:center;color:#aa0000;font-size:12px;font-weight:bold;">'.abs($exh).' H : '.abs($exm).' M</td>';
    								else:
    								    $exHrsTime = '<td style="text-align:center;font-size:12px;">'.abs($exh).' H : '.abs($exm).' M</td>';
    								endif;
    							}
    							// Shift Time Calculation
    							$totalShiftTime = (!empty($emp->total_shift_time)) ? explode(':',$emp->total_shift_time) : explode(':','08:45');
    							$stime = 0;$stime = 0;//print_r($totalShiftTime);print_r(' = '.$emp->emp_code.'@'.$currentDate.'<br>');
    							if(intVal($totalShiftTime[0]) > 0 OR intVal($totalShiftTime[1]) > 0):
    								$stime = (intVal($totalShiftTime[0]) * 3600) + (intVal($totalShiftTime[1]) * 60);
    							endif;
    							
    							//$all_puch = sortDates($punchTimes);
    							$all_puch = sortDates($punchDates);
    							$twh = 0;$TWHRS=0;$t=1;$wph = Array();$idx=0;
    							foreach($all_puch as $punch)
    							{
    								$wph[$idx][]=strtotime($punch);
    								if($t%2 == 0){$TWHRS += floatVal($wph[$idx][1]) - floatVal($wph[$idx][0]);$idx++;}
    								$t++;
    							}
    							
    							// Count Lunch Time (If Lunch time > 45 Mins then Lunch Time Time is Actual Otherwise 45 Mins Fixed
    							$countedLT =0;$ltime = 0;
    							if(count($wph) >= 2){$countedLT = floatVal($wph[1][0]) - floatVal($wph[0][1]);}
    							$halfDayTime = intval($stime/2) + 60;
    							$lunchTime = 0;
    							if((intVal($TWHRS) > $halfDayTime) OR (count($wph) >= 2)){$lunchTime = 2700;}
    							//if(count($wph) > 2){$lunchTime = 2700;}
    							$ltime = $lunchTime;
    							if($countedLT < 2700){$lunchTime -= $countedLT;}else{$lunchTime = 0;$ltime = $countedLT;}
    							$TWHRS = $TWHRS - $lunchTime;
    							
    							$ot = $ot1 = (intVal($TWHRS) > intVal($stime)) ? (intVal($TWHRS) - intVal($stime)) : 0;
    							
    							$wh = intVal($TWHRS) - intVal($ot);
    							
    							$work_hours = floor($wh / 3600) .':'. floor($wh / 60 % 60);
    							$ot = floor($ot / 3600) .':'. floor($ot / 60 % 60);
    							$TWHRS += $exTime;$totalWorkTime = $TWHRS;
    							$TWHRS = floor($TWHRS / 3600) .':'. floor($TWHRS / 60 % 60);
    							$lunchTime = floor($ltime / 3600) .':'. floor($ltime / 60 % 60);
    							
    							// $allPunchDates = implode(', ',sortDates($punchDates));
    							// if($dorn==2){$allPunches = implode(', ',sortDates($allPunchDates,'DESC'));}
    							$allPunches = '';
    							if(!empty($punchDates))
    							{
    								$allPunchDates = ($dorn==2) ? sortDates($punchDates) : sortDates($punchDates);
    								$ap = Array();
    								foreach($allPunchDates as $p)
    								{
    									$spanTag = '';
    									if(in_array($p,$mflag)){$ap[] = '<b>'.date("d H:i:s",strtotime($p)).'</b>';}
    									else{$ap[] = date("d H:i:s",strtotime($p));}
    								}
    								$allPunches = implode(', ',$ap);
    							}
    							
    							
    							// Check For Missed Punch
    							if(count($punchTimes) % 2 != 0)
    							{
    								$status = '<td style="text-align:center;color:#233288;font-size:12px;">M</td>';
    							}
    							else
    							{
    								if($totalWorkTime <= 0){$status = '<td style="text-align:center;color:#aa0000;font-size:12px;">A</td>';}
    								else{$status = '<td style="text-align:center;color:#00aa00;font-size:12px;">'.$present_status.'</td>';}
    							}
    							$workHrs = '<td style="text-align:center;font-size:12px;">'.$work_hours.'</td>';
    							$ltTd = '<td style="text-align:center;font-size:12px;">'.$lunchTime.'</td>';
    							$exHrs = $exHrsTime;//'<td style="text-align:center;font-size:12px;">'.$exHrsTime.'</td>';
    							$otData = '<td style="text-align:center;font-size:12px;">'.$ot.'</td>';
    							$totalWorkHrs = '<td style="text-align:center;font-size:12px;">'.$TWHRS.'</td>';
    							$lateStatus = '<td style="text-align:center;font-size:12px;">'.$late.'</td>';	
    						} 
    						else
    						{
    							$attend_status = false;if($currentDay == 'Wed'){$present_status = 'W';}else{$present_status='A';}
    							$status = '<td style="text-align:center;color:#aa0000;font-size:12px;">'.$present_status.'</td>';
    							$workHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$ltTd = '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$exHrs= '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$otData = '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$totalWorkHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$lateStatus = '<td style="text-align:center;font-size:12px;">'.$late.'</td>';
    						} 
					    }	
						else
						{
						    $emp->shift_name = 'NA';$allPunches='NA';
							$status = '<td style="text-align:center;;font-size:12px;">NA</td>';
							$workHrs = '<td style="text-align:center;font-size:12px;">NA</td>';
							$ltTd = '<td style="text-align:center;font-size:12px;">NA</td>';
							$exHrs= '<td style="text-align:center;font-size:12px;">NA</td>';
							$otData = '<td style="text-align:center;font-size:12px;">NA</td>';
							$totalWorkHrs = '<td style="text-align:center;font-size:12px;">NA</td>';
							$lateStatus = '<td style="text-align:center;font-size:12px;">NA</td>';
						}
					
						$empTable .='<tr>';
							$empTable .='<td style="text-align:center;font-size:12px;">'.$ecode.'</td>';
							$empTable .='<td style="text-align:left;font-size:12px;">'.$emp->emp_name.'</td>';
							$empTable .='<td style="font-size:12px;">'.$emp->name.'</td>';
							$empTable .='<td style="font-size:12px;">'.$emp->shift_name.'</td>';
							$empTable .='<td style="font-size:12px;">'.date("d-m-Y",strtotime($currentDate)).'</td>';
							$empTable .= $status.$workHrs.$ltTd.$exHrs.$otData.$totalWorkHrs.$lateStatus;
							$empTable .='<td style="font-size:12px;text-align:left;">'.$allPunches.'</td>';
						$empTable .='</tr>';
					}
				}
			}
			
			
			$response = '<table class="table-bordered itemList" border="1" repeat_header="1">';
				$response .= '<thead>
						<tr style="background:#eee;">
							<th>Emp Code</th>
							<th>Employee</th>
							<th>Department</th>
							<th>Shift</th>
							<th>Punch Date</th>
							<th>Status</th>
							<th>WH</th>
							<th>Lunch</th>
							<th>Ex. Hours</th>
							<th>OT</th>
							<th>TWH</th>
							<th>Late</th>
							<th>All Pucnhes</th>
						</tr></thead><tbody>'.$empTable.'</tbody></table>';
 			//echo $response;exit;
			if($file_type == 'excel')
			{
				$xls_filename = 'monthlyAttendance.xls';
				
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment; filename='.$xls_filename);
				header('Pragma: no-cache');
				header('Expires: 0');
				
				echo $response;
			}
			else
			{
				$htmlHeader = '<div class="table-wrapper">
									<table class="table txInvHead">
										<tr class="txRow">
											<td class="fs-17 text-left" style="letter-spacing: 1px;font-weight:bold;">'.$companyData->company_name.'</td>
											<td class="text-right pad-right-10"><b>Report Month : '.date("F-Y",strtotime($year.'-'.$month.'-01')).'</td>
										</tr>
									</table>
								</div>';
				$htmlFooter = '<table style="border-top:1px solid #000;padding:3px;">
								<tr><td style="width:50%;text-align:left;">Printed On {DATE j-m-Y}</td><td style="width:50%;text-align:right;">Page No :- {PAGENO}</td></tr>
								</table>';
				
				$mpdf = new \Mpdf\Mpdf();
				$pdfFileName='monthlyAttendance.pdf';
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

	public function monthlyAttendanceSummaryNew(){
        $this->data['empList'] = $this->employee->getEmployeeList('emp_code');
        $this->load->view($this->monthlySummary,$this->data);
    }

    public function printMonthlySummaryNew($dates,$biomatric_id="ALL",$file_type = 'excel'){
		
		set_time_limit(0);
		if(!empty($dates))
		{
			$duration = explode('~',$dates);
			$biomatric_id = ($biomatric_id == "ALL") ? '' : $biomatric_id ;
			//$empData = $this->attendance->getEmployeeList($biomatric_id);
			$empData = $this->attendance->getEmployeeList('10057');
			$companyData = $this->attendance->getCompanyInfo();
			$current_month  = date("m");
			$month  = date("m",strtotime($duration[0]));
			$year  = date("Y",strtotime($duration[0]));
			$FromDate = date("Y-m-d",strtotime($duration[0]));
			$ToDate  = date("Y-m-d",strtotime($duration[1]));
			
			$fdate = date("Y-m-d 00:00:01",strtotime($duration[0]));
			$tdate  = date("Y-m-d 23:59:59",strtotime($duration[1]));
			
			$first_day = date("d",strtotime($duration[0]));
			$last_day = date("d",strtotime($duration[1]));
			
			$empTable = '';
			$thead ='';$tbody ='';$i=1;$printData='';$empCount = 1;
			$begin = new DateTime($FromDate);
			$end = new DateTime($ToDate);
			$end = $end->modify( '+1 day' ); 
			
			$interval = new DateInterval('P1D');
			$daterange = new DatePeriod($begin, $interval ,$end);
			
			foreach($daterange as $date)
			{
				$currentDate =  date("Y-m-d",strtotime($date->format("Y-m-d")));
				$nextDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
				$currentDay = date('D', strtotime($currentDate));
				$punchData = New StdClass();
				$todayPunchData = $this->biometric->getPunchData($currentDate,$nextDate);
				$punches = Array();
				foreach($todayPunchData as $pnc)
				{
					$jarr = json_decode($pnc->punch_data);
					$punches = array_merge($punches,$jarr);
				}
				
				if(!empty($empData))
				{
					foreach($empData as $emp)
					{
						$ecode = sprintf("%04d", $emp->biomatric_id);$punchDates = Array();$punchTimes = Array();$mflag=Array();
						$attend_status = false;$wo = 0;$wh = 0;$wi = 0;$late = '';$allPunches ='';$dorn=1;$present_status = 'P';
						$workHrs = '';$otData = '';$status = '';$exOtHrs = '';$totalWorkHrs = '';$lateStatus = '';$exHrs = '';
						
						$todayPunches = $this->biometric->getAttendanceLogByEmp($currentDate,$emp->id);
						$nextDayPunches = $this->biometric->getAttendanceLogByEmp($nextDate,$emp->id);
						$empPucnhes = array_merge($todayPunches,$nextDayPunches);
						print_r($empPucnhes);exit;
						// Get ShiftData
						/* $shiftData = $this->shiftModel->getAttendanceLog($currentDate,$emp->id);
						$emp->shift_start = $shiftData->shift_start;
						$emp->shift_end = $shiftData->shift_end;
						$emp->shift_id = $shiftData->shift_id;
						$emp->shift_name = $shiftData->shift_name;
						$emp->total_shift_time = $shiftData->total_shift_time;
						$nextDayShiftData = $this->shiftModel->getAttendanceLog($nextDate,$emp->id); */
						$shiftStart = date('d-m-Y H:i:s', strtotime($currentDate.' '.$todayPunches->shift_start. ' -180 minutes'));
						if($todayPunches->shift_end < date('H:i:s', strtotime('11:00:00'))) // Night Shift
						{
							if(!empty($nextDayPunches))
							{
								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$nextDayPunches->shift_start. ' -180 minutes'));
							}
							else
							{
								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$shiftData->shift_start. ' -180 minutes'));
							}
							$dorn=2;
						}
						else
						{
							if(!empty($nextDayPunches))
							{
								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$nextDayPunches->shift_start. ' -180 minutes'));
							}
							else
							{
								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$shiftData->shift_start. ' -180 minutes'));
							}
						}
						//$empPucnhes = array_keys(array_combine(array_keys($punches), array_column($punches, 'Empcode')),$ecode);
						
						if(!empty($empPucnhes))
						{
							foreach($empPucnhes as $punch)
							{
								$todayPunch = $punch->punch_date;	
								$pnchDate = date('d-m-Y H:i:s', strtotime(strtr($todayPunch->PunchDate, '/', '-')));
								if((strtotime($pnchDate) >= strtotime($shiftStart)) AND (strtotime($pnchDate) <= strtotime($shiftEnd)))
								//if(($pnchDate >= $shiftStart))
								{
									$punchDates[]=date('Y-m-d H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
									$punchTimes[]=date('H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
									$mflag[]='S';
								}
							}
						}
						// Get Manual Punches
						/*$mpData = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($currentDate)),$emp->id);
						$mpDataND = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($nextDate)),$emp->id);
						$mpData = array_merge($mpData,$mpDataND);
						if(!empty($mpData))
						{
							foreach($mpData as $mpRow):
								$time = explode(" ",$mpRow->punch_in);
								
								$pDate = date('Y-m-d H:i:s',strtotime(strtr($mpRow->punch_in, '/', '-')));
            					if((strtotime($pDate) >= strtotime($shiftStart)) AND (strtotime($pDate) <= strtotime($shiftEnd)))
            					{
            					    $punchDates[]=$pDate;
    								$punchTimes[] = date("H:i:s",strtotime($time[1]));
    								$mflag[]=$pDate;
            					}
							endforeach;
						}*/
						
						if(!empty($punchDates))
						{
							$attend_status = true;if($currentDay == 'Wed'){$present_status = 'WP';}
							$punch_in = date('d-m-Y H:i:s', strtotime(min($punchDates)));
							$punch_out = date('d-m-Y H:i:s', strtotime(max($punchDates)));
							if( count($punchDates) == 1 ):
								$punch_out = $shiftEnd;
							endif;

							$late_in =  date('d-m-Y H:i:s', strtotime($shiftStart.' + '.intVal($emp->late_in).' minute'));
							$late = ($punch_in > $late_in) ? 'Y' : '';
							
							$time1 = new DateTime(date('H:i:s',strtotime($punch_in)));
							$time2 = new DateTime(date('H:i:s',strtotime($punch_out)));
							$interval = $time1->diff($time2);
							$total_hours = $interval->format('%H:%I:%S');
							$total_is = $interval->format('%I:%S');
							
							$punch_in = date('H:i', strtotime($punch_in));
							$punch_out = date('H:i', strtotime($punch_out));
							$total_hours = date('H:i', strtotime($total_hours));
							
							// Total Hours Calculation
							$totalHrs = explode(':',$total_hours);
							// Get Extra Hours
							$exHrsTime = '';$exTime = 0;
							$exHrsData = $this->attendance->getExtraHours(date("Y-m-d",strtotime($currentDate)),$emp->id);
							
							if(!empty($exHrsData))
							{
								$exTime = (intVal($exHrsData->ex_hours) * 3600) + (intVal($exHrsData->ex_mins) * 60);
								$exh = (!empty($exHrsData->ex_hours)) ? $exHrsData->ex_hours : '00';
								$exm = (!empty($exHrsData->ex_mins)) ? $exHrsData->ex_mins : '00';
								
								if($exh < 0 OR $exm < 0):
								    $exHrsTime = '<td style="text-align:center;color:#aa0000;font-size:12px;font-weight:bold;">'.abs($exh).' H : '.abs($exm).' M</td>';
								else:
								    $exHrsTime = '<td style="text-align:center;font-size:12px;">'.abs($exh).' H : '.abs($exm).' M</td>';
								endif;
							}
							// Shift Time Calculation
							$totalShiftTime = (!empty($emp->total_shift_time)) ? explode(':',$emp->total_shift_time) : explode(':','08:45');
							$stime = 0;$stime = 0;//print_r($totalShiftTime);print_r(' = '.$emp->emp_code.'@'.$currentDate.'<br>');
							if(intVal($totalShiftTime[0]) > 0 OR intVal($totalShiftTime[1]) > 0):
								$stime = (intVal($totalShiftTime[0]) * 3600) + (intVal($totalShiftTime[1]) * 60);
							endif;
							
							//$all_puch = sortDates($punchTimes);
							$all_puch = sortDates($punchDates);
							$twh = 0;$TWHRS=0;$t=1;$wph = Array();$idx=0;
							foreach($all_puch as $punch)
							{
								$wph[$idx][]=strtotime($punch);
								if($t%2 == 0){$TWHRS += floatVal($wph[$idx][1]) - floatVal($wph[$idx][0]);$idx++;}
								$t++;
							}
							
							// Count Lunch Time (If Lunch time > 45 Mins then Lunch Time Time is Actual Otherwise 45 Mins Fixed
							$countedLT =0;$ltime = 0;
							if(count($wph) >= 2){$countedLT = floatVal($wph[1][0]) - floatVal($wph[0][1]);}
							$halfDayTime = intval($stime/2) + 60;
							$lunchTime = 0;
							if((intVal($TWHRS) > $halfDayTime) OR (count($wph) >= 2)){$lunchTime = 2700;}
							//if(count($wph) > 2){$lunchTime = 2700;}
							$ltime = $lunchTime;
							if($countedLT < 2700){$lunchTime -= $countedLT;}else{$lunchTime = 0;$ltime = $countedLT;}
							$TWHRS = $TWHRS - $lunchTime;
							
							$ot = $ot1 = (intVal($TWHRS) > intVal($stime)) ? (intVal($TWHRS) - intVal($stime)) : 0;
							
							$wh = intVal($TWHRS) - intVal($ot);
							
							$work_hours = floor($wh / 3600) .':'. floor($wh / 60 % 60);
							$ot = floor($ot / 3600) .':'. floor($ot / 60 % 60);
							$TWHRS += $exTime;$totalWorkTime = $TWHRS;
							$TWHRS = floor($TWHRS / 3600) .':'. floor($TWHRS / 60 % 60);
							$lunchTime = floor($ltime / 3600) .':'. floor($ltime / 60 % 60);
							
							// $allPunchDates = implode(', ',sortDates($punchDates));
							// if($dorn==2){$allPunches = implode(', ',sortDates($allPunchDates,'DESC'));}
							$allPunches = '';
							if(!empty($punchDates))
							{
								$allPunchDates = ($dorn==2) ? sortDates($punchDates) : sortDates($punchDates);
								$ap = Array();
								foreach($allPunchDates as $p)
								{
									$spanTag = '';
									if(in_array($p,$mflag)){$ap[] = '<b>'.date("d H:i:s",strtotime($p)).'</b>';}
									else{$ap[] = date("d H:i:s",strtotime($p));}
								}
								$allPunches = implode(', ',$ap);
							}
							
							
							// Check For Missed Punch
							if(count($punchTimes) % 2 != 0)
							{
								$status = '<td style="text-align:center;color:#233288;font-size:12px;">M</td>';
							}
							else
							{
								if($totalWorkTime <= 0){$status = '<td style="text-align:center;color:#aa0000;font-size:12px;">A</td>';}
								else{$status = '<td style="text-align:center;color:#00aa00;font-size:12px;">'.$present_status.'</td>';}
							}
							$workHrs = '<td style="text-align:center;font-size:12px;">'.$work_hours.'</td>';
							$ltTd = '<td style="text-align:center;font-size:12px;">'.$lunchTime.'</td>';
							$exHrs = $exHrsTime;//'<td style="text-align:center;font-size:12px;">'.$exHrsTime.'</td>';
							$otData = '<td style="text-align:center;font-size:12px;">'.$ot.'</td>';
							$totalWorkHrs = '<td style="text-align:center;font-size:12px;">'.$TWHRS.'</td>';
							$lateStatus = '<td style="text-align:center;font-size:12px;">'.$late.'</td>';	
						}
						else
						{
							$attend_status = false;if($currentDay == 'Wed'){$present_status = 'W';}else{$present_status='A';}
							$status = '<td style="text-align:center;color:#aa0000;font-size:12px;">'.$present_status.'</td>';
							$workHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
							$ltTd = '<td style="text-align:center;font-size:12px;">--:--</td>';
							$exHrs= '<td style="text-align:center;font-size:12px;">--:--</td>';
							$otData = '<td style="text-align:center;font-size:12px;">--:--</td>';
							$totalWorkHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
							$lateStatus = '<td style="text-align:center;font-size:12px;">'.$late.'</td>';
						}
					
						$empTable .='<tr>';
							$empTable .='<td style="text-align:center;font-size:12px;">'.$ecode.'</td>';
							$empTable .='<td style="text-align:left;font-size:12px;">'.$emp->emp_name.'</td>';
							$empTable .='<td style="font-size:12px;">'.$emp->name.'</td>';
							$empTable .='<td style="font-size:12px;">'.$emp->shift_name.'</td>';
							$empTable .='<td style="font-size:12px;">'.date("d-m-Y",strtotime($currentDate)).'</td>';
							$empTable .= $status.$workHrs.$ltTd.$exHrs.$otData.$totalWorkHrs.$lateStatus;
							$empTable .='<td style="font-size:12px;text-align:left;">'.$allPunches.'</td>';
						$empTable .='</tr>';
					}
				}
			}
			
			
			$response = '<table class="table-bordered itemList" border="1" repeat_header="1">';
				$response .= '<thead>
						<tr style="background:#eee;">
							<th>Emp Code</th>
							<th>Employee</th>
							<th>Department</th>
							<th>Shift</th>
							<th>Punch Date</th>
							<th>Status</th>
							<th>WH</th>
							<th>Lunch</th>
							<th>Ex. Hours</th>
							<th>OT</th>
							<th>TWH</th>
							<th>Late</th>
							<th>All Pucnhes</th>
						</tr></thead><tbody>'.$empTable.'</tbody></table>';
 			// echo $response;exit;
			if($file_type == 'excel')
			{
				$xls_filename = 'monthlyAttendance.xls';
				
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment; filename='.$xls_filename);
				header('Pragma: no-cache');
				header('Expires: 0');
				
				echo $response;
			}
			else
			{
				$htmlHeader = '<div class="table-wrapper">
									<table class="table txInvHead">
										<tr class="txRow">
											<td class="fs-17 text-left" style="letter-spacing: 1px;font-weight:bold;">'.$companyData->company_name.'</td>
											<td class="text-right pad-right-10"><b>Report Month : '.date("F-Y",strtotime($year.'-'.$month.'-01')).'</td>
										</tr>
									</table>
								</div>';
				$htmlFooter = '<table style="border-top:1px solid #000;padding:3px;">
								<tr><td style="width:50%;text-align:left;">Printed On {DATE j-m-Y}</td><td style="width:50%;text-align:right;">Page No :- {PAGENO}</td></tr>
								</table>';
				
				$mpdf = new \Mpdf\Mpdf();
				$pdfFileName='monthlyAttendance.pdf';
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

    public function loadAttendanceSheet(){
        $data = $this->input->post();
		
		$month = date('m',strtotime($data['month']));
		set_time_limit(0);
		$empData = $this->attendance->getEmployeeListForMonthAttendance();
		$companyData = $this->attendance->getCompanyInfo();
		$current_month  = date("m");
		$year  = ((int)$month >= 1 and (int)$month < 4)?$this->session->userdata('endYear') : $this->session->userdata('startYear');;
		$FromDate = date("d/m/Y_00:01",strtotime($year.'-'.$month.'-01'));
		$ToDate  = date("t/m/Y_11:59",strtotime($year.'-'.$month.'-01'));
		
		
		$first_day = 1;$punchData = NULL;$empCount = 1;$printData='';
		$last_day = date("t",strtotime($year.'-'.$month.'-01'));
		
		
		$thead ='';$tbody ='';$i=1;
		$thead .='<tr><th class="text-center" colspan="'.($last_day + 2).'">Employee Attandance Sheet for '.date("F-Y",strtotime($year.'-'.$month.'-01')).'</th></tr>';
		$thead .='<tr><th>Employee</th><th>Emp Code</th>';
		
		$emp1 = Array();$response = '';$empTable='';$pageData = Array();
		if(!empty($empData))
		{
			foreach($empData as $emp)
			{
				$ecode = sprintf("%04d", $emp->biomatric_id);
				$present = 0;$leave = 0;$absent = 0;$theadDate = '';$theadDay = '';$wo = 0;$monthWH = 0;$wh=0;$wi = 0;$oth = 0;$oti = 0;
				$inData = '';$outData = '';$lunchInData = '';$lunchOutData = '';$workHrs = '';$otData = '';$status = '';
				
				$tbody .='<tr>';
				$tbody .='<td><b>'.$emp->emp_name.'</b><br><small>'.$emp->title.'</small></td>';
				$tbody .='<td><b>'.$emp->emp_code.'</b></td>';
				for($d=1;$d<=$last_day;$d++)
				{
					$punchData = New StdClass();$empPucnhes = Array();
					$filterDate = date("Y-m-d",strtotime($year.'-'.$month.'-'.$d));
					$punchData = $this->biometric->getPunchData($filterDate,$filterDate);
					
					if(!empty($punchData))
					{
						$punches = json_decode($punchData[0]->punch_data);
						$empPucnhes = array_keys(array_combine(array_keys($punches), array_column($punches, 'Empcode')),$ecode);
					}
					$attend_status = false;
					if($i==1){$thead .='<th>'.$d.'</th>';}
					$currentDate = date('d/m/Y', strtotime($year.'-'.$month.'-'.$d));$punchDates = Array();
					
					if(!empty($empPucnhes))
					{
						foreach($empPucnhes as $punch)
						{
							$todayPunch = $punches[$punch];							
							$punchDates[]=date('Y/m/d H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
						}
					}							
					// Get Manual Punches
					$mpData = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($currentDate)),$emp->id);
					if(!empty($mpData))
					{
						foreach($mpData as $mpRow):
							$time = explode(" ",$mpRow->punch_in);
							$punchDates[]=date('Y/m/d H:i:s',strtotime(strtr($mpRow->punch_in, '/', '-')));
						endforeach;
					}
					
					if(!empty($punchDates)):
						$tbody .='<th class="text-success">P</th>';
					else:
						$tbody .='<th class="text-danger">A</th>';
					endif;
				}
				$tbody .='</tr>';$i++;
			}
		}
		$thead .='</tr>';
		
		$this->printJson(["status"=>1,"thead"=>$thead,"tbody"=>$tbody]);
    }

    public function printSalarySheet($month,$biomatric_id="ALL",$file_type = 'excel'){
        $data = $this->input->post();
		
		set_time_limit(0);
		$biomatric_id = ($biomatric_id == "ALL") ? '' : $biomatric_id ;
		$empData = $this->attendance->getEmployeeList($biomatric_id);
		$companyData = $this->attendance->getCompanyInfo();
		$current_month  = date("m");
		$year  = ((int)$month >= 1 and (int)$month < 4)?$this->session->userdata('endYear') : $this->session->userdata('startYear');
		$FromDate = date("d/m/Y_00:01",strtotime($year.'-'.$month.'-01'));
		$ToDate  = date("t/m/Y_11:59",strtotime($year.'-'.$month.'-01'));
		$last_day = date("t",strtotime($ToDate));
		
		$endDate  = date("t-m-Y",strtotime($year.'-'.$month.'-01'));
		/*if(strtotime($endDate) > strtotime(date('d-m-Y')))
		{
			$ToDate  = date("d/m/Y_11:59",strtotime(date('d-m-Y')));
			$last_day = date("d",strtotime(date('d-m-Y')));
		}*/	
		$first_day = 1;$punchData = NULL;$empCount = 1;$printData='';		
		$theadDates ='';$tbody ='';$i=1;
		
		$emp1 = Array();$response = '';$empTable='';$pageData = Array();
		if(!empty($empData))
		{
			foreach($empData as $emp)
			{
				$ecode = sprintf("%04d", $emp->biomatric_id);
				$present = 0;$leave = 0;$absent = 0;$theadDate = '';$theadDay = '';$wo = 0;$monthWH = 0;$wh=0;$wi = 0;$oth = 0;$oti = 0;
				$inData = '';$outData = '';$lunchInData = '';$lunchOutData = '';$workHrs = '';$otData = '';$status = '';
				
				if($i==1){$theadDates .='<tr><th>Employee</th><th>Emp Code</th>';}
				$tbody .='<tr>';
				$tbody .='<td><b>'.$emp->emp_name.'</b><br><small>'.$emp->title.'</small></td>';
				$tbody .='<td><b>'.$emp->emp_code.'</b></td>';
				for($d=1;$d<=$last_day;$d++)
				{
					$currentDate =  date("Y-m-d",strtotime($year.'-'.$month.'-'.$d));
					$nextDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
					if($i==1){$theadDates .='<th>'.$d.'</th>';}
					if(strtotime($currentDate) <= strtotime(date('d-m-Y')))
					{
						$currentDay = date('D', strtotime($currentDate));
						$punchData = New StdClass();
						$todayPunchData = $this->biometric->getPunchData($currentDate,$nextDate);
						$punches = Array();$punchDates = Array();
						foreach($todayPunchData as $pnc)
						{
							$jarr = json_decode($pnc->punch_data);
							$punches = array_merge($punches,$jarr);
						}
						
						// Get ShiftData
						$shiftData = $this->shiftModel->getAttendanceLog($currentDate,$emp->id);
						$emp->shift_start = $shiftData->shift_start;
						$emp->shift_end = $shiftData->shift_end;
						$emp->shift_id = $shiftData->shift_id;
						$emp->shift_name = $shiftData->shift_name;
						$emp->total_shift_time = $shiftData->total_shift_time;
						$nextDayShiftData = $this->shiftModel->getAttendanceLog($nextDate,$emp->id);
						$shiftStart = date('d-m-Y H:i:s', strtotime($currentDate.' '.$emp->shift_start. ' -180 minutes'));
						if($emp->shift_end < date('H:i:s', strtotime('11:00:00'))) // Night Shift
						{
							if(!empty($nextDayShiftData))
							{
								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$nextDayShiftData->shift_start. ' -180 minutes'));
							}
							else
							{
								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$shiftData->shift_start. ' -180 minutes'));
							}
							$dorn=2;
						}
						else
						{
							if(!empty($nextDayShiftData))
							{
								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$nextDayShiftData->shift_start. ' -180 minutes'));
							}
							else
							{
								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$shiftData->shift_start. ' -180 minutes'));
							}
						}
						//print_r($nextDayShiftData);
						//print_r($emp->shift_end.'@@@'.$shiftStart.' *** '.$shiftEnd);
						$empPucnhes = array_keys(array_combine(array_keys($punches), array_column($punches, 'Empcode')),$ecode);
						if(!empty($empPucnhes))
						{
							foreach($empPucnhes as $punch)
							{
								$todayPunch = $punches[$punch];	
								$pnchDate = date('d-m-Y H:i:s', strtotime(strtr($todayPunch->PunchDate, '/', '-')));
								if((strtotime($pnchDate) >= strtotime($shiftStart)) AND (strtotime($pnchDate) <= strtotime($shiftEnd)))
								{
									$punchDates[]=date('Y-m-d H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
									$punchTimes[]=date('H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
									$mflag[]='S';
								}
							}
						}
						// Get Manual Punches
						$mpData = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($currentDate)),$emp->id);
						$mpDataND = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($nextDate)),$emp->id);
						$mpData = array_merge($mpData,$mpDataND);
						if(!empty($mpData))
						{
							foreach($mpData as $mpRow):
								$time = explode(" ",$mpRow->punch_in);
								
								$pDate = date('Y-m-d H:i:s',strtotime(strtr($mpRow->punch_in, '/', '-')));
								if((strtotime($pDate) >= strtotime($shiftStart)) AND (strtotime($pDate) <= strtotime($shiftEnd)))
								{
									$punchDates[]=$pDate;
									$punchTimes[] = date("H:i:s",strtotime($time[1]));
									$mflag[]=$pDate;
								}
							endforeach;
						}
						//print_r($punchDates);
						$attend_status = false;
						
						if(!empty($punchDates))
						{
							// Get Extra Hours
							$exHrsTime = '';$exTime = 0;
							$exHrsData = $this->attendance->getExtraHours(date("Y-m-d",strtotime($currentDate)),$emp->id);
							
							if(!empty($exHrsData))
							{
								$exTime = (intVal($exHrsData->ex_hours) * 3600) + (intVal($exHrsData->ex_mins) * 60);
								$exh = (!empty($exHrsData->ex_hours)) ? $exHrsData->ex_hours : '00';
								$exm = (!empty($exHrsData->ex_mins)) ? $exHrsData->ex_mins : '00';
								
								if($exh < 0 OR $exm < 0):
									$exHrsTime = '<td style="text-align:center;color:#aa0000;font-size:12px;font-weight:bold;">'.abs($exh).' H : '.abs($exm).' M</td>';
								else:
									$exHrsTime = '<td style="text-align:center;font-size:12px;">'.abs($exh).' H : '.abs($exm).' M</td>';
								endif;
							}
							// Shift Time Calculation
							$totalShiftTime = (!empty($emp->total_shift_time)) ? explode(':',$emp->total_shift_time) : explode(':','08:45');
							$stime = 0;$stime = 0;
							if(intVal($totalShiftTime[0]) > 0 OR intVal($totalShiftTime[1]) > 0):
								$stime = (intVal($totalShiftTime[0]) * 3600) + (intVal($totalShiftTime[1]) * 60);
							endif;
							
							//$all_puch = sortDates($punchTimes);
							$all_puch = sortDates($punchDates);
							$twh = 0;$TWHRS=0;$t=1;$wph = Array();$idx=0;
							foreach($all_puch as $punch)
							{
								$wph[$idx][]=strtotime($punch);
								if($t%2 == 0){$TWHRS += floatVal($wph[$idx][1]) - floatVal($wph[$idx][0]);$idx++;}
								$t++;
							}
							
							// Count Lunch Time (If Lunch time > 45 Mins then Lunch Time Time is Actual Otherwise 45 Mins Fixed
							$countedLT =0;$ltime = 0;
							if(count($wph) >= 2){$countedLT = floatVal($wph[1][0]) - floatVal($wph[0][1]);}
							$halfDayTime = intval($stime/2) + 60;
							$lunchTime = 0;
							if((intVal($TWHRS) > $halfDayTime) OR (count($wph) >= 2)){$lunchTime = 2700;}
							//if(count($wph) > 2){$lunchTime = 2700;}
							$ltime = $lunchTime;
							if($countedLT < 2700){$lunchTime -= $countedLT;}else{$lunchTime = 0;$ltime = $countedLT;}
							$TWHRS = $TWHRS - $lunchTime;
							
							$ot = $ot1 = (intVal($TWHRS) > intVal($stime)) ? (intVal($TWHRS) - intVal($stime)) : 0;
							
							$wh = intVal($TWHRS) - intVal($ot);
							
							$TWHRS += $exTime;
							//$TWHRS = floor($TWHRS / 3600) .'H '. floor($TWHRS / 60 % 60). 'M';
							$TWHRS = round(($TWHRS / 3600),2);
								
							$tbody .='<th>'.$TWHRS.'</th>';
						}
						else
						{
							$tbody .='<th></th>';
						}
					}
					else{$tbody .='<th></th>';}
				}
				$tbody .='</tr>';$theadDates .='</tr>';$i++;
			}
		}
		
		$response = '<table class="table-bordered itemList" border="1" repeat_header="1">';
		$response .= '<thead>
							<tr><th class="text-center" colspan="'.($last_day + 2).'">Employee Attandance Sheet for '.date("F-Y",strtotime($year.'-'.$month.'-01')).'</th></tr>
							
							'.$theadDates.'
					</thead>';
		$response .= '<tbody>'.$tbody.'</tbody></table>';
		
		//echo $response;exit;
		if($file_type == 'excel')
		{
			$xls_filename = 'monthlyAttendance.xls';
			
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment; filename='.$xls_filename);
			header('Pragma: no-cache');
			header('Expires: 0');
			
			echo $response;
		}
		else
		{
			$htmlHeader = '<div class="table-wrapper">
								<table class="table txInvHead">
									<tr class="txRow">
										<td class="fs-17 text-left" style="letter-spacing: 1px;font-weight:bold;">'.$companyData->company_name.'</td>
										<td class="text-right pad-right-10"><b>Report Month : '.date("F-Y",strtotime($year.'-'.$month.'-01')).'</td>
									</tr>
								</table>
							</div>';
			$htmlFooter = '<table style="border-top:1px solid #000;padding:3px;">
							<tr><td style="width:50%;text-align:left;">Printed On {DATE j-m-Y}</td><td style="width:50%;text-align:right;">Page No :- {PAGENO}</td></tr>
							</table>';
			
			$mpdf = new \Mpdf\Mpdf();
			$pdfFileName='monthlyAttendance.pdf';
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
		
		//$this->printJson(["status"=>1,"thead"=>$thead,"tbody"=>$tbody]);
    }
    
    /* Employee Recruitment Form | CREATED AT : 22/09/2022 | CREATED BY : MEGHAVI*/
    function empRecruitmentForm(){
		$this->data['companyData'] = $this->employee->getCompanyInfo();
	
		$logo=base_url('assets/images/logo.png');
		// $this->data['letter_head']=base_url('assets/images/letterhead_top.png');
		
		$pdfData = $this->load->view('reports/hr_report/emp_recruitment_form',$this->data,true);
		
		$htmlHeader = '<table class="table">
							<tr>
								<td style="width:25%;"><img src="'.$logo.'" style="height:50px;"></td>
								<td class="org_title text-center" style="font-size:1.5rem;width:50%">Employee Information Form</td>
								<td style="width:25%;" class="text-right"><span style="font-size:0.8rem;">R-HR-01 (00/01.10.17)</td>
							</tr>
						</table>';
		// print_r($htmlHeader.$pdfData);exit;
		$mpdf = new \Mpdf\Mpdf();
		$pdfFileName='DC-REG-.pdf';
		// $stylesheet = file_get_contents(base_url('assets/extra-libs/datatables.net-bs4/css/dataTables.bootstrap4.css'));
		// $stylesheet = file_get_contents(base_url('assets/css/style.css?v='.time()));
		$stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->SetWatermarkImage($logo,0.03,array(120,60));
		$mpdf->showWatermarkImage = true;
		$mpdf->SetProtection(array('print'));
		
		$mpdf->SetHTMLHeader($htmlHeader);
		$mpdf->AddPage('P','','','','',5,5,25,5,5,5,'','','','','','','','','','A4-P');
		$mpdf->WriteHTML($pdfData);
		$mpdf->Output($pdfFileName,'I');
	}

    public function printHourlyReport($dates,$biomatric_id="ALL",$file_type = 'excel'){
		
		set_time_limit(0);
		if(!empty($dates))
		{
			$duration = explode('~',$dates);
			$biomatric_id = ($biomatric_id == "ALL") ? '' : $biomatric_id ;
			$empData = $this->attendance->getEmployeeList($biomatric_id);
// 			$empData = $this->attendance->getEmployeeList('20400');
			$companyData = $this->attendance->getCompanyInfo();
			$current_month  = date("m");
			$month  = date("m",strtotime($duration[0]));
			$year  = date("Y",strtotime($duration[0]));
			$FromDate = date("Y-m-d",strtotime($duration[0]));
			$ToDate  = date("Y-m-d",strtotime($duration[1]));
			
			$fdate = date("Y-m-d 00:00:01",strtotime($duration[0]));
			$tdate  = date("Y-m-d 23:59:59",strtotime($duration[1]));
			
			$first_day = date("d",strtotime($duration[0]));
			$last_day = date("d",strtotime($duration[1]));
			
			$empTable = '';
			$thead ='';$tbody ='';$i=1;$printData='';$empCount = 1;
			$begin = new DateTime($FromDate);
			$end = new DateTime($ToDate);
			$end = $end->modify( '+1 day' ); 
			
			$interval = new DateInterval('P1D');
			$daterange = new DatePeriod($begin, $interval ,$end);
			
			foreach($daterange as $date)
			{
				$currentDate =  date("Y-m-d",strtotime($date->format("Y-m-d")));
				$nextDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
				$currentDay = date('D', strtotime($currentDate));
				$punchData = New StdClass();
				$todayPunchData = $this->biometric->getPunchData($currentDate,$nextDate);
				
				$punches = Array();
				foreach($todayPunchData as $pnc)
				{
					$jarr = json_decode($pnc->punch_data);
					$punches = array_merge($punches,$jarr);
				}
				
				if(!empty($empData))
				{
					foreach($empData as $emp)
					{
    						$ecode = sprintf("%04d", $emp->biomatric_id);$punchDates = Array();$punchTimes = Array();$mflag=Array();
    						$attend_status = false;$wo = 0;$wh = 0;$wi = 0;$late = '';$allPunches ='';$dorn=1;$present_status = 'P';
    						$workHrs = '';$otData = '';$status = '';$exOtHrs = '';$totalWorkHrs = '';$lateStatus = '';$exHrs = '';
					    if(!empty($emp->emp_joining_date) AND (strtotime($emp->emp_joining_date) <= strtotime($currentDate)))
					    {
    						
    						// Get ShiftData
    						$shiftData = $this->shiftModel->getAttendanceLog($currentDate,$emp->id);
    						$emp->shift_start = $shiftData->shift_start;
    						$emp->shift_end = $shiftData->shift_end;
    						$emp->shift_id = $shiftData->shift_id;
    						$emp->shift_name = $shiftData->shift_name;
    						$emp->total_shift_time = $shiftData->total_shift_time;
    						$nextDayShiftData = $this->shiftModel->getAttendanceLog($nextDate,$emp->id);
    						$shiftStart = date('d-m-Y H:i:s', strtotime($currentDate.' '.$emp->shift_start. ' -180 minutes'));
    						if($emp->shift_end < date('H:i:s', strtotime('11:00:00'))) // Night Shift
    						{
    							if(!empty($nextDayShiftData))
    							{
    								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$nextDayShiftData->shift_start. ' -180 minutes'));
    							}
    							else
    							{
    								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$shiftData->shift_start. ' -180 minutes'));
    							}
    							//$shiftStart = date('d-m-Y H:i:s', strtotime($currentDate.' 13:15:00'));
    							//$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' 12:30:00'));
    							$dorn=2;
    						}
    						else
    						{
    							if(!empty($nextDayShiftData))
    							{
    								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$nextDayShiftData->shift_start. ' -180 minutes'));
    							}
    							else
    							{
    								$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' '.$shiftData->shift_start. ' -180 minutes'));
    							}
    							//$shiftStart = date('d-m-Y H:i:s', strtotime($currentDate.' 04:30:00'));
    							//$shiftEnd = date('d-m-Y H:i:s', strtotime($nextDate.' 04:00:00'));
    						}
    						//print_r($nextDayShiftData);
    						//print_r($emp->shift_end.'@@@'.$shiftStart.' *** '.$shiftEnd);
    						$empPucnhes = array_keys(array_combine(array_keys($punches), array_column($punches, 'Empcode')),$ecode);
    						if(!empty($empPucnhes))
    						{
    							foreach($empPucnhes as $punch)
    							{
    								$todayPunch = $punches[$punch];	
    								$pnchDate = date('d-m-Y H:i:s', strtotime(strtr($todayPunch->PunchDate, '/', '-')));
    								if((strtotime($pnchDate) >= strtotime($shiftStart)) AND (strtotime($pnchDate) <= strtotime($shiftEnd)))
    								//if(($pnchDate >= $shiftStart))
    								{
    									$punchDates[]=date('Y-m-d H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
    									$punchTimes[]=date('H:i:s',strtotime(strtr($todayPunch->PunchDate, '/', '-')));
    									$mflag[]='S';
    								}
    							}
    						}
    						// Get Manual Punches
    						$mpData = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($currentDate)),$emp->id);
    						$mpDataND = $this->attendance->getManualPunchData(date("Y-m-d",strtotime($nextDate)),$emp->id);
    						$mpData = array_merge($mpData,$mpDataND);
    						if(!empty($mpData))
    						{
    							foreach($mpData as $mpRow):
    								$time = explode(" ",$mpRow->punch_in);
    								
    								$pDate = date('Y-m-d H:i:s',strtotime(strtr($mpRow->punch_in, '/', '-')));
                					if((strtotime($pDate) >= strtotime($shiftStart)) AND (strtotime($pDate) <= strtotime($shiftEnd)))
                					{
                					    $punchDates[]=$pDate;
        								$punchTimes[] = date("H:i:s",strtotime($time[1]));
        								$mflag[]=$pDate;
                					}
    							endforeach;
    						}
    						
    						if(!empty($punchDates))
    						{
    							$attend_status = true;if($currentDay == 'Wed'){$present_status = 'WP';}
    							$punch_in = date('d-m-Y H:i:s', strtotime(min($punchDates)));
    							$punch_out = date('d-m-Y H:i:s', strtotime(max($punchDates)));
    							if( count($punchDates) == 1 ):
    								$punch_out = $shiftEnd;
    							endif;
    
    							$late_in =  date('d-m-Y H:i:s', strtotime($shiftStart.' + '.intVal($emp->late_in).' minute'));
    							$late = ($punch_in > $late_in) ? 'Y' : '';
    							
    							$time1 = new DateTime(date('H:i:s',strtotime($punch_in)));
    							$time2 = new DateTime(date('H:i:s',strtotime($punch_out)));
    							$interval = $time1->diff($time2);
    							$total_hours = $interval->format('%H:%I:%S');
    							$total_is = $interval->format('%I:%S');
    							
    							$punch_in = date('H:i', strtotime($punch_in));
    							$punch_out = date('H:i', strtotime($punch_out));
    							$total_hours = date('H:i', strtotime($total_hours));
    							
    							// Total Hours Calculation
    							$totalHrs = explode(':',$total_hours);
    							// Get Extra Hours
    							$exHrsTime = '';$exTime = 0;
    							$exHrsData = $this->attendance->getExtraHours(date("Y-m-d",strtotime($currentDate)),$emp->id);
    							
    							if(!empty($exHrsData))
    							{
    								$exTime = (intVal($exHrsData->ex_hours) * 3600) + (intVal($exHrsData->ex_mins) * 60);
    								$exh = (!empty($exHrsData->ex_hours)) ? $exHrsData->ex_hours : '00';
    								$exm = (!empty($exHrsData->ex_mins)) ? $exHrsData->ex_mins : '00';
    								
    								if($exh < 0 OR $exm < 0):
    								    $exHrsTime = '<td style="text-align:center;color:#aa0000;font-size:12px;font-weight:bold;">'.abs($exh).' H : '.abs($exm).' M</td>';
    								else:
    								    $exHrsTime = '<td style="text-align:center;font-size:12px;">'.abs($exh).' H : '.abs($exm).' M</td>';
    								endif;
    							}
    							// Shift Time Calculation
    							$totalShiftTime = (!empty($emp->total_shift_time)) ? explode(':',$emp->total_shift_time) : explode(':','08:45');
    							$stime = 0;$stime = 0;//print_r($totalShiftTime);print_r(' = '.$emp->emp_code.'@'.$currentDate.'<br>');
    							if(intVal($totalShiftTime[0]) > 0 OR intVal($totalShiftTime[1]) > 0):
    								$stime = (intVal($totalShiftTime[0]) * 3600) + (intVal($totalShiftTime[1]) * 60);
    							endif;
    							
    							//$all_puch = sortDates($punchTimes);
    							$all_puch = sortDates($punchDates);
    							$twh = 0;$TWHRS=0;$t=1;$wph = Array();$idx=0;
    							foreach($all_puch as $punch)
    							{
    								$wph[$idx][]=strtotime($punch);
    								if($t%2 == 0){$TWHRS += floatVal($wph[$idx][1]) - floatVal($wph[$idx][0]);$idx++;}
    								$t++;
    							}
    							
    							// Count Lunch Time (If Lunch time > 45 Mins then Lunch Time Time is Actual Otherwise 45 Mins Fixed
    							$countedLT =0;$ltime = 0;
    							if(count($wph) >= 2){$countedLT = floatVal($wph[1][0]) - floatVal($wph[0][1]);}
    							$halfDayTime = intval($stime/2) + 60;
    							$lunchTime = 0;
    							if((intVal($TWHRS) > $halfDayTime) OR (count($wph) >= 2)){$lunchTime = 2700;}
    							//if(count($wph) > 2){$lunchTime = 2700;}
    							$ltime = $lunchTime;
    							if($countedLT < 2700){$lunchTime -= $countedLT;}else{$lunchTime = 0;$ltime = $countedLT;}
    							$TWHRS = $TWHRS - $lunchTime;
    							
    							$ot = $ot1 = (intVal($TWHRS) > intVal($stime)) ? (intVal($TWHRS) - intVal($stime)) : 0;
    							
    							$wh = intVal($TWHRS) - intVal($ot);
    							
    							$work_hours = floor($wh / 3600) .':'. floor($wh / 60 % 60);
    							$ot = floor($ot / 3600) .':'. floor($ot / 60 % 60);
    							$TWHRS += $exTime;$totalWorkTime = $TWHRS;
    							$TWHRS = floor($TWHRS / 3600) .':'. floor($TWHRS / 60 % 60);
    							$lunchTime = floor($ltime / 3600) .':'. floor($ltime / 60 % 60);
    							
    							// $allPunchDates = implode(', ',sortDates($punchDates));
    							// if($dorn==2){$allPunches = implode(', ',sortDates($allPunchDates,'DESC'));}
    							$allPunches = '';
    							if(!empty($punchDates))
    							{
    								$allPunchDates = ($dorn==2) ? sortDates($punchDates) : sortDates($punchDates);
    								$ap = Array();
    								foreach($allPunchDates as $p)
    								{
    									$spanTag = '';
    									if(in_array($p,$mflag)){$ap[] = '<b>'.date("d H:i:s",strtotime($p)).'</b>';}
    									else{$ap[] = date("d H:i:s",strtotime($p));}
    								}
    								$allPunches = implode(', ',$ap);
    							}
    							
    							
    							// Check For Missed Punch
    							if(count($punchTimes) % 2 != 0)
    							{
    								$status = '<td style="text-align:center;color:#233288;font-size:12px;">M</td>';
    							}
    							else
    							{
    								if($totalWorkTime <= 0){$status = '<td style="text-align:center;color:#aa0000;font-size:12px;">A</td>';}
    								else{$status = '<td style="text-align:center;color:#00aa00;font-size:12px;">'.$present_status.'</td>';}
    							}
    							$workHrs = '<td style="text-align:center;font-size:12px;">'.$work_hours.'</td>';
    							$ltTd = '<td style="text-align:center;font-size:12px;">'.$lunchTime.'</td>';
    							$exHrs = $exHrsTime;//'<td style="text-align:center;font-size:12px;">'.$exHrsTime.'</td>';
    							$otData = '<td style="text-align:center;font-size:12px;">'.$ot.'</td>';
    							$totalWorkHrs = '<td style="text-align:center;font-size:12px;">'.$TWHRS.'</td>';
    							$lateStatus = '<td style="text-align:center;font-size:12px;">'.$late.'</td>';	
    						} 
    						else
    						{
    							$attend_status = false;if($currentDay == 'Wed'){$present_status = 'W';}else{$present_status='A';}
    							$status = '<td style="text-align:center;color:#aa0000;font-size:12px;">'.$present_status.'</td>';
    							$workHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$ltTd = '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$exHrs= '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$otData = '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$totalWorkHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
    							$lateStatus = '<td style="text-align:center;font-size:12px;">'.$late.'</td>';
    						} 
					    }	
						else
						{
							$status = '<td style="text-align:center;;font-size:12px;">NA</td>';
							$workHrs = '<td style="text-align:center;font-size:12px;">NA</td>';
							$otData = '<td style="text-align:center;font-size:12px;">NA</td>';
							$totalWorkHrs = '<td style="text-align:center;font-size:12px;">NA</td>';
						}
					
						$empTable .='<tr>';
							$empTable .='<td style="text-align:center;font-size:12px;">'.$ecode.'</td>';
							$empTable .='<td style="text-align:left;font-size:12px;">'.$emp->emp_name.'</td>';
							$empTable .='<td style="font-size:12px;">'.date("d-m-Y",strtotime($currentDate)).'</td>';
							$empTable .= $status.$workHrs.$otData.$totalWorkHrs;
						$empTable .='</tr>';
					}
				}
			}
			
			
			$response = '<table class="table-bordered itemList" border="1" repeat_header="1">';
				$response .= '<thead>
						<tr style="background:#eee;">
							<th>Emp Code</th>
							<th>Employee</th>
							<th>Punch Date</th>
							<th>Status</th>
							<th>WH</th>
							<th>OT</th>
							<th>TWH</th>
						</tr></thead><tbody>'.$empTable.'</tbody></table>';
 			//echo $response;exit;
			if($file_type == 'excel')
			{
				$xls_filename = 'monthlyAttendance.xls';
				
				header('Content-Type: application/vnd.ms-excel');
				header('Content-Disposition: attachment; filename='.$xls_filename);
				header('Pragma: no-cache');
				header('Expires: 0');
				
				echo $response;
			}
			else
			{
				$htmlHeader = '<div class="table-wrapper">
									<table class="table txInvHead">
										<tr class="txRow">
											<td class="fs-17 text-left" style="letter-spacing: 1px;font-weight:bold;">'.$companyData->company_name.'</td>
											<td class="text-right pad-right-10"><b>Report Month : '.date("F-Y",strtotime($year.'-'.$month.'-01')).'</td>
										</tr>
									</table>
								</div>';
				$htmlFooter = '<table style="border-top:1px solid #000;padding:3px;">
								<tr><td style="width:50%;text-align:left;">Printed On {DATE j-m-Y}</td><td style="width:50%;text-align:right;">Page No :- {PAGENO}</td></tr>
								</table>';
				
				$mpdf = new \Mpdf\Mpdf();
				$pdfFileName='monthlyAttendance.pdf';
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


	/* Created By :- Sweta @01-09-2023 */
	public function canteen(){
        $this->data['pageHeader'] = 'CANTEEN REPORT';
		$this->data['cmList'] = $this->employee->getCompanyList($cm_id='');	
        $this->data['empList'] = $this->employee->getEmployeeList();
        $this->load->view($this->canteenReport,$this->data);
    }

	/* Created By :- Sweta @01-09-2023 */
	public function getEmpCanteenData(){
        $data = $this->input->post();
        $errorMessage = array();
		if($data['to_date'] < $data['from_date'])
			$errorMessage['toDate'] = "Invalid date.";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $canteenData = $this->employee->getEmpCanteenData($data);
			
            $tbody="";$i=1;	 $thead="";$totalGuest=0; $totalDish=0;
            foreach($canteenData as $row):
				$noOfGuest = ($row->no_person - 1);
                    $tbody .= '<tr>
							<td>'.$i++.'</td>
							<td>'.date("d-m-Y H:i:s",strtotime($row->created_at)).'</td>
							<td>'.$row->emp_name.'</td>
							<td>'.(($row->trans_type == 1) ? "Lunch" : "Dinner").'</td>
							<td>'.$noOfGuest.'</td>
							<td>'.$row->no_person.'</td>
						</tr>';
						$totalGuest += $noOfGuest;
						$totalDish += $row->no_person;
            endforeach;
		
            $this->printJson(['status'=>1, 'tbody'=>$tbody ,'total_guest'=>$totalGuest,'total_dish'=>$totalDish]);
        endif;
    }

	
	public function getEmpListByDept(){
		$data = $this->input->post();
		$empList = $this->employee->getEmployeeList(['emp_dept_id'=>$data['emp_dept_id'],'emp_unit_id'=>$data['emp_unit_id']]);
		$options = '<option value="">Select All</option>';
		if(!empty($empList)){
			foreach($empList as $row){
				$options .= '<option value="'.$row->id.'">['.$row->emp_code.'] '.$row->emp_name.'</option>';
			}
		}
		$this->printJson(['status'=>1,'options'=>$options]);
	}

	
	public function printCanteenIcard($emp_id=''){ 
		$data = $this->input->post();
		if(empty($data)){$data['emp_id'] =  $emp_id;$data['emp_unit_id'] = '';$data['emp_dept_id'] = '';}
		$pdata = '';
		$logo = base_url('assets/images/logo.png');
        $empData = $this->employee->getEmployeeList(['id'=>$data['emp_id'],'emp_dept_id'=>$data['emp_dept_id'],'emp_unit_id'=>$data['emp_unit_id']]);
		$empArray = array();
		foreach($empData as $row){
            $qrIMG=base_url('assets/uploads/cati_card_qr/'.$row->id.'.png');
            if(!file_exists($qrIMG)){
                $qrText = $row->emp_code.'~'.$row->emp_name;
                $file_name = $row->id;
                $qrIMG = base_url().$this->getQRCode($qrText,'assets/uploads/cati_card_qr/',$file_name);
            }

			$profile_pic = 'male_user.png';
			if(!empty($row->emp_profile)){$profile_pic = $row->emp_profile;}
			else
			{
				if(!empty($row->emp_gender) and $row->emp_gender=="Female"):
					$profile_pic = 'female_user.png';
				else:
					$profile_pic = 'male_user.png';
				endif;
			}
			$emp_id = $data['emp_id'];
			$emp_unit = (!empty($row->unit_id)) ? $row->unit_id : 0;
			$qrText = encodeURL(['emp_code'=>$row->emp_code,'type'=>'login_qr']);
			$qrCode = $this->getQRCode($qrText,'assets/uploads/emp_qr/',$emp_id.time());
			$ic_bg = ((empty($emp_unit)) ? base_url('assets/images/icard1.png') : base_url('assets/images/icard'.$emp_unit.'.png'));
			$lh_padding = (!empty($emp_unit) AND $emp_unit == 2) ? 'padding-top:112px;' : 'padding-top:96px;';
			
			$empQr = ((!empty($qrCode)) ? '<img src="'.base_url($qrCode).'" alt="" style="width:70px;height:70px;">' : '');
			//if(!empty($qrCode)){echo '<img src="'.$empQr.'" alt="" class="empQr" >';}
			
			if($emp_unit == 2){
			    $prof_pic = '<div style="position:fixed;top:50px;left:59px;">
			            <img src="'.base_url('assets/uploads/emp_profile/'.$profile_pic).'" style="width:60px;height:60px;border: 2px solid #adadad;">
			           </div>';
			}
			else{
			    $prof_pic = '<div style="position:fixed;top:30px;left:59px;">
			            <img src="'.base_url('assets/uploads/emp_profile/'.$profile_pic).'" style="width:60px;height:60px;border: 2px solid #adadad;">
			           </div>';
			}
			
			$pdata = '<div class="icard" style="'.$lh_padding.'margin:0 auto;text-align:center;">
								<!--<div class="signature-img" style="width:100%;text-align:center;margin:0 auto;">
									<img src="'.base_url('assets/uploads/emp_profile/'.$profile_pic).'" style="width:60px;height:60px;border: 2px solid #adadad;">
								</div>-->
								<div class="name-title" style="font-size:10px;margin:5px; 0;">'.$row->emp_name.'</div>
								<div class="emp-code" style="font-size:10px;text-align:center;color:#FFF !important;font-weight:bold;">CODE : '.$row->emp_code.'</div>
								<table style="width:100%;text-align:left;">
									<tr>
										<th style="width:10%;"><i class="fas fa-phone" style="transform: rotate(90deg);"></i></th>
										<td style="width:50%;font-size:10px;">'.$row->emp_contact.'</td>
										<th style="width:10%;"><i class="fas fa-user"></i></th>
										<td style="width:50%;font-size:10px;">'.$row->department_name.'</td>
									</tr>
									<tr>
										<th><i class=" fas fa-map-marker-alt"></i></th>
										<td colspan="3" style="font-size:9px;">'.$row->emp_address.'</td>
									</tr>
								</table>
								<div class="width80"><div class="divider light"></div></div>
								'.$empQr.'
							
						</div>
						<div class="ic-auth" style="font-size:8px;text-align:center"><i>This is computer generated I-CARD</i></div>';
			$empArray[]=
			['empDetail'=>$prof_pic.'<div>'.$pdata.'</div>'];
		}
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [52.15,85.17]]);
		$stylesheet = file_get_contents(base_url('assets/css/icard.css'));
        $mpdf->WriteHTML($stylesheet, 1);
		
        $mpdf->SetDisplayMode('fullpage');
		$mpdf->SetDefaultBodyCSS('background', "url('".$ic_bg."')");
		$mpdf->SetDefaultBodyCSS('background-image-resize', 4);
        $mpdf->setTitle('I-CARD');
		foreach($empArray as $emp)
		{
			$mpdf->AddPage('P','','','','',2,2,2,2,2,2);
			$mpdf->WriteHTML($emp['empDetail']);
		}
		
        $mpdf->Output('qr_prints.pdf','I');	
    }

	public function printCanteenIcard1(){ 
		$data = $this->input->post();
		$pdata = '';
		$logo = base_url('assets/images/logo.png');
        $empData = $this->employee->getEmployeeList(['id'=>$data['emp_id'],'emp_dept_id'=>$data['emp_dept_id'],'cm_id'=>$data['emp_cm_id']]);
		$empArray = array();
		foreach($empData as $row){
            $qrIMG=base_url('assets/uploads/cati_card_qr/'.$row->id.'.png');
            if(!file_exists($qrIMG)){
                $qrText = $row->id;
                $file_name = $row->id;
                $qrIMG = base_url().$this->getQRCode($qrText,'assets/uploads/cati_card_qr/',$file_name);
            }

			$profile_pic = 'male_user.png';
			if(!empty($row->emp_profile)){$profile_pic = $row->emp_profile;}
			else
			{
				if(!empty($row->emp_gender) and $row->emp_gender=="Female"):
					$profile_pic = 'female_user.png';
				else:
					$profile_pic = 'male_user.png';
				endif;
			}
			$header = '<table class="table top-table-border" >
						<tr>
							<th><img src="'.base_url('assets/images/vertical_logo.png').'" style="height:30px;" ></th>
							<td class="text-center" style="font-size:0.65rem">
									<b>'.(!empty($row->company_name)?$row->company_name:'Satani').'<br></b>
									<small style="font-size:0.4rem">'.$row->company_address.'<br>Tel. : '.$row->company_phone.'</small>
							</td>
						</tr>
					</table>';
			$pdata = '<div >
						<table class="table top-table-border">
							<tr class="bg-light">
								<td colspan="2" style="font-size:0.65rem;border-right:0px;padding-right:0px !important;"> 
									<p><b >'.$row->emp_name.'</b></p>
									<span class="designation"><i>'.$row->title.' - '.$row->name.'</i> | EMP CODE : '.$row->emp_code.'</i></span>
								</td>
								<td style="border-left:0px;font-size:0.65rem;padding:0px;text-align:right"><img src="'.base_url('assets/uploads/emp_profile/'.$profile_pic).'" alt="" style="height:40px;width:40px"></td>
							</tr>
							<tr>
								<td style="font-size:0.65rem;width:30%"><b>DOB : </b>'.$row->emp_birthdate.'</td>
								<td style="font-size:0.65rem;width:30%"><b>DOJ : </b>'.$row->emp_joining_date.'</td>
								<td style="font-size:0.65rem;width:40%"><b>Phone : </b>'.$row->emp_contact.'</td>
							</tr>
							<tr>
								<td style="font-size:0.65rem;" colspan="3"><b>Address : </b>'.$row->emp_address.' </td>
							</tr>
						</table>';
			$qrCode='<table class="table top-table">
							<tr >
								<th style="text-align:center;padding:0px" colspan="2"><img src="'.$qrIMG.'" style="height:25mm;"></th>
							</tr>
							<tr  >
								<td class="text-left" style="font-size:0.65rem" colspan="2"><p>This card must be with a person all the time during field work/duty hours. Loss off the card must be reported immediately to HR Dept. If this card found, please return to the company address.</p></td>
							</tr>
							<tr>
								<th  style="font-size:0.65rem;text-align:center;vertical-aligh:bottom;height:50px">Authorized Signatury</th>
								<th  style="font-size:0.65rem;text-align:center;vertical-aligh:bottom;">Employees Signature</th>
							</tr>
					</table>';	
			$empArray[]=
			[
				'empDetail'=>'<div>'.$header.$pdata.'</div>',
				'qrCode'=>$qrCode
			];
		}
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [85.6, 54]]);
		$stylesheet = file_get_contents(base_url('assets/css/pdf_style.css'));
        $mpdf->WriteHTML($stylesheet, 1);

        $mpdf->SetDisplayMode('fullpage');
        $mpdf->setTitle('CanteenICard');
		foreach($empArray as $emp)
		{
			$mpdf->AddPage('P','','','','',2,2,2,2,2,2);
			$mpdf->WriteHTML($emp['empDetail']);

			$mpdf->AddPage('P','','','','',2,2,2,2,2,2);
			$mpdf->WriteHTML($emp['qrCode']);
		}
		
        $mpdf->Output('qr_prints.pdf','I');	
    }

	public function empSalaryDetails(){
		$this->data['pageHeader'] = 'EMPLOYEE SALARY DETAILS';		
		$this->data['cmList'] = $this->employee->getCompanyList($cm_id='');	
        $this->load->view($this->salary_report,$this->data);
	}
	
	public function getEmpSalaryDetails(){
		$data = $this->input->post();
		$empData = $this->employee->getEmployeeList(['emp_unit_id'=>$data['emp_unit_id']]);	

		$month = date('Y-m-d',strtotime($data['month']));		
		$attendanceData = $this->biometric->getSalaryHours(['from_date'=>date('Y-m-01',strtotime($month)),'to_date'=>date('Y-m-t',strtotime($month)),'datewise'=>1]);	
			
		$i=1; $tbody="";
		if(!empty($empData)):
			foreach($empData as $row):
				$twh = (!empty($attendanceData[$row->id]['twh']))?$attendanceData[$row->id]['twh']:0;
				$twh = sprintf('%0.2f',round(($twh/3600),2));
				$tbody .= '<tr>
					<td>'.$i++.'</td>
					<td>'.$row->emp_name.'</td>
					<td>'.$row->emp_code.'</td>
					<td>'.$row->department_name.'</td>
					<td>'.$row->sal_amount.'</td>
					<td>'.$row->hrs_day.'</td>
					<td>'.sprintf('%0.2f',round(($row->sal_amount * $row->hrs_day),2)).'</td>
					<td>'.$twh.'</td>
					<td>'.sprintf('%0.2f',round(($row->sal_amount * $twh),2)).'</td>
					<td>'.formatDate($row->increment_date).'</td>
					<td>'.$row->sal_amount.'</td>
					<td></td>
				</tr>';
			endforeach;
		endif;
		
		$this->printJson(['status'=>1, 'tbody'=>$tbody]);
	}

	/* Leave Report */
	public function leaveReport(){
        $this->data['pageHeader'] = 'LEAVE REPORT';
        $this->data['empList'] = $this->employee->getEmployeeList(['is_active'=>[0,1]]);
        $this->data['deptList'] = $this->department->getDepartmentList();
        $this->data['companyList'] = $this->employee->getCompanyList();
        $this->load->view($this->leave_report,$this->data);
    }

	public function getEmpList(){
        $data = $this->input->post();
		$options="";
        
        if(!empty($data['dept_id'])):
            $empData = $this->employee->getEmployeeList(['emp_dept_id'=>$data['dept_id'],'is_active'=>[0,1],'emp_unit_id'=>$data['unit_id']]);
            $options = '<option value="">Select All Employee</option>';
            foreach($empData as $row):  
				$options .= '<option value="'.$row->id.'">['.$row->emp_code.'] '.$row->emp_name.'</option>';
            endforeach;
        endif;
        $this->printJson(['options'=>$options]);
    }

	public function getLeaveReportData(){
        $data = $this->input->post();
        $errorMessage = array();
		if($data['to_date'] < $data['from_date'])
			$errorMessage['toDate'] = "Invalid date.";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $leaveData = $this->employee->getLeaveReportData($data);			
            $tbody="";$i=1;
            foreach($leaveData as $row):

				$start_date = date('d-m-Y',strtotime($row->start_date));
				$end_date = date('d-m-Y',strtotime($row->end_date));
				$total_days = $row->total_days.' Days';
				
				if(!empty($row->type_leave) && $row->type_leave == 'SL'){
					$start_date = date('d-m-Y H:i',strtotime($row->start_date));
					$end_date = date('d-m-Y H:i',strtotime($row->end_date));
					$hours = intval($row->total_days/60);
					$mins = intval($row->total_days%60);
					$total_days = sprintf('%02d',$hours).':'.sprintf('%02d',$mins).' Hours';
				}

				$tbody .= '<tr>
						<td>'.$i++.'</td>
						<td class="text-left">'.$row->company_name.'</td>
						<td class="text-left">'.$row->name.'</td>
						<td class="text-left">['.$row->emp_code.'] '.$row->emp_name.'</td>
						<td class="text-left">'.$row->leave_type.'</td>
						<td>'.$start_date.'</td>
						<td>'.$end_date.'</td>
						<td>'.$total_days.'</td>
						<td class="text-left">'.$row->leave_reason.'</td>
					</tr>';

            endforeach;
		
            $this->printJson(['status'=>1, 'tbody'=>$tbody ]);
        endif;
    }

	/* Facility Report */
	public function facilityReport(){
        $this->data['pageHeader'] = 'FACILITY REPORT';
        $this->data['empList'] = $this->employee->getEmployeeList();
        $this->data['deptList'] = $this->department->getDepartmentList();
        $this->data['companyList'] = $this->employee->getCompanyList();
        $this->load->view($this->facility_report,$this->data);
    }

	public function getFacilityReportData(){
        $data = $this->input->post();
        $errorMessage = array();
		if($data['to_date'] < $data['from_date'])
			$errorMessage['toDate'] = "Invalid date.";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $leaveData = $this->employee->getFacilityReportData($data);			
            $tbody="";$i=1;
            foreach($leaveData as $row):

				$tbody .= '<tr>
						<td>'.$i++.'</td>
						<td class="text-left">'.$row->company_name.'</td>
						<td class="text-left">'.$row->name.'</td>
						<td class="text-left">['.$row->emp_code.'] '.$row->emp_name.'</td>
						<td class="text-left">'.$row->ficility_type.'</td>
						<td class="text-left">'.formatDate($row->entry_date).'</td>
						<td class="text-left">'.$row->amount.'</td>
						<td class="text-left">'.$row->reason.'</td>
					</tr>';

            endforeach;
		
            $this->printJson(['status'=>1, 'tbody'=>$tbody ]);
        endif;
    }
}
?>