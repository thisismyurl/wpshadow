# GitHub Codespaces URL Configuration

> **Automatic Detection:** WordPress automatically detects your environment and uses the correct URL. No configuration needed!

---

## How It Works

### Environment Detection
The system uses the `CODESPACE_NAME` environment variable to determine if you're in GitHub Codespaces:

```bash
# GitHub Codespaces environment
CODESPACE_NAME="exciting-chainsaw-abc123"
GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN="app.github.dev"

# These are passed to Docker containers via devcontainer.json
```

### WordPress Auto-Configuration
WordPress automatically configures its URL based on the environment:

```php
// In docker-compose.yml WORDPRESS_CONFIG_EXTRA
if ( ! empty( getenv( 'CODESPACE_NAME' ) ) ) {
    // GitHub Codespaces
    define( 'WP_HOME', 'https://[codespace-name]-8080.[domain]' );
    define( 'WP_SITEURL', 'https://[codespace-name]-8080.[domain]' );
} else {
    // Local development
    define( 'WP_HOME', 'http://localhost:8080' );
    define( 'WP_SITEURL', 'http://localhost:8080' );
}
```

---

## What Gets Detected

### GitHub Codespaces
✅ **Automatically uses:**
- Codespace URL with HTTPS
- Proper domain forwarding
- SSL/TLS certificates
- Port forwarding through GitHub

**Example:** `https://exciting-chainsaw-abc123-8080.app.github.dev`

### Local Development
✅ **Automatically uses:**
- Localhost URL with HTTP
- Direct port access
- No SSL/TLS overhead
- Fast reload times

**Example:** `http://localhost:8080`

---

## URLs Generated

### GitHub Codespaces

**WordPress Admin:**
```
https://[CODESPACE_NAME]-8080.[GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN]
https://exciting-chainsaw-abc123-8080.app.github.dev
```

**phpMyAdmin:**
```
https://[CODESPACE_NAME]-8081.[GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN]
https://exciting-chainsaw-abc123-8081.app.github.dev
```

### Local Development

**WordPress Admin:**
```
http://localhost:8080
```

**phpMyAdmin:**
```
http://localhost:8081
```

---

## Configuration Files Involved

### 1. `.devcontainer/devcontainer.json`
Passes environment variables to the container:
```json
"containerEnv": {
  "CODESPACE_NAME": "${localEnv:CODESPACE_NAME}",
  "GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN": "${localEnv:GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
}
```

### 2. `docker-compose.yml`
Passes variables to WordPress service:
```yaml
environment:
  CODESPACE_NAME: ${CODESPACE_NAME:-}
  GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN: ${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}
```

### 3. Docker Compose Variables in WordPress
Configures WordPress with detected URL:
```php
WORDPRESS_CONFIG_EXTRA: |
  if ( ! empty( getenv( 'CODESPACE_NAME' ) ) ) {
    define( 'WP_HOME', 'https://' . getenv( 'CODESPACE_NAME' ) . '-8080.' . ... );
    define( 'WP_SITEURL', 'https://' . getenv( 'CODESPACE_NAME' ) . '-8080.' . ... );
  }
```

---

## Script Detection

### Post-Start Script (`post-start-enhanced.sh`)
Also detects and displays the correct URL:

```bash
if [ -n "$CODESPACE_NAME" ]; then
    # GitHub Codespaces
    PFD=${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}
    WP_URL="https://${CODESPACE_NAME}-8080.${PFD}"
    echo "WordPress: $WP_URL"
else
    # Local environment
    echo "WordPress: http://localhost:8080"
fi
```

---

## Testing URL Detection

### Check if Running in Codespaces
```bash
echo "CODESPACE_NAME: $CODESPACE_NAME"
echo "GITHUB_CODESPACES: $GITHUB_CODESPACES"
echo "GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN: $GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN"
```

### Check WordPress Configuration
```bash
docker compose exec wordpress wp --allow-root option get home
docker compose exec wordpress wp --allow-root option get siteurl
```

### Verify in PHP
```bash
docker compose exec wordpress php -r "
echo 'WP_HOME: ' . WP_HOME . PHP_EOL;
echo 'WP_SITEURL: ' . WP_SITEURL . PHP_EOL;
echo 'CODESPACE_NAME: ' . getenv('CODESPACE_NAME') . PHP_EOL;
"
```

---

## Manual Override (If Needed)

If you need to manually set the WordPress URL:

### Option 1: Environment Variable
```bash
docker compose exec wordpress wp --allow-root option update home "https://your-custom-url"
docker compose exec wordpress wp --allow-root option update siteurl "https://your-custom-url"
```

### Option 2: Database Direct
```bash
docker compose exec db mysql -uwordpress -pwordpress wordpress -e "
UPDATE wp_options SET option_value='https://your-custom-url' WHERE option_name='home';
UPDATE wp_options SET option_value='https://your-custom-url' WHERE option_name='siteurl';
"
```

### Option 3: Edit docker-compose.yml
Modify the `WORDPRESS_CONFIG_EXTRA` section directly.

---

## Troubleshooting URLs

### WordPress Shows Wrong URL
1. Check detected URL in terminal: `docker compose logs phpmyadmin` (should show correct URL)
2. Verify WordPress configuration: `docker compose exec wordpress wp --allow-root option get home`
3. Check database: `docker compose exec db mysql -uwordpress -pwordpress -e "SELECT option_name, option_value FROM wp_options WHERE option_name IN ('home', 'siteurl');" wordpress`

### Codespace URL Not Detected
1. Verify environment variable: `echo $CODESPACE_NAME`
2. Check if Docker sees it: `docker compose exec wordpress env | grep CODESPACE`
3. Restart WordPress: `docker compose restart wordpress`

### Localhost Not Working
1. Verify port forwarding: `docker compose ps` (should show port mappings)
2. Check service is listening: `curl http://localhost:8080`
3. Check logs: `docker compose logs wordpress`

### HTTPS Issues in Codespaces
1. All Codespaces URLs use HTTPS automatically
2. Browsers handle certificates automatically
3. If needed, add to WordPress: `define( 'WP_HOME', 'https://...' );`

---

## Security Notes

### GitHub Codespaces
✅ **Secure by default:**
- Uses HTTPS with GitHub-managed certificates
- Port forwarding through secure channels
- Private by default (visibility can be configured)
- Access controlled via GitHub permissions

### Local Development
✅ **Development-only:**
- HTTP only (fine for local development)
- No external exposure by default
- Direct Docker network isolation

---

## Performance Considerations

### Codespaces (HTTPS)
- Slight overhead for SSL/TLS handshake
- Generally imperceptible for development
- Network latency depends on geographic location

### Local (HTTP)
- Minimal overhead
- Fastest response times
- Ideal for development and testing

---

## Additional Resources

- [GitHub Codespaces Documentation](https://docs.github.com/en/codespaces)
- [Dev Containers Specification](https://containers.dev/)
- [WordPress Configuration](https://developer.wordpress.org/plugins/wordpress-org/how-your-plugin-works-in-wordpress-core/)
- [Docker Environment Variables](https://docs.docker.com/compose/environment-variables/)
