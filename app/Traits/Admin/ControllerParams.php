<?php

namespace App\Traits\Admin;

use Illuminate\Http\Request;

trait ControllerParams
{
    public $viewAction = '';

    public $params = [];

    public function initializeParams(Request $request)
    {
        $route = explode('.', $request->route()->getAction()['as']);
        $this->params = [
            'module' => @$route[0],
            'controller' => @$route[1],
            'action' => @$route[2],
            'client_ip' => $request->ip(),
            'item_per_page' => $request->input('item_per_page', 30),
            'order' => $request->input('order', 'DESC'),
        ];

        $this->params['routeBase'] = $this->params['module'].'.'.$this->params['controller'].'.';
        $this->params['viewBase'] = $this->params['module'].'.pages.'.$this->params['controller'];
        $this->params = array_merge($this->params, $request->all());
        $this->viewAction = str_replace($this->params['module'], $this->params['module'].'.pages', $request->route()->getAction()['as']);
    }
}
