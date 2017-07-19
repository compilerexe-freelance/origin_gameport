<?php
namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Models\Listing;
use App\Models\Game;
use App\Models\Platform;
use App\Models\Digital;
use App\Models\User;
use Carbon\Carbon;
use Validator;
use Redirect;
use Session;
use SEO;
use Config;

class ListingController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Check for slug in overview
     *
     * @param  string  $slug
     * @return mixed
     */
    public function index($slug)
    {
        if (substr_count($slug, '-') >= 2) {
            return $this->show($slug);
        } else {
            return $this->overview($slug);
        }
    }

    /**
     * Overview listings
     *
     * @param  string|null  $system
     * @return view
     */
    public function overview($system = null)
    {

        // check for platform
        if ($system != null) {
            $system = platform::where('acronym', $system)->first();

            // check if platform exist
            if ($system == null) {
                return abort('404');
            }
        }

        // Get all active listing
        // check if system is given
        if ($system == null) {
            $listings = Listing::with('game', 'game.giantbomb', 'game.platform', 'user')->orderby('created_at', 'desc')->where('status', null)->whereHas('user', function ($query) {$query->where('status',1);})->orWhere('status', '0')->whereHas('user', function ($query) {$query->where('status',1);})->get();

            // Order - default order is created_at
            $listings_order = session()->has('listingsOrder') ? session()->get('listingsOrder') : 'created_at';

            // Order direction - default is asc
            if (session()->has('listingsOrderByDesc') && session()->get('listingsOrderByDesc')) {
                $listings = $listings->sortBy($listings_order);
            } else {
                $listings = $listings->sortByDesc($listings_order);
            }

            // Platform filters
            if (session()->has('listingsPlatformFilter')) {
                $listings = $listings->whereIn('game.platform_id', session()->get('listingsPlatformFilter'));
            }

            // Option filters
            if (session()->has('listingsOptionFilter')) {
                foreach (session()->get('listingsOptionFilter') as $filter) {
                    $listings = $listings->where($filter, true);
                }
            }

            // Page title
            SEO::setTitle(trans('general.title.listings_all', ['page_name' => config('settings.page_name'), 'sub_title' => config('settings.sub_title')]));
        } else {
            $listings = Listing::with('game', 'game.giantbomb', 'game.platform', 'user')->whereHas('game', function ($query) use ($system) {
                $query->where('platform_id', $system->id);
            })->where('status', null)->whereHas('user', function ($query) {$query->where('status',1);})->orWhere('status', '0')->whereHas('game', function ($query) use ($system) {
                $query->where('platform_id', $system->id);
            })->whereHas('user', function ($query) {$query->where('status',1);})->orderby('created_at', 'desc')->get();

            // Order - default order is created_at
            $listings_order = session()->has('listingsOrder') ? session()->get('listingsOrder') : 'created_at';

            // Order direction - default is asc
            if (session()->has('listingsOrderByDesc') && session()->get('listingsOrderByDesc')) {
                $listings = $listings->sortBy($listings_order);
            } else {
                $listings = $listings->sortByDesc($listings_order);
            }

            // Option filters
            if (session()->has('listingsOptionFilter')) {
                foreach (session()->get('listingsOptionFilter') as $filter) {
                    $listings = $listings->where($filter, true);
                }
            }

            // Page title
            SEO::setTitle(trans('general.title.listings_platform', ['page_name' => config('settings.page_name'), 'sub_title' => config('settings.sub_title'), 'platform' => $system->name]));
        }

        // Get the current page from the url if it's not set default to 1
        $page = Input::get('page', 1);

        // Number of items per page
        $perPage = 24;

        // Start displaying items from this number;
        $offSet = ($page * $perPage) - $perPage; // Start displaying items from this number

        $listings_paginated = new \Illuminate\Pagination\LengthAwarePaginator($listings->forPage($page,$perPage), count($listings), $perPage, $page, ['path' => request()->url()]);

        if ($listings_paginated->isEmpty() && session()->has('listingsOptionFilter') && $page != 1) {
            if ($system == null) {
                return redirect('listings');
            } else {
                return redirect('listings/' . $system->acronym);
            }
        }

        return view('frontend.listing.overview', ['listings' => $listings_paginated,  'system' => $system]);
    }

    /**
     * Show listing details
     *
     * @param  string  $slug
     * @return view
     */
    public function show($slug)
    {
        // Get listing id from slug string
        $listing_id = ltrim(strrchr($slug, '-'), '-');
        $listing = Listing::with('game', 'user', 'game.giantbomb', 'game.platform')->find($listing_id);

        // Check if listing exists
        if (is_null($listing)) {
            return abort('404');
        }

        // Check if slug is right
        $slug_check = str_slug($listing->game->name) . '-' . $listing->game->platform->acronym . '-' . str_slug($listing->user->name) . '-' . $listing->id;

        // Redirect to correct slug link
        if ($slug_check != $slug) {
            return Redirect::to(url('listings/' . $slug_check));
        }

        // Trade list
        if ($listing->trade_list) {
            $trade_list = Game::whereIn('id', array_keys(json_decode($listing->trade_list, true)))->with('giantbomb', 'platform')->get();
        } else {
            $trade_list = null;
        }

        // increment clicks
        $listing->increment('clicks');

        // SEO Data
        if ($listing->sell == 1) {
            SEO::setTitle(trans('general.title.listing_buy', ['game_name' => $listing->game->name, 'platform' => $listing->game->platform->name, 'price' => $listing->price_formatted, 'user_name' => $listing->user->name, 'place' =>  isset($listing->user->location) ? $listing->user->location->place : '']));
            SEO::setDescription(trans('general.description.listing_buy', ['game_name' => $listing->game->name, 'platform' => $listing->game->platform->name, 'price' => $listing->price_formatted, 'user_name' => $listing->user->name, 'place' =>  isset($listing->user->location) ? $listing->user->location->place : '', 'page_name' => config('settings.page_name'), 'sub_title' => config('settings.sub_title')]));
        } else {
            SEO::setTitle(trans('general.title.listing_trade', ['game_name' => $listing->game->name, 'platform' => $listing->game->platform->name, 'user_name' => $listing->user->name, 'place' =>  $listing->user->location->place]));
            SEO::setDescription(trans('general.description.listing_trade', ['game_name' => $listing->game->name, 'platform' => $listing->game->platform->name, 'user_name' => $listing->user->name, 'place' =>  $listing->user->location->place, 'page_name' => config('settings.page_name'), 'sub_title' => config('settings.sub_title')]));
        }


        SEO::metatags()->addMeta('article:published_time', $listing->created_at->toW3CString(), 'property');
        SEO::metatags()->addMeta('article:section', $listing->game->platform->name, 'property');

        // Get image size for og
        if ($listing->game->image_cover) {
            $imgsize = getimagesize($listing->game->image_cover);
            SEO::opengraph()->addImage(['url' => $listing->game->image_cover, ['height' => $imgsize[1], 'width' => $imgsize[0]]]);
            // Twitter Card Image
            SEO::twitter()->setImage($listing->game->image_cover);
        }

        // Set back URL when logged user can edit listing
        if (\Auth::check() && (\Auth::user()->id == $listing->user_id || \Auth::user()->hasPermission('edit_listings'))) {
            // Save back URL for finished form
          Session::flash('backUrl', $listing->url_slug);
        }

        return view('frontend.listing.show', ['game' => $listing->game, 'listing' => $listing,'trade_list' => $trade_list]);
    }

    /**
     * Show listing create form
     *
     * @return view
     */
    public function add()
    {
        // check if user account is active
        if (! \Auth::user()->isActive()) {
            \Auth::logout();
            return redirect('login')->with('error', trans('auth.deactivated'));
        }

        SEO::setTitle(trans('general.title.listing_add', ['page_name' => config('settings.page_name'), 'sub_title' => config('settings.sub_title')]));
        return view('frontend.listing.form', ['platforms' => \App\Models\Platform::all()]);
    }

    /**
     * Edit listing form
     *
     * @param  string $slug
     * @return view
     */
    public function editForm($slug)
    {

        // get back url from session when listing is saved
        if (Session::has('backUrl')) {
            Session::keep('backUrl');
        }

        // Check if logged in
        if (!(\Auth::check())) {
            return Redirect::to('/');
        }

        // check if user account is active
        if (! \Auth::user()->isActive()) {
            \Auth::logout();
            return redirect('login')->with('error', trans('auth.deactivated'));
        }

        // Get listing id from slug string
        $listing_id = ltrim(strrchr($slug, '-'), '-');
        $listing = Listing::with('game', 'user', 'game.giantbomb', 'game.platform')->find($listing_id);

        // Check if listing exists
        if (is_null($listing)) {
            return Redirect::to('/');
        }

        // Check if User can edit listing
        if (!(\Auth::user()->id == $listing->user_id) && !\Auth::user()->hasPermission('edit_listings')) {
            return abort('404');
        }

        // Check listing status
        if (!($listing->status == 0 || is_null($listing->status))) {
            return abort('404');
        }

        // Check if slug is right
        $slug_check = str_slug($listing->game->name) . '-' . $listing->game->platform->acronym . '-' . str_slug($listing->user->name) . '-' . $listing->id;

        // Redirect to correct slug link
        if ($slug_check != $slug) {
            return Redirect::to(url('listings/' . $slug_check . '/edit'));
        }

        // Trade list
        if ($listing->trade_list) {
            $trade_list = Game::whereIn('id', array_keys(json_decode($listing->trade_list, true)))->with('giantbomb', 'platform')->get();
        } else {
            $trade_list = null;
        }

        // Page title
        SEO::setTitle(trans('general.title.listing_edit', ['game_name' => $listing->game->name, 'platform' => $listing->game->platform->name]));

        return view('frontend.listing.form', ['platforms' => \App\Models\Platform::all(), 'listing' => $listing, 'game' => $listing->game, 'trade_list' => $trade_list]);
    }

    /**
     * Add new listing form with game
     *
     * @param  string $slug
     * @return view
     */
    public function gameForm($slug)
    {
        if (Session::has('backUrl')) {
            Session::keep('backUrl');
        }

        // Check if logged in
        if (!(\Auth::check())) {
            return Redirect::to('/');
        }

        // check if user account is active
        if (! \Auth::user()->isActive()) {
            \Auth::logout();
            return redirect('login')->with('error', trans('auth.deactivated'));
        }

        // Get listing id from slug string
        $game_id = ltrim(strrchr($slug, '-'), '-');
        $game = Game::with('giantbomb', 'platform')->find($game_id);

        // Check if listing exists
        if (is_null($game)) {
            return abort('404');
        }

        // Check if slug is right
        $slug_check = str_slug($game->name) . '-' . $game->platform->acronym . '-' . $game->id;

        // Redirect to correct slug link
        if ($slug_check != $slug) {
            return Redirect::to(url('listings/' . $slug_check . '/new'));
        }

        SEO::setTitle(trans('general.title.listing_add_game', ['page_name' => config('settings.page_name'), 'sub_title' => config('settings.sub_title'),'game_name' => $game->name, 'platform' => $game->platform->name]));

        return view('frontend.listing.form', ['platforms' => \App\Models\Platform::all(), 'game' => $game]);
    }

    /**
     * Save listing after edit
     *
     * @param  Request $request
     * @return redirect
     */
    public function edit(Request $request)
    {
        // Check if logged in
        if (!(\Auth::check())) {
            return Redirect::to('login');
        }

        // check if user account is active
        if (! \Auth::user()->isActive()) {
            \Auth::logout();
            return redirect('login')->with('error', trans('auth.deactivated'));
        }

        // check if user changed hidden inputs
        try {
            $request->merge(array('game_id' => decrypt($request->game_id), 'listing_id' => decrypt($request->listing_id)));
        } catch(\Exception $ex) {
            // show a alert message
            \Alert::error('<i class="fa fa-times m-r-5"></i> Nothing saved. Do not try to change hidden inputs!')->flash();

            return ($url = Session::get('backUrl')) ? redirect()->to($url) : redirect()->back();
        }

        $this->validate($request, [
            'game_id' => 'required|exists:games,id',
            'listing_id' => 'required|exists:listings,id'
        ]);

        $listing = Listing::find($request->listing_id);

        // Check if game id is right
        if ($listing->game->id != $request->game_id) {
            // show a alert message
            \Alert::error('<i class="fa fa-times m-r-5"></i> Nothing saved. Do not try to change hidden inputs!')->flash();

            return ($url = Session::get('backUrl')) ? redirect()->to($url) : redirect()->back();
        }

        // Check if User can edit listing
        if (!(\Auth::user()->id == $listing->user_id) && !\Auth::user()->hasPermission('edit_listings')) {
            return abort('404');
        }

        // Check listing status
        if (!($listing->status == 0 || is_null($listing->status))) {
            return abort('404');
        }

        if ($request->sell_status == 0 && $request->trade_status == 0) {
            return Redirect::to('/');
        }


        $datapost = Input::all();

        $datapost['delivery'] = (Input::has('delivery')) ? 1 : 0;
        $datapost['pickup'] = (Input::has('pickup')) ? 1 : 0;

        $datapost['digital'] = (Input::has('digital')) ? 1 : 0;
        $datapost['limited'] = (Input::has('limited')) ? 1 : 0;

        if ($datapost['limited'] == 1 && Input::get('limited_name') !== "") {
            $limited_edition = $datapost['limited_name'];
        }


        if ($datapost['limited'] == 1 && Input::get('limited_name') !== "") {
            $limited_edition = $datapost['limited_name'];
        }

        // check if delivery or pickup is selected
        if ($datapost['delivery'] == 0 && $datapost['pickup'] == 0 && !config('settings.digital_downloads_only')) {
            return ($url = Session::get('backUrl')) ? redirect()->to($url) : redirect()->back();
        }

        if (isset($datapost['trade_list'])) {
            // Save Trade List data to games_trade for game overview
            foreach ($datapost['trade_list'] as $trade_game) {
              // filter price
              $add_price = filter_var($trade_game['price'], FILTER_SANITIZE_NUMBER_INT);
              // check if listing game is in trade list
              if ($trade_game['id'] != $request->game_id) {
                  $data_trade[$trade_game['id']] = array(
                  'game_id' => $trade_game['id'],
                  'price' => !empty($add_price) ? $add_price : '0',
                  'price_type' => !empty($add_price) ? $trade_game['price_type'] : 'none',
                );
              }
            }

            $trade_list = isset($data_trade) ? json_encode($data_trade) : null;
        } else {
            $trade_list = null;
            $trade_status = 0;
        }


        // Listing details
        $listing->limited_edition = isset($limited_edition) ? $limited_edition : null;
        $listing->condition = $request->condition;
        // Check if digital downloads only is enabled
        if (config('settings.digital_downloads_only')) {
            $listing->pickup = 0;
            $listing->delivery = 1;
            $listing->delivery_price = null;
        } else {
            $listing->pickup = $request->pickup ? 1 : 0;
            $listing->delivery = $request->delivery ? 1 : 0;
            $listing->delivery_price = $request->delivery ? $request->delivery_price : null;
        }
        $listing->description = $request->description;

        // check if digital ditributor exists
        $digital_distributor = Digital::find($request->digital_distributor);

        // Digital Download
        if (($datapost['digital'] == 1 && $digital_distributor) || config('settings.digital_downloads_only') && $digital_distributor ) {
            $listing->digital = $digital_distributor->id;
            $listing->condition = null;
        } else {
            if ($listing->condition == 0) {
                $listing->condition = 5;
            }
            $listing->digital = null;
        }

        // Sell data
        $listing->sell_negotiate = $request->sell_status == 1 ? ($request->sell_negotiate ? 1 : 0) : 0;
        $listing->sell = $request->sell_status;
        $listing->price = $request->sell_status == 1 ? $request->price : null;

        // Trade data
        $listing->trade_negotiate =  $request->trade_status == 1 ? ($request->trade_negotiate ? 1 : 0) : 0;
        $listing->trade = $trade_list ? $request->trade_status : ($request->trade_status && $request->trade_negotiate ? 1 : 0);
        $listing->trade_list = $request->trade_status == 1 ? $trade_list : null;

        // Payment data
        $listing->payment =  $request->sell_status ? ($request->enable_payment ? 1 : 0) : 0;

        // Remove picture
        if ($request->picture_remove && !is_null($listing->picture) && !$request->hasFile('picture')) {
            $disk = "local";
            \Storage::disk($disk)->delete('/public/listings/' . $listing->picture);
            $listing->picture = null;
        }

        // Picture
        if ($request->hasFile('picture')) {

            // Image Beta
            $extension = 'jpg';
            $newfilename = time().'-'.$listing->id.'.'.$extension;
            $destination_path = "public/listings";


            $img = \Image::make($request->picture->path());
            $disk = "local";

            \Storage::disk($disk)->put($destination_path.'/'.$newfilename, $img->stream());

            // Delete old image
            if (!is_null($listing->picture)) {
                \Storage::disk($disk)->delete('/public/listings/' . $listing->picture);
            }

            $listing->picture = $newfilename;
        }

        // stop saving when sell and trade status is still 0
        if ($listing->sell == 0 && $listing->trade == 0) {
            return ($url = Session::get('backUrl')) ? redirect()->to($url) : redirect()->back();
        }

        $listing->save();

        // create trade list for game
        if ($listing->trade_list) {
            foreach (json_decode($listing->trade_list) as $trade_game) {
                $trade_synch_list[$trade_game->game_id] = ['listing_game_id' => $listing->game_id, 'price' => $trade_game->price, 'price_type' => $trade_game->price_type];
            }
            $listing->tradegames()->sync($trade_synch_list);
        } else {
            $listing->tradegames()->detach();
        }

        // show a success message
        \Alert::success('<i class="fa fa-save m-r-5"></i>' . trans('listings.alert.saved', ['game_name' => str_replace("'", '', $listing->game->name)]))->flash();

        return ($url = Session::get('backUrl')) ? redirect()->to($url) : redirect()->back();
    }

    /**
     * Delete listing
     *
     * @param  Request $request
     * @return redirect
     */
    public function delete(Request $request)
    {

        // Check if logged in
        if (!(\Auth::check())) {
            return abort('404');
        }

        // check if user account is active
        if (! \Auth::user()->isActive()) {
            \Auth::logout();
            return redirect('login')->with('error', trans('auth.deactivated'));
        }

        // decrypt input
        $request->merge(array('listing_id' => decrypt($request->listing_id)));

        $this->validate($request, [
            'listing_id' => 'required|exists:listings,id'
        ]);

        $listing = Listing::find($request->listing_id);

        if (!$listing) {
            return abort('404');
        }

        // Check if logged in user can delete this listing
        if (!\Auth::user()->can('edit_listings') && !(\Auth::user()->id == $listing->user_id)) {
            return abort('404');
        }

        // Check status of listing
        if ($listing->status >= 1) {
            return abort('404');
        }

        // Check if delete from listing
        if (\URL::previous() == $listing->url_slug) {
            $redirect_back = false;
        } else {
            $redirect_back = true;
        }

        // Remove picture
        if (!is_null($listing->picture)) {
            $disk = "local";
            \Storage::disk($disk)->delete('/public/listings/' . $listing->picture);
            $listing->picture = null;
            $listing->save();
        }

        // delete listing
        $listing->delete();

        // show a success message
        \Alert::error('<i class="fa fa-trash m-r-5"></i>' . trans('listings.alert.deleted', ['game_name' => str_replace("'", '', $listing->game->name)]))->flash();

        return $redirect_back ? redirect()->back() : redirect()->to('/');
    }

    /**
     * Store new listing
     *
     * @param  Request $request
     * @return redirect
     */
    public function store(Request $request)
    {
        // Check if logged in
        if (!(\Auth::check())) {
            return Redirect::to('login');
        }

        // check if user account is active
        if (! \Auth::user()->isActive()) {
            \Auth::logout();
            return redirect('login')->with('error', trans('auth.deactivated'));
        }

        $this->validate($request, [
            'game_id' => 'required|exists:games,id'
        ]);

        // check if sell and trade is deactivated
        if ($request->sell_status == 0 && $request->trade_status == 0) {
            return ($url = Session::get('backUrl')) ? redirect()->to($url) : redirect()->back();
        }

        // Check if user set location
        if (!\Auth::user()->location) {
            return ($url = Session::get('backUrl')) ? redirect()->to($url) : redirect()->back();
        }

        $datapost = Input::all();

        $datapost = Input::all();

        $datapost['delivery'] = (Input::has('delivery')) ? 1 : 0;
        $datapost['pickup'] = (Input::has('pickup')) ? 1 : 0;

        $datapost['digital'] = (Input::has('digital')) ? 1 : 0;
        $datapost['limited'] = (Input::has('limited')) ? 1 : 0;

        if ($datapost['limited'] == 1 && Input::get('limited_name') !== "") {
            $limited_edition = $datapost['limited_name'];
        }

        // check if delivery or pickup is selected
        if ($datapost['delivery'] == 0 && $datapost['pickup'] == 0 && !config('settings.digital_downloads_only')) {
            return ($url = Session::get('backUrl')) ? redirect()->to($url) : redirect()->back();
        }

        if (isset($datapost['trade_list'])) {
            // Save Trade List data to games_trade for game overview
            foreach ($datapost['trade_list'] as $trade_game) {
              // filter price
              $add_price = filter_var($trade_game['price'], FILTER_SANITIZE_NUMBER_INT);
              // check if listing game is in trade list
              if ($trade_game['id'] != $request->game_id) {
                  $data_trade[$trade_game['id']] = array(
                  'game_id' => $trade_game['id'],
                  'price' => !empty($add_price) ? $add_price : '0',
                  'price_type' => !empty($add_price) ? $trade_game['price_type'] : 'none',
                );
              }
            }
            $trade_list = isset($data_trade) ? json_encode($data_trade) : null;
        } else {
            $trade_list = null;
            $trade_status = 0;
        }

        // create new listing
        $listing = new Listing;

        // General data
        $listing->user_id = \Auth::user()->id;
        $listing->game_id = $request->game_id;


        // Listing details
        $listing->limited_edition = isset($limited_edition) ? $limited_edition : null;
        $listing->condition = $request->condition;
        // Check if digital downloads only is enabled
        if (config('settings.digital_downloads_only')) {
            $listing->pickup = 0;
            $listing->delivery = 1;
            $listing->delivery_price = null;
        } else {
            $listing->pickup = $request->pickup ? 1 : 0;
            $listing->delivery = $request->delivery ? 1 : 0;
            $listing->delivery_price = $request->delivery ? $request->delivery_price : null;
        }
        $listing->description = $request->description;

        // check if digital ditributor exists
        $digital_distributor = Digital::find($request->digital_distributor);

        // Digital Download
        if (($datapost['digital'] == 1 && $digital_distributor) || config('settings.digital_downloads_only') && $digital_distributor ) {
            $listing->digital = $digital_distributor->id;
            $listing->condition = null;
        } else {
            if ($listing->condition == 0) {
                $listing->condition = 5;
            }
            $listing->digital = null;
        }

        // Sell data
        $listing->sell_negotiate = $request->sell_status == 1 ? ($request->sell_negotiate ? 1 : 0) : 0;
        $listing->sell = $request->sell_status;
        $listing->price = $request->sell_status == 1 ? $request->price : null;

        // Trade data
        $listing->trade_negotiate =  $request->trade_status == 1 ? ($request->trade_negotiate ? 1 : 0) : 0;
        $listing->trade = $trade_list ? $request->trade_status : ($request->trade_status && $request->trade_negotiate ? 1 : 0);
        $listing->trade_list = $request->trade_status == 1 ? $trade_list : null;

        // Payment data
        $listing->payment =  $request->sell_status ? ($request->enable_payment ? 1 : 0) : 0;

        // stop saving when sell and trade status is still 0
        if ($listing->sell == 0 && $listing->trade == 0) {
            return ($url = Session::get('backUrl')) ? redirect()->to($url) : redirect()->back();
        }

        $listing->clicks = 0;

        $listing->last_offer_at = new Carbon;

        $listing->save();

        // Picture
        if ($request->hasFile('picture')) {

            // Image Beta
            $extension = 'jpg';
            $newfilename = time().'-'.$listing->id.'.'.$extension;
            $destination_path = "public/listings";


            $img = \Image::make($request->picture->path());
            $disk = "local";

            \Storage::disk($disk)->put($destination_path.'/'.$newfilename, $img->stream());

            $listing->picture = $newfilename;
        }

        $listing->save();

        // create trade list for game
        if ($listing->trade_list) {
            foreach (json_decode($listing->trade_list) as $trade_game) {
                $trade_synch_list[$trade_game->game_id] = ['listing_game_id' => $listing->game_id, 'price' => $trade_game->price, 'price_type' => $trade_game->price_type];
            }
            $listing->tradegames()->sync($trade_synch_list);
        }

        // show a success message
        \Alert::success('<i class="fa fa-plus m-r-5"></i>' . trans('listings.alert.created', ['game_name' => str_replace("'", '', $listing->game->name)]))->flash();

        return Redirect::to($listing->url_slug);
    }

    /**
     * Sort listings
     *
     * @param  string  $slug
     * @return mixed
     */
    public function order($order, $desc = null)
    {
        session()->put('listingsOrder', $order);

        if ($desc == 'desc') {
            session()->put('listingsOrderByDesc', true);
        } else {
            session()->put('listingsOrderByDesc', false);
        }

        return Redirect::to(url()->current() == url()->previous() ? url('/') : url()->previous());
    }

    /**
     * Filter listings
     *
     * @param  string  $slug
     * @return mixed
     */
    public function filter(\Illuminate\Http\Request $request)
    {
        session()->put('listingsPlatformFilter', $request->platformIds);
        session()->put('listingsOptionFilter', $request->options);

        return url()->current() == url()->previous() ? url('/') : url()->previous();
    }

    /**
     * Remove filter for listings
     *
     * @param  string  $slug
     * @return mixed
     */
    public function filterRemove()
    {
        session()->remove('listingsPlatformFilter');
        session()->remove('listingsOptionFilter');

        return Redirect::to(url()->current() == url()->previous() ? url('/') : url()->previous());
    }
}
