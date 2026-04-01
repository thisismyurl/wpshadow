# 🚀 WPShadow DevContainer - Quick Reference Card

## Quick Start (Choose One)

### GitHub Codespaces
```
1. Click Code → Codespaces → Create codespace on main
2. Wait 3-5 minutes for setup
3. Open http://[codespace-url]-8080.app.github.dev
```

### Local Dev Container (VS Code)
```
1. Install Dev Containers extension
2. Open folder in VS Code
3. Click 🟢 icon → Reopen in Container
4. Wait 3-5 minutes for setup
5. Open http://localhost:8080
```

---

## 🌐 Access Points

| Service | GitHub Codespaces | Local |
|---------|-------------------|-------|
| **WordPress** | https://[codespace]-8080.app.github.dev | http://localhost:8080 |
| **phpMyAdmin** | https://[codespace]-8081.app.github.dev | http://localhost:8081 |
| **MySQL** | Accessible via container | localhost:3306 |

> **Note:** URL is automatically detected - WordPress configures itself correctly in both environments!

---

## 🔨 Essential Commands

```bash
# Services
docker compose ps              # View running services
docker compose up -d          # Start services
docker compose down           # Stop services
docker compose restart        # Restart services
docker compose logs -f        # View logs in real-time

# WordPress
docker compose exec wordpress bash              # Enter container
docker compose exec wordpress wp --allow-root plugin list   # List plugins
docker compose exec wordpress wp --allow-root user list     # List users

# Database
mysql -h127.0.0.1 -uwordpress -pwordpress wordpress        # Connect to MySQL
docker compose exec db mysqldump -uwordpress -pwordpress wordpress > backup.sql  # Backup

# Development
php -l wpshadow.php                    # Check syntax
composer install                       # Install dependencies
composer phpcs                         # Code standards
composer test                          # Run tests
```

---

## 📊 Service Status Quick Check

```bash
# All containers
docker compose ps

# WordPress logs
docker compose logs wordpress

# Database logs
docker compose logs db

# phpMyAdmin logs
docker compose logs phpmyadmin
```

---

## 🔧 Common Tasks

### Reset Everything (Clean Slate)
```bash
docker compose down -v
docker compose up -d
```

### View Database Content
```bash
# Via MySQL
mysql -h127.0.0.1 -uwordpress -pwordpress wordpress

# Via phpMyAdmin
# Open http://localhost:8081
# Login: wordpress / wordpress
```

### Check Plugin Installation
```bash
docker compose exec wordpress ls -la /var/www/html/wp-content/plugins/wpshadow/
```

### View WordPress Debug Log
```bash
docker compose exec wordpress tail -f /var/www/html/wp-content/debug.log
```

### Run Plugin Tests
```bash
docker compose exec wordpress bash
cd /var/www/html/wp-content/plugins/wpshadow
composer test
```

---

## 🚨 Troubleshooting Quick Fixes

| Issue | Solution |
|-------|----------|
| Services won't start | `docker compose up -d` + wait 30 sec |
| Page won't load | Check `docker compose ps` - all containers running? |
| MySQL connection error | Wait 30 sec after startup, then retry |
| WordPress shows errors | Check logs: `docker compose logs wordpress` |
| Plugin not visible | Restart: `docker compose restart wordpress` |
| Port already in use | Change port in docker-compose.yml |
| Everything broken | `docker compose down -v` then rebuild |

---

## 📁 Important Files

```
.devcontainer/
├── devcontainer.json          # Main config (DO NOT MODIFY)
├── post-create.sh             # Runs on creation
├── post-start-enhanced.sh     # Runs on start
├── DEVCONTAINER_SETUP.md      # Full documentation
├── REBUILD_SUMMARY.md         # What was changed
└── .env.example               # Environment variables

docker-compose.yml             # Service definitions
docker-start.sh               # Manual start script
Makefile.devcontainer         # Make commands
```

---

## 💡 Pro Tips

1. **Use Makefile for common tasks:**
   ```bash
   make status              # See service status
   make docker-logs         # View all logs
   make code-quality        # Run code checks
   ```

2. **Keep databases safe:**
   ```bash
   docker compose exec db mysqldump -uwordpress -pwordpress wordpress > backup.sql
   ```

3. **Debug with VS Code:**
   - Set breakpoints in code
   - Services run with Xdebug enabled
   - Use Debug Console to inspect

4. **Edit plugin files:**
   - Changes live-reload automatically
   - No need to restart WordPress

5. **Monitor in real-time:**
   ```bash
   docker compose logs -f wordpress
   ```

---

## 🔗 Useful Links

- [Full Documentation](DEVCONTAINER_SETUP.md)
- [Rebuild Summary](REBUILD_SUMMARY.md)
- [Docker Docs](https://docs.docker.com/)
- [WordPress Docs](https://developer.wordpress.org/)
- [VS Code Dev Containers](https://code.visualstudio.com/docs/devcontainers/containers)

---

## 📞 Emergency Commands

```bash
# If everything is broken:
docker compose down -v                    # Remove all containers/volumes
docker system prune -a                    # Clean up Docker
docker compose up -d                      # Start fresh

# Check container health:
docker compose ps                         # Status of all services
docker compose logs --tail=50 wordpress   # Last 50 lines of WordPress

# Full environment reset:
rm -rf .docker/                          # Remove cached data
docker compose down -v                   # Remove volumes
## Rebuild in VS Code or Codespaces
```

---

**Save this card! 📋** Bookmark DEVCONTAINER_SETUP.md for full reference.
