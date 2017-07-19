@php $link = str_replace('%game_name%', urlencode($game->name), config('settings.buy_button_ref_link')); @endphp

<a href="{{ $link }}" target="_blank" class="buy-button ad m-r-10 m-b-10">
  <span><i class="icon fa fa-shopping-basket" aria-hidden="true"></i> {{ trans('general.ads.buy_ref', ['merchant' => config('settings.buy_button_ref_merchant')]) }}</span>
</a>
