<?php
class DebitNoteModel extends MasterModel{
    private $transMain = "trans_main";
    private $transChild = "trans_child";
    private $transExpense = "trans_expense";
    private $transDetails = "trans_details";
    private $stockTrans = "stock_transaction";

    public function getDTRows($data){
        $data['tableName'] = $this->transMain;
        $data['select'] = "trans_main.*";

        $data['where']['trans_main.entry_type'] = $data['entry_type'];

        if($data['status'] == 0):
            $data['where']['trans_main.trans_status !='] = 3;
        elseif($data['status'] == 1):
            $data['where']['trans_main.trans_status'] = 3;
        endif;

        $data['where']['trans_main.trans_date >='] = $this->startYearDate;
        $data['where']['trans_main.trans_date <='] = $this->endYearDate;

        $data['order_by']['trans_main.trans_date'] = "DESC";
        $data['order_by']['trans_main.id'] = "DESC";

        $data['searchCol'][] = "";
        $data['searchCol'][] = "";
        $data['searchCol'][] = "trans_main.order_type";
        $data['searchCol'][] = "trans_main.trans_number";
        $data['searchCol'][] = "DATE_FORMAT(trans_main.trans_date,'%d-%m-%Y')";
        $data['searchCol'][] = "trans_main.party_name";
        $data['searchCol'][] = "trans_main.taxable_amount";
        $data['searchCol'][] = "trans_main.gst_amount";
        $data['searchCol'][] = "trans_main.net_amount";

        $columns =array(); foreach($data['searchCol'] as $row): $columns[] = $row; endforeach;
        if(isset($data['order'])){$data['order_by'][$columns[$data['order'][0]['column']]] = $data['order'][0]['dir'];}
        
        return $this->pagingRows($data);
    }

    public function save($data){
        try{
            $this->db->trans_begin();

            if(empty($data['id'])):
                $data['trans_no'] = $this->transMainModel->nextTransNo($data['entry_type']);
                $data['trans_number'] = $data['trans_prefix'].$data['trans_no'];
            endif;

            if(!empty($data['id'])):
                $vouData = $this->getDebitNote(['id'=>$data['id'],'itemList'=>1]);

                foreach($vouData->itemList as $row):
                    if($row->stock_eff == 1 && !empty($row->ref_id)):
                        $setData = array();
                        $setData['tableName'] = $this->transChild;
                        $setData['where']['id'] = $row->ref_id;
                        $setData['set_value']['dispatch_qty'] = 'IF(`dispatch_qty` - '.$row->qty.' >= 0, `dispatch_qty` - '.$row->qty.',0)';
                        $setData['update']['trans_status'] = "(CASE WHEN dispatch_qty >= qty THEN 1 ELSE 0 END)";
                        $this->setValue($setData);
                    endif;

                    $this->trash($this->transChild,['id'=>$row->id]);
                endforeach;

                //$this->trash($this->transChild,['trans_main_id'=>$data['id']]);
                $this->trash($this->transExpense,['trans_main_id'=>$data['id']]);
                $this->remove($this->transDetails,['main_ref_id'=>$data['id'],'table_name'=>$this->transMain,'description'=>"DN TERMS"]);
                $this->remove($this->transDetails,['main_ref_id'=>$data['id'],'table_name'=>$this->transMain,'description'=>"DN MASTER DETAILS"]);
                $this->remove($this->stockTrans,['main_ref_id'=>$data['id'],'entry_type'=>$data['entry_type']]);

                if(!empty($vouData->ref_id)):
                    $setData = array();
                    $setData['tableName'] = $this->transMain;
                    $setData['where']['id'] = $vouData->ref_id;
                    $setData['set_value']['rop_amount'] = 'IF(`rop_amount` - '.$vouData->net_amount.' >= 0, `rop_amount` - '.$vouData->net_amount.',0)';
                    $this->setValue($setData);
                endif;
            endif;
            
            if($data['memo_type'] == "CASH"):
				$cashAccData = $this->party->getParty(['system_code'=>"CASHACC"]);
				$data['opp_acc_id'] = $cashAccData->id;
			else:
				$data['opp_acc_id'] = $data['party_id'];
			endif;
            $data['ledger_eff'] = 1;
            $data['gstin'] = (!empty($data['gstin']))?$data['gstin']:"URP";
            $data['disc_amount'] = array_sum(array_column($data['itemData'],'disc_amount'));;
            $data['total_amount'] = $data['taxable_amount'] + $data['disc_amount'];
            $data['gst_amount'] = $data['igst_amount'] + $data['cgst_amount'] + $data['sgst_amount'];

            $accType = ($data['order_type'] == "Increase Sales")?"SALESACC":"PURACC";
            $spAcc = $this->party->getParty(['system_code'=>$accType]);
            $data['vou_acc_id'] = (!empty($spAcc))?$spAcc->id:0;

            $masterDetails = (!empty($data['masterDetails']))?$data['masterDetails']:array();
            $itemData = $data['itemData'];

            $transExp = getExpArrayMap(((!empty($data['expenseData']))?$data['expenseData']:array()));
			$expAmount = $transExp['exp_amount'];
            $termsData = (!empty($data['conditions']))?$data['conditions']:array();

            unset($transExp['exp_amount'],$data['itemData'],$data['expenseData'],$data['termsData'],$data['masterDetails'],$data['conditions']);		

            $result = $this->store($this->transMain,$data,'Debit Note');

            if(!empty($masterDetails)):
                $masterDetails['id'] = "";
                $masterDetails['main_ref_id'] = $result['id'];
                $masterDetails['table_name'] = $this->transMain;
                $masterDetails['description'] = "DN MASTER DETAILS";
                $this->store($this->transDetails,$masterDetails);
            endif;

            $expenseData = array();
            if($expAmount <> 0):				
				$expenseData = $transExp;
			endif;

            if(!empty($termsData)):
                $termsData = [
                    'id' =>"",
                    'table_name' => $this->transMain,
                    'description' => "DN TERMS",
                    'main_ref_id' => $result['id'],
                    't_col_1' => $termsData
                ];
                $this->store($this->transDetails,$termsData);
            endif;

            $i=1;
            foreach($itemData as $row):
                $row['entry_type'] = $data['entry_type'];
                $row['trans_main_id'] = $result['id'];
                $row['gst_amount'] = $row['igst_amount'];
                $row['is_delete'] = 0;

                $itemTrans = $this->store($this->transChild,$row);

                if($row['stock_eff'] == 1):
                    $stockData = [
                        'id' => "",
                        'entry_type' => $data['entry_type'],
                        'unique_id' => 0,
                        'ref_date' => $data['trans_date'],
                        'ref_no' => $data['trans_number'],
                        'main_ref_id' => $result['id'],
                        'child_ref_id' => $itemTrans['id'],
                        'location_id' => $this->RTD_STORE->id,
                        'batch_no' => "GB",
                        'party_id' => $data['party_id'],
                        'item_id' => $row['item_id'],
                        'p_or_m' => -1,
                        'qty' => $row['qty'],
                        'size' => $row['packing_qty'],
                        'price' => $row['price']
                    ];

                    $this->store($this->stockTrans,$stockData);

                    if(!empty($row['ref_id'])):
                        $setData = array();
                        $setData['tableName'] = $this->transChild;
                        $setData['where']['id'] = $row['ref_id'];
                        $setData['set']['dispatch_qty'] = 'dispatch_qty, + '.$row['qty'];
                        $setData['update']['trans_status'] = "(CASE WHEN dispatch_qty >= qty THEN 1 ELSE 0 END)";
                        $this->setValue($setData);
                    endif;
                endif;
            endforeach;

            if(!empty($data['ref_id'])):
                $setData = array();
                $setData['tableName'] = $this->transMain;
                $setData['where']['id'] = $data['ref_id'];
                $setData['set']['rop_amount'] = 'rop_amount, + '.$data['net_amount'];
                $this->setValue($setData);
            endif;
            
            $data['id'] = $result['id'];
            $this->transMainModel->ledgerEffects($data,$expenseData);

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }

    public function getDebitNote($data){
        $queryData = array();
        $queryData['tableName'] = $this->transMain;
        $queryData['select'] = "trans_main.*";
        $queryData['where']['trans_main.id'] = $data['id'];
        $result = $this->row($queryData);

        if($data['itemList'] == 1):
            $result->itemList = $this->getDebitNoteItems($data);
        endif;

        $queryData = array();
        $queryData['tableName'] = $this->transExpense;
        $queryData['where']['trans_main_id'] = $data['id'];
        $result->expenseData = $this->row($queryData);

        $queryData = array();
        $queryData['tableName'] = $this->transDetails;
        $queryData['select'] = "t_col_1 as condition";
        $queryData['where']['main_ref_id'] = $data['id'];
        $queryData['where']['table_name'] = $this->transMain;
        $queryData['where']['description'] = "DN TERMS";
        $result->termsConditions = $this->row($queryData);

        return $result;
    }

    public function getDebitNoteItems($data){
        $queryData = array();
        $queryData['tableName'] = $this->transChild;
        $queryData['select'] = "trans_child.*";
        $queryData['where']['trans_child.trans_main_id'] = $data['id'];
        $result = $this->rows($queryData);
        return $result;
    }

    public function getDebitNoteItem($data){
        $queryData = array();
        $queryData['tableName'] = $this->transChild;
        $queryData['where']['id'] = $data['id'];
        $result = $this->row($queryData);
        return $result;
    }

    public function delete($id){
        try{
            $this->db->trans_begin();

            $postData["table_name"] = $this->transMain;
            $postData['where'] = [['column_name'=>'from_entry_type','column_value'=>$this->data['entryData']->id]];
            $postData['find'] = [['column_name'=>'ref_id','column_value'=>$id]];
            $checkRef = $this->checkEntryReference($postData);
            if($checkRef['status'] == 0):
                return $checkRef;
            endif;

            $vouData = $this->getDebitNote(['id'=>$id,'itemList'=>1]);
            if(!empty($vouData->ref_id)):
                $setData = array();
                $setData['tableName'] = $this->transMain;
                $setData['where']['id'] = $vouData->ref_id;
                $setData['set_value']['rop_amount'] = 'IF(`rop_amount` - '.$vouData->net_amount.' >= 0, `rop_amount` - '.$vouData->net_amount.', 0)';
                $this->setValue($setData);
            endif;

            foreach($vouData->itemList as $row):
                if($row->stock_eff == 1 && !empty($row->ref_id)):
                    $setData = array();
                    $setData['tableName'] = $this->transChild;
                    $setData['where']['id'] = $row->ref_id;
                    $setData['set_value']['dispatch_qty'] = 'IF(`dispatch_qty` - '.$row->qty.' >= 0, `dispatch_qty` - '.$row->qty.', 0)';
                    $setData['update']['trans_status'] = "(CASE WHEN dispatch_qty >= qty THEN 1 ELSE 0 END)";
                    $this->setValue($setData);
                endif;

                $this->trash($this->transChild,['id'=>$row->id]);
            endforeach;

            $this->transMainModel->deleteLedgerTrans($id);

            //$this->trash($this->transChild,['trans_main_id'=>$id]);
            $this->trash($this->transExpense,['trans_main_id'=>$id]);
            
            $this->remove($this->transDetails,['main_ref_id'=>$id,'table_name'=>$this->transMain,'description'=>"DN TERMS"]);
            $this->remove($this->transDetails,['main_ref_id'=>$id,'table_name'=>$this->transMain,'description'=>"DN MASTER DETAILS"]);
            $this->remove($this->stockTrans,['main_ref_id'=>$id,'entry_type'=>$this->data['entryData']->id]);
            
            $result = $this->trash($this->transMain,['id'=>$id],'Debit Note');

            if ($this->db->trans_status() !== FALSE):
                $this->db->trans_commit();
                return $result;
            endif;
        }catch(\Throwable $e){
            $this->db->trans_rollback();
            return ['status'=>2,'message'=>"somthing is wrong. Error : ".$e->getMessage()];
        }
    }
}
?>