<?php
class Employee extends MY_Controller
{
	public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "Employee List";
		$this->data['headData']->controller = "app/employee";
	}

	public function monthlyAttendanceSummary($month = 1){
		$this->data['headData']->appMenu = "monthlyAttendanceSummary";
		$this->data['month'] = $month;

		$empData = $this->employee->getEmployee(['id'=>$this->loginId]);
		$duration = [];
		if($month == 1){
			$duration[0] = date("Y-m-01");
			$duration[1] = date("Y-m-t");
		}else{
			$duration[0] = date("Y-m-01",strtotime("-1 month"));
			$duration[1] = date("Y-m-t",strtotime("-1 month"));
		}
		
		set_time_limit(0);
		if(!empty($duration)){
			$biomatric_id = $empData->biomatric_id;
			$empAttendanceLog = $this->biometric->getDateWiseSummaryV2(['from_date'=>$duration[0],'to_date'=>$duration[1],'emp_code'=>$biomatric_id]);
			$companyData = $this->attendance->getCompanyInfo();
			$current_month  = date("m");
			$FromDate = date("Y-m-d",strtotime($duration[0]));
			$ToDate  = date("Y-m-d",strtotime($duration[1]));			
			

			$empTable='';$totalWH='';$aLogData=Array();$monthWorkHrs=0;$ltCount=0;$eoCount=0;$ltPenalty=0;$eoPenalty=0;$aLogData['wh'] = 0;$aLogData['lhrs'] = 0;$aLogData['xhrs'] = 0;$aLogData['ot'] = 0;$aLogData['aot'] = 0;$aLogData['twh'] = 0;
			foreach($empAttendanceLog as $row)
			{
				$allPunches = '';$allPunches1 = '';

                $empPunches = explode(',',$row->punch_date);
                $sortType = ($row->shift_type == 1) ? 'ASC' : 'ASC';
                $empPunches = sortDates($empPunches,$sortType);					
                
                $currentDay = date('D', strtotime($row->attendance_date));
                $xmins = $row->ex_mins;
                $startLunch = date('d-m-Y H:i:s', strtotime($row->attendance_date.' '.$row->lunch_start));
                $endLunch = date('d-m-Y H:i:s', strtotime($row->attendance_date.' '.$row->lunch_end));
                
                $shift_start = date('d-m-Y H:i:s', strtotime($row->attendance_date.' '.$row->shift_start));
                $nextDate = ($row->shift_type == 2) ? date('Y-m-d', strtotime($row->attendance_date . ' +1 day')) :$row->attendance_date;
                $shift_end = date('d-m-Y H:i:s', strtotime($nextDate.' '.$row->shift_end));
                $punch_in = date('d-m-Y H:i:s', strtotime(min($empPunches)));
                $punch_out = date('d-m-Y H:i:s', strtotime(max($empPunches)));					
                
                $ap = Array();
                foreach($empPunches as $p){$ap[] = date("H:i:s",strtotime($p));}
                $allPunches1 = implode(', ',$ap);
                
                $t=1;$wph = Array();$idx=0;$stay_time=0;$twh = 0;$wh=0;$ot=0;$xtime='';$status='';$ps='';
                $late_in_s = 0;$early_in_s = 0;$early_out_s = 0;$late_out_s = 0;$lastp_index = count($empPunches)-1;	
                
                // Count Early In, Late In, Early Out, Late Out Time in Seconds
                if(strtotime($punch_in) < strtotime($shift_start)){$early_in_s = strtotime($shift_start) - strtotime($punch_in);}
                if(strtotime($punch_in) > strtotime($shift_start)){$late_in_s = strtotime($punch_in) - strtotime($shift_start);}
                if(strtotime($punch_out) < strtotime($shift_end)){$early_out_s = strtotime($shift_end) - strtotime($punch_out);}
                if(strtotime($punch_out) > strtotime($shift_end)){$late_out_s = strtotime($punch_out) - strtotime($shift_end);}
					
                // Apply Late In Policy
                if($late_in_s > ($row->ltp_minute*60)){$ltCount++;}
                if($ltCount > $row->ltp_days){$twh -= ($row->ltp_phrs*3600);$ltPenalty += $row->ltp_phrs;$ltCount--;}
                
                // Apply Early Out Policy
                if($early_out_s > ($row->eop_minute*60)){$eoCount++;}
                if($eoCount > $row->eop_days){$twh -= ($row->eop_phrs*3600);$eoPenalty += $row->eop_phrs;$eoCount--;}
					
                // Trim Early In Punch
                if($early_in_s <= ($row->ru_in_time*60)){$empPunches[0] = date('Y-m-d H:i:s', strtotime($empPunches[0]) + $early_in_s );}
                // Trim Late In Punch
                if($late_in_s <= ($row->rd_in_time*60)){$empPunches[0] = date('Y-m-d H:i:s', strtotime($empPunches[0]) - $late_in_s);}
                
                // Trim Early Out Punch
                if($early_out_s <= ($row->ru_out_time*60)){$empPunches[$lastp_index] = date('Y-m-d H:i:s', strtotime($empPunches[$lastp_index]) + $early_out_s);}
                // Trim Late Out Punch
                if($late_out_s <= ($row->rd_out_time*60)){$empPunches[$lastp_index] = date('Y-m-d H:i:s', strtotime($empPunches[$lastp_index]) - $late_out_s);}
                $punch_in = date('d-m-Y H:i:s', strtotime(min($empPunches)));
                $punch_out = date('d-m-Y H:i:s', strtotime(max($empPunches)));
                $punch_inH = intVal(date('H', strtotime($punch_in)));
                
                $ap = Array();
                foreach($empPunches as $p){$ap[] = date("H:i:s",strtotime($p));}
				$allPunches = implode(', ',$ap);
					
                // Count Overtime as per shift
                if(strtotime($punch_out) > strtotime($shift_end)){$ot = strtotime($punch_out) - strtotime($shift_end);}
                $row->ot_mins = (empty($row->adjust_to)) ? $row->ot_mins : 0;
                
                // Count Total Time [1-2,3-4,5-6.....]
                $wph1 = Array();$idx1=0;$wstay_time=0;$t1=1;$punch_diff=0;$x1=0;
                foreach($empPunches as $punch)
                {
                    if(strtotime($punch) > strtotime($shift_end))
                    {
                        if($x1 == 0)
                        {
                            
                            $wph[$idx][]=strtotime($shift_end);
                            if($t%2 == 0)
                            {
                                $stay_time += floatVal($wph[$idx][1]) - floatVal($wph[$idx][0]);
                                $idx++;
                            }
                            $t++;
                            
                            $punch_diff = floatVal(strtotime($punch) - strtotime($shift_end));
                            $wstay_time += $punch_diff;
                        }
                        else
                        {
                            $wph1[$idx1][]=strtotime($punch);
                            if($t1%2 == 0)
                            {
                                $wstay_time += floatVal($wph1[$idx1][1]) - floatVal($wph1[$idx1][0]);
                                $idx1++;
                            }
                            $t1++;
                        }
                        $x1++;
                    }
                    else
                    {
                        $wph[$idx][]=strtotime($punch);
                        if($t%2 == 0)
                        {
                            $stay_time += floatVal($wph[$idx][1]) - floatVal($wph[$idx][0]);
                            $idx++;
                        }
                        $t++;
                    }
                    
                }

                $twh = $stay_time;
                
                // Reduce Lunch Time
                $fixLunch = 3600; // 1 Hour
                $row->lunch_time = 0;
                //if($punch_inH < 16 AND (!in_array($row->emp_unit_id,[5])))
                if($punch_inH < 16)
                {
                    if((strtotime($punch_in) < strtotime($startLunch)) AND (strtotime($punch_out) > strtotime($endLunch)))
                    {
                        $countedLT = 0;$xlt = 0;
                        if(count($empPunches) > 2)
                        {
                            $countedLT = strtotime($empPunches[2]) - strtotime($empPunches[1]);
                            $twh += $countedLT;
                            if($countedLT > $fixLunch)
                            {
                                $fixLunch = $countedLT;
                            }
                        }
                        $row->lunch_time = $fixLunch;
                    }
                }
                //$row->lunch_time = $fixLunch;
                $twh -= $row->lunch_time;
					
                // Get Extra Hours
                $xtime = '<td style="text-align:center;font-size:12px;">--:--</td>';    							
                if(!empty($row->ex_mins))
                {
                    $textStyle= ($row->ex_mins < 0) ? "color:#aa0000;font-weight:bold;" : "";
                    $xtime = '<td style="text-align:center;font-size:12px;'.$textStyle.'">'.formatSeconds(abs($row->ex_mins),'H:i').'</td>';
                    
                }
                $ot = $wstay_time;
                $wh = $twh;
                $twh += $row->ot_mins;
                $twh += $row->ex_mins;
                $twh += $row->adj_mins;
                $twh1 = $twh;
                
                // Set Present/Absent/ Status					
                $ps='A';$status = '<td style="text-align:center;color:#aa0000;font-size:12px;">'.$ps.'</td>';
                if($currentDay == 'Wed'){$ps = 'WO';}
                if(count($empPunches) > 0)
                {
                    if($twh > 0){$ps='P';}else{$ps='A';}
                    if(count($empPunches) % 2 != 0){$ps='M';}
                }
                
                if($ps == 'M'){$status = '<a href="javascript:void(0)" class="btn btn-sm btn-primary fs-11 btn-block" style="padding:2px 4px;margin-top:5px;">'.$ps.'</a>';}
                if($ps == 'WO'){$status = '<a href="javascript:void(0)" class="btn btn-sm btn-dark fs-11  btn-block" style="padding:2px 4px;margin-top:5px;">'.$ps.'</a>';}
                if($ps == 'P'){$status = '<a href="javascript:void(0)" class="btn btn-sm btn-success fs-11  btn-block" style="padding:2px 4px;margin-top:5px;">'.$ps.'</a>';}
                					
                $workHrs = '<div class="col">'.formatSeconds($wh,'H:i').'</div>';
                $ltTd = '<div class="col">'.formatSeconds($row->lunch_time,'H:i').'</div>';
                $exHrs = $xtime;
                $aotData = '<div class="col">'.formatSeconds($row->ot_mins,'H:i').'</div>';
                $totalWorkHrs = formatSeconds($twh,'H:i');
                $monthWorkHrs += $twh;
                
                if($ps == 'A')
                {
                    $status = '<a href="javascript:void(0)" class="btn btn-sm btn-danger fs-11 btn-block" style="padding:2px 4px; margin-top:5px;">'.$ps.'</a>';
                    $workHrs = '<td style="text-align:center;font-size:12px;">--:--</td>';
                    $ltTd = '<td style="text-align:center;font-size:12px;">--:--</td>';
                    $exHrs= '<div class="col"> --:--</div>';
                    $aotData = '<div class="col"> --:--</div>';
                    $totalWorkHrs = '--:--';
                }			
				
                $empTable .= '<li class="grid_item listItem item transition position-static" data-category="transition">
                    <a href="javascript:void(0)">
                        <div class="media-content">
                            <div>
                                <h6 class="name">'.date('d-m-Y', strtotime($row->attendance_date)).'</h6>
                                <p class="my-1"> <label class="text-bold">Shift : </label> '.$row->shift_name.'</p>
                                <p class="my-1"> <label class="text-bold">Punches : </label> '.$allPunches.' </p>
                                <div class="btn-group process-tags">
                                    <span class="badge bg-light-peach btn flex-fill" style="padding:5px;margin-bottom:7px;">WH : '.$workHrs.'</span>
                                    <span class="badge bg-light-teal btn flex-fill" style="padding:5px;margin-bottom:7px;">Lunch : '.$ltTd.'</span>
                                    <span class="badge bg-light-cream btn flex-fill" style="padding:5px;margin-bottom:7px;">OT : '.$aotData.'</span>
                                </div>   
                            </div>
                           
                        </div>
                        <div class="left-content">
                            <a class="lead-action" href="#" role="button">
                                <span class="badge badge-danger">'.$totalWorkHrs.'</span>
                                
                            </a>
                            '.$status.'
                        </div>  
                    </a>                
                </li>';
			}
		
            $totalWH .= '<li class="grid_item listItem item transition position-static " data-category="transition">
                <a href="javascript:void(0)">
                    <div class="media-content">
                        <div>
                            <h6 class="name">Total Working Hours</h6>
                        </div>
                    </div>
                    <div class="left-content w-auto">
                        <a href="javascript:void(0)" class="btn btn-danger fs-14 btn-block" style="padding:8px 8px;">'.formatSeconds($monthWorkHrs,'H:i').'</a>
                    </div>                    
                </a>
            </li>';
		}
        
		$this->data['attendData'] = $totalWH.$empTable;
        $this->load->view('app/monthly_summary',$this->data);
	}
}
?>