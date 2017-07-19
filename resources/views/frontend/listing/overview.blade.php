@extends('frontend.layouts.app')


@section('subheader')

<div style="position: relative;  height: 0px; ">


  <div style="position: absolute; z-index:0 !important; top: 0; width: 100%;">
    @if(!is_null($system))
    <div style="background-color: {{$system->color}}; height: 300px; margin-top: -60px; z-index: 0; position: relative;"></div>
    @endif

    <div style="background: linear-gradient(0deg, rgba(26,24,24,1) 0%, rgba(26,24,24,0.6) 50%, rgba(26,24,24,0) 100%),url('{{ asset('/img/game_pattern_white.png') }}'); z-index: 1; height: 300px; position: absolute; top: 0; width: 100%; margin-top: -60px;"></div>

  </div>

</div>

@stop



@section('content')

@if(!is_null($system))

<div style="margin-bottom: 50px;">
  {{-- Check if platform logo setting is enabled --}}
  @if( config('settings.platform_logo') )
    <img src="{{ asset('logos/' . $system->acronym . '.png/') }}" alt="" height="40">
  @else
    <span class="platform-title">{{$system->name}}</span>
  @endif
</div>

@endif

{{-- Load Google AdSense --}}
@if(config('settings.google_adsense'))
  @include('frontend.ads.google')
@endif

{{-- Listings title --}}
<div style="margin-bottom: 20px; font-size: 24px; color: #fff; font-weight: 700; "><i class="fa fa-tags" aria-hidden="true"></i> {{ trans('general.listings') }}</div>

{{-- Start Filter / Sort options --}}
<div class="m-b-20 flex-center-space">
    {{-- Start Filter button --}}
    <div>
        {{-- Filter Button with active filter count - open modal --}}
        <a href="#" data-toggle="modal" data-target="#modal_filter" class="btn btn-dark">
            <i class="fa fa-filter" aria-hidden="true"></i> {{ trans('general.sortfilter.filter') }} @if(session()->get('listingsPlatformFilter') || session()->has('listingsOptionFilter')) ({{count(session()->get('listingsPlatformFilter'))+count(session()->get('listingsOptionFilter'))}}) @endif
        </a>
        {{-- Remove button - only visible with active filters --}}
        @if(session()->has('listingsPlatformFilter') || session()->has('listingsOptionFilter'))
        <a href="{{ url('listings/filter/remove') }}" class="m-l-5 btn btn-dark">
            <i class="fa fa-times" aria-hidden="true"></i>
        </a>
        @endif
    </div>
    {{-- End Filter button --}}
    {{-- Start sort options --}}
    <div>
        {{-- Sort order button (desc / asc) --}}
        <a href="{{ url('listings/order') }}/{{ session()->has('listingsOrder') ? session()->get('listingsOrder') : 'created_at' }}/{{  session()->has('listingsOrderByDesc') ? (session()->get('listingsOrderByDesc') ? 'asc' : 'desc') : 'asc' }}" class="btn btn-dark" style="vertical-align: inherit;">
            <i class="fa fa-sort-{{ session()->has('listingsOrderByDesc') ? (session()->get('listingsOrderByDesc') ? 'asc' : 'desc') : 'asc' }}" aria-hidden="true"></i>
        </a>
        {{-- Sort dropdown --}}
        <div class="m-l-5 inline-block">
            <select id="order_by" class="form-control select" style="height: 33px !important;">
                {{-- Sort by --}}
                <option disabled>{{ trans('general.sortfilter.sort_by') }}</option>
                {{-- Created at option --}}
                <option value="{{ url('listings/order/created_at') }}" {{ session()->has('listingsOrder') ? (session()->get('listingsOrder') == 'created_at' ? 'selected' : '') : '' }}>{{ trans('general.sortfilter.sort_date') }}</option>
                {{-- Price option --}}
                <option value="{{ url('listings/order/price') }}" {{ session()->has('listingsOrder') ? (session()->get('listingsOrder') == 'price' ? 'selected' : '') : '' }}>{{ trans('general.sortfilter.sort_price') }}</option>
                {{-- Distance option --}}
                @if((\Auth::check() && (\Auth::user()->location && \Auth::user()->location->longitude && \Auth::user()->location->latitude)) || (session()->has('latitude') && session()->has('longitude')))
                <option value="{{ url('listings/order/distance') }}" {{ session()->has('listingsOrder') ? (session()->get('listingsOrder') == 'distance' ? 'selected' : '') : '' }}>{{ trans('general.sortfilter.sort_distance') }}</option>
                @endif
            </select>
        </div>
    </div>
    {{-- End sort options --}}
</div>
{{-- End Filter / Sort options --}}

{{-- Start modal for filter options --}}
<div class="modal fade modal-fade-in-scale-up modal-dark" id="modal_filter" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header">

        <div class="background-pattern" style="background-image: url('{{ asset('/img/game_pattern.png') }}');"></div>

        <div class="title">
          <button type="button" class="close" data-dismiss="modal">
            <span aria-hidden="true">×</span><span class="sr-only">{{ trans('general.close') }}</span>
          </button>
          {{-- Title (Filter) --}}
          <h4 class="modal-title" id="myModalLabel">
            <i class="fa fa-filter" aria-hidden="true"></i>
            {{ trans('general.sortfilter.filter') }}
          </h4>
        </div>

      </div>
      @if(is_null($system))
      {{-- Start platform filters --}}
      <div class="modal-seperator">
          {{ trans('general.sortfilter.filter_platforms') }}
      </div>
      <div class="modal-body">
          @php
              // Get all platforms
              $platforms = DB::table('platforms')->get();
              // Active filters
              $active_filters = session()->get('listingsPlatformFilter') ?  session()->get('listingsPlatformFilter') : [];
          @endphp
          @foreach($platforms as $platform)
              {{-- Platform label --}}
              <a href="#" class="label platform-label platform-filter m-r-5 m-b-5 inline-block {{ in_array($platform->id, $active_filters) ? 'platform-filter-active' : '' }}" data-id="{{$platform->id}}" data-color="{{$platform->color}}" @if(in_array($platform->id, $active_filters)) style="background-color:{{$platform->color}};" @endif>
                  {{ $platform->name }}
              </a>
          @endforeach

      </div>
      {{-- End platform filters --}}
      @endif
      {{-- Start option filters --}}
      <div class="modal-seperator">
          {{ trans('general.sortfilter.filter_options') }}
      </div>
      <div class="modal-body">
          @php
              $active_options = session()->get('listingsOptionFilter') ?  session()->get('listingsOptionFilter') : [];
          @endphp
          {{-- Sell option --}}
          <a href="#" class="label platform-label option-filter m-r-5 m-b-5 inline-block {{ in_array('sell', $active_options) ? 'option-filter-active' : '' }}" data-filter="sell">
            <i class="fa fa-shopping-basket" aria-hidden="true"></i> {{ trans('listings.general.sell') }}
          </a>
          {{-- Trade option --}}
          <a href="#" class="label platform-label option-filter m-r-5 m-b-5 inline-block {{ in_array('trade', $active_options) ? 'option-filter-active' : '' }}" data-filter="trade">
              <i class="fa fa-exchange" aria-hidden="true"></i> {{ trans('listings.general.trade') }}
          </a>
          {{-- Pickup option --}}
          <a href="#" class="label platform-label option-filter m-r-5 m-b-5 inline-block {{ in_array('pickup', $active_options) ? 'option-filter-active' : '' }}" data-filter="pickup">
              <i class="fa fa-handshake-o" aria-hidden="true"></i> {{ trans('listings.general.pickup') }}
          </a>
          {{-- Delivery option --}}
          <a href="#" class="label platform-label option-filter m-r-5 m-b-5 inline-block {{ in_array('delivery', $active_options) ? 'option-filter-active' : '' }}" data-filter="delivery">
              <i class="fa fa-truck" aria-hidden="true"></i> {{ trans('listings.general.delivery') }}
          </a>
          {{-- Digital download option --}}
          <a href="#" class="label platform-label option-filter m-r-5 m-b-5 inline-block {{ in_array('digital', $active_options) ? 'option-filter-active' : '' }}" data-filter="digital">
              <i class="fa fa-download" aria-hidden="true"></i> {{ trans('listings.form.details.digital') }}
          </a>
          {{-- Secure payment option --}}
          <a href="#" class="label platform-label option-filter m-r-5 m-b-5 inline-block {{ in_array('payment', $active_options) ? 'option-filter-active' : '' }}" data-filter="payment">
              <i class="fa fa-shield" aria-hidden="true"></i> {{ trans('payment.secure_payment') }}
          </a>
      </div>
      {{-- End option filters --}}
      <div class="modal-footer">
        {{-- Cancel button --}}
        <a href="#" data-dismiss="modal" data-bjax class="btn btn-lg btn-dark btn-animate btn-animate-vertical">
          <span><i class="icon fa fa-times" aria-hidden="true"></i> {{ trans('general.cancel') }}</span>
        </a>
        {{-- Filter submit button --}}
        <a class="btn btn-lg btn-success btn-animate btn-animate-vertical" id="filter-submit" href="#">
          <span>
            <i class="icon fa fa-filter" aria-hidden="true"></i> {{ trans('general.sortfilter.filter') }}
          </span>
        </a>
      </div>
    </div>
  </div>
</div>
{{-- End modal for filter options --}}

  {{-- START LISTINGS --}}
  <div class="row">

    @forelse($listings as $listing)
      {{-- START GAME --}}
      <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3 col-xl-2 m-b-20">

        {{-- Start Game Cover --}}
        <div class="card game-cover-wrapper hvr-grow-shadow">
          {{-- Pacman Loader for background image - show only when cover exists --}}
          @if($listing->game->image_cover)
          <div class="loader pacman-loader cover-loader"></div>
          {{-- Show game name, when no cover exist --}}
          @else
          <div class="no-cover-name">{{$listing->game->name}}</div>
          @endif

          <a href="{{ $listing->url_slug }}">
            {{-- Start Trade Ribbon --}}
            @if($listing->trade)
            <div class="ribbon ribbon-clip ribbon-bottom ribbon-trade" style="@if($listing->sell) margin-bottom: 50px; @else margin-bottom: 10px; @endif">
              <span class="ribbon-inner"><i class="icon fa fa-exchange" aria-hidden="true"></i></span>
            </div>
            @endif
            {{-- End Trade Ribbon --}}

            {{-- Start Sell Ribbon --}}
            @if($listing->sell)
            <div class="ribbon ribbon-clip ribbon-bottom ribbon-sell">
              <div class="ribbon-inner">
                <span class="currency">{{ Currency(Config::get('settings.currency'))->getSymbol() }}</span>
                <span class="price"> {{ $listing->getPrice(false) }}</span>
              </div>
            </div>
            @endif
            {{-- End Sell Ribbon --}}

            {{-- Payment icon --}}
            @if($listing->payment)
            <div class="animation-scale-up payment-enabled">
              <i class="fa fa-shield" aria-hidden="true"></i>
            </div>
            @endif

            {{-- Digital download icon --}}
            @if($listing->digital)
            <div class="animation-scale-up digital-download {{ $listing->payment ? 'with-payment' : '' }}">
              <i class="fa fa-download" aria-hidden="true"></i>
            </div>
            @endif

            {{-- Pickup icon --}}
            @if($listing->pickup)
            <div class="pickup-icon {{ $listing->digital ? 'with-digital' : '' }} {{ $listing->payment ? 'with-payment' : '' }}">
              <i class="fa fa-handshake-o" aria-hidden="true"></i>
            </div>
            @endif

            {{-- Delivery icon --}}
            @if($listing->delivery)
            <div class="delivery-icon {{ $listing->pickup ? 'with-pickup' : '' }} {{ $listing->digital ? 'with-digital' : '' }} {{ $listing->payment ? 'with-payment' : '' }}">
              <i class="fa fa-truck" aria-hidden="true"></i>
            </div>
            @endif

            {{-- Generated game cover with platform on top --}}
            @if($listing->game->cover_generator)
              <div class="lazy game-cover gen"  data-original="{{$listing->game->image_cover}}"></div>
              <div class="game-platform-gen" style="background-color: {{$listing->game->platform->color}}; text-align: {{$listing->game->platform->cover_position}};">
                {{-- Check if platform logo setting is enabled --}}
                @if( config('settings.platform_logo') )
                  <img src="{{ asset('logos/' . $listing->game->platform->acronym . '_tiny.png/') }}" alt="{{$listing->game->platform->name}} Logo">
                @else
                  <span>{{$listing->game->platform->name}}</span>
                @endif
              </div>
            {{-- Normal game cover --}}
            @else
              <div class="lazy game-cover"  data-original="{{$listing->game->image_cover}}"></div>
            @endif
          </a>
        </div>
        {{-- End Game Cover --}}

        {{-- Start User info --}}
        <div class="game-user-details">
          {{-- Distance --}}
          @if($listing->distance)
          <span class="distance">
            <i class="fa fa-location-arrow" aria-hidden="true"></i> {{$listing->distance}} {{config('settings.distance_unit')}}
          </span>
          @endif
          {{-- User avtar and name --}}
          <a href="{{ $listing->user->url }}" class="user-link">
            <span class="avatar avatar-xs @if($listing->user->isOnline()) avatar-online @else avatar-offline @endif">
              <img src="{{$listing->user->avatar_square_tiny}}" alt="{{$listing->user->name}}'s Avatar"><i></i>
            </span>
            {{$listing->user->name}}
          </a>
        </div>
        {{-- End User info --}}
      </div>
      {{-- End GAME --}}
    @empty
      {{-- Start empty list message --}}
      <div class="empty-list">
        {{-- Icon --}}
        <div class="icon">
          <i class="fa fa-frown-o" aria-hidden="true"></i>
        </div>
        {{-- Text --}}
        <div class="text">
          {{ trans('listings.general.no_listings') }}
        </div>
      </div>
      {{-- End empty list message --}}
    @endforelse

  </div>
  {{-- END LISTINGS --}}


{{ $listings->links() }}

@section('after-scripts')


<script type="text/javascript">
$(document).ready(function(){
    {{-- Platform filter --}}
    $('.platform-filter').click(function(e) {
        e.preventDefault();
        $(this).toggleClass('platform-filter-active')
        if ($(this).hasClass('platform-filter-active')) {
            $(this).css('background-color', $(this).data('color') );
        } else {
            $(this).css('background-color', '');
        }
    });

    {{-- Option filter --}}
    $('.option-filter').click(function(e) {
        e.preventDefault();
        $(this).toggleClass('option-filter-active')
    });

    {{-- Submit filter options --}}
    $('#filter-submit').click(function(e) {
        e.preventDefault();
        $(this).html('<i class="fa fa-spinner fa-pulse fa-fw"></i>');
        $(this).addClass('loading');
        {{-- Collect all active platform ids --}}
        var platform_ids = [];
        $('.platform-filter-active').each(function() {
            platform_ids.push($(this).data("id"))
        });
        {{-- Collect all active options --}}
        var options = [];
        $('.option-filter-active').each(function() {
            options.push($(this).data("filter"))
        });
        $.ajax({
            url:'{{ url("listings/filter") }}',
            type: 'POST',
            data: {platformIds:platform_ids, options: options},
            {{-- Send CSRF Token over ajax --}}
            headers: { 'X-CSRF-TOKEN': Laravel.csrfToken },
            success: function (data) {
                window.location=data;
            }
        });
    });

    {{-- Order by change URL --}}
    $('#order_by').change(function () {
        var goToUrl = $(this).val();
        window.location.replace(goToUrl);
        window.location.href = goToUrl;
    });

  $(window).on("scroll", function() {
    if($(window).scrollTop() >= 30){
      $('.site-navbar').css('background-color','rgba(34,33,33,1)');
      $(".sticky-header").removeClass('slide-up')
      $(".sticky-header").addClass('slide-down')
    }else{
      $('.site-navbar').css('background','linear-gradient(0deg, rgba(34,33,33,0) 0%, rgba(34,33,33,0.8) 100%)');
    }
      var fromTop = $(window).scrollTop();
      $(".sticky-header").toggleClass("down", (fromTop > 100));
  });

});
</script>
@endsection



@stop
