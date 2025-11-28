<form data-res_function="getBomResponse">
    <input type="hidden" id="prc_id" name="prc_id" value="<?=$prc_id?>">
    <div class="error general_error"></div>
    <table id='bomTable' class="table table-bordered jpExcelTable mb-5">  
        <thead class="text-center">
            <tr>
                <th style="min-width:20px">#</th>
                <th style="min-width:100px">Item</th>
                <th style="min-width:100px">Process</th>
                <th style="min-width:100px">Qty</th>
            </tr>
        </thead>
        <tbody id="bomTbodyData" class="text-center">
            <?php
            if(!empty($kitData)){
                $groupedkit = array_reduce($kitData, function($group, $kit) { $group[$kit->group_name][] = $kit;  return $group; }, []);
                $i=1;
                foreach ($groupedkit as $group => $kitArray){
                    $options = '';
                    ?>
                        <tr class="bg-light">
                        
                        <td colspan="4"><?=$group?></td>
                    </tr>
                    <?php
                    foreach ($kitArray as $row){
                       
                        $item_id = ""; $ppc_qty = ""; $process_id ="";$id="";$multi_heat = ""; $production_qty = 0;$item_name="";
                        $disabled = "disabled";$checked = "";
                        if(!empty($prcBom)){
                            $bomkey = array_search($row->ref_item_id,array_column($prcBom,'item_id'));
                            $item_id = $prcBom[$bomkey]->item_id; 
                            $item_name = $prcBom[$bomkey]->item_name; 
                            $ppc_qty = $prcBom[$bomkey]->ppc_qty; 
                            $process_id =$prcBom[$bomkey]->process_id;
                            $multi_heat =$prcBom[$bomkey]->multi_heat;
                            $production_qty =$prcBom[$bomkey]->production_qty;
                            $id=$prcBom[$bomkey]->id;
                            
                            
                        }
                       if(!empty($id)){ $disabled = "";$checked = "checked";}
                        ?>
                        <tr>
                            <td>
                                <?php
                                if($production_qty == 0){
                                    ?>
                                     <input type="checkbox" id="md_checkbox_<?=$i?>" value="" class="filled-in chk-col-success bomCheck" data-rowid ="<?=$i?>" <?=$checked?> ><label for="md_checkbox_<?=$i?>" class="mr-10" ></label>
                                    <?php
                                }
                                ?>
                               

                                <input type="hidden" name="id[]" id="id<?=$i?>" value="<?=$id?>" class="bomData<?=$i?>"  <?=$disabled?>> 
                                <input type="hidden" name="bom_group[]" id="bom_group<?=$i?>" value="<?=$group?>" class="bomData<?=$i?>"  <?=$disabled?>>
                                <input type="hidden" name="item_id[]" id="item_id<?=$i?>" value="<?=$row->ref_item_id?>" class="bomData<?=$i?>"  <?=$disabled?>>
                                <input type="hidden" name="ppc_qty[]" id="ppc_qty<?=$i?>" value="<?=$row->qty?>" class="bomData<?=$i?>"  <?=$disabled?>>
                                <input type="hidden" name="process_id[]" id="process_id<?=$i?>" value="<?=$row->process_id?>" class="bomData<?=$i?>"  <?=$disabled?>>
                            </td>
                            <td><?=$row->item_name?></td>
                            <td><?=!empty($row->process_name)?$row->process_name:'Initial Stage'?></td>
                            <td><?=$row->qty?></td>
                        </tr>
                        <?php
                        $i++;
                    }
                }
            }else{
                ?>
                <th colspan="4" class="text-center">No data avaolable.</th>
                <?php
            }
            ?>
        </tbody>
    </table>
</form>

<script>
    $(document).ready(function(){
        /*$(document).on('change','.itemChange',function() {
            var row_id = $(this).find(":selected").data('row_id');
            var bom_qty = $(this).find(":selected").data('bom_qty');
            var process_id = $(this).find(":selected").data('process_id');
            $("#ppc_qty"+row_id).val(bom_qty);
            $("#process_id"+row_id).val(process_id);
        });*/
        $(document).on("click",".bomCheck",function(){
    		$(".bomCheck").map(function(){ 
    			var id = $(this).data('rowid');
    			if($(this).prop("checked") == true){ $(".bomData"+id).removeAttr('disabled'); }
    			else{ $(".bomData"+id).attr('disabled','disabled'); } 
    		})
        });
    });
    
    function getBomResponse(data,formId="prcMaterial"){ 
        if(data.status==1){
            $('#'+formId)[0].reset();
            var postData = {'prc_id':$("#prc_id").val()};closeModal(formId);
            Swal.fire({
                title: "Success",
                text: data.message,
                icon: "success",
                showCancelButton: false,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ok!"
            }).then((result) => {
                loadProcessDetail(postData);
            });
            
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