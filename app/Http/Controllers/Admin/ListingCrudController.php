<?php
namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\ListingRequest as StoreRequest;
use App\Http\Requests\ListingRequest as UpdateRequest;

class ListingCrudController extends CrudController
{
    public function setUp()
    {

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel("App\Models\Listing");
        $this->crud->setRoute("admin/listing");
        $this->crud->setEntityNameStrings('listing', 'listings');

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */

        // ------ CRUD FIELDS
        $this->crud->addFilter([
            'type' => 'simple',
            'name' => 'active',
            'label'=> 'Active'
        ], false, function ($values) { // if the filter is active
            $this->crud->addClause('where', 'status', '0');
            $this->crud->addClause('orWhere', 'status', null);
        });

        $this->crud->addFilter([
            'type' => 'simple',
            'name' => 'sold',
            'label'=> 'Sold'
        ], false, function ($values) { // if the filter is active
            $this->crud->addClause('where', 'status', '1');
        });

        $this->crud->addFilter([
            'type' => 'simple',
            'name' => 'complete',
            'label'=> 'Complete'
        ], false, function ($values) { // if the filter is active
            $this->crud->addClause('where', 'status', '2');
        });


        //$this->crud->setFromDb();
        $this->crud->addFilter([
            'type' => 'simple',
            'name' => 'trashed',
            'label'=> 'Trashed'
        ], false, function ($values) { // if the filter is active
            $this->crud->query = $this->crud->query->onlyTrashed();
        });

        // ------ CRUD COLUMNS
        $this->crud->addColumn(['name' => 'sell', 'label' => 'Sell', 'type' => 'text']);
        $this->crud->addColumn(['name' => 'status', 'label' => 'Status', 'type' => 'model_function','function_name' => 'getStatusAdmin']);
        $this->crud->addColumn(['name' => 'user_id', 'label' => 'User', 'type' => 'model_function','function_name' => 'getUserAdmin']);
        $this->crud->addColumn(['name' => 'game_id', 'label' => 'Game', 'type' => 'model_function','function_name' => 'getGameAdmin']);
        $this->crud->addColumn(['name' => 'price', 'label' => 'Price', 'type' => 'model_function','function_name' => 'getPriceAdmin']);
        $this->crud->addColumn(['name' => 'trade', 'label' => 'Trade', 'type' => 'model_function','function_name' => 'getTradeAdmin']);
        $this->crud->addColumn(['name' => 'created_at', 'label' => 'Created', 'type' => 'model_function','function_name' => 'getDateAdmin']);
        $this->crud->addColumn(['name' => 'clicks', 'label' => 'Clicks', 'type' => 'text']);

        // ------ CRUD BUTTONS
        $this->crud->addButtonFromView('top', 'add', 'create_listing', 'top');
        $this->crud->addButtonFromView('line', 'edit', 'edit_listing', 'beginning');
        $this->crud->addButtonFromView('line', 'show', 'show_listing', 'beginning');
        $this->crud->removeButton('create');
        $this->crud->removeButton('update');

        // ------ CRUD ACCESS
        // $this->crud->allowAccess(['list', 'create', 'update', 'reorder', 'delete']);
        // $this->crud->denyAccess(['list', 'create', 'update', 'reorder', 'delete']);

        // ------ CRUD REORDER
        // $this->crud->enableReorder('label_name', MAX_TREE_LEVEL);
        // NOTE: you also need to do allow access to the right users: $this->crud->allowAccess('reorder');

        // ------ CRUD DETAILS ROW
        $this->crud->enableDetailsRow();
        $this->crud->allowAccess('details_row');
        // ------ REVISIONS
        // You also need to use \Venturecraft\Revisionable\RevisionableTrait;
        // Please check out: https://laravel-backpack.readme.io/docs/crud#revisions
        // $this->crud->allowAccess('revisions');

       // ------ AJAX TABLE VIEW
        $this->crud->enableAjaxTable();

        // ------ DATATABLE EXPORT BUTTONS
        // Show export to PDF, CSV, XLS and Print buttons on the table view.
        // Does not work well with AJAX datatables.
        // $this->crud->enableExportButtons();

        // ------ ADVANCED QUERIES
        //$this->crud->orderBy('created_at','desc');
        $this->crud->with('game', 'user');
    }

    public function store(StoreRequest $request)
    {
        return parent::storeCrud();
    }

    public function update(UpdateRequest $request)
    {
        return parent::updateCrud();
    }

    public function showDetailsRow($id)
    {
        $listing = \App\Models\Listing::with('game', 'user', 'game.giantbomb', 'game.platform')->find($id);

        // Trade list
        if ($listing->trade_list) {
            $trade_list = \App\Models\Game::whereIn('id', array_keys(json_decode($listing->trade_list, true)))->with('giantbomb', 'platform')->get();
        } else {
            $trade_list = null;
        }

        return view('backend.listings_details_row', ['trade_list' => $trade_list]);
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
