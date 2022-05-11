<?php

namespace XD\LazyForms\Extensions;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Control\Controller;
use SilverStripe\ORM\DataExtension;

/**
 * Class LazyFormsBaseElementExtension
 * @package XD\LazyForms\Extensions
 * @property BaseElement|LazyFormsBaseElementExtension $owner
 */
class LazyFormsBaseElementExtension extends DataExtension
{

    private static $casting = [
        'LazyForm' => 'HTMLText'
    ];

    public function LazyForm($name = 'Form')
    {
        /** @var Controller|LazyFormsControllerExtension $controller */
        $controller = Controller::curr();
        if ($controller->hasMethod('LazyForm')) {
            return $controller->LazyForm($name);
        }
        return false;
    }

    public function LazyInclude($name)
    {
        /** @var Controller|LazyFormsControllerExtension $controller */
        $controller = Controller::curr();
        if ($controller->hasMethod('LazyInclude')) {
            return $controller->LazyInclude($name, $this->owner);
        }
        return false;
    }

}
