<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\CrudTrait;
use ClickNow\Money\Money;
use Config;

class Listing extends Model
{
    use CrudTrait, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'listings';
    protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['user_id','game_id','name','picture','description','price','condition','limited_edition','delivery','delivery_price','pickup','sell','trade','trade_list','status','clicks'];
    // protected $hidden = [];
    protected $dates = ['deleted_at'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function game()
    {
        return $this->belongsTo('App\Models\Game')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function offers()
    {
        return $this->hasMany('App\Models\Offer')->orderBy('created_at', 'desc');
    }

    // This is a list of all games that can be trade for the game
    public function tradegames()
    {
        return $this->belongsToMany('App\Models\Game', 'game_trade')->withPivot('listing_game_id', 'price', 'price_type');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */

    /*
    |
    | Format Condition to string. 5 Condition Levels - 1 is worst -> 5 is best.
    | You can change the names of the coditions in your listings lang file.
    |
    */
    public function getConditionStringAttribute()
    {
        switch ($this->condition) {
            case null:
              return trans('listings.general.digital_download');
            case 1:
                return trans('listings.general.conditions.1');
            case 2:
                return trans('listings.general.conditions.2');
            case 3:
                return trans('listings.general.conditions.3');
            case 4:
                return trans('listings.general.conditions.4');
            case 5:
                return trans('listings.general.conditions.5');
        }
    }

    /*
    |
    | Format price attribute to given currency from settings. (without symbol)
    |
    */
    public function getPriceFormattedAttribute()
    {
        return money($this->price, Config::get('settings.currency'))->format(true, Config::get('settings.decimal_place'));
    }

    /*
    |
    | Method to get price with or without symbol
    |
    */
    public function getPrice($currency = true)
    {
        return money($this->price, Config::get('settings.currency'))->format($currency, Config::get('settings.decimal_place'));
    }

    /*
    |
    | Convert formatted price attribute to int before saving to database.
    |
    */
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = abs(filter_var($value, FILTER_SANITIZE_NUMBER_INT));
    }

    /*
    |
    | Convert formatted price attribute to int before saving to database.
    |
    */
    public function setDeliveryPriceAttribute($value)
    {
        $this->attributes['delivery_price'] = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /*
    |
    | Format delivery price attribute to given currency from settings.
    |
    */
    public function getDeliveryPriceFormattedAttribute()
    {
        return money($this->delivery_price, Config::get('settings.currency'))->format(true, Config::get('settings.decimal_place'));
    }

    /*
    |
    | Method to get price with or without symbol
    |
    */
    public function getDeliveryPrice($currency = true)
    {
        return money($this->delivery_price, Config::get('settings.currency'))->format(true, Config::get('settings.decimal_place'));
    }

    /*
    |
    | Get URL
    |
    */
    public function getUrlSlugAttribute()
    {
        return url('listings/' . str_slug($this->game->name) . '-' . $this->game->platform->acronym . '-' . strtolower($this->user->name) . '-' . $this->id);
    }

    /*
    |
    | Get Original Picture
    |
    */
    public function getPictureOriginalAttribute()
    {
        if (!is_null($this->picture)) {
            return asset('images/picture/' . $this->picture);
        } else {
            return null;
        }
    }

    /*
    |
    | Get Original Picture
    |
    */
    public function getPictureSquareAttribute()
    {
        if (!is_null($this->picture)) {
            return asset('images/avatar_square/' . $this->picture);
        } else {
            return null;
        }
    }

    /*
    |
    | Get distance to listing
    |
    */
    public function getDistanceAttribute()
    {
        // get location points (lat / long)
        if ($this->user->location->latitude && $this->user->location->longitude) {
            $latitudeFrom = $this->user->location->latitude;
            $longitudeFrom = $this->user->location->longitude;
        } else {
            return null;
        }

        if (\Auth::check() && (\Auth::user()->location && \Auth::user()->location->longitude && \Auth::user()->location->latitude)) {
            if (\Auth::user()->id == $this->user->id) {
                return null;
            }
            $latitudeTo = \Auth::user()->location->latitude;
            $longitudeTo = \Auth::user()->location->longitude;
        } elseif (session()->has('latitude') && session()->has('longitude')) {
            $latitudeTo = session()->get('latitude');
            $longitudeTo = session()->get('longitude');
        } else {
            return null;
        }

        // calculate distance
        $theta = $longitudeFrom - $longitudeTo;
        $dist = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;

        // return distance in unit set in the admin panel
        switch (config('settings.distance_unit')) {
            case 'km':
                return round($miles * 1.609344);
            case 'mi':
                return round($miles);
            case 'nm':
                return round($miles * 0.8684);
            default:
                return round($miles);
        }

    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |
    | Get user for backend
    |
    */
    public function getUserAdmin()
    {
        if ($this->user->isOnline()) {
            return '<div class="user-block">
					<img class="img-circle" src="' . $this->user->avatar_square_tiny . '" alt="User Image">
					<span class="username"><a href="' . $this->user->url .'" target="_blank">' . $this->user->name . '</a></span>
					<span class="description"><i class="fa fa-circle text-success"></i> Online</span>
				</div>';
        } else {
            return '<div class="user-block">
						<img class="img-circle" src="' . $this->user->avatar_square_tiny . '" alt="User Image">
						<span class="username"><a href="' . $this->user->url .'" target="_blank">' . $this->user->name . '</a></span>
						<span class="description"><i class="fa fa-circle text-danger"></i> Offline</span>
					</div>';
        }
    }

    /*
    |
    | Get Name with cover and release year for backend
    |
    */
    public function getGameAdmin()
    {
        return '<div class="user-block">
					<img class="img-circle" src="' . $this->game->image_square_tiny . '" alt="User Image">
					<span class="username"><a href="' . $this->url_slug .'" target="_blank">' . $this->game->name . '</a></span>
					<span class="description"><span class="label" style="background-color: '. $this->game->platform->color . '; margin-right: 10px;">' . $this->game->platform->name .'</span><i class="fa fa-calendar"></i> ' . $this->game->release_date->format('Y') . '</span>
				</div>';
    }

    /*
    |
    | Get html formatted status in admin panel
    |
    */
    public function getStatusAdmin()
    {
        switch ($this->status) {
            case 0:
                return '<span class="label label-success">Active</span>';
            case 1:
                return '<span class="label label-primary">Sold</span>';
            case 2:
                return '<span class="label label-default">Complete</span>';
        }
    }

    /*
    |
    | Get Name with cover and release year for backend
    |
    */
    public function getPriceAdmin()
    {
        if ($this->sell) {
            return '<h4 style="margin: 0px !important;"><span class="label label-success">' . $this->getPriceFormattedAttribute() .'</span></h4>';
        } else {
            return '<h4 style="margin: 0px !important;"><span class="label label-danger"><i class="fa fa-shopping-basket"></i></span></h4>';
        }
    }

    /*
    |
    | Get Name with cover and release year for backend
    |
    */
    public function getTradeAdmin()
    {
        if ($this->trade == 1) {
            return '<h4 style="margin: 0px !important;"><span class="label label-success"><i class="fa fa-exchange"></i></span></h4>';
        } else {
            return '<h4 style="margin: 0px !important;"><span class="label label-danger"><i class="fa fa-exchange"></i></span></h4>';
        }
    }

    /*
    |
    | Get formatted creation date for backend
    |
    */
    public function getDateAdmin()
    {
        return '<strong>' . $this->created_at->format(Config::get('settings.date_format')) . '</strong><br>' . $this->created_at->format(Config::get('settings.time_format'));
    }
}
