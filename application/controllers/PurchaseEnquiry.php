<?php
class PurchaseEnquiry extends MY_Controller{
    private $indexPage = 'purchase_enquiry/index';
    private $enquiryForm = "purchase_enquiry/form";
    private $confirmForm = "purchase_enquiry/enquiry_confirm";
    
    public function __construct(){
        parent::__construct();
        $this->isLoggedin();
		$this->data['headData']->pageTitle = "Purchase Enquiries";
		$this->data['headData']->controller = "purchaseEnquiry";
		$this->data['headData']->pageUrl = "purchaseEnquiry";
        $this->data['entryData'] = $this->transMainModel->getEntryType(['controller'=>'purchaseEnquiry','tableName'=>'p_enq_master']);
    }

    public function index(){
        $this->data['tableHeader'] = getPurchaseDtHeader($this->data['headData']->controller);
        $this->load->view($this->indexPage,$this->data);
    }

    public function getDTRows($status=0){
        $data = $this->input->post(); $data['status']=$status;
        $data['entry_type'] = $this->data['entryData']->id;
        $result = $this->purchaseEnquiry->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):
            $row->sr_no = $i++;
            if($row->trans_status == 0):
				$row->status = '<span class="font-10 font-weight-bold badge bg-danger">Pending</span>';
			elseif($row->trans_status == 1):
				$row->status = '<span class="font-10 font-weight-bold badge bg-warning">Quotation</span>';
            elseif($row->trans_status == 2):
                $row->status = '<span class="font-10 font-weight-bold badge bg-primary">Approve</span>';
            elseif($row->trans_status == 3):
                $row->status = '<span class="font-10 font-weight-bold badge bg-dark">Rejected</span>';
            elseif($row->trans_status == 4):
                $row->status = '<span class="font-10 font-weight-bold badge bg-success">Completed</span>';             
            endif;	
            $row->controller = "purchaseEnquiry";
            $sendData[] = getPurchaseEnquiryData($row);
        endforeach;        
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function addEnquiry(){
        $this->data['entry_type'] = $this->data['entryData']->id;
        $this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
        $this->data['trans_no'] = $this->data['entryData']->trans_no;
        $this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];        
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>2]);
        $this->data['categoryList'] = $this->itemCategory->getCategoryList(['final_category'=>0, 'ref_id'=>0]);
        $this->data['unitData'] = $this->item->itemUnits();
        $this->load->view($this->enquiryForm,$this->data);
    }

    public function save(){
        $data = $this->input->post();
        $errorMessage = array();
        if(empty($data['party_id']))
            $errorMessage['party_id'] = "Supplier Name is required.";
        if(empty($data['trans_no']))
            $errorMessage['trans_no'] = 'Enquiry No. is required.';
        if(empty($data['itemData']))
            $errorMessage['itemData'] = 'Item Detail is required.';

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:	
            $data['trans_number'] = $data['trans_prefix'].$data['trans_no'];
            $data['vou_name_l'] = $this->data['entryData']->vou_name_long;
            $data['vou_name_s'] = $this->data['entryData']->vou_name_short;

            $p_id = count($data['party_id']);
            if($p_id > 0){
                foreach($data['party_id'] as $row){
                    $data['party_id'] = $row;
                    $result = $this->purchaseEnquiry->save($data);
                }
            }else{
                $result = $this->purchaseEnquiry->save($data);
            }
            $this->printJson($result);
        endif;
    }

    public function edit($id){
        $this->data['dataRow'] = $this->purchaseEnquiry->getEnquiry($id);    
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>2]);
        $this->data['categoryList'] = $this->itemCategory->getCategoryList(['final_category'=>0, 'ref_id'=>0]);
        $this->data['unitData'] = $this->item->itemUnits();
        $this->load->view($this->enquiryForm,$this->data);
    }

    public function delete(){
        $id = $this->input->post('id');
		if(empty($id)):
			$this->printJson(['status'=>0,'message'=>'Something went wrong...Please try again.']);
		else:
			$this->printJson($this->purchaseEnquiry->delete($id));
		endif;
    }

    public function getEnquiryData(){
        $enq_id = $this->input->post('enq_id');
        $this->data['enquiryItems'] = $this->purchaseEnquiry->getEnquiryData($enq_id);
        $this->load->view($this->confirmForm,$this->data);
    }

    public function enquiryConfirmed(){
        $data = $this->input->post();
        $errorMessage = array();

        if(empty($data['item_name'][0])):
            $errorMessage['item_name_error'] = "Please select Item.";
        else:
            foreach($data['qty'] as $key=>$value):
                if(empty($value)):
                    $errorMessage['qty'.$data['trans_id'][$key]] = "Qty is required.";
                endif;
            endforeach;

            foreach($data['rate'] as $key=>$value):
                if(empty($value)):
                    $errorMessage['rate'.$data['trans_id'][$key]] = "Price is required.";
                endif;
            endforeach;

            foreach($data['quote_no'] as $key=>$value):
                if(empty($value)):
                    $errorMessage['quote_no'.$data['trans_id'][$key]] = "Quotation No is required.";
                endif;
            endforeach;

            foreach($data['quote_date'] as $key=>$value):
                if(empty($value)):
                    $errorMessage['quote_date'.$data['trans_id'][$key]] = "Quotation Date is required.";
                endif;
            endforeach;
        endif;

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $data['created_by'] = $this->session->userdata('loginId');
            $this->printJson($this->purchaseEnquiry->enquiryConfirmed($data));
        endif;
    }

	public function itemSearch(){
		$this->printJson($this->purchaseEnquiry->itemSearch());
	}

    public function approvePEnquiry(){
		$data = $this->input->post();
		if(empty($data['id'])):
			$this->printJson(['status'=>0,'message'=>'Something went wrong...Please try again.']);
		else:
			$this->printJson($this->purchaseEnquiry->approvePEnquiry($data));
		endif;
	}	

    public function getItemData(){
        $data = $this->input->post();
        $itemData = $this->item->getItem(['item_name'=>$data['item_name']]);

        $itemType = "Select Item Type";
		if(!empty($itemData->item_type)){
            $categoryData = $this->itemCategory->getCategoryList(['final_category'=>0, 'ref_id'=>0]);		
			foreach($categoryData as $row){
				$selected = (!empty($itemData->item_type) && $itemData->item_type == $row->id)?'selected':'';
				$itemType .= '<option value="'.$row->id.'" '.$selected.'>'.$row->category_name.'</option>';
			}
		}	
        $unit = "Select Unit";
		if(!empty($itemData->unit_id)){
			$unitData = $this->item->itemUnits();			
			foreach($unitData as $row){
				$selected = (!empty($itemData->unit_id) && $itemData->unit_id == $row->id)?'selected':'';
				$unit .= '<option value="'.$row->id.'" '.$selected.'>['.$row->unit_name.'] '.$row->description.'</option>';
			}
		}	
		$item_id = (!empty($itemData->id)?$itemData->id:'');
		$this->printJson(['status'=>1,'item_id'=>$item_id,'unit_id'=>$unit,'item_type'=>$itemType]);
    }
    
    public function addEnqFromRequest($id){
		$this->data['req_id'] = $id;
        $this->data['entry_type'] = $this->data['entryData']->id;
        $this->data['trans_prefix'] = $this->data['entryData']->trans_prefix;
        $this->data['trans_no'] = $this->data['entryData']->trans_no;
        $this->data['trans_number'] = $this->data['trans_prefix'].$this->data['trans_no'];        
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>2]);
        $this->data['categoryList'] = $this->itemCategory->getCategoryList(['final_category'=>0, 'ref_id'=>0]);
        $this->data['unitData'] = $this->item->itemUnits();
        $this->data['reqItemList'] = $this->purchaseIndent->getPurchaseRequestForOrder($id);
        $this->load->view($this->enquiryForm,$this->data);
    }
}
?>