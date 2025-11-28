<?php
class IncrementPolicyModel extends MasterModel{
    private $increment_policy = "increment_policy";
    private $employee_master = "employee_master";
    
	public function getDTRows($data){
        $data['tableName'] = $this->increment_policy;
        $data['select'] = "increment_policy.*";
		$data['group_by'][]='policy_no';
        $data['where']['status'] = $data['status'];

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "effect_date";
        $data['searchCol'][] = "policy_no";
        $data['searchCol'][] = "policy_name";
        $data['searchCol'][] = "ref_month";

		$columns =array('','','effect_date','policy_no','policy_name','ref_month');
		if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        return $this->pagingRows($data);
    }

    public function nextPolicyNo(){
        $data['tableName'] = $this->increment_policy;
        $data['select'] = "MAX(policy_no) as policy_no";
		// $data['where']['increment_policy.effect_date >= '] = $this->startYearDate;
        // $data['where']['increment_policy.effect_date <= '] = $this->endYearDate;
		$policy_no = $this->specificRow($data)->policy_no;
		$nextPolicyNo = (!empty($policy_no))?($policy_no + 1):1;
		return $nextPolicyNo;
    }

    public function getIncrementPolicy($policy_no){
        $data['tableName'] = $this->increment_policy;
        $data['select'] = "increment_policy.*,department_master.name,employee_master.emp_code,employee_master.emp_name";
        $data['leftJoin']['employee_master'] = "increment_policy.emp_id = employee_master.id";
        $data['leftJoin']['department_master'] = "employee_master.emp_dept_id = department_master.id";
        $data['where']['policy_no'] = $policy_no;
        return $this->rows($data);
    }

    public function save($data) { 
        try {
            $this->db->trans_begin();
            foreach ($data['emp_id'] as $key => $value) :
                if(!empty($data['rate_hour'][$key])):
                    $incData = [
                        'id' => $data['id'][$key],
                        'policy_no' => $data['policy_no'],
                        'policy_name' => $data['policy_name'],
                        'effect_date' => $data['effect_date'],
                        'remark' => $data['remark'],
                        'ref_month' => $data['ref_month'],
                        'emp_id' => $value,
                        'old_rate_hour' => $data['sal_amount'][$key],
                        'old_monthly_hours' => $data['hrs_day'][$key],
                        'old_monthly_salary' => $data['month_salary'][$key],
                        'rate_hour' => $data['rate_hour'][$key],
                        'monthly_hours' => $data['monthly_hours'][$key],
                        'monthly_salary' => $data['monthly_salary'][$key],
                        'created_by' => $data['created_by']
                    ];
                $result = $this->store($this->increment_policy, $incData, 'Increment Policy');
            endif; 
            endforeach;
            if ($this->db->trans_status() !== FALSE) :
                $this->db->trans_commit();
                return $result;
            endif;
        } catch (\Exception $e) {
            $this->db->trans_rollback();
            return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
        }
    }

    public function delete($id){
        return $this->trash($this->increment_policy,['policy_no'=>$id],'Increment Policy');
    }

    public function policyApply($data){
        $incData = $this->getIncrementPolicy($data['id']); 
        foreach ($incData as $row) :
			$empData = [
				'monthly_salary' => $row->monthly_salary,
				'sal_amount' => $row->rate_hour,
				'hrs_day' => $row->monthly_hours,
				'increment_date' => $row->effect_date
			];
			$this->edit('employee_master',['id'=>$row->emp_id],$empData); 
        endforeach;
        $this->edit($this->increment_policy,['policy_no'=>$data['id']],['status'=>$data['status']]);

		return ['status' => 1, 'message' => 'Policy Apply Successfully.'];
	}
}
?>