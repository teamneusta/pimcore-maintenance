composer=docker run \
	--rm \
	--volume=$(PWD):/app \
	--volume=$(HOME)/.composer:/tmp \
	--user=$(shell id -u):$(shell id -g) \
	composer:1.9.0

php-cli=docker run \
	--rm \
	--volume=$(PWD):/app \
	--workdir=/app \
	--user=$(shell id -u):$(shell id -g) \
	php:7.2.21-cli-alpine \
	php

vendor/%: composer.json
	${composer} install --no-interaction

install: vendor/

clean:
	rm -rf vendor composer.lock

test: vendor/bin/phpunit
	${php-cli} vendor/bin/phpunit -c phpunit.xml.dist

.PHONY: clean install test
