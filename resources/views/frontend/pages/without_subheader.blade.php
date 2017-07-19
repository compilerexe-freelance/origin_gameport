@extends('frontend.layouts.app')

@section('content')
  <div class="panel">
    <div class="panel-body">
      {!! $page->content !!}
    </div>
  </div>
@stop
