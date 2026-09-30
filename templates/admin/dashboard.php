<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $op_warehouse;
$op_nonce = wp_create_nonce( 'op_nonce' );
?>
<?php
/**
 * Created by PhpStorm.
 * User: anhvnit
 * Date: 12/4/16
 * Time: 23:40
 */

?>
<div class="op-admin-wrap wrap">
<h1 class="wp-heading-inline"><?php echo __( 'POS Dashboard', 'openpos' ); ?></h1>
<script type="text/javascript">
    (function($) {
        function esc(s){ return $('<div>').text(s == null ? '' : s).html(); }
        $('body').on('click','.reset-outlet-balance',function () {
            var warehouse_id = $(this).data('warehouse-id');
            var warehouse_name = $(this).data('warehouse-name');
            var confirm_msg = '<?php echo esc_js( __( 'This will reset the cash balance for', 'openpos' ) ); ?> "' + warehouse_name + '" <?php echo esc_js( __( 'to 0. Are you sure ?', 'openpos' ) ); ?>';
            if(confirm(confirm_msg))
            {
                $.ajax({
                    url: openpos_admin.ajax_url,
                    type: 'post',
                    dataType: 'json',
                    data:{action:'admin_openpos_reset_balance',op_nonce: '<?php echo $op_nonce?>',warehouse_id: warehouse_id},
                    success:function(data){
                        $('#openpos-cash-balance-'+warehouse_id).text(0);
                    }
                })
            }
        });
        $('body').on('click','.reset-outlet-debit-balance',function () {
            var warehouse_id = $(this).data('warehouse-id');
            var warehouse_name = $(this).data('warehouse-name');
            var confirm_msg = '<?php echo esc_js( __( 'This will reset the debit balance for', 'openpos' ) ); ?> "' + warehouse_name + '" <?php echo esc_js( __( 'to 0. Are you sure ?', 'openpos' ) ); ?>';
            if(confirm(confirm_msg))
            {
                $.ajax({
                    url: openpos_admin.ajax_url,
                    type: 'post',
                    dataType: 'json',
                    data:{action:'admin_openpos_reset_debit_balance',op_nonce: '<?php echo $op_nonce?>',warehouse_id: warehouse_id},
                    success:function(data){
                        $('#openpos-debit-balance-'+warehouse_id).text(0);
                    }
                })
            }
        });

        

        $(document).on('ready',function(){
            

        
            <?php
                $label = array();
                $sale_data = array();
                $transaction_data = array();
                $commision_data = array();
                foreach($chart_data as $index =>  $c)
                {
                    if($index == 0)
                    {
                        continue;
                    }
                    $label[] = $c[0];
                    $sale_data[] = round($c[1],wc_get_price_decimals());;
                    $transaction_data[] = $c[2];
                    $commision_data[] = round($c[3],wc_get_price_decimals());
                }
            ?>
            <?php  $pie_type = 'register'; ?>
            var data = {
                datasets: [{
                    data: [],
                    backgroundColor: [],
                }],
                labels: []
            };

            var ctx_pie = document.getElementById("myChart-pie").getContext("2d");
            var myPieChart = new Chart(ctx_pie, {
                type: 'pie',
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    title: {
                        display: true,
                        text: '<?php echo ($pie_type == 'register' ) ? __('Sale by Register','openpos') : __('Sale by Outlet','openpos'); ?>'
                    }
                }
            });

            // One pie chart per outlet for "Sales by Payment".
            var opOutletIds = <?php echo json_encode( array_map( function($w){ return (string) $w['id']; }, $op_warehouse->warehouses() ) ); ?>;
            var paymentCharts = {};
            opOutletIds.forEach(function(outletId){
                var el = document.getElementById('myChart-payment-' + outletId);
                if(!el){
                    return;
                }
                paymentCharts[outletId] = new Chart(el.getContext('2d'), {
                    type: 'pie',
                    data: { datasets: [{ data: [], backgroundColor: [] }], labels: [] },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: { position: 'bottom' }
                    }
                });
            });
            
            function loadChart(duration){
                    $.ajax({
                            url: openpos_admin.ajax_url,
                            type: 'post',
                            dataType: 'json',
                            data: {action: 'op_dashboard', op_nonce: '<?php echo $op_nonce; ?>',duration:duration},
                            beforeSend:function(){
                                $('.op-widget-ajax-data').addClass('loading');
                            },
                            success:function(response){
                                
                                var sale_data = response['sale_data'];
                                var register_data = response['register_data'];
                                var payment_data = response['payment_data'];
                                var sales_summary = response['sales_summary'];

                                //sales summary
                                if(sales_summary){
                                    $('#op-summary-total-sales').html(sales_summary.total_sales_formatted);
                                    $('#op-summary-total-profit').html(sales_summary.total_profit_formatted);
                                    $('#op-summary-total-count').text(sales_summary.total_count);
                                    var $body = $('#op-sales-summary-body').empty();
                                    (sales_summary.outlets || []).forEach(function(o){
                                        $body.append(
                                            '<tr><td>'+esc(o.name)+'</td><td>'+o.sales_formatted+'</td><td>'+o.profit_formatted+'</td><td>'+o.count+'</td></tr>'
                                        );
                                    });
                                }

                                //register chart

                                myPieChart.data = register_data.data;
                                myPieChart.options.title.text = register_data.label;
                                myPieChart.update();

                                //payment chart (per outlet)
                                Object.keys(paymentCharts).forEach(function(outletId){
                                    paymentCharts[outletId].data = { datasets: [{ data: [], backgroundColor: [] }], labels: [] };
                                });
                                (payment_data || []).forEach(function(p){
                                    var chart = paymentCharts[String(p.warehouse_id)];
                                    if(chart){
                                        chart.data = p.chart;
                                        chart.update();
                                    }
                                });
                                Object.keys(paymentCharts).forEach(function(outletId){
                                    paymentCharts[outletId].update();
                                });

                                $('.op-widget-ajax-data').removeClass('loading');
                            }
                    });
            }
        

            loadChart('<?php echo $duration; ?>');

            $(document).on('change','#op-dashboard-duration',function(){
                var duration = $(this).val();
                loadChart(duration);
            });
            
        });

    }(jQuery));
</script>

<div class="op-dashboard-content container">

    <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-8 col-lg-4">
            <select id="op-dashboard-duration" class="form-control">
                <option value="today" <?php selected($duration,'today'); ?>><?php echo __('Today','openpos'); ?></option>
                <option value="yesterday" <?php selected($duration,'yesterday'); ?>><?php echo __('Yesterday','openpos'); ?></option>
                <option value="this_week" <?php selected($duration,'this_week'); ?>><?php echo __('This Week','openpos'); ?></option>
                <option value="last_7_days" <?php selected($duration,'last_7_days'); ?>><?php echo __('Last 7 Days','openpos'); ?></option>
                <option value="this_month" <?php selected($duration,'this_month'); ?>><?php echo __('This Month','openpos'); ?></option>
                <option value="last_30_days" <?php selected($duration,'last_30_days'); ?>><?php echo __('Last 30 days','openpos'); ?></option>
            </select>
        </div>
        <div class="col-md-8 col-sm-6 col-xs-4 col-lg-8"><a href="<?php echo $pos_url; ?>"class="button-primary btn-default pull-right" target="_blank"><?php echo __('Goto POS','openpos'); ?></a></div>
    </div>
    <div class="row">
        
            <div class="col-md-7 col-sm-7 col-xs-12 col-lg-7 op-widget-container">
                <div class="op-widget-content op-widget-ajax-data">
                    <div class="title"><label><?php echo __('Sales Summary','openpos'); ?></label></div>
                    <div class="op-sales-summary-totals">
                        <div class="op-sales-summary-tile">
                            <span id="op-summary-total-sales">-</span>
                            <label><?php echo __('Total Sales','openpos'); ?></label>
                        </div>
                        <div class="op-sales-summary-tile">
                            <span id="op-summary-total-profit">-</span>
                            <label><?php echo __('Total Profit','openpos'); ?></label>
                        </div>
                        <div class="op-sales-summary-tile">
                            <span id="op-summary-total-count">-</span>
                            <label><?php echo __('Number of Sales','openpos'); ?></label>
                        </div>
                    </div>
                    <div class="op-import-tablewrap">
                        <table class="table op-sales-summary-table">
                            <thead><tr>
                                <th><?php echo __('Outlet','openpos'); ?></th>
                                <th><?php echo __('Sales','openpos'); ?></th>
                                <th><?php echo __('Profit','openpos'); ?></th>
                                <th><?php echo __('Sales Count','openpos'); ?></th>
                            </tr></thead>
                            <tbody id="op-sales-summary-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="col-md-5 col-sm-5 col-xs-12 col-lg-5 op-widget-container">
                <div class="op-widget-content op-widget-ajax-data">
                    <div class="op-chart-canvas-wrap op-chart-canvas-wrap-lg"><canvas id="myChart-pie"></canvas></div>
                </div>
            </div>
        
    </div>
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12 col-lg-12 op-widget-container">
            <div class="op-widget-content op-widget-ajax-data">
                <div class="title"><label><?php echo __('Sales by Payment','openpos'); ?></label></div>
                <div class="row op-payment-outlet-charts">
                    <?php foreach($op_warehouse->warehouses() as $op_wh): ?>
                        <div class="col-md-6 col-sm-6 col-xs-12 col-lg-6 op-payment-outlet-chart-col">
                            <div class="op-payment-outlet-name"><?php echo esc_html($op_wh['name']); ?></div>
                            <div class="op-chart-canvas-wrap"><canvas id="myChart-payment-<?php echo esc_attr($op_wh['id']); ?>"></canvas></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class=" row">
        <div class="last-orders col-md-8 col-sm-8 col-xs-12 col-lg-8 op-widget-container" >
            <div class="op-widget-content">
                <div class="title"><label><?php echo __('Last Orders','openpos'); ?></label></div>
                <div id="table_div_latest_orders">
                <table class="table table-bordered" style="width: 100%;" id="lastest-order">
                    <thead>
                        <tr>
                        <th><?php echo __('#','openpos'); ?></th>
                        <th><?php echo __('Customer','openpos'); ?></th>
                        <th><?php echo __('Grand Total','openpos'); ?></th>
                        <th><?php echo __('Sale By','openpos'); ?></th>
                        <th><?php echo __('Created At','openpos'); ?></th>
                        <th><?php echo __('Status','openpos'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dashboard_data['order'] as $order): ?>
                        <tr>
                            <td><?php echo $order['view']; ?></td>
                            <td><?php echo $order['customer_name']; ?></td>
                            <td><?php echo $order['total']; ?></td>
                            <td><?php echo $order['cashier']; ?></td>
                            <td><?php echo $order['created_at']; ?></td>
                            <td class="order_status"><?php echo $order['status']; ?></td>
                        </tr>
                        <?php endforeach;   ?>
                    </tbody>
                </table>
                </div>
            </div>
        </div>
        <div class="total col-md-4 col-sm-4 col-xs-12 col-lg-4 op-widget-container">
            <div class="op-widget-content real-content-container">
                <div class="title"><label><?php echo __('Cash Balance per Outlet','openpos'); ?></label> <a class="op-audit-log-link" href="<?php echo admin_url('admin.php?page=op-audit-log'); ?>"><?php echo __('View audit log','openpos'); ?></a></div>
                <?php if(!empty($dashboard_data['outlet_balance'])): ?>
                    <?php foreach($dashboard_data['outlet_balance'] as $outlet): ?>
                        <div class="op-outlet-balance-block">
                            <div class="op-outlet-balance-name"><?php echo esc_html($outlet['name']); ?></div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12">
                                    <ul id="total-details">
                                        <li>
                                            <div class="field-title" style="text-align: center;">
                                                <span id="openpos-cash-balance-<?php echo esc_attr($outlet['id']); ?>"><?php echo $outlet['balance_formatted']; ?></span>
                                                <a href="javascript:void(0);" class="reset-outlet-balance" data-warehouse-id="<?php echo esc_attr($outlet['id']); ?>" data-warehouse-name="<?php echo esc_attr($outlet['name']); ?>" style="outline: none;display: block;border:none;" title="<?php echo esc_attr__('Reset Balance','openpos'); ?>">
                                                    <img src="<?php echo OPENPOS_URL; ?>/assets/images/reset.png" height="24px" />
                                                </a>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <?php if(isset($dashboard_data['debt_balance'])): ?>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12">
                                        <ul id="total-details">
                                            <li>
                                                <div class="field-title" style="text-align: center;color:red;">
                                                    <span id="openpos-debit-balance-<?php echo esc_attr($outlet['id']); ?>"><?php echo $outlet['debit_formatted']; ?></span>
                                                    <a href="javascript:void(0);" class="reset-outlet-debit-balance" data-warehouse-id="<?php echo esc_attr($outlet['id']); ?>" data-warehouse-name="<?php echo esc_attr($outlet['name']); ?>" style="outline: none;display: block;border:none;" title="<?php echo esc_attr__('Reset Debit Balance','openpos'); ?>">
                                                        <img src="<?php echo OPENPOS_URL; ?>/assets/images/reset.png" height="24px" />
                                                    </a>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12">
                            <ul id="total-details">
                                <li>
                                    <div class="field-title" style="text-align: center;">
                                        <span id="openpos-cash-balance-0"><?php echo $dashboard_data['cash_balance']; ?></span>
                                        <a href="javascript:void(0);" class="reset-outlet-balance" data-warehouse-id="0" data-warehouse-name="<?php echo esc_attr__('Default online store','openpos'); ?>" style="outline: none;display: block;border:none;" title="Reset Balance">
                                            <img src="<?php echo OPENPOS_URL; ?>/assets/images/reset.png" height="34px" />
                                        </a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>
