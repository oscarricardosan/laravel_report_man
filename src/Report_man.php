<?php

namespace Oscarricardosan\Report_man;


use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

abstract class Report_man
{

    /**
     * @var QueryBuilder
     */
    protected $query;


    /**
     * Data type that accepts the user filter
     * @var array
     */
    protected $filter_field_types= ['date', 'text', 'numeric'];

    /**
     * @var array
     */
    public $relational_operators = [
        ['label'=> 'Igual a ', 'value'=> '=='],
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
          $this->query->join('orders', 'users.id', '=', 'orders.user_id');
     * @return $this
     */
    abstract public function loadRelationships();

    /**
     * @param string $table
     * @return bool
     */
    public function needs_table_relation($table)
    {
        return true;
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

}