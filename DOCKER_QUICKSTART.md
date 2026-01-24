# Docker Testing - Quick Reference

## One-Command Startup

```bash
# From the workspace root
cd /workspaces/wpshadow
docker-compose up -d
```

## Service Status

```bash
# Check all services
docker-compose ps

# View logs (live)
docker-compose logs -f wordpress

# View MySQL logs
docker-compose logs -f mysql
```

## Access Points

### WordPress
- **Codespaces**: `http://$CODESPACE_NAME-9000.$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN`
- **Local**: `http://localhost:9000`

### MySQL (from inside container)
```bash
mysql -h mysql -u wordpress -pwordpress wordpress
```

### WordPress Admin
- Go to WordPress URL and log in with your admin account
- Navigate to **Plugins** to see wpshadow

## Essential Commands

| Task | Command |
|------|---------|
| Start services | `docker-compose up -d` |
| Stop services | `docker-compose down` |
| Restart WordPress | `docker-compose restart wordpress` |
| Restart MySQL | `docker-compose restart mysql` |
| View all logs | `docker-compose logs` |
| Follow WordPress logs | `docker-compose logs -f wordpress` |
| Execute command in WordPress | `docker-compose exec wordpress bash` |
| MySQL client | `docker exec -it wpshadow-mysql mysql -u wordpress -pwordpress wordpress` |
| Fresh start | `docker-compose down -v && docker-compose up -d` |

## Port Verification

```bash
# Check if port 9000 is correctly mapped
docker-compose ps | grep 9000

# Should output something like:
# 0.0.0.0:9000->80/tcp

# Verify no other services on port 9000
docker ps | grep 9000 | wc -l
# Should return only 1 (the WordPress container)
```

## URL Configuration Verification

The WordPress setup automatically detects whether you're in Codespaces or local:

```php
// This is handled automatically in docker-compose.yml:
define('WP_HOME', 'http://' . (getenv('CODESPACE_NAME') 
  ? getenv('CODESPACE_NAME') . '-9000.' . getenv('GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN') 
  : 'localhost:9000'));
```

**Result:**
- ✅ Codespaces: Uses full domain (no localhost)
- ✅ Local: Uses localhost:9000
- ✅ Port 9000: Exclusively used
- ✅ No redirects: Configuration prevents port jumping

## Testing Workflow

1. **Start services**: `docker-compose up -d`
2. **Edit code**: Make changes in `/workspaces/wpshadow/`
3. **Reload browser**: Changes visible immediately
4. **Check logs**: `docker-compose logs -f wordpress | grep -i error`
5. **Commit**: `git add . && git commit -m "message" && git push`

## Troubleshooting

### Services won't start
```bash
# Check logs
docker-compose logs

# Try clean restart
docker-compose down
docker-compose up -d
```

### Port 9000 already in use
```bash
# Find what's using it
lsof -i :9000

# Kill it
kill -9 <PID>

# Then restart
docker-compose restart wordpress
```

### Plugin not showing
```bash
# Verify volume mount
docker exec wpshadow-wordpress ls -la /var/www/html/wp-content/plugins/wpshadow/

# Should show wpshadow.php
```

### WordPress shows localhost in settings
```bash
# Check environment variables
docker-compose config | grep WP_HOME

# For Codespaces, these should be auto-set
# For local: ensure docker-compose.yml has correct CODESPACE_NAME and GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN
```

## Performance

- **Initial startup**: 2-3 minutes (first run)
- **Subsequent starts**: 30-60 seconds
- **Code sync**: <2 seconds
- **Database response**: <100ms

## Next Steps

1. Access WordPress at the URL above
2. Complete WordPress setup if first run
3. Go to Plugins → wpshadow
4. Activate the plugin
5. Access Tools → wpshadow to see diagnostics
6. Run the 648 diagnostics to test functionality

## Got Issues?

See [DOCKER_TESTING_SETUP.md](../DOCKER_TESTING_SETUP.md) for comprehensive troubleshooting and detailed setup guide.
