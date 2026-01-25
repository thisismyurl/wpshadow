# WP Shadow Development Environment

This directory contains the complete production-ready WordPress plugin development environment for WP Shadow.

## What's Included

### Docker Services
- **WordPress** (latest) - Running on port 8080
- **MySQL 8.0** - Database on port 3306
- **phpMyAdmin** - Database management on port 8081

### Development Tools
- PHP_CodeSniffer with WordPress Coding Standards
- PHPUnit for testing
- PHPStan for static analysis
- WP-CLI for WordPress management
- Node.js LTS
- Composer

### VS Code Extensions
- PHP Intelephense (IntelliSense)
- WordPress Toolbox
- PHP Sniffer (WPCS integration)
- Xdebug
- EditorConfig
- ESLint
- Prettier
- Code Spell Checker
- GitHub Actions
- GitLens
- GitHub Copilot

## Quick Start

1. Open this repository in GitHub Codespaces or VS Code with Dev Containers
2. Wait for the environment to build (first time takes ~5 minutes)
3. Access WordPress at http://localhost:8080
4. Login with: `admin` / `admin`

## Available Commands

### Composer Scripts
```bash
composer run phpcs        # Check code against WordPress standards
composer run phpcbf       # Auto-fix coding standard issues
composer run phpstan      # Run static analysis
composer run test         # Run PHPUnit tests
composer run test:coverage # Generate test coverage report
composer run lint         # Run both PHPCS and PHPStan
composer run fix          # Alias for phpcbf
```

### WP-CLI Commands
```bash
wp plugin list --allow-root          # List all plugins
wp plugin activate wpshadow --allow-root  # Activate plugin
wp db check --allow-root             # Check database connection
```

### Docker Commands
```bash
docker-compose ps                    # View service status
docker-compose logs -f wordpress     # Follow WordPress logs
docker-compose restart               # Restart all services
docker-compose down -v              # Stop and remove all data
```

## Files Created

- **docker-compose.yml** - Docker services configuration
- **devcontainer.json** - VS Code Dev Container settings
- **setup.sh** - Initial environment setup script (runs once)
- **post-start.sh** - WordPress initialization script (runs on start)

## Configuration Files

- **phpcs.xml** - WordPress Coding Standards configuration
- **phpunit.xml** - PHPUnit test configuration
- **.editorconfig** - Code style settings
- **composer.json** - PHP dependencies and scripts

## WordPress Access

- **WordPress URL**: http://localhost:8080
- **Username**: admin
- **Password**: admin
- **phpMyAdmin**: http://localhost:8081

## Development Workflow

1. Make changes to plugin code
2. Run `composer run fix` to auto-fix coding standards
3. Run `composer run lint` to check for issues
4. Run `composer run test` to verify tests pass
5. Commit your changes

## Troubleshooting

### Services not starting
```bash
docker-compose down -v
docker-compose up -d
```

### Reset WordPress
```bash
docker-compose down -v
docker-compose up -d
# Wait for services, then reload http://localhost:8080
```

### View logs
```bash
docker-compose logs -f wordpress
docker-compose logs -f db
```

## WordPress.org Compliance

This environment is configured to match WordPress.org plugin submission requirements:
- WordPress Coding Standards enforcement
- PHP 7.4+ compatibility checking
- I18n text domain validation
- Global prefix validation (wpshadow, WPShadow)
- EditorConfig for consistent formatting
