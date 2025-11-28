<?php
class SalesQuotationModel extends MasterModel{
    private $sqMaster = "sq_master";
    private $sqTrans = "sq_trans";
    private $transExpense = "trans_expense";
    private $transDetails = "trans_details";

    public function getDTRows($data){
        $data['tableName'] = $this->sqTrans;
        $data['select'] = "sq_trans.id,item_master.item_name,sq_trans.qty,sq_trans.price,sq_master.id as trans_main_id,sq_master.trans_number,DATE_FORMAT(sq_master.trans_date,'%d-%m-%Y') as trans_date,sq_master.party_id,party_master.sales_executive,party_master.party_name,sq_master.sales_type,sq_trans.trans_status,sq_master.is_approve,employee_master.emp_name as confirm_by_name,sq_master.approve_date,sq_master.quote_rev_no,sq_trans.confirm_by,sq_trans.cod_date";

        $data['leftJoin']['sq_master'] = "sq_master.id = sq_trans.trans_main_id";
        $data['leftJoin']['item_master'] = "item_master.id = sq_trans.item_id";
        $data['leftJoin']['employee_master'] = "employee_master.id = sq_trans.confirm_by";
        $data['leftJoin']['party_master'] = "party_master.id = sq_master.party_id";

        $data['where']['sq_trans.entry_type'] = $data['entry_type'];

        if($data['status'] == 1) { 
			$data['where']['sq_trans.confirm_by != '] = 0; 
			$data['where']['sq_trans.cod_date >= '] = $this->startYearDate;
			$data['where']['sq_trans.cod_date <= '] = $this->endYearDate;
		} 
        else { $data['where']['sq_trans.confirm_by'] = 0; }

        $data['order_by']['sq_master.trans_date'] = "DESC";
        $data['order_by']['sq_master.id'] = "DESC";

        $data['group_by'][] = "sq_trans.trans_main_id";

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "sq_master.quote_rev_no";
        $data['searchCol'][] = "sq_master.trans_number";
        $data['searchCol'][] = "DATE_FORMAT(sq_master.trans_date,'%d-%m-%Y')";
        $data['searchCol'][] = "party_master.party_name";
        $data['searchCol'][] = "item_master.item_name";
        $data['searchCol'][] = "sq_trans.qty";
        $data['searchCol'][] = "sq_trans.price";
        $data['searchCol'][] = "employee_master.emp_name";
        $data['searchCol'][] = "DATE_FORMAT(sq_master.approve_date,'%d-%m-%Y')";

        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
        if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        
        return $this->pagingRows($data);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            if(!empty($data['is_rev'])):
                $this->quotationRevision($data['id']);
                $data['quote_rev_no'] = $data['quote_rev_no'] + 1;
            endif;

            if(!empty($data['id'])):
                $dataRow = $this->getSalesQuotation(['id'=>$data['id'],'itemList'=>1]);
                foreach($dataRow->itemList as $row):
                    if(!empty($row->ref_id)):
                        $setData = array();
                        $setData['tableName'] = $this->sqTrans;
                        $setData['where']['id'] = $row->ref_id;
                        $setData['update']['trans_status'] = 0;
                        $this->setValue($setData);
                    endif;

                    $this->trash($this->sqTrans,['id'=>$row->id]);
                endforeach;

                $this->trash($this->transExpense,['trans_main_id'=>$data['id']]);
                $this->remove($this->transDetails,['main_ref_id'=>$data['id'],'table_name'=>$this->sqMaster,'description'=>"SQ TERMS"]);
            endif;
            
            $itemData = $data['itemData'];

            $transExp = getExpArrayMap(((!empty($data['expenseData']))?$data['expenseData']:array()));
			$expAmount = $transExp['exp_amount'];
            $termsData = (!empty($data['conditions']))?$data['conditions']:array();

            unset($transExp['exp_amount'],$data['itemData'],$data['expenseData'],$data['conditions'],$data['is_rev']);		

            $result = $this->store($this->sqMaster,$data,'Sales Quotation');

            $expenseData = array();
            if($expAmount <> 0):				
				$expenseData = $transExp;
                $expenseData['id'] = "";
				$expenseData['trans_main_id'] = $result['id'];
                $this->store($this->transExpense,$expenseData);
			endif;

            //Terms & Conditions
            if(!empty($termsData)):
                $termsData = [
                    'id' =>"",
                    'table_name' => $this->sqMaster,
                    'description' => "SQ TERMS",
                    'main_ref_id' => $result['id'],
                    't_col_1' => $termsData
                ];
                $this->store($this->transDetails,$termsData);
            endif;

            $i=1;
            foreach($itemData as $row):
                $row['entry_type'] = $data['entry_type'];
                $row['trans_main_id'] = $result['id'];
                $row['is_delete'] = 0;
                $this->store($this->sqTrans,$row);

                if(!empty($row['ref_id'])):
                    $setData = array();
                    $setData['tableName'] = 'se_trans';
                    $setData['where']['id'] = $row['ref_id'];
                    $setData['update']['trans_status'] = "1";
                    $this->setValue($setData);
                endif;
            endforeach;
            
            if(!empty($data['ref_id'])):
                $refIds = explode(",",$data['ref_id']);
                foreach($refIds as $main_id):
                    $setData = array();
                    $setData['tableName'] = 'se_master';
                    $setData['where']['id'] = $main_id;
                    $setData['update']['trans_status'] = "(SELECT IF( COUNT(id) = SUM(IF(trans_status <> 0, 1, 0)) ,1 , 0 ) as trans_status FROM sq_trans WHERE trans_main_id = ".$main_id." AND is_delete = 0)";
                    $this->setValue($setData);
                endforeach;
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

    public function quotationRevision($id){
        try{
            $this->db->trans_begin();

            $quotationData = $this->getSalesQuotation(['id'=>$id,'itemList'=>1]);

            $itemData = $quotationData->itemList;

            $termsData = '';
            if(!empty($quotationData->termsConditions)):
                $termsData = $quotationData->termsConditions;
            endif;

            $transExp = (!empty($quotationData->expenseData))?$quotationData->expenseData:array();

            unset($quotationData->itemList,$quotationData->termsConditions,$quotationData->expenseData,$quotationData->created_name);

            $quotationData = (array) $quotationData;
            $quotationData["ref_id"] = $quotationData["id"];
            $quotationData["id"] = "";
            $quotationData["from_entry_type"] = $quotationData['entry_type'];
            $quotationData["entry_type"] = "";
            
            $result = $this->store($this->sqMaster,$quotationData,'Sales Quotation');

            $expenseData = array();
            if(!empty($transExp)):
				$expenseData = (array) $transExp;
                $expenseData['id'] = "";
				$expenseData['trans_main_id'] = $result['id'];
                $this->store($this->transExpense,$expenseData);
			endif;

            //Terms & Conditions
            if(!empty($termsData)):
                $termsData = [
                    'id' =>"",
                    'table_name' => $this->sqMaster,
                    'description' => "SQ TERMS",
                    'main_ref_id' => $result['id'],
                    't_col_1' => $termsData->condition
                ];
                $this->store($this->transDetails,$termsData);
            endif;

            $i=1;
            foreach($itemData as $row):
                $row = (array) $row;
                $row['from_entry_type'] = $row['entry_type'];
                $row['entry_type'] = "";
                $row['ref_id'] = $row['id'];
                $row['id'] = "";
                $row['trans_main_id'] = $result['id'];
                $row['is_delete'] = 0;	

                unset($row['item_name'],$row['unit_name'],$row['hsn_code']);
                $this->store($this->sqTrans,$row);
            endforeach;
            

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
        $queryData['tableName'] = $this->sqMaster;
        $queryData['where']['trans_number'] = $data['trans_number'];
        $queryData['where']['entry_type'] = $data['entry_type'];

        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];

        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

    public function getQuotationRevisionList($data){
        $queryData = array();
        $queryData['tableName'] = $this->sqMaster;
        $queryData['select'] = "id,trans_number,quote_rev_no,doc_date";
        $queryData['where']['sq_master.trans_number'] = $data['trans_number'];
        $result = $this->rows($queryData);
        return $result;
    }

    public function getSalesQuotation($data){
        $queryData = array();
        $queryData['tableName'] = $this->sqMaster;
        $queryData['select'] = "sq_master.*,employee_master.emp_name as created_name,party_master.party_name";
        $queryData['leftJoin']['employee_master'] = "employee_master.id = sq_master.created_by";
        $queryData['leftJoin']['party_master'] = "party_master.id = sq_master.party_id";
        $queryData['where']['sq_master.id'] = $data['id'];
        $result = $this->row($queryData);

        if($data['itemList'] == 1):
            $result->itemList = $this->getSalesQuotationItems($data);
        endif;
        if(!empty($data['discStatus'])):
			$disc = $this->getSQWitoutDisc($data['id']);
			$result->discStatus = (!empty($disc)) ? count($disc) : 0;
		endif;

        $queryData = array();
        $queryData['tableName'] = $this->transExpense;
        $queryData['where']['trans_main_id'] = $data['id'];
        $result->expenseData = $this->row($queryData);

        $queryData = array();
        $queryData['tableName'] = $this->transDetails;
        $queryData['select'] = "t_col_1 as condition";
        $queryData['where']['main_ref_id'] = $data['id'];
        $queryData['where']['table_name'] = $this->sqMaster;
        $queryData['where']['description'] = "SQ TERMS";
        $result->termsConditions = $this->row($queryData);

        return $result;
    }

    public function getSalesQuotationItems($data){
        $queryData = array();
        $queryData['tableName'] = $this->sqTrans;
        $queryData['select'] = "sq_trans.*,item_master.item_name,unit_master.unit_name,item_master.hsn_code";
        $queryData['leftJoin']['item_master'] = "item_master.id = sq_trans.item_id";
        $queryData['leftJoin']['unit_master'] = "unit_master.id = item_master.unit_id";
        $queryData['where']['sq_trans.trans_main_id'] = $data['id'];
        $result = $this->rows($queryData);
        return $result;
    }

    public function getSalesQuotationItem($data){
        $queryData = array();
        $queryData['tableName'] = $this->sqTrans;
        $queryData['where']['id'] = $data['id'];
        $result = $this->row($queryData);
        return $result;
    }    

    public function delete($id){
        try{
            $this->db->trans_begin();

            $dataRow = $this->getSalesQuotation(['id'=>$id,'itemList'=>1]);
            foreach($dataRow->itemList as $row):
                if(!empty($row->ref_id)):
                    $setData = array();
                    $setData['tableName'] = 'se_trans';
                    $setData['where']['id'] = $row->ref_id;
                    $setData['update']['trans_status'] = 0;
                    $this->setValue($setData);
                endif;

                $this->trash($this->sqTrans,['id'=>$row->id]);
            endforeach;

            if(!empty($dataRow->ref_id)):
                $oldRefIds = explode(",",$dataRow->ref_id);
                foreach($oldRefIds as $main_id):
                    $setData = array();
                    $setData['tableName'] = 'se_master';
                    $setData['where']['id'] = $main_id;
                    $setData['update']['trans_status'] = "(SELECT IF( COUNT(id) = SUM(IF(trans_status <> 0, 1, 0)) ,1 , 0 ) as trans_status FROM sq_trans WHERE trans_main_id = ".$main_id." AND is_delete = 0)";
                    $this->setValue($setData);
                endforeach;
            endif;

            $this->trash($this->transExpense,['trans_main_id'=>$id]);
            $this->remove($this->transDetails,['main_ref_id'=>$id,'table_name'=>$this->sqMaster,'description'=>"SQ TERMS"]);
            $result = $this->trash($this->sqMaster,['id'=>$id],'Sales Quotation');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

    public function getPendingQuotationItems($data){
        $queryData = array();
        $queryData['tableName'] = $this->sqTrans;
        $queryData['select'] = "sq_trans.*,sq_master.entry_type as main_entry_type,sq_master.trans_number,sq_master.trans_date,sq_master.doc_no";
        $queryData['leftJoin']['sq_master'] = "sq_trans.trans_main_id = sq_master.id";
        $queryData['where']['sq_master.party_id'] = $data['party_id'];
        $queryData['where']['sq_trans.entry_type'] = $this->data['entryData']->id;
        $queryData['where']['sq_trans.confirm_status'] = 2;
        $queryData['where']['sq_trans.trans_status'] = 0;
        return $this->rows($queryData);
    }

    public function getQuotationItems($data){
		$qdata['tableName'] = $this->sqTrans;
        $qdata['select'] = 'sq_trans.*,item_master.item_name,item_master.unit_id,unit_master.unit_name';
        $qdata['leftJoin']['item_master'] = "item_master.id = sq_trans.item_id";
        $qdata['leftJoin']['unit_master'] = "unit_master.id = item_master.unit_id";
        $qdata['where']['sq_trans.entry_type'] = $data['entry_type'];
        $qdata['where']['sq_trans.trans_main_id'] = $data['quote_id'];
		$quoteItems = $this->rows($qdata);

		if(!empty($quoteItems)):
			$i=1; $html="";
			foreach($quoteItems as $row):
				if(empty($row->confirm_by)):
					$checked = "";
					$disabled = "disabled";
					$html .= '<tr>
							<td class="text-center">
								<input type="checkbox" id="md_checkbox'.$i.'" class="filled-in chk-col-success itemCheckCQ" data-rowid="'.$i.'" check="'.$checked.'" '.$checked.' />
								<label for="md_checkbox'.$i.'" style="margin-bottom:0px;"></label>
							</td>
							<td>
								'.$row->item_name.'
								<input type="hidden" name="item_id[]" id="item_id'.$i.'" class="form-control" value="'.$row->item_id.'" '.$disabled.' />
								<input type="hidden" name="trans_id[]" id="trans_id'.$i.'" class="form-control" value="'.$row->id.'" '.$disabled.' />
								<input type="hidden" name="inq_trans_id[]" id="inq_trans_id'.$i.'" class="form-control" value="'.$row->ref_id.'" '.$disabled.' />
								<input type="hidden" name="unit_id[]" id="unit_id'.$i.'" class="form-control" value="'.$row->unit_id.'" '.$disabled.' />
							</td>
							<td>
								'.floatVal($row->qty).' <small>('.$row->unit_name.')</small>
								<input type="hidden" name="qty[]" id="qty'.$i.'" class="form-control floatOnly" data-id="'.$i.'" value="'.$row->qty.'" min="0" '.$disabled.' />
								<div class="error qty'.$row->id.'"></div>
							</td>
							<td>
								'.$row->price.'
								<input type="hidden" name="price[]" id="price'.$i.'" class="form-control floatOnly" data-id="'.$i.'" value="'.$row->price.'" min="0" '.$disabled.' />
								<div class="error price'.$row->id.'"></div>
							</td>
							<td>
								<input type="number" name="confirm_price[]" id="confirm_price'.$i.'" class="form-control floatOnly" data-id="'.$i.'" value="0" min="0" '.$disabled.' />
								<div class="error confirm_price'.$row->id.'"></div>
							</td>					
						</tr>';				
				else:
										
					$checked = "checked";
					$disabled = "disabled";

					$html .= '<tr>
							<td class="text-center">
								<input type="checkbox" id="md_checkbox'.$i.'" class="filled-in chk-col-success itemCheck" data-rowid="'.$i.'" check="'.$checked.'" '.$checked.' '.$disabled.' />
								<label for="md_checkbox'.$i.'" style="margin-bottom:0px;"></label>
							</td>
							<td>
								'.$row->item_name.'
								<input type="hidden" name="item_id[]" id="item_id'.$i.'" class="form-control" value="'.$row->item_id.'" '.$disabled.' />
								<input type="hidden" name="trans_id[]" id="trans_id'.$i.'" class="form-control" value="'.$row->id.'" '.$disabled.' />
								<input type="hidden" name="inq_trans_id[]" id="inq_trans_id'.$i.'" class="form-control" value="'.$row->ref_id.'" '.$disabled.' />
								<input type="hidden" name="unit_id[]" id="unit_id'.$i.'" class="form-control" value="'.$row->unit_id.'" '.$disabled.' />
							</td>
							<td>
								'.$row->qty.'
								<input type="hidden" name="qty[]" id="qty'.$i.'" class="form-control floatOnly" data-id="'.$i.'" value="'.$row->qty.'" min="0" '.$disabled.' />
								<div class="error qty'.$row->id.'"></div>
							</td>
							<td>
								'.$row->price.'
								<input type="hidden" name="price[]" id="price'.$i.'" class="form-control floatOnly" data-id="'.$i.'" value="'.$row->price.'" min="0" '.$disabled.' />
								<div class="error price'.$row->id.'"></div>
							</td>
							<td>
								<input type="number" name="confirm_price[]" id="confirm_price'.$i.'" class="form-control floatOnly" data-id="'.$i.'" value="'.$row->org_price.'" min="0" '.$disabled.' />
								<div class="error confirm_price'.$row->id.'"></div>
							</td>
						</tr>';

				endif;$i++;
			endforeach;
		else:
			$html = '<tr><td colspan="5" class="text-center">No data available in table</td></tr>';
		endif;
		return $html;
	}

    public function saveConfirmQuotation($data){
		try{
            $this->db->trans_begin();
			
            //save sales Quotation items
            foreach($data['trans_id'] as $key=>$value):
                $itmId = $data['item_id'][$key];
                $itmData = Array();
                /*** Store New Confirmed Item to Item Master ***/
                if(empty($data['item_id'][$key])):
                    $itmData['id']="";
                    $itmData['price']=$data['confirm_price'][$key];
                    $itmData['unit_id']=$data['unit_id'][$key];
                    $itmData['party_id']=$data['customer_id'];
                    $itmData['item_type'] = 1;
                    $newItem = $this->store($this->itemMaster,$itmData);
                    if(!empty($newItem['insert_id'])){$itmId = $newItem['insert_id'];}
                endif;
                /*** Update Quotation Transaction with Confirmed Parameters  ***/
                $transData = [
                                'id' =>  $value,
                                'item_id' =>  $itmId,
                                'org_price' => $data['confirm_price'][$key],
                                'cod_date' => formatDate($data['confirm_date'],'Y-m-d'),
                                'confirm_by' => $data['confirm_by']
                            ];
                $this->store($this->sqTrans,$transData);
            endforeach;
            
            // $customerSave = $this->store('party_master',['id'=>$data['customer_id'],'party_category'=>1]);		
            
            $queryData = array();
            $queryData['where']['trans_main_id'] = $data['id'];
            $queryData['where']['confirm_by'] = 0;
            $queryData['where']['entry_type'] = $data['entry_type'];
            $queryData['resultType'][] = "numRows";
            $queryData['tableName'] = $this->sqTrans;
            $quotationItems = $this->rows($queryData);

            if(count($quotationItems) <= 0):
                $this->store($this->sqMaster,['id'=>$data['id'],'trans_status' => 1]);
            endif;

            $result = ['status'=>1,'message'=>'Sales Quotation Confirmed Successfully.'];

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
        return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }	
	}
	
	public function getSQWitoutDisc($trans_main_id){
		$data['tableName'] = $this->sqTrans;
		$data['where']['trans_main_id'] = $trans_main_id;
		$data['where']['disc_per > '] = 0;
		return $this->rows($data);
	}
}
?>