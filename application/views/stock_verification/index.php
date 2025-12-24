<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6"><h4 class="card-title"><?=$pageHeader?></h4></div>  
                            <div class="col-md-6 float-right">  
                                <div class="input-group">
                                    <div class="input-group-append" style="width:40%;">
                                        <select id="item_type" class="form-control select2">
                                            <?php
                                                foreach($this->itemTypes as $type=>$typeName):
                                                    echo '<option value="'.$type.'">'.$typeName.'</option>';
                                                endforeach;
                                            ?>
                                            <option value="99">Semi Finish</option>
                                        </select>
                                    </div>
                                    <div class="input-group-append" style="width:40%;">
                                        <select id="store_name" class="form-control select2">
                                            <option value="">ALL Location</option>
                                            <?php 
                                                if(!empty($locationList)){
                                                    foreach($locationList as $row){
                                                        echo '<option value="' . encodeURL($row['store_name']) . '">' . $row['store_name'] . '</option>';
                                                    }
                                                }
                                            ?>
                                        </select> 
                                    </div>
                                    <div class="input-group-append">
                                        <button type="button" class="btn waves-effect waves-light btn-success refreshReportData loadData" title="Load Data">
									        <i class="fas fa-sync-alt"></i> Load
								        </button>
                                    </div>
                                </div>
                            </div>
                        </div>                                         
                    </div>  
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id='reportTable' class="table table-bordered ssTable" data-url='/<?=$dataUrl?>'></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>

<?php $this->load->view('includes/footer'); ?>
<script>
$(document).ready(function(){
    $(document).on('click','.loadData',function(e){
		let item_type = $('#item_type').val();
        let store_name = $('#store_name').val();
        store_name = store_name !='' ? '/'+store_name : '';
        $("#reportTable").attr("data-url",'/getDTRows/'+item_type + store_name);
        initTable();
    });  
});

// function editStock(data,button){ 
// 	$("#storeModel").modal();
//     $("#item_id").val(data.id);//console.log(item_id);
// 	$.ajax({
// 		url: base_url +'stockVerification/editStock',
// 		data: {item_id:data.id},
// 		type: "POST",
// 		dataType:'json',
// 		success:function(data){
// 			$("#reportTable").dataTable().fnDestroy();
// 			// $("#theadData").html(data.thead);
// 			$("#tbodyData").html(data.tbody);
// 			initTable(0);
// 		}
// 	});
// }
</script>