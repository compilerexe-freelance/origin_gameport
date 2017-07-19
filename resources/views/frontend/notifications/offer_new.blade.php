@php
$listing = $listings->where('id', $notification->data['listing_id'] )->first();
$offer = $offers->where('id', $notification->data['offer_id'] )->first();
@endphp
{{-- Start Notification --}}
<section class="panel hvr-grow-shadow2">
  <a class="notification {{ $notification->read_at ? 'grayscale' : '' }} flex-center" href="{{$offer->url}}" data-notif-id="{{$notification->id}}">
    {{-- Notification icon --}}
    <div class="icons flex-center">
      {{-- Listing Game --}}
      <span class="avatar no-flex-shrink m-r-10">
        <img src="{{$listing->game->image_square_tiny}}">
      </span>
      {{-- Icon between --}}
      <i class="fa {{ $notification->data['trade'] ? 'fa-exchange' : 'fa-shopping-basket'}}  no-flex-shrink" aria-hidden="true"></i>
      {{-- Trade game or price --}}
      <span class="avatar no-flex-shrink m-l-10">
        @if($notification->data['trade'])
          @if($offer->game)
            <img src="{{$offer->game->image_square_tiny}}">
          @endif
        @else
          <span class="text-success f-w-700">{{$offer->price_offer_formatted}}</span>
        @endif
      </span>
    </div>

    <div>
      {{-- Notification text --}}
      @if($notification->data['trade'])
        <span class="display-block">
          {{ trans('notifications.offer_new_trade', ['username' => $offer->user->name, 'gamename' => $listing->game->name, 'tradegame' => $offer->game ? $offer->game->name : 'removed']) }}
        </span>
      @else
        <span class="display-block">
          {{ trans('notifications.offer_new_buy', ['username' => $offer->user->name, 'gamename' => $listing->game->name, 'price' => $offer->price_offer_formatted]) }}
        </span>
      @endif
      {{-- Notificaion icon and date --}}
      <span class="created-at">
        <i class="fa {{ $notification->data['trade'] ? 'fa-exchange' : 'fa-shopping-basket' }}"></i> {{$notification->created_at->diffForHumans()}}
      </span>
    </div>

  </a>

</section>
{{-- End notification --}}
