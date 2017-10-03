<?php

namespace Oscarricardosan\Report_man;


use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Support\Facades\DB;

abstract class Report_man
{

    /**
     * @var QueryBuilder
     */
    protected $query;



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
     * @return array
     * Example:
         return [
            [
                'label'=> 'Users',
                'fields'=> [
                    ['label'=> "User name", "sql"=> "users.name"],
                    ['label'=> "User email", "sql"=> "users.email"],
                ]
            ]
         ];
     */
    abstract public function tables();



    /**
     * @return array
     * Example:
         return [
            ['label'=> "User created", "sql"=> "users.created_at"],
            ['label'=> "User updated", "sql"=> "users.updated_at"],
         ];
     */
    abstract public function dateFields();



    /**
     * Define all relationships for the $tables query to works correctly
     * Example:
         $this->query
            ->join('contacts', 'users.id', '=', 'contacts.user_id')
            ->join('orders', 'users.id', '=', 'orders.user_id');

     * @return $this
     */
    abstract public function loadRelationships();


}