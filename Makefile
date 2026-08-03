COMPOSE = docker compose
PHP = $(COMPOSE) exec php

.DEFAULT_GOAL := help

.PHONY: help up down restart ps logs logs-php logs-nginx shell health

help: ## Show available commands
	@awk 'BEGIN {FS = ":.*## "} /^[a-zA-Z_-]+:.*## / {printf "%-14s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

up: ## Build and start the environment
	$(COMPOSE) up -d --build

down: ## Stop the environment
	$(COMPOSE) down

restart: ## Restart the environment
	$(COMPOSE) restart

ps: ## Show service status
	$(COMPOSE) ps

logs: ## Follow all logs
	$(COMPOSE) logs -f

logs-php: ## Follow PHP logs
	$(COMPOSE) logs -f php

logs-nginx: ## Follow Nginx logs
	$(COMPOSE) logs -f nginx

shell: ## Open a shell in the PHP container
	$(PHP) sh

health: ## Check the application
	curl --fail --silent --show-error http://localhost:8081/
