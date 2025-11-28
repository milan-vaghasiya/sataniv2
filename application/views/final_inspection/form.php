
<form>
    <div class="col-md-12">
        <div class="row">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Batch No. </th>
                            <th>Item Name</th>
                            <th>Stock Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?=$dataRow->prc_number?></td>
                            <td><?=$dataRow->item_name?></td>
                            <td><?=$batchData->qty?></td>
                        </tr>
                    </tbody>
                </table>
                <hr>
                <div class="row">
                    <input type="hidden" name="id" id="id" value="" />
                    <input type="hidden" name="prc_id" id="prc_id" value="<?=(!empty($dataRow->id))?$dataRow->id:""?>" />
                    <input type="hidden" name="trans_no" value="<?=(!empty($dataRow->trans_no))?$dataRow->trans_no:$trans_no?>" />
                    <input type="hidden" name="trans_number" value="<?=(!empty($dataRow->trans_number))?$dataRow->trans_number:$trans_number?>" />
                    <input type="hidden" name="item_id" id="item_id" value="<?=(!empty($dataRow->item_id))?$dataRow->item_id:""?>" />
                    <input type="hidden" name="prc_number" id="prc_number" value="<?=(!empty($dataRow->prc_number))?$dataRow->prc_number:""?>" />
                    <input type="hidden" name="heat_no" id="heat_no" value="<?=(!empty($batchData->heat_no))?$batchData->heat_no:""?>" />
                    <input type="hidden" name="report_type" id="report_type" value="2" />

                    <?php $sample_size = 5?>
                    <div class="col-md-2 form-group">
                        <label for="insp_date">Date</label>
                        <input type="date" name="insp_date" id="insp_date" class="form-control req" value="<?=date('Y-m-d')?>" />
                    </div>  
                    <div class="col-md-2 form-group">
                        <label for="inspected_qty">Inspected Qty</label>
                        <input type="text" name="inspected_qty" id="inspected_qty" class="form-control floatonly req" value="0" />
                    </div>
                    <div class="col-md-2 form-group">
                        <label for="ok_qty">Ok Qty</label>
                        <input type="text" name="ok_qty" id="ok_qty" class="form-control floatonly req" value="0" />
                        <div class="error ok_qty"></div>
                    </div>   
                    <div class="col-md-2 form-group">
                        <label for="rej_found">Reject Found</label>
                        <input type="text" name="rej_found" id="rej_found" class="form-control floatonly req" value="0" />
                    </div>  
                    <div class="col-md-2 form-group">
                        <label for="rej_qty">Reject Qty</label>
                        <input type="text" name="rej_qty" id="rej_qty" class="form-control floatonly" value="0" />
                    </div>         
                    <div class="col-md-2 form-group">  
                        <label for="sampling_qty">Sampling Qty.</label>
                        <div class="input-group">
                            <input type="text" name="sampling_qty" id="sampling_qty" class="form-control floatOnly req" value="<?=$sample_size?>" />
                            <button type="button" class="btn waves-effect waves-light btn-success float-center loaddata" title="Load Data">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>   
                    <div class="error general"></div>
                    <div class="table-responsive">
                        <table id="preDispatchtbl" class="table table-bordered generalTable">
                            <thead class="thead-info" id="theadData">
                                <tr style="text-align:center;">
                                    <th rowspan="2" style="width:5%;">#</th>
                                    <th rowspan="2" style="width:20%">Parameter</th>
                                    <th rowspan="2" style="width:20%">Specification</th>
                                    <th rowspan="2" style="width:10%">Instrument</th>
                                    <th rowspan="2" style="width:15%">Min</th>
                                    <th rowspan="2" style="width:15%">Max</th>
                                    <th colspan="<?= $sample_size?>">Observation on Samples</th>
                                </tr>
                                <tr style="text-align:center;">
                                    <?php for($j=1;$j<=$sample_size;$j++):?> 
                                        <th><?= $j ?></th>
                                    <?php endfor;?>    
                                </tr>
                            </thead>
                            <tbody id="tbodyData">
                                <?php
                                $i=1;  $tbodyData="";
                                if(!empty($paramData)):
                                    foreach($paramData as $row):
                                        $obj = new StdClass;
                                        if(!empty($inInspectData)):
                                            $obj = json_decode($inInspectData->observation_sample); 
                                        endif;
                                        $tbodyData .= '<tr class="text-center">
                                                <td>'.$i++.'</td>
                                                <td style="width:20px;">'.$row->parameter.'</td>
                                                <td style="width:20px;">'.$row->specification.'</td>
                                                <td style="width:20px;">'.$row->instrument.'</td>
                                                <td style="width:20px;">'.$row->min.'</td>
                                                <td style="width:20px;">'.$row->max.'</td>';
                                        for($c=0;$c<$sample_size;$c++):
                                            if(!empty($obj->{$row->id})):
                                                $tbodyData .= '<td style="min-width:100px;"><input type="text" name="sample'.($c+1).'_'.$row->id.'" class="form-control text-center"  value="'.$obj->{$row->id}[$c].'" data-row_id ="'.$i.'" ></td>';
                                            else:
                                                $tbodyData .= '<td style="min-width:100px;"><input type="text" name="sample'.($c+1).'_'.$row->id.'" class="form-control text-center value="" data-row_id ="'.$i.'"></td>';
                                            endif;
                                        endfor;
                                    endforeach;
                                else:
                                    $tbodyData .=  '<td class="text-center" colspan="11">No data available.</td>';
                                endif;
                                echo $tbodyData;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function() {
        
        $(document).on('click', '.loaddata', function(e) {
            var item_id = $('#item_id').val();
            var sampling_qty = $("#sampling_qty").val();
            if (sampling_qty) {
                $.ajax({
                    url: base_url + controller + '/getFinalInspectionData',
                    data: {
                        item_id: item_id,
                        sampling_qty:sampling_qty
                    },
                    type: "POST",
                    dataType: 'json',
                    success: function(data) {
                        $("#theadData").html(data.theadData);
                        $("#tbodyData").html(data.tbodyData);
                    }
                });
            }
        });

    });

</script>