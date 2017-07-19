@extends('frontend.layouts.app')
@include('frontend.user.subheader')

@section('content')
@yield('user-content')


<!-- Nav tabs -->
<ul class="subheader-tabs m-t-50" role="tablist">
  <li class="nav-item">
    <a data-toggle="tab" href="#listings" data-target="#listings" role="tab" class="subheader-link">
      <i class="fa fa-tags" aria-hidden="true"></i><span class="hidden-xs-down"> {{ trans('users.profile.listings') }}</span>
    </a>
  </li>
  @if($user->ratings->count() > 0)
  <li class="nav-item">
    <a data-toggle="tab" href="#ratings" data-target="#ratings" role="tab" class="subheader-link">
      <i class="fa fa-thumbs-up" aria-hidden="true"></i><span class="hidden-xs-down"> {{ trans('users.profile.ratings') }}</span>
    </a>
  </li>
  @endif
  {{-- <li class="nav-item">
    <a data-toggle="tab" href="#" data-target="#media" role="tab" class="subheader-link">
      <i class="fa fa-bar-chart" aria-hidden="true"></i><span class="hidden-xs-down"> {{ trans('users.profile.stats') }}</span>
    </a>
  </li> --}}
</ul>

<div class="tab-content subheader-margin">

  {{-- START LISTINGS --}}
  <div class="row tab-pane fade" id="listings" role="tabpanel">

    @forelse($listings as $listing)
    {{-- START GAME --}}
    <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3 col-xl-2">
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
              <span class="currency">{{ Currency(Config::get('settings.currency'))->getSymbol() }}</span><span class="price"> {{ $listing->getPrice(false) }}</span>
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
    </div>
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

  {{-- START RATINGS --}}
  <div class="tab-pane fade" id="ratings" role="tabpanel">
    @forelse ($ratings as $rating)
      @php
        $user_rating = \App\Models\User::find($rating->user_id_from);
        if($rating->rating == 2){
          $bg = 'bg-success';
          $icon = 'fa-thumbs-up';
        }else if($rating->rating == 1){
          $bg = 'bg-dark';
          $icon = 'fa-minus';
        }else{
          $bg = 'bg-danger';
          $icon = 'fa-thumbs-down';
        }
      @endphp
      {{-- Start Rating --}}
      <section class="panel rating-panel hvr-grow-shadow2 {{$bg}}">
        <div class="background-pattern" style="background-image: url('{{ asset('/img/game_pattern.png') }}') !important;"></div>
        <div class="background-color" style="border-radius: 5px;"></div>

        <div class="panel-body">
          {{-- Rating icon --}}
          <i class="fa {{$icon}} rating-icon" aria-hidden="true"></i>
          {{-- User avatar --}}
          <span class="avatar">
            <img src="{{$user_rating->avatar_square}}" alt="{{$user_rating->name}}'s Avatar">
          </span>
          {{-- Notice --}}
          <div>
            <span class="from-user">{{ trans('users.profile.rating_from', ['username' => $user_rating->name]) }}</span>
            {{-- Rating notice --}}
            @if($rating->notice)
              {{-- Head text with username from rater--}}
              <span class="notice"><i class="fa fa-quote-left" aria-hidden="true"></i> {{$rating->notice}} <i class="fa fa-quote-right" aria-hidden="true"></i></span>
            @else
              {{-- No notice --}}
              <span class="notice">{{ trans('offers.status_complete.no_notice') }}</span>
            @endif
          </div>

        </div>
      </section>
      {{-- End Rating --}}
    @empty
      <div style="text-align: center;">
        {{-- No Ratings --}}
        <div style="text-align: center; display: block;">
          <span class="fa-stack fa-lg" style="font-size: 50px;text-align: center;">
            <i class="fa fa-thumbs-up fa-stack-1x"></i>
            <i class="fa fa-ban fa-stack-2x text-danger"></i>
          </span>
        </div>
        <span class="no-ratings">{{ trans('users.general.no_ratings') }}</span>
      </div>
    @endforelse
  </div>
  {{-- END RATINGS --}}

  {{-- Start Edit / Delete when user has permission --}}
  @if(Auth::check() && Auth::user()->hasPermission('edit_users'))
  <div>
    @if($user->isActive())
      <a href="{{ url(config('backpack.base.route_prefix', 'admin') . '/user/' . $user->id . '/ban') }}" class="btn btn-danger m-r-5"><i class="fa fa-trash"></i> Ban</a>
    @else
      <a href="{{ url(config('backpack.base.route_prefix', 'admin') . '/user/' . $user->id . '/ban') }}" class="btn btn-success m-r-5"><i class="fa fa-check-circle"></i> Unban</a>
    @endif
    <a href="{{ url(config('backpack.base.route_prefix', 'admin') . '/user/' . $user->id . '/edit') }}" class="btn btn-dark" target="_blank"><i class="fa fa-edit"></i> {{ trans('general.edit') }}</a>
  </div>
  @endif

</div>



@section('after-scripts')


<script src="//cdnjs.cloudflare.com/ajax/libs/masonry/4.1.1/masonry.pkgd.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.1/imagesloaded.pkgd.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>


<script type="text/javascript">
$(document).ready(function(){

  {{-- Javascript to enable link to tab --}}
  var hash = document.location.hash;
  var prefix = "!";
  if (hash) {
      hash = hash.replace(prefix,'');
      var hashPieces = hash.split('?');
      activeTab = $('[role="tablist"] [data-target="' + hashPieces[0] + '"]');
      activeTab && activeTab.tab('show');

      var $this = activeTab,
      loadurl = $this.attr('href'),
      targ = $this.attr('data-target');


      if( !$.trim( $(targ).html() ).length ){

        $.ajax({
            url: loadurl,
            type: 'GET',
            beforeSend: function() {
                // TODO: show your spinner
                $('#loading').show();
            },
            complete: function() {
                // TODO: hide your spinner
                $('#loading').hide();
            },
            success: function(result) {
              $(targ).html(result);
            }
        });


      }


  }else{
      activeTab = $('[role="tablist"] [data-target="#listings"]');
      activeTab && activeTab.tab('show');
  }

  {{-- Change hash for page-reload --}}
  $('[role="tablist"] a').on('shown.bs.tab', function (e) {
      var $this = $(this),
      loadurl = $this.attr('href'),
      targ = $this.attr('data-target');

      if( !$.trim( $(targ).html() ).length ){

        $.ajax({
            url: loadurl,
            type: 'GET',
            beforeSend: function() {
                // TODO: show your spinner
                $('#loading').show();
            },
            complete: function() {
                // TODO: hide your spinner
                $('#loading').hide();
            },
            success: function(result) {
              $(targ).html(result);
            }
        });


      }


      window.location.hash = targ.replace("#", "#" + prefix);


  });

  {{-- Navbar scroll effect --}}
  var scroll = function () {
    if(lastScrollTop >= 30){
      $('.site-navbar').css('background-color','rgba(34,33,33,1)');
      $(".sticky-header").removeClass('slide-up')
      $(".sticky-header").addClass('slide-down')
    }else{
      $('.site-navbar').css('background','linear-gradient(0deg, rgba(34,33,33,0) 0%, rgba(34,33,33,0.8) 100%)');
    }
  };
  var raf = window.requestAnimationFrame ||
      window.webkitRequestAnimationFrame ||
      window.mozRequestAnimationFrame ||
      window.msRequestAnimationFrame ||
      window.oRequestAnimationFrame;
  var $window = $(window);
  var lastScrollTop = $window.scrollTop();

  if (raf) {
      loop();
  }

  function loop() {
      var scrollTop = $window.scrollTop();
      if (lastScrollTop === scrollTop) {
          raf(loop);
          return;
      } else {
          lastScrollTop = scrollTop;

          // fire scroll function if scrolls vertically
          scroll();
          raf(loop);
      }
  }



});
</script>
@endsection


@stop
