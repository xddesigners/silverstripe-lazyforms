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
        'loadlazyitem',
    ];

    private static $casting = [
        'LazyForm' => 'HTMLText',
        'LazyInclude' => 'HTMLText',
    ];

    public function includeScript()
    {
        //language=JS
        $script = '
        let lazyload=function(name,id,type){
            let home=window.location.pathname==="/"?"home":"";
            let url=window.location.href.split("?")[0]+home+"/loadlazyitem/"+type+"/"+name;
            url = url.replace(/([^:]\/)\/+/g, "$1");
            let xmlhttp=new XMLHttpRequest();
            xmlhttp.onreadystatechange = function(){
                if (xmlhttp.readyState==4 && xmlhttp.status==200){
                    let target=document.querySelector("[data-lazyid="+id+"]");
                    target.innerHTML=xmlhttp.responseText;
                    target.classList.add("lazyloaded");
                    document.dispatchEvent(new CustomEvent("onLazyformLoaded",{detail:{lazytype:type,lazyid:id,target:target,name:name}}));
                }
            };
            xmlhttp.open("GET",url,true);
            xmlhttp.send();
        };
        let lazyinclude=function(name,target){
            lazyload(name,target,"include");        
        };
        let lazyform=function(name,target){
            lazyload(name,target,"form");
        };
        let lis=document.getElementsByClassName("lazyinclude");
        for(let i=0;i<lis.length;i++) {
           let id=lis[i].dataset.lazyid="include-"+i;
           lazyload(lis[i].dataset.lazyinclude,id,"include");
        }
        let lfs=document.getElementsByClassName("lazyform");
        for(let i=0;i<lfs.length;i++) {
           let id=lfs[i].dataset.lazyid="form-"+i;
           lazyload(lfs[i].dataset.lazyform,id,"form");
        }';

        $script = preg_replace(["/\s+\n/", "/\n\s+/", "/ +/"], ["", " ", " "], $script);
        Requirements::customScript(<<<JS
            $script
        JS
            , 'lazyforms');
    }

    public function LoadLazyItem($request)
    {
        $type = $request->param('ID');
        $name = $request->param('OtherID');
        switch ($type) {
            case 'include':
                return $this->owner->LoadLazyInclude($name);
            case 'form':
            default:
                return $this->owner->LoadLazyForm($name);
        }
    }

    public function LazyForm($name = 'Form', $preload = true)
    {
        $this->includeScript();
        if ($preload === 'true') $preload = true;
        if ($preload === 'false') $preload = false;

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
        if ($preload === true) {
            $content = $form->forTemplate();
        } elseif (is_string($preload) && $preload != 'false') {
            $content = $this->owner->LazyIncludeByName($preload);
        } else {
            $content = '...';
        }
        return '<div class="lazyform" data-lazyform="' . $name . '">' . $content . '</div>';
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

    public function LoadLazyForm($formName)
    {
        if ($form = $this->owner->LazyFormByName($formName)) {
            return $form->forTemplate();
        }
        return $this->owner->httpError(404);
    }

    public function LazyInclude($name, $preload = true, $scope = null)
    {
        $this->includeScript();
        if ($preload === 'true') $preload = true;
        if ($preload === 'false') $preload = false;
        if ($preload === true) {
            // preload include, default
            $include = $this->owner->LazyIncludeByName($name, $scope);
            $content = $include->forTemplate();
        } elseif (is_string($preload) && $preload != 'false') {
            // preload faster loading simple template
            $content = $this->owner->LazyIncludeByName($preload);
        } else {
            // empty
            $content = '...';
        }
        $name = str_replace('\\', '-', $name);
        return '<div class="lazyinclude" data-lazyinclude="' . $name . '">' . $content . '</div>';
    }

    public function LazyIncludeByName($name, $scope = null)
    {
        if (!$scope) $scope = $this->owner;
        $name = str_replace('-', '\\', $name);
        return $scope->renderWith($name);
    }

    public function LoadLazyInclude($includeName)
    {
        // how to handle scope here? for now use this->owner as scope
        $scope = $this->owner;
        if ($include = $this->owner->LazyIncludeByName($includeName, $scope)) {
            return $include->forTemplate();
        }
        return $this->owner->httpError(404);
    }

}
