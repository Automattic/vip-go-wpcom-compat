# WordPress.com Compatibility

Compatibility shims for sites that moved from WordPress.com VIP to the WordPress VIP Platform. Loaded by sites as a client mu-plugin (`client-mu-plugins/vip-go-wpcom-compat`), never activated as a normal plugin.

## Commands

```bash
composer lint                # PHP syntax
composer cs                  # PHPCS (CI fails on violations)
npx wp-env start             # Local environment
composer test:integration    # Integration tests (needs wp-env)
composer test:integration-ms # Multisite integration tests (needs wp-env)
```

## Conventions

See `CONTRIBUTING.md` for the workflow and standards. In short: branch from `develop` and open pull requests against it; `master` is the release branch. Commits must be signed. Commit messages and pull request descriptions should explain why a change was made, not just what changed.

## Pitfalls

- **Backward compatibility is the product.** Sites have relied on these exact global function names, hooks, priorities and outputs for years. Don't rename, re-prefix, namespace or "tidy" them, even when PHPCS complains; add a `phpcs:ignore` with the sniff and a reason instead.
- Keep coding standards fixes separate from behaviour changes.
- Sites update by pulling `master` into a submodule or subtree, so `master` must stay the name of the release branch.
- The plugin depends on VIP Platform code (`wpcom_vip_load_plugin()`, `WPCOM_VIP_CLI_Command`, `WPCOM_VIP_CLIENT_MU_PLUGIN_DIR`). `tests/platform-stubs.php` stands in for it, and wp-env maps it in as an mu-plugin.
- `plugins/writing-helper/` is a subtree of Automattic/writing-helper and is excluded from PHPCS. Fix it upstream.
- `wpcom-sitemap.php` is ported from WordPress.com and still contains WordPress.com-only code paths (`queue_pings_job()`, `$current_blog`).
