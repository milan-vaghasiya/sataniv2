<?php
class StockInward extends MY_Controller{
    private $indexPage = "stock_inward/index";
    private $form = "stock_inward/form";    

    public function __construct(){
		parent::__construct();
		$this->data['headData']->pageTitle = "Opening Stock";
		$this->data['headData']->controller = "stockInward";        
        $this->data['headData']->pageUrl = "stockInward";
        $this->data['entryData'] = $this->transMainModel->getEntryType(['controller'=>'stockInward']);        
	}

    public function index(){
        $this->data['tableHeader'] = getStoreDtHeader("stockInward");
        $this->load->view($this->indexPage,$this->data);
    }

    public function getDTRows(){
        $data = $this->input->post();
        $data['entry_type'] = $this->data['entryData']->id;
        $result = $this->itemStock->getItemInwardDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):
            $row->sr_no = $i++;
            $sendData[] = getStockInwardData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

	public function addStock(){
        $data = $this->input->post();
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>['1,2,3']]);
        $this->data['locationList'] = $this->storeLocation->getStoreLocationList(['store_type'=>'0,15','final_location'=>1]);
        $this->load->view($this->form, $this->data);
    }
	
	public function save(){
        $data = $this->input->post();
		$errorMessage = array();		

        if(empty($data['item_id']))
			$errorMessage['item_id'] = "Item Name is required.";
        if(empty(floatVal($data['qty'])))
			$errorMessage['qty'] = "Qty is required.";
        if(empty($data['location_id']))
            $errorMessage['location_id'] = "Location is required.";
        
        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $data['location_id'] = $data['location_id'];
			$data['batch_no'] =(!empty($data['batch_no'])?$data['batch_no']:'General Batch');
			$data['heat_no'] =(!empty($data['heat_no'])?$data['heat_no']:'Opening Stock');
			$data['ref_no'] = 'OP. STOCK';
            $data['entry_type'] = $this->data['entryData']->id;
            $this->printJson($this->itemStock->save($data));
        endif;
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->itemStock->deleteOpeningStock($id));
        endif;
    }
}
?>