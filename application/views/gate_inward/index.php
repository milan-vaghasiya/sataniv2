<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
	<div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
				<div class="page-title-box">
                    <div class="float-start">
                        <ul class="nav nav-pills">
                            <li class="nav-item"> 
                                <button onclick="grnStatusTab('giTable','2',0);" class="nav-tab btn waves-effect waves-light btn-outline-danger active" style="outline:0px" data-toggle="tab" aria-expanded="false">Pending</button> 
                            </li>
                            <li class="nav-item"> 
                                <button onclick="grnStatusTab('giTable','2',1);" class="nav-tab btn waves-effect waves-light btn-outline-success" style="outline:0px" data-toggle="tab" aria-expanded="false">Completed</button> 
                            </li>
                        </ul>
					</div>
					<div class="float-end">
                        <?php
                            $addParam = "{'modal_id' : 'bs-right-xl-modal', 'call_function':'addGateInward', 'form_id' : 'addGateInward', 'title' : 'Gate Inward'}";
                        ?>
                        <button type="button" class="btn waves-effect waves-light btn-outline-dark permission-write float-right press-add-btn" onclick="modalAction(<?=$addParam?>);"><i class="fa fa-plus"></i> Add GI</button>
					</div>
				</div>
            </div>
		</div>
        <div class="row">
            <div class="col-12">
				<div class="col-12">
					<div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id='giTable' class="table table-bordered ssTable ssTable-cf" data-url='/getDTRows'></table>
                            </div>
                        </div>
					</div>
				</div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('includes/footer'); ?>
<script>
$(document).ready(function() {
	initbulkTagButton();
	$(document).on('click', '.BulkTagPrint', function() {
		if ($(this).attr('id') == "masterSelect") {
			if ($(this).prop('checked') == true) {
				$(".bulkTag").show();
				$("input[name='ref_id[]']").prop('checked', true);
			} else {
				$(".bulkTag").hide();
				$("input[name='ref_id[]']").prop('checked', false);
			}
		} else {
			if ($("input[name='ref_id[]']").not(':checked').length != $("input[name='ref_id[]']").length) {
				$(".bulkTag").show();
				$("#masterSelect").prop('checked', false);
			} else {
				$(".bulkTag").hide();
			}

			if ($("input[name='ref_id[]']:checked").length == $("input[name='ref_id[]']").length) {
				$("#masterSelect").prop('checked', true);
				$(".bulkTag").show();
			}
			else{
				$("#masterSelect").prop('checked', false);
			}
		}
	});
	
	$(document).on('click', '.bulkTag', function() {
		var ref_id = [];
		$("input[name='ref_id[]']:checked").each(function() {
			ref_id.push(this.value);
		});
		var ids = ref_id.join("~");
		var status = $("input[name='ref_id[]']:checked").first().data('status') || 0;
		
		if(status != 0){
			window.open(base_url + 'gateInward/printMaterialTag/' + ids, '_blank');
		}else{
			window.open(base_url + 'gateInward/ir_print/' + ids, '_blank');
		}
			
	});
});

function grnStatusTab(tableId,grn_type,status,hp_fn_name="",page=""){

    $("#"+tableId).attr("data-url",$("#"+tableId).data('url')+'/'+grn_type+'/'+status);

	$("#"+tableId).data("hp_fn_name","");
    $("#"+tableId).data("page","");
    $("#"+tableId).data("hp_fn_name",hp_fn_name);
    $("#"+tableId).data("page",page);

    ssTable.state.clear();
	initTable();
	initbulkTagButton();
}

function initbulkTagButton() {
	var bulkTagBtn = '<button class="btn btn-outline-dark bulkTag" tabindex="0" aria-controls="giTable" type="button"><span>IIR PDF</span></button>';
	$("#giTable_wrapper .dt-buttons").append(bulkTagBtn);
	$(".bulkTag").hide();
}
</script>