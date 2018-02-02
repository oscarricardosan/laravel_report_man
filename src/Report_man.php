<?php

namespace Oscarricardosan\Report_man;


use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Oscarricardosan\Report_man\Maps\ParamsMap;

abstract class Report_man
{

    protected $footer_in_txt_to_files= [['']];

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $query_results;

    /**
     * @var array
     */
    protected $fields_in_query;

    /**
     * @var array
     */
    protected $tables_in_query;


    /**
     * Data type that accepts the user filter
     * @var array
     */
    protected $filter_field_types= ['date', 'text', 'numeric'];

    /**
     * @var array
     */
    public $relational_operators = [
        ['label'=> 'Igual a ', 'value'=> '='],
        ['label'=> 'Menor a ', 'value'=> '<'],
        ['label'=> 'Menor o igual que ', 'value'=> '<='],
        ['label'=> 'Mayor a ', 'value'=> '>'],
        ['label'=> 'Mayor o igual que ', 'value'=> '>='],
        ['label'=> 'Diferente a ', 'value'=> '!='],
    ];

    /**
     * @var array
     */
    public  $logical_operators = [
        ['label'=> 'Y', 'value'=> 'AND'],
        ['label'=> 'O', 'value'=> 'OR'],
    ];


    public function __construct()
    {
        $this->query= DB::table($this->baseTable());
        return $this;
    }


    /**
     * Name of base table
     * @return string
     * Example: return "users";
     */
    abstract public function baseTable();



    /**
     * @return Collection
     * Example:
         return collect([
            [
                'label'=> 'Users',
                'fields'=> collect([
                    ['label'=> "User name", "sql"=> "users.name", "filter_field"=> false],
                    ['label'=> "User email", "sql"=> "users.email"],
                    ['label'=> "User created", "sql"=> "users.created_t", "filter_field"=> true, "type"=> "date"],
                ])
            ]
         ]);
     */
    abstract public function tables();



    /**
     * Define all relationships for the $tables query to works correctly
     * Example:
       if($this->needs_table_relation('contacts'))
          $this->query->join('contacts', 'users.id', '=', 'contacts.user_id');
       if($this->needs_table_relation('orders'))
          $this->query->leftJoin('orders', 'users.id', '=', 'orders.user_id');
     * @return $this
     */
    abstract public function loadRelationships();

    /**
     * @param string $table
     * @return bool
     */
    public function needs_table_relation($table)
    {

        return in_array($table, $this->tables_in_query);
    }

    /**
     * @return array
     */
    public function get_filter_fields()
    {
        $fields= [];
        foreach($this->tables() as $indexTable=> $table){
            $results= $table['fields']->where('filter_field', true);
            $fields[$indexTable]['label']= $table['label'];
            $fields[$indexTable]['fields']= $results->all();
        }
        return $fields;
    }

    /**
     * @return array
     */
    public function get_filter_fields_to_public_space()
    {
        $fields= [];
        foreach($this->tables() as $indexTable=> $table){
            $results= $table['fields']->where('filter_field', true);
            $cleaned= $results->map(function($item, $key){
                unset($item['sql']);
                return $item;
            });
            $fields[$indexTable]['label']= $table['label'];
            $fields[$indexTable]['fields']= $cleaned->all();
        }
        return $fields;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function build_query(ParamsMap $paramsMap)
    {
        $this
            ->build_select($paramsMap->fields)
            ->build_where(is_array($paramsMap->filters)?$paramsMap->filters:[])
            ->extract_tables_of_select_fields()
            ->loadRelationships();
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function build_select(array $fields)
    {
        $this->fields_in_query= $this->solve_fields($fields);
        $fields_in_select= [];
        foreach ($this->fields_in_query as $field){
            $fields_in_select[]= $field['sql'];
        }
        $this->query->select($fields_in_select);
        $this->load_groupBy();
        return $this;
    }

    /**
     * @return $this
     */
    protected function load_groupBy()
    {
        $fieldsGroupBy= [];
        foreach ($this->fields_in_query as $field){
            if(!array_has($field, 'to_group_by'))
                $fieldsGroupBy[]= $field['sql'];
            elseif($field['to_group_by']!==false)
                $fieldsGroupBy[]= $field['to_group_by'];
        }
        if(count($fieldsGroupBy)>0)
            $this->query->groupBy($fieldsGroupBy);
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function build_where(array $filters)
    {
        foreach ($filters as $filter){
            $field_sql= $this->solve_field($filter->field);
            if(array_key_exists('sql_where', $field_sql))
                $field_where= $field_sql['sql_where'];
            else
                $field_where= $field_sql['sql'];
            if(is_null($filter->logical_operator) || $this->logical_operators[$filter->logical_operator]['value'] == 'AND'){
                $this->query->where(
                    $field_where,
                    $this->relational_operators[$filter->relational_operator]['value'],
                    $filter->value
                );
            }elseif($this->logical_operators[$filter->logical_operator]['value'] == 'OR'){
                $this->query->orWhere(
                    $field_where,
                    $this->relational_operators[$filter->relational_operator]['value'],
                    $filter->value
                );
            }

        }
        return $this;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function solve_fields(array $fields)
    {
        $resolved_fields= [];
        foreach ($fields as $field){
            $resolved_fields[]= $this->solve_field($field);
        }
        return $resolved_fields;
    }

    /**
     * @param string $field
     * @return array
     */
    public function solve_field($field)
    {
        $parts= explode(':', $field);
        $index_table= $parts[0];
        $index_field= $parts[1];
        $table= $this->tables()[$index_table];
        $field= $table['fields'][$index_field];
        $field['table_label']= $table['label'];
        return $field;
    }

    /**
     * @return $this
     */
    public function extract_tables_of_select_fields()
    {
        $tables= [];
        foreach ($this->fields_in_query as $field){
            if(array_has($field, "needsTable") && $field["needsTable"] !== false){
                $tables[]= $field["needsTable"];
            }
            if(is_string($field['sql'])){
                $parts= explode('.', $field['sql']);
                if(count($parts)>0){
                    $tables[]= $parts[0];
                }
            }
            if(array_has($field, "to_group_by") && is_string($field['to_group_by'])){
                $parts= explode('.', $field['to_group_by']);
                if(count($parts)>0){
                    $tables[]= $parts[0];
                }
            }
        }
        $this->tables_in_query= array_unique($tables);
        return $this;
    }

    /**
     * @return array
     */
    public function extract_labels_of_fields_in_select()
    {
        $headers= [];
        foreach ($this->fields_in_query as $field){
            $headers[]= $field['table_label'].' - '.$field['label'];
        }
        return $headers;
    }

    /**
     * @return $this
     */
    public function execQuery()
    {
        $this->query_results= $this->query->get();
        return $this;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get_query_results()
    {
        return $this->query_results;
    }

    /**
     * @param string $file_name
     * @param string $typeFile xls, xlsx, csv, pdf
     * @return Excel
     */
    public function download($file_name, $typeFile)
    {
        return Excel::create($file_name, function($excel) {
            $this->query_results;
            $excel->setCreator('Sabueso digital')->setCompany('savne.net');
            $excel->sheet('Hoja 1', function($sheet) {
                $headers= [$this->extract_labels_of_fields_in_select()];
                $results= json_decode($this->get_query_results()->toJson(), true);
                $sheet->fromArray(array_merge($headers, $results, $this->footer_in_txt_to_files), null, 'A1', false, false);
            });

        })->download($typeFile);
    }

}