<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$total_pages = $per_page > 0 ? (int)ceil($total / $per_page) : 1;
$base_url = admin_url('admin.php?page=op-audit-log');
$build_url = function($args) use ($base_url,$filter_action,$filter_warehouse,$filter_from,$filter_to){
    $q = array(
        'op_action' => $filter_action,
        'op_warehouse' => $filter_warehouse,
        'op_from' => $filter_from,
        'op_to' => $filter_to,
    );
    $q = array_merge($q,$args);
    $q = array_filter($q, function($v){ return $v !== '' && $v !== null; });
    return $base_url.'&'.http_build_query($q);
};
?>
<div class="op-admin-wrap wrap op-audit-log-wrap">
    <h1 class="wp-heading-inline"><?php echo __('Audit Log','openpos'); ?></h1>
    <p class="op-import-lead"><?php echo __('A record of sensitive POS actions, such as resetting the cash or debit balance for an outlet.','openpos'); ?></p>

    <div class="op-import-card op-audit-filters">
        <form method="get" action="<?php echo admin_url('admin.php'); ?>">
            <input type="hidden" name="page" value="op-audit-log">
            <div class="row">
                <div class="col-sm-6 col-md-3 op-import-opt">
                    <label><?php echo __('Action','openpos'); ?></label>
                    <select name="op_action" class="form-control">
                        <option value=""><?php echo __('All actions','openpos'); ?></option>
                        <?php foreach($labels as $key => $lbl): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filter_action,$key); ?>><?php echo esc_html($lbl); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3 op-import-opt">
                    <label><?php echo __('Outlet','openpos'); ?></label>
                    <select name="op_warehouse" class="form-control">
                        <option value=""><?php echo __('All outlets','openpos'); ?></option>
                        <?php foreach($outlets as $o): ?>
                            <option value="<?php echo esc_attr($o['id']); ?>" <?php selected((string)$filter_warehouse,(string)$o['id']); ?>><?php echo esc_html($o['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3 op-import-opt">
                    <label><?php echo __('From date','openpos'); ?></label>
                    <input type="date" name="op_from" class="form-control" value="<?php echo esc_attr($filter_from); ?>">
                </div>
                <div class="col-sm-6 col-md-3 op-import-opt">
                    <label><?php echo __('To date','openpos'); ?></label>
                    <input type="date" name="op_to" class="form-control" value="<?php echo esc_attr($filter_to); ?>">
                </div>
            </div>
            <p>
                <button type="submit" class="btn btn-success"><?php echo __('Filter','openpos'); ?></button>
                <a class="btn btn-default" href="<?php echo esc_url($base_url); ?>"><?php echo __('Reset filter','openpos'); ?></a>
            </p>
        </form>
    </div>

    <div class="op-import-card">
        <div class="op-import-tablewrap">
            <table class="table op-audit-log-table">
                <thead><tr>
                    <th><?php echo __('Date & Time','openpos'); ?></th>
                    <th><?php echo __('User','openpos'); ?></th>
                    <th><?php echo __('Action','openpos'); ?></th>
                    <th><?php echo __('Outlet','openpos'); ?></th>
                    <th><?php echo __('Old Value','openpos'); ?></th>
                    <th><?php echo __('New Value','openpos'); ?></th>
                    <th><?php echo __('IP','openpos'); ?></th>
                </tr></thead>
                <tbody>
                    <?php if(empty($rows)): ?>
                        <tr><td colspan="7" style="text-align:center;"><?php echo __('No log entries found.','openpos'); ?></td></tr>
                    <?php else: foreach($rows as $r): ?>
                        <tr>
                            <td><?php echo esc_html( get_date_from_gmt( get_gmt_from_date($r->created_at), 'Y-m-d H:i' ) ); ?></td>
                            <td><?php echo esc_html($r->user_name ? $r->user_name : '#'.$r->user_id); ?></td>
                            <td><span class="op-audit-action op-audit-action-<?php echo esc_attr($r->action); ?>"><?php echo esc_html(isset($labels[$r->action]) ? $labels[$r->action] : $r->action); ?></span></td>
                            <td><?php echo esc_html($r->warehouse_name); ?></td>
                            <td><?php echo wc_price($r->old_value); ?></td>
                            <td><?php echo wc_price($r->new_value); ?></td>
                            <td class="op-audit-ip"><?php echo esc_html($r->ip_address); ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($total_pages > 1): ?>
            <div class="op-audit-pagination">
                <?php for($p = 1; $p <= $total_pages; $p++): ?>
                    <?php if($p == $paged): ?>
                        <span class="op-audit-page-current"><?php echo $p; ?></span>
                    <?php else: ?>
                        <a href="<?php echo esc_url($build_url(array('paged' => $p))); ?>"><?php echo $p; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
