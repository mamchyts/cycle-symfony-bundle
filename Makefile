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


.PHONY: up
up: ## Run project
	docker compose up --build --detach --force-recreate --remove-orphans


.PHONY: down
down: ## Shutdown project
	docker compose down --remove-orphans


.PHONY: restart
restart: ## Restart project
	docker compose restart


.PHONY: update
update: ## Update project
	make git.checkout-pull
	make composer.install


.PHONY: composer.install
composer.install: ## Composer install
	docker compose exec -T -e XDEBUG_MODE=off fpm composer install --prefer-dist --no-progress --no-interaction


.PHONY: check.composer-validate
check.composer-validate: ## Composer validate
	docker compose exec -T -e XDEBUG_MODE=off fpm composer validate --strict


.PHONY: check.php-cs-fixer
check.php-cs-fixer: ## Run the CS Fixer without fix
	docker compose exec -T -e XDEBUG_MODE=off fpm ./vendor/bin/php-cs-fixer --config=.php-cs-fixer.php --using-cache=no fix --diff --allow-risky=yes --dry-run --verbose


.PHONY: check.php-cs-fixer-fix
check.php-cs-fixer-fix: ## Run the CS Fixer
	docker compose exec -T -e XDEBUG_MODE=off fpm ./vendor/bin/php-cs-fixer --config=.php-cs-fixer.php --using-cache=no fix --diff --allow-risky=yes


.PHONY: check.phpstan
check.phpstan: ## Run phpstan analyze
	docker compose exec -T -e XDEBUG_MODE=off fpm ./vendor/bin/phpstan clear-result-cache -n -c phpstan.neon -vvv
	docker compose exec -T -e XDEBUG_MODE=off fpm ./vendor/bin/phpstan analyse -n -c phpstan.neon --memory-limit=512M -vvv


.PHONY: check.all
check.all: check.composer-validate check.php-cs-fixer check.phpstan


.PHONY: cache-clear
cache-clear: ## Run clear cache
	docker compose exec -T -e XDEBUG_MODE=off fpm rm -rf var/cache/dev
	docker compose exec -T -e XDEBUG_MODE=off fpm rm -rf var/cache/prod
	docker compose exec -T -e XDEBUG_MODE=off fpm rm -rf var/cache/test
	docker compose exec -T -e XDEBUG_MODE=off fpm ./bin/console cache:warmup --no-interaction --no-debug --env=dev


.PHONY: tests
tests: ## Run tests
	docker compose exec -T -e XDEBUG_MODE=coverage fpm ./vendor/bin/codecept run unit --env=test --fail-fast=1 --coverage-html
