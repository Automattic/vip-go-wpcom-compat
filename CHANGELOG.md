# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-10-09

The first versioned release. Its runtime code is the same as `master` before this release, including the PHP 8.2 and 8.4 fixes below; everything else is tooling, tests and documentation. Sites that pull `master` into a submodule or subtree will see new development files (such as `tests/`, `.github/` and `composer.json`), none of which are loaded at runtime.

The plugin header now declares WordPress 6.4 and PHP 7.4 as the minimum versions. As a client mu-plugin, WordPress doesn't enforce them.

### Fixed

- Resolve PHP 8.2 and 8.4 deprecation notices by @trepmal in <https://github.com/Automattic/vip-go-wpcom-compat/pull/53>
- Fix a redundant reference assignment in MediaRSS for PHP 8.2 by @mslinnea in <https://github.com/Automattic/vip-go-wpcom-compat/pull/51>

### Maintenance

- Bring the plugin up to the VIP plugin standards baseline, with Composer tooling, integration tests, and CI for linting, tests and releases by @GaryJones in <https://github.com/Automattic/vip-go-wpcom-compat/pull/54>
- Apply the automatic coding standards fixes, with tests for the sitemaps by @GaryJones in <https://github.com/Automattic/vip-go-wpcom-compat/pull/55>
- Add doc comments and tidy the remaining code style issues by @GaryJones in <https://github.com/Automattic/vip-go-wpcom-compat/pull/56>
- Enforce the coding standards in CI by @GaryJones in <https://github.com/Automattic/vip-go-wpcom-compat/pull/57>

### Documentation

- Restructure the README, documenting previously undocumented behaviour, and add contributor guidelines by @GaryJones in <https://github.com/Automattic/vip-go-wpcom-compat/pull/54>

[1.0.0]: https://github.com/Automattic/vip-go-wpcom-compat/releases/tag/1.0.0
