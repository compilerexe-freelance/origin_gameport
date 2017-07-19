<?php
namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use App\Models\MenuItem;
use App\Models\Language;

class FooterComposer
{
    /**
     * The user repository implementation.
     *
     * @var UserRepository
     */
    protected $menu;
    protected $languages;

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(MenuItem $menu, Language $languages)
    {
        // Dependencies automatically resolved by service container...
        $this->menu = $menu;
        $this->languages = $languages;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with(['menu' => $this->menu->with('page')->orderBy('lft')->get(), 'languages' => config('settings.locale_selector') ? $this->languages->where('active',1)->get() : null]);
    }
}
