<?php

namespace Oscarricardosan\Report_man\Maps;

/**
 * @property array $fields
 * @property array $filters
 */
class ParamsMap extends BaseMap
{
    public function setFiltersAttribute(array $filters)
    {
        $filtersMap=[];
        foreach ($filters as $filter) {
            $filtersMap[]= new FilterMap($filter);
        }
        $this->attributes['filters']= $filtersMap;
    }
}