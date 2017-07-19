@extends('frontend.layouts.app')

{{-- Load game subheader --}}
@include('frontend.game.subheader')

@section('content')

{{-- Load content from game subheader --}}
@yield('game-content')


{{-- Start Subheader tabs --}}
<div class="flex-center-space m-t-50">
  {{-- Nav tabs --}}
  <div class="no-flex-shrink">
    <ul class="subheader-tabs" role="tablist">
      {{-- Details link --}}
      <li class="nav-item">
        <a data-toggle="tab" href="#details" data-target="#details" role="tab" class="subheader-link">
          <i class="fa fa-tag" aria-hidden="true"></i><span class="hidden-xs-down"> {{ trans('listings.overview.subheader.details') }}</span>
        </a>
      </li>
      {{-- Media tab (Images & Videos) --}}
      @if($game->giantbomb_id)
      <li class="nav-item">
        <a data-toggle="tab" href="{{ url('/games/' . $game->id . '/media') }}" data-target="#media" role="tab" class="subheader-link">
          <i class="fa fa-picture-o" aria-hidden="true"></i><span class="{{ config('settings.comment_listing') ? 'hidden-sm-down' : 'hidden-xs-down'}}"> {{ trans('games.overview.subheader.media') }}</span>
        </a>
      </li>
      @endif
      {{-- Comments tab --}}
      @if(config('settings.comment_listing'))
      <li class="nav-item">
        <a data-toggle="tab" href="#comments" data-target="#comments" role="tab" class="subheader-link">
          <i class="fa fa-comments" aria-hidden="true"></i><span class="{{ config('settings.comment_listing') ? 'hidden-sm-down' : 'hidden-xs-down'}}"> {{ trans('comments.comments') }}</span>
        </a>
      </li>
      @endif
    </ul>
  </div>
  {{-- Share buttons --}}
  <div @if(config('settings.comment_listing')) class="subheader-social-comments" @endif>
    {{-- Facebook share --}}
    <a href="https://www.facebook.com/dialog/share?
{{config('settings.facebook_client_id') ? 'app_id='. config('settings.facebook_client_id') . '&' : '' }}display=popup&href={{URL::current()}}&redirect_uri={{ url('self.close.html')}}" onclick="window.open(this.href, 'facebookwindow','left=20,top=20,width=600,height=400,toolbar=0,resizable=1'); return false;" class="btn btn-icon btn-round btn-lg social-facebook m-r-5">
      <i class="icon fa fa-facebook" aria-hidden="true"></i>
    </a>
    {{-- Twitter share --}}
    @if($listing->sell == 1)
    <a href="http://twitter.com/intent/tweet?text={{trans('general.share.twitter_listing_buy', ['game_name' => $game->name, 'platform' => $game->platform->name, 'price' => $listing->price_formatted])}} &#8921; {{URL::current()}}" onclick="window.open(this.href, 'twitterwindow','left=20,top=20,width=600,height=300,toolbar=0,resizable=1'); return false;" class="btn btn-icon btn-round btn-lg social-twitter m-r-5">
      <i class="icon fa fa-twitter" aria-hidden="true"></i>
    </a>
    {{-- Twitter share with text for trade --}}
    @else
    <a href="http://twitter.com/intent/tweet?text={{trans('general.share.twitter_listing_trade', ['game_name' => $game->name, 'platform' => $game->platform->name])}} &#8921; {{URL::current()}}" onclick="window.open(this.href, 'twitterwindow','left=20,top=20,width=600,height=300,toolbar=0,resizable=1'); return false;" class="btn btn-icon btn-round btn-lg social-twitter m-r-5">
      <i class="icon fa fa-twitter" aria-hidden="true"></i>
    </a>
    @endif
    {{-- Google plus share --}}
    <a href="https://plus.google.com/share?url={{URL::current()}}" onclick="window.open(this.href, 'googlepluswindow','left=20,top=20,width=600,height=400,toolbar=0,resizable=1'); return false;" class="btn btn-icon btn-round btn-lg social-google-plus m-r-5">
      <i class="icon fa fa-google-plus" aria-hidden="true"></i>
    </a>
  </div>
</div>
{{-- End Subheader tabs --}}

<div class="tab-content subheader-margin">

  {{-- Load Google AdSense --}}
  @if(config('settings.google_adsense'))
    @include('frontend.ads.google')
  @endif

  @if(config('settings.comment_listing'))
  {{-- Start comments tab --}}
  <div class="tab-pane fade" id="comments" role="tabpanel">
    @php $item_type = 'listing'; $item_id = $listing->id; @endphp
    @include('frontend.comments.form')
  </div>
  {{-- End comments tab --}}
  @endif

  {{-- Start Listings tab --}}
  <div class="tab-pane fade" id="details" role="tabpanel">


    {{-- Listing values --}}
    <div class="listing-values">

      {{-- PayPal Payment --}}
      @if($listing->payment)
      <div class="value paypal-payment">
        <span class="p-10 inline-block">{{ trans('payment.secure_payment') }}</span><span class="p-10 inline-block protected"><i class="fa fa-shield" aria-hidden="true"></i></span>
      </div>
      @endif

      {{-- Limited edition --}}
      @if($listing->limited_edition)
      <div class="value">
        <i class="fa fa-star" aria-hidden="true"></i> {{$listing->limited_edition}}
      </div>
      @endif

      @if($listing->digital)
      {{-- Digital ditributor --}}
      <div class="value">
        <i class="fa fa-download" aria-hidden="true"></i> {{$listing->game->platform->digitals->where('id',$listing->digital)->first()->name}}
      </div>
      @else
      {{-- Condition --}}
      <div class="value">
        {{ trans('listings.general.condition') }}: {{$listing->condition_string}}
      </div>
      @endif

      {{-- Pickup --}}
      <div class="value">
        <i class="fa fa-handshake-o" aria-hidden="true"></i>
        {{ trans('listings.general.pickup') }} <i class="fa @if($listing->pickup) fa-check-circle text-success @else fa-times-circle text-danger @endif" aria-hidden="true"></i>
      </div>

      {{-- Delivery --}}
      <div class="value">
        <i class="fa fa-truck" aria-hidden="true"></i>
        {{ trans('listings.general.delivery') }} <i class="fa @if($listing->delivery) fa-check-circle text-success @else fa-times-circle text-danger @endif" aria-hidden="true"></i>
        @if($listing->delivery && $listing->delivery_price != '0')
          <span class="delivery-price-span"> + {{ $listing->getDeliveryPrice() }}</span>
        @endif
      </div>
    </div>

    {{-- Start Listing Details --}}
    <div class="row row-eq-height ">

      {{-- Description / Created at --}}
      <div class="@if($listing->picture) col-lg-10 col-md-9 col-xs-12 @else col-xs-12 @endif">
        <section class="panel">
          <div class="panel-body">
            {{-- Description --}}
            @if($listing->description)
              <div class="listing-description"> {!! $listing->description !!}</div>
            {{-- No Description --}}
            @else
              <div class="listing-description missing flex-center"><i class="fa fa-ban m-r-5" aria-hidden="true"></i> {{ trans('listings.general.no_description') }}</div>
            @endif
          </div>

          {{-- Listing Created at --}}
          <div class="panel-footer text-light padding">
            {{ trans('listings.overview.created') }} {{$listing->created_at->diffForHumans()}}
          </div>
        </section>
      </div>

      {{-- Picture --}}
      @if($listing->picture)
      <div class="col-lg-2 col-md-3 col-xs-12" style="padding-bottom: 20px;">
          <a class="open-listing-picture" href="{{ $listing->picture_original }}" data-source="{{ $listing->picture_original }}" title="{{ $game->name }}" data-effect="mfp-zoom-in">
            <div class="lazy overlay hvr-grow-shadow2 listing-picture-wrapper" data-original="{{ $listing->picture_square }}">
              <div class="loader pacman-loader picture-loader"></div>
              <div class="imgDescription"><div class="valign"><i class="fa fa-expand" aria-hidden="true"></i></div></div>
              <div style="position: absolute; top: 0; right: 0px; padding: 5px; color: #fff; background-color: #212121; border-radius: 5px; margin: 10px;"><i class="fa fa-picture-o" aria-hidden="true"></i></div>
            </div>
          </a>
      </div>
      @endif
    </div>
    {{-- End Listing Details --}}

    {{-- Start Listing Trade --}}
    @if($listing->trade)
    {{-- Trade title --}}
    <div style="margin-bottom: 20px; font-size: 24px; color: #fff; font-weight: 700; ">
      <i class="fa fa-exchange" aria-hidden="true" id="trade-list"></i> {{ trans('listings.general.trade') }}
    </div>

    {{-- Trade info when user click button in subheader --}}
    <div class="panel panel-body hidden" id="trade-info">
      <i class="fa fa-info-circle" aria-hidden="true"></i> {{ trans('listings.overview.trade_info', ['game_name' => $game->name]) }}
    </div>

    {{-- Start Trade game list --}}
    <div class="row">
    {{-- Get additional charges --}}
    @php $add_charge = json_decode($listing->trade_list,true); @endphp
    @if($trade_list)
    @foreach($trade_list as $trade_game)
      <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3 col-xl-2">
        {{-- Start Game Cover --}}
        <div class="card game-cover-wrapper hvr-grow-shadow">
          {{-- Pacman Loader for background image - show only when cover exists --}}
          @if($trade_game->image_cover)
          <div class="loader pacman-loader cover-loader"></div>
          {{-- Show game name, when no cover exist --}}
          @else
          <div class="no-cover-name">{{$trade_game->name}}</div>
          @endif

            <a href="javascript:void(0)" data-toggle="modal" data-target="{{ Auth::check() ? '#modal-trade_' . $trade_game->id : '#LoginModal' }}">

              {{-- Start Additional Charge Ribbon --}}
              @if($add_charge[$trade_game->id]['price_type'] != 'none')
              @if($add_charge[$trade_game->id]['price_type'] == 'want')
              <div class="ribbon ribbon-clip ribbon-bottom ribbon-danger">
              @elseif($add_charge[$trade_game->id]['price_type'] == 'give')
              <div class="ribbon ribbon-clip ribbon-bottom ribbon-success">
              @endif
                <div class="ribbon-inner">
                  @if($add_charge[$trade_game->id]['price_type'] == 'want')
                  <span class="currency"><i class="fa fa-minus"></i></span>
                  @elseif($add_charge[$trade_game->id]['price_type'] == 'give')
                  <span class="currency"><i class="fa fa-plus"></i></span>
                  @endif<span class="price"> {{ money($add_charge[$trade_game->id]['price'], Config::get('settings.currency')) }}</span>
                </div>
              </div>
              @endif
              {{-- End Additional Charge Ribbon --}}

              {{-- Start Game Cover --}}
              {{-- Generated game cover with platform on top --}}
              @if($trade_game->cover_generator)
                <div class="lazy game-cover gen"  data-original="{{$trade_game->image_cover}}"></div>
                <div class="game-platform-gen" style="background-color: {{$trade_game->platform->color}}; text-align: {{$trade_game->platform->cover_position}};">
                  {{-- Check if platform logo setting is enabled --}}
                  @if( config('settings.platform_logo') )
                    <img src="{{ asset('logos/' . $trade_game->platform->acronym . '_tiny.png/') }}" alt="{{$trade_game->platform->name}} Logo">
                  @else
                    <span>{{$trade_game->platform->name}}</span>
                  @endif
                </div>
              {{-- Normal game cover --}}
              @else
                <div class="lazy game-cover"  data-original="{{$trade_game->image_cover}}"></div>
              @endif
              {{-- End Game Cover --}}

              {{-- Exchange Icon overlay on hover --}}
              <div class="imgDescription gcover">
                <div class="valign">
                  <i class="fa fa-exchange" aria-hidden="true"></i>
                </div>
              </div>

            </a>

        </div>
      </div>

      {{-- Start Trade Modal --}}
      <div class="modal fade modal-fade-in-scale-up modal-trade" id="modal-trade_{{$trade_game->id}}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">

            <div class="modal-header">
              {{-- Background pattern --}}
              <div class="background-pattern" style="background-image: url('{{ asset('/img/game_pattern.png') }}');"></div>
              {{-- Background color --}}
              <div class="background-color"></div>
              {{-- Title (Trade / Close button) --}}
              <div class="title">
                <button type="button" class="close" data-dismiss="modal">
                  <span aria-hidden="true">×</span><span class="sr-only">{{ trans('listings.modal.close') }}</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                  <i class="fa fa-exchange m-r-5" aria-hidden="true"></i>
                  {!! trans('listings.modal_trade.trade_game' , ['game_name' => $listing->game->name]) !!}
                </h4>
              </div>

            </div>

            <div class="modal-body">
              <div class="flex-center-space">
                {{-- Start Game Info --}}
                <div class="game-overview">
                  <div>
                    {{-- Game cover --}}
                    <span class="avatar cover">
                      <img src="{{ $game->image_square_tiny }}">
                    </span>
                  </div>
                  <div>
                    {{-- Game title & platform --}}
                    <span class="title">
                      <strong>{{$game->name}}</strong>
                    </span>
                    <span class="platform" style="background-color:{{$game->platform->color}};">
                      {{$game->platform->name}}
                    </span>
                  </div>
                </div>
                {{-- End Game Info --}}
                {{-- Additional charge from user --}}
                <div class="additional-charge flex-center">
                  @if($add_charge[$trade_game->id]['price_type'] == 'give')
                  <div class="charge-icon">
                    <i class="fa fa-plus"></i>
                  </div>
                  <div class="charge-money">
                    {{ money($add_charge[$trade_game->id]['price'], Config::get('settings.currency')) }}
                  </div>
                  @endif
                </div>
              </div>

              <div class="seperator"><span><i class="fa fa-exchange" aria-hidden="true"></i></span></div>

              <div class="game-overview trade game">
                {{-- Additional charge from Partner --}}
                <div class="additional-charge flex-center">
                  @if($add_charge[$trade_game->id]['price_type'] == 'want')
                  <div class="charge-money partner">
                    {{ money($add_charge[$trade_game->id]['price'], Config::get('settings.currency')) }}
                  </div>
                  <div class="charge-icon partner">
                    <i class="fa fa-plus"></i>
                  </div>
                  @endif
                </div>
                {{-- Start Info from trade game --}}
                <div class="overview">
                  <div>
                    <span class="title">
                      <strong>{{$trade_game->name}}</strong>
                    </span>
                    <span class="platform" style="background-color:{{$trade_game->platform->color}};">
                      {{$trade_game->platform->name}}
                    </span>
                  </div>
                  <div>
                    <span class="avatar cover trade">
                      <img src="{{$trade_game->image_square_tiny}}">
                    </span>
                  </div>
                </div>
                {{-- End Info from trade game --}}
              </div>

            </div>

            <div class="modal-footer">
              {!! Form::open(array('url'=>'offer/add', 'id'=>'form-trade-' . $trade_game->id, 'role'=>'form')) !!}

              {{-- Encrypt hidden inputs. We don't want, that the user can change the values --}}
              <input name="game_id" type="hidden" value="{{  encrypt($game->id) }}">
              <input name="listing_id" type="hidden" value="{{ encrypt($listing->id) }}">
              <input name="trade_game" type="hidden" value="{{ $trade_game->id }}">
              <a href="javascript:void(0)" class="cancel-button" data-dismiss="modal">
                <i class="fa fa-times" aria-hidden="true"></i> {{ trans('listings.modal.close') }}
              </a>
              <a href="javascript:void(0)" class="trade-button trade-submit" data-trade="{{$trade_game->id}}">
                <span><i class="fa fa-exchange" aria-hidden="true"></i> {{ trans('listings.general.trade') }}</span>
              </a>
              {!! Form::close() !!}
            </div>

          </div>
        </div>
      </div>
      {{-- End Trade Modal --}}

    @endforeach
    @endif

    {{-- Check if trade suggestions are allowed --}}
    @if($listing->trade_negotiate)
      <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3 col-xl-2">
        {{-- Start Game Cover --}}
        <div class="card game-cover-wrapper hvr-grow-shadow">

          {{-- Cover for trade suggestion --}}
          <div class="game-cover-suggestion " style="background: radial-gradient(rgba(48,47,47,0) 0, rgba(48,47,47,0.7) 60%, rgba(48,47,47,0.9) 100%), url({{ asset('/img/game_pattern_white.png') }}) 0% 20%;"></div>

          {{-- Suggestion icon --}}
          <div class="no-cover-name suggestion-icon"><i class="fa fa-retweet" aria-hidden="true"></i></div>
          {{-- Title (Suggest a Game) --}}
          <div class="suggestion-name m-t-40">{{ trans('listings.modal_trade.suggest') }}</div>

            <a href="javascript:void(0)" data-toggle="modal" data-target="{{ Auth::check() ? '#modal-trade_suggestion' : '#LoginModal' }}">

              {{-- Exchange Icon overlay on hover --}}
              <div class="imgDescription gcover">
                <div class="valign">
                  <i class="fa fa-retweet" aria-hidden="true"></i>
                </div>
              </div>

            </a>

        </div>
      </div>

      {{-- Start Trade Suggestion Modal --}}
      <div class="modal fade modal-fade-in-scale-up modal-trade" id="modal-trade_suggestion" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
          <div class="modal-content">

            <div class="modal-header">
              {{-- Background pattern --}}
              <div class="background-pattern" style="background-image: url('{{ asset('/img/game_pattern.png') }}');"></div>
              {{-- Background color --}}
              <div class="background-color"></div>
              {{-- Title (Trade / Close button) --}}
              <div class="title">
                <button type="button" class="close" data-dismiss="modal">
                  <span aria-hidden="true">×</span><span class="sr-only">{{ trans('listings.modal.close') }}</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                  <i class="fa fa-exchange m-r-5" aria-hidden="true"></i>
                  {!! trans('listings.modal_trade.trade_game' , ['game_name' => $listing->game->name]) !!}
                </h4>
              </div>

            </div>
            {!! Form::open(array('url'=>'offer/add', 'id'=>'form-trade-suggestion', 'role'=>'form')) !!}
            <div class="modal-body">
              <div class="flex-center-space">
                {{-- Start Game Info --}}
                <div class="game-overview">
                  <div>
                    {{-- Game cover --}}
                    <span class="avatar cover">
                      <img src="{{ $game->image_square_tiny }}">
                    </span>
                  </div>
                  <div>
                    {{-- Game title & platform --}}
                    <span class="title">
                      <strong>{{$game->name}}</strong>
                    </span>
                    <span class="platform" style="background-color:{{$game->platform->color}};">
                      {{$game->platform->name}}
                    </span>
                  </div>
                </div>
                {{-- End Game Info --}}
                {{-- Additional charge from user --}}
                <div class="additional-charge contract flex-center" id="add_charge_user_wrapper">
                  <a class="charge-icon" id="add_charge_user_button" href="#">
                    <i class="fa fa-money money-user"></i><i class="fa fa-plus"></i>
                  </a>
                  <div class="charge-money" id="add_charge_user_form" style="display: none;">
                    <span class="m-r-5">{{ Currency(Config::get('settings.currency'))->getSymbol() }}</span><input type="text" name="add_charge_user" id="add_charge_user" placeholder="{{ trans('listings.form.placeholder.sell_price_suggestion',  ['currency_name' => Currency(Config::get('settings.currency'))->getName()]) }}" class="form-control input">
                  </div>
                </div>
              </div>

              <div class="seperator m-b-10"><span><i class="fa fa-exchange" aria-hidden="true"></i></span></div>

              {{-- Input group for game search --}}
              <div id="select-game">
                <div class="input-group input-group-lg select-game m-b-10">
                  <span class="input-group-addon">
                    {{-- Search icon when search is complete --}}
                    <span id="listingsearchcomplete">
                      <i class="fa fa-search"></i>
                    </span>
                    {{-- Spin icon when search is in progress --}}
                    <span class="hidden" id="listingsearching">
                      <i class="fa fa-refresh fa-spin"></i>
                    </span>
                  </span>
                  {{-- Input for typeahead --}}
                  <input type="text" class="form-control rounded input-lg inline input" id="offersearch">
                </div>
              </div>
              {{-- Selected game --}}
              <div class="selected-game"></div>
            </div>

            <div class="modal-footer">
              {{-- Encrypt hidden inputs. We don't want, that the user can change the values --}}
              <input name="game_id" type="hidden" value="{{  encrypt($game->id) }}">
              <input name="listing_id" type="hidden" value="{{ encrypt($listing->id) }}">
              <a href="javascript:void(0)" class="cancel-button" data-dismiss="modal">
                <i class="fa fa-times" aria-hidden="true"></i> {{ trans('listings.modal.close') }}
              </a>
              <a href="javascript:void(0)" class="trade-button loading" id="trade-submit-suggestion">
                <span><i class="fa fa-exchange" aria-hidden="true"></i> {{ trans('listings.general.trade') }}</span>
              </a>
            </div>
            {!! Form::close() !!}
          </div>
        </div>
      </div>
      {{-- End Trade Suggestion Modal --}}
    @endif


    </div>
    {{-- End Trade game list --}}
    {{-- End Listing Trade --}}

    @endif
    <div class="row">
      {{-- Start User Profile Widget --}}
      <div class="@if($listing->user->location && $listing->user->location->latitude && $listing->user->location->longitude && config('settings.google_maps_key')) col-lg-10 col-md-9 col-xs-12 @else col-xs-12 @endif">
        <section class="panel">
          <div class="panel-body">
            <div class="flex-center-space">
              <a class="profile-link flex-center" href="{{$listing->user->url}}">
                {{-- User Avatar --}}
                <span class="avatar @if($listing->user->isOnline()) avatar-online @else avatar-offline @endif m-r-10">
                  <img src="{{$listing->user->avatar_square}}" alt="{{$listing->user->name}}'s Avatar"><i></i>
                </span>
                {{-- User Name & Location --}}
                <div>
                  <span class="profile-name no-wrap small">
                    {{$listing->user->name}}
                  </span>
                  <span class="profile-location small">
                  @if($listing->user->location)
                    <img src="{{ asset('img/flags/' .   $listing->user->location->country_abbreviation . '.svg') }}" height="14"/> {{$listing->user->location->country_abbreviation}}, {{$listing->user->location->place}} <span class="postal-code">{{$listing->user->location->postal_code}}</span>
                  @endif
                  </span>
                </div>
              </a>
              {{-- User Ratings --}}
              <div class="no-flex-shrink">
              @if(is_null($listing->user->positive_percent_ratings))
                {{-- No Ratings --}}
                <span class="fa-stack fa-lg">
                  <i class="fa fa-thumbs-up fa-stack-1x"></i>
                  <i class="fa fa-ban fa-stack-2x text-danger"></i>
                </span>
                <span class="no-ratings small">{{ trans('users.general.no_ratings') }}</span>
              @else
                @php
                  if($listing->user->positive_percent_ratings > 70){
                    $rating_icon = 'fa-thumbs-up text-success';
                  }else if($listing->user->positive_percent_ratings > 40){
                    $rating_icon = 'fa-minus';
                  }else{
                    $rating_icon = 'fa-thumbs-down text-danger';
                  }
                @endphp
                {{-- Ratings in percent --}}
                <span class="rating-percent small"><i class="fa {{$rating_icon}}" aria-hidden="true"></i> {{$listing->user->positive_percent_ratings}}%</span>
                {{-- Ratings Count --}}
                <div class="rating-counts small">
                  <span class="text-danger"><i class="fa fa-thumbs-down" aria-hidden="true"></i> {{$listing->user->negative_ratings}}</span>&nbsp;&nbsp;
                  <i class="fa fa-minus" aria-hidden="true"></i> {{$listing->user->neutral_ratings}}&nbsp;&nbsp;
                  <span class="text-success"><i class="fa fa-thumbs-up" aria-hidden="true"></i> {{$listing->user->positive_ratings}}</span>
                </div>
              @endif
              </div>
            </div>
          </div>
          <div class="panel-footer text-light">
            {{-- Member since --}}
            <div class="p-20">
              {{-- Check if user is banned --}}
              @if($listing->user->status)
                {{-- Member since --}}
                {{ trans('users.general.member_since', ['time' => $listing->user->created_at->diffForHumans(null,true)]) }}
              @else
                {{-- User banned label --}}
                <span class="platform-label bg-danger m-t-10">{{ trans('users.profile.banned') }}</span>
              @endif
            </div>
            {{-- Links --}}
            <div>
              <a href="{{$listing->user->url}}" class="button" id="save-submit">
                <i class="fa fa-user" aria-hidden="true"></i> {{ trans('users.general.profile') }}
              </a>
            </div>
          </div>
        </section>
      </div>
      {{-- End User Profile Widget --}}
      @if($listing->user->location && $listing->user->location->latitude && $listing->user->location->longitude && config('settings.google_maps_key'))
      {{-- Start google maps wrapper --}}
      <div class="col-lg-2 col-md-3 col-xs-12">
        <div class="google-maps"></div>
      </div>
      {{-- End google maps wrapper --}}
      @endif

    </div>

  </div>
  {{-- End Listings tab --}}

  {{-- Start Media (Images & Videos) tab --}}
  <div class="tab-pane fade" id="media" role="tabpanel">
  </div>
  {{-- End Media (Images & Videos) tab --}}

  {{-- Start Edit / Delete when user has permission --}}
  @if( Auth::check() && ((Auth::user()->id == $listing->user_id) || Auth::user()->hasPermission('edit_listings')))
  <div>
    @if($listing->status == 0 || is_null($listing->status))
    <a href="javascript:void(0)" data-toggle="modal" data-target="#modal_delete_{{$listing->id}}" class="btn btn-danger m-r-5"><i class="fa fa-trash"></i> {{ trans('general.delete') }}</a>
    <a href="{{ $listing->url_slug . '/edit' }}" class="btn btn-dark"><i class="fa fa-edit"></i> {{ trans('general.edit') }}</a>

    {{-- Start modal for delete listing --}}
    <div class="modal fade modal-fade-in-scale-up modal-danger" id="modal_delete_{{$listing->id}}" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">

          <div class="modal-header">

            <div class="background-pattern" style="background-image: url('{{ asset('/img/game_pattern.png') }}');"></div>

            <div class="title">
              <button type="button" class="close" data-dismiss="modal">
                <span aria-hidden="true">×</span><span class="sr-only">{{ trans('general.close') }}</span>
              </button>
              {{-- Delete  listing title --}}
              <h4 class="modal-title" id="myModalLabel">
                <i class="fa fa-trash m-r-5" aria-hidden="true"></i>{{ trans('users.modal_delete_listing.title', ['gamename' => $listing->game->name]) }}
              </h4>
            </div>

          </div>

          <div class="modal-body">
            {{-- Delete info --}}
            <span><i class="fa fa-info-circle" aria-hidden="true"></i> {{ trans('users.modal_delete_listing.info') }}</span>

          </div>

          <div class="modal-footer">
            {!! Form::open(array('url'=>'listings/delete', 'id'=>'form-delete', 'role'=>'form')) !!}
            {{-- Close button --}}
            <a href="#" data-dismiss="modal" data-bjax class="btn btn-lg btn-dark btn-animate btn-animate-vertical m-r-10"><span><i class="icon fa fa-times" aria-hidden="true"></i> {{ trans('general.cancel') }}</span></a>
            <input name="listing_id" type="hidden" value="{{ encrypt($listing->id) }}">
            {{-- Delete button --}}
            <button class="btn btn-lg btn-danger btn-animate btn-animate-vertical" type="submit" id="delete-submit">
              <span><i class="icon fa fa-trash" aria-hidden="true"></i> {{ trans('users.modal_delete_listing.delete_listing') }}
              </span>
            </button>
            {!! Form::close() !!}
          </div>
        </div>
      </div>
    </div>
    @endif
    {{-- End modal for delete listing --}}
  </div>
  @endif
  {{-- End Edit / Delete when user has permission --}}

</div>



  {{-- Start Buy Modal --}}
  <div class="modal fade modal-fade-in-scale-up modal-buy" id="modal-buy" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">

        <div class="modal-header">
          {{-- Background pattern --}}
          <div class="background-pattern" style="background-image: url('{{ asset('/img/game_pattern.png') }}');"></div>
          {{-- Background color --}}
          <div class="background-color"></div>
          {{-- Title (Buy & Close button) --}}
          <div class="title">
            <button type="button" class="close" data-dismiss="modal">
              <span aria-hidden="true">×</span><span class="sr-only">{{ trans('listings.modal.close') }}</span>
            </button>
            <h4 class="modal-title" id="myModalLabel">
              <i class="fa fa-shopping-basket m-r-5" aria-hidden="true"></i>
              {!! trans('listings.modal_buy.buy_game', ['game_name' => $game->name]) !!}
            </h4>
          </div>

        </div>

        {!! Form::open(array('url'=>'offer/add', 'id'=>'form-buy', 'role'=>'form')) !!}

        <div class="modal-body">
          {{-- Start Game Details --}}
          <div class="game-overview">
            <div>
              {{-- Game Cover --}}
              <span class="avatar cover">
                <img src="{{$game->image_square_tiny}}">
              </span>
            </div>
            <div>
              {{-- Game Title & platform --}}
              <span class="title">
                <strong>{{$game->name}}</strong>
              </span>
              <span class="platform" style="background-color:{{$game->platform->color}};">
                {{$game->platform->name}}
              </span>
            </div>
          </div>
          {{-- End Game Details --}}

          <div class="seperator"><span><i class="fa fa-shopping-basket" aria-hidden="true"></i></span></div>
          {{-- Start Buy Overview --}}
          <div class="price-overview flex-center-space">
            <div>
              {{-- Secure Payment --}}
              @if($listing->payment)
              <div class="paypal-payment inline-block">
                <span class="p-10 inline-block">{{ trans('payment.secure_payment') }}</span><span class="p-10 inline-block protected"><i class="fa fa-shield" aria-hidden="true"></i></span>
              </div>
              <div class="payment-gateways">
                  {{-- PayPal --}}
                  @if( config('settings.paypal') )
                      <i class="fa fa-cc-paypal m-r-5" aria-hidden="true"></i>
                  @endif
                  {{-- Stripe --}}
                  @if( config('settings.stripe') )
                      <i class="fa fa-cc-stripe m-r-5" aria-hidden="true"></i>
                  @endif
              </div>
              @endif

            </div>
            <div>
              <span class="total">
                {{ trans('listings.modal_buy.total') }}
              </span>
              {{-- Check if price suggestions are allowed --}}
              @if($listing->sell_negotiate)
                {{-- Listing Price --}}
                <span id="listing-price">{{ $listing->getPrice() }}</span>
                {{-- Price suggestion link --}}
                <div id="price-suggest-link-wrapper">
                  <a href="#" id="price-suggest-link" class="price-suggest-link btn btn-dark"><i class="fa fa-retweet" aria-hidden="true"></i> {{ trans('listings.modal_buy.suggest_price') }}</a>
                </div>
                {{-- Price suggestion form --}}
                <div id="price-suggest-form" style="display: none;">
                  <div  class="input-group input-group-lg" style="width: 200px; font-weight: 500 !important;">
                    <span class="input-group-addon">
                      <span>{{ Currency(Config::get('settings.currency'))->getSymbol() }}</span>
                    </span>
                    {{-- Price Input --}}
                    <input type="text" class="form-control rounded input-lg inline input" style="text-align: right;"
                    data-validation="number,required" data-validation-ignore=",,." data-validation-error-msg='<div class="alert dark alert-icon alert-danger" role="alert"><i class="icon fa fa-exclamation-triangle" aria-hidden="true"></i> {{ trans('listings.form.validation.price') }}</div>' data-validation-error-msg-container="#price-error-dialog" name="price_suggestion" id="price_suggestion" autocomplete="off" value="{{(isset($listing) ? old('price',$listing->price) : null)}}" placeholder="{{ trans('listings.form.placeholder.sell_price_suggestion',  ['currency_name' => Currency(Config::get('settings.currency'))->getName()]) }}"/>
                  </div>
                </div>
              @else
                {{-- Listing Price --}}
                {{ $listing->getPrice() }}
              @endif
              {{-- Delivery Price --}}
              @if($listing->delivery)
              <span class="total shipping {{ $listing->sell_negotiate ? 'm-t-5' : ''}}">
                @if(is_null($listing->delivery_price) || $listing->delivery_price == 0 )
                  {{ trans('listings.modal_buy.delivery_free') }}
                @else
                  {{ trans('listings.modal_buy.delivery_price', ['price' => $listing->getDeliveryPrice()]) }}
                @endif
              </span>
              @endif
            </div>
          </div>
          {{-- End Buy Overview --}}
        </div>

        <div class="modal-footer">
          {{-- Encrypt hidden inputs. We don't want, that the user can change the values --}}
          <input name="game_id" type="hidden" value="{{  encrypt($game->id) }}">
          <input name="listing_id" type="hidden" value="{{ encrypt($listing->id) }}">
          <a href="javascript:void(0)" class="cancel-button" data-dismiss="modal">
            <i class="fa fa-times" aria-hidden="true"></i> {{ trans('listings.modal.close') }}
          </button>
          <a href="javascript:void(0)" class="buy-button" id="buy-submit">
            <span><i class="fa fa-shopping-basket" aria-hidden="true"></i> {{ trans('listings.modal_buy.buy') }}</span>
          </a>
        </div>

        {!! Form::close() !!}

      </div>
    </div>
  </div>
  {{-- End Buy Modal --}}




@section('after-scripts')

<script src="//cdnjs.cloudflare.com/ajax/libs/masonry/4.1.1/masonry.pkgd.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/4.1.1/imagesloaded.pkgd.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js"></script>

<script src="//cdnjs.cloudflare.com/ajax/libs/mustache.js/2.3.0/mustache.min.js"></script>

{{-- Load comment script --}}
@if(config('settings.comment_listing'))
  @yield('comments-script')
@endif

{{-- Start Mustache Template for selected game --}}
<script id="selected-game" type="x-tmpl-mustache">
  <div>
    <div class="flex-center-space">
      {{-- Additional charge from partner --}}
      <div class="additional-charge contract flex-center" id="add_charge_partner_wrapper">
        <div class="charge-money partner" id="add_charge_partner_form" style="display: none;">
          <span class="m-r-5">{{ Currency(Config::get('settings.currency'))->getSymbol() }}</span><input type="text" name="add_charge_partner" id="add_charge_partner" placeholder="{{ trans('listings.form.placeholder.sell_price_suggestion',  ['currency_name' => Currency(Config::get('settings.currency'))->getName()]) }}" class="form-control input">
        </div>
        <a class="charge-icon partner" id="add_charge_partner_button" href="#">
          <i class="fa fa-plus"></i><i class="fa fa-money money-partner"></i>
        </a>
      </div>
      <div class="game-overview trade game">
        <div>
        </div>
        {{-- Start Info from trade game --}}
        <div class="overview">
          <div>
            <span class="title">
              <strong><% name %></strong>
            </span>
            <span class="platform" style="background-color:<% platform_color %>;">
              <% platform_name %>
            </span>
          </div>
          <div>
            <span class="avatar cover trade">
              <img src="<% pic %>">
            </span>
          </div>
        </div>
        {{-- End Info from trade game --}}
      </div>
    </div>
  <input name="trade_game" type="hidden" value="<% id %>">

  <div class="flex-center-space m-t-20">
    <div></div>
    <div><a href="javascript:void(0)" id="reselect-game" class="btn btn-dark"><i class="fa fa-repeat" aria-hidden="true"></i> {{ trans('listings.form.game.reselect') }}</a></div>
  </div>
</div>
</script>
{{-- End Mustache Template for selected game --}}

{{-- Load mask money script if price suggestions is activated --}}
@if($listing->sell_negotiate || $listing->trade_negotiate)
<script src="{{ asset('js/jquery.maskMoney.min.js') }}"></script>
@endif

{{-- Load google maps when user location have lat and long --}}
@if($listing->user->location && $listing->user->location->latitude && $listing->user->location->longitude && config('settings.google_maps_key'))
<script src="//cdnjs.cloudflare.com/ajax/libs/gmap3/7.2.0/gmap3.min.js"></script>
<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key={{ config('settings.google_maps_key') }}"></script>
@endif

@if($listing->picture)
<link rel="stylesheet" href="{{ asset('css/magnific-popup.min.css') }}">
@endif

<script type="text/javascript">
$(document).ready(function(){


   /*$.ajax({
   url : 'http://localhost/wiledia2/public/translate/en/de/{{$listing->game->description}}',
   type: 'GET',

     success: function(data){
         $('.gsummary').html(data);
     }
  });*/



@if($listing->picture)
  {{-- Popup for picture --}}
  $('.open-listing-picture').magnificPopup({
      type: 'image',
      tClose: '{{ trans('games.gallery.close') }}',
      tLoading: '{{ trans('games.gallery.loading') }}',
      image: {
          tError: '{{ trans('games.gallery.error') }}'
      },
      mainClass: 'mfp-zoom-in',
      removalDelay: 300, //delay removal by X to allow out-animation
      callbacks: {
          beforeOpen: function() {
              $('#portfolio a').each(function(){
                  $(this).attr('title', $(this).find('img').attr('alt'));
              });
          },
          open: function() {
              //overwrite default prev + next function. Add timeout for css3 crossfade animation
              $.magnificPopup.instance.next = function() {
                  var self = this;
                  self.wrap.removeClass('mfp-image-loaded');
                  setTimeout(function() { $.magnificPopup.proto.next.call(self); }, 120);
              };
              $.magnificPopup.instance.prev = function() {
                  var self = this;
                  self.wrap.removeClass('mfp-image-loaded');
                  setTimeout(function() { $.magnificPopup.proto.prev.call(self); }, 120);
              };
          },
          imageLoadComplete: function() {
              var self = this;
              setTimeout(function() { self.wrap.addClass('mfp-image-loaded'); }, 16);
          }
      }
   });
@endif

  {{-- Google maps when user location have lat and long --}}
  @if($listing->user->location && $listing->user->location->latitude && $listing->user->location->longitude && config('settings.google_maps_key'))
  var center = [{{$listing->user->location->latitude}}, {{$listing->user->location->longitude}}];
  $('.google-maps')
    .gmap3({
      center: center,
      zoom: 11,
      mapTypeId : google.maps.MapTypeId.ROADMAP
    })
    .marker([
      {position:[{{$listing->user->location->latitude}}, {{$listing->user->location->longitude}}]}
    ])
    .circle({
      center: center,
      radius : 2000,
      fillColor : "#FFAF9F",
      strokeWeight : 0
    });

  @endif

  @if($listing->sell_negotiate)
  {{-- Start mask prices for money input --}}
  var price_suggestion = $("#price_suggestion");
  var price_suggestion_link = $("#price-suggest-link");
  var price_suggestion_form = $("#price-suggest-form");

  var maskMoneyOptions = {thousands:'{{ Currency(Config::get('settings.currency'))->getThousandsSeparator() }}', decimal:'{{ Currency(Config::get('settings.currency'))->getDecimalMark() }}'};

  price_suggestion.maskMoney(maskMoneyOptions);
  price_suggestion.trigger("mask.maskMoney");

  price_suggestion_link.click( function() {
    $("#listing-price").addClass('price-suggestion-enabled ');
    $('#price-suggest-link-wrapper').css({opacity: 0, transition: 'opacity 0.4s'}).slideUp();
    price_suggestion_form.slideDown();
    return false;
  })
  @endif


  @if($listing->trade_negotiate)


  {{-- Start mask prices for money input --}}
  var add_charge_user = $("#add_charge_user");

  var maskMoneyOptions = {thousands:'{{ Currency(Config::get('settings.currency'))->getThousandsSeparator() }}', decimal:'{{ Currency(Config::get('settings.currency'))->getDecimalMark() }}'};

  add_charge_user.maskMoney(maskMoneyOptions);
  add_charge_user.trigger("mask.maskMoney");

  $("#add_charge_user_button").click(function () {
    if ( !$('#add_charge_partner_wrapper').hasClass('contract') ) {
      $("#add_charge_partner").val('');
      $('#add_charge_partner_wrapper').toggleClass('contract');
      $('#add_charge_partner_form').animate({width:'toggle'},100);
    }
    add_charge_user.val('');
    add_charge_user.trigger("mask.maskMoney");
    $('#add_charge_user_wrapper').toggleClass('contract');
    $('#add_charge_user_form').animate({width:'toggle'},100);
    return false;
  });

  {{-- Submit button --}}
  var trade_submit_suggestion = $("#trade-submit-suggestion");

  {{-- Start typeahead for listing game search --}}
  {{-- Bloodhound engine with remote search data in json format --}}
  $('#offersearch').submit(false);
  var listingGameSearch = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    remote: {
      url: '{{ url("games/search/json/%QUERY") }}',
      wildcard: '%QUERY'
    }
  });

  {{-- Typeahead with data from bloodhound engine --}}
  $('#offersearch').typeahead(null, {
    name: 'offer-search',
    display: 'name',
    source: listingGameSearch,
    highlight: true,
    limit:6,
    templates: {
      empty: [
        '<div class="nosearchresult bg-danger"><a href="{{ url("games/add") }}">',
          '<span><i class="fa fa-ban"></i> {{ trans('listings.form.validation.no_game_found') }} <strong>{{ trans('listings.form.validation.no_game_found_add') }}</strong><span>',
        '</a></div>'
      ].join('\n'),
      suggestion: function (data) {
          return '<div class="searchresult hvr-grow-shadow2"><span class="link"><div class="inline-block m-r-10"><span class="avatar"><img src="' + data.pic + '" class="img-circle"></span></div><div class="inline-block"><strong class="title">' + data.name + '</strong><br><small class="text-uc text-xs"><span class="platform-label" style="background-color: ' + data.platform_color + ';">' + data.platform_name + '</span></small></div></span></div>';
      }
    }
  })
  .on('typeahead:asyncrequest', function() {
      $('#listingsearchcomplete').hide();
      $('#listingsearching').show();
  })
  .on('typeahead:asynccancel typeahead:asyncreceive', function() {
      $('#listingsearching').hide();
      $('#listingsearchcomplete').show();
  });

  {{-- Change selected game on selecting typeahead --}}
  $('#offersearch').bind('typeahead:selected', function(obj, datum, name) {
    trade_submit_suggestion.removeClass('loading');
    var customTags = [ '<%', '%>' ];
    Mustache.tags = customTags;
    var template = $('#selected-game').html();
    Mustache.parse(template);   // optional, speeds up future uses
    var append_date = Mustache.render(template, datum);
    $('#select-game').slideUp(300);
    $(append_date).hide().appendTo('.selected-game').css({opacity: 1, transition: 'opacity 0.4s'}).slideDown(300);
    $('.listing-form').delay(300).css({opacity: 0, transition: 'opacity 0.4s'}).slideDown(300);
    setTimeout(function(){$('#offersearch').typeahead('val', ''); }, 10);
    $("#add_charge_partner").maskMoney(maskMoneyOptions).trigger("mask.maskMoney");
    $("#add_charge_partner_button").click(function () {
      if ( !$('#add_charge_user_wrapper').hasClass('contract') ) {
        $("#add_charge_user").val('');
        $('#add_charge_user_wrapper').toggleClass('contract');
        $('#add_charge_user_form').animate({width:'toggle'},100);
      }
      $("#add_charge_partner").val('').trigger("mask.maskMoney");
      $('#add_charge_partner_wrapper').toggleClass('contract');
      $('#add_charge_partner_form').animate({width:'toggle'},100);
      return false;
    });
  });

  {{-- Reset game --}}
  $('.selected-game').on('click', '#reselect-game', function(e) {
    trade_submit_suggestion.addClass('loading');
    e.preventDefault();
    $(this).parent().parent().parent().css({opacity: 0, transition: 'opacity 0.4s'}).slideUp(300, function() {
        $(this).remove();
    });
    $('#select-game').css({opacity: 1, transition: 'opacity 0.4s'}).slideDown(300);
  });
  {{-- End typeahead for listing game search --}}

  {{-- Trade suggestion submit --}}
  trade_submit_suggestion.click( function(){
    $("#trade-submit-suggestion span").html('<i class="fa fa-spinner fa-pulse fa-fw"></i>').addClass('loading');
    $('#form-trade-suggestion').submit();
  });
  @endif



  {{-- Buy submit --}}
  $("#buy-submit").click( function(){
    $('#buy-submit span').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>');
    $('#buy-submit').addClass('loading');
    $('#form-buy').submit();
  });

  {{-- Trade submit --}}
  $(".trade-submit").click( function(){
    $('.trade-submit span').html('<i class="fa fa-spinner fa-pulse fa-fw"></i>');
    $('.trade-submit').addClass('loading');
    $('#form-trade-' + $(this).data('trade')).submit();
  });

  {{-- Trade Button scroll --}}
  $('#trade-button-subheader').click(function(){
      $('html, body').animate({
          scrollTop: $('#trade-list').offset().top
      }, 500);
      $('#trade-info').fadeIn(500);
      return false;
  });

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
      activeTab = $('[role="tablist"] [data-target="#details"]');
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


  {{-- Scroll function for navbar --}}
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
