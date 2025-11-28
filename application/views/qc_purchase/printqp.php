<div class="row" style="margin:0px 6mm;">
	<div class="col-12">
	    <table class="table bg-light-grey">
			<tr class="" style="letter-spacing: 2px;font-weight:bold;padding:2px !important; border-bottom:1px solid #000000;">
				<td style="width:33%;" class="fs-18 text-left">
					GSTIN: <?=$companyData->company_gst_no?>
				</td>
				<td style="width:33%;" class="fs-18 text-center">Purchase Order</td>
				<td style="width:33%;" class="fs-18 text-right"></td>
			</tr>
		</table>               
                
		
		<table class="table top-table-border" style="margin-top:2px;">
			<tr>
		        <td rowspan="4" style="width:67%;vertical-align:top;">
                    <b>M/S. <?=$poData->party_name?></b><br>
                    <?= $poData->party_address ." - ".$poData->party_pincode ?><br><br>
                    <b>Kind. Attn. : <?=$poData->contact_person?></b> <br>
                    Contact No. & Email : <?=$poData->party_mobile.' & '.$poData->party_email ?><br>
                    GSTIN : <?=$poData->gstin?>
                </td>
                        
				<th style="width:12%;vertical-align:center;">PO No.</th>
				<td style="width:18%;vertical-align:center;"><?=$poData->trans_number?></td>
			</tr>
			<tr>
				<th>PO Date</th><td><?=formatDate($poData->trans_date)?></td>
			</tr>
            <tr>
                <th class="text-left">Ref. No.</th>
                <td><?=$poData->doc_no?></td>
            </tr>
            <tr>
                <th class="text-left">Ref. Date</th>
                <td><?=(!empty($poData->doc_date)) ? formatDate($poData->doc_date) : ""?></td>
            </tr>
		</table>
		
		<table class="table item-list-bb" style="margin-top:10px;">
			<tr>
				<th style="width:40px;">No.</th>
				<th class="text-left">Item Description</th>
				<th style="width:60px;">GST <small>%</small></th>
				<th style="width:100px;">Qty</th>
				<th style="width:50px;">UOM</th>
				<th style="width:60px;">Rate<br><small>(INR)</small></th>
				<th style="width:110px;">Amount<br><small>(INR)</small></th>
			</tr>
			<?php
				$i=1;$totalQty = 0;$migst=0;$mcgst=0;$msgst=0;
				if(!empty($poData->itemList)):
					foreach($poData->itemList as $row):
						$itemDesc = '['.$row->category_code.'] '.$row->category_name.'<br>';
						if(!empty($row->size)){$itemDesc .= '<b>SIZE : </b>'.$row->size;}
						if(!empty($row->size) AND !empty($row->make)){$itemDesc .= ' | ';}
						if(!empty($row->make)){$itemDesc .= '<b>MAKE : </b>'.$row->make;}
						$itemDesc .= (!empty($row->delivery_date)) ? '<br><small>Delivery Date :'.formatDate($row->delivery_date).'</small>' : '';
						
						echo '<tr>';
							echo '<td class="text-center">'.$i++.'</td>';
							echo '<td>'.$itemDesc.'</td>';
							echo '<td class="text-center">'.$row->igst_per.'</td>';
							echo '<td class="text-right">'.$row->qty.'</td>';
							echo '<td class="text-center">Nos</td>';
							echo '<td class="text-right">'.$row->price.'</td>';
							echo '<td class="text-right">'.$row->amount.'</td>';
						echo '</tr>';
						$totalQty += $row->qty;
						if($row->igst_per > $migst){$migst=$row->igst_per;$mcgst=$row->cgst_per;$msgst=$row->sgst_per;}
					endforeach;
				endif;
			?>
			<tr>
				<th colspan="3" class="text-right">Total Qty.</th>
				<th class="text-right"><?=sprintf('%.3f',$totalQty)?></th>
				<th colspan="2" class="text-right">Sub Total</th>
				<th class="text-right"><?=numberFormatIndia(sprintf('%.2f',$poData->taxable_amount))?></th>
			</tr>
			<?php
				$rwspan= 0; $srwspan = '';
				$beforExp = "";
				$afterExp = "";
				$invExpenseData = (!empty($poData->expenseData)) ? $poData->expenseData : array();
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
					$taxAmt = floatVal($poData->{$taxRow->map_code.'_amount'});
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
				<th class="text-left" colspan="4" rowspan="<?=$rwspan?>">
					<b>Note: </b> <?=$poData->remark?>
				</th>

				<?php if(empty($rwspan)): ?>
                    <th colspan="2" class="text-right">Round Off</th>
					<td class="text-right"><?=sprintf('%.2f',$poData->round_off_amount)?></td>
                <?php endif; ?>
			</tr>
			<?=$beforExp.$taxHtml.$afterExp?>
			<tr>
				<th class="text-left" colspan="4" rowspan="<?=$fixRwSpan?>">
					Amount In Words : <br><?=numToWordEnglish(sprintf('%.2f',$poData->net_amount))?>
				</th>

				<?php if(empty($rwspan)): ?>
                    <th colspan="2" class="text-right">Grand Total</th>
                    <th class="text-right"><?=numberFormatIndia(sprintf('%.2f',$poData->net_amount))?></th>
                <?php else: ?>
                    <th colspan="2" class="text-right">Round Off</th>
                    <td class="text-right"><?=sprintf('%.2f',$poData->round_off_amount)?></td>
                <?php endif; ?>
			</tr>

			<?php if(!empty($rwspan)): ?>
			<tr>
				<th colspan="2" class="text-right">Grand Total</th>
				<th class="text-right"><?=numberFormatIndia(sprintf('%.2f',$poData->net_amount))?></th>
			</tr>	
			<?php endif; ?>		
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
	</div>
</div>