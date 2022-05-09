# SilverStripe Lazy Forms
Convert normal SilverStripe form into Lazy loading forms and take maximum advantage of the SilverStripe Static Publishing module.
In the static publishing module pages with forms are not allowed.
The module show the exact same form in disabled state while loading. This form can be used as a skeleton.
With CSS you can change it to greyed out, see example below.

The form is fully functional after loading.

### Installation
```
composer require xddesigners/silverstripe-lazyforms

// optional if not already present
composer require "silverstripe/staticpublishqueue
```

### Usage
Replace your $Form calls in your .ss templates.
```php
// in SS template
// old situation
$Form
// changed: load lazy form
$LazyForm

// in case Form method has different name
// before
$ProductReviewForm
// changed: load lazy form
$LazyForm('ProductReviewForm')
```

### Styling the skeleton form
The form is loaded without SecurityID in a disabled state in the static published page.
You can grey out all the fields with you own css. This is visible while the real form is loading.

```scss
/* SCSS Example */
.lazyform--loading {
   ul.optionset label,
  .text label,
  .textarea label,
  .text input,
  .textarea textarea,
  select.dropdown,
  .button{
    background-color: #999 !important;
    color: #999 !important;
    opacity: .5 !important;
  }
}
```
