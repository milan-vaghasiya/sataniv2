<style>table, .table{width:100% !important;border-collapse:collapse !important;}td,th{border-collapse:collapse !important;}</style>
<div class="row">
	<div class="col-12">
		<table class="table" style="border-bottom:1px solid #000000;">
			<tr>
			    <td style="width:20%;"></td>
				<td class="text-uppercase text-center" style="font-size:1.3rem;font-weight:bold;width:40%;">PROCESS ROUTE CARD</td>
				<td class="text-uppercase text-right" style="font-size:1.3rem;font-weight:bold;width:20%;"></td>
			</tr>
		</table>
		<table class="table itemList DD tbl-fs-11">
			<tr class="text-left">
				<th style="width:15%">Batch No.</th>
				<td style="width:25%"><?= $prcData->prc_number ?></td>
				<th style="width:15%">Batch Quantity</th>
				<td style="width:15%"><?= floatval($prcData->prc_qty) ?></td>
				<th style="width:15%">Batch Date</th>
				<td style="width:15%"><?= formatDate($prcData->prc_date) ?></td>
			</tr>
		    <tr class="text-left">
				<th>Product Name</th>
				<td><?= $prcData->item_name ?></td>
				<th>Product No.</th>
				<td><?= $prcData->item_code ?></td>
				<th>Created By</th>
				<td><?= $prcData->emp_name ?></td>
			</tr>
            <tr class="text-left">
				<th>Remark</th>
				<td colspan="5"><?= $prcData->remark ?></td>
			</tr>
		</table>
		<h4 class="row-title">Material Detail:</h4>
		<table class="table itemList DD tbl-fs-11">
			<tr class="thead-gray">
				<th>Item Description</th>
				<th>Batch No</th>
				<th>Supplier</th>
				<th class="text-center" style="width:15%;">Issued Qty</th>
			</tr>
			<?php
			if (!empty($prcMaterialData)) :
                $i = 1;
				foreach ($prcMaterialData as $row) :
                    echo '<tr>';
                    echo '<td>' . $row->item_name . '</td>';
                    echo '<td>' . $row->batch_no . '</td>';
                    echo '<td>' . $row->supplier_name . '</td>';
                    echo '<td class="text-center">' . floatVal(abs($row->issue_qty)) . '</td>';
                    echo '</tr>';
				endforeach;
			else :
				echo '<tr><th class="text-center" colspan="2">Record Not Found !</th></tr>';
			endif;
			?>
		</table>
		<h4 class="row-title">Process Detail:</h4>
		<table class="table itemList pad5 tbl-fs-11">
			<tr class="text-center thead-gray">
				<th style="width:5%;">No.</th>
				<th class="text-left">Process Detail</th>
				<th style="width:12%;">Issued Qty</th>
				<th style="width:12%;">OK Qty</th>
				<th style="width:12%;">Rej. Qty</th>
				<th style="width:12%;">Pending Qty</th>
			</tr>
			<?php
			if (!empty($prcProcessData)) :
				$i = 1;
                if($prcData->status > 1):
                    foreach ($prcProcessData as $row) :
                        $currentProcess = !empty($row->current_process)?$row->current_process : 'Initial Stage';
                        $in_qty = (!empty($row->current_process_id))?(!empty($row->in_qty)?$row->in_qty:0):$row->ok_qty;
                        $ok_qty = !empty($row->ok_qty)?$row->ok_qty:0;
                        $rej_found_qty = !empty($row->rej_found)?$row->rej_found:0;
                        $rej_qty = !empty($row->rej_qty)?$row->rej_qty:0;
                        $rw_qty = !empty($row->rw_qty)?$row->rw_qty:0;
                        $pendingReview = $rej_found_qty - $row->review_qty;
                        $pending_production =($in_qty) - ($ok_qty+$rej_qty+$rw_qty+$pendingReview);

                        echo '<tr>';
                        echo '<td class="text-center">' . $i++ . '</td>';
                        echo '<td class="text-left">' . $currentProcess . '</td>';
                        echo '<td class="text-center">' . floatVal($in_qty) . '</td>';
                        echo '<td class="text-center">' . floatVal($ok_qty) . '</td>';
                        echo '<td class="text-center">' . floatVal($rej_qty) . '</td>';
                        echo '<td class="text-center">' . floatVal($pending_production) . '</td>';
                        echo '</tr>';
                    endforeach;
                else:
                    foreach($prcProcessData as $key=>$row){
                        echo '<tr>';
                        echo '<td class="text-center">' . $i++ . '</td>';
                        echo '<td class="text-left">' . $row->process_name . '</td>';
                        echo '<td class="text-center">0</td>';
                        echo '<td class="text-center">0</td>';
                        echo '<td class="text-center">0</td>';
                        echo '<td class="text-center">0</td>';
                        echo '</tr>';
                    }
                endif;
			else :
				echo '<tr><th class="text-center" colspan="6">Record Not Found !</th></tr>';
			endif;
			?>

		</table>
	</div>
</div>