.PHONY: lint

test: lint

lint:
	find . -name '*.php' -type f -print0 | xargs -0 -n 1 php -nl
