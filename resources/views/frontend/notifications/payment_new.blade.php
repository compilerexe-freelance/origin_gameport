@php
$listing = $listings->where('id', $notification->data['listing_id'] )->first();
$offer = $offers->where('id', $notification->data['offer_id'] )->first();
@endphp
{{-- Start Notification --}}
<section class="panel hvr-grow-shadow2">
  <a class="notification {{ $notification->read_at ? 'grayscale' : '' }} flex-center" href="{{$offer->url}}" data-notif-id="{{$notification->id}}">
    {{-- Notification icon --}}
    <div class="icons flex-center">
      <div class="circle-icon bg-primary">
        <i class="fa fa-money"></i>
      </div>
      {{-- Listing Game --}}
      <span class="avatar no-flex-shrink m-l-10">
        <img src="{{$listing->game->image_square_tiny}}">
      </span>
    </div>

    <div>
      {{-- Notification text --}}
        <span class="display-block">
          {{ trans('notifications.offer_paid', ['username' => $offer->user->name, 'gamename' => $listing->game->name]) }}
        </span>
      {{-- Notificaion icon and date --}}
      <span class="created-at">
        <i class="fa fa-money"></i> {{$notification->created_at->diffForHumans()}}
      </span>
    </div>

  </a>

</section>
{{-- End notification --}}
