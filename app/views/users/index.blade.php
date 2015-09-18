@section('scripts')
    {{ HTML::style('packages/jquery-ui-1.11.1/jquery-ui.min.css') }}
    {{ HTML::style('packages/jqGrid-4.6.0/css/ui.Jqgrid.css') }}
    {{ HTML::style('packages/select2-3.5.1/select2.css') }}
    {{ HTML::script('packages/jquery-ui-1.11.1/jquery-ui.min.js') }}
    {{ HTML::script('packages/jqGrid-4.6.0/js/i18n/grid.locale-en.js') }}
    {{ HTML::script('packages/BBQ-1.2.1-modified/jquery.ba-bbq.js') }}
    {{ HTML::script('packages/jqGrid-4.6.0/plugins/grid.history.js') }}
    {{ HTML::script('packages/jqGrid-4.6.0/js/jquery.jqGrid.min.js') }}

    {{ HTML::script('assets/js/base.js') }}
    {{ HTML::script('assets/js/users/grid.js') }}

@stop
@section('content')
    <table id="jgGrid"></table>
    <br/>
    <div id="jgGridPager"></div>
@stop
