<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Items extends MY_Controller{
    private $indexPage = "item_master/index";
    private $form = "item_master/form";
    private $itemKitForm = "item_master/item_kit";
    private $productProcessForm = "item_master/product_process";
    private $excel_upload_form = "item_master/excel_upload_form";
	private $itemRevision = "item_master/item_revision";
    private $inspectionForm = "item_master/inspection";
    private $rmInspectionForm = "item_master/rm_inspection";
    private $productDetails ="item_master/product_details";
    private $serviceForm = "item_master/service_form";

    public function __construct(){
		parent::__construct();
		$this->data['headData']->pageTitle = "Item Master";
		$this->data['headData']->controller = "items";        
	}

    public function list($item_type = 0){
        $this->data['headData']->pageUrl = "items/list/".$item_type;
        $this->data['item_type'] = $item_type;
        $headerName = str_replace(" ","_",strtolower($this->itemTypes[$item_type]));
        $this->data['tableHeader'] = getMasterDtHeader($headerName);
        $this->load->view($this->indexPage,$this->data);
    }

    public function getDTRows($item_type = 0){
        $data = $this->input->post();$data['item_type'] = $item_type;
        $result = $this->item->getDTRows($data);
        $sendData = array();$i=($data['start']+1);
        foreach($result['data'] as $row):
            $row->sr_no = $i++;
            $row->item_type_text = $this->itemTypes[$row->item_type];
            $sendData[] = getProductData($row);
        endforeach;
        $result['data'] = $sendData;
        $this->printJson($result);
    }

    public function addItem(){
        $data = $this->input->post();
        $this->data['item_type'] = $data['item_type'];
        $this->data['item_code'] = $this->getItemCode($data['item_type']);
        $this->data['unitData'] = $this->item->itemUnits();
        $this->data['categoryList'] = $this->itemCategory->getCategoryList(['category_type'=>$data['item_type'],'final_category'=>1]);
        $this->data['hsnData'] = $this->hsnModel->getHSNList();
        $this->data['customFieldList'] = $this->customField->getCustomFieldList(); 
        $this->data['customOptionList'] = $this->customOption->getMasterList(); 
        $this->data['materialGrade'] = $this->materialGrade->getMaterialGrades();
        $this->data['supplierList'] = $this->party->getPartyList(['party_category'=>2]); // 06-08-2024
        
        if($data['item_type'] == 8){
            $this->load->view($this->serviceForm,$this->data);
        }else{
            $this->load->view($this->form,$this->data);
        }
    }
    
    /* Auto Generate Item Code */
    public function getItemCode($item_type=""){
        $itemType = (!empty($item_type))?$item_type:$this->input->post('item_type');
        $code = $this->item->getItemCode($itemType);
        $prefix = "";
        if($itemType == 1):
            $prefix = "FG";
        elseif($itemType == 2):
            $prefix = "CS";
        elseif($itemType == 3):
            $prefix = "RM";
        elseif($itemType == 8):
            $prefix = "SE";
        endif;

        $item_code = $prefix.sprintf("%04d",$code);

        if(!empty($item_type)):
            return $item_code;
        else:
            $this->printJson(['status'=>1,'item_code'=>$item_code]);
        endif;
    }

    public function save(){
        $data = $this->input->post();
        $errorMessage = array();
        
        if(empty($data['item_code']))
            $errorMessage['item_code'] = "Item Code is required.";

        if($data['item_type'] == 3){
            if(empty($data['size']) AND empty($data['shape']) AND empty($data['bartype']))
                $errorMessage['item_name'] = "Item Name is required.";
        }else{
            if(empty($data['item_name']))
                $errorMessage['item_name'] = "Item Name is required.";
        }
        if(empty($data['unit_id']))
            $errorMessage['unit_id'] = "Unit is required.";
        if(empty($data['category_id']))
            $errorMessage['category_id'] = "Category is required.";

        if($data['item_type'] == 1):
            if(!empty($_FILES['item_image']['name'])):
                $attachment = "";
                $this->load->library('upload');
                
                $_FILES['userfile']['name']     = $_FILES['item_image']['name'];
                $_FILES['userfile']['type']     = $_FILES['item_image']['type'];
                $_FILES['userfile']['tmp_name'] = $_FILES['item_image']['tmp_name'];
                $_FILES['userfile']['error']    = $_FILES['item_image']['error'];
                $_FILES['userfile']['size']     = $_FILES['item_image']['size'];

                $imagePath = realpath(APPPATH . '../assets/uploads/item_image/');

                $fileName = preg_replace('/[^A-Za-z0-9]+/', '_', strtolower($_FILES['item_image']['name']));
                $config = ['file_name' => $fileName, 'allowed_types' => 'jpg|jpeg|png|gif|JPG|JPEG|PNG', 'max_size' => 10240, 'overwrite' => FALSE, 'upload_path' => $imagePath];

                $this->upload->initialize($config);

                if(!$this->upload->do_upload()):
                    $errorMessage['item_image'] .= $fileName . " => " . $this->upload->display_errors();
                else:
                    $uploadData = $this->upload->data();
                    $attachment = $uploadData['file_name'];
                endif;

                if(!empty($errorMessage['item_image'])):
                    if (file_exists($imagePath . '/' . $attachment)) : unlink($imagePath . '/' . $attachment); endif;
                endif;

                $data['item_image'] = $attachment;
            endif;
        endif;
            
        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            if($data['item_type'] == 3):
				$data['item_name'] = $data['size'].' '.$data['shape'].' '.$data['bartype'];
			endif;
            $this->printJson($this->item->save($data));
        endif;
    }

    public function edit(){
        $data = $this->input->post();
        $this->data['dataRow'] = $itemDetail = $this->item->getItem($data);
        $this->data['item_type'] = $itemDetail->item_type;
        $this->data['unitData'] = $this->item->itemUnits();
        $this->data['categoryList'] = $this->itemCategory->getCategoryList(['category_type'=>$itemDetail->item_type,'final_category'=>1]);
        $this->data['hsnData'] = $this->hsnModel->getHSNList();
        $this->data['customFieldList'] = $this->customField->getCustomFieldList(); 
        $this->data['customOptionList'] = $this->customOption->getMasterList();  
        $this->data['customData'] = $this->item->getItemUdfData(['item_id'=>$data['id']]);
        $this->data['materialGrade'] = $this->materialGrade->getMaterialGrades();
        $this->data['supplierList'] = $this->party->getPartyList(['party_category'=>2]); // 06-08-2024

        if($itemDetail->item_type == 8){
            $this->load->view($this->serviceForm,$this->data);
        }else{
            $this->load->view($this->form,$this->data);
        }
    }

    public function delete(){
        $id = $this->input->post('id');
        if(empty($id)):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->item->delete($id));
        endif;
    }

    public function getItemList(){
        $data = $this->input->post();
        $itemList = $this->item->getItemList($data);
        $this->printJson(['status'=>1,'data'=>['itemList'=>$itemList]]);
    }

    public function getItemDetails(){
        $data = $this->input->post();
        $itemDetail = $this->item->getItem($data);
        $this->printJson(['status'=>1,'data'=>['itemDetail'=>$itemDetail]]);
    }
    
    /* Product Excel Upload */ 
    /* Created By :- Avruti @12-04-2024 */
    public function addProductExcel(){
        $this->load->view($this->excel_upload_form,$this->data);
    }

    /* Created By :- Avruti @12-04-2024 */
    public function saveProductExcel(){
        $data = $this->input->post();
        $errorMessage = array();

        if(empty($data['itemData']))
            $errorMessage['itemData'] = "Enter Item detail";

        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
            foreach($data['itemData'] as $row):
                $catData = $this->itemCategory->getCategory(['category_name'=>$row['category_id']]);
                $unitData = $this->item->getUnitNameWiseId(['unit_name'=>$row['unit_id']]);  

                $row['category_id'] = $catData->id;
                $row['unit_id'] = $unitData->id;
                $row['item_type'] = 1;
                $row['packing_standard'] = (!empty($row['packing_standard'] > 0)) ? $row['packing_standard'] : 1 ; 

                $result = $this->item->save($row);
            endforeach;
           
            $this->printJson(['status'=>1,'message'=>'Item saved successfully.']);
        endif;
    }

    public function checkItemDuplicate(){
        $data = $this->input->post();
        $customWhere = "item_name = '".$data['item_name']."' ";
        $itemData = $this->item->getItem(['customWhere'=>$customWhere]);
        $this->printJson(['status'=>1,'item_id'=>(!empty($itemData->id)?$itemData->id:"")]);
    }
	
	/* Start Item Revision Created By Rashmi @24-04-2024 */
    public function addItemRevision(){
        $id = $this->input->post('id'); 
        $this->data['item_id'] = $id; 
        $this->load->view($this->itemRevision,$this->data);
    }

    public function saveItemRevision(){ 
        $data = $this->input->post(); 
		$errorMessage = array();
		
        if(empty($data['rev_no']))
            $errorMessage['rev_no'] = "Revision No. is required.";     
        if(empty($data['rev_date']))
            $errorMessage['rev_date'] = "Revision Date is required.";

		if(!empty($errorMessage)):
			$this->printJson(['status'=>0,'message'=>$errorMessage]);
		else:
            if(isset($_FILES['drawing_file']['name'] ) && $_FILES['drawing_file']['name'] != null || !empty($_FILES['drawing_file']['name'])):
                $this->load->library('upload');
                $_FILES['userfile']['name']     = $_FILES['drawing_file']['name'];
                $_FILES['userfile']['type']     = $_FILES['drawing_file']['type'];
                $_FILES['userfile']['tmp_name'] = $_FILES['drawing_file']['tmp_name'];
                $_FILES['userfile']['error']    = $_FILES['drawing_file']['error'];
                $_FILES['userfile']['size']     = $_FILES['drawing_file']['size'];
                
                $imagePath = realpath(APPPATH . '../assets/uploads/items/drawings');
                $config = ['file_name' => time()."_order_item_".$_FILES['userfile']['name'],'allowed_types' => '*','max_size' => 10240,'overwrite' => FALSE, 'upload_path'  =>$imagePath];

                $this->upload->initialize($config);
                if (!$this->upload->do_upload()):
                    $errorMessage['drawing_file'] = $this->upload->display_errors();
                    $this->printJson(["status"=>0,"message"=>$errorMessage]);
                else:
                    $uploadData = $this->upload->data();
                    $data['drawing_file'] = $uploadData['file_name'];
                endif;
            else:
                unset($data['drawing_file']);
            endif;

			$this->printJson($this->item->saveItemRevision($data));
		endif;
    }
    
    public function itemRevisionHtml(){  
        $data = $this->input->post();
        $revisionData = $this->item->getItemRevision(['item_id'=>$data['item_id']]);
		$i=1; $tbody='';
        
		if(!empty($revisionData)):
			foreach($revisionData as $row):
                $deleteParam = "{'postData':{'id' : ".$row->id."},'res_function':'itemRevisionHtml','fndelete':'deleteItemRevision'}";
                if($row->is_active == 1):
                    $activeParam = "{'postData':{'id' : ".$row->id.", 'is_active' : 0,'item_id' : ".$row->item_id."},'fnsave':'activeInactive','res_function':'itemRevisionHtml','message':'Are you sure want to In-Active this Item Revision?'}";
                    $activeButton = '<button type="button" class="btn btn-sm btn-youtube permission-modify" href="javascript:void(0)" datatip="In-Active" flow="down" onclick="confirmStore('.$activeParam.');"><i class="fa fa-ban"></i></button>';    
                else:
                    $activeParam = "{'postData':{'id' : ".$row->id.", 'is_active' : 1,'item_id' : ".$row->item_id."},'fnsave':'activeInactive','res_function':'itemRevisionHtml','message':'Are you sure want to Active this Item Revision?'}";
                    $activeButton = '<button type="button" class="btn btn-sm btn-success permission-remove" href="javascript:void(0)" datatip="Active" flow="down" onclick="confirmStore('.$activeParam.');"><i class="fa fa-check"></i></button>'; 
                endif;
                $drawingFile = (!empty($row->drawing_file)?'&nbsp;<a class="btn btn-sm btn-primary" href="'.base_url('assets/uploads/items/drawings/'.$row->drawing_file).'" target="_blank"><i class="fa fa-download"></i></a>':"");
				$tbody.= '<tr>
						<td>'.$i++.'</td>
						<td>'.$row->rev_no.'</td>
						<td>'.formatDate($row->rev_date).'</td>
						<td class="text-center">
							<button type="button" onclick="trash('.$deleteParam.');" class="btn btn-sm btn-outline-danger waves-effect waves-light permission-remove"><i class="mdi mdi-trash-can-outline"></i></button>
                            '.$activeButton.$drawingFile.'
						</td>
					</tr>';
			endforeach;
		endif;
        $this->printJson(['status'=>1,'tbodyData'=>$tbody]);
	}
	
	public function deleteItemRevision(){ 
        $data=$this->input->post();
        if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
			$this->printJson($this->item->deleteItemRevision($data['id']));
		endif;
    }
    
    public function activeInactive(){
        $postData = $this->input->post();
        if(empty($postData['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->item->activeInactive($postData));
        endif;
    }
    // End Item Revision

    /*  Inspection  Created By Rashmi @25/04/2024*/
    public function addInspection(){
        $data = $this->input->post();
        $this->data['item_id'] = $data['id'];
        $this->data['item_type'] = $data['item_type'];
        $this->data['processData'] = $this->item->getItemProcess($data['id']);        
        $this->data['revisionData'] = $this->item->getItemRevision(['item_id'=>$data['id']]);
        $this->load->view($this->inspectionForm,$this->data);
    }
        
    public function saveInspection(){
        $data = $this->input->post(); 
        $errorMessage = array();
        if(empty($data['parameter'])){
            $errorMessage['parameter'] = "Parameter is required.";
        }   
        if(empty($data['specification'])){
            $errorMessage['specification'] = "Specification is required.";
        }
        if($data['item_type'] == 1){
            if(!isset($data['rev_no'])){ 
                $errorMessage['rev_no'] = "Revision is required."; 
            }
        }
        if(empty($data['control_method'])){
            $errorMessage['control_method'] = "Control Method is required."; 
        }
        if(!empty($errorMessage)):
            $this->printJson(['status'=>0,'message'=>$errorMessage]);
        else:
           $data['control_method'] = ($data['item_type'] == 1) ? implode(",", $data['control_method']) : $data['control_method'];
            unset($data['item_type']);
            $this->printJson($this->item->saveInspection($data));
        endif;
    }

    public function inspectionHtml(){
        $data = $this->input->post();
        $paramData = $this->item->getInspectionParam($data['item_id']); 
        $tbodyData="";$i=1; 
        if(!empty($paramData)):
            $i=1;
            foreach($paramData as $row):
                $deleteParam = "{'postData':{'id' : ".$row->id."},'res_function':'inspectionHtml','fndelete':'deleteInspection'}";
                $editBtn = "<button type='button' onclick='editInspParam(".json_encode($row).",this);' class='btn btn-sm btn-outline-info waves-effect waves-light btn-sm permission-modify' datatip='Edit'><i class='far fa-edit'></i></button>";
                $tbodyData.= '<tr>
                                <td>'.$i++.'</td>
                                '.(($data['item_type'] != 3) ? '<td>'.$row->rev_no.'</td>' : '').'
                                '.(($data['item_type'] != 3) ? '<td>'.$row->process_name.'</td>' : '').'
                                <td>'.(($row->param_type == 1)?$row->parameter:'').'</td>
                                '.(($data['item_type'] != 3)? '<td>'.(($row->param_type == 2)?$row->parameter:'').'</td>': '').'
                                <td>'.$row->machine_tool.'</td>
                                <td>'.$row->specification.'</td>
                                <td>'.(($row->min == 0.00)?'-':$row->min).'</td>
                                <td>'.(($row->max == 0.00)?'-':$row->max).'</td>
                                <td class="text-center"><img src="'.((!empty($row->char_class))?base_url("/assets/images/symbols/".$row->char_class.'.png'):'').'" style="width:20px;"></td>
                                <td>'.$row->instrument.'</td>
                                <td>'.$row->size.'</td>
                                <td>'.$row->frequency.'</td>
                                <td>'.$row->freq_unit.'</td>
                                <td>'.$row->reaction_plan.'</td>
                                <td>'.$row->control_method.'</td>
                                <td class="text-center">
                                 '.$editBtn.'
                                    <button type="button" onclick="trash('.$deleteParam.');" class="btn btn-sm btn-outline-danger waves-effect waves-light permission-remove"><i class="mdi mdi-trash-can-outline"></i></button>
                                </td>
                        </tr>';
            endforeach;
        else:
            $tbodyData.= '<tr><td colspan="17" style="text-align:center;">No Data Found</td></tr>';
        endif;

        $this->printJson(['status'=>1,'tbodyData'=>$tbodyData]);
    }

    public function deleteInspection(){
        $data=$this->input->post();
        if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
            $this->printJson($this->item->deleteInspection($data['id']));
        endif;
    }

    public function createInspectionExcel($item_id,$item_type){
        $processData = $this->item->getProductProcessList(['item_id'=>$item_id]);
        $spreadsheet = new Spreadsheet();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
  
        $html = "<tr>";
        if ($item_type == 1) {
            $html .= "<th>rev_no</th>";
            $html .= "<th>param_type</th>";
        }
        $html .= "<th>parameter</th>
            <th>machine_tool</th>
            <th>specification</th>
            <th>min</th>
            <th>max</th>
            <th>char_class</th>
            <th>instrument</th>
            <th>size</th>
            <th>frequency</th>
            <th>freq_unit</th>
            <th>Control_Method</th>
            <th>reaction_plan</th>
        </tr>";

        $exlData = '<table>' . $html . '</table>'; 
        $spreadsheet = $reader->loadFromString($exlData);
        $excelSheet = $spreadsheet->getActiveSheet();
        $excelSheet = $excelSheet->setTitle('Inspection');
        
        $hcol = $excelSheet->getHighestColumn();
        $hrow = $excelSheet->getHighestRow();
        $packFullRange = 'A1:' . $hcol . $hrow;
        foreach (range('A', $hcol) as $col) :
            $excelSheet->getColumnDimension($col)->setAutoSize(true);
        endforeach;
        $colOffset = ($item_type == 3) ? 0 : 2; 

        for ($i = 1; $i <= 5; $i++) {
            if ($item_type == 1) {
            /*** Process Code Drop down */
                $objValidation2 = $excelSheet->getCell('B' . $i)->getDataValidation();
                $objValidation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $objValidation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $objValidation2->setAllowBlank(false);
                $objValidation2->setShowInputMessage(true);
                $objValidation2->setShowDropDown(true);
                $objValidation2->setPromptTitle('Pick from list');
                $objValidation2->setPrompt('Please pick a value from the drop-down list.');
                $objValidation2->setErrorTitle('Input error');
                $objValidation2->setError('Value is not in list');
                $objValidation2->setFormula1('"Product,Process"');
                $objValidation2->setShowDropDown(true);
            }
            if ($item_type == 1) {
                $objValidation2 = $excelSheet->getCell('H' . $i)->getDataValidation();
            }else{
                $objValidation2 = $excelSheet->getCell('F' . $i)->getDataValidation();
            }
                $objValidation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $objValidation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $objValidation2->setAllowBlank(false);
                $objValidation2->setShowInputMessage(true);
                $objValidation2->setShowDropDown(true);
                $objValidation2->setPromptTitle('Pick from list');
                $objValidation2->setPrompt('Please pick a value from the drop-down list.');
                $objValidation2->setErrorTitle('Input error');
                $objValidation2->setError('Value is not in list');
                $objValidation2->setFormula1('"'.implode(',',array_keys($this->classArray)).'"');
                $objValidation2->setShowDropDown(true);

            if ($item_type == 1) {
                $objValidation2 = $excelSheet->getCell('L' . $i)->getDataValidation();
            }else{
                $objValidation2 = $excelSheet->getCell('J' . $i)->getDataValidation();
            }
                $objValidation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $objValidation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $objValidation2->setAllowBlank(false);
                $objValidation2->setShowInputMessage(true);
                $objValidation2->setShowDropDown(true);
                $objValidation2->setPromptTitle('Pick from list');
                $objValidation2->setPrompt('Please pick a value from the drop-down list.');
                $objValidation2->setErrorTitle('Input error');
                $objValidation2->setError('Value is not in list');
                $objValidation2->setFormula1('"Hrs,Lot"');
                $objValidation2->setShowDropDown(true);

                $objValidation2 = $excelSheet->getCell('M' . $i)->getDataValidation();
                $objValidation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $objValidation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $objValidation2->setAllowBlank(false);
                $objValidation2->setShowInputMessage(true);
                $objValidation2->setShowDropDown(true);
                $objValidation2->setPromptTitle('Pick from list');
                $objValidation2->setPrompt('Please pick a value from the drop-down list.');
                $objValidation2->setErrorTitle('Input error');
                $objValidation2->setError('Value is not in list');
                $objValidation2->setFormula1('"IIR,IPR,FIR,SAR"');
                $objValidation2->setShowDropDown(true);
        }

        if($item_type == 1){
        $i = 1;
        if(!empty($processData)):
            foreach ($processData as $row) :
            
                $pdfData = '<table>' . $html . '</table>';

                $reader->setSheetIndex($i);

                $spreadsheet = $reader->loadFromString($pdfData, $spreadsheet);

                $row->process_name = trim(preg_replace('/[^A-Za-z0-9\-]/', ' ', $row->process_name));
                $row->process_name = substr(trim(str_replace('-', ' ', $row->process_name)),0,30);
                $spreadsheet->getSheet($i)->setTitle($row->process_name);
                $excelSheet = $spreadsheet->getSheet($i);
                $hcol = $excelSheet->getHighestColumn();
                $hrow = $excelSheet->getHighestRow();
                $packFullRange = 'A1:' . $hcol . $hrow;
                foreach (range('A', $hcol) as $col) :
                    $excelSheet->getColumnDimension($col)->setAutoSize(true);
                endforeach;
                for ($j = 1; $j <= 5; $j++) {
                    /*** Process Code Drop down */
                    $objValidation2 = $excelSheet->getCell('B' . $j)->getDataValidation();
                    $objValidation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $objValidation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $objValidation2->setAllowBlank(false);
                    $objValidation2->setShowInputMessage(true);
                    $objValidation2->setShowDropDown(true);
                    $objValidation2->setPromptTitle('Pick from list');
                    $objValidation2->setPrompt('Please pick a value from the drop-down list.');
                    $objValidation2->setErrorTitle('Input error');
                    $objValidation2->setError('Value is not in list');
                    $objValidation2->setFormula1('"Product,Process"');
                    $objValidation2->setShowDropDown(true);

                    $objValidation2 = $excelSheet->getCell('H' . $j)->getDataValidation();
                    $objValidation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $objValidation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $objValidation2->setAllowBlank(false);
                    $objValidation2->setShowInputMessage(true);
                    $objValidation2->setShowDropDown(true);
                    $objValidation2->setPromptTitle('Pick from list');
                    $objValidation2->setPrompt('Please pick a value from the drop-down list.');
                    $objValidation2->setErrorTitle('Input error');
                    $objValidation2->setError('Value is not in list');
                    $objValidation2->setFormula1('"'.implode(',',array_keys($this->classArray)).'"');
                    $objValidation2->setShowDropDown(true);

                    $objValidation2 = $excelSheet->getCell('L' . $j)->getDataValidation();
                    $objValidation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $objValidation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $objValidation2->setAllowBlank(false);
                    $objValidation2->setShowInputMessage(true);
                    $objValidation2->setShowDropDown(true);
                    $objValidation2->setPromptTitle('Pick from list');
                    $objValidation2->setPrompt('Please pick a value from the drop-down list.');
                    $objValidation2->setErrorTitle('Input error');
                    $objValidation2->setError('Value is not in list');
                    $objValidation2->setFormula1('"Hrs,Lot"');
                    $objValidation2->setShowDropDown(true);

                    $objValidation2 = $excelSheet->getCell('M' . $j)->getDataValidation();
                    $objValidation2->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $objValidation2->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $objValidation2->setAllowBlank(false);
                    $objValidation2->setShowInputMessage(true);
                    $objValidation2->setShowDropDown(true);
                    $objValidation2->setPromptTitle('Pick from list');
                    $objValidation2->setPrompt('Please pick a value from the drop-down list.');
                    $objValidation2->setErrorTitle('Input error');
                    $objValidation2->setError('Value is not in list');
                    $objValidation2->setFormula1('"IIR,IPR,FIR,SAR"');
                    $objValidation2->setShowDropDown(true);

                }
                $i++;
            endforeach;
            
        endif;
    }
        $fileDirectory = realpath(APPPATH . '../assets/uploads/inspection');
        $fileName = '/inspection' . time() . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($fileDirectory . $fileName);
        header("Content-Type: application/vnd.ms-excel");
        redirect(base_url('assets/uploads/inspection') . $fileName);
    }

    public function importExcel(){
        $postData = $this->input->post(); 
        $insp_excel = '';
        if (isset($_FILES['insp_excel']['name']) || !empty($_FILES['insp_excel']['name'])) :
            $this->load->library('upload');
            $_FILES['userfile']['name']     = $_FILES['insp_excel']['name'];
            $_FILES['userfile']['type']     = $_FILES['insp_excel']['type'];
            $_FILES['userfile']['tmp_name'] = $_FILES['insp_excel']['tmp_name'];
            $_FILES['userfile']['error']    = $_FILES['insp_excel']['error'];
            $_FILES['userfile']['size']     = $_FILES['insp_excel']['size'];

            $imagePath = realpath(APPPATH . '../assets/uploads/inspection');
            $config = ['file_name' => "inspection_" . $_FILES['userfile']['name'], 'allowed_types' => '*', 'max_size' => 10240, 'overwrite' => FALSE, 'upload_path' => $imagePath];

            $this->upload->initialize($config);
            if (!$this->upload->do_upload()) :
                $errorMessage['insp_excel'] = $this->upload->display_errors();
                $this->printJson(["status" => 0, "message" => $errorMessage]);
            else :
                $uploadData = $this->upload->data();
                $insp_excel = $uploadData['file_name'];
            endif;
            
            if (!empty($insp_excel)) {
                $processData = [];
                $processData = $this->item->getItemProcess($postData['item_id']);
                $prsDt = new stdClass(); 
                $prsDt->process_name = 'Inspection';
                $processData[] = $prsDt;

                $revData = $this->item->getItemRevision(['item_id'=>$postData['item_id']]);
                $revArr = array_column($revData , 'rev_no');

                $row = 0;$paramData=[];
                foreach ($processData as $prs) : 
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($imagePath . '/' . $insp_excel);
                    
                    $prs->process_name = trim(preg_replace('/[^A-Za-z0-9\-]/', ' ', $prs->process_name));
                    $prs->process_name = substr(trim(str_replace('-', ' ', $prs->process_name)),0,30);
                    
					$exl_sheet = $spreadsheet->getSheetByName($prs->process_name);
                    $fileData = (!empty($exl_sheet) ? array($exl_sheet->toArray(null, true, true, true)) : []); 
                    $fieldArray = array();
                    if (!empty($fileData)) {
                        $fieldArray = $fileData[0][1];
                        for ($i = 2; $i <= count($fileData[0]); $i++) {
                            $rowData = array();
                            $c = 'A';
                            foreach ($fileData[0][$i] as $key => $colData) :
								$field_val = strtolower($fieldArray[$c]);
								$rowData[$field_val] = $colData;
								$c++;
                            endforeach;
                            if($postData['item_type'] == 1){
                                if(empty($revArr)){
                                    $this->printJson(['status' => 0, 'message' => 'Revision No. is required.']);
                                }
                                if(isset($rowData['rev_no']) && $rowData['rev_no'] != ''){
                                    if(!in_array($rowData['rev_no'], $revArr)){
                                        $this->printJson(['status' => 0, 'message' => 'Revision No Mismatch...!']);
                                    }
                                }
                            }

                            if(!empty($rowData['parameter'])):

                                $paramData[]=[
                                    'id'=>'',
                                    'process_id'=>(!empty($prs->process_id)?$prs->process_id:''),
                                    'item_id'=>$postData['item_id'],
                                    'rev_no' => ($postData['item_type'] == 1 && isset($rowData['rev_no']) && $rowData['rev_no'] != '' ? $rowData['rev_no'] : NULL),
                                    'param_type' => ($postData['item_type'] == 3 ? 1 : (!empty($rowData['param_type']) && $rowData['param_type'] == 'Product' ? 1 : 2)),
                                    'parameter'=>$rowData['parameter'],
                                    'machine_tool'=>$rowData['machine_tool'],
                                    'specification'=>$rowData['specification'],
                                    'min'=>(!empty($rowData['min']) ? $rowData['min'] : NULL),
                                    'max'=>(!empty($rowData['max']) ? $rowData['max'] : NULL),
                                    'char_class'=>(!empty($rowData['char_class']) ? $rowData['char_class'] : NULL),
                                    'instrument'=>$rowData['instrument'],
                                    'size'=>(!empty($rowData['size']) ? $rowData['size'] : NULL),
                                    'frequency'=>(!empty($rowData['frequency']) ? $rowData['frequency'] : NULL),
                                    'freq_unit'=>(!empty($rowData['freq_unit']) ? $rowData['freq_unit'] : NULL),
                                    'control_method'=>(!empty($rowData['control_method']) ? $rowData['control_method'] : NULL),
									'reaction_plan'=>(!empty($rowData['reaction_plan']) ? $rowData['reaction_plan'] : NULL),
                                    'created_by'=>$this->loginId,
                                    'created_at'=>date("Y-m-d H:i:s"),
                                ]; 
                                $row++;
                            endif;
                        }
                    }
                endforeach;
				
                if(!empty($paramData)){
                    $result = $this->item->saveInspectionParamExcel($paramData);
                    $this->printJson($result);
                }else{
                    $this->printJson(['status' => 0, 'message' => 'Data not found...!']);
                }
                
            } else {
                $this->printJson(['status' => 0, 'message' => 'Data not found...!']);
            }
        else :
            $this->printJson(['status' => 0, 'message' => 'Please Select File!']);
        endif;
    }

    // 06-08-2024
    public function getProductDetails($id){
        $this->data['item_id'] = $id;
        $this->data['productData'] = $this->item->getItem(['id'=>$id]);          
        $this->data['rawMaterial'] = $this->item->getItemList(['item_type'=>'1,3,2']);
        $this->data['process'] = $this->item->getProductProcessList(['item_id'=>$id]);
        $this->data['processDataList'] = $this->process->getProcessList();
        $this->data['productProcess'] = $this->item->getProductProcessForSelect($id);
        $this->load->view($this->productDetails,$this->data);
    }

    /* Product BOM */
    public function groupSearch(){
        $data = $this->input->post();
		$this->printJson($this->item->groupSearch($data));
	}

    public function saveProductKit(){ 
        $data = $this->input->post();
		$errorMessage = array();
		
        if(empty($data['group_name'])){
            $errorMessage['group_name'] = "Group Name is required.";
        }
        if(empty($data['kit_item_id'])){
            $errorMessage['kit_item_id'] = "Item is required.";
        }		
        if(empty($data['kit_item_qty'])){
            $errorMessage['kit_item_qty'] = "Qty. is required.";
        }
		
		if(!empty($errorMessage)):
			$this->printJson(['status'=>0,'message'=>$errorMessage]);
		else:			
			$this->printJson($this->item->saveProductKit($data));
		endif;
    }

    public function productKitHtml(){
        $data = $this->input->post();
        $productKitData = $this->item->getProductKitData(['item_id'=>$data['item_id']]);
		$i=1; $tbody='';
        
		if(!empty($productKitData)):
			foreach($productKitData as $row):
                $deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'Process','res_function':'getProductKitHtml','fndelete':'deleteProductKit'}";
				$tbody.= '<tr class="text-center">
						<td>'.$i++.'</td>
						<td>'.$row->group_name.'</td>
						<td>'.$row->process_name.'</td>
						<td>'.$row->item_name.'</td>
						<td>'.floatval($row->qty).'</td>
						<td>
							<button type="button" onclick="trash('.$deleteParam.');" class="btn btn-sm btn-outline-danger waves-effect waves-light permission-remove"><i class="mdi mdi-trash-can-outline"></i></button>
						</td>
					</tr>';
			endforeach;
        else:
            $tbody = '<tr><td colspan="6" class="text-center">No data found.</td></tr>';
		endif;
        $this->printJson(['status'=>1,'tbodyData'=>$tbody]);
	}
	
	public function deleteProductKit(){ 
        $data=$this->input->post();
        if(empty($data['id'])):
            $this->printJson(['status'=>0,'message'=>'Somthing went wrong...Please try again.']);
        else:
			$this->printJson($this->item->deleteProductKit($data['id']));
		endif;
    }

    /* Product Process */
    public function saveProductProcess(){
        $data = $this->input->post();
        $errorMessage = array();

        if(empty($data['process_id'])){
            $errorMessage['process_id'] = "Process is required.";
        }

        if(!empty($errorMessage)):
            $this->printJson(['status'=>2,'message'=>$errorMessage]);
        else:
            $data['sequence'] = max($data['process_id']);
            $data['process_id'] = implode(",",$data['process_id']); 
            $this->printJson($this->item->saveProductProcess($data));
        endif;
    }

    public function productProcessHtml(){
        $data = $this->input->post();
        $processData = $this->item->getItemProcess($data['item_id']);
        $processDataList = $this->process->getProcessList();

        $tbody = ''; $options = '<option value="">Select Process</option>';
        if (!empty($processData)) :
            $i = 1;            
            foreach ($processData as $row) :
                $tbody .= '<tr id="'.$row->id.'">
                        <td class="text-center">'.$i++.'</td>
                        <td>'.$row->process_name.'</td>
                        <td>'.$row->sequence.'</td>
                      </tr>';
            endforeach;
        else :
            $tbody .= '<tr><td colspan="3" class="text-center">No data found.</td></tr>';
        endif;
        $this->printJson(['status'=>1,'tbodyData'=>$tbody]);
    }

    public function updateProductProcessSequance(){
        $data = $this->input->post();
		$errorMessage = array();		
		if(empty($data['id']))
			$errorMessage['id'] = "Item ID is required.";
		
		if(empty($errorMessage)):
			$this->printJson($this->item->updateProductProcessSequance($data));			
		endif;
    }

    /* Cycle Time */
    public function saveCT(){
        $data = $this->input->post();

        $data['loginId'] = $this->session->userdata('loginId');
        $cycleTimeData = ['id' => $data['id'], 'cycle_time' => $data['cycle_time'], 'finish_wt' => $data['finish_wt'], 'loginId' => $data['loginId']];

        $this->printJson($this->item->saveProductProcessCycleTime($cycleTimeData));
    }

    public function cycleTimeHtml(){
        $data = $this->input->post();
        $processData = $this->item->getItemProcess($data['item_id']);
		$i=1; $tbody='';
        
        if (!empty($processData)) :
            $i = 1;
            $html = "";
            foreach ($processData as $row) :
                $pid = (!empty($row->id)) ? $row->id : "";
                $ct = (!empty($row->cycle_time)) ? $row->cycle_time : "";
                $fgwt = (!empty($row->finish_wt)) ? $row->finish_wt : "";
                $tbody .= '<tr id="' . $row->id . '">
                    <td class="text-center">' . $i++ . '</td>
                    <td>' . $row->process_name . '</td>
                    <td class="text-center">
                        <input type="text" name="cycle_time[]" class="form-control numericOnly" step="1" value="' . $ct . '" />
                        <input type="hidden" name="id[]" value="' . $pid . '" />
                    </td>
                    <td class="text-center">
                        <input type="text" name="finish_wt[]" class="form-control floatOnly" step="1" value="' . $fgwt . '" />
                    </td>                                 
                  </tr>';
            endforeach;
        else :
            $tbody = '<tr><td colspan="4" class="text-center">No Data Found.</td></tr>';
        endif;
        $this->printJson(['status'=>1,'tbodyData'=>$tbody]);
	}

    /* Standards */
    public function saveStandards(){
        $data = $this->input->post();

        foreach($data['id'] as $key=>$value):
            if($_FILES['attachment']['name'][$key] != null || !empty($_FILES['attachment']['name'][$key])):
                $this->load->library('upload');
                $_FILES['userfile']['name']     = $_FILES['attachment']['name'][$key];
                $_FILES['userfile']['type']     = $_FILES['attachment']['type'][$key];
                $_FILES['userfile']['tmp_name'] = $_FILES['attachment']['tmp_name'][$key];
                $_FILES['userfile']['error']    = $_FILES['attachment']['error'][$key];
                $_FILES['userfile']['size']     = $_FILES['attachment']['size'][$key];
                
                $imagePath = realpath(APPPATH . '../assets/uploads/standards/');
                $config = ['file_name' => $data['item_id'][$key].'_'.$data['process_id'][$key].'_'.$_FILES['userfile']['name'],'allowed_types' => '*','max_size' => 10240,'overwrite' => FALSE, 'upload_path'	=>$imagePath];
    
                $this->upload->initialize($config);
                if (!$this->upload->do_upload()):
                    $errorMessage['attachment'][$key] = $this->upload->display_errors();
                    $this->printJson(["status"=>0,"message"=>$errorMessage]);
                else:
                    $uploadData = $this->upload->data();
                    $data['attachment'][$key] = $uploadData['file_name'];
                endif;
            endif;
        endforeach;
        $data['loginId'] = $this->session->userdata('loginId');

        $standardData = ['id' => $data['id'], 'attachment' => (!empty($data['attachment']) ? $data['attachment'] : ''), 'loginId' => $data['loginId']];
        $this->printJson($this->item->saveStandards($standardData));
    }

    public function standardsHtml(){
        $data = $this->input->post();
        $processData = $this->item->getItemProcess($data['item_id']);
		$i=1; $tbody='';
        
        if (!empty($processData)) :
            $i = 1;
            $html = "";
            foreach ($processData as $row) :
                $pid = (!empty($row->id)) ? $row->id : "";

                $tbody .= '<tr id="' . $row->id . '">
                    <td class="text-center">' . $i++ . '</td>
                    <td>' . $row->process_name . '</td>
                    <td class="text-center">
                        <input type="file" name="attachment[]" class="form-control">
                                  
                        <input type="hidden" name="id[]" value="' . $pid . '" />
                        <input type="hidden" name="item_id[]" value="' . (!empty($row->item_id) ? $row->item_id : '') . '" />
                        <input type="hidden" name="process_id[]" value="' . (!empty($row->process_id) ? $row->process_id : '') . '" />
                    </td>   
                    <td>';
                    
                        if(!empty($row->attachment)){                                                
                            $tbody .= '<a href="'.base_url('assets/uploads/standards/'.$row->attachment).'" target="_blank"><i class="fa fa-arrow-down"></i></a>';
                        }
                        
                    $tbody .= '</td>
                  </tr>';
            endforeach;
        else :
            $tbody = '<tr><td colspan="3" class="text-center">No Data Found.</td></tr>';
        endif;
        $this->printJson(['status'=>1,'tbodyData'=>$tbody]);
	}
}
?>