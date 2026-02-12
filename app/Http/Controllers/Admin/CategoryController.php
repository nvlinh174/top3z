<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category as MainModel;
use App\Traits\Admin\ControllerParams;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ControllerParams;

    public $viewAction = '';

    public $params = [];

    public $model;

    public function __construct(Request $request, MainModel $model)
    {
        $this->initializeParams($request);
        $this->model = $model;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view($this->viewAction, ['params' => $this->params]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function createRoot()
    {
        $root = $this->model->find(1);
        if ($root) {
            return response()->json([
                'success' => false,
                'message' => 'Chuyên mục gốc đã tồn tại',
            ]);
        }
        $root = $this->model->create([
            'name' => 'Root',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Chuyên mục gốc đã được tạo',
            'data' => $root,
        ]);
    }
}
