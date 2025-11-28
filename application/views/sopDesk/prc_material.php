<?php
    $prcMaterial = '<div class="text-center">
				        <img src="'.base_url('assets/images/background/dnf_3.png').'" style="width:50%;">
					    <div class="text-center text-muted font-16 fw-bold">Pleasae click any <strong>PRC</strong> to see Data</div>
				    </div>';
	if(!empty($prcMaterialData))
	{
        $prcMaterial = "";
        $bomGrup = [];
        foreach($prcMaterialData as $row){
            $row->used_material = ($prcData->mfg_type == 'Forging')?$row->used_material:$production_qty;
            if(!isset($bomGrup[$row->group_name]['total_used_qty'])){ $bomGrup[$row->group_name]['total_used_qty'] = $row->used_material; }
            $row->qty = ($prcData->mfg_type == 'Forging')?$row->qty:1;
            $rq = $prcData->prc_qty * $row->qty;
            $iq = $row->issue_qty;  $uq = 0; $sq = 0;
            $return = (!empty($row->return_qty)?$row->return_qty:0);
            $scrap = (($row->category_id != 55)?(!empty($row->scrap_qty)?$row->scrap_qty:0):0);
            $inPrdStock = $iq-($return + $row->scrap_qty);
            if($bomGrup[$row->group_name]['total_used_qty'] > 0){
                if($inPrdStock >= $bomGrup[$row->group_name]['total_used_qty']){
                    $uq =  $bomGrup[$row->group_name]['total_used_qty'];
                }else{
                    $uq =  $inPrdStock;
                }
                $bomGrup[$row->group_name]['total_used_qty'] -= $uq;
            }
            
            $usedScrap = (($row->category_id != 55)?(!empty($row->used_scrap)?$row->used_scrap:0):0);
            $sq = round(($iq - ($uq + $return + $scrap)),2);
            $uq = $uq - $usedScrap;

            $printBtn = '';
            if(!empty($iq) && $iq > 0){
                $pUrl = encodeURL(['item_id'=>$row->item_id,'prc_id'=>$row->prc_id]);
                $printBtn = '<a href="'.base_url('pos/printMaterialAcceptTag/'.$pUrl).'" target="_blank" class="dropdown-item btn btn-danger permission-modify" datatip="Material Print" flow="down"><i class="icon icon-action-redo"></i>  Print</a>';
            }
            $returnParam = "{'postData':{'prc_id' : ".$prcData->id.",'item_id' : ".$row->item_id."},'modal_id' : 'bs-right-lg-modal', 'call_function':'materialReturn', 'form_id' : 'materialReturn', 'title' : 'Return Material', 'js_store_fn' : 'storeSop', 'fnsave' : 'storeReturned','button':'close'}";
           
            $reqParam = "{'postData':{'item_id' : ".$row->item_id.", 'req_qty' : '".$rq."', 'prc_id' : ".$row->prc_id."},'modal_id' : 'bs-right-lg-modal', 'controller':'store', 'call_function':'materialRequest', 'form_id' : 'addRequisition', 'js_store_fn':'storeSop', 'title' : 'Add Requisition', 'fnsave' : 'save'}";
           
            $issueDetailParam = "{'postData':{'item_id' : ".$row->item_id." , 'prc_id' : ".$row->prc_id."},'modal_id' : 'bs-right-lg-modal', 'controller':'sopDesk', 'call_function':'getIssueDetail', 'form_id' : 'getIssueDetail', 'js_store_fn':'storeSop', 'title' : 'Issue Details', 'button' : 'close'}";
           
            $prcMaterial .= '<div class=" grid_item" style="width:100%;">
                                <div class="card sh-perfect">
                                    <div class="card-body">                                    
                                        <div class="task-box">
                                            <div class="float-end">
                                                '.(($prcData->mfg_type == 'Forging')?'
                                                <div class="dropdown d-inline-block">
                                                    <a class="dropdown-toggle" id="dLabel1" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                                        <i class="las la-ellipsis-v font-24 text-muted"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a href="javascript:void(0)" class="dropdown-item btn btn-danger permission-modify" datatip="Issue Detail" flow="down"  onclick="modalAction('.$issueDetailParam.')"><i class="icon icon-action-redo"></i> Issue Detail</a>
                                                        <a href="javascript:void(0)" class="dropdown-item btn btn-danger permission-modify" datatip="Return Material" flow="down"  onclick="loadform('.$returnParam.')"><i class="icon icon-action-redo"></i> Return</a>
                                                        <a href="javascript:void(0)" class="dropdown-item btn btn-danger permission-modify" datatip="Request Material" flow="down"  onclick="modalAction('.$reqParam.')"><i class="icon icon-action-redo"></i> Requisition</a>
                                                        '.$printBtn.'
                                                    </div>
                                                </div>':$printBtn).'
                                            </div>
                                            <h5 class="mt-0 fs-15 cursor-pointer" >'.$row->item_name.'</h5>
                                            <div class="d-flex justify-content-between">  
                                                <h6 class="fw-semibold">Supplier : <span class="text-muted font-weight-normal">'.$row->supplier_name.'</span></h6>
                                                <h6 class="fw-semibold">Heat No. : <span class="text-muted font-weight-normal"> '.(!empty($row->heat_no)?$row->heat_no:$row->stock_trans_heat).'</span></h6>                          
                                            </div>
                                            <hr class="hr-dashed my-5px">
                                            <div class="media align-items-center btn-group process-tags">
                                                <span class="badge bg-light-teal btn flex-fill" datatip="Issue Qty" flow="down">IQ : '.floatval($iq).'</span>
                                                <span class="badge bg-light-cream btn flex-fill" datatip="Used Qty"  flow="down">UQ : '.floatval($uq).'</span>
                                                <span class="badge bg-light-peach btn flex-fill" datatip="Scarp Qty" flow="down">SC : '.floatval($scrap+$usedScrap).'</span>
                                                <span class="badge bg-light-cream btn flex-fill" datatip="Return Qty"  flow="down">MR : '.floatval($return).'</span>
                                                <span class="badge bg-light-raspberry btn flex-fill" datatip="Stock Qty"  flow="down">SQ : '.floatval($sq).'</span>
                                            </div>                                       
                                        </div>
                                    </div>
                                </div>
                            </div>';
        }
    }elseif(!empty($reqMaterialData) && $prcData->mfg_type == 'Forging'){
        $prcMaterial = "";
        $bomGrup = [];
        foreach($reqMaterialData as $row){
            if(!isset($bomGrup[$row->group_name]['total_used_qty'])){ $bomGrup[$row->group_name]['total_used_qty'] = 0; }
            
            $rq = $prcData->prc_qty * $row->qty;
            $iq = 0;  $uq = 0; $sq = 0;
            $return = !empty($row->return_qty)?$row->return_qty:0;
            $inPrdStock = $iq-$return;
            if($bomGrup[$row->group_name]['total_used_qty'] > 0){
                if($inPrdStock >= $bomGrup[$row->group_name]['total_used_qty']){
                    $uq =  $bomGrup[$row->group_name]['total_used_qty'];
                    
                }else{
                    $uq =  $inPrdStock;
                }
                $bomGrup[$row->group_name]['total_used_qty'] -= $uq;
            }
           
            $sq = $iq - ($uq + $return);
            
            $reqParam = "{'postData':{'item_id' : ".$row->ref_item_id.", 'req_qty' : '".$rq."', 'prc_id' : ".$prcData->id."},'modal_id' : 'bs-right-lg-modal', 'controller':'store', 'call_function':'materialRequest', 'form_id' : 'addRequisition', 'js_store_fn':'storeSop', 'title' : 'Add Requisition', 'fnsave' : 'save'}";
           
            $prcMaterial .= '<div class=" grid_item" style="width:100%;">
                <div class="card sh-perfect">
                    <div class="card-body">                                    
                        <div class="task-box">
                            <div class="float-end">
                                <div class="dropdown d-inline-block">
                                    <a class="dropdown-toggle" id="dLabel1" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                        <i class="las la-ellipsis-v font-24 text-muted"></i>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0)" class="dropdown-item btn btn-danger permission-modify" datatip="Request Material" flow="down"  onclick="modalAction('.$reqParam.')"><i class="icon icon-action-redo"></i> Requisition</a>
                                     </div>
                                </div>
                            </div>
                            <h5 class="mt-0 fs-15 cursor-pointer" >'.$row->item_name.'</h5>
                            <div class="d-flex justify-content-between">  
                                <h6 class="fw-semibold">Supplier : <span class="text-muted font-weight-normal"></span></h6>
                                <h6 class="fw-semibold">Heat No. : <span class="text-muted font-weight-normal"></span></h6>                          
                            </div>
                            <hr class="hr-dashed my-5px">
                            <div class="media align-items-center btn-group process-tags">
                                <span class="badge bg-light-teal btn flex-fill" datatip="Issue Qty" flow="down">IQ : '.floatval($iq).'</span>
                                <span class="badge bg-light-cream btn flex-fill" datatip="Used Qty"  flow="down">UQ : '.floatval($uq).'</span>
                                <span class="badge bg-light-cream btn flex-fill" datatip="Return Qty"  flow="down">MR : '.floatval($return).'</span>
                                <span class="badge bg-light-raspberry btn flex-fill" datatip="Stock Qty"  flow="down">SQ : '.floatval($sq).'</span>
                            </div>                                       
                        </div>
                    </div>
                </div>
            </div>';
        }
    }elseif($prcData->mfg_type == 'Machining'){
        $issueDetailParam = "{'postData':{'item_id' : ".$prcData->item_id." , 'prc_id' : ".$prcData->prc_id."},'modal_id' : 'bs-right-lg-modal', 'controller':'sopDesk', 'call_function':'getIssueDetail', 'form_id' : 'getIssueDetail', 'js_store_fn':'storeSop', 'title' : 'Issue Details', 'button' : 'close'}";
           
        $prcMaterial = '<div class=" grid_item" style="width:100%;">
            <div class="card sh-perfect">
                <div class="card-body">                                    
                    <div class="task-box">
                        <div class="float-end">
                            <div class="dropdown d-inline-block">
                                <a class="dropdown-toggle" id="dLabel1" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    <i class="las la-ellipsis-v font-24 text-muted"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0)" class="dropdown-item btn btn-danger permission-modify" datatip="Issue Detail" flow="down"  onclick="modalAction('.$issueDetailParam.')"><i class="icon icon-action-redo"></i> Issue Detail</a>
                                 </div>
                            </div>
                        </div>
                        <h5 class="mt-0 fs-15 cursor-pointer" >'.$prcData->item_name.'</h5>
                        <div class="d-flex justify-content-between">  
                            <h6 class="fw-semibold">Supplier : <span class="text-muted font-weight-normal"></span></h6>
                            <h6 class="fw-semibold">Heat No. : <span class="text-muted font-weight-normal"></span></h6>                          
                        </div>
                        <hr class="hr-dashed my-5px">
                        <div class="media align-items-center btn-group process-tags">
                            <span class="badge bg-light-teal btn flex-fill" datatip="Issue Qty" flow="down">IQ : 0</span>
                            <span class="badge bg-light-cream btn flex-fill" datatip="Used Qty"  flow="down">UQ : 0</span>
                            <span class="badge bg-light-cream btn flex-fill" datatip="Return Qty"  flow="down">MR : 0</span>
                            <span class="badge bg-light-raspberry btn flex-fill" datatip="Stock Qty"  flow="down">SQ : 0</span>
                        </div>                                       
                    </div>
                </div>
            </div>
        </div>';
    }
    
    
    echo $prcMaterial;

?>