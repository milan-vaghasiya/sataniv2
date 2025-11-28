
<div class="col-md-12">
    <div class="row">
        <div class="table-responsive">
            <table class="table table-bordered jpExcelTable">
                <thead class="text-center thead-dark">
                    <tr>
                        <th style="min-width:20px">#</th>
                        <th style="min-width:50px">Issue No</th>
                        <th style="min-width:50px">Issue Date</th>
                        <th style="min-width:50px">Heat No</th>
                        <th style="min-width:50px">Batch No</th>
                        <th style="min-width:50px">Qty.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if(!empty($itemData)):
                            $i=1;
                            foreach($itemData as $row){
                                echo '<tr class="text-center">
                                    <td>'.$i++.'</td>
                                    <td >'.$row->ref_no.'</td>
                                    <td>'.formatDate($row->ref_date).'</td>
                                    <td>'.$row->heat_no.'</td>
                                    <td>'.$row->batch_no.'</td>
                                    <td>'.$row->qty.'</td>
                                </tr>';
                            }
                        endif;
                   ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
