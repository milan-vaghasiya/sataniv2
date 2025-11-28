<div class="col-md-12 form-group">
            <table class="table table-bordered">
                <tr class="bg-light">
                    <th style="width:33%">Prc No</th>
                        <td><?=$prsData->prc_number?></td>
                </tr>
                    <tr >
                    <th class="bg-light">Product</th>
                    <td ><?=$prsData->item_name?></td>
                </tr>
                <tr>
                    <th class="bg-light">Currunt Process</th>
                    <td><?=$prsData->process_name?></td>
                </tr>
                <tr > 
                    <th class="bg-light">Pend. Production qty</th>
                    <td ><?=((!empty($pending_production))?floatval($pending_production):'')?></td>
                </tr>
            </table>
        </div>
        <input type="hidden" name="id" id="id" value="">
        <input type="hidden" name="prc_id" id="prc_id" value="<?=$prsData->prc_id?>">
        <input type="hidden" name="prc_process_id" id="prc_process_id" value="<?=$prsData->accepted_process_id?>">
        <input type="hidden" name="process_id" id="process_id" value="<?=$prsData->current_process_id?>">
        <input type="hidden" name="accepted_qty" id="accepted_qty" value="<?=$prsData->accepted_qty?>">
        <div class="row">
            <div class="col-md-6 form-group">
                <label for="trans_type">Type</label>
                <select name="trans_type" id="trans_type" class="form-control">
                    <option value="1">Regular</option>
                    <option value="2">Rework</option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="production_qty">Production Qty</label>
                <input type="text" id="production_qty" class="form-control numericOnly req qtyCal" value="">

            </div>
            <div class="col-md-6 form-group">
                <label for="ok_qty">Ok Qty</label>
                <input type="text" name="ok_qty" id="ok_qty" class="form-control numericOnly req " value="" readonly>
                <div class="error batch_stock_error"></div>
            </div>
            <div class="col-md-6 form-group" >
                <label for="rej_found">Rejection Qty</label>
                <input type="text" name="rej_found" id="rej_found" class="form-control numericOnly qtyCal">
            </div>
            <div class="col-md-6 form-group">
                <label for="process_by">Process By</label>
                <select name="process_by" id="process_by" class="form-control select2">
                    <option value="1">Machine Process</option>
                    <option value="2">Department Process</option>
                </select>
            </div>
            <div class="col-md-6 form-group">
                <label for="processor_id">Machine</label>
                <select name="processor_id" id="processor_id" class="form-control select2">
                    <option value="">Select Machine</option>
                    <?php
                    if(!empty($machineList)):
                        foreach($machineList as $row):
                            ?><option value="<?=$row->id?>"> <?=((!empty($row->item_code))?'['.$row->item_code.'] ':'').$row->item_name?></option><?php
                        endforeach;
                    endif;
                    ?>
                </select>
            </div>
            <?php if(($prsData->current_process_id == $prcProcess[0]) && $prsData->mfg_type == 'Forging'): ?>
            <div class="col-md-6">
                <label for="wt_nos">Input Weight</label>
                <input type="text" class="form-control floatOnly" name="wt_nos" id="wt_nos" value="<?=$wt_nos?>">
            </div>
            <?php else : ?>
            <!--<input type="hidden" class="form-control floatOnly" name="wt_nos" id="wt_nos" value="<?=$wt_nos?>">-->
            <?php endif; ?>
            <div class="col-md-12 form-group" >
                <label for="remark">Remark</label>
                <input type="text" name="remark" id="remark" class="form-control">
            </div>
        </div>
