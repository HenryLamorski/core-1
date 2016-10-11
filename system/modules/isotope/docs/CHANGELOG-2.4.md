Isotope eCommerce Changelog
===========================


Version 2.4.0-beta1 (2016-07-06)
--------------------------------

### New

- Isotope now works with jQuery, MooTools or Vanilla JS
- Added new report for "members vs. guest" orders (#1577)
- Allow to set a default variant for each product (#1565)
- Skip shipping and payment methods if only one option is available (#1217)
- Added the responsive images feature (#1423)
- Added option to edit products that were added to the cart
- Added support for SQL condition, new/old filter and sorting in related product list (#1518)
- Added support for ajax file uploads with terminal42/contao-fineuploader
- The FileTree attribute is now sortable (#1561)
- Added CSS class field to product type (#1532)
- Can now perform reports on order, payment or shipping date (#1620)
- Added config setting to define an order details module for backend view (#1578)
- Validate the price attribute in product type against duplication (#1542)
- Added pagination information to product list template (#1650)
- Enable module tables in Isotope setup after all modules are loaded (#1624)
- Added hook triggered when address data is updated (#1473)
- Added hook to modify fields in checkout process


### Improved

- Product images are now shown in the sort-on-page backend view (#1249)
- Meta data cannot be entered per product variant
- Collection dates (locked, paid, shipped) are now stored as NULL if empty
- Sales report now shows the number of items in totals (#1577)
- Allow custom form field as recipient tokens in notification center
- Do not limit product categories selection by active page filter (#1648)
- Allow backend filter for text, radio and checkbox attributes (#1644)
- Datatrans payment method now supports SHA-256 algorithm (#1640)


### Fixed

- "Description" column in iso_collection_invoice template was not translated (#1652)
- Product image import did not work with subfolders (#1666)
