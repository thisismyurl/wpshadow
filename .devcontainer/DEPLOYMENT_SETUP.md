# WPShadow Deployment Setup Guide

## Quick Setup for Persistent Deployment

Your WPShadow Codespace can automatically configure deployment to your live site on every session start.

### Step 1: Save Your SSH Keys as Codespace Secrets

1. **Copy your private key:**
   ```bash
   cat ~/.ssh/greengeeks_rsa
   ```

2. **Copy your public key:**
   ```bash
   cat ~/.ssh/greengeeks_rsa.pub
   ```

3. **Add them as GitHub Codespace secrets:**
   - Go to: https://github.com/settings/codespaces
   - Click "New secret"
   - Add two secrets:
     - Name: `GREENGEEKS_SSH_PRIVATE_KEY`
       Value: (paste private key content - entire file including BEGIN/END lines)
     - Name: `GREENGEEKS_SSH_PUBLIC_KEY`
       Value: (paste public key content)

4. **Grant repository access:**
   - In the secrets page, select "thisismyurl/wpshadow" repository
   - Click "Update selection"

### Step 2: Rebuild Codespace (or just restart)

**Option A: Restart (keeps current session)**
- Close and reopen your Codespace
- SSH keys will auto-configure

**Option B: Rebuild (fresh start)**
- Command Palette (Ctrl+Shift+P)
- "Codespaces: Rebuild Container"

### What Gets Auto-Configured

On every Codespace start:
- ✅ SSH keys placed in `~/.ssh/greengeeks_rsa`
- ✅ Deployment config created at `.deploy-git.env`
- ✅ Git remote `greengeeks` added automatically
- ✅ Ready to deploy with `./deploy-git.sh`

### Manual Deployment

If you don't want to use secrets, the files are already in your workspace:
- `.deploy-git.env` - Your deployment configuration
- `~/.ssh/greengeeks_rsa` - Your SSH key (this session only)

**To deploy manually:**
```bash
./deploy-git.sh
```

### Troubleshooting

**SSH keys not working after restart:**
- Check secrets are added: https://github.com/settings/codespaces
- Verify repository access is granted
- Run manually: `bash .devcontainer/setup-deployment.sh`

**Can't find SSH keys to copy:**
```bash
# Your keys are here (this session):
cat ~/.ssh/greengeeks_rsa         # Private key
cat ~/.ssh/greengeeks_rsa.pub     # Public key
```

### Current Deployment Configuration

**Live Site:** https://wpshadow.com/
**WordPress Path:** `/home/sailmar1/public_html/wpshadow/`
**Server:** mtl202.greengeeks.net
**User:** sailmar1

**Deploy command:**
```bash
./deploy-git.sh
```

---

**Learn More:**
- [GitHub Codespaces Secrets Docs](https://docs.github.com/en/codespaces/managing-your-codespaces/managing-encrypted-secrets-for-your-codespaces)
- [WPShadow Development Guide](../docs/COMPLETE_SETUP_GUIDE.md)
