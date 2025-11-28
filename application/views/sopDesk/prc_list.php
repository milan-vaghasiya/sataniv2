<?php
    $prcList = '';
    $partyName = (!empty($row->party_name) ? '<p class="fs-13"><i class="fas fa-user"></i> '.$row->party_name.'</p>' : '');
    foreach($prcData as $row){
		$btn = "";
		if($row->status == 1 ){
			$startParam = "{'postData':{'id' : ".$row->id."},'message' : 'Are you sure you want to start PRC ? once you start you can not edit or delete','fnsave':'startPRC','res_function':'getPrcResponse'}";
			$btn .= ' <a class="dropdown-item" href="javascript:void(0)" datatip="Start" flow="down" onclick="confirmSOPStore('.$startParam.')"><i class="fas fa-stop-circle text-muted font-10"></i> Start Task</a>';
			if($row->prc_type != 3){
				$editParam = "{'postData':{'id' : ".$row->id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'editPrc', 'title' : 'Update PRC', 'fnsave' : 'savePRC', 'js_store_fn' : 'storeSop'}";
				$btn .= ' <a class="dropdown-item" href="javascript:void(0)" datatip="Edit" flow="down" onclick="modalAction('.$editParam.')"><i class="mdi mdi-square-edit-outline text-muted"></i> Edit</a>';

				$deleteParam = "{'postData':{'id' : ".$row->id."},'message' : 'PRC','res_function':'getPrcResponse'}";
				$btn .= ' <a class="dropdown-item" href="javascript:void(0)" datatip="Delete" flow="down" onclick="trashSop('.$deleteParam.')"><i class="mdi mdi-trash-can-outline text-muted"></i> Delete</a>';
		
			}
		}
		if($status == 2){
			if($row->mfg_type == 'Forging'){
				$updateQtyParam = "{'postData':{'id' : ".$row->id."}, 'modal_id' : 'bs-right-md-modal', 'form_id' : 'updatePrcQty', 'title' : 'Update PRC Qty [".$row->prc_number."] ', 'call_function' : 'updatePrcQty', 'button' : 'close'}";
				$btn .= '<a class="dropdown-item" href="javascript:void(0)" datatip="Update PRC Qty." flow="down" onclick="modalAction(' . $updateQtyParam . ');"><i class="fas fa-stop-circle text-muted font-10"></i> Update PRC Qty.</a>';
			}
		    
			$holdParam = "{'postData':{'id' : ".$row->id.", 'status' : 4},'message' : 'Are you sure want to Hold this PRC ?', 'fnsave' : 'changePrcStatus','res_function':'getPrcResponse'}";
			$btn .= ' <a class="dropdown-item" href="javascript:void(0)" datatip="Hold" flow="down" onclick="confirmSOPStore('.$holdParam.')"><i class="fas fa-stop-circle text-muted font-10"></i> Hold</a>';
			
			$shortParam = "{'postData':{'id' : ".$row->id.", 'status' : 5},'message' : 'Are you sure want to Short Close this PRC ?', 'fnsave' : 'changePrcStatus','res_function':'getPrcResponse'}";
			$btn .= ' <a class="dropdown-item" href="javascript:void(0)" datatip="Short Close" flow="down" onclick="confirmSOPStore('.$shortParam.')"><i class="fas fa-stop-circle text-muted font-10"></i> Short Close</a>';
		}
		elseif($row->status == 4){
			$restartParam = "{'postData':{'id' : ".$row->id.", 'status' : 2},'message' : 'Are you sure want to Restart this PRC ?', 'fnsave' : 'changePrcStatus','res_function':'getPrcResponse'}";
			$btn .= ' <a class="dropdown-item" href="javascript:void(0)" datatip="Restart" flow="down" onclick="confirmSOPStore('.$restartParam.')"><i class="fas fa-stop-circle text-muted font-10"></i> Restart</a>';
		}
		// $mtParam = "{'postData':{'id' : ".$row->id.",'item_id':".$row->item_id."},'modal_id' : 'bs-right-lg-modal', 'form_id' : 'prcMaterial', 'title' : 'Required Material For PRC', 'fnsave' : 'savePrcMaterial', 'js_store_fn' : 'storeSop','call_function':'requiredMaterial'}";
		// $btn .= ' <a class="dropdown-item" href="javascript:void(0)" datatip="Start" flow="down" onclick="modalAction('.$mtParam.')"><i class="fas fa-stop-circle text-muted font-10"></i> Required Material</a>';

    	$prcList .='<div href="#" class="media grid_item">
                		<div class="media-body">
                			<div class="d-inline-block">
                				<h6><a href="javascript:void(0)" type="button" class="text-primary prcNumber" data-id="'.$row->id.'" >#'.$row->prc_number.'</a></h6>
                				<p class="text-muted"><i class="mdi mdi-clock"></i> '.$row->prc_date.' | '.$row->mfg_type.'</p>
                				<p class="fs-13"><i class="fa fa-bullseye"></i> '.$row->item_code.' '.$row->item_name.'</p>
                				'.$partyName.'
                			</div>
                			<div></div>
                		</div>
                		<div class="media-right">
                			<a class="dropdown-toggle lead-action" data-bs-toggle="dropdown" href="#" role="button"><i class="mdi mdi-chevron-down fs-3"></i></a>
                			<div class="dropdown-menu">
                			   '.$btn.'
                			</div><br>
                			<p class="text-danger"> '.floatval($row->prc_qty).' <small class="">'.$row->uom.'</small></p>
                		</div>
                	</div>';
    }
    echo $prcList;
?>