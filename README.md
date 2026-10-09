# WordPress.com Compatibility

**Contributors:** automattic, wpcomvip  
**Tags:** WordPress VIP, WordPress.com, compatibility, migration  
**Requires at least:** 6.4  
**Tested up to:** 7.1  
**Requires PHP:** 7.4  
**Stable tag:** 1.0.1  
**License:** GPL-2.0-or-later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html

Compatibility shims for sites that moved from WordPress.com VIP to the WordPress VIP Platform.

## Description

This plugin provides compatibility for sites that moved from WordPress.com VIP to the WordPress VIP Platform (formerly VIP Go). It was added to sites during their migration to ease the transition, so that themes and plugins written for WordPress.com kept working without changes.

It's a transitional shim rather than a permanent solution, but many sites still rely on it, so it continues to be maintained to keep behaviour the same. Read through this README and the code to decide whether your site still needs it, or whether it can be removed safely (see the [FAQ](#frequently-asked-questions)).

### Plugin loading

To minimise the number of moving parts, plugins were migrated with exactly the same version as on WordPress.com, with the exception of `es-wp-query`, because versions prior to `0.2.0` are not compatible with the VIP Platform.

`wpcom_vip_load_plugin()` has a couple of important differences on the VIP Platform:

1. It does not accept a `$version` argument.
2. Plugins can only be loaded from `plugins/` and its subfolders.

To address those differences, all loaded plugins were moved into `plugins/` during the code migration. Where a `wpcom_vip_load_plugin()` call used the `$version` argument, it was replaced with `wpcom_vip_legacy_load_plugin()`, a thin wrapper around `wpcom_vip_load_plugin()` that loads the correct version.

⚠️ If you see calls to `wpcom_vip_legacy_load_plugin()` in your codebase (themes or your custom plugins), removing this plugin will break your site. ⚠️

Unversioned:

~~~php
// WordPress.com loads from `vip/plugins/my-plugin`.
wpcom_vip_load_plugin( 'my-plugin' );

// The VIP Platform loads from `plugins/`.
wpcom_vip_load_plugin( 'my-plugin' );
~~~

Versioned:

~~~php
// WordPress.com loads from `vip/plugins/my-plugin-2.0`.
wpcom_vip_load_plugin( 'my-plugin', 'plugins', '2.0' );

// The VIP Platform loads from `plugins/my-plugin-2.0`, either:
// 1. with the compatibility wrapper:
wpcom_vip_legacy_load_plugin( 'my-plugin', 'plugins', '2.0' );
// 2. or with the VIP Platform's wpcom_vip_load_plugin():
wpcom_vip_load_plugin( 'my-plugin-2.0/my-plugin.php' );
~~~

The plugin also filters `plugins_url()` so that it returns working URLs for "plugins" that live inside a theme, such as `themes/my-theme/plugins/my-plugin/`.

### Deprecated functions

The following functions are deprecated on the VIP Platform. They're kept as shims so that themes and plugins calling them don't cause fatal errors:

- `wpcom_vip_load_wp_rest_api()` loaded the built-in WP REST API endpoints on WordPress.com VIP. It isn't needed on the VIP Platform or in core WordPress, and can be safely removed.
- `wpcom_vip_enable_https_canonical()`: WordPress.com forced HTTP to be the canonical version of URLs by default. It isn't needed on the VIP Platform.
- `require_lib( $slug )` is an internal WordPress.com function that loads shared WordPress.com libraries. Copy any libraries you need into your repository; if a library exists at `client-mu-plugins/lib/<slug>/<slug>.php`, this shim loads it.
- `is_wpcom_vip()` checked whether code was running on WordPress.com VIP rather than locally. It isn't needed on the VIP Platform.
- `vip_goog_stats()`, `wpcom_vip_remove_mp6_styles()`, `wpcom_vip_remove_bbpress2_staff_css()`, `wpcom_vip_enabled_cap_in_oembed()` and `wpcom_vip_protected_embed_to_original()` do nothing (the last returns its content unchanged).

### Shortcodes

- `protected-iframe` displays "protected embeds" imported from WordPress.com. It doesn't allow sites to create new embeds, nor does it enable the "protected" domain. Other plugins can do that, such as [humanmade/protected-embeds](https://github.com/humanmade/protected-embeds).

### WP-CLI commands

- `wp wpcom-compat import-protected-embeds <file>` imports "protected embeds" from a CSV file into the `protected_embeds` database table.

### Bundled plugins

- [Writing Helper](https://github.com/Automattic/writing-helper) "helps you write your posts". This WordPress.com feature lets posts be copied, and feedback be requested on drafts. Disable it by calling `add_filter( 'wpcom_compat_enable_writing_helper', '__return_false' );` before this plugin loads.
- MediaRSS adds Media RSS to RSS feeds. It's expected to move to Jetpack in time ([Jetpack issue #11062](https://github.com/Automattic/jetpack/issues/11062)).

### Other behaviour

- Plain-text URLs in post content and excerpts are converted to links on display, with `wpcom_make_content_clickable()`, the WordPress.com implementation of [`make_clickable()`](https://developer.wordpress.org/reference/functions/make_clickable/). It only calls `make_clickable()` where needed, as that function is expensive.
- Jetpack SSO is enabled, and matches users by email address, so that accounts imported from WordPress.com stay linked to the same WordPress.com account.
- The WordPress.com sitemaps (`/sitemap.xml` and `/news-sitemap.xml`) are served, and listed in `robots.txt`. Define `WPCOM_SKIP_DEFAULT_SITEMAP` or `WPCOM_SKIP_DEFAULT_NEWS_SITEMAP` as `true` to turn them off.

## Installation

The plugin was added to sites as a client mu-plugin, in `client-mu-plugins/vip-go-wpcom-compat`, during their migration from WordPress.com. It's only added once; after that, it's up to you whether to keep it updated or remove it.

The `master` branch holds released code. Development happens on `develop`, so always update from `master`.

### Updating a submodule

1. Go to `client-mu-plugins/vip-go-wpcom-compat`.
2. Run `git pull origin master`.
3. Go back to the root of your repository, and commit the result.
4. Push.

### Updating a subtree

From the root of your repository:

1. Run `git subtree pull --prefix client-mu-plugins/vip-go-wpcom-compat https://github.com/Automattic/vip-go-wpcom-compat.git master --squash`.
2. Optionally, edit the commit message.
3. Push.

## Frequently Asked Questions

### Can I remove this plugin?

Possibly. Check that your code doesn't use anything listed in the [Description](#description), in particular `wpcom_vip_legacy_load_plugin()`, the deprecated functions, the `protected-iframe` shortcode, and the WordPress.com sitemaps. Test the removal on a non-production environment first.

### How do I stop plain-text URLs being turned into links?

~~~php
remove_filter( 'the_content', 'wpcom_make_content_clickable', 120 );
remove_filter( 'the_excerpt', 'wpcom_make_content_clickable', 120 );
~~~

### How do I turn off Writing Helper?

Add `add_filter( 'wpcom_compat_enable_writing_helper', '__return_false' );` to code that runs before this plugin loads.

## Development

See [CONTRIBUTING.md](CONTRIBUTING.md) for setting up a local environment and running the checks.

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md) before opening a pull request against `develop`.

## Support

WordPress VIP customers should [open a support ticket](https://docs.wpvip.com/get-help/) with VIP Support. For bugs, please [open an issue](https://github.com/Automattic/vip-go-wpcom-compat/issues).

## Credits

Built and maintained by [WordPress VIP](https://wpvip.com/) at [Automattic](https://automattic.com/), with contributions from Josh Betz, Derrick Tennant, Rinat Khaziev, Shantanu, Rebecca Hum, Caleb Burks, Brett Shumaker, Linnea Huxford, Mohammad Jangda, Kailey Lampert, Gary Jones, and [others](https://github.com/Automattic/vip-go-wpcom-compat/graphs/contributors).

## License

GPL-2.0-or-later. See [LICENSE](LICENSE) for the full text.
