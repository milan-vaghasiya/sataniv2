<?php $this->load->view('includes/header'); ?>

<div class="page-content-tab">
	<div class="container-fluid">
		<div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<div class="row">
							<div class="col-md-6">
								<h4 class="card-title">Change Job Stage</h4>
							</div>
							
						</div>
					</div>
					<div class="card-body">
                        <form id="changeJobStage">
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label for="prc_id">PRC Number</label>
                                        <select class="form-control select2" id="prc_id" name="prc_id" >
                                            <option value="">Select PRC Number</option>
                                            <?php
                                            if(!empty($prcList)){
                                                foreach($prcList as $row){
                                                    if(empty($row->stock_id)){ ?>
                                                        <option value="<?=$row->id?>" data-process_ids='<?=$row->process_ids?>'><?=$row->prc_number?></option>
                                            <?php   }
                                                }
                                            }
                                            ?>
                                        </select>                                        
                                </div>
                                <div class="col-md-2 form-group">
                                    <button type="button" class="btn btn-block btn-info waves-effect waves-light loadProcess mt-20"  >Load</button>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="stage_id">Production Stages</label>
                                    <select name="stage_id" id="stage_id" data-input_id="process_id1" class="form-control select2" style="width:80%">
                                        <option value="">Select Stage</option>
                                    </select>
                                </div>
                                <div class="col-md-2 form-group">
                                    <button type="button" class="btn  btn-success waves-effect add-process btn-block addJobStage mt-20">+ Add</a>
                                </div>
                                <hr>
                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <div class="error stage_error"></div>
                                        <table id="jobStages" class="table excel_table table-bordered">
                                            <thead class="thead-info">
                                                <tr>
                                                    <th style="width:10%;text-align:center;">#</th>
                                                    <th style="width:65%;">Process Name</th>
                                                    <th style="width:10%;">Remove</th>
                                                </tr>
                                            </thead>
                                            <tbody id="stageRows">
                                                <?php
                                                echo '<tr><td colspan="3" class="text-center">No Data Found.</td></tr>';
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                            </div>
                        </form>
                            
                        
                    </div>

                    <div class="card-footer">
                        <div class="col-md-12">
                            <button type="button" class="btn waves-effect waves-light btn-outline-success float-right save-form" onclick="saveStage('changeJobStage');" ><i class="fa fa-check"></i> Save</button>
                            <a href="<?=base_url($headData->controller.'/changeJobStage')?>" class="btn waves-effect waves-light btn-outline-secondary float-right save-form" style="margin-right:10px;"><i class="fa fa-times"></i> Cancel</a>
                        </div>
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>



<?php $this->load->view('includes/footer'); ?>
<script>
    $(document).ready(function () {
        $(document).on('click', ".loadProcess", function () {
            var prc_id = $("#prc_id").val();
            
            $("#stage_id").html("");
            $("#stageRows").html("");
            if(prc_id){
                var process_ids = $('#prc_id :selected').data('process_ids');
                $.ajax({
                    url: base_url + controller + '/getJobStages',
                    data: { prc_id: prc_id,process_ids:process_ids},
                    type: "POST",
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 0) {
                            swal("Sorry...!", data.message, "error");
                        }
                        else {
                            $("#stage_id").html(data.processOptions);
                            $("#stageRows").html(data.stageRows);
                        }
                    }
                });
            }
            
        });

        $("#jobStages tbody").sortable({
            items: 'tr',
            cursor: 'pointer',
            axis: 'y',
            dropOnEmpty: false,
            helper: fixWidthHelper,
            start: function (e, ui) { ui.item.addClass("selected"); },
            stop: function (e, ui) {
                ui.item.removeClass("selected");
                var seq = 0;
                $(this).find("tr").each(function () { $(this).find("td").eq(0).html(seq + 1); seq++; });
            },
            update: function () {
                var ids = '';
                $(this).find("tr").each(function (index) { ids += $(this).attr("id") + ","; });
                var lastChar = ids.slice(-1);
                if (lastChar == ',') { ids = ids.slice(0, -1); }
            

                
            }
        });

        $(document).on('click', '.addJobStage', function () {
            var jobid = $('#prc_id').val();
            var process_id = $('#stage_id').val();
            $(".stage_id").html("");
            if (jobid != "" && process_id != "") {
                $.ajax({
                    type: "POST",
                    url: base_url + controller + '/addJobStage',
                    data: { prc_id: jobid, process_id: process_id },
                    dataType: 'json',
                    success: function (data) {
                        $('#stageRows').html(""); $('#stageRows').html(data.stageRows);
                        $('#stage_id').html(""); $('#stage_id').html(data.processOptions);
                    }
                });
            } else {
                $(".stage_id").html("Stage is required.");
            }
        });

	});

function fixWidthHelper(e, ui) {
    ui.children().each(function() {
        $(this).width($(this).width());
    });
    return ui;
}

function saveStage(formId){
	var fd = $('#'+formId)[0];
    var formData = new FormData(fd);
	$.ajax({
		url: base_url + controller + '/saveJobProcessSequence',
		data:formData,
        processData: false,
        contentType: false,
		type: "POST",
		dataType:"json",
	}).done(function(data){
		if(data.status===0){
			$(".error").html("");
			$.each( data.message, function( key, value ) {
				$("."+key).html(value);
			});
		}else if(data.status==1){
			Swal.fire( 'Success', data.message, 'success' );
            window.location.reload();
		}else{
			Swal.fire( 'Sorry...!', data.message, 'error' );
		}				
	});
}

function removeJobStage(button){
	//Determine the reference of the Row using the Button.
	var row = $(button).closest("TR");
	var table = $("#jobStages")[0];
	table.deleteRow(row[0].rowIndex);
	$('#jobStages tbody tr td:nth-child(1)').each(function(idx, ele) {
        ele.textContent = idx + 1;
    });
	var countTR = $('#jobStages tbody tr:last').index() + 1;
	if(countTR == 0){
        $("#jobStages tbody").html('<tr id="noData"><td colspan="3" align="center">No data available in table</td></tr>');
	}
}

</script>