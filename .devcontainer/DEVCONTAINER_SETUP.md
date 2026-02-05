# WPShadow Development Container - Setup Guide

This directory contains the configuration for the WPShadow development environment using Dev Containers.

## Overview

The development container is configured to provide:
- **Docker support** for running WordPress in containers
- **WordPress testing environment** with MySQL database
- **phpMyAdmin** for database management
- **Automatic service startup** on container creation and start
- **PHP development tools** for testing and code quality

## Quick Start

### 1. Open in Dev Container

**GitHub Codespaces:**
- Click "Code" → "Codespaces" → "Create codespace on main"
- Wait 3-5 minutes for setup to complete

**VS Code (Local):**
- Install "Dev Containers" extension
- Open repository folder in VS Code
- Click green icon → "Reopen in Container"

### 2. Wait for Automatic Setup

The environment will automatically:
1. Start Docker services (MySQL, WordPress, phpMyAdmin)
2. Initialize databases
3. Set up WordPress
4. Install dependencies

**Setup takes 3-5 minutes.** You'll see logs in the terminal showing progress.

### 3. Access Services

#### WordPress Dashboard
- **Local:** http://localhost:8080
- **Codespaces:** See terminal for dynamic URL

#### phpMyAdmin
- **Local:** http://localhost:8081
- **Codespaces:** See terminal for dynamic URL

#### MySQL Database
- **Host:** localhost:3306
- **User:** wordpress
- **Password:** wordpress
- **Database:** wordpress

## Automatic URL Detection

The environment automatically detects whether you're running in **GitHub Codespaces** or **local development** and uses the appropriate URL:

### GitHub Codespaces
- **Automatic Detection:** Uses `CODESPACE_NAME` environment variable
- **WordPress URL:** `https://[codespace-name]-8080.app.github.dev`
- **phpMyAdmin URL:** `https://[codespace-name]-8081.app.github.dev`
- **No Configuration Needed:** Displayed in terminal on startup

### Local Development
- **WordPress URL:** `http://localhost:8080`
- **phpMyAdmin URL:** `http://localhost:8081`
- **MySQL:** `localhost:3306`

WordPress automatically configures itself with the correct URL based on your environment.

---

## Accessing Services

```bash
# Check service status
docker compose ps

# View detailed logs
docker compose logs

# Restart services
docker compose restart

# Force rebuild (if corrupted)
docker compose down -v
docker compose up -d
```

### MySQL Connection Issues?

```bash
# Test MySQL connection
mysql -h127.0.0.1 -uwordpress -pwordpress wordpress

# Or use docker exec
docker compose exec db mysql -uwordpress -pwordpress -e "SHOW DATABASES;"
```

### WordPress Not Responding?

```bash
# Check WordPress container logs
docker compose logs wordpress

# Enter WordPress container
docker compose exec wordpress bash

# Check WordPress health
curl http://localhost:8080/wp-admin/
```

### PHP Syntax Errors in Plugin?

```bash
# Validate plugin syntax
php -l wpshadow.php

# Or via Docker
docker compose exec wordpress php -l /var/www/html/wp-content/plugins/wpshadow/wpshadow.php
```

## Configuration Files

### `devcontainer.json`
Main configuration for:
- Base container image (Alpine Linux)
- Docker support (Docker-in-Docker)
- VS Code extensions
- Port forwarding
- Environment variables
- Post-create and post-start hooks

### `post-create.sh`
Runs once when container is created:
- Starts Docker Compose services
- Waits for MySQL to be ready
- Waits for WordPress to be ready
- Installs dependencies (Composer, npm)

### `post-start-enhanced.sh`
Runs every time container starts:
- Verifies Docker services are running
- Checks MySQL connectivity
- Checks WordPress connectivity
- Displays connection information
- Shows helpful tips

### `docker-compose.yml`
Defines three services:
1. **MySQL 8.0** - Database server
2. **WordPress** - PHP application with mounted plugin
3. **phpMyAdmin** - Database management interface

## Common Tasks

### View Logs
```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f wordpress
docker compose logs -f db
```

### Access WordPress Container Shell
```bash
docker compose exec wordpress bash
```

### Run WP-CLI Commands
```bash
docker compose exec wordpress wp --allow-root plugin list
docker compose exec wordpress wp --allow-root user list
docker compose exec wordpress wp --allow-root db query "SELECT * FROM wp_options LIMIT 5;"
```

### Run PHP Code
```bash
docker compose exec wordpress php -r 'echo phpinfo();'
```

### Backup Database
```bash
docker compose exec db mysqldump -uwordpress -pwordpress wordpress > backup.sql
```

### Restore Database
```bash
docker compose exec -T db mysql -uwordpress -pwordpress wordpress < backup.sql
```

### Clear WordPress Cache
```bash
docker compose exec wordpress rm -rf /var/www/html/wp-content/cache/*
```

## Environment Variables

Available in containers:
- `WORDPRESS_DB_HOST=db`
- `WORDPRESS_DB_USER=wordpress`
- `WORDPRESS_DB_PASSWORD=wordpress`
- `WORDPRESS_DB_NAME=wordpress`
- `WORDPRESS_DEBUG=1`

### PHP Timeout Configuration

The development environment is configured with extended timeouts (999 seconds) for debugging and long-running operations:

- **PHP max_execution_time**: 999 seconds (default: 30)
- **PHP max_input_time**: 999 seconds (default: 60)
- **PHP default_socket_timeout**: 999 seconds (default: 60)
- **VS Code terminal timeout**: 999 seconds
- **HTTP request timeout**: 999 seconds

**Configuration files:**
- `.devcontainer/php-custom.ini` - PHP configuration mounted into WordPress container
- `docker-compose.yml` - PHP ini_set directives in WORDPRESS_CONFIG_EXTRA
- `.devcontainer/devcontainer.json` - VS Code terminal and HTTP timeout settings

**Why 999 seconds?**
- Allows XDebug step-through debugging without timeout
- Permits long-running diagnostic scans
- Enables manual testing without interruption
- Prevents timeout errors during development

**Note:** These settings are only for development. Production should use standard timeout values (30-60 seconds).

## GitHub Copilot Permissions

The development environment is configured to allow GitHub Copilot Chat to perform actions without confirmation prompts:

**Enabled Capabilities:**
- ✅ **Run terminal commands** - Copilot can execute bash commands directly
- ✅ **Execute code** - Run code snippets for testing and validation
- ✅ **Create/edit files** - Generate and modify files without asking
- ✅ **Code actions** - Apply refactoring, fixes, and improvements automatically
- ✅ **Auto-apply suggestions** - Changes applied immediately when appropriate
- ✅ **Use instruction files** - Reads `.github/copilot-instructions.md` for context

**Configured settings in devcontainer.json:**
```json
"github.copilot.chat.runCommand.enabled": true,
"github.copilot.chat.executeCode.enabled": true,
"github.copilot.chat.codeActions.enabled": true,
"github.copilot.chat.autoApply": "always"
```

**Why these permissions?**
- Faster development workflow without constant prompts
- Copilot can fully assist with DevOps tasks (git, docker, etc.)
- Enable autonomous debugging and testing
- Seamless file creation and code generation

**Security Note:** These permissions only apply within the Codespaces container. Copilot still requires your GitHub authentication and cannot access anything outside the container.

## Git Credentials & GitHub Access

### Automatic Repository Authentication

GitHub Codespaces automatically authenticates you for the current repository using your GitHub credentials. You don't need to add your PAT for `git push/pull` to `thisismyurl/wpshadow`.

### Using Personal Access Token (PAT) for Other Repos

If you need to clone or access other GitHub repositories, you can store your PAT as a Codespaces secret:

#### Setup Instructions

1. **Create a GitHub PAT** (if you don't have one):
   - Go to GitHub → Settings → Developer settings → [Personal access tokens](https://github.com/settings/tokens)
   - Click "Generate new token"
   - Select scopes: `repo` (for private repos), `workflow` (for Actions)
   - Copy the token

2. **Add to Codespaces Secrets**:
   - Go to GitHub → Settings → [Codespaces](https://github.com/settings/codespaces)
   - Click "Add secret"
   - Name: `GH_TOKEN`
   - Value: Paste your token
   - Repository access: Select `wpshadow` or all repositories

3. **Automatic Configuration**:
   - The `post-create.sh` script automatically detects the `GH_TOKEN` secret
   - Git credentials are stored locally in `~/.git-credentials`
   - You can now clone private repos and push to any repository with your PAT

#### Manual Configuration

If you prefer to set it up manually or need to update credentials:

```bash
# Configure git credential helper
git config --global credential.helper store

# Add your credentials (replace YOUR_TOKEN with your actual PAT)
echo "https://YOUR_TOKEN@github.com" >> ~/.git-credentials

# Secure the file
chmod 600 ~/.git-credentials
```

#### Security Notes

- Codespaces secrets are **encrypted** and never stored in your repository
- `~/.git-credentials` is stored locally in the Codespaces container filesystem
- Each Codespaces instance gets its own isolated file system
- Secrets are deleted when you delete the Codespaces instance
- Never commit `.git-credentials` to git (it's in `.gitignore`)

#### Testing Your Setup

```bash
# Test cloning a private repository
git clone https://github.com/yourname/private-repo.git

# Or check if credentials are working
git config --global credential.helper
cat ~/.git-credentials  # Shows your configured credential helper
```

## Performance Tips

### Reduce Build Time
- First build is slowest (downloads base images)
- Subsequent builds are cached and faster
- Only rebuild if you modify `devcontainer.json`

### Optimize Docker
```bash
# Clean up unused images/volumes
docker system prune -a

# Check disk usage
docker system df
```

### Development Tips
- Mount plugin directory for live editing
- Use XDebug for step-through debugging
- Enable WP_DEBUG for error visibility
- Use phpMyAdmin for database queries
- Run tests frequently during development

## Extending the Container

To add more VS Code extensions, edit `devcontainer.json`:

```json
"extensions": [
  "existing-extension",
  "new-extension-id"
]
```

To add more apt packages, edit `onCreateCommand`:

```bash
"onCreateCommand": "apk add --no-cache git curl bash mysql-client your-package"
```

## Security Notes

### Development Only
This configuration is for **development only**. It's not suitable for production:
- Default passwords are hardcoded
- Debug mode is enabled
- All services are on same network

### Safe Practices
- Never commit passwords to git
- Use `.env` files for sensitive data (not in codespace)
- Don't expose container ports to internet
- Use strong passwords for production WordPress

## Testing

### Run Plugin Tests
```bash
# Inside container
docker compose exec wordpress bash
cd /var/www/html/wp-content/plugins/wpshadow
composer test
```

### Run Code Standards Check
```bash
docker compose exec wordpress bash
cd /var/www/html/wp-content/plugins/wpshadow
composer phpcs
```

### Run Static Analysis
```bash
docker compose exec wordpress bash
cd /var/www/html/wp-content/plugins/wpshadow
composer phpstan
```

## Getting Help

- **Check logs:** `docker compose logs -f`
- **Rebuild container:** VS Code → Cmd+Shift+P → "Dev Containers: Rebuild Container"
- **Check setup log:** `cat /tmp/wpshadow-setup.log`
- **Check start log:** `cat /tmp/wpshadow-start.log`

## References

- [Dev Containers Documentation](https://containers.dev/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [WordPress Docker Documentation](https://hub.docker.com/_/wordpress)
- [phpMyAdmin Documentation](https://www.phpmyadmin.net/)
