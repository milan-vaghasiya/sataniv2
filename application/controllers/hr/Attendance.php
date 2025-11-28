<?php
class Attendance extends MY_Controller
{
    private $indexPage = "hr/attendance/index";
    private $monthlyAttendance = "hr/attendance/month_attendance";
    private $attendanceForm = "hr/attendance/form";
    private $approveOTPage = "hr/attendance/approve_ot";
    private $approveOTForm = "hr/attendance/approve_ot_form";
    
	public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "Attendance";
		$this->data['headData']->controller = "hr/attendance";
	}
	
	public function index(){
		$this->data['lastSyncedAt'] = "";
		$this->data['lastSyncedAt'] = $this->biometric->getDeviceData();
		$this->data['lastSyncedAt'] = (!empty($this->data['lastSyncedAt'][0]->last_sync_at)) ? date('j F Y, g:i a',strtotime($this->data['lastSyncedAt'][0]->last_sync_at)) : "";
        $this->data['companyList'] = $this->attendance->getCompanyList();
        $this->load->view($this->indexPage,$this->data);
    }
    
    /**** Check Device Status | Created By JP @10.07.2023 ***/
	public function getDeviceStatus()
	{
	    $this->data['deviceStatus'] = $this->biometric->getDeviceStatus();
		$this->load->view('hr/attendance/device_status',$this->data);
	}

	public function monthlyAttendance(){
        $this->load->view($this->monthlyAttendance,$this->data);
    }

    public function loadAttendanceSheet(){
        $data = $this->input->post();
		$this->printJson($this->attendance->loadAttendanceSheet($data['month']));
    }
    /*
    public function syncDeviceData(){
		//$this->printJson($this->biometric->syncDeviceData());
    }*/
    
    /**** NEW STRUCTURE (attendance_log) | Created By JP @09-12-2022 ***/
    public function syncDevicePunches(){
		$data = $this->input->post();
		$this->printJson($this->biometric->syncDevicePunches($data));
    }
    
    /**** NEW STRUCTURE (emp_punches) | Created By JP @09-12-2022 ***/
    /*public function syncDevicePunchesV2(){
		$this->printJson($this->biometric->syncDevicePunchesV2());
    }*/
    
    /** Approve OT | Created By Milan @11-03-2023 **/
    public function approveOT(){
        //$result = $this->biometric->getSalaryHours(['from_date'=>"2023-03-10",'to_date'=>"2023-03-10",'is_report'=>1,'emp_id'=>169]);
        //print_r($result);exit;	
		$this->data['cmList'] = $this->employee->getCompanyList($cm_id='');	
        $this->load->view($this->approveOTPage,$this->data);
    }
    
    public function getEmployeeAttendanceData(){
        $data = $this->input->post();
        
        $data['is_report'] = 1;
        $result = $this->biometric->getSalaryHours($data);
        
        $html = '';$i=1;
        foreach($result as $row):
            $row = (object) $row;
            
            $empPunches = explode(',',$row->punch_date);
			$sortType = ($row->shift_type == 1) ? 'ASC' : 'ASC';
			$empPunches = sortDates($empPunches,$sortType);	
            
            $ap = Array();
			foreach($empPunches as $p){$ap[] = date("H:i",strtotime($p));}
			$allPunches = implode(', ',$ap);

            $editParam = "{'postData':{'id' : ".$row->id.",'ot':".$row->ot."},'modal_id' : 'bs-right-md-modal', 'call_function':'editEmployeeOT', 'form_id' : 'approveOT', 'title' : 'Approve Employee OT','fnsave':'saveEmployeeOt'}";
            $editButton = '<a class="btn btn-success btn-edit permission-modify" href="javascript:void(0)" datatip="Approve OT" flow="down" onclick="modalAction('.$editParam.');"><i class="fas fa-check" ></i></a>';
            
			$action = getActionButton($editButton);

            $selectBox = '<input type="checkbox" name="ref_id[]" id="ref_id_'.$i.'" data-rowid="'.$i.'" class="filled-in chk-col-success BulkRequest" value="'.$row->id.'#'.formatSeconds($row->ot,'H:i').'"><label for="ref_id_'.$i.'"></label>';
            
            $ot_from=0;$ot_to=(24*3600);
            if($row->ot > 0):
                if($data['ot_filter'] == -1):
                    $ot_from = 1801;
                    $ot_to = (24*3600);
                elseif(!empty($data['ot_filter'])):
                    $ot_to = $data['ot_filter'];
                endif;
                if($row->ot >= $ot_from && $row->ot <= $ot_to):
                    $adjFrom = '';
                    if(!empty($row->adjust_from)){$af = explode('@',$row->adjust_from);$adjFrom = formatSeconds($row->adj_mins,'H:i').'<br><small>'.formatDate($af[1]).'</small>';}
                    $adjTo = '';$approvedBg = ($row->atot > 0) ? 'bg-light-success' : '';
                    if(!empty($row->adjust_to)){$at = explode('@',$row->adjust_to);$adjTo = formatSeconds($row->ot,'H:i').'<br><small>'.formatDate($at[1]).'</small>';}
                    
					if(empty($data['ot_type']))
					{
						$html .= '<tr class="'.$approvedBg.'">
							<td>'.$action.'</td>
							<td class="fs-12">'.$i.'</td>
							<td class="fs-12">'.$selectBox.'</td>
							<td class="fs-12">'.$row->emp_code.'</td>
							<td class="fs-12 text-left">'.$row->emp_name.'</td>
							<td class="fs-12">'.$row->shift_name.'</td>
							<td class="fs-12">'.formatDate($row->attendance_date).'</td>
							<td class="fs-12">'.formatSeconds($row->ot,'H:i').'</td>
							<td class="fs-12">'.formatSeconds($row->atot,'H:i').'</td>
							<td class="fs-12">'.$row->ot_approved_by_name.'</td>
							<td class="fs-12">'.$adjFrom.'</td>
							<td class="fs-12">'.$adjTo.'</td>
							<td class="fs-12">'.$allPunches.'</td>
						</tr>'; $i++;
					}elseif($data['ot_type'] == 1 AND $row->atot <= 0){
						$html .= '<tr>
							<td>'.$action.'</td>
							<td class="fs-12">'.$i.'</td>
							<td class="fs-12">'.$selectBox.'</td>
							<td class="fs-12">'.$row->emp_code.'</td>
							<td class="fs-12 text-left">'.$row->emp_name.'</td>
							<td class="fs-12">'.$row->shift_name.'</td>
							<td class="fs-12">'.formatDate($row->attendance_date).'</td>
							<td class="fs-12">'.formatSeconds($row->ot,'H:i').'</td>
							<td class="fs-12">'.formatSeconds($row->atot,'H:i').'</td>
							<td class="fs-12">'.$row->ot_approved_by_name.'</td>
							<td class="fs-12">'.$adjFrom.'</td>
							<td class="fs-12">'.$adjTo.'</td>
							<td class="fs-12">'.$allPunches.'</td>
						</tr>'; $i++;
					}elseif($data['ot_type'] == 2 AND $row->atot > 0){
						$html .= '<tr>
							<td>'.$action.'</td>
							<td class="fs-12">'.$i.'</td>
							<td class="fs-12">'.$selectBox.'</td>
							<td class="fs-12">'.$row->emp_code.'</td>
							<td class="fs-12 text-left">'.$row->emp_name.'</td>
							<td class="fs-12">'.$row->shift_name.'</td>
							<td class="fs-12">'.formatDate($row->attendance_date).'</td>
							<td class="fs-12">'.formatSeconds($row->ot,'H:i').'</td>
							<td class="fs-12">'.formatSeconds($row->atot,'H:i').'</td>
							<td class="fs-12">'.$row->ot_approved_by_name.'</td>
							<td class="fs-12">'.$adjFrom.'</td>
							<td class="fs-12">'.$adjTo.'</td>
							<td class="fs-12">'.$allPunches.'</td>
						</tr>'; $i++;
					}
                endif;
            endif;
            
        endforeach;
        
        $this->printJson(['status'=>1,'tbody'=>$html]);
    }
    
    public function editEmployeeOT(){
        $data = $this->input->post();
        $summaryData = $this->attendance->getAlogSummary($data['id']);
        $summaryData->actual_ot = $data['ot'];
        $this->data['dataRow'] = $summaryData;
        $this->data['deptList'] = $this->department->getDepartmentList(1);
        $this->load->view($this->approveOTForm,$this->data);
    }
    
    public function saveEmployeeOt(){
        $data = $this->input->post();
        $errorMessage = array();

        if(timeToSeconds($data['ot_mins']) > timeToSeconds($data['actual_ot']))
            $errorMessage['ot_mins'] = "Invalid OT.";
        if($data['ot_type'] == 2 AND empty($data['adjust_date']))
            $errorMessage['adjust_date'] = "Adjust Date is required";
            
        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            unset($data['actual_ot']);
            $this->printJson($this->attendance->saveEmployeeOt($data));
        endif;
    }
    
    public function approveOTBulkByDate($dates=""){
        
        if(!empty($dates))
        {
            $duration = explode('~',$dates);//print_r($duration);exit;
            if(count($duration) == 2)
            {
                $FromDate = date("Y-m-d",strtotime($duration[0]));
			    $ToDate  = date("Y-m-d",strtotime($duration[1]));
			    $begin = new DateTime($FromDate);
    			$end = new DateTime($ToDate); 
    			
    			$interval = new DateInterval('P1D');
    			$daterange = new DatePeriod($begin, $interval ,$end);
    			$i=0;
    			
    			foreach($daterange as $date)
    			{
    			    $currentDate =  date("Y-m-d",strtotime($date->format("Y-m-d")));
                    $result = $this->biometric->getSalaryHours(['from_date'=>$currentDate,'to_date'=>$currentDate,'is_report' => 1]);
                    //print_r($result);exit;
                    
                    if(!empty($result))
                    {
                        foreach($result as $row)
                        {
                            $row = (object) $row;
                            if($row->ot > 0)
                            {
                                $otApprovalData = Array();
                                
                                $otApprovalData['id'] = $row->id;
                                $otApprovalData['emp_id'] = $row->emp_id;
                                $otApprovalData['ot_type'] = 1; // Regular OT
                                $otApprovalData['ot_mins'] = formatSeconds($row->ot,'H:i');
                                $otApprovalData['attendance_date'] = $row->attendance_date;
                                $otApprovalData['remark'] = 'BULK APPROVED';
                                //print_r($otApprovalData);print_r('<hr>');
                                $this->attendance->saveEmployeeOt($otApprovalData);
                                $i++;
                            }
                        }
                    }
    			}
                echo 'Total OT Approved = '.$i;
            }
            else
            {
                echo '<h1 class="text-center">Invalid Date</h1>';
            }
        }
        else
        {
            echo '<h1 class="text-center">OT Date Not Found</h1>';
        }
    }
    
    public function saveBulkOT(){
        $data = $this->input->post();
		if(empty($data['remark']) || $data['remark']==''):
            $this->printJson(['status'=>0,'message'=>'Remark required...Please try again.']);
        else:
			$this->printJson($this->attendance->saveBulkOT($data));
		endif;
    }
}
?>