@section('subheader')
<div class="subheader-image-bg">
  <div class="bg-image-wrapper">
    {{-- Background image of subheader --}}
    @if($game->image_cover)
    <div class="bg-image" style="background: linear-gradient(0deg, rgba(25,24,24,1) 0%, rgba(25,24,24,0.8) 30%, rgba(25,24,24,0) 80%), url({{ $game->image_cover }});"></div>
    {{-- Default background when image cover is missing --}}
    @else
    <div class="bg-image no-image" style="background: linear-gradient(0deg, rgba(25,24,24,1) 0%, rgba(25,24,24,0.8) 30%, rgba(25,24,24,0) 80%), url({{ asset('/img/game_pattern_white.png') }});"></div>
    @endif
  </div>
  {{-- background color overlay --}}
  <div class="bg-color"></div>
</div>

{{-- Listing sold overlay --}}
@if((isset($listing) && ($listing->status != 0 && !is_null($listing->status))) || isset($listing) && !$listing->user->isActive() )
  <div class="listing-sold-overlay flex-center">
    <div class="msg">
      <div class="msg bg-danger">
        <i class="fa fa-times"></i> {{ trans('listings.general.sold') }}
      </div>
      {{-- Gameover Button --}}
      <div class="m-t-20 text-center">
        <a class="gameoverview-button" href="{{ $game->url_slug }}"><i class="fa fa-angle-double-right" aria-hidden="true"></i> {{ trans('listings.overview.subheader.go_gameoverview') }}</a>
      </div>
    </div>
  </div>
@endif


@endsection

@section('game-content')

<div class="row no-space">
  <div class="offset-xs-3 col-xs-6 offset-sm-0 col-sm-4 col-lg-3 col-xxl-2">

    {{-- Start Game Cover --}}
    <div class="game-cover-wrapper shadow">
      {{-- Pacman Loader for background image - show only when cover exists --}}
      @if($game->image_cover)
      <div class="loader pacman-loader cover-loader"></div>
      {{-- Show game name, when no cover exist --}}
      @else
      <div class="no-cover-name">{{$game->name}}</div>
      @endif

      {{-- Start Trade Ribbon --}}
      @if(isset($listing) && $listing->trade)
      <div class="ribbon ribbon-clip ribbon-bottom ribbon-trade" style="@if($listing->sell) margin-bottom: 50px; @else margin-bottom: 10px; @endif">
        <span class="ribbon-inner"><i class="icon fa fa-exchange" aria-hidden="true"></i></span>
      </div>
      @endif
      {{-- End Trade Ribbon --}}

      {{-- Start Sell Ribbon --}}
      @if(isset($listing) && $listing->sell)
      <div class="ribbon ribbon-clip ribbon-bottom ribbon-sell">
        <div class="ribbon-inner">
          <span class="currency">{{ Currency(Config::get('settings.currency'))->getSymbol() }}</span><span class="price"> {{ $listing->getPrice(false) }}</span>
        </div>
      </div>
      @endif
      {{-- End Sell Ribbon --}}

      {{-- Digital download icon --}}
      @if(isset($listing) && $listing->digital)
      <div class="animation-scale-up digital-download">
        <i class="fa fa-download" aria-hidden="true"></i>
      </div>
      @endif

      {{-- Generated game cover with platform on top --}}
      @if($game->cover_generator)
        <div class="lazy game-cover gen"  data-original="{{$game->image_cover}}"></div>
        <div class="game-platform-gen" style="background-color: {{$game->platform->color}}; text-align: {{$game->platform->cover_position}};">
          {{-- Check if platform logo setting is enabled --}}
          @if( config('settings.platform_logo') )
            <img src="{{ asset('logos/' . $game->platform->acronym . '_tiny.png/') }}" alt="{{$game->platform->name}} Logo">
          @else
            <span>{{$game->platform->name}}</span>
          @endif
        </div>
      {{-- Normal game cover --}}
      @else
        <div class="lazy game-cover"  data-original="{{$game->image_cover}}"></div>
      @endif
    </div>
    {{-- End Game Cover --}}


  </div>


  <div class="col-xs-12 col-sm-8 col-lg-9 col-xxl-10">

    {{-- Start Game Details --}}
    <div class="game-details">
      <div class="flex-center-space" style="flex-wrap: wrap;">
        <div>
        {{-- Game title --}}
        <div class="gtitle">
          {{$game->name}}
        </div>
        {{-- Publisher --}}
        <div class="gpublisher">
          {{$game->publisher}}
        </div>
        </div>
        <div>
          {{-- Metascore --}}
          @if(isset($game->metacritic) && $game->metacritic->score)
          <a href="{{$game->metacritic->url}}" target="_blank" class="metascore {{$game->metacritic->score_class}} m-r-10">
             <span class="score">{{$game->metacritic->score}}</span>
             <span class="text">{{ trans('games.overview.subheader.metascore') }}</span>
          </a>
          @endif
          {{-- Userscore --}}
          @if(isset($game->metacritic) && $game->metacritic->userscore)
          <a href="{{$game->metacritic->url}}" target="_blank" class="metascore user">
             <span class="score">{{$game->metacritic->userscore}}</span>
             <span class="text">{{ trans('games.overview.subheader.userscore') }}</span>
          </a>
          @endif
        </div>
      </div>

      <div class="gsummary">
        {!! $game->description !!}
      </div>
      {{-- Available on different platforms --}}
      @if(isset($different_platforms) && count($different_platforms)>0)
      <div class="gavailable">
        {{ trans('games.overview.subheader.also_available') }}
      </div>
      {{-- Platform list --}}
      <div class="glist">
        @foreach($different_platforms as $different_platform)
        <a href="{{ $different_platform->url_slug }}" >
          <div onMouseOver="this.style.backgroundColor='{{ $different_platform->platform->color }}'" onMouseOut="this.style.backgroundColor=''" class="gitem" style="border: 2px solid {{$different_platform->platform->color}};">
            {{-- Check if platform logo setting is enabled --}}
            @if( config('settings.platform_logo') )
              <img src="{{ asset('logos/' . $different_platform->platform->acronym . '_tiny.png/') }}" alt="{{$different_platform->platform->name}} Logo">
            @else
              <span>{{$different_platform->platform->name}}</span>
            @endif
          </div>
        </a>
        @endforeach
      </div>
      @endif

      {{-- Buttons for listing --}}
      @if(isset($listing))
        {{-- Start Buy Button --}}
        @if($listing->sell)
          <a href="javascript:void(0);" data-toggle="modal" data-target="{{ Auth::check() ? '#modal-buy' : '#LoginModal' }}" class="buy-button m-t-20 m-r-10 m-b-10">
            <span><i class="icon fa fa-shopping-basket" aria-hidden="true"></i> {{ trans('listings.overview.subheader.buy_now') }}</span>
            {{-- Check if user allow price suggestions --}}
            @if($listing->sell_negotiate)
              <span class="suggestion"><i class="fa fa-retweet" aria-hidden="true"></i></span>
            @endif
          </a>
        @endif
        {{-- End Buy Button --}}

        {{-- Start Trade Button --}}
        @if($listing->trade)
          <a href="javascript:void(0);" class="trade-button m-b-10 {{ $listing->sell ? '' : 'm-t-20'}}" id="trade-button-subheader">
            <span><i class="icon fa fa-exchange" aria-hidden="true"></i> {{ trans('listings.general.trade') }}</span>
            {{-- Check if user allow trade suggestions --}}
            @if($listing->trade_negotiate)
              <span class="suggestion"><i class="fa fa-retweet" aria-hidden="true"></i></span>
            @endif
          </a>
        @endif
        {{-- End Trade Button --}}

        {{-- Gameover Button --}}
        <div class="m-t-10">
          <a class="gameoverview-button" href="{{ $game->url_slug }}"><i class="fa fa-angle-double-right" aria-hidden="true"></i> {{ trans('listings.overview.subheader.go_gameoverview') }}</a>
        </div>
      @else
        {{-- Load Buy Button Ref Link --}}
        @if(config('settings.buy_button_ref'))
          @include('frontend.ads.buyref')
        @endif
      @endif

    </div>
    {{-- End Game Details --}}

  </div>

</div>
@stop
