<?php

namespace App\Http\Controllers;
use App\Models\Listing;
use App\Models\Game;

use Illuminate\Http\Request;

class SeoController extends Controller
{
    /**
     * Sitemap index
     *
     * @return view
     */
    public function sitemapIndex()
    {
        $listing = Listing::orderBy('updated_at', 'desc')->first();

        return response()->view('seo.sitemap.index', [
            'listing' => $listing,
        ])->header('Content-Type', 'text/xml');
    }

    /**
     * Sitemap for all listings
     *
     * @return view
     */
    public function sitemapListings()
    {
        $listings = Listing::all();

        return response()->view('seo.sitemap.listings', [
            'listings' => $listings,
        ])->header('Content-Type', 'text/xml');
    }

    /**
     * Sitemap for all games
     *
     * @return view
     */
    public function sitemapGames()
    {
        $games = Game::all();

        return response()->view('seo.sitemap.games', [
            'games' => $games,
        ])->header('Content-Type', 'text/xml');
    }

}
