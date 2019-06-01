## Views Selection Rendered Widget
Views-enabled entity reference widget that actually renders the chosen fields.

## Entity reference widgets with views-enabled displays
Did you notice views-enabled entity reference widgets don't display the fields selected in the entity reference views display?

This feature used to work in Views 3 for Drupal 7. It is considered [a core regression issue](https://www.drupal.org/project/drupal/issues/2174633 "https://www.drupal.org/project/drupal/issues/2174633") and this module backports that patch.

## Installation Instructions
Clone this module under modules/custom.  
Enable it and a new widget option will show up when editing entity reference fields.  
It should read "Views: Filter by an entity reference view and display rendered fields".  
Chose it then edit the entity reference view display to pick the fields you want to display in the widget options. It should look just like the view preview.

## Authors
* Capi Etheriel 

## License
GNU GPLv2
    
