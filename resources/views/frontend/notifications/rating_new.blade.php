@php
$listing = $listings->where('id', $notification->data['listing_id'] )->first();
$offer = $offers->where('id', $notification->data['offer_id'] )->first();
{{-- Set right user --}}
Auth::user()->id == $listing->user->id ? $user = $offer->user : $user = $listing->user;
@endphp
{{-- Start Notification --}}
<section class="panel hvr-grow-shadow2">
  <a class="notification {{ $notification->read_at ? 'grayscale' : '' }} flex-center" href="{{$offer->url}}" data-notif-id="{{$notification->id}}">
    <div class="icons flex-center">
      {{-- Rating icon --}}
      @if($notification->data['rating'] == 0)
      <div class="circle-icon bg-danger">
        <i class="fa fa-thumbs-down"></i>
      </div>
      @elseif($notification->data['rating'] == 1)
      <div class="circle-icon bg-dark">
        <i class="fa fa-minus"></i>
      </div>
      @elseif($notification->data['rating'] == 2)
      <div class="circle-icon bg-success">
        <i class="fa fa-thumbs-up"></i>
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
          {{-- Rating text --}}
          @if($notification->data['rating'] == 0)
            {{ trans('notifications.rating_new_negative', ['username' => $user->name]) }}
          @elseif($notification->data['rating'] == 1)
            {{ trans('notifications.rating_new_neutral', ['username' => $user->name]) }}
          @elseif($notification->data['rating'] == 2)
            {{ trans('notifications.rating_new_positive', ['username' => $user->name]) }}
          @endif
        </span>
      {{-- Notificaion icon and date --}}
      <span class="created-at">
        @if($notification->data['rating'] == 0)
          <i class="fa fa-thumbs-down"></i>
        @elseif($notification->data['rating'] == 1)
          <i class="fa fa-minus"></i>
        @elseif($notification->data['rating'] == 2)
          <i class="fa fa-thumbs-up"></i>
        @endif
        {{$notification->created_at->diffForHumans()}}
      </span>
    </div>

  </a>

</section>
{{-- End notification --}}
