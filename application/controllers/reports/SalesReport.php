<?php
class SalesReport extends MY_Controller{

    public function __construct(){
        parent::__construct();
        $this->data['headData']->pageTitle = "Sales Report";
        $this->data['headData']->controller = "reports/salesReport";
    }

    public function orderMonitoring(){
        $this->data['headData']->pageUrl = "reports/salesReport/orderMonitoring";
        $this->data['headData']->pageTitle = "ORDER MONITORING";
        $this->data['pageHeader'] = 'ORDER MONITORING';
        $this->data['startDate'] = getFyDate(date("Y-m-01"));
        $this->data['endDate'] = getFyDate(date("Y-m-t"));
        $this->data['partyList'] = $this->party->getPartyList(['party_category'=>1]);
        $this->load->view("reports/sales_report/order_monitoring",$this->data);
    }

    public function getOrderMonitoringData(){
        $data = $this->input->post();
        $result = $this->salesReport->getOrderMonitoringData($data);

        $tbody = '';$i=1;
        foreach($result as $row):
            $tbody .= '<tr>
                <td>'.$i.'</td>
                <td>'.formatDate($row->trans_date).'</td>
                <td>'.$row->trans_number.'</td>
                <td>'.$row->party_name.'</td>
                <td>'.$row->item_name.'</td>
                <td>'.floatval($row->qty).'</td>
                <td>'.$row->inv_date.'</td>
                <td>'.$row->inv_number.'</td>
                <td>'.$row->inv_qty.'</td>
                <td>'.$row->deviation_days.'</td>
                <td>'.floatVal($row->deviation_qty).'</td>
            </tr>';
            $i++;
        endforeach;

        $this->printJson(['status'=>1,'tbody'=>$tbody]);
    }

    public function salesAnalysis(){
        $this->data['headData']->pageUrl = "reports/salesReport/salesAnalysis";
        $this->data['headData']->pageTitle = "SALES ANALYSIS";
        $this->data['pageHeader'] = 'SALES ANALYSIS';
        $this->data['startDate'] = getFyDate(date("Y-m-01"));
        $this->data['endDate'] = getFyDate(date("Y-m-t"));
        /* $this->data['partyList'] = $this->party->getPartyList(['party_category'=>1]);
        $this->data['itemList'] = $this->item->getItemList(['item_type'=>1]); */
        $this->load->view("reports/sales_report/sales_analysis",$this->data);
    }

    public function getSalesAnalysisData(){
        $data = $this->input->post();
        $result = $this->salesReport->getSalesAnalysisData($data);

        $thead = $tbody = $tfoot = ''; $i=1;
        if($data['report_type'] == 1):
            $thead .= '<tr>
                <th>#</th>
                <th class="text-left">Customer Name</th>
                <th class="text-right">Taxable Amount</th>
                <th class="text-right">GST Amount</th>
                <th class="text-right">Net Amount</th>
            </tr>';

            $taxableAmount = $gstAmount = $netAmount = 0;
            foreach($result as $row):
                $tbody .= '<tr>
                    <td>'.$i.'</td>
                    <td class="text-left">'.$row->party_name.'</td>
                    <td class="text-right">'.floatval($row->taxable_amount).'</td>
                    <td class="text-right">'.floatval($row->gst_amount).'</td>
                    <td class="text-right">'.floatval($row->net_amount).'</td>
                </tr>';
                $i++;
                $taxableAmount += floatval($row->taxable_amount);
                $gstAmount += floatval($row->gst_amount);
                $netAmount += floatval($row->net_amount);
            endforeach;

            $tfoot .= '<tr>
                <th colspan="2" class="text-right">Total</th>
                <th class="text-right">'.$taxableAmount.'</th>
                <th class="text-right">'.$gstAmount.'</th>
                <th class="text-right">'.$netAmount.'</th>
            </tr>';
        else:
            $thead .= '<tr>
                <th>#</th>
                <th class="text-left">Item Name</th>
                <th class="text-right">Qty.</th>
                <th class="text-right">Price</th>
                <th class="text-right">Taxable Amount</th>
            </tr>';

            $totalQty = $taxableAmount = 0;
            foreach($result as $row):
                $tbody .= '<tr>
                    <td>'.$i.'</td>
                    <td class="text-left">'.$row->item_name.'</td>
                    <td class="text-right">'.floatVal($row->qty).'</td>
                    <td class="text-right">'.floatVal($row->price).'</td>
                    <td class="text-right">'.floatVal($row->taxable_amount).'</td>
                </tr>';
                $i++;
                $totalQty += floatval($row->qty);
                $taxableAmount += floatval($row->taxable_amount);
            endforeach;

            $tfoot .= '<tr>
                <th colspan="2" class="text-right">Total</th>
                <th class="text-right">'.$totalQty.'</th>
                <th></th>
                <th class="text-right">'.$taxableAmount.'</th>
            </tr>';
        endif;

        $this->printJson(['status'=>1,'thead'=>$thead,'tbody'=>$tbody,'tfoot'=>$tfoot]);
    }
}
?>