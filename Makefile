help: ## Prints help for targets with comments
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

build: ## Builds the app
	docker compose build --parallel

rebuild: ## Builds the app
	docker compose build --no-cache

up: ## Brings Docker stack up
	docker compose up -d

down: ## Puts Docker stack down
	docker compose down -v

restart: ## Restarts docker stack
	docker compose restart

php-open-sh: ## Opens PHP shell inside running container
	docker exec -it lishack-cms-web bash
