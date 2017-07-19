@extends('frontend.layouts.app')


@section('subheader')

<div style="position: relative">


  <div style="position: absolute; z-index:0 !important; top: 0; width: 100%; ">

    <div id="parallax" style="background: linear-gradient(0deg, rgba(26,24,24,1) 0%, rgba(26,24,24,0.8) 30%, rgba(26,24,24,0.5) 100%),url('{{ asset('/img/game_pattern_white.png') }}'); height:200px; "></div>
  </div>

</div>

@stop



@section('content')

<div style="margin-bottom: 20px; font-size: 24px; color: #fff; font-weight: 700; "><i class="fa fa-search" aria-hidden="true"></i> {{ trans('games.overview.search_result', ['value' => $value]) }}</div>

{{-- <hr style="border-top: 1px solid rgba(255,255,255,0.2)"> --}}


  {{-- START GAME LIST --}}
  <div class="row">
    @foreach ($games as $game)
      {{-- START GAME --}}
      <div class="col-xs-6 col-sm-4 col-md-3 col-lg-3 col-xl-2 m-b-20">

        {{-- Start Game Cover --}}
        <div class="card game-cover-wrapper hvr-grow-shadow">
          {{-- Pacman Loader for background image - show only when cover exists --}}
          @if($game->image_cover)
          <div class="loader pacman-loader cover-loader"></div>
          {{-- Show game name, when no cover exist --}}
          @else
          <div class="no-cover-name">{{$game->name}}</div>
          @endif

          <a href="{{ $game->url_slug }}">

            {{-- Start listing count ribbon --}}
            @if($game->listingsCount > 0)
            <div class="ribbon ribbon-clip ribbon-bottom ribbon-dark">
              <div class="ribbon-inner">
                <span class="currency"><i class="fa fa-tags"></i></span><span class="price"> {{$game->listingsCount}}</span>
              </div>
            </div>
            @endif
            {{-- End listing count ribbon --}}

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
          </a>
        </div>
        {{-- End Game Cover --}}
      </div>
      {{-- End GAME --}}
    @endforeach

  </div>
  {{-- END GAME LIST --}}

  {{ $games->links() }}

@stop
