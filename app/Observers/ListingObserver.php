<?php
namespace App\Observers;

use App\Models\Listing;
use App\Models\User;
use App\Notifications\ListingDeleted;
use Carbon\Carbon;

class ListingObserver
{

    /**
     * Listen to the Listing deleting event.
     *
     * @param  Listing  $listing
     * @return void
     */
    public function deleting(Listing $listing)
    {
        // Check status of listing
        if ($listing->status >= 1) {
            return abort('404');
        }

        // Notifications to all open offer user and delete all offers
        foreach ($listing->offers as $offer) {
            if ($offer->status == 0 && $offer->declined == 0) {
                $offer_user = User::find($offer->user_id);
                $offer_user->notify(new ListingDeleted($offer));
                $offer->declined = 1;
                $offer->decline_note = 'listings.general.deleted';
                $offer->closed_at = new Carbon;
                $offer->save();
            }
        }

        return true;
    }
}
