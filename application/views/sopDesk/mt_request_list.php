<?php
if(!empty($requestList)){
    foreach($requestList AS $row){
        $deleteParam = "{'postData':{'id' : ".$row->id."}, 'message' : 'Request', 'fndelete' : 'deleteRequest', 'res_function' : 'getPrcResponse'}";
        $deleteButton = '<a class="dropdown-item text-danger btn-delete permission-remove" href="javascript:void(0)" onclick="trashSop('.$deleteParam.');" datatip="Remove" flow="down"><i class="mdi mdi-trash-can-outline"></i> Remove</a>';

        $editParam = "{'postData':{'id' : ".$row->id."},'modal_id' : 'bs-right-lg-modal', 'controller':'sopDesk', 'form_id' : 'editRequest', 'call_function' : 'editRequest', 'js_store_fn' : 'storeSop', 'title' : 'Update Request', 'fnsave' : 'saveMaterialRequest'}";
        $editButton = '<a class="dropdown-item text-success btn-edit permission-modify" href="javascript:void(0)" datatip="Edit" flow="down" onclick="loadform('.$editParam.');"><i class="mdi mdi-square-edit-outline"></i> Edit</a>';
        ?>
        <div href="#" class="media grid_item">
            <div class="media-body">
                <div class="d-inline-block">
                    <h6><a href="javascript:void(0)" type="button" class="text-primary"><?=$row->trans_number?></a></h6>
                    <p class="fs-13"><i class="mdi mdi-clock"></i> <?=formatDate($row->trans_date)?></p>
                    <p class="fs-13"><i class="fa fa-bullseye"></i><?=$row->item_code.' '.$row->item_name?></p>
                </div>
                <div></div>
            </div>
            <div class="media-right">
                <a class="dropdown-toggle lead-action" data-bs-toggle="dropdown" href="#" role="button"><i class="mdi mdi-chevron-down fs-3"></i></a>
                <div class="dropdown-menu">
                    <?=$editButton.$deleteButton?>
                </div><br>
                <p class="text-danger"> <?=floatval($row->req_qty)?></p>
            </div>
        </div>
        <?php
    }
}
?>