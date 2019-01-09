# VIP Go / WordPress.com Compatibility

This plugin provides compatibility for sites that are moving from WordPress.com to VIP Go.

## Deprecated Functions

The following functions are deprecated on VIP Go and are added as shims to keep themes and plugins from throwing fatal errors:

* `wpcom_vip_load_wp_rest_api()` - Loads the built-in WP REST API endpoints in WordPress.com VIP context.  This function is not needed on VIP Go, or core WordPress to load the REST API and can be safely removed.
* `wpcom_vip_enable_https_canonical()` - By default HTTP is forced to be the canonical version of URLs on WordPress.com. This function is not needed on VIP Go.

## Shortcodes

The following shortcodes are either ported over from WordPress.com or are created with minimal backwards compatability:

* `protected-iframe` - Displays "protected embeds" imported from WordPress.com.  This does not allow sites to create new embeds, nor does it enable the "protected" domain.  Other plugins can be used for this, such as [humanmade/protected-embeds](https://github.com/humanmade/protected-embeds).

## WP-CLI Commands

The following custom WP-CLI commands exist in this plugin:

* `wpcom-compat import-protected-embeds` - Imports "protected embeds" from a CSV file into the database table `protected_embeds`.

## Filters

### `wpcom_make_content_clickable()` - Convert plain text URLs in `post_content` to links on display.

* `wpcom_make_content_clickable()` is the WordPress.com implementation of [`make_clickable()`](https://developer.wordpress.org/reference/functions/make_clickable/). It uses `make_clickable()` conditionally as that is an expensive function. This conversion of plain text URLs to HTML links is turned on by default on WordPress.com and VIP Go.

* To turn off this behavior please use the following piece of code:
```
remove_filter( 'the_content', 'wpcom_make_content_clickable', 120 );
remove_filter( 'the_excerpt', 'wpcom_make_content_clickable', 120 );
```
