<?php
namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\GameRequest as StoreRequest;
use App\Http\Requests\GameRequest as UpdateRequest;

class GameCrudController extends CrudController
{
    public function setUp()
    {

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel("App\Models\Game");
        $this->crud->setRoute("admin/game");
        $this->crud->setEntityNameStrings('game', 'games');
        $this->crud->enableAjaxTable();

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */

        //$this->crud->setFromDb();

        $this->crud->addField(['name'  => 'name' ,'attributes' => ['required' => 'required']]);
        $this->crud->addField(['name'  => 'description', 'type' => 'summernote']);

        $this->crud->addField(['name'  => 'cover_generator', 'label' => 'Enable cover generator', 'type' => 'toggle', 'hint' => 'Add platform bar with logo on top of game cover.']);

        $this->crud->addField(['name'  => 'cover', 'type'  => 'image_generator' ,
    'upload' => true,
    'crop' => true ]);


        $this->crud->addField(['name'  => 'release_date','type' => 'date_picker', 'attributes' => ['required' => 'required']]);
        $this->crud->addField(['name'  => 'publisher']);
        $this->crud->addField(['name'  => 'developer']);
        $this->crud->addField(['name'  => 'pegi','type'  => 'enum']);

        $this->crud->addField(['label' => "Platform", 'type' => 'select2', 'name' => 'platform_id', 'entity' => 'platform', 'attribute' => 'name', 'model' => "App\Models\Platform" ]);
        $this->crud->addField(['label' => "Genre", 'type' => 'select', 'name' => 'genre_id', 'entity' => 'genre', 'attribute' => 'name', 'model' => "App\Models\Genre" ]);

        // hidden columns
        $this->crud->addColumn(['name' => 'release_date']);
        $this->crud->addColumn(['name' => 'cover']);
        $this->crud->addColumn(['name' => 'giantbomb_id']);

        $this->crud->addColumn(['name' => 'name', 'type' => 'model_function','function_name' => 'getNameAdmin']);
        $this->crud->addColumn(['name' => 'platform_id','type' => 'model_function','function_name' => 'getConsoleAdmin']);
        $this->crud->addColumn(['name' => 'publisher']);
        $this->crud->addColumn(['name' => 'name', 'label' => 'Active Listings', 'type' => 'model_function','function_name' => 'getListingsAdmin']);


        $this->crud->addButtonFromView('top', 'add', 'create_game', 'beginning');
        // ------ CRUD FIELDS
        // $this->crud->addField($options, 'update/create/both');
        // $this->crud->addFields($array_of_arrays, 'update/create/both');
        // $this->crud->removeField('name', 'update/create/both');
        // $this->crud->removeFields($array_of_names, 'update/create/both');

        // ------ CRUD COLUMNS
        // $this->crud->addColumn(); // add a single column, at the end of the stack
        // $this->crud->addColumns(); // add multiple columns, at the end of the stack
        // $this->crud->removeColumn('column_name'); // remove a column from the stack
        // $this->crud->removeColumns(['column_name_1', 'column_name_2']); // remove an array of columns from the stack
        // $this->crud->setColumnDetails('column_name', ['attribute' => 'value']); // adjusts the properties of the passed in column (by name)
        // $this->crud->setColumnsDetails(['column_1', 'column_2'], ['attribute' => 'value']);

        // ------ CRUD BUTTONS
        // possible positions: 'beginning' and 'end'; defaults to 'beginning' for the 'line' stack, 'end' for the others;
        // $this->crud->addButton($stack, $name, $type, $content, $position); // add a button; possible types are: view, model_function
        // $this->crud->addButtonFromModelFunction($stack, $name, $model_function_name, $position); // add a button whose HTML is returned by a method in the CRUD model
        // $this->crud->addButtonFromView($stack, $name, $view, $position); // add a button whose HTML is in a view placed at resources\views\vendor\backpack\crud\buttons
        // $this->crud->removeButton($name);
        // $this->crud->removeButtonFromStack($name, $stack);

        // ------ CRUD ACCESS
        // $this->crud->allowAccess(['list', 'create', 'update', 'reorder', 'delete']);
        // $this->crud->denyAccess(['list', 'create', 'update', 'reorder', 'delete']);

        // ------ CRUD REORDER
        // $this->crud->enableReorder('label_name', MAX_TREE_LEVEL);
        // NOTE: you also need to do allow access to the right users: $this->crud->allowAccess('reorder');

        // ------ CRUD DETAILS ROW
        // $this->crud->enableDetailsRow();
        // NOTE: you also need to do allow access to the right users: $this->crud->allowAccess('details_row');
        // NOTE: you also need to do overwrite the showDetailsRow($id) method in your EntityCrudController to show whatever you'd like in the details row OR overwrite the views/backpack/crud/details_row.blade.php

        // ------ REVISIONS
        // You also need to use \Venturecraft\Revisionable\RevisionableTrait;
        // Please check out: https://laravel-backpack.readme.io/docs/crud#revisions
        // $this->crud->allowAccess('revisions');

        // ------ AJAX TABLE VIEW
        // Please note the drawbacks of this though:
        // - 1-n and n-n columns are not searchable
        // - date and datetime columns won't be sortable anymore
        // $this->crud->enableAjaxTable();


        // ------ DATATABLE EXPORT BUTTONS
        // Show export to PDF, CSV, XLS and Print buttons on the table view.
        // Does not work well with AJAX datatables.
        // $this->crud->enableExportButtons();

    // ------ ADVANCED QUERIES
    }

    public function store(StoreRequest $request)
    {
        return parent::storeCrud();
    }

    public function update(UpdateRequest $request)
    {
        return parent::updateCrud();
    }

    /**
     * Respond with the JSON of one or more rows, depending on the POST parameters.
     * @return JSON Array of cells in HTML form.
     */
    public function search()
    {
        $this->crud->hasAccessOrFail('list');

        // crate an array with the names of the searchable columns
        $columns = collect($this->crud->columns)
                                ->reject(function ($column, $key) {
                                    // the select_multiple columns are not searchable
                                        return isset($column['type']) && $column['type'] == 'select_multiple';
                                })
                                ->pluck('name')
                                // add the primary key, otherwise the buttons won't work
                                ->merge($this->crud->model->getKeyName())
                                ->toArray();

        // details row fix
        if ($this->crud->details_row) {
            array_unshift($columns, 'id');
        }

        // structure the response in a DataTable-friendly way
        $dataTable = new \LiveControl\EloquentDataTable\DataTable($this->crud->query, $columns);

        // make the datatable use the column types instead of just echoing the text
        $dataTable->setFormatRowFunction(function ($entry) {
            // get the actual HTML for each row's cell
                $row_items = $this->crud->getRowViews($entry, $this->crud);

                // add the buttons as the last column
                if ($this->crud->buttons->where('stack', 'line')->count()) {
                    $row_items[] = \View::make('crud::inc.button_stack', ['stack' => 'line'])
                                                        ->with('crud', $this->crud)
                                                        ->with('entry', $entry)
                                                        ->render();
                }

                // add the details_row buttons as the first column
                if ($this->crud->details_row) {
                    array_unshift($row_items, \View::make('crud::columns.details_row_button')
                                                        ->with('crud', $this->crud)
                                                        ->with('entry', $entry)
                                                        ->render());
                }

            return $row_items;
        });

        return $dataTable->make();
    }
}
