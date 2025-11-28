<html>
    <head>
        <title>Delivery Challan</title>
        <!-- Favicon icon -->
        <link rel="icon" type="image/png" sizes="16x16" href="<?=base_url();?>assets/images/favicon.png">
    </head>
    <body>
        <div class="row" style="margin:0px 6mm;">
            <div class="col-12">
                <table class="table bg-light-grey">
					<tr class="" style="letter-spacing: 2px;font-weight:bold;padding:2px !important; border-bottom:1px solid #000000;">
						<td style="width:33%;" class="fs-16 text-left">GSTIN: <?=$companyData->company_gst_no?></td>
						<td style="width:34%;" class="fs-18 text-center">DELIVERY CHALLAN</td>
						<td style="width:33%;" class="fs-16 text-right"></td>
					</tr>
				</table> 
                
                <table class="table item-list-bb fs-22" style="margin-top:5px;">
                    <tr >
                        <td rowspan="4" style="width:67%;vertical-align:top;">
                            <b>M/S. <?=$dataRow->party_name?></b><br>
                            <?=(!empty($dataRow->delivery_address) ? $dataRow->delivery_address : '')?><br>
                            <b>Kind. Attn. : <?=$partyData->contact_person?></b> <br>
                            Contact No. : <?=$partyData->party_mobile?><br>
                            Email : <?=$partyData->party_email?><br><br>
                            GSTIN : <?=$dataRow->gstin?>
                        </td>
                        <td>
                            <b>DC. No.</b>
                        </td>
                        <td>
                            <?=$dataRow->trans_number?>
                        </td>
                    </tr>
                    <tr>
				        <th class="text-left">DC Date</th>
                        <td><?=formatDate($dataRow->trans_date)?></td>
                    </tr>
                    <tr>
                        <th class="text-left">Cust. PO. No.</th>
                        <td><?=$dataRow->doc_no?></td>
                    </tr>
                    <tr>
                        <th class="text-left">Cust. PO. Date</th>
                        <td><?=(!empty($dataRow->doc_date)) ? formatDate($dataRow->doc_date) : ""?></td>
                    </tr>
                </table>
                
                <table class="table item-list-bb" style="margin-top:10px;">                    
                    <thead>
                        <tr>
                            <th style="width:5%;">No.</th>
                            <th style="width:50%;">Item Description</th>
                            <th style="width:15%;">HSN/SAC</th>
                            <th style="width:15%;">Qty</th>
                            <th style="width:15%;">Packing Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i=1; $totalQty=0;
                            if(!empty($dataRow->itemList)):
                                foreach($dataRow->itemList as $row):
                                    $item_remark = (!empty($row->item_remark))?'<br><small>Remark : '.$row->item_remark.'</small>':'';
                                    
                                    $batchList = json_decode($row->batch_detail);
                                    $batchData = '';
                                    if (!empty($batchList)) {
                                        $batchDetails = [];                                        
                                        foreach ($batchList as $batch) {
                                            $batchDetails[] = $batch->batch_no.' [ Qty : '.floatval($batch->batch_qty).' ]';
                                        }                                        
                                        $batchData = '<br><small><b>Batch Detail : </b>' . implode(', ', $batchDetails) . '</small>';
                                    }
                                    $rowspan = (!empty($batchData) ? '2' : '1');
                                    $rowspan = (!empty($row->nature_of_process) ? ($rowspan + 1) : $rowspan);
                                    
                                    echo '<tr>';
                                        echo '<td class="text-center" rowspan='.$rowspan.'>'.$i++.'</td>';
                                        echo '<td class="text-left"><b>'.$row->item_name.'</b>'.$item_remark.'</td>';
                                        echo '<td class="text-center">'.$row->hsn_code.'</td>';
                                        echo '<td class="text-center">'.floatval($row->qty).' <small>'.$row->unit_name.'</small></td>';
                                        echo '<td class="text-center">'.(($row->packing_type == 1) ? 'Box' : 'Wire Mesh Jaal').'</td>';
                                    echo '</tr>';

                                    echo (!empty($row->nature_of_process) ? '<tr><td colspan="4">'.$row->nature_of_process.'</td></tr>' : '');

                                    echo (!empty($batchData) ? '<tr><td colspan="4"><b>Batch Detail : </b>'.implode(', ', $batchDetails).'</td></tr>' : '');

                                    $totalQty += $row->qty;
                                endforeach;
                            endif;
                        ?>
                        <tr>
                            <th colspan="3" class="text-right">Total Qty.</th>
                            <th class="text-center"><?=floatval($totalQty)?></th>
                            <th class="text-right"></th>
                        </tr>                                 
                    </tbody>  
                </table> 
                		
                <table class="table top-table" style="margin-top:10px;">
                    <tr>
                        <th class="text-left">Terms & Conditions :-</th>
                    </tr>
                    <?php
                        if(!empty($termsData->condition)):
                            echo '<tr>';
                                echo '<td class=" fs-10">'.$termsData->condition.'</td>';
                            echo '</tr>';
                        endif;
                    ?>
                </table>
                
                <htmlpagefooter name="lastpage">
                    <div style="height: 120px; width: 100%; background-image: url('<?=$lhb?>'); background-size: cover; background-repeat: no-repeat; background-position: center;">
                        <table class="table top-table" style="margin-top:10px; border-top:1px solid #545454; width: 100%;">
                            <tr>
                                <td style="width:70%;"></td>
                                <th class="text-center">For, <?=$companyData->company_name?></th>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <?=$dataRow->created_name?><br>Prepared By
                                </td>
                                <td class="text-center">
                                    <br><br>Authorised By
                                </td>
                            </tr>
                        </table>
                    </div>
                </htmlpagefooter>
                <sethtmlpagefooter name="lastpage" value="on" />

            </div>
        </div>        
    </body>
</html>