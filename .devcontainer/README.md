# WPShadow Codespaces Auto-Configuration

This directory contains configuration for GitHub Codespaces and VS Code Dev Containers.

## What This Does

When you open this workspace in a Codespace, it will automatically:

### 1. Default Chat Agent
- Sets `@wpshadow` as the default chat participant
- All chat interactions will automatically use the WPShadow Agent
- Agent profile: `.github/agents/WPShadow Agent.agent.md`

### 2. Auto-Start Docker
- Starts all Docker containers defined in `docker-compose.yml`
- Containers:
  - `wpshadow-test` (WordPress test environment on port 9000)
  - `wpshadow-site` (Main WPShadow.com site on port 8080)
  - MySQL databases for both
- Waits for WordPress to be ready before showing success message

### 3. Environment Setup
- Installs Composer dependencies if needed
- Makes setup scripts executable
- Displays helpful information about:
  - Access URLs (with correct GitHub Codespaces URLs)
  - Admin credentials (admin/admin)
  - Key documentation files
  - Quick test commands

## Files in This Directory

- **`devcontainer.json`** - Main configuration file
  - Sets default chat agent
  - Configures port forwarding (8080, 9000)
  - Defines VS Code extensions to install
  - Specifies post-create and post-attach commands

- **`post-create.sh`** - Runs once when container is first created
  - Installs dependencies
  - Sets up initial environment

- **`post-attach.sh`** - Runs every time you attach to the workspace
  - Auto-starts Docker containers
  - Shows container status
  - Displays access URLs and credentials

## Testing the Configuration

To test if auto-start is working:

1. **Rebuild Container**: 
   - Press `F1` or `Ctrl+Shift+P`
   - Type "Codespaces: Rebuild Container"
   - Select and wait for rebuild

2. **Check Containers**:
   ```bash
   docker ps | grep wpshadow
   ```
   Should show 4 containers running.

3. **Check Chat Agent**:
   - Open GitHub Copilot Chat
   - Start typing - should default to `@wpshadow`

4. **Access WordPress**:
   - Test site: https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/
   - Main site: https://fictional-space-bassoon-qr65q7qqx4p2xvgr-8080.app.github.dev/

## Troubleshooting

### Containers Don't Auto-Start
```bash
# Manually start containers
cd /workspaces/wpshadow
docker-compose up -d
```

### Chat Agent Not Defaulting to @wpshadow
- Check `.vscode/settings.json` has `"github.copilot.chat.defaultParticipant": "wpshadow"`
- Reload VS Code window: `F1` → "Developer: Reload Window"

### Port Forwarding Issues
- Check Ports tab in VS Code (usually bottom panel)
- Ports 8080 and 9000 should be forwarded automatically
- If not, manually forward them

## Customization

Edit `devcontainer.json` to:
- Change default ports
- Add more VS Code extensions
- Modify container startup behavior
- Add additional post-create commands

## Reference Documentation

- [DOCKER_TESTING_ENVIRONMENT.md](../DOCKER_TESTING_ENVIRONMENT.md) - Complete Docker testing guide
- [.github/agents/WPShadow Agent.agent.md](../.github/agents/WPShadow%20Agent.agent.md) - Agent profile and instructions
- [docs/PRODUCT_PHILOSOPHY.md](../docs/PRODUCT_PHILOSOPHY.md) - WPShadow product philosophy

---

**Last Updated:** January 23, 2026
