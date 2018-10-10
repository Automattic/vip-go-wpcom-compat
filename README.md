# VIP Go / WordPress.com Compatibility

This plugin provides compatibility for sites that are moving from WordPress.com to VIP Go.

## Deprecated Functions

The following functions are deprecated on VIP Go and are added as shims to keep themes and plugins from throwing fatal errors:

* `wpcom_vip_load_wp_rest_api()` - Loads the built-in WP REST API endpoints in WordPress.com VIP context.  This function is not needed on VIP Go, or core WordPress to load the REST API and can be safely removed.

## Shortcodes

The following shortcodes are either ported over from WordPress.com or are created with minimal backwards compatability:

* `protected-iframe` - Displays "protected embeds" imported from WordPress.com.  This does not allow sites to create new embeds, nor does it enable the "protected" domain.  Other plugins can be used for this, such as [humanmade/protected-embeds](https://github.com/humanmade/protected-embeds).

## WP-CLI Commands

The following custom WP-CLI commands exist in this plugin:

* `wpcom-compat import-protected-embeds` - Imports "protected embeds" from a CSV file into the database table `protected_embeds`.
