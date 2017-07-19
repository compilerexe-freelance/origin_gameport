@php
$listing = $listings->where('id', $notification->data['listing_id'] )->first();
$user = $users->where('id', $notification->data['user_id'] )->first();
@endphp
{{-- Start Notification --}}
<section class="panel hvr-grow-shadow2">
  <a class="notification {{ $notification->read_at ? 'grayscale' : '' }} flex-center" href="{{$listing->url_slug}}#!comments" data-notif-id="{{$notification->id}}">
    {{-- Notification icon --}}
    <div class="icons flex-center">
      {{-- Listing Game --}}
      <span class="avatar no-flex-shrink">
        <img src="{{$listing->game->image_square_tiny}}">
      </span>
      {{-- User avatar --}}
      <span class="avatar no-flex-shrink m-l-10">
        <img src="{{$user->avatar_square_tiny}}">
      </span>
    </div>

    <div>
      {{-- Notification text --}}
        <span class="display-block">
          {{ trans('notifications.comment_new', ['username' => $user->name, 'gamename' => $listing->game->name]) }}
        </span>
      {{-- Notificaion icon and date --}}
      <span class="created-at">
        <i class="fa fa-comment"></i> {{$notification->created_at->diffForHumans()}}
      </span>
    </div>

  </a>

</section>
{{-- End notification --}}
