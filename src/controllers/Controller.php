<?php

namespace crocodicstudio\crudbooster\controllers;

// use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;

// use Illuminate\Foundation\Validation\ValidatesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
// use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController
{
    // use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    /**
     * Execute an action on the controller.
     *
     * CRUDBooster registers routes with optional wildcards ({one?}/{two?}/...),
     * so we filter out null values to only pass actual parameters to the method.
     */
    public function callAction($method, $parameters)
    {
        return $this->{$method}(...array_values(array_filter($parameters, function ($v) {
            return ! is_null($v);
        })));
    }
}
