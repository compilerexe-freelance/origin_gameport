<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Backpack\CRUD\CrudTrait;
use ClickNow\Money\Money;


use Config;

class Game extends Model
{
    use CrudTrait, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'games';
    protected $primaryKey = 'id';
    // public $timestamps = false;
    // protected $guarded = ['id'];
    protected $fillable = ['name','description','cover_generator','cover','release_date','publisher','developer','pegi','platform_id','genre_id'];
    // protected $hidden = [];
    protected $dates = ['release_date','deleted_at'];


    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /**
     * Get all of the game's comments.
     */
    public function comments()
    {
        return $this->morphMany('App\Models\Comment', 'commentable');
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function platform()
    {
        return $this->belongsTo('App\Models\Platform');
    }

    public function giantbomb()
    {
        return $this->belongsTo('App\Models\Giantbomb');
    }

    public function metacritic()
    {
        return $this->hasOne('App\Models\Metacritic');
    }

    public function listings()
    {
        return $this->hasMany('App\Models\Listing')->orderBy('price')->where('status', null)->whereHas('user', function ($query) {$query->where('status',1);})->orWhere('status', 0)->whereHas('user', function ($query) {$query->where('status',1);});
    }

    public function listingsCount()
    {
        return $this->hasOne('App\Models\Listing')
            ->selectRaw('game_id, count(*) as aggregate')
            ->groupBy('game_id')->where('status', null)->whereHas('user', function ($query) {$query->where('status',1);})->orWhere('status', 0)->whereHas('user', function ($query) {$query->where('status',1);});
    }

    public function cheapestListing()
    {
        return $this->hasOne('App\Models\Listing')
            ->selectRaw('game_id, min(price) as aggregate')
            ->groupBy('game_id')->where('status', null)->where('sell', 1)->whereHas('user', function ($query) {$query->where('status',1);})->orWhere('status', 0)->where('sell', 1)->whereHas('user', function ($query) {$query->where('status',1);});
    }
    // This is a list of all games that can be trade for the game
    public function tradegames()
    {
        return $this->belongsToMany('App\Models\Listing', 'game_trade')->withPivot('listing_game_id', 'price', 'price_type');
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
    | Save cover to database
    |
    */
    public function setCoverAttribute($value)
    {
        $attribute_name = "cover";
        $disk = "local";
        $destination_path = "public/games";

        // if a base64 was sent, store it in the db
        if (starts_with($value, 'data:image')) {
            // 0. Make the image
          $image = \Image::make($value);
          // 1. Generate a filename.
          $filename = time().'-'.$this->id.'.jpg';
          // 2. Store the image on disk.
          \Storage::disk($disk)->put($destination_path.'/'.$filename, $image->stream());

                  // Delete old image
                  if (!is_null($this->getAttribute('cover'))) {
                      \Storage::disk($disk)->delete('/public/games/' . $this->getAttribute('cover'));
                  }

          // 3. Save the path to the database
          $this->attributes[$attribute_name] = $filename;
          // if string was sent
        } else {
            $this->attributes[$attribute_name] = $value;
        }
    }


    /*
    |
    | Helper Class for count listings
    |
    */
    public function getListingsCountAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (! array_key_exists('listingsCount', $this->relations)) {
            $this->load('listingsCount');
        }

        $related = $this->getRelation('listingsCount');

        // then return the count directly
        return ($related) ? (int) $related->aggregate : 0;
    }

    /*
    |
    | Helper Class for cheapest listings
    |
    */
    public function getCheapestListingAttribute()
    {
        // if relation is not loaded already, let's do it first
        if (! array_key_exists('cheapestListing', $this->relations)) {
            $this->load('cheapestListing');
        }

        $related = $this->getRelation('cheapestListing');

        // format cheapest price
        if ($related) {
            $cheapest_price = money($related->aggregate, Config::get('settings.currency'))->format();
        };

        // then return the price directly
        return ($related) ?  $cheapest_price : 0;
    }

    /*
    |
    | Get Cover Image
    |
    */
    public function getImageCoverAttribute()
    {
        if (!is_null($this->cover)) {
            return asset('images/cover/' . $this->cover);
        } else {
            return null;
        }
    }

    /*
    |
    | Get Carousel Image
    |
    */
    public function getImageCarouselAttribute()
    {
        if (!is_null($this->cover)) {
            return asset('images/carousel/' . $this->cover);
        } else {
            return asset('images/carousel/no_cover.jpg');
        }
    }

    /*
    |
    | Get Square (Tiny) Image
    |
    */
    public function getImageSquareTinyAttribute()
    {
        if (!is_null($this->cover)) {
            return asset('images/square_tiny/' . $this->cover);
        } else {
            return asset('images/square_tiny/no_cover.jpg');
        }
    }

    /*
    |
    | Get Square Image
    |
    */
    public function getImageSquareAttribute()
    {
        if (!is_null($this->cover)) {
            return asset('images/square/' . $this->cover);
        } else {
            return asset('images/square/no_cover.jpg');
        }
    }

    /*
    |
    | Get URL
    |
    */
    public function getUrlSlugAttribute()
    {
        return url('games/' . str_slug($this->name) . '-' . $this->platform->acronym . '-' . $this->id);
    }


    /*
    |--------------------------------------------------------------------------
    | ADMIN FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |
    | Get Image for backend
    |
    */
    public function getImageAdmin()
    {
        if (!is_null($this->cover)) {
            return "<img src='" . asset('uploads/game/square_tiny/' . $this->cover)  . "' height='50' class='img-circle' />";
        } elseif (!is_null($this->giantbomb_id)) {
            return '<img src="http://www.giantbomb.com/api/image/square_avatar/' . $this->giantbomb->image . '" />';
        } else {
            return "<img src='" . asset('uploads/game/square_tiny/no_cover.jpg') . "' height='50' class='img-circle' />";
        }
    }

    /*
    |
    | Get Console label for backend
    |
    */
    public function getConsoleAdmin()
    {
        return '<span class="label" style="background-color: '. $this->platform->color . ';">' . $this->platform->name .'</span>';
    }

    /*
    |
    | Get Name with cover and release year for backend
    |
    */
    public function getNameAdmin()
    {
        return '<div class="user-block">
					<img class="img-circle" src="' . $this->getImageSquareTinyAttribute() . '" alt="User Image">
					<span class="username"><a href="' . $this->getUrlSlugAttribute() .'" target="_blank">' . $this->name . '</a></span>
					<span class="description"><i class="fa fa-calendar"></i> ' . $this->release_date->format('Y') . '</span>
				</div>';
    }

    /*
    |
    | Get Listings count and cheapest listing for backend
    |
    */
    public function getListingsAdmin()
    {
        if ($this->getListingsCountAttribute() > 0) {
            if ($this->getCheapestListingAttribute() == '0') {
                return '<div class="block"><span class="label label-success">' . $this->getListingsCountAttribute() .'</span></div> <span class="text-muted text-xs"><i class="fa fa-exchange"></i> Trade only</span>';
            } else {
                return '<div class="block"><span class="label label-success">' . $this->getListingsCountAttribute() .'</span></div> <span class="text-muted text-xs"><i class="fa fa-shopping-basket"></i> starting from ' . $this->getCheapestListingAttribute() . '</span>';
            }
        } else {
            return '<span class="label label-danger">' . $this->getListingsCountAttribute() .'</span>';
        }
    }
}
