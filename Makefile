DOCKER_COMPOSE  = docker-compose

EXEC        = $(DOCKER_COMPOSE) exec app
RUN        = $(DOCKER_COMPOSE) run app

SYMFONY         = $(EXEC_PHP) app/console
COMPOSER        = $(EXEC_PHP) composer

## 
## Project
## -------
## 

build:
	@$(DOCKER_COMPOSE) pull --parallel --quiet --ignore-pull-failures 2> /dev/null
	$(DOCKER_COMPOSE) build --pull

kill:
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

start: ## Start the project
	$(DOCKER_COMPOSE) up -d --remove-orphans --no-recreate

stop: ## Stop the project
	$(DOCKER_COMPOSE) stop

composer-install: ## Execute composer instalation
	$(RUN) composer install --prefer-dist

test: composer-install ## Execute composer instalation
	$(RUN) bin/simple-phpunit

composer-update: ## Execute package update
	$(RUN) composer update $(BUNDLE)

enter: ## enter docker container
	$(EXEC) bash

.PHONY: build start stop enter

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
