# SilverStripe Lazy Forms for Static Publisher
Convert normal SilverStripe form into Lazy loading forms and take maximum advantage of the SilverStripe Static Publishing module.
The module show the exact same form in disabled state on load. This can be used as a skeleton.
With CSS you van change it to greyed out.

### Installation
```
composer require xddesigners/silverstripe-qr-code-generator
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
/* SCSS Foundation example */
.lazyform--loading {
  ul.optionset label,
  .text label,
  .textarea label,
  .text input,
  .textarea textarea,
  select.dropdown,
  .button{
    background-color: $medium-gray !important;
    color: $medium-gray !important;
    opacity: .5 !important;
  }
}
```