@php
$listing = $listings->where('id', $notification->data['listing_id'] )->first();
$offer = $offers->where('id', $notification->data['offer_id'] )->first();
@endphp
{{-- Start Notification --}}
<section class="panel hvr-grow-shadow2">
  <a class="notification {{ $notification->read_at ? 'grayscale' : '' }} flex-center" href="{{$offer->url}}" data-notif-id="{{$notification->id}}">
    <div class="icons flex-center">
      {{-- Notification icon --}}
      @if($notification->data['status'] == 'declined')
      <div class="circle-icon bg-danger">
        <i class="fa fa-times"></i>
      </div>
      @else
      <div class="circle-icon bg-success">
        <i class="fa fa-check"></i>
      </div>
      @endif
      {{-- Listing Game --}}
      <span class="avatar no-flex-shrink m-l-10">
        <img src="{{$listing->game->image_square_tiny}}">
      </span>
    </div>

    <div>
      {{-- Notification text --}}
        <span class="display-block">
          @if($notification->data['status'] == 'declined')
            {{ trans('notifications.offer_status_declined', ['username' => $listing->user->name, 'gamename' => $listing->game->name]) }}
          @else
            {{ trans('notifications.offer_status_accepted', ['username' => $listing->user->name, 'gamename' => $listing->game->name]) }}
          @endif
        </span>
      {{-- Notificaion icon and date --}}
      <span class="created-at">
        <i class="fa {{ $notification->data['status'] == 'declined' ? 'fa-times' : 'fa-check' }}"></i> {{$notification->created_at->diffForHumans()}}
      </span>
    </div>

  </a>

</section>
{{-- End notification --}}
