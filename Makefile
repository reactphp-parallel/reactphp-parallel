# set all to phony
SHELL=bash

.PHONY: *

DOCKER_CGROUP:=$(shell cat /proc/1/cgroup | grep docker | wc -l)

ifneq ("$(wildcard /.dockerenv)","")
    IN_DOCKER=TRUE
else ifneq ("$(DOCKER_CGROUP)","0")
	IN_DOCKER=TRUE
else
    IN_DOCKER=FALSe
endif

ifeq ("$(IN_DOCKER)","TRUE")
	DOCKER_RUN=
else
	DOCKER_RUN=docker run --rm -it \
		-v "`pwd`:`pwd`" \
		-w "`pwd`" \
		"wyrihaximusnet/php:7.4-zts-alpine3.12-dev"
endif

all: syntax-php cs-fix cs stan psalm unit infection composer-require-checker composer-unused backward-compatibility-check

syntax-php: ## Lint PHP syntax
	$(DOCKER_RUN) vendor/bin/parallel-lint --exclude vendor .

cs: ## Check the code for code style issues
	$(DOCKER_RUN) vendor/bin/phpcs --parallel=$(shell nproc)

cs-fix: ## Fix any automatically fixable code style issues
	$(DOCKER_RUN) vendor/bin/phpcbf --parallel=$(shell nproc)

stan: ## Run static analysis (PHPStan)
	$(DOCKER_RUN) vendor/bin/phpstan analyse src tests --level max --ansi -c phpstan.neon

psalm: ## Run static analysis (Psalm)
	$(DOCKER_RUN) vendor/bin/psalm --threads=$(shell nproc) --shepherd --stats

unit: ## Run tests
	$(DOCKER_RUN) vendor/bin/phpunit --colors=always -c phpunit.xml.dist --coverage-text --coverage-html covHtml --coverage-clover ./build/logs/clover.xml

unit-ci: unit
	if [ -f ./build/logs/clover.xml ]; then wget https://scrutinizer-ci.com/ocular.phar && sleep 3 && php ocular.phar code-coverage:upload --format=php-clover ./build/logs/clover.xml; fi

infection: ## Run mutation testing
	$(DOCKER_RUN) vendor/bin/infection --ansi --min-msi=100 --min-covered-msi=100 --threads=$(shell nproc)

composer-require-checker: ## Ensure we require every package used in this package directly
	$(DOCKER_RUN) vendor/bin/composer-require-checker --ignore-parse-errors --ansi -vvv --config-file=composer-require-checker.json

composer-unused: ## Ensure we don't require any package we don't use in this package directly
	$(DOCKER_RUN) composer unused --ansi

backward-compatibility-check: ## Check code for backwards incompatible changes
	$(DOCKER_RUN) vendor/bin/roave-backward-compatibility-check || true

task-list-ci:
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "%s\n", $$1}' | jq --raw-input --slurp -c 'split("\n")| .[0:-1]'

help:
	@printf "\033[33mUsage:\033[0m\n  make [target]\n\n\033[33mTargets:\033[0m\n"
	@printf "  \033[32m%-32s\033[0m %s\n"  "all" "Runs everything"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-32s\033[0m %s\n", $$1, $$2}'
