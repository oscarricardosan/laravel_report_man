<?php

namespace App\Report_mans;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Oscarricardosan\Report_man\Report_man;

class Pqr_report_man extends Report_man
{

    /**
     * Name of base table
     * @return string
     * Example: return "users";
     */
    public function baseTable()
    {
        return 'pqrs';
    }


    /**
     * @return Collection
     * Example:
     * return collect([
     * [
     * 'label'=> 'Users',
     * 'fields'=> collect([
     * ['label'=> "User name", "sql"=> "users.name", "filter_field"=> false],
     * ['label'=> "User email", "sql"=> "users.email"],
     * ['label'=> "User created", "sql"=> "users.created_t", "filter_field"=> true, "type"=> "date"],
     * ])
     * ]
     * ]);
     */
    public function tables()
    {
        return collect([
            [
                'label'=> 'Caso',
                'fields'=> collect([
                    ['label'=> "ID", "sql"=> "pqrs.id", "filter_field"=> true, "type"=> "numeric"],
                    ['label'=> "Fecha", "sql"=> "pqrs.pqr_date", "filter_field"=> true, "type"=> "date"],
                    ['label'=> "Estado",
                        "sql"=> DB::raw("pqr_states.name as pqr_states_name"),
                        "to_group_by"=> "pqr_states.name",
                    ],
                    ['label'=> "Fecha cierre", "sql"=> "pqrs.closed_at"],
                    ['label'=> "Tipo",
                        "sql"=> DB::raw("pqr_types.name as pqr_type_name"),
                        "to_group_by"=> "pqr_types.name",
                    ],
                    ['label'=> "Descripción", "sql"=> "pqrs.description"],
                    ['label'=> "Motivo",
                        "sql"=> DB::raw("pqr_reasons.name as pqr_reason_name"),
                        "to_group_by"=> "pqr_reasons.name",
                    ],
                    ['label'=> "Días abierto",
                        "sql"=> DB::raw('EXTRACT(DAY FROM (coalesce(pqrs.closed_at, now()) - pqr_date)) as open_days'),
                        "to_group_by"=> DB::raw('EXTRACT(DAY FROM (coalesce(pqrs.closed_at, now()) - pqr_date))')
                    ],
                    ['label'=> "Días sin movimientos",
                        "sql"=> DB::raw('EXTRACT(DAY FROM (now() - pqr_traceability.created_at)) as days_without_activity'),
                        "to_group_by"=> 'pqr_traceability.created_at'
                    ],
                    ['label'=> "Cantidad", "sql"=> DB::raw('count(pqrs.*)'), "to_group_by"=> false],
                ])
            ], [
                'label'=> 'Cliente',
                'fields'=> collect([
                    ['label'=> "ID", "sql"=> "pqrs.customer_id", "filter_field"=> true, "type"=> "numeric"],
                    ['label'=> "Tipo documento", "sql"=> "customer_type_of_identity_document.short_name"],
                    ['label'=> "Número de documento", "sql"=> "pqrs.customer_identity_number", "filter_field"=> true, "type"=> "numeric"],
                    ['label'=> "Nombre", "sql"=> "pqrs.customer_name"],
                    ['label'=> "Email", "sql"=> "pqrs.customer_email", "filter_field"=> true, "type"=> "text"],
                    ['label'=> "Ciudad",
                        "sql"=> DB::raw('customer_city.name as customer_city'),
                        "to_group_by"=> 'customer_city.name',
                        "filter_field"=> true, "type"=> "text"
                    ],
                    ['label'=> "Departamento",
                        "sql"=> DB::raw('customer_state.name as customer_state'),
                        "to_group_by"=> 'customer_state.name',
                        "filter_field"=> true, "type"=> "text"
                    ],
                    ['label'=> "Dirección", "sql"=> "pqrs.customer_address"],
                    ['label'=> "Teléfono", "sql"=> "pqrs.customer_phone_numbers"],
                ])
            ], [
                'label'=> 'Usuario asignado',
                'fields'=> collect([
                    ['label'=> "Nombre",
                        "sql"=> DB::raw('user_assigned.name as user_assigned_name'),
                        "to_group_by"=> 'user_assigned.name'],
                    ['label'=> "Email", "sql"=> "user_assigned.email", "filter_field"=> true, "type"=> "text"],
                ])
            ], [
                'label'=> 'Ultimo movimiento',
                'fields'=> collect([
                    ['label'=> "Tipo", "sql"=> "pqr_traceability.type"],
                    ['label'=> "Fecha", "sql"=> "pqr_traceability.created_at"],
                ])
            ],
        ]);
    }



    /**
     * Define all relationships for the $tables query to works correctly
     * Example:
     * if($this->needs_table_relation('contacts')
     * $this->query->leftJoin('contacts', 'users.id', '=', 'contacts.user_id');
     * if($this->needs_table_relation('orders')
     * $this->query->join('orders', 'users.id', '=', 'orders.user_id');
     * @return $this
     */
    public function loadRelationships()
    {
        if($this->needs_table_relation('user_assigned'))
            $this->query->leftJoin('users as user_assigned', 'user_assigned.id', '=', 'pqrs.assigned_user_id');

        if($this->needs_table_relation('pqr_types'))
            $this->query->leftJoin('pqr_types', 'pqr_types.id', '=', 'pqrs.pqr_type_id');

        if($this->needs_table_relation('pqr_reasons'))
            $this->query->leftJoin('pqr_reasons', 'pqr_reasons.id', '=', 'pqrs.pqr_reason_id');

        if($this->needs_table_relation('pqr_states'))
            $this->query->leftJoin('pqr_states', 'pqr_states.id', '=', 'pqrs.pqr_state_id');

        if($this->needs_table_relation('pqr_traceability'))
            $this->query->leftJoin('pqr_traceability', 'pqr_traceability.id', '=', 'pqrs.last_record_traceability_id');

        if($this->needs_table_relation('customer_type_of_identity_document'))
            $this->query->leftJoin('type_of_identity_documents as customer_type_of_identity_document', 'customer_type_of_identity_document.id', '=', 'pqrs.customer_type_of_identity_document_id');

        if($this->needs_table_relation('customer_city'))
            $this->query->leftJoin('cities as customer_city', 'customer_city.id', '=', 'pqrs.customer_city_id');

        if($this->needs_table_relation('customer_state'))
            $this->query->leftJoin('states as customer_state', 'customer_state.id', '=', 'customer_city.state_id');

        return $this;
    }
}