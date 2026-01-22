.PHONY: help start stop restart logs shell-site shell-test db-site db-test clean reset setup activate

# Colors for output
GREEN  := \033[0;32m
YELLOW := \033[1;33m
NC     := \033[0m

help: ## Show this help message
	@echo "╔═══════════════════════════════════════════════════════════════╗"
	@echo "║         WPShadow Docker Development - Make Commands           ║"
	@echo "╚═══════════════════════════════════════════════════════════════╝"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  $(GREEN)%-20s$(NC) %s\n", $$1, $$2}'
	@echo ""

setup: ## Initial setup - run this first
	@echo "Running setup script..."
	@./setup-docker.sh

start: ## Start all Docker containers
	@echo "$(GREEN)Starting Docker containers...$(NC)"
	@docker-compose up -d
	@echo "$(GREEN)✓ Containers started$(NC)"
	@echo "  Main Site:  http://localhost:8080"
	@echo "  Test Site:  http://localhost:9000"
	@echo "  phpMyAdmin: http://localhost:8081"
	@echo "  MailHog:    http://localhost:8025"

stop: ## Stop all Docker containers
	@echo "$(YELLOW)Stopping Docker containers...$(NC)"
	@docker-compose down
	@echo "$(GREEN)✓ Containers stopped$(NC)"

restart: ## Restart all Docker containers
	@echo "$(YELLOW)Restarting Docker containers...$(NC)"
	@docker-compose restart
	@echo "$(GREEN)✓ Containers restarted$(NC)"

restart-site: ## Restart main site container
	@docker-compose restart wordpress-site
	@echo "$(GREEN)✓ Main site restarted$(NC)"

restart-test: ## Restart test site container
	@docker-compose restart wordpress-test
	@echo "$(GREEN)✓ Test site restarted$(NC)"

logs: ## Show logs for all containers
	@docker-compose logs -f

logs-site: ## Show logs for main site
	@docker-compose logs -f wordpress-site

logs-test: ## Show logs for test site
	@docker-compose logs -f wordpress-test

logs-db-site: ## Show logs for main site database
	@docker-compose logs -f db-site

logs-db-test: ## Show logs for test site database
	@docker-compose logs -f db-test

shell-site: ## Open shell in main site container
	@docker-compose exec wordpress-site bash

shell-test: ## Open shell in test site container
	@docker-compose exec wordpress-test bash

db-site: ## Open MySQL CLI for main site
	@docker-compose exec db-site mysql -u wordpress -pwordpress wpshadow_site

db-test: ## Open MySQL CLI for test site
	@docker-compose exec db-test mysql -u wordpress -pwordpress wpshadow_test

wp-site: ## Run WP-CLI command in main site (usage: make wp-site CMD="plugin list")
	@docker-compose exec wordpress-site wp --allow-root $(CMD)

wp-test: ## Run WP-CLI command in test site (usage: make wp-test CMD="plugin list")
	@docker-compose exec wordpress-test wp --allow-root $(CMD)

activate: ## Activate theme and plugin in main site
	@echo "$(GREEN)Activating theme and plugins in main site...$(NC)"
	@docker-compose exec wordpress-site wp --allow-root theme activate theme-wpshadow 2>/dev/null || echo "Theme not found (create /workspaces/theme-wpshadow first)"
	@docker-compose exec wordpress-site wp --allow-root plugin activate wpshadow
	@docker-compose exec wordpress-site wp --allow-root plugin activate wpshadow-site
	@echo "$(GREEN)✓ Activated$(NC)"

activate-test: ## Activate plugin in test site
	@echo "$(GREEN)Activating plugins in test site...$(NC)"
	@docker-compose exec wordpress-test wp --allow-root plugin activate wpshadow
	@docker-compose exec wordpress-test wp --allow-root plugin activate wpshadow-site
	@echo "$(GREEN)✓ Activated$(NC)"

status: ## Show status of all containers
	@docker-compose ps

clean: ## Stop containers and remove volumes (DESTRUCTIVE)
	@echo "$(YELLOW)⚠️  This will DELETE all databases and WordPress files!$(NC)"
	@echo "Press Ctrl+C to cancel, or Enter to continue..."
	@read dummy
	@docker-compose down -v
	@echo "$(GREEN)✓ Cleaned up$(NC)"

reset-site: ## Reset main site (keeps code, deletes database)
	@echo "$(YELLOW)⚠️  This will DELETE the main site database!$(NC)"
	@echo "Press Ctrl+C to cancel, or Enter to continue..."
	@read dummy
	@docker-compose down
	@docker volume rm wpshadow_wp_site_data wpshadow_wp_site_db 2>/dev/null || true
	@docker-compose up -d wordpress-site db-site
	@echo "$(GREEN)✓ Main site reset. Complete WordPress setup at http://localhost:8080$(NC)"

reset-test: ## Reset test site (keeps code, deletes database)
	@echo "$(YELLOW)⚠️  This will DELETE the test site database!$(NC)"
	@echo "Press Ctrl+C to cancel, or Enter to continue..."
	@read dummy
	@docker-compose down
	@docker volume rm wpshadow_wp_test_data wpshadow_wp_test_db 2>/dev/null || true
	@docker-compose up -d wordpress-test db-test
	@echo "$(GREEN)✓ Test site reset. Complete WordPress setup at http://localhost:9000$(NC)"

backup-site: ## Backup main site database
	@echo "$(GREEN)Backing up main site database...$(NC)"
	@docker-compose exec db-site mysqldump -u wordpress -pwordpress wpshadow_site > backup-site-$(shell date +%Y%m%d-%H%M%S).sql
	@echo "$(GREEN)✓ Backup saved$(NC)"

backup-test: ## Backup test site database
	@echo "$(GREEN)Backing up test site database...$(NC)"
	@docker-compose exec db-test mysqldump -u wordpress -pwordpress wpshadow_test > backup-test-$(shell date +%Y%m%d-%H%M%S).sql
	@echo "$(GREEN)✓ Backup saved$(NC)"

import-site: ## Import database to main site (usage: make import-site FILE=backup.sql)
	@test -n "$(FILE)" || (echo "$(YELLOW)Usage: make import-site FILE=backup.sql$(NC)" && exit 1)
	@echo "$(GREEN)Importing to main site...$(NC)"
	@docker-compose exec -T db-site mysql -u wordpress -pwordpress wpshadow_site < $(FILE)
	@echo "$(GREEN)✓ Database imported$(NC)"

import-test: ## Import database to test site (usage: make import-test FILE=backup.sql)
	@test -n "$(FILE)" || (echo "$(YELLOW)Usage: make import-test FILE=backup.sql$(NC)" && exit 1)
	@echo "$(GREEN)Importing to test site...$(NC)"
	@docker-compose exec -T db-test mysql -u wordpress -pwordpress wpshadow_test < $(FILE)
	@echo "$(GREEN)✓ Database imported$(NC)"

theme-create: ## Create basic theme structure
	@echo "$(GREEN)Creating theme structure...$(NC)"
	@mkdir -p /workspaces/theme-wpshadow/{css,js,inc,templates}
	@echo "$(GREEN)✓ Theme directories created in /workspaces/theme-wpshadow$(NC)"

open-site: ## Open main site in browser
	@echo "Opening main site..."
	@python3 -m webbrowser http://localhost:8080 2>/dev/null || open http://localhost:8080 || xdg-open http://localhost:8080 || echo "Visit: http://localhost:8080"

open-test: ## Open test site in browser
	@echo "Opening test site..."
	@python3 -m webbrowser http://localhost:9000 2>/dev/null || open http://localhost:9000 || xdg-open http://localhost:9000 || echo "Visit: http://localhost:9000"

open-pma: ## Open phpMyAdmin in browser
	@echo "Opening phpMyAdmin..."
	@python3 -m webbrowser http://localhost:8081 2>/dev/null || open http://localhost:8081 || xdg-open http://localhost:8081 || echo "Visit: http://localhost:8081"

open-mail: ## Open MailHog in browser
	@echo "Opening MailHog..."
	@python3 -m webbrowser http://localhost:8025 2>/dev/null || open http://localhost:8025 || xdg-open http://localhost:8025 || echo "Visit: http://localhost:8025"

ps: status ## Alias for status

debug: ## Show debug information
	@echo "╔═══════════════════════════════════════════════════════════════╗"
	@echo "║                    Debug Information                          ║"
	@echo "╚═══════════════════════════════════════════════════════════════╝"
	@echo ""
	@echo "$(GREEN)Docker Version:$(NC)"
	@docker --version
	@echo ""
	@echo "$(GREEN)Docker Compose Version:$(NC)"
	@docker-compose --version
	@echo ""
	@echo "$(GREEN)Container Status:$(NC)"
	@docker-compose ps
	@echo ""
	@echo "$(GREEN)Volume List:$(NC)"
	@docker volume ls | grep wpshadow
	@echo ""
	@echo "$(GREEN)Network List:$(NC)"
	@docker network ls | grep wpshadow

urls: ## Show all access URLs
	@echo "╔═══════════════════════════════════════════════════════════════╗"
	@echo "║                    Access URLs                                ║"
	@echo "╚═══════════════════════════════════════════════════════════════╝"
	@echo ""
	@echo "$(GREEN)Main Site:$(NC)"
	@echo "  Frontend: http://localhost:8080"
	@echo "  Admin:    http://localhost:8080/wp-admin"
	@echo ""
	@echo "$(GREEN)Test Site:$(NC)"
	@echo "  Frontend: http://localhost:9000"
	@echo "  Admin:    http://localhost:9000/wp-admin"
	@echo ""
	@echo "$(GREEN)Tools:$(NC)"
	@echo "  phpMyAdmin: http://localhost:8081"
	@echo "  MailHog:    http://localhost:8025"
	@echo ""
