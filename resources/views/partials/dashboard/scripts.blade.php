{{-- resources/views/partials/dashboard/scripts.blade.php --}}
<script src="{{ asset('dashboard/js/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('dashboard/js/moment.min.js') }}"></script>
<script src="{{ asset('dashboard/js/utils.js') }}"></script>
<script src="{{ asset('dashboard/js/Chart.min.js') }}"></script>
<script src="{{ asset('dashboard/js/fullcalendar.min.js') }}"></script>
<script src="{{ asset('dashboard/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('dashboard/js/tooplate-scripts.js') }}"></script>
<script>
    let ctxLine, ctxBar, ctxPie, optionsLine, optionsBar, optionsPie, configLine, configBar, configPie;
    $(function () {
        updateChartOptions();
        drawLineChart();
        drawBarChart();
        drawPieChart();
        drawCalendar();
        $(window).resize(function () {
            updateChartOptions();
            updateLineChart();
            updateBarChart();
            reloadPage();
        });
    });
</script>
