SHELL := /bin/bash

-include .env

ARGS = $(shell arg="$(call filter-out,$@,$(MAKECMDGOALS))" && echo $${arg:-${1}})

.DEFAULT_GOAL := help

.PHONY: help
help:
	@grep -E '(^.+: ?##.*$$)|(^##)' $(MAKEFILE_LIST) | cut -c 10- | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-32s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m## /[33m/' && printf "\n"


.PHONY: git.checkout-pull
git.checkout-pull: ## Git checkout to master and pull
	git checkout master && git pull --prune --rebase origin


.PHONY: composer.install
composer.install: ## Composer install
	XDEBUG_MODE=off composer install --prefer-dist --no-progress --no-interaction


.PHONY: check.composer-validate
check.composer-validate: ## Composer validate
	XDEBUG_MODE=off composer validate --strict


.PHONY: check.php-cs-fixer
check.php-cs-fixer: ## Run the CS Fixer without fix
	XDEBUG_MODE=off fpm ./vendor/bin/php-cs-fixer --config=.php-cs-fixer.php --using-cache=no fix --diff --allow-risky=yes --dry-run --verbose


.PHONY: check.php-cs-fixer-fix
check.php-cs-fixer-fix: ## Run the CS Fixer
	XDEBUG_MODE=off fpm ./vendor/bin/php-cs-fixer --config=.php-cs-fixer.php --using-cache=no fix --diff --allow-risky=yes


.PHONY: check.phpstan
check.phpstan: ## Run phpstan analyze
	XDEBUG_MODE=off fpm ./vendor/bin/phpstan clear-result-cache -n -c phpstan.neon -vvv
	XDEBUG_MODE=off fpm ./vendor/bin/phpstan analyse -n -c phpstan.neon --memory-limit=512M -vvv


.PHONY: check.all
check.all: check.composer-validate check.php-cs-fixer check.phpstan


.PHONY: tests
tests: ## Run tests
	XDEBUG_MODE=off fpm ./vendor/bin/codecept clean
	XDEBUG_MODE=off fpm ./vendor/bin/codecept build
