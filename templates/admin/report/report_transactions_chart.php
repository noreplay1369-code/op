<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 col-log-12 col-sm-12 col-xs-12">
            <div id="curve_chart"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
    (function($) {

        google.charts.setOnLoadCallback(drawChart);

        var op_chart_data = <?php echo json_encode($chart_data); ?>;
        var op_gchart = null;

        function drawChart() {
            var data = google.visualization.arrayToDataTable(op_chart_data);

            var options = {
                title: '',
                curveType: 'function',
                legend: { position: 'bottom', textStyle: { color: '#6b7280', fontSize: 12 } },
                colors: ['#4f46e5', '#00b894', '#f59e0b', '#e11d48'],
                chartArea: { left: 60, top: 20, right: 20, bottom: 40, width: '100%', height: '75%' },
                fontName: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif',
                backgroundColor: 'transparent',
                hAxis: { textStyle: { color: '#6b7280' }, gridlines: { color: '#eef0f5' } },
                vAxis: { textStyle: { color: '#6b7280' }, gridlines: { color: '#eef0f5' } }
            };

            op_gchart = new google.visualization.LineChart(document.getElementById('curve_chart'));

            op_gchart.draw(data, options);
        }

        // Google Charts doesn't redraw itself on viewport/orientation changes,
        // so redraw on resize (debounced) to stay responsive on all devices.
        var op_resize_timer = null;
        $(window).on('resize', function(){
            clearTimeout(op_resize_timer);
            op_resize_timer = setTimeout(function(){
                if(op_gchart){ drawChart(); }
            }, 200);
        });


    }(jQuery));
</script>