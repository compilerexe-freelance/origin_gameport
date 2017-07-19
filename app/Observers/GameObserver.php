<?php
namespace App\Observers;

use App\Models\Game;
use App\Models\Listing;
use App\Models\User;
use App\Notifications\ListingDeleted;
use Carbon\Carbon;

class GameObserver
{

    /**
     * Listen to the Game deleting event.
     *
     * @param  Listing  $listing
     * @return void
     */
    public function deleting(Game $game)
    {

        // Get all listing
        $listings = Listing::where('game_id', $game->id)->get();

        foreach ($listings as $listing) {
            // Check status of listing
            if ($listing->status == 0) {
                // Notifications to all open offer user and delete all offers
                foreach ($listing->offers as $offer) {
                    if ($offer->status == 0 && $offer->declined == 0) {
                        $offer_user = User::find($offer->user_id);
                        $offer_user->notify(new ListingDeleted($offer));
                        $offer->declined = 1;
                        $offer->decline_note = 'listings.general.deleted_game';
                        $offer->closed_at = new Carbon;
                        $offer->save();
                    }
                }
                $listing->delete();
            }
        }

        return true;
    }
}
