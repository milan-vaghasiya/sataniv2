<html>
    <head>
        <title>Purchase Order</title>
        <!-- Favicon icon -->
        <link rel="icon" type="image/png" sizes="16x16" href="<?=base_url();?>assets/images/favicon.png">
    </head>
    <body>
		<div class="row" style="margin:0px 6mm;">
            <div class="col-12">
				<table class="table bg-light-grey">
					<tr class="" style="letter-spacing: 2px;font-weight:bold;padding:2px !important; border-bottom:1px solid #000000;">
						<td style="width:33%;" class="fs-18 text-left">
							GSTIN: <?=$companyData->company_gst_no?>
						</td>
						<td style="width:33%;" class="fs-18 text-center"><b>Purchase Order</b></td>
						<td style="width:33%;" class="fs-18 text-right"></td>
					</tr>
				</table>               
                
                <table class="table item-list-bb fs-22" style="margin-top:5px;">
                    <tr >
                        <td rowspan="4" style="width:67%;vertical-align:top;">
                            <b>M/S. <?=$dataRow->party_name?></b><br>
                            <?= $partyData->party_address ." - ".$partyData->party_pincode ?><br><br>
                            <b>Kind. Attn. : <?=$dataRow->contact_person?></b> <br>
                            Contact No. & Email : <?=$dataRow->party_mobile.' & '.$partyData->party_email ?><br>
                            GSTIN : <?=$dataRow->gstin?>
                        </td>
                        <td>
                            <b>PO. No.</b>
                        </td>
                        <td>
                            <?=$dataRow->trans_number?>
                        </td>
                    </tr>
                    <tr>
				        <th class="text-left">PO Date</th>
                        <td><?=formatDate($dataRow->trans_date)?></td>
                    </tr>
                    <tr>
                        <th class="text-left">Supplier Ref. No.</th>
                        <td><?=$dataRow->doc_no?></td>
                    </tr>
                    <tr>
                        <th class="text-left">Ref. Date</th>
                        <td><?=(!empty($dataRow->doc_date)) ? formatDate($dataRow->doc_date) : ""?></td>
                    </tr>
                    <?php if(!empty($dataRow->delivery_address)): ?>
                        <tr>
                            <td colspan="3"><b>Delivery Address : </b><?= $dataRow->delivery_address.(!empty($dataRow->delivery_pincode) ? ' - '.$dataRow->delivery_pincode : "")?></td>
                        </tr>
                    <?php endif; ?>
                </table>
                
                <table class="table item-list-bb" style="margin-top:10px;">
					<thead>
						<tr class="bg-light">
							<th style="width:40px;">No.</th>
							<th class="text-left">Item Description</th>
							<th style="width:75px;">Grade</th>
							<th style="width:80px;">Qty</th>
							<th style="width:75px;">Rate<br><small>(<?=$partyData->currency?>)</small></th>
							<th style="width:60px;">Disc.<small>(%)</small></th>
                            <th style="width:60px;">GST <small>(%)</small></th>
							<th style="width:110px;">Taxable Amount<br><small>(<?=$partyData->currency?>)</small></th>
						</tr>
					</thead>
					<tbody>
						<?php
							$i=1;$totalQty = 0;$totalDisc=0;
							if(!empty($dataRow->itemList)):
								foreach($dataRow->itemList as $row):
									$indent = (!empty($row->ref_number)) ? '<br>Reference No:'.$row->ref_number : '';
									
									$item_remark = (!empty($row->item_remark))?'<br><small>'.$row->item_remark.'</small>':'';
									$specs = (!empty($row->item_description) ? nl2br($row->item_description):'');
									$row->tamt = $row->tamt - $row->tdisamt;
									
									$rowspan = (!empty($specs) ? '2': '1');
									
									echo '<tr>';
										echo '<td class="text-center" rowspan="'.$rowspan.'">'.$i++.'</td>';
										echo '<td>'.$row->item_name.$item_remark.'</td>';
										echo '<td class="text-center">'.$row->material_grade.'</td>';
										echo '<td class="text-right">'.sprintf('%0.2f',$row->tqty).' <small>'.$row->unit_name.'</small></td>';
										echo '<td class="text-center">'.$row->price.'</td>';
										echo '<td class="text-center">'.$row->tdisamt.' <small>('.$row->disc_per.'%)</small></td>';
										echo '<td class="text-center">'.$row->gst_per.'</td>';
										echo '<td class="text-right" rowspan="'.$rowspan.'">'.$row->tamt.'</td>';
									echo '</tr>';
									echo (!empty($specs)) ? '<tr><td colspan="6"><b>Specification : </b>'.$specs.'</td></tr>' : '';
									
									$totalQty += $row->tqty;
									$totalDisc += $row->tdisamt;
								endforeach;
							endif;
						?>
						<tr>
							<th colspan="3" class="text-right">Total Qty.</th>
							<th class="text-right"><?=sprintf('%.3f',$totalQty)?></th>
							<th class="text-right">Disc. Total</th>
							<th class="text-right"><?=numberFormatIndia(sprintf('%.2f',$totalDisc))?></th>
							<th class="text-right">Sub Total</th>
							<th class="text-right"><?=numberFormatIndia(sprintf('%.2f',$dataRow->taxable_amount))?></th>
						</tr>
						<?php
							$rwspan= 0; $srwspan = '';
							$beforExp = "";
							$afterExp = "";
							$invExpenseData = (!empty($dataRow->expenseData)) ? $dataRow->expenseData : array();
							foreach ($expenseList as $row) :
								$expAmt = 0;
								$amtFiledName = $row->map_code . "_amount";
								if (!empty($invExpenseData) && $row->map_code != "roff") :
									$expAmt = floatVal($invExpenseData->{$amtFiledName});
								endif;

								if(!empty($expAmt)):
									if ($row->position == 1) :
										if($rwspan == 0):
											$beforExp .= '<th class="text-right" colspan="2">'.$row->exp_name.'</th>
											<td class="text-right">'.sprintf('%.2f',$expAmt).'</td>';
										else:
											$beforExp .= '<tr>
												<th colspan="2" class="text-right">'.$row->exp_name.'</th>
												<td class="text-right">'.sprintf('%.2f',$expAmt).'</td>
											</tr>';
										endif;
										$rwspan++;
									endif;
								endif;
							endforeach;

							$taxHtml = '';
							foreach ($taxList as $taxRow) :
								$taxAmt = 0;
								$taxAmt = floatVal($dataRow->{$taxRow->map_code.'_amount'});
								if(!empty($taxAmt)):
									if($rwspan == 0):
										$taxHtml .= '<th colspan="2" class="text-right">'.$taxRow->name.'</th>
										<td class="text-right">'.sprintf('%.2f',$taxAmt).'</td>';
									else:
										$taxHtml .= '<tr>
											<th colspan="2" class="text-right">'.$taxRow->name.'</th>
											<td class="text-right">'.sprintf('%.2f',$taxAmt).'</td>
										</tr>';
									endif;
								
									$rwspan++;
								endif;
							endforeach;

							foreach ($expenseList as $row) :
								$expAmt = 0;
								$amtFiledName = $row->map_code . "_amount";
								if (!empty($invExpenseData) && $row->map_code != "roff") :
									$expAmt = floatVal($invExpenseData->{$amtFiledName});
								endif;

								if(!empty($expAmt)):
									if ($row->position == 2) :
										if($rwspan == 0):
											$afterExp .= '<th class="text-right" colspan="2">'.$row->exp_name.'</th>
											<td class="text-right">'.sprintf('%.2f',$expAmt).'</td>';
										else:
											$afterExp .= '<tr>
												<th colspan="2" class="text-right">'.$row->exp_name.'</th>
												<td class="text-right">'.sprintf('%.2f',$expAmt).'</td>
											</tr>';
										endif;
										$rwspan++;
									endif;
								endif;
							endforeach;

							$fixRwSpan = (!empty($rwspan))?3:0;
						?>
						<tr>
							<td class="text-left" colspan="5" rowspan="<?=$rwspan?>">
								<b>Note: </b> <br><?=$dataRow->remark?>
							</td>

							<?php if(empty($rwspan)): ?>
                                <th colspan="2" class="text-right">Round Off</th>
								<td class="text-right"><?=sprintf('%.2f',$dataRow->round_off_amount)?></td>
                            <?php endif; ?>
						</tr>
						<?=$beforExp.$taxHtml.$afterExp?>
						<tr>
							<td class="text-left" colspan="5" rowspan="<?=$fixRwSpan?>">
								<b>Amount In Words (<?=$partyData->currency?>) :</b> <br><?=numToWordEnglish(sprintf('%.2f',$dataRow->net_amount))?>
							</td>

							<?php if(empty($rwspan)): ?>
                                <th colspan="2" class="text-right">Grand Total <small>(<?=$partyData->currency?>)</small></th>
                                <th class="text-right"><?=numberFormatIndia(sprintf('%.2f',$dataRow->net_amount))?></th>
                            <?php else: ?>
                                <th colspan="2" class="text-right">Round Off</th>
                                <td class="text-right"><?=numberFormatIndia(sprintf('%.2f',$dataRow->round_off_amount))?></td>
                            <?php endif; ?>
						</tr>

						<?php if(!empty($rwspan)): ?>
						<tr>
							<th colspan="2" class="text-right">Grand Total <small>(<?=$partyData->currency?>)</small></th>
							<th class="text-right"><?=numberFormatIndia(sprintf('%.2f',$dataRow->net_amount))?></th>
						</tr>	
						<?php endif; ?>		
					</tbody>
                </table>

				<table class="table item-list-bb" style="margin-top:10px;">
				    <tr>
				        <th class="bg-light text-left" colspan="4">Delivery Schedule :</th>
				    </tr>
					<tr>
						<th class="bg-light text-center" style="width:10%;">No.</th>
						<th class="bg-light text-center" style="width:60%;">Item Description</th>
						<th class="bg-light text-center" style="width:15%;">Date/Week/Month</th>
						<th class="bg-light text-right" style="width:15%;">Qty.</th>
					</tr>
    				<?php
    					$j=1;
    					if(!empty($poTrans)):
    						foreach($poTrans as $row):
    							echo '<tr>';
    								echo '<td class="text-center">'.$j++.'</td>';
    								echo '<td class="text-center">'.$row->item_name.'</td>';
    								echo '<td class="text-center">'.(($row->schedule_type == 1)?formatDate($row->delivery_date):$row->sch_label).'</td>';
    								echo '<td class="text-right">'.$row->qty.'</td>';
    							echo '</tr>';
    						endforeach;
    					endif;
    				?>
				</table>
				
			    <table class="table top-table" style="margin-top:10px;">
                    <tr>
                        <th class="text-left">Terms & Conditions :-</th>
                    </tr>
                    <?php
                        if(!empty($termsData->condition)):
                            echo '<tr>';
                                echo '<td>'.$termsData->condition.'</td>';
                            echo '</tr>';
                        endif;
                    ?>
                </table>
                
                <htmlpagefooter name="lastpage">
					<!--<table class="table top-table" style="margin-top:10px;border-top:1px solid #545454;">
                        <tr>
                            <td style="width:70%;"></td>
                            <th class="text-center">For, <?=$companyData->company_name?></th>
                        </tr>
                        <tr>
                            <td></td>
                            <td class="text-center"><br> <br> Authorised By</td>
                        </tr>
                    </table>-->
                    <img src="<?=$lhb?>" class="img">
                </htmlpagefooter>
				<sethtmlpagefooter name="lastpage" value="on" />
            </div>
        </div>        
    </body>
    
</html>
