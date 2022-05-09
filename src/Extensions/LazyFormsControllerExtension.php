<?php

namespace XD\LazyForms\Extensions;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\FormField;
use SilverStripe\View\Requirements;

/**
 * Class LazyFormsPageControllerExtension
 * @package XD\LazyForms\Extensions
 * @property \PageController|LazyFormsControllerExtension $owner
 */
class LazyFormsControllerExtension extends Extension
{

    private static $allowed_actions = [
        'loadlazyform'
    ];

    /**
     * @var string[]
     * Only these methods are allowed can be configured via YML
     */
    private static $allowed_forms = [
        'Form',
        'ProductReviewForm'
    ];

    private static $casting = [
        'LazyForm' => 'HTMLText'
    ];

    public function includeScript()
    {
        $script = 'let lazyform=function(name,target){
                let url=window.location.href.split("?")[0]+"/loadlazyform/"+name;
                let xmlhttp=new XMLHttpRequest();
                xmlhttp.onreadystatechange = function(){
                    if (xmlhttp.readyState==4 && xmlhttp.status==200){
                        target.innerHTML=xmlhttp.responseText;
                        document.dispatchEvent(new CustomEvent("onLazyformLoaded",{target:target}));
                    }
                };
                xmlhttp.open("GET",url,true);
                xmlhttp.send();
            };
            let ts=document.getElementsByClassName("lazyform");
            for(let i=0;i<ts.length;i++) {
               lazyform(ts[i].dataset.lazyform,ts[i]);
            }';
        $script = preg_replace(["/\s+\n/", "/\n\s+/", "/ +/"], ["", " ", " "], $script);
        Requirements::customScript(<<<JS
            $script
        JS
        ,'lazyforms');
    }

    public function LazyForm($name = 'Form')
    {
        $this->includeScript();

        // create greyed out form skeleton
        $form = $this->LazyFormByName($name);
        $form->addExtraClass('lazyform--loading');
        $form->disableSecurityToken();
        $fields = $form->Fields();
        foreach ($fields as $field) {
            /** @var FormField $field */
            $field->setDisabled(true);
        }
        $actions = $form->Actions();
        foreach ($actions as $action) {
            /** @var FormAction $action */
            $action->setDisabled(true);
        }
        return '<div class="lazyform" data-lazyform="' . $name . '">' . $form->forTemplate() . '</div>';
    }

    /**
     * @param $formName
     * @return Form
     */
    public function LazyFormByName($formName)
    {
        $allowed_actions = $this->owner->config()->get('allowed_actions');
        if (in_array($formName, $allowed_actions)) {
//        if( Director::is_ajax() ) {
            if ($this->owner->hasMethod($formName)) {
                return $this->owner->$formName();
            }
//        }
        }
        return null;
    }

    public function LoadLazyForm($request)
    {
        $formName = $request->param('ID');
        if ($form = $this->owner->LazyFormByName($formName)) {
            return $form->forTemplate();
        }
        return $this->owner->httpError(404);
    }

}
