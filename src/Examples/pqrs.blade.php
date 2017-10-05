@extends('layouts.adminLte')
@section('pageTitle', 'Reporteador para casos')
@section('header')
    @include('layouts.header.intranet_page_header')
@endsection
@section('sidebar')
    @include('layouts.sidebar.intranet_sidebar')
@endsection
@section('estateSidebar', '')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Reporteador de casos
            </h1>
        </section>
        <!-- Main content -->
        <section class="content">
            @include('partials.general.errors')
            @include('partials.general.msg')
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">
                        Panel de control
                    </h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        @component('report_man::components.dashboard')
                            @slot('report', $report)
                            @slot('route_to_export_xls', route('Pqr_report_man.Generate_xls'))
                            @slot('route_to_export_xlsx', route('Pqr_report_man.Generate_xlsx'))
                            @slot('route_to_export_csv', route('Pqr_report_man.Generate_csv'))
                        @endcomponent
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!-- Pqr_report_man.Index -->
@endsection