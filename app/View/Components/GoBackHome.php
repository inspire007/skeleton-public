<?php

namespace App\View\Components;

use Illuminate\View\Component;

class GoBackHome extends Component
{
    protected $theme;
	
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->theme = config('site.theme');
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
		return view("themes.".$this->theme.".components.go-back-home");
	}
}
