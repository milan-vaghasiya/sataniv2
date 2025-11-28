<?php $this->load->view('includes/header'); ?>
<div class="page-content-tab">
    <div class="container-fluid bg-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-center">
                        <h4><u>Raw Material Reciving Inspection Report</u></h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 form-group">
                                <label for="grn_prefix"> GRN No : <?= (!empty($dataRow->trans_number))? $dataRow->trans_number:"";?> </label> 
                            </div>
                            <div class="col-md-2 form-group">
                                <label for="grn_date"> GRN Date : <?= (!empty($dataRow->trans_date))? $dataRow->trans_date:"";?> </label>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="item_name"> Item Name : <?= (!empty($dataRow->item_name))? $dataRow->item_name:"";?> </label>
                            </div>
                            <div class="col-md-3 form-group">
                                <label for="party_name"> Party Name : <?= (!empty($dataRow->party_name))? $dataRow->party_name:"";?> </label>
                            </div>
                            <div class="col-md-2 form-group">
                                <label for="qty"> Qty : <?= (!empty($dataRow->qty))? $dataRow->qty:"";?> </label>
                            </div>
                        </div>
                        <form autocomplete="off" id="InInspection">
                            <?php $sample_size = 10; ?>
                            <div class="col-md-12">
                                <input type="hidden" name="id" value="<?=(!empty($dataRow->id))?$dataRow->id:""?>" />
                                <input type="hidden" name="mir_id" id="mir_id" value="<?=(!empty($dataRow->mir_id))?$dataRow->mir_id:""?>" />
                                <input type="hidden" name="trans_no" value="<?=(!empty($dataRow->trans_no))?$dataRow->trans_no:$trans_no?>" />
                                <input type="hidden" name="trans_prefix" value="<?=(!empty($dataRow->trans_prefix))?$dataRow->trans_prefix:$trans_prefix?>" />
                                <input type="hidden" name="item_id" id="item_id" value="<?=(!empty($dataRow->item_id))?$dataRow->item_id:""?>" />
                                <input type="hidden" name="sampling_qty" id="sampling_qty" value="<?=$sample_size?>" id="sampling_qty">
                            </div>
                            <div class="col-md-12"> <div class="error general"></div> </div>
							<div class="col-md-12 mt-3">
								<div class="row form-group">
									<div class="table-responsive">
                                        
                                        <input type="hidden" name="sample_size" value="<?=$sample_size?>">
										<table id="preDispatchtbl" class="table table-bordered generalTable">
											<thead class="thead-info">
												<tr style="text-align:center;">
													<th rowspan="2" style="width:5%;">#</th>
                                                    <th rowspan="2">Parameter</th>
                                                    <th rowspan="2">Specification</th>
                                                    <th rowspan="2">Tolerance</th>
                                                    <th rowspan="2">Instrument</th>
													<th colspan="<?=$sample_size?>">Observation on Samples</th>
													<th rowspan="2">Result</th>
                                                </tr>
                                                <tr style="text-align:center;">
                                                <?php
                                                for($c=0;$c<$sample_size;$c++):
                                                   ?>
                                                   <th><?=$c+1?></th>
                                                   <?php
                                                endfor;
                                                ?>
													
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyData">
                                                <?php
                                                    $tbodyData="";$i=1; 
                                                   
                                                    if(!empty($inspectParamData)):
                                                        foreach($inspectParamData as $row):
                                                            $obj = New StdClass;
                                                            $cls="";
                                                            if(!empty($row->lower_limit) OR !empty($row->upper_limit)):
                                                                $cls="floatOnly";
                                                            endif;
                                                            if(!empty($inInspectData)):
                                                                $obj = json_decode($inInspectData->observation_sample); 
                                                            endif;
                                                            $inspOption = '';
                                                            $inspOption  = '<option value="Ok" '.((!empty($obj->{$row->id}) && ($obj->{$row->id}[$sample_size]=='Ok'))?'selected':'').' >Ok</option>
				                                            				<option value="Not Ok" '.((!empty($obj->{$row->id}) && ($obj->{$row->id}[$sample_size]=='Not Ok'))?'selected':'').'>Not Ok</option>';
				
                                                            $tbodyData.= '<tr>
                                                                        <td style="text-align:center;">'.$i++.'</td>
                                                                        <td>' . $row->parameter . '</td>
                                                                        <td>' . $row->specification . '</td>
                                                                        <td>' . $row->tolerance . '</td>
                                                                        <td>' . $row->instrument . '</td>';
                                                            for($c=0;$c<$sample_size;$c++):
                                                                if(!empty($obj->{$row->id})):
                                                                    $tbodyData .= '<td><input type="text" name="sample'.($c+1).'_'.$row->id.'" id="sample'.($c+1).'_'.$i.'" class="form-control text-center parameter_limit'.$cls.'" value="'.$obj->{$row->id}[$c].'" data-row_id ="'.$i.'" ></td>';
                                                                else:
                                                                    $tbodyData .= '<td><input type="text" name="sample'.($c+1).'_'.$row->id.'" id="sample'.($c+1).'_'.$i.'" class="form-control text-center parameter_limit'.$cls.'" value="" data-row_id ="'.$i.'"></td>';
                                                                endif;
                                                            endfor;
                                                            if(!empty($obj->{$row->id})):
                                                                $tbodyData .= '<td style="width:100px;"><select name="status_'.$row->id.'" id="status_'.$i.'" class="form-control  text-center" value="'.$obj->{$row->id}[$sample_size].'">'.$inspOption.'</select></td></tr>';
                                                            else:
                                                                $tbodyData .= '<td style="width:100px;"><select name="status_'.$row->id.'" id="status_'.$i.'" class="form-control  text-center" value="">'.$inspOption.'</select></td></tr>';
                                                            endif;
                                                        endforeach;
                                                    else:
                                                        $tbodyData .='<tr><th class="text-center" colspan="'.(6+$sample_size).'">No data available.</th></tr>';
                                                    endif;
                                                    echo $tbodyData;
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
                            <button type="button" class="btn waves-effect waves-light btn-outline-success float-right save-form" onclick="saveInInspection('InInspection','saveInInspection');" ><i class="fa fa-check"></i> Save</button>
                            <a href="<?=base_url('gateInward')?>" class="btn waves-effect waves-light btn-outline-secondary float-right save-form" style="margin-right:10px;"><i class="fa fa-times"></i> Cancel</a>
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
    
});

function saveInInspection(formId,fnsave){
    setPlaceHolder();
	if(fnsave == "" || fnsave == null){fnsave="save";}
	var form = $('#'+formId)[0];
	var fd = new FormData(form);
	$.ajax({
		url: base_url + controller + '/' + fnsave,
		data:fd,
		type: "POST",
		processData:false,
		contentType:false,
		dataType:"json",
	}).done(function(data){
		if(data.status===0){
			$(".error").html("");
			$.each( data.message, function( key, value ) {$("."+key).html(value);});
		}else if(data.status==1){
			initTable(); $('#'+formId)[0].reset();$(".modal").modal('hide');
			Swal.fire({ icon: 'success', title: data.message});
            location.href = base_url + controller;
        }else{
			initTable();  $('#'+formId)[0].reset();$(".modal").modal('hide');   
			Swal.fire({ icon: 'error', title: data.message });
            // location.href = base_url + controller;
        }
	});
}

</script>