DOCKER_COMPOSE  = docker-compose
EXEC        	= $(DOCKER_COMPOSE) exec app
RUN        		= $(DOCKER_COMPOSE) run app
COMPOSER        = $(RUN) composer
QA        		= docker run -it --rm -v `pwd`:/project mykiwi/phaudit:7.2

##
## Project
## -------
##

kill:
	$(DOCKER_COMPOSE) kill
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

start: up test ## Start the project

up: ## Up the project
	$(DOCKER_COMPOSE) up -d --build --remove-orphans

stop: ## Stop the project
	$(DOCKER_COMPOSE) stop

composer-install: ## Execute composer instalation
	$(COMPOSER) install --prefer-dist

test: composer-install ## Execute composer instalation
	$(RUN) bin/simple-phpunit

composer-update: ## Execute package update
	$(COMPOSER) update $(BUNDLE)

php-cs-fixer: ## apply php-cs-fixer fixes
	$(QA) php-cs-fixer fix src --using-cache=no --verbose --diff --rules @Symfony

enter: ## enter docker container
	$(EXEC) bash

.PHONY: up start stop enter

.DEFAULT_GOAL := help
help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'
.PHONY: help
