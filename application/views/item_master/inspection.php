<form id="inspection" data-res_function="inspectionHtml">
    <div class="row">
        <input type="hidden" name="id" id="id" class="id" value="" />
        <input type="hidden" name="item_id" id="item_id" class="item_id" value="<?=$item_id?>" />
        <input type="hidden" name="item_type" id="item_type" value="<?=$item_type?>" />

        <?php if($item_type == 1): ?>
        <div class="col-md-4 form-group">
            <label for="rev_no">Revision No.</label>
            <select name="rev_no" id="rev_no" class="form-control select2 req">
                <option value="">Select Revision No.</option>
                <?php
                    foreach($revisionData as $row):
                        echo '<option value="'.$row->rev_no.'">'.$row->rev_no.'</option>';
                    endforeach;
                ?>
            </select>
            <div class="error rev_no"></div>
        </div>
        <div class="col-md-4 form-group">
            <label for="process_id">Process</label>
            <select name="process_id" id="process_id" class="form-control select2 req">
                <option value="">Select Process</option>
                <?php
                    foreach($processData as $row):
                        echo '<option value="'.$row->process_id.'">'.$row->process_name.'</option>';
                    endforeach;
                ?>
            </select>
            <div class="error process_id"></div>
        </div>

        <div class="col-md-4 form-group">
            <label for="param_type">Parameter Type</label>
            <select name="param_type" id="param_type" class="form-control select2">
                <option value="1">Product</option>
                <option value="2">Process</option>
            </select>
            <div class="error param_type"></div>
        </div>
        <?php endif; ?>        

        <?php if($item_type == 3): ?>
            <input type="hidden" name="param_type" id="param_type" value="1" />
            <input type="hidden" name="control_method" id="control_method" value="IIR" />
        <?php endif; ?>
        
        <div class="col-md-4 form-group">
            <label for="parameter">Parameter</label>
            <input type="text" name="parameter" id="parameter" class="form-control req" value="" />
        </div>
        <div class="col-md-4 form-group">
            <label for="specification">Specification</label>
            <input type="text" name="specification" id="specification" class="form-control req" value="" />
        </div>
        <div class="col-md-4 form-group">
            <div class="input-group">
                <div class="input-group-append" style="width:50%;">
                    <label for="min">Min</label>
                    <input type="text" name="min" id="min" class="form-control floatOnly" value="" placeholder="Min"/>
                </div>
                <div class="input-group-append" style="width:50%;">
                    <label for="max">Max</label>
                    <input type="text" name="max" id="max" class="form-control floatOnly" value="" placeholder="Max"/>
                </div>
            </div>
        </div>
        <div class="col-md-4 form-group">
            <label for="machine_tool">Machine Tools(MFG)</label>
            <input type="text" name="machine_tool" id="machine_tool" class="form-control">
        </div>
        <div class="col-md-4 form-group">
            <label for="instrument">Instrument</label>
            <input type="text" name="instrument" id="instrument" class="form-control">
        </div>
        <div class="col-md-4 form-group">
            <label for="char_class">Special Char. Class</label>
            <select name="char_class" id="char_class" class="form-control symbl1 select2">
                <option value="">Select</option>
                <?php
                    foreach($this->classArray AS $key=>$symbol){ 
                        if(!empty($symbol)){
                            echo '<option value="'.$key.'" > '.$symbol.'</option>';
                        }
                    }
                ?>
            </select>
        </div>
        <div class="col-md-4 form-group">
            <div class="input-group">
                <div class="input-group-append" style="width:33%;">
                    <label for="size">Size</label>
                    <input type="text" name="size" id="size" class="form-control">
                </div>
                <div class="input-group-append" style="width:33%;">
                    <label for="frequency">Frequency</label>
                    <input type="text" name="frequency" id="frequency" class="form-control numericOnly">
                </div>
                <div class="input-group-append" style="width:34%;">
                    <label for="freq_unit">Frequency Unit</label>
                    <select name="freq_unit" id="freq_unit" class="form-control select2">
                        <option value="Hrs">Hrs</option>
                        <option value="Lot">Lot</option>
                    </select>
                </div>
            </div>
        </div>
         <?php if($item_type == 1): ?>
        <div class="col-md-3 form-group">
            <label for="control_method">Control Method</label>
            <select name="control_method[]" id="control_method" class="form-control select2 req" multiple>
                <option value="IIR">IIR (Incoming Inspection Report)</option>
                <option value="SAR">SAR (Setup Approval Report)</option>
                <option value="IPR">IPR (Inprocess Inspection Report)</option>
                <option value="FIR">FIR (Final Inspection Report)</option>
            </select>
            <div class="error control_method"></div>
        </div>
        <?php endif; ?>
          <div class="<?=($item_type == 1)?'col-md-5':'col-md-8'?> form-group">
            <label for="control_method">Reaction Plan</label>
            <div class="input-group">
                <div class="input-group-append" style="width:80%;">
                    <input type="text" name="reaction_plan" id="reaction_plan" class="form-control">
                </div>
                <div class="input-group-append" style="width:20%;">                                           
                    <?php
                    $param = "{'formId':'inspection','fnsave':'saveInspection','controller':'items','res_function':'inspectionHtml'}";
                    ?>
                    <button type="button" class="btn waves-effect waves-light btn-outline-success save-form" onclick="customStore(<?=$param?>)"><i class="fa fa-check"></i> Save</button>
                </div>      
            </div>
        </div>
    </div>
</form>
<hr>
<div class="row">
        <div class="col-md-4">
            <a href="<?= base_url($headData->controller . '/createInspectionExcel/' . $item_id.'/'.$item_type.'/' ) ?>" class="btn btn-block btn-info bg-info-dark mr-2" target="_blank">
                <i class="fa fa-download"></i>&nbsp;&nbsp;
                <span class="btn-label">Download Excel&nbsp;&nbsp;<i class="fa fa-file-excel"></i></span>
            </a>
        </div>
        <div class="col-md-4">
            <input type="file" name="insp_excel" id="insp_excel" class="form-control float-left" />
            <h6 class="col-md-12 msg text-primary text-center mt-1"></h6>
        </div>
        <div class="col-md-4">
            <a href="javascript:void(0);" class="btn btn-block btn-success bg-success-dark ml-2 importExcel" type="button">
                <i class="fa fa-upload"></i>&nbsp;
                <span class="btn-label">Upload Excel &nbsp;<i class="fa fa-file-excel"></i></span>
            </a>
        </div>
    </div>
<hr>
    <div class="row">
        <div class="table-responsive">
            <table id="inspectionId" class="table table-bordered align-items-center fhTable">
                <thead class="thead-info">
                    <tr>
                            <th style="width:5%;">#</th>
                        <?php if($item_type == 1): ?>
                            <th>Rev No</th>
                            <th>Process Name</th>
                        <?php endif; ?>
                            <th>Product</th>
                        <?php if($item_type == 1): ?>
                            <th>Process</th>
                        <?php endif; ?>
                            <th>Machine Tools(MFG)</th>
                            <th>Specification</th>
                            <th>Min</th>
                            <th>Max</th>
                            <th>Special Char. Class</th>
                            <th>Instrument</th>
                            <th>Size</th>
                            <th>Frequency</th>
                            <th>Frequency Unit</th>
                            <th>Reaction Plan</th>
                            <th>Control Method</th>
                            <th class="text-center" style="width:10%;">Action</th>
                    </tr>
                </thead>
                <tbody id="inspectionBody" class="scroll-tbody scrollable maxvh-60">
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
var tbodyData = false;
$(document).ready(function(){
    if(!tbodyData){
        var postData = {'postData':{'item_id':$("#item_id").val(),'item_type':$("#item_type").val()},'table_id':"inspectionId",'tbody_id':'inspectionBody','tfoot_id':'','fnget':'inspectionHtml'};
        getTransHtml(postData);
        tbodyData = true;
    }

    $(document).on('click', '.importExcel', function(e) {
        e.stopImmediatePropagation();e.preventDefault();
        $(this).attr("disabled", "disabled");
        var fd = new FormData(); 
        fd.append("insp_excel", $("#insp_excel")[0].files[0]);
        fd.append("item_id", $("#item_id").val());
        fd.append("item_type", $("#item_type").val());
        $.ajax({
            url: base_url + controller + '/importExcel',
            data: fd,
            type: "POST",
            processData: false,
            contentType: false,
            dataType: "json",
        }).done(function(data) { 
            $(".msg").html(data.message);
            $(this).removeAttr("disabled");
            $("#insp_excel").val(null);
            if (data.status == 1) {
                inspectionHtml(data);   
            }
        });
    });

});

function inspectionHtml(data,formId="inspection"){ 
    if(data.status==1){
        $('#'+formId)[0].reset();
        var postData = {'postData':{'item_id':$("#item_id").val(),'item_type':$("#item_type").val()},'table_id':"inspectionId",'tbody_id':'inspectionBody','tfoot_id':'','fnget':'inspectionHtml'};
        getTransHtml(postData);
    }else{
        if(typeof data.message === "object"){
            $(".error").html("");
            $.each( data.message, function( key, value ) {$("."+key).html(value);});
        }else{
            initTable();
            Swal.fire({ icon: 'error', title: data.message });
        }			
    }
}

function editInspParam(data) { 
	$.each(data, function (key, value) { 
    $("#" + key).val(value); });
    if (data.control_method && data.control_method.toUpperCase() !== 'NULL') {
        var controlMethod = data.control_method.split(",");
        $('#control_method').val(controlMethod);
    } else {
        $('#control_method').val([]);
    }
    $('#char_class').select2();
    $("#rev_no").select2();
    $("#process_id").select2();
    $("#freq_unit").select2();
    if(data.item_type == 1){
    $("#control_method").select2();
    }
}

</script>