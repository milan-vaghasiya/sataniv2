<form data-res_function="getEndPcsReturnResponse">
    <div class="card">
        <div class="media align-items-center btn-group process-tags">
            <span class="badge bg-light-peach btn flex-fill">Item : <?=(!empty($dataRow['item_name'])?$dataRow['item_name']:'')?></span>
            <span class="badge bg-light-peach btn flex-fill issueQty">Issue Qty : </span>
            <span class="badge bg-light-peach btn flex-fill usedQty">Used Qty : </span>
        </div>                                       
    </div>
    <div class="row">
        <input type="hidden" name="prc_process_id" id="prc_process_id" value="<?=(!empty($dataRow['id']) ? $dataRow['id'] : '')?>">
        <input type="hidden" name="prc_id" id="prc_id" value="<?=(!empty($dataRow['prc_id']) ? $dataRow['prc_id'] : '')?>">
        <input type="hidden" name="current_process_id" id="current_process_id" value="<?=(!empty($dataRow['current_process_id']) ? $dataRow['current_process_id'] : '')?>">

        <div class="col-md-4 form-group">
            <label for="rm_item_id">Product</label>
            <select id="rm_item_id" name="rm_item_id" class="form-control select2 req getOption">
                <option value="">Select Product</option>
                <?php
                $bomGrup = [];
                if(!empty($prcMaterialData)){
                    foreach($prcMaterialData as $row){
                        if($row->group_name == 'RM GROUP'){
                            if(!isset($bomGrup[$row->group_name]['total_used_qty'])){ $bomGrup[$row->group_name]['total_used_qty'] = $row->used_material; }
                            $rq = $prcData->prc_qty * $row->qty;
                            $iq = $row->issue_qty;  $uq = 0; $sq = 0;
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
                            $scrap = !empty($row->scrap_qty)?$row->scrap_qty:0;
                            $sq = $iq - ($uq + $return);
                            $uq = $uq - $scrap;
            
                            echo '<option data-grade_id="'.$row->grade_id.'" data-issue_qty="'.floatval($iq).'" data-stock_qty="'.floatval($uq).'" value="'.$row->item_id.'">'.$row->item_name.'</option>';
                        }
                    }
                }
                ?>
            </select>  
        </div>
        <div class="col-md-4 form-group">
            <label for="entry_type">Return Type</label>
            <select id="entry_type" name="entry_type" class="form-control select2 req getOption">
                <option value="1002">Return As Scrap</option>
                <option value="1003">Return By Product</option>
            </select>  
        </div>
        <div class="col-md-4 form-group">
            <label for="scrap_item_id">Scrap Group / By Product Item</label>
            <select id="scrap_item_id" name="scrap_item_id" class="form-control select2 req">
                <option value="">Select</option>
            </select>  
        </div>
        <div class="col-md-4 form-group byProductQty">
            <label for="location_id">Location</label>
            <select id="location_id" name="location_id" class="form-control select2 req">
                <option value="">Select Location</option>
                <?=getLocationListOption($locationList)?>
            </select>  
        </div>

        <div class="col-md-4 form-group">
            <label for="qty">Qty.(K.G.)</label>
            <input type="text" name="qty" id="qty" class="form-control floatOnly req" placeholder="Enter Quantity" value="0" min="0" />
            <input type="hidden" name="issue_qty" id="issue_qty" value="0" />
            <input type="hidden" name="stock_qty" id="stock_qty" value="0" />
        </div>

        <div class="col-md-4 form-group byProductQty">
            <label for="qty_pcs">Qty.(Pcs.)</label>
            <input type="text" name="qty_pcs" id="qty_pcs" class="form-control floatOnly req" placeholder="Enter Quantity" value="0" min="0" />
          
        </div>

        <div class="col-md-12 form-group float-end mt-2">
            <?php $param = "{'formId':'endPcsReturn','fnsave':'saveEndPcsReturn','res_function':'getEndPcsReturnResponse'}"; ?>
            <button type="button" class="btn waves-effect waves-light btn-success btn-save save-form float-right" onclick="storeSop(<?=$param?>)" style="height:35px"><i class="fa fa-check"></i> Save</button>
        </div>

    </div>
</form>
<div class="col-md-12">
    <div class="row">
        <h5 style="width:100%;margin:0 auto;vertical-align:middle;border-top:1px solid #ccc;padding:5px 0px;">Return Transaction :</h5>
        <div class="table-responsive  mb-3">
            <table id='returnTable' class="table table-bordered jpExcelTable mb-5">
                <thead class="text-center">
                    <tr>
                        <th style="min-width:20px">#</th>
                        <th style="min-width:50px">Date</th>
                        <th style="min-width:50px">Product</th>
                        <th style="min-width:50px">Scrap Group</th>
                        <th style="min-width:50px">Qty.</th>
                        <th style="min-width:30px;">Action</th>
                    </tr>
                </thead>
                <tbody id="returnTbodyData">
                   
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
var tbodyData = false;
$(document).ready(function(){
    $(".byProductQty").hide();
    if(!tbodyData){
        var postData = {'postData':{'prc_id':$("#prc_id").val(),'prc_process_id':$("#prc_process_id").val(),'qty':$("#qty").val(),'issue_qty':$("#issue_qty").val()},'table_id':"returnTable",'tbody_id':'returnTbodyData','tfoot_id':'','fnget':'getEndPcsReturnHtml'};
        getEndPcsReturnHtml(postData);
        tbodyData = true;
    }

    $(document).on('change','.getOption',function(e){
        e.stopImmediatePropagation();e.preventDefault();
        var rm_item_id = $('#rm_item_id').val();
        var entry_type = $("#entry_type").val();
		var grade_id = $('#rm_item_id :selected').data('grade_id');
        var prc_id = $('#prc_id').val();
        var current_process_id = $('#current_process_id').val();
		var stock_qty = $('#rm_item_id :selected').data('stock_qty');
		var issue_qty = $('#rm_item_id :selected').data('issue_qty');
		var valid = 1;
        if(entry_type == 1002 && (grade_id == "" || grade_id == 0)){ valid=0; }
        if(rm_item_id == ""){  valid=0;  }
        if(valid){
            $.ajax({
                url:base_url + controller + "/getScrapGroupList",
                type:'post',
                data:{ rm_item_id:rm_item_id, grade_id:grade_id, prc_id:prc_id, current_process_id:current_process_id, stock_qty:stock_qty,entry_type:entry_type },
                dataType:'json',
                success:function(data){
                    $("#scrap_item_id").html("");
                    $("#scrap_item_id").html(data.options);
                    $("#qty").val(data.totalQty);
                    $("#stock_qty").val(stock_qty);
                    $(".issueQty").html("Issue Qty : "+issue_qty);
                    $(".usedQty").html("Used Qty : "+stock_qty);
                    initSelect2();	
                }
            });
        }

        if(entry_type == 1003){
            $(".byProductQty").show();
        }else{
            $(".byProductQty").hide();
        }
    });

});
function getEndPcsReturnResponse(data,formId="endPcsReturn"){ 
    if(data.status==1){
        $('#'+formId)[0].reset(); initSelect2();
        var postData = {'postData':{'prc_id':$("#prc_id").val(),'prc_process_id':$("#prc_process_id").val(),'qty':$("#qty").val(),'issue_qty':$("#issue_qty").val()},'table_id':"returnTable",'tbody_id':'returnTbodyData','tfoot_id':'','fnget':'getEndPcsReturnHtml'};
        getEndPcsReturnHtml(postData);
        $(".getOption").trigger('change');
    }else{
        if(typeof data.message === "object"){
            $(".error").html("");
            $.each( data.message, function( key, value ) {$("."+key).html(value);});
        }else{
            Swal.fire({ icon: 'error', title: data.message });
        }			
    }
}
</script>