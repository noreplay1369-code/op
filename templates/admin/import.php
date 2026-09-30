<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $op_warehouse;
$op_nonce = wp_create_nonce( 'op_nonce' );
$import_fields = $this->import_fields();
$outlets = $op_warehouse->warehouses();
$selected_outlet = isset($_GET['warehouse_id']) ? (int)$_GET['warehouse_id'] : 0;
?>
<div class="op-admin-wrap wrap op-import-wrap">
    <h1 class="wp-heading-inline"><?php echo __('Import Products & Stock','openpos'); ?></h1>
    <p class="op-import-lead"><?php echo __('Upload one file to create new products, update existing ones and set stock. Only the columns you have are needed; empty cells never overwrite existing data.','openpos'); ?></p>

    <!-- STEP 1 -->
    <div class="op-import-card" id="op-import-step-upload">
        <div class="op-import-step"><span>1</span> <?php echo __('Choose file','openpos'); ?></div>
        <div id="op-import-drop" class="op-import-drop" tabindex="0">
            <div class="op-import-drop-icon">&#8682;</div>
            <div><strong><?php echo __('Drop your file here','openpos'); ?></strong> <?php echo __('or click to browse','openpos'); ?></div>
            <div class="op-import-hint">CSV, XLS, XLSX, ODS &middot; <?php echo __('first row = column names','openpos'); ?></div>
            <div id="op-import-filename" class="op-import-filename"></div>
        </div>
        <input type="file" id="op-import-file" accept=".csv,.txt,.xls,.xlsx,.ods" style="display:none;">
        <p class="op-import-links">
            <a href="javascript:void(0);" id="op-import-template"><?php echo __('Download simple product template','openpos'); ?></a>
            &nbsp;·&nbsp;
            <a href="javascript:void(0);" id="op-import-template-variable"><?php echo __('Download variable product template','openpos'); ?></a>
            &nbsp;·&nbsp;
            <a href="javascript:void(0);" id="op-import-template-mixed"><?php echo __('Download combined template (simple + variable)','openpos'); ?></a>
            <br><span class="op-import-hint"><?php echo __('Recognised columns are matched automatically (English or Indonesian names). Any other column can be saved as a custom field. Nearly everything from the Add Product screen is supported — type, attributes, variations, images, shipping, linked products. Simple and variable products (with their variations) can be mixed in the same sheet — the Type column on each row decides.','openpos'); ?></span>
        </p>
        <div id="op-import-error" class="op-import-alert" style="display:none;"></div>
    </div>

    <!-- VARIABLE PRODUCT HELP -->
    <div class="op-import-card op-import-help">
        <p><strong><?php echo __('Importing variable products','openpos'); ?></strong></p>
        <ul>
            <li><?php echo __('Put the parent row first with Type = variable, Name, and its Attribute name/value columns (e.g. Attribute 1 Name = Color, Attribute 1 Value(s) = Red | Blue | Green — this defines the options).','openpos'); ?></li>
            <li><?php echo __('Then add one row per variation right after it with Type = variation, Parent = the parent SKU (or ID), and the same Attribute name column but with a single value for that variation (e.g. Attribute 1 Value(s) = Red). Each variation can have its own SKU, Barcode, price and stock.','openpos'); ?></li>
            <li><?php echo __('Leave Type empty and only fill Parent to also import a row as a variation.','openpos'); ?></li>
            <li><strong><?php echo __('Simple and variable products can be mixed freely in the same sheet','openpos'); ?></strong> — <?php echo __('just leave the columns that a row does not need (attributes on a simple row, price/stock on a variable parent row) empty; the Type column on each row is what decides how it is imported.','openpos'); ?></li>
        </ul>
    </div>

    <!-- STEP 2 + 3 -->
    <div id="op-import-step-config" style="display:none;">
        <div class="op-import-card">
            <div class="op-import-step"><span>2</span> <?php echo __('Check columns','openpos'); ?> <em id="op-import-count"></em></div>
            <div class="op-import-tablewrap">
                <table class="table op-import-map">
                    <thead><tr>
                        <th><?php echo __('Column in your file','openpos'); ?></th>
                        <th><?php echo __('Example','openpos'); ?></th>
                        <th><?php echo __('Import as','openpos'); ?></th>
                    </tr></thead>
                    <tbody id="op-import-map-body"></tbody>
                </table>
            </div>
            <div id="op-import-map-warning" class="op-import-alert" style="display:none;"></div>
        </div>

        <div class="op-import-card">
            <div class="op-import-step"><span>3</span> <?php echo __('Options','openpos'); ?></div>
            <div class="row">
                <div class="col-sm-6 col-md-3 op-import-opt">
                    <label><?php echo __('Stock goes to outlet','openpos'); ?></label>
                    <select id="op-import-outlet" class="form-control">
                        <?php foreach($outlets as $o): ?>
                            <option value="<?php echo esc_attr($o['id']); ?>" <?php selected($selected_outlet,(int)$o['id']); ?>><?php echo esc_html($o['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3 op-import-opt">
                    <label><?php echo __('Stock quantity','openpos'); ?></label>
                    <select id="op-import-stock-action" class="form-control">
                        <option value="replace"><?php echo __('Replace current qty','openpos'); ?></option>
                        <option value="append"><?php echo __('Add to current qty','openpos'); ?></option>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3 op-import-opt">
                    <label><?php echo __('If product already exists','openpos'); ?></label>
                    <select id="op-import-on-found" class="form-control">
                        <option value="update"><?php echo __('Update it','openpos'); ?></option>
                        <option value="skip"><?php echo __('Skip it','openpos'); ?></option>
                    </select>
                </div>
                <div class="col-sm-6 col-md-3 op-import-opt">
                    <label><?php echo __('If product is not found','openpos'); ?></label>
                    <select id="op-import-on-missing" class="form-control">
                        <option value="create"><?php echo __('Create new product','openpos'); ?></option>
                        <option value="skip"><?php echo __('Skip it','openpos'); ?></option>
                    </select>
                </div>
            </div>
            <p class="op-import-hint"><?php echo __('Products are matched by ID, then SKU, then Barcode, then exact Name (only if no other identifier is in the row). New products default to type Simple unless a Type column says otherwise.','openpos'); ?></p>
            <p>
                <button type="button" class="btn btn-success" id="op-import-start"><?php echo __('Start import','openpos'); ?></button>
                <button type="button" class="btn btn-default" id="op-import-reset"><?php echo __('Choose another file','openpos'); ?></button>
            </p>
        </div>
    </div>

    <!-- PROGRESS -->
    <div class="op-import-card" id="op-import-step-run" style="display:none;">
        <div class="op-import-step"><span>&#10003;</span> <?php echo __('Importing','openpos'); ?> <em id="op-import-progress-text"></em></div>
        <div class="op-import-bar"><div id="op-import-bar-fill"></div></div>
        <div class="op-import-stats">
            <div class="s-created"><b id="op-stat-created">0</b><span><?php echo __('Created','openpos'); ?></span></div>
            <div class="s-updated"><b id="op-stat-updated">0</b><span><?php echo __('Updated','openpos'); ?></span></div>
            <div class="s-skipped"><b id="op-stat-skipped">0</b><span><?php echo __('Skipped','openpos'); ?></span></div>
            <div class="s-error"><b id="op-stat-error">0</b><span><?php echo __('Errors','openpos'); ?></span></div>
        </div>
        <div id="op-import-log-wrap" style="display:none;">
            <h4><?php echo __('Rows that need attention','openpos'); ?></h4>
            <div class="op-import-tablewrap">
                <table class="table op-import-log"><thead><tr>
                    <th><?php echo __('Row','openpos'); ?></th><th><?php echo __('Result','openpos'); ?></th><th><?php echo __('Message','openpos'); ?></th>
                </tr></thead><tbody id="op-import-log-body"></tbody></table>
            </div>
        </div>
        <p>
            <button type="button" class="btn btn-default" id="op-import-cancel"><?php echo __('Stop','openpos'); ?></button>
            <button type="button" class="btn btn-default" id="op-import-report" style="display:none;"><?php echo __('Download report (CSV)','openpos'); ?></button>
            <button type="button" class="btn btn-success" id="op-import-again" style="display:none;"><?php echo __('Import another file','openpos'); ?></button>
        </p>
    </div>

    <!-- OVERVIEW (shown after import finishes) -->
    <div class="op-import-card" id="op-import-overview" style="display:none;">
        <div class="op-import-step"><span>&#128203;</span> <?php echo __('Import Overview','openpos'); ?></div>

        <div id="op-overview-duplicate-alert" class="op-import-alert op-import-alert-warning" style="display:none;">
            <strong><?php echo __('Possible duplicates found in your file','openpos'); ?></strong>
            <p class="op-import-hint" style="margin:4px 0 8px;"><?php echo __('These rows matched the same product, so only the last one in the file is what stayed on the product.','openpos'); ?></p>
            <ul id="op-overview-duplicate-list" class="op-overview-list"></ul>
        </div>

        <div id="op-overview-existing-alert" class="op-import-alert op-import-alert-info" style="display:none;">
            <strong><?php echo __('Products that already existed (updated, not created new)','openpos'); ?></strong>
            <ul id="op-overview-existing-list" class="op-overview-list"></ul>
        </div>

        <details id="op-overview-details">
            <summary><?php echo __('Show every row that was processed','openpos'); ?> <em id="op-overview-count"></em></summary>
            <div class="op-import-tablewrap">
                <table class="table op-import-log" id="op-overview-table"><thead><tr>
                    <th><?php echo __('Row','openpos'); ?></th>
                    <th><?php echo __('Product','openpos'); ?></th>
                    <th><?php echo __('Result','openpos'); ?></th>
                    <th><?php echo __('What changed','openpos'); ?></th>
                </tr></thead><tbody id="op-overview-body"></tbody></table>
            </div>
        </details>
    </div>
</div>

<script type="text/javascript">
(function($){
    "use strict";
    var ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
    var nonce = "<?php echo $op_nonce; ?>";
    var fields = <?php echo wp_json_encode($import_fields); ?>;
    var T = {
        ignore: "<?php echo esc_js(__('— Ignore this column —','openpos')); ?>",
        meta: "<?php echo esc_js(__('Custom field (saved under the column name)','openpos')); ?>",
        rows: "<?php echo esc_js(__('rows found','openpos')); ?>",
        noId: "<?php echo esc_js(__('Map at least one of: Product ID, SKU, Barcode or Product name, so rows can be matched.','openpos')); ?>",
        noName: "<?php echo esc_js(__('Tip: without a Product name column, only existing products can be updated (new ones need a name).','openpos')); ?>",
        confirmStart: "<?php echo esc_js(__('Start importing now? Existing products will be changed according to your options.','openpos')); ?>",
        fail: "<?php echo esc_js(__('Request failed. Please try again.','openpos')); ?>",
        oCreated: "<?php echo esc_js(__('Created (new product)','openpos')); ?>",
        oUpdated: "<?php echo esc_js(__('Updated (already existed)','openpos')); ?>",
        oSkipped: "<?php echo esc_js(__('Skipped','openpos')); ?>",
        oError: "<?php echo esc_js(__('Error','openpos')); ?>",
        oRow: "<?php echo esc_js(__('row','openpos')); ?>",
        oRows: "<?php echo esc_js(__('rows','openpos')); ?>"
    };
    var state = {headers:[], rows:[], mapping:[]};
    var running = false, stopFlag = false, report = [];
    var BATCH = 20;

    function esc(s){ return $('<div>').text(s == null ? '' : s).html(); }
    function showError(msg){ $('#op-import-error').text(msg).show(); }

    // ---------- upload ----------
    var $drop = $('#op-import-drop'), $file = $('#op-import-file');
    $drop.on('click keypress', function(e){ if(e.type==='click' || e.which===13){ $file.trigger('click'); } });
    $drop.on('dragover dragenter', function(e){ e.preventDefault(); $drop.addClass('over'); });
    $drop.on('dragleave drop', function(e){ e.preventDefault(); $drop.removeClass('over'); });
    $drop.on('drop', function(e){ var f = e.originalEvent.dataTransfer.files; if(f && f.length){ upload(f[0]); } });
    $file.on('change', function(){ if(this.files.length){ upload(this.files[0]); } });

    function upload(file){
        $('#op-import-error').hide();
        $('#op-import-filename').text(file.name);
        var fd = new FormData();
        fd.append('action','op_import_parse'); fd.append('op_nonce',nonce); fd.append('file',file);
        $('body').addClass('op_loading');
        $.ajax({url:ajaxUrl,type:'post',dataType:'json',data:fd,contentType:false,processData:false})
        .done(function(r){
            if(r.status == 1){
                state.headers = r.headers; state.rows = r.rows; state.mapping = r.mapping;
                buildMapping();
                $('#op-import-count').text('· '+r.rows.length+' '+T.rows);
                $('#op-import-step-config').slideDown(150);
            }else{ showError(r.message || T.fail); }
        }).fail(function(){ showError(T.fail); })
        .always(function(){ $('body').removeClass('op_loading'); $file.val(''); });
    }

    // ---------- mapping ----------
    function buildMapping(){
        var $b = $('#op-import-map-body').empty();
        $.each(state.headers, function(i,h){
            var samples = [];
            for(var k=0;k<state.rows.length && samples.length<2;k++){ var v = state.rows[k].c[i]; if(v){ samples.push(v); } }
            var sel = '<select class="form-control op-import-map-sel" data-col="'+i+'">';
            sel += '<option value="">'+esc(T.ignore)+'</option>';
            var groups = {};
            var order = [];
            $.each(fields,function(key,f){
                if(!groups[f.group]){ groups[f.group] = []; order.push(f.group); }
                groups[f.group].push(key);
            });
            $.each(order,function(_,g){
                sel += '<optgroup label="'+esc(g)+'">';
                $.each(groups[g],function(_,key){
                    var f = fields[key];
                    sel += '<option value="'+key+'"'+(state.mapping[i]===key?' selected':'')+'>'+esc(f.label)+'</option>';
                });
                sel += '</optgroup>';
            });
            sel += '<option value="meta"'+(state.mapping[i]==='meta'?' selected':'')+'>'+esc(T.meta)+'</option></select>';
            $b.append('<tr class="'+(state.mapping[i]?'is-mapped':'')+'"><td><strong>'+esc(h)+'</strong></td><td class="op-import-sample">'+esc(samples.join(' · '))+'</td><td>'+sel+'</td></tr>');
        });
        checkMapping();
    }
    $(document).on('change','.op-import-map-sel',function(){
        var col = $(this).data('col'), val = $(this).val();
        if(val && val !== 'meta'){
            $('.op-import-map-sel').not(this).each(function(){ if($(this).val()===val){ $(this).val('').trigger('change'); } });
        }
        state.mapping[col] = val;
        $(this).closest('tr').toggleClass('is-mapped', !!val);
        checkMapping();
    });
    function mapped(key){ return state.mapping.indexOf(key) !== -1; }
    function checkMapping(){
        var msg = '';
        if(!(mapped('id')||mapped('sku')||mapped('barcode')||mapped('name'))){ msg = T.noId; }
        else if(!mapped('name')){ msg = T.noName; }
        $('#op-import-map-warning').text(msg).toggle(!!msg);
        $('#op-import-start').prop('disabled', !(mapped('id')||mapped('sku')||mapped('barcode')||mapped('name')));
    }

    // ---------- run ----------
    $('#op-import-start').on('click',function(){
        if(!confirm(T.confirmStart)){ return; }
        running = true; stopFlag = false; report = [];
        var m = {}; $.each(state.mapping,function(i,v){ if(v){ m[i]=v; } });
        var opts = {
            warehouse_id: $('#op-import-outlet').val(),
            stock_action: $('#op-import-stock-action').val(),
            on_found: $('#op-import-on-found').val(),
            on_missing: $('#op-import-on-missing').val()
        };
        $('#op-import-step-config').hide(); $('#op-import-step-upload').hide();
        $('#op-import-step-run').show();
        $('#op-import-log-body').empty(); $('#op-import-log-wrap').hide();
        $('#op-import-cancel').show(); $('#op-import-report,#op-import-again').hide();
        var stats = {created:0,updated:0,skipped:0,error:0}, done = 0, total = state.rows.length;
        updateStats(stats); progress(0,total);

        (function next(){
            if(stopFlag || done >= total){ finish(stats, done, total); return; }
            var batch = state.rows.slice(done, done+BATCH);
            $.ajax({url:ajaxUrl,type:'post',dataType:'json',data:{
                action:'op_import_run', op_nonce:nonce,
                rows: JSON.stringify(batch), mapping: JSON.stringify(m),
                headers: JSON.stringify(state.headers), options: JSON.stringify(opts)
            }}).done(function(r){
                if(r.status != 1){ batchFail(batch, r.message || T.fail, stats); }
                else{ $.each(r.data,function(_,x){ stats[x.status] = (stats[x.status]||0)+1; report.push(x); if(x.status !== 'created' && x.status !== 'updated'){ addLog(x); } }); }
            }).fail(function(){ batchFail(batch, T.fail, stats); })
            .always(function(){ done += batch.length; updateStats(stats); progress(done,total); next(); });
        })();
    });
    function batchFail(batch,msg,stats){
        $.each(batch,function(_,row){ var x={n:row.n,status:'error',message:msg,id:0,name:''}; stats.error++; report.push(x); addLog(x); });
    }
    function addLog(x){
        $('#op-import-log-wrap').show();
        $('#op-import-log-body').append('<tr class="log-'+x.status+'"><td>'+x.n+'</td><td>'+esc(x.status)+'</td><td>'+esc(x.message)+'</td></tr>');
    }
    function updateStats(s){ $('#op-stat-created').text(s.created); $('#op-stat-updated').text(s.updated); $('#op-stat-skipped').text(s.skipped); $('#op-stat-error').text(s.error); }
    function progress(done,total){
        var p = total ? Math.round(done*100/total) : 100;
        $('#op-import-bar-fill').css('width',p+'%'); $('#op-import-progress-text').text(done+' / '+total+' ('+p+'%)');
    }
    function finish(){
        running = false; $('#op-import-cancel').hide(); $('#op-import-report,#op-import-again').show();
        renderOverview();
    }
    $('#op-import-cancel').on('click',function(){ stopFlag = true; });
    $('#op-import-again,#op-import-reset').on('click',function(){
        state = {headers:[],rows:[],mapping:[]};
        $('#op-import-step-run,#op-import-step-config,#op-import-overview').hide();
        $('#op-import-step-upload').show();
        $('#op-import-filename').text(''); $('#op-import-error').hide();
    });

    // ---------- overview ----------
    function resultLabel(status){
        var map = {created:T.oCreated, updated:T.oUpdated, skipped:T.oSkipped, error:T.oError};
        return map[status] || status;
    }
    function renderOverview(){
        var $dupList = $('#op-overview-duplicate-list').empty();
        var $existList = $('#op-overview-existing-list').empty();
        var $body = $('#op-overview-body').empty();

        // group by resolved product id (created/updated rows only) to spot rows
        // in this same file that landed on the very same product.
        var byId = {};
        $.each(report, function(_, x){
            if(x.id && (x.status === 'created' || x.status === 'updated')){
                (byId[x.id] = byId[x.id] || []).push(x);
            }
        });
        var dupCount = 0;
        $.each(byId, function(id, rows){
            if(rows.length > 1){
                dupCount++;
                var rowNums = $.map(rows, function(r){ return r.n; }).join(', ');
                $dupList.append('<li>'+esc(rows[rows.length-1].name || ('#'+id))+' <span class="op-overview-muted">(ID #'+id+')</span> — '+T.oRows+' '+rowNums+'</li>');
            }
        });
        $('#op-overview-duplicate-alert').toggle(dupCount > 0);

        var existing = $.grep(report, function(x){ return x.status === 'updated'; });
        $.each(existing, function(_, x){
            $existList.append('<li>'+esc(x.name || ('#'+x.id))+' <span class="op-overview-muted">(ID #'+x.id+', '+T.oRow+' '+x.n+')</span></li>');
        });
        $('#op-overview-existing-alert').toggle(existing.length > 0);

        $.each(report, function(_, x){
            $body.append(
                '<tr class="log-'+x.status+'"><td>'+x.n+'</td>'+
                '<td>'+(x.id ? esc(x.name || '')+' <span class="op-overview-muted">#'+x.id+'</span>' : '&mdash;')+'</td>'+
                '<td>'+esc(resultLabel(x.status))+'</td>'+
                '<td>'+esc(x.message)+'</td></tr>'
            );
        });
        $('#op-overview-count').text('('+report.length+')');
        $('#op-import-overview').show();
    }

    // ---------- downloads ----------
    function csvCell(v){ v = (v==null?'':String(v)); return /[",\n]/.test(v) ? '"'+v.replace(/"/g,'""')+'"' : v; }
    function download(name, lines){
        var blob = new Blob(["\uFEFF"+lines.join("\r\n")], {type:'text/csv;charset=utf-8;'});
        var a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = name;
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
    }
    $('#op-import-template').on('click',function(){
        var head = ['Type','ID','SKU','Barcode','Name','Regular Price','Sale Price','Cost Price','Qty','Category','Tags','Short Description','Description','Status','Tax Status','Weight','Image URL'];
        var r1 = ['simple','','KOPI-001','8991234500011','Kopi Susu 250ml','25000','22000','15000','50','Minuman > Kopi','kopi|susu','Kopi susu botol','Kopi susu dingin siap minum','publish','taxable','0.3',''];
        var r2 = ['simple','','TEH-002','8991234500028','Teh Botol 350ml','7000','','4500','120','Minuman > Teh','teh','','','publish','taxable','0.4',''];
        download('openpos-import-template-simple.csv',[head.map(csvCell).join(','),r1.map(csvCell).join(','),r2.map(csvCell).join(',')]);
    });
    $('#op-import-template-variable').on('click',function(){
        var head = ['Type','Parent','ID','SKU','Barcode','Name','Regular Price','Sale Price','Cost Price','Qty','Category','Attribute 1 Name','Attribute 1 Value(s)','Description','Status','Image URL'];
        var parent = ['variable','','','KAOS-001','','Kaos Polos','','','','','Pakaian > Kaos','Warna','Merah | Biru | Hijau','Kaos polos katun combed','publish',''];
        var v1 = ['variation','KAOS-001','','KAOS-001-MERAH','8991234600011','','55000','','35000','20','','Warna','Merah','','publish',''];
        var v2 = ['variation','KAOS-001','','KAOS-001-BIRU','8991234600028','','55000','','35000','15','','Warna','Biru','','publish',''];
        var v3 = ['variation','KAOS-001','','KAOS-001-HIJAU','8991234600035','','55000','','35000','0','','Warna','Hijau','','publish',''];
        download('openpos-import-template-variable.csv',[head,parent,v1,v2,v3].map(function(r){ return r.map(csvCell).join(','); }));
    });
    $('#op-import-template-mixed').on('click',function(){
        var head = ['Type','Parent','ID','SKU','Barcode','Name','Regular Price','Sale Price','Cost Price','Qty','Category','Attribute 1 Name','Attribute 1 Value(s)','Description','Status','Image URL'];
        var simple1 = ['simple','','','KOPI-001','8991234500011','Kopi Susu 250ml','25000','22000','15000','50','Minuman > Kopi','','','Kopi susu dingin siap minum','publish',''];
        var simple2 = ['simple','','','TEH-002','8991234500028','Teh Botol 350ml','7000','','4500','120','Minuman > Teh','','','','publish',''];
        var parent = ['variable','','','KAOS-001','','Kaos Polos','','','','','Pakaian > Kaos','Warna','Merah | Biru | Hijau','Kaos polos katun combed','publish',''];
        var v1 = ['variation','KAOS-001','','KAOS-001-MERAH','8991234600011','','55000','','35000','20','','Warna','Merah','','publish',''];
        var v2 = ['variation','KAOS-001','','KAOS-001-BIRU','8991234600028','','55000','','35000','15','','Warna','Biru','','publish',''];
        download('openpos-import-template-mixed.csv',[head,simple1,simple2,parent,v1,v2].map(function(r){ return r.map(csvCell).join(','); }));
    });
    $('#op-import-report').on('click',function(){
        var lines = ['Row,Result,Product ID,Name,Message'];
        $.each(report,function(_,x){ lines.push([x.n,x.status,x.id||'',x.name||'',x.message].map(csvCell).join(',')); });
        download('openpos-import-report.csv',lines);
    });
})(jQuery);
</script>
