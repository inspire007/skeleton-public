<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Alert extends Component
{
	protected $theme;
	
	public $type;
	
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($type)
    {
        $this->theme = config('site.theme');
		$this->type = $type;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view("themes.".$this->theme.".components.alert");
    }
}
