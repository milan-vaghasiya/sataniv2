<?php
class StoreReport extends MY_Controller{
    public function __construct(){
		parent::__construct();
		$this->isLoggedin();
		$this->data['headData']->pageTitle = "Store Report";
		$this->data['headData']->controller = "reports/storeReport";
    }

    public function stockRegister(){
        $this->data['pageHeader'] = 'STOCK REGISTER';
        $this->data['headData']->pageUrl = "reports/storeReport/stockRegister";
        $this->data['locationList'] = $this->storeLocation->getStoreLocationList(['final_location'=>1,'store_type'=>0]);

        $locationList = [];
        foreach($this->data['locationList'] as $key => $row){
            $locationList[$row->store_name] = array(
                'id' => $row->id,
                'store_name' => $row->store_name,
            );
        }
        $this->data['locationList'] = $locationList;

        $this->load->view("reports/store_report/item_stock",$this->data);
    }

    public function getStockRegisterData(){
        $data = $this->input->post();
        $item_type = $data['item_type'];        
        $data['location_id'] = $this->storeLocation->getLocationIds($data);

        if (!empty($data['item_type']) && $data['item_type'] == 99) {
            $data['stock_where'] = 'stock_transaction.location_id = ' . $this->FORGE_STORE->id;
        } elseif (!empty($data['location_id'])) {
            $data['stock_where'] = 'stock_transaction.location_id IN (' . implode(',', $data['location_id']) . ')';
        } else {
            $data['stock_where'] = 'stock_transaction.location_id != ' . $this->FORGE_STORE->id;
        }

        $data['item_type'] = (!empty($data['item_type']) && $data['item_type'] == 99) ? 1 : $data['item_type'];
        
        $result = $this->storeReport->getStockRegisterData($data);

        $tbody = '';$i=1;
        foreach($result as $row):
            
            $batch_qty = floatVal($row->stock_qty);
            if(floatVal($row->stock_qty) > 0){
                $batch_qty = '<a href="'.base_url("reports/storeReport/batchStockHistory/".$row->item_id."/".$item_type).'" target="_blank" datatip="Ledger" flow="left">'.floatVal($row->stock_qty).'</a>';
            }
            
            $tbody .= '<tr>
                <td class="text-center">'.$i++.'</td>
                <td class="text-left">
					<a href="'.base_url("reports/storeReport/itemHistory/".$row->item_id).'" target="_blank" datatip="History" flow="left">'.$row->item_name.'</a>
				</td>
                <td  class="text-right">
                    '.$batch_qty.'
				</td>
            </tr>';
        endforeach;

        $this->printJson(['status'=>1,'tbody'=>$tbody]);
    }

    public function itemHistory($item_id=""){
        $this->data['itemData'] = $this->item->getItem(['id'=>$item_id]);
        $this->data['from_date'] = $this->startYearDate;
        $this->data['to_date'] = $this->endYearDate;
        $this->load->view('reports/store_report/item_history',$this->data);
    }
	
    public function getItemHistory(){
		$data = $this->input->post();
        $itemData = $this->item->getItem(['id'=>$data['item_id']]);
        $itemSummary = $this->storeReport->getItemSummary($data);
        $itemHistory = $this->storeReport->getItemHistory($data);

        $thead = '<tr class="text-center">
            <th colspan="6" class="text-left">'.((!empty($itemData))?$itemData->item_name:"Item History").'</th>
            <th colspan="2" class="text-right">Op. Stock : '.floatVal($itemSummary->op_stock_qty).'</th>
        </tr>
        <tr>
            <th style="min-width:25px;">#</th>
            <th style="min-width:100px;">Trans. Type</th>
            <th style="min-width:100px;">Trans. No.</th>
            <th style="min-width:50px;">Trans. Date</th>
            <th style="min-width:50px;">Heat No</th>
            <th style="min-width:50px;">Inward</th>
            <th style="min-width:50px;">Outward</th>
            <th style="min-width:50px;">Balance</th>
        </tr>';
		
        $i=1; $tbody =""; $tfoot=""; $balanceQty = $itemSummary->op_stock_qty;
        foreach($itemHistory as $row):  
            $balanceQty += $row->qty * $row->p_or_m;          
            $tbody .= '<tr>
                <td>' . $i++ . '</td>
                <td>'.$row->sub_menu_name.'<br> [ '.$row->store_name.' ] '.$row->location.' </td> 
                <td>'.$row->ref_no.'</td>
                <td>'.formatDate($row->ref_date).'</td>
                <td>'.$row->heat_no.'<br>'.$row->party_name.'</td>
                <td>'.floatVal($row->in_qty).'</td>
                <td>'.floatVal($row->out_qty).'</td>
                <td>'.floatVal($balanceQty).'</td>
            </tr>';
        endforeach;

        $tfoot .= '<tr>
            <th colspan="5" class="text-right">Cl. Stock</th>
            <th>' .floatVal($itemSummary->in_stock_qty). '</th>
            <th>' .floatVal($itemSummary->out_stock_qty). '</th>
            <th>' .floatVal($itemSummary->cl_stock_qty). '</th>
        </tr>';

        $this->printJson(['status'=>1,'thead'=>$thead,'tbody'=>$tbody,'tfoot'=>$tfoot]);
    }
	
	public function stockTransactions(){    
		$this->data['item_id'] = $this->input->post('item_id');
		$data['item_id'] = $this->data['item_id'];
        $result = $this->storeReport->getStockTransaction($data);
        
        $tbody = ""; $i=1;
        foreach($result as $row):
            $tbody .= '<tr>
                <td>'.$i++.'</td>
                <td class="text-left">'.$row->batch_no.'</td>
                <td  class="text-right">'.floatVal($row->stock_qty).'</td>
            </tr>';
        endforeach;
		$this->data['tbody'] = $tbody;
        $this->load->view("reports/store_report/brand_wise_trans",$this->data);
    }
    
    /*public function stockTransactions($id = ""){
        $this->data['item_id'] = $id;
        $this->data['pageHeader'] = 'STOCK REGISTER';
        $this->data['headData']->pageUrl = "reports/storeReport/stockRegister";
        $this->data['itemList'] = $this->item->getItemList();
        $this->load->view("reports/store_report/item_stock_trans",$this->data);
    }
    
    public function getStockTransaction(){
        $data = $this->input->post();
        $result = $this->storeReport->getStockTransaction($data);
        
        $tbody = ""; $i=1;
        foreach($result as $row):
            $tbody .= '<tr>
                <td>'.$i++.'</td>
                <td class="text-left">'.$row->batch_no.'</td>
                <td  class="text-right">'.floatVal($row->stock_qty).'</td>
            </tr>';
        endforeach;
        
        $this->printJson(['status'=>1,'tbody'=>$tbody]);
    }*/
    
   /* INVENTORY MONITORING REPORT CREATE BY RASHMI 15/05/2024*/
    public function inventoryMonitor(){
        $this->data['pageHeader'] = 'INVENTORY MONITORING REPORT';
        $this->data['categoryList'] = $this->itemCategory->getCategoryList(['final_category'=>0,'ref_id'=>0]);
        $this->load->view('reports/store_report/inventory_monitor',$this->data);
    }

	/*CREATE BY RASHMI 15/05/2024*/
    public function getInventoryMonitor(){
        $data = $this->input->post();
        $errorMessage = array();
		if(empty($data['to_date']))
			$errorMessage['toDate'] = "Date is required.";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $itemData = $this->storeReport->getInventoryMonitor($data);
            $tbody="";$i=1;$opningStock=0;$closingStock=0;$totalClosing=0;$fyOpeningStock=0;$totalOpeningStock=0;$monthlyInward=0;$monthlyCons=0;$inventory=0;$amount=0;$total=0;$totalInventory=0;$totalValue=0;$totalUP=0;
            
            foreach($itemData as $row):                
                $data['item_id'] = $row->id;
                $fyOSData = Array();
                $opningStock = (!empty($row->opening_qty)) ? $row->opening_qty : 0;
                $monthlyInward = $row->rqty;
                $monthlyCons = abs($row->iqty);
                $totalOpeningStock = floatval($opningStock);
                $closingStock = ($totalOpeningStock + $monthlyInward - $monthlyCons);
                $total = round(($closingStock * $row->price), 2);
                
                $tbody .= '<tr>
                    <td>'.$i++.'</td>
                    <td>'.(!empty($row->item_code)?'['.$row->item_code.'] ':'').$row->item_name.'</td>
                    <td>'.floatVal($totalOpeningStock).'</td>
                    <td>'.floatVal(round($monthlyInward,2)).'</td>
                    <td>'.floatVal(round($monthlyCons,2)).'</td>
                    <td>'.floatVal(round($closingStock,2)).'</td>
                    <td>'.number_format($row->price, 2).'</td>
                    <td>'.number_format($total, 2).'</td>
                </tr>';
                $totalInventory += round($row->price,2);
                $totalValue += $total;
                $totalClosing += $closingStock;
            endforeach;
            
            $totalAvgRate = (!empty($totalInventory)) ? round(($totalInventory / ($i-1)),2) : 0;
            $totalUP = (!empty($totalInventory)) ? round(($totalValue / $totalInventory),2) : 0;

            $this->printJson(['status'=>1, 'tbody'=>$tbody, 'totalClosing'=>number_format($totalClosing,2), 'totalInventory'=>number_format($totalAvgRate,2), 'totalUP'=>number_format($totalUP,2), 'totalValue'=>number_format($totalValue,2)]);
        endif;
    }

    /* Raw Material Stock Register Report */
    public function rmStockRegister() {
        $this->data['pageHeader'] = 'Raw Material Stock Register';
        $rmStock = $this->getRMStockRegister();
        $this->data['tableBody'] = $rmStock['tbody'];
        $this->data['materialGrade'] = $rmStock['materialGrade'];
        $this->load->view('reports/store_report/rm_stock_reg',$this->data);
    }

    public function getRMStockRegister() {
        $data = $this->input->post();
        $errorMessage = array();

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $rmStockData = $this->storeReport->getRMStockRegister();
            $tbody="";$i=1;

            $size_arr = array();
            $temp_arr = array();
            foreach($rmStockData as $row):
                if(in_array($row->size, $size_arr)){
                    $temp_arr[$row->size] = array_merge_recursive($temp_arr[$row->size], array('material_grade' => $row->material_grade, 'stock_qty' => $row->stock_qty));
                } else {
                    $size_arr[] = $row->size;
                    $temp_arr[$row->size] = array('material_grade' => $row->material_grade, 'stock_qty' => $row->stock_qty);
                }
            endforeach;

            $materialGrade = $this->materialGrade->getMaterialGrades();

            foreach($temp_arr as $key => $row):
                
                $tbody .= '<tr>';
                $tbody .= '<td>'.$i++.'</td>';
                $tbody .= '<td>'.$key.'</td>';
                foreach($materialGrade as $mgRow):
                    $mGrade = $mgRow->material_grade;
                    if(is_array($row['material_grade'])){
                        if(in_array($mGrade, $row['material_grade'])){
                            $row_id = array_search($mGrade, $row['material_grade']);
                            $tbody .= '<td class="text-center">'.floatval($row['stock_qty'][$row_id]).'</td>';
                        }else {
                            $tbody .= '<td>0</td>';
                        }
                    } else {
                        if($mGrade == $row['material_grade']){
                            $tbody .= '<td>'.floatval($row['stock_qty']).'</td>';
                        }else {
                            $tbody .= '<td>0</td>';
                        }
                    }
                endforeach;
                $tbody.='</tr>';
            endforeach;
            return ['materialGrade' => $materialGrade , 'tbody' => $tbody];
        endif;
    }
    
    public function batchStockHistory($item_id="",$item_type=""){
		$this->data['headData']->pageTitle = "Stock History";
		$this->data['item_type'] = $item_type;
        $this->data['itemData'] = $this->item->getItem(['id'=>$item_id]);
        $this->load->view('reports/store_report/stock_history',$this->data);
    }
	
	public function getBatchStockHistory(){
		$data = $this->input->post();
		
		if(!empty($data['item_type']) && $data['item_type'] == 99) {
            $data['location_id'] = $this->FORGE_STORE->id;
        }else{
            $data['location_not_in'] = $this->FORGE_STORE->id;
        }
		
		$data['stock_required'] = 1;
		$data['group_by'] = "stock_transaction.location_id,stock_transaction.batch_no,stock_transaction.heat_no";
		$data['supplier'] = 1;
        $stockHistory = $this->itemStock->getItemStockBatchWise($data);
		
        $i=1; $tbody =""; 
        foreach($stockHistory as $row):  
            $qcTagParam = ['item_id' => $row->item_id,'batch_no' => $row->batch_no,'heat_no'=> $row->heat_no,'location_id' => $row->location_id,'location_name' => '['.$row->store_name.']  '.$row->location ,'qty'=>$row->qty];
            $qcTagUrl = encodeURL($qcTagParam);
            $iirTagPrint = '<a href="'.base_url('pos/printMaterialTag/'.$qcTagUrl).'" type="button" class="btn btn-primary" datatip="QC Stock Tag" flow="down" target="_blank"><i class="fas fa-print"></i></a>';   
            
            /*
            $stfParam = "{'postData':{'location_id':".$row->location_id.",'item_id':".$row->item_id.",'stock_qty':".floatVal($row->qty).",'batch_no':'".$row->batch_no."','heat_no':'".$row->heat_no."'},'modal_id' : 'modal-md', 'form_id' : 'stockTransfer', 'title' : 'Stock Transfer','call_function':'stockTransfer','fnsave' : 'saveStockTransfer'}";
            $stfBtn = '<a class="btn btn-success btn-edit" href="javascript:void(0)" datatip="Stock Transfer" flow="down" onclick="modalAction('.$stfParam.');"><i class="fa fa-exchange"></i></a>';
            
            $actionBtn = ($row->item_type == 3) ? getActionButton($stfBtn) : '';  
            <td>' .$actionBtn. '</td>
            */
            
            $tbody .= '<tr>
                <td>' . $i++ . '</td>
                <td>'.$row->store_name.' - '.$row->location.'</td>
                <td>'.$row->heat_no.'<br>'.$row->party_name.'</td>
                <td>'.$row->batch_no.'</td>
                <td>'.$row->qty.'</td>
                <td>'.$iirTagPrint.'</td>
            </tr>';
        endforeach;

        $this->printJson(['status'=>1,'tbody'=>$tbody]);
    }
    
    public function stockTransfer(){
       $this->data['dataRow'] =  $this->input->post();
       $this->data['locationData'] = $this->itemStock->getStockLocationList(['store_type'=>'0,15','final_location'=>1]);
       $this->load->view('reports/store_report/stock_transfer_form',$this->data);
    }
    
    public function saveStockTransfer(){
        $data = $this->input->post();
        $errorMessage = array();

        if(empty($data['to_location_id']))
            $errorMessage['to_location_id'] = "Store Location is required.";
        if(empty($data['transfer_qty']))
            $errorMessage['transfer_qty'] = "Qty is required.";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            $postdata = ['location_id'=>$data['from_location_id'],'batch_no'=>$data['batch_no'],'heat_no'=>$data['heat_no'],'stock_required'=>1,'single_row'=>1];
            $checkStock = $this->itemStock->getItemStockBatchWise($postdata);
            if($checkStock->qty < $data['transfer_qty']):
                $this->printJson(['status'=>2,'message'=>'Stock not avalible.','stock_qty'=>$checkStock->qty]);
            else:
                $data['created_by'] = $this->session->userdata('loginId');
                $this->printJson($this->itemStock->saveStockTransfer($data));
            endif;
        endif;
    }
    
    public function locationWiseStock(){
        $this->data['pageHeader'] = 'LOCATION WISE STOCK';
        $this->data['locationList'] = $this->storeLocation->getStoreLocationList(['store_type'=>'0,15,6,4,5','final_location'=>1]);
        $this->data['headData']->pageUrl = "reports/storeReport/locationWiseStock";
        $this->load->view("reports/store_report/location_wise_stock",$this->data);
    }

    public function getLocationWiseStock(){
        $data = $this->input->post();
        $data['stock_required'] = 1;
        $data['group_by'] = "stock_transaction.location_id,stock_transaction.batch_no,stock_transaction.heat_no";
        $result = $this->storeReport->getLocationWiseStock($data);
        $tbody = '';$tfoot='';$qty=0;$i=1;
        foreach($result as $row):
            $tbody .= '<tr>
                <td class="text-center">'.$i++.'</td>
                <td class="text-left">'.$row->item_name.'</td>
                <td>'.$row->store_name.' - '.$row->location.'</td>
                <td>'.$row->heat_no.'<br>'.$row->party_name.'</td>
                <td>'.$row->batch_no.'</td>
                <td>'.floatVal($row->qty).'</td>
            </tr>';
            $qty+=$row->qty;
        endforeach;
        $tfoot ='<tr class="thead-dark">
                    <th class="text-right" colspan="5">Total Qty</th>
                    <th>'.$qty.'</th>
                </tr>';

        $this->printJson(['status'=>1,'tbody'=>$tbody,'tfoot'=>$tfoot]);
    }
    
    public function getItemType(){
        $data = $this->input->post();
        $result = $this->item->getItemList(['item_type'=>$data['item_type']]);
        $tbody = '<option value="">Select Item</option>';
        foreach($result as $row):
            $tbody .= '<option value='.$row->id.'>'.$row->item_name.'</option>';
        endforeach;

        $this->printJson(['status'=>1,'tbody'=>$tbody]);
    }
}
?>