@if ($crud->hasAccess('show'))
	<a href="{{ url($crud->route.'/page/'.$entry->getKey()) }}" class="btn btn-xs btn-default"><i class="fa fa-eye"></i> {{ trans('backpack::crud.preview') }}</a>
@endif
