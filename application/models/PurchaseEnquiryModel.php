<?php
class PurchaseEnquiryModel extends MasterModel
{
	private $p_enq_master = "p_enq_master";
	private $p_enq_trans = "p_enq_trans";
	private $purchase_indent = "purchase_indent";

	public function getDTRows($data){
		$data['tableName'] = $this->p_enq_trans;
		$data['select'] = "p_enq_trans.*,p_enq_master.trans_number,p_enq_master.trans_date,p_enq_master.party_id,party_master.party_name";
		$data['leftJoin']['p_enq_master'] = "p_enq_master.id = p_enq_trans.trans_main_id";
		$data['leftJoin']['party_master'] = "party_master.id = p_enq_master.party_id";
		$data['leftJoin']['item_master'] = "item_master.item_name = p_enq_trans.item_name";

		if(!empty($data['status'])){
			if($data['status'] == 1) { $data['where']['p_enq_trans.trans_status'] = 4; }			
			if($data['status'] == 2) { $data['where']['p_enq_trans.trans_status'] = 3; }			
		}else{
			$data['where_in']['p_enq_trans.trans_status'] = [0,1,2];
		}

		$data['where']['p_enq_master.entry_type'] = $data['entry_type'];
		$data['where']['p_enq_master.trans_date >='] = $this->startYearDate;
		$data['where']['p_enq_master.trans_date <='] = $this->endYearDate;
		$data['order_by']['p_enq_master.id'] = 'DESC';

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "p_enq_master.trans_number";
        $data['searchCol'][] = "DATE_FORMAT(p_enq_master.trans_date,'%d-%m-%Y')";
        $data['searchCol'][] = "party_master.party_name";
        $data['searchCol'][] = "p_enq_trans.item_name";
        $data['searchCol'][] = "p_enq_trans.qty";
        $data['searchCol'][] = "p_enq_trans.confirm_qty";
        $data['searchCol'][] = "p_enq_trans.confirm_rate";
        $data['searchCol'][] = "p_enq_trans.confirm_date";
        $data['searchCol'][] = "p_enq_trans.item_remark";

        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
		if (isset($data['order'])) { $data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir']; }
		return $this->pagingRows($data);
	}

	public function getEnquiry($id){
		$data['tableName'] = $this->p_enq_master;
		$data['where']['id'] = $id;
		$result = $this->row($data);

		$result->itemData = $this->getEnquiryTrans($id);
		return $result;
	}

	public function getEnquiryTrans($id){
		$data['select'] = "p_enq_trans.*,unit_master.unit_name,unit_master.description,item_master.item_code,item_master.hsn_code,item_master.gst_per,item_category.category_name";
		$data['join']['unit_master'] = "unit_master.id = p_enq_trans.unit_id";
		$data['leftJoin']['item_master'] = "item_master.item_name = p_enq_trans.item_name";
		$data['leftJoin']['item_category'] = "item_category.id = item_master.item_type";
		$data['where']['p_enq_trans.trans_main_id'] = $id;
		$data['tableName'] = $this->p_enq_trans;
		$result = $this->rows($data);
		return $result;
	}

	public function save($data){
		try {
            $this->db->trans_begin();
			
			$itemData = $data['itemData'];
			unset($data['trans_id'],$data['unit_name'],$data['row_index'],$data['item_name'],$data['item_id'],$data['item_type'],$data['item_type_name'],$data['qty'],$data['item_remark'],$data['itemData'],$data['unit_id'],$data['req_id']);

			if($this->checkDuplicate($data) > 0):
                $errorMessage['trans_no'] = "Enquiry No. is duplicate.";
                $result = ['status'=>0,'message'=>$errorMessage];
            endif;
            
            if(!empty($data['id'])):
				$itemList = $this->getEnquiryTrans($data['id']);
				foreach($itemList as $row):
					$this->trash($this->p_enq_trans,['id'=>$row->id]);
					if(!empty($row->req_id)):
						$this->edit($this->purchase_indent,['id'=>$row->req_id],['order_status'=>1]);
					endif;
				endforeach;
			endif;

            $result = $this->store($this->p_enq_master,$data,'Purchase Enquiry');

            foreach($itemData as $row):
                if(!empty($row['req_id'])):
					$this->edit($this->purchase_indent,['id'=>$row['req_id']],['order_status'=>2]);
                endif;
                
                $row['entry_type'] = $data['entry_type'];
                $row['trans_main_id'] = $result['id'];
                $row['is_delete'] = 0;
                $this->store($this->p_enq_trans,$row);
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

	public function checkDuplicate($data){
        $queryData['tableName'] = $this->p_enq_master;
        $queryData['where']['trans_number'] = $data['trans_number'];
        $queryData['where']['entry_type'] = $data['entry_type'];

        if(!empty($data['id']))
            $queryData['where']['id !='] = $data['id'];

        $queryData['resultType'] = "numRows";
        return $this->specificRow($queryData);
    }

	public function delete($id){
        try{
            $this->db->trans_begin();

	        $itemList = $this->getEnquiryTrans($id);
            foreach($itemList as $row):
                $this->trash($this->p_enq_trans,['id'=>$row->id]);
				if(!empty($row->req_id)):
                    $this->edit($this->purchase_indent,['id'=>$row->req_id],['order_status'=>1]);
                endif;
            endforeach;
            
            $result = $this->trash($this->p_enq_master,['id'=>$id],'Purchase Enquiry');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Exception $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

	public function getEnquiryData($enq_id){
		$data = array();
		$data['tableName'] = $this->p_enq_trans;
		$data['select'] = "p_enq_trans.*,unit_master.unit_name,unit_master.description";
		$data['join']['unit_master'] = "unit_master.id = p_enq_trans.unit_id";
		$data['where']['p_enq_trans.trans_main_id'] = $enq_id;
		$result = $this->rows($data);

		if (!empty($result)) :
			$i = 1;
			$html = "";
			foreach ($result as $row) :
				if (empty($row->trans_status)) :
					$checked = "";
					$disabled = "disabled";
					$html .= '<tr>
							<td class="text-center">
								<input type="checkbox" id="md_checkbox' . $i . '" class="filled-in chk-col-success itemCheck" data-rowid="' . $i . '" check="' . $checked . '" ' . $checked . ' />
								<label for="md_checkbox' . $i . '">' . $i . '</label>
							</td>
							<td>
								' . $row->item_name . '
								<input type="hidden" name="item_name[]" id="item_name' . $i . '" class="form-control" value="' . $row->item_name . '" ' . $disabled . ' />
								<input type="hidden" name="trans_id[]" id="trans_id' . $i . '" class="form-control" value="' . $row->id . '" ' . $disabled . ' />
							</td>
							<td>
								<input type="number" name="qty[]" id="qty' . $i . '" class="form-control floatOnly" value="' . $row->qty . '" min="0" ' . $disabled . ' / readOnly>
								<div class="error qty' . $row->id . '"></div>
							</td>
							<td>
								<select name="feasible[]" id="feasible' . $i . '"  class="form-control" '  . $disabled . ' >
									<option value="1">Yes</option>
									<option value="2">No</option>
								</select>
								
								<div class="error qty' . $row->id . '"></div>
							</td>
							<td>
								<input type="text" name="rate[]" id="rate' . $i . '" class="form-control floatOnly" value="0" min="0" ' . $disabled . ' />
								<div class="error rate' . $row->id . '"></div>
							</td>
							<td>
								<input type="text" name="quote_no[]" id="quote_no' . $i . '" class="form-control" value="0" min="0" ' . $disabled . ' />
							</td>
							<td>
								<input type="date" name="quote_date[]" id="quote_date' . $i . '" class="form-control " value="0" min="0" ' . $disabled . ' />
							</td>
							<td>
								<input type="text" name="quote_remark[]" id="quote_remark' . $i . '" class="form-control" value="" min="0" ' . $disabled . ' />
							</td>
						</tr>';
				else :
					$data = array();
					$data['tableName'] = 'item_master';
					$data['where']['id'] = $row->item_id;
					$itemData = $this->row($data);

					$checked = "checked";
					$disabled = "disabled";
					$html .= '<tr>
							<td class="text-center">
								<input type="checkbox" id="md_checkbox' . $i . '" class="filled-in chk-col-success itemCheck" data-rowid="' . $i . '" check="' . $checked . '" ' . $checked . ' ' . $disabled . ' />
								<label for="md_checkbox' . $i . '">' . $i . '</label>
							</td>
							<td>
								' . $itemData->item_name . '
								<input type="hidden" name="item_name[]" id="item_name' . $i . '" class="form-control" value="' . $itemData->item_name . '" ' . $disabled . ' />
								<input type="hidden" name="trans_id[]" id="trans_id' . $i . '" class="form-control" value="' . $row->id . '" ' . $disabled . ' />
							</td>
							<td>
								<input type="number" name="qty[]" id="qty' . $i . '" class="form-control floatOnly" value="' . $row->confirm_qty . '" min="0" ' . $disabled . ' />
								<div class="error qty' . $row->id . '"></div>
							</td>
							<td>
								<select name="feasible[]" id="feasible' . $i . '"  class="form-control" '  . $disabled . ' >
									<option value="1" '.((!empty($row->feasible) && $row->feasible == 1) ? 'selected' : '').'>Yes</option>
									<option value="2" '.((!empty($row->feasible) && $row->feasible == 2) ? 'selected' : '').'>No</option>
								</select>								
								<div class="error qty' . $row->id . '"></div>
							</td>
							<td>
								<input type="number" name="rate[]" id="rate' . $i . '" class="form-control floatOnly" value="' . $row->confirm_rate . '" min="0" ' . $disabled . ' />
								<div class="error rate' . $row->id . '"></div>
							</td>
							<td>
								<input type="number" name="quote_no[]" id="quote_no' . $i . '" class="form-control" value="'.$row->quote_no.'" ' . $disabled . ' />
							</td>
							<td>
								<input type="date" name="quote_date[]" id="quote_date' . $i . '" class="form-control" value="'.$row->quote_date.'" ' . $disabled . ' />
							</td>
							<td>
								<input type="text" name="quote_remark[]" id="quote_remark' . $i . '" class="form-control" value="'.$row->quote_remark.'" ' . $disabled . ' />
							</td>
						</tr>';
				endif;
				$i++;
			endforeach;
		else :
			$html = '<tr><td colspan="6" class="text-center">No data available in table</td></tr>';
		endif;
		return $html;
	}

	public function enquiryConfirmed($enqConData){
		try {
            $this->db->trans_begin();

			$data = array();
			$data['tableName'] = $this->p_enq_master;
			$data['where']['id'] = $enqConData['enq_id'];
			$enquiryData = $this->row($data);

			$data = array();
			$data['tableName'] = $this->p_enq_trans;
			$data['select'] = "p_enq_trans.*,unit_master.unit_name,unit_master.description";
			$data['join']['unit_master'] = "unit_master.id = p_enq_trans.unit_id";
			$data['where']['p_enq_trans.trans_main_id'] = $enqConData['enq_id'];
			$data['where_in']['p_enq_trans.id'] = $enqConData['trans_id'];
			$enquiryItemData = $this->rows($data);


			$supplierId = $enquiryData->party_id;

			$masterData = [
				'id' => $enqConData['enq_id'],
				'confirm_date' => date("Y-m-d"),
				'party_id' => $supplierId
			];

			//save purchase enquiry master data
			$this->store($this->p_enq_master, $masterData);

			//save purchase enquiry items
			foreach ($enquiryItemData as $key => $row) :
				$itemMasterData = [
					'id' => "",
					'item_name' => $enqConData['item_name'][$key],
					'price' => $enqConData['rate'][$key],
					'unit_id' => $row->unit_id,
					'item_type' => $row->item_type
				];
				$data = array();
				$data['tableName'] = "item_master";
				$data['where']['item_name'] = $enqConData['item_name'][$key];
				$item = $this->row($data);
				
				if (empty($item)) :
					$itemSave = $this->store('item_master', $itemMasterData);
					$itemId = $itemSave['insert_id'];
				else :
					$itemId = $item->id;
					$itemMasterData['id'] = $item->id;
					$itemSave = $this->store('item_master', $itemMasterData);
				endif;

				$transData = [
					'id' => $row->id,
					'item_id' => $itemId,
					'confirm_qty' => $enqConData['qty'][$key],
					'confirm_rate' => $enqConData['rate'][$key],
					'feasible' => $enqConData['feasible'][$key],
					'quote_remark' => $enqConData['quote_remark'][$key],
					'quote_no' => $enqConData['quote_no'][$key],
					'quote_date' => $enqConData['quote_date'][$key],
					'trans_status' => 1
				];
				$this->store($this->p_enq_trans, $transData);
			endforeach;

			$confirmedItems = $this->getEnquiryTransConfirm($enqConData['enq_id']);
			if ($confirmedItems <= 0) :
				$this->store($this->p_enq_master, ['id' => $enqConData['enq_id'], 'trans_status' => 1]);
			endif;

			if ($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return ['status' => 1, 'message' => 'Purchase Enquiry Confirmed Successfully.'];
			endif;
		} catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}

	public function getEnquiryTransConfirm($id){
		$data['where']['trans_main_id'] = $id;
		$data['where']['trans_status'] = 1;
		$data['tableName'] = $this->p_enq_trans;
		$result = $this->numRows($data);
		return $result;
	}

	public function itemSearch(){
		$data['tableName'] = 'item_master';
		$data['select'] = 'item_name';
		$data['where']['item_type != '] = 1;
		$result = $this->rows($data);
		$searchResult = array();
		foreach ($result as $row) {
			$searchResult[] = $row->item_name;
		}
		return  $searchResult;
	}

	public function approvePEnquiry($data){
		try {
            $this->db->trans_begin();

			$this->store($this->p_enq_trans, ['id' => $data['id'], 'trans_status' => $data['val']]);

			if ($this->db->trans_status() !== FALSE) :
				$this->db->trans_commit();
				return ['status' => 1, 'message' => 'Purchase Enquiry ' . $data['msg'] . ' successfully.'];
			endif;
		} catch (\Exception $e) {
			$this->db->trans_rollback();
			return ['status' => 2, 'message' => "somthing is wrong. Error : " . $e->getMessage()];
		}
	}
	
	
}
