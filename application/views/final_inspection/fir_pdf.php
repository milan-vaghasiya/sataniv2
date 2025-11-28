<div class="row">
	<div class="col-12">
        <table class="table item-list-bb" style="margin-top:2px;">
            <tr>
				<td><b>Date : </b><?=(!empty($firData->insp_date)) ? formatDate($firData->insp_date) : ""?></td>
				<td><b>FIR No : </b><?=(!empty($firData->trans_number)) ? $firData->trans_number : ""?></td>
				<td><b>Batch No : </b><?=(!empty($firData->prc_number)) ? $firData->prc_number : ""?></td>				
			</tr>
			<tr>
				<td><b>Item Code : </b><?=(!empty($firData->item_code)) ? $firData->item_code : ""?></td>
				<td colspan="2"><b>Item Name : </b><?=(!empty($firData->item_name)) ? $firData->item_name : ""?></td>
			</tr>
			<tr>
				<td><b>Inspected Qty : </b><?=(!empty($firData->inspected_qty)) ? floatval($firData->inspected_qty) : ""?></td>
				<td><b>Ok Qty : </b><?=(!empty($firData->ok_qty)) ? floatval($firData->ok_qty) : ""?></td>
				<td><b>Rejected Qty : </b><?=(!empty($firData->rej_qty)) ? floatval($firData->rej_qty) : ""?></td>
			</tr>
		</table>

		<table class="table item-list-bb" style="margin-top:10px;">
		<?php $sample_size= (!empty($firData->sampling_qty))?floatval($firData->sampling_qty):5 ?>
		<tr style="text-align:center;">
			<th rowspan="2" >#</th>
			<th rowspan="2">Parameter</th>
			<th rowspan="2">Specification</th>
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
			<?php
				$tbodyData="";$i=1; 
				if(!empty($paramData)):
					foreach($paramData as $row):
						$obj = New StdClass;
						if(!empty($firData)):
							$obj = json_decode($firData->observation_sample);
						endif;
						$flag=false;$paramItems = '';
							$paramItems.= '<tr>
										<td style="text-align:center;">'.$i.'</td>
										<td style="text-align:center;">'.$row->parameter.'</td>
										<td style="text-align:center;">'.$row->specification.'</td>   
										<td style="text-align:center;">'.$row->instrument.'</td>
										<td style="text-align:center;">'.$row->min.'</td>
										<td style="text-align:center;">'.$row->max.'</td>';
							for($c=0;$c<$sample_size;$c++):
								if(!empty($obj->{$row->id})):
									$paramItems .= '<td style="text-align:center;">'.$obj->{$row->id}[$c].'</td>';
								endif;
								if(!empty($obj->{$row->id}[$c])){$flag=true;}
							endfor;
				// 			if($flag):
								$tbodyData .= $paramItems;$i++;
				// 			endif;
					endforeach;
				else:
					$tbodyData.= '<tr><td colspan="11" style="text-align:center;">No Data Found</td></tr>';
				endif;
				echo $tbodyData;
			?>
		</table>
	</div>
</div>