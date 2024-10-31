=== PG Simple Affiliate Shop ===
Contributors: peoplesgeek
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2H2HYH6LVFM4Q
Tags: affiliate store, affiliate shop, affiliate product management, simple affiliate page, affiliate marketing
Requires at least: 3.4
Tested up to: 5.2.1
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Create a simple and attractive store for your affiliate products and banner advertisements. Easily manage the products you promote.
 
== Description ==

PG Simple Affiliate Shop creates a simple store on your site for managing both products with customised reviews and simple banner advertisements.

As an affiliate marketer you know that keeping your product pages up to date for customers and managing those long affiliate links can be time consuming. Make the process easier by letting the Simple Affiliate Shop handle the formatting and uploading for you. 
= Features =
* Attach testimonials, descriptions and images to the products you are promoting on your site
* Use the same product information for a banner as well as a product or an inline image
* Drag and drop into any order you like
* Categorise products and display different types on different pages
* Easy upload images using the standard WordPress media uploader
* Keeps all uploaded images separate from other WordPress images for easy maintenance
* Easily use SEO tools like WordPress SEO by Yoast to improve visibility on search engines
* Simple to customise text for buy now buttons and whether to display buttons in banners
* Use shortcodes from other plugins in most fields

If you can assist with providing a translation for this plugin then please contact me via the [Peoples Geek website](http://www.PeoplesGeek.com/contact)

_This plugin is in no way associated with affiliate.com, clickbank, amazon, webgains, commission junction, Rakuten linkshare or any affiliate company. It simply allows you to format the links from any source in a way that is easy to manage and present to your website visitors_


== Installation ==

1. Upload all the files into your wp-plugins directory
1. Activate the plugin at the plugin administration page
1. Add the [pg_sas_shop] shortcodes onto the page where you would like your shop to appear
1. Add Products and Banners to your shop from the 'Shop' menu in the admin area of your site
1. Add the [pg_sas_banner] shortcodes to any widget area to show a list of banners
1. Visit the settings page and customise text and appearance for shop, banner and inline elements

== Frequently Asked Questions ==

= Where is my shopping cart, where do I add PayPal =
If you are asking this question you may be after an e-commerce plugin rather than an affiliate marketing plugin. You may find this article ["What is Affiliate Marketing?"](http://www.peoplesgeek.com/2013/01/what-is-affiliate-marketing/) useful.
This plugin is for promoting and marketing other peoples products in return for a commission rather then selling your own product directly.

= All my products are showing as banners =
When you create a product for your shop you must choose a category. The default category is 'Banner Advertisement'.
To show products in your store pages choose the category of 'Shop-Page'

= Individual Products are 'not found' =
You may need to go into your permalink preferences and simply click the save changes to refresh your permalinks after adding the shop. This will only need to be done once.

= Can I have the same product as a Banner and a Product =
Yes! You can choose multiple categories for each of the product advertisements you create. By default 'Shop Products' and all its children are shown in the shop page. You can fine tune these using categories and other parameters in the shop shortcode.

= What options are available with the shortcode =
You can select products and banners by category and ID and decide if you want to show child categories or not. For examples see the help documentation in the settings menu

= When I upload an image the file name stays blank =
If you do not select 'Link URL' as 'File URL' in the media uploader (just above the button where you click 'Insert into Post') then the link is not passed back. Usually the default is 'file' but some themes and WordPress installations change this default and you will have to select it each time you upload an image, or change the default back to file

= The shortcodes do not work =
The standard shortcode depends on the slug 'shop-product' and 'banner-advert'. If you remove or rename these then you will have to specify the category in the shortcode. 
Make sure that you use the slug for the category and not the name.

= How can I style the single page different to my default theme page =
A full page can be a good way to show more detail for your products. 
This is already supported and the title of each product in the shop will link to a full page already. 
 
If you want to customise the look of the full page then you create a file called 'single-pgeek_sas.php' an example is provided in the inc directory of the plugin for TwentyTwelve theme.
You then customise this page as part of your theme - so you don't have to hack any other template files.
PG Simple Affiliate Shop follows the rules of the WordPress page Template Hierarchy [See the Codex page here](http://codex.wordpress.org/Template_Hierarchy#Single_Post_display )

== Screenshots ==

1. The 'Edit/New product' page
2. The 'All products' page
3. The shortcut in a standard page to create the shop
4. The shortcut in a text widget to create the banner
5. The resulting shop
6. The new shortcode helper/button in the editor

== Changelog ==

= Version 1.5 =
* Added support for shortcodes in the link field as suggested by @stiglv59dk. You can now add a shortcode in the link field
* Fix: The helper now works with 4.8 and the new TinyMCE thanks to Alex for his assistance.
* Bumped after testing against 5.2.1 (use classic block for PG Simple Affiliate shop if using Gutenberg blocks)

= Version 1.4 =
* Added the ability to set a featured image for each shop product. This is to support this feature for some themes that use featured image. Note that it depends on your theme as to whether this option is used.

= Version 1.3.4 =
* Fix: The helper for inserting short codes into pages and posts now supports the new and old tinyMCE editor

= Version 1.3.3 =
* Provided a sample theme file that allows you to customise the look of a single product page (without this the single product page will use your theme default page)
* Made the image in the shop optionally clickable as well as the buy now button and the banner - new option in settings
* Fixed the CSS in the admin pannel for the shortcode helper box to match the new 3.8 admin look and feel

= Version 1.3.2 =
* Added a helper button for inserting short codes into pages and posts so you don't have to remember any codes. Look for the new button in the editor toolbar when creating or editing a page or post. Click on this button and a popup will appear to help you select if you want a shop, product or inline image. The correct shortcode will be automatically added to your page or post at the current cursor position.

= Version 1.3.1 =
* Fixed a bug where the bottom pagination link for a page 1 went to the last product on the page rather than the first page of products (props Roger for bringing it to my attention). This only effects you if you are using pagination displayed at the bottom of a page of products.

= Version 1.3.0 =
* Added pagination so you can set how many products show per page. This can be overwritten at the shortcode level
* Changed the title of each product to a link to the product single page to allow customisation of pages for SEO if desired
* Added a filter so you can override the pagination if required for your theme
* Added more helpful comments on the 'image not local' warning
* Added support for shortcodes in the description, cost, testimonial and customer fields
* Fix for help tab not showing in settings if you are using another plugin that customises menus
* Added support for localisation and translation - if you can assist with translating the plugin into your language then please get in touch

= Version 1.2.0 =
* Fixed problem where you may have to refresh permalinks on installation to make individual product pages display
* Removed forcing the color of the shop h2 header so it will now use your theme color (retained the dotted separator)
* Added additional CSS elements to shop image to allow easier customisation (pg-sas-image)
* Added a new shortcode to allow an image inline to a post or page (see settings menu help tab)
* Changed CSS class 'orange' to 'a.pg-sas-orange' to avoid clashes
* Added full set of color buttons from  [Web Designer Wall](http://webdesignerwall.com/tutorials/css3-gradient-buttons)
* Added an optional 'hover image' for the banner that can be used with or in place of the banner buy now button

    **Note:** If you have moved pg-sas.css to your theme then please merge the changes to your version and also remember to copy the clickhere.png image to your theme (or replace the image to one of your choice)

= Version 1.1.0 =
* Allow HTML markup in the description and testimonial fields
* Make a new lines in the description or testimonial field show as an HTML break
* Check in the theme folder for the stylesheet first to allow for easier customisation by users
* Changed markup of testimonial quotations to allow easier overriding to match user's theme

= Version 1.0.0 =
* Initial Release

== Upgrade Notice ==

= Version 1.3.2 =
* Added a helper for inserting short codes into pages and posts - look for the shop icon in the editor toolbar and you never have to look up codes again!

= Version 1.3.1 =
* Small bug fix for pagination that only effects you if you are using pagination displayed at the bottom of a page of products.

= Version 1.3.0 =
* Added pagination so you can set how many products show per page. This can be overwritten at the shortcode level - if your shop pages are getting too long then upgrade to this version

= 1.2.0 =
If you would like a set of button colors, new inline image and hover over effect in the banner instead of the buy now button then upgrade to this release!
**Note:** If you have moved pg-sas.css to your theme then please merge the changes to your version and also remember to copy the clickhere.png image to your theme (or replace the image to one of your choice)

= 1.1.0 =
Some updates to allow you to change the formatting of the shop and support for HTML in the description

= 1.0.0 =
This initial version gets you started with your simple affiliate shop

