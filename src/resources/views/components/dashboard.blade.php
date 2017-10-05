<?php
    $tables= $report->tables();
?>
<div class="report_manPanel2041 col-sm-12">
    <form class="panelOfTables" method="post" target="_blank">
        {{ csrf_field() }}
        <h5>Tablas</h5>
        @foreach($tables->chunk(4) as $block)
        <div class="row">
            @foreach($block as $index_table=> $table)
                <div class="col-sm-3">
                    <table class="table table-bordered table-striped table-condensed">
                        <thead>
                        <tr>
                            <th>
                                <label>
                                    <input type="checkbox" class="check_fields"> {{ $table['label'] }}
                                </label>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($table['fields'] as $index_field=> $field)
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" name="fields[]" class="field" value="{{ $index_table }}:{{ $index_field }}"> {{ $field['label'] }}
                                    </label>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
            </div>
        @endforeach
        <h5>
            Filtros
            <a href="#" class="addFilter">
                <i class="fa fa-plus-circle"></i>Añadir filtro
            </a>
        </h5>
        <div class="col-sm-12 filters">
        </div>

        <div class="row">
            <div class="col-sm-12" style="text-align: center">
                @if(isset($route_to_export_xls))
                    <button type="submit" class="btn btn-primary" style="margin-right: 2em;" formaction="{{ $route_to_export_xls }}">
                        <i class="fa fa-file-excel-o"></i> Generar Excel xls
                    </button>
                @endif
                @if(isset($route_to_export_xlsx))
                    <button type="submit" class="btn btn-primary" style="margin-right: 2em;" formaction="{{ $route_to_export_xlsx }}">
                        <i class="fa fa-file-excel-o"></i> Generar Excel xlsx
                    </button>
                @endif
                @if(isset($route_to_export_csv))
                    <button type="submit" class="btn btn-primary" style="margin-right: 2em;" formaction="{{ $route_to_export_csv }}">
                        <i class="fa fa-file-text-o"></i> Generar CSV
                    </button>
                @endif
            </div>
        </div>
    </form>
    @verbatim
    <script type="text/template" class="fitlerTPL">
        <div class="row">
            <div class="col-sm-3">
                {{ if(index>0){ }}
                    <select class="form-control" name="filters[{{= index }}][logical_operator]">
                        {{ $.each(logical_operators, function(index, operator){ }}
                        <option value="{{= index }}">{{= operator.label }}</option>
                        {{ }) }}
                    </select>
                {{ }else{ }}
                <select class="form-control" readonly disabled></select>
                {{ } }}
            </div>
            <div class="col-sm-3">
                <select class="form-control filter_field" name="filters[{{= index }}][field]">
                    {{ $.each(filter_fields, function(index_table, table){ }}
                        {{ $.each(table.fields, function(index_field, filter_field){ }}
                        <option value="{{= index_table }}:{{= index_field }}" data-type="{{= filter_field.type }}">{{= table.label }} - {{= filter_field.label }}</option>
                        {{ }) }}
                    {{ }) }}
                </select>
            </div>
            <div class="col-sm-3">
                <select class="form-control" name="filters[{{= index }}][relational_operator]">
                    {{ $.each(relational_operators, function(index, operator){ }}
                    <option value="{{= index }}">{{= operator.label }}</option>
                    {{ }) }}
                </select>
            </div>
            <div class="col-sm-3">
                <input class="form-control filter_value" name="filters[{{= index }}][value]" step="0.01">
            </div>
            <div class="col-sm-12" style="text-align: right">
                <a href="#" style="color: darkred;" class="removeFilter">
                    <i class="fa fa-minus-circle"></i>Remover filtro
                </a>
            </div>
        </div>
    </script>
    @endverbatim
</div>

<style>
    .report_manPanel2041 table>thead,
    .report_manPanel2041 table>thead>tr,
    .report_manPanel2041 table>thead>tr>th{
        background: #508ca9;
        font-weight: normal!important;
        color: white;
    }
    .report_manPanel2041 table>tbody>tr>td>label,
    .report_manPanel2041 table>thead>tr>th>label{
        cursor: pointer;
        display: block;
        font-weight: normal;
        margin-bottom: 0;
    }
</style>
<script>
    $(document).ready(function(){
        var filterIndex= 0;
        var dataToFilterTpl= {
            logical_operators: {!! json_encode($report->logical_operators) !!},
            relational_operators: {!! json_encode($report->relational_operators) !!},
            filter_fields: {!! json_encode($report->get_filter_fields_to_public_space()) !!},
        };
        $('.report_manPanel2041 .check_fields').click(function(){
            if($(this).is(':checked'))
                $(this).closest('table').find('[type="checkbox"].field').prop('checked', true);
            else
                $(this).closest('table').find('[type="checkbox"].field').prop('checked', false);
        });
        $('.report_manPanel2041 .addFilter').click(function(event){
            event.preventDefault();
            dataToFilterTpl.index= filterIndex;
            $('.report_manPanel2041 .filters').append(
                $('.report_manPanel2041 .fitlerTPL').renderTpl(dataToFilterTpl)
            );
            filterIndex++;
            $('.report_manPanel2041 .filter_field').change();
        });
        $(document).on('click', '.report_manPanel2041 .removeFilter', function(event){
            event.preventDefault();
            $(this).closest('.row').remove();
        });
        $(document).on('change', '.report_manPanel2041 .filter_field', function(){
            var type= $(this).find('option:selected').data('type');

            if(type== 'numeric')
                $(this).closest('.row').find('.filter_value').attr('type', 'number');
            else if(type== 'date')
                $(this).closest('.row').find('.filter_value').attr('type', 'date');
            else
                $(this).closest('.row').find('.filter_value').attr('type', 'text');
        });

    });
</script>