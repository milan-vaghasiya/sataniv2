<?php
if(!empty($mcStockData)){
    foreach($mcStockData AS $row){
        $prcParam = "{'postData':{'batch_no' : '".$row->batch_no."','item_id':".$row->item_id.",'heat_no':'".$row->heat_no."','mfg_type':'Machining'}, 'modal_id' : 'bs-right-lg-modal', 'form_id' : 'addPrc', 'title' : 'Create PRC', 'call_function' : 'addPrc', 'fnsave' : 'savePRC', 'js_store_fn' : 'storeSop'}"; // 26-10-2024
        $prcBtn = '<a class="dropdown-item" href="javascript:void(0)" datatip="Create PRC." flow="down" onclick="modalAction(' . $prcParam . ');"><i class="fas fa-stop-circle text-muted font-10"></i> Create PRC</a>';

        $updateParam = "{'postData':{'batch_no' : '".$row->batch_no."','item_id':".$row->item_id.",'heat_no':'".$row->heat_no."','mfg_type':'Machining'}, 'modal_id' : 'modal-md', 'form_id' : 'addPrc', 'title' : 'Update PRC Qty', 'call_function' : 'updateMachiningQty', 'fnsave' : 'saveMachiningQty', 'js_store_fn' : 'storeSop'}"; //10-11-2024
        $updateBtn = '<a class="dropdown-item" href="javascript:void(0)" datatip="Update PRC Qty." flow="down" onclick="modalAction(' . $updateParam . ');"><i class="fas fa-stop-circle text-muted font-10"></i> Update PRC Qty</a>';
        ?>
        <div href="#" class="media grid_item">
            <div class="media-body">
                <div class="d-inline-block">
                    <h6><a href="javascript:void(0)" type="button" class="text-primary"><?=$row->batch_no?></a></h6>
                    <p class="fs-13"><i class="fa fa-bullseye"></i><?=$row->item_code.' '.$row->item_name?></p>
                    <p class="fs-13"><i class="fa fa-bullseye"></i><?=$row->heat_no?></p>
                </div>
                <div></div>
            </div>
            <div class="media-right">
                <a class="dropdown-toggle lead-action" data-bs-toggle="dropdown" href="#" role="button"><i class="mdi mdi-chevron-down fs-3"></i></a>
                <div class="dropdown-menu">
                    <?=$prcBtn.$updateBtn?>
                </div><br>
                <p class="text-danger"> <?=floatval($row->qty)?></p>
            </div>
        </div>
        <?php
    }
}
?>