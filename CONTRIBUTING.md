# Contributing to WordPress.com Compatibility

Thank you for your interest in contributing. This document covers setting up a local environment, running the checks, and proposing a change.

Sites have relied on this plugin's behaviour for years, so changes should keep that behaviour the same unless there's a clear reason not to.

## Code of Conduct

This project follows the [Automattic Code of Conduct](https://automattic.com/code-of-conduct/).

## Development Setup

### Prerequisites

- [Node.js](https://nodejs.org/) LTS or later (for `npx wp-env`)
- [Docker Desktop](https://www.docker.com/products/docker-desktop)
- [Composer](https://getcomposer.org/)

### Setup

1. Clone the repository.
2. Install dependencies:
   ~~~bash
   composer install
   ~~~
3. Start the WordPress environment:
   ~~~bash
   npx wp-env start
   ~~~
4. Visit http://localhost:8888 and log in with `admin` / `password`.

On VIP, this plugin relies on functions and classes from the VIP Platform's mu-plugins. wp-env loads stand-ins for them from `tests/platform-stubs.php`, so the plugin can load locally.

### Checks

~~~bash
composer lint                # PHP syntax
composer cs                  # Coding standards
composer test:integration    # Integration tests (single site, needs wp-env)
composer test:integration-ms # Integration tests (multisite, needs wp-env)
~~~

## Workflow

1. Create a branch from `develop`:
   ~~~bash
   git checkout develop
   git pull origin develop
   git checkout -b fix/short-description
   ~~~
2. Make your change, with tests.
3. Run the checks locally.
4. Push your branch and open a pull request against `develop`.

`master` holds released code, and is what sites pull from, so changes reach it only through a release.

## Code Standards

We follow the [WordPress VIP Coding Standards](https://github.com/Automattic/VIP-Coding-Standards), configured in `.phpcs.xml.dist`. CI fails on any violation. Run `composer cs-fix` to fix what PHPCS can fix automatically.

Where PHPCS flags code that has to stay as it is, because changing it would change behaviour that sites rely on, add a `// phpcs:ignore` comment naming the sniff and giving the reason, as the existing code does. Keep coding standards changes in their own pull requests, separate from behaviour changes.

`plugins/writing-helper/` is a subtree of [Automattic/writing-helper](https://github.com/Automattic/writing-helper), so changes to it belong upstream.

## Tests

Integration tests live in `tests/Integration/`.

- New behaviour should come with tests.
- A bug fix should come with a test that fails without the fix.
- Name tests after the behaviour they check, for example `test_links_a_plain_text_url()`.

## Pull Requests

- Keep each pull request to one change, and explain what it does and why.
- Reference related issues, for example `Fixes #123`.
- Make sure CI passes before requesting a review.
- **Sign your commits.** `develop` and `master` only accept commits with a verified signature, so a pull request with an unsigned commit cannot be merged until it is re-signed and force-pushed. See GitHub's guide to [signing commits](https://docs.github.com/en/authentication/managing-commit-signature-verification/signing-commits).
