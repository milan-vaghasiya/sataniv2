<?php
class StockVerification extends MY_Controller
{
    private $indexPage = "stock_verification/index";
    private $formPage = "stock_verification/form";

    public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "Stock Verification";
		$this->data['headData']->controller = "stockVerification";
		$this->data['headData']->pageUrl = "stockVerification";
        $this->data['entryData'] = $this->transMainModel->getEntryType(['controller'=>'stockVerification']);
	}

    public function index(){
        $this->data['pageHeader'] = 'STOCK VERIFICATION';
        $this->data['dataUrl'] = 'getDTRows/1';
        $this->data['tableHeader'] = getStoreDtHeader("stockVerification");
        $this->data['locationList'] = $this->storeLocation->getStoreLocationList(['final_location'=>1,'store_type'=>0]);

        $locationList = [];
        foreach($this->data['locationList'] as $key => $row){
            $locationList[$row->store_name] = array(
                'id' => $row->id,
                'store_name' => $row->store_name,
            );
        }
        $this->data['locationList'] = $locationList;

        $this->load->view($this->indexPage,$this->data);
    }         

    public function getDTRows($item_type="",$store_name = ""){
        $data = $this->input->post();
        $data['item_type'] = $item_type;
        $data['store_name'] = !empty($store_name) ? decodeURL($store_name) : '';
        $data['location_id'] = $this->storeLocation->getLocationIds($data);
        if (!empty($data['item_type']) && $data['item_type'] == 99) {
            $data['stock_where'] = 'stock_transaction.location_id = ' . $this->FORGE_STORE->id;
        }
        if (!empty($data['location_id'])) {
            $data['stock_where'] = 'stock_transaction.location_id IN (' . implode(',', $data['location_id']) . ')';
        }
        $data['item_type'] = (!empty($data['item_type']) && $data['item_type'] == 99) ? 1 : $data['item_type'];
        
        $result = $this->stockVerify->getDTRows($data);
        $sendData = array();$i=($data['start'] + 1);
        foreach($result['data'] as $row):
            $row->sr_no = $i++;
            $sendData[] = getStockVerificationData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function editStock(){
        $data = $this->input->post();
        $this->data['item_id'] =  $data['item_id'];
        // $this->data['stockData']= $this->itemStock->getItemStockBatchWise(['item_id'=>$data['item_id'], 'location_id'=>$this->RTD_STORE->id,'group_by'=>'location_id,batch_no']);
        $this->data['stockData']= $this->itemStock->getItemStockBatchWise(['item_id'=>$data['item_id'], 'group_by'=>'location_id,batch_no,heat_no']);
        $this->load->view($this->formPage, $this->data);
    }

    public function save(){
        $data = $this->input->post(); 
        $errorMessage = array();
        if(empty($data['item_id']))
            $errorMessage['item_id'] = "Item is required.";
		if(!empty($data['physical_qty'])): $i=1;
			foreach($data['physical_qty'] as $key=>$value):
                if($value != ''){
				    if(empty($data['reason'][$key])){
					    $errorMessage['reason'.$i] = "Reason is required.";
                    }
                } $i++;
			endforeach;
		endif;

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $data['entry_type'] = $this->data['entryData']->id;
            $this->printJson($this->stockVerify->save($data));
        endif;
    }
}
?>