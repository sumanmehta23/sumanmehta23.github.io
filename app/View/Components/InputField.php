<?php

namespace App\View\Components;

use Illuminate\View\Component;

class InputField extends Component
{
    public $type;
    public $name;
    public $placeholder;
    public $leftIcon;
    public $rightIcon;
    public $rightButton;

    public function __construct(
        $type = 'text',
        $name = '',
        $placeholder = '',
        $leftIcon = null,
        $rightIcon = null,
        $rightButton = null
    ) {
        $this->type = $type;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->leftIcon = $leftIcon;
        $this->rightIcon = $rightIcon;
        $this->rightButton = $rightButton;
    }

    public function render()
    {
        return view('components.input-field');
    }
}
