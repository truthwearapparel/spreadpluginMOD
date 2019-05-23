# spreadpluginMOD
This is a modified version of the WP-Spreadplugin (https://wordpress.org/plugins/wp-spreadplugin/) created by Thimo Grauerholz. 

It's a subclass so that I could customize the layout of the product pages and incorporate other plugins.

You can see it in action at https://truthwearapparel.com/shop

# Notes
This mod is very specific and was not designed to be for general use and will probably not work on your site.
This may be more useful as a reference on how to customize the layout of WP-Spreadplugin.

# Installation
1. Create a separate directory in the plugins directory of your wordpress installation.
2. Upload spreadpluginMOD.php into it
3. in the require_once, ensure that it is pointing to the right directory of WP-Spreadplugin
4. In the original spreadplugin.php file, change all private functions and variables to protected.

# To Use
1. Just use the same shortcodes as the original WP-Spreadplugin
