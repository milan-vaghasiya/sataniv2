
<div class="row">
	<div class="col-12">
		<table class="table top-table-border" style="margin-top:2px;">
			<tr>
				<td><b>Date: </b><?=(!empty($lineInspectData->insp_date)) ? formatDate($lineInspectData->insp_date) : ""?></td>
				<td><b>Batch No: </b><?=(!empty($lineInspectData->prc_number)) ? $lineInspectData->prc_number : ""?></td>
				<td><b>PRC Date: </b><?=(!empty($lineInspectData->prc_date)) ? formatDate($lineInspectData->prc_date) : ""?></td>
				
			</tr>
			<tr>
				<td><b>Part: </b><?=(!empty($lineInspectData->item_name)) ? $lineInspectData->item_name : ""?></td>
				<td><b>Machine: </b><?=(!empty($lineInspectData->machine_name)) ? $lineInspectData->machine_name : ""?></td>
				<td><b>Setup: </b><?=(!empty($lineInspectData->process_name)) ?$lineInspectData->process_name:""?></td>
			</tr>
			
		</table>
		
		<table class="table item-list-bb" style="margin-top:10px;">
			<tr style="text-align:center;">
				<th rowspan="2" style="width:5%;">#</th>
				<th rowspan="2">Parameter</th>
				<th rowspan="2">Specification</th>
				<th rowspan="2">Instrument</th>
				<th rowspan="2">Min</th>
				<th rowspan="2">Max</th>
				<th colspan="<?= $rcount ?>">Observation Samples</th>
			</tr>
			<tr>
				<?php echo $theadData; ?>
			</tr>
			<?php echo $tbodyData; ?>
		</table>
		
	</div>
</div>