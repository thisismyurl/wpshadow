# GitHub Updates - Quick Setup (5 minutes)

## For WP Support Plugin - Private Repository Automatic Updates

### The Goal
Your WP Support plugin automatically checks for updates on GitHub and enables one-click updates in WordPress admin, just like plugins from WordPress.org.

---

## Setup Steps

### Step 1: Create GitHub Token (2 minutes)

1. Go to: https://github.com/settings/tokens
2. Click **"Generate new token"** → **"Generate new token (classic)"**
3. Fill in:
   - **Name:** WordPress Support Updates
   - **Expiration:** No expiration (or set 1 year if you prefer)
   - **Scope:** Check only `repo` ✓ (full control of private repos)
4. **Generate token**
5. **Copy the token immediately** (you won't see it again!)

### Step 2: Add Token to WordPress (2 minutes)

1. Go to WordPress Admin Dashboard
2. Click **"WP Support"** in left menu
3. Look for **"GitHub Updates"** tab (or go to: WP Support → Settings → GitHub Updates)
4. Paste your token in the **"Personal Access Token"** field
5. Click **"Save Token"**
6. See: ✓ **"GitHub token saved successfully"**

### Step 3: Test (1 minute)

1. Go to **Plugins** page in WordPress
2. Look for **WP Support (thisismyurl)** plugin
3. You should see an "Updates available" notice (if a newer release exists on GitHub)
4. Click "Update now" to test one-click update

---

## You're Done! 🎉

The plugin will now:
- ✅ Check GitHub for new releases every 12 hours automatically
- ✅ Show update notifications in WordPress admin
- ✅ Allow one-click updates from the Plugins dashboard

---

## Troubleshooting

### Updates Not Showing?

**Check 1:** Verify your GitHub repo has a "Releases" section
- Go to: https://github.com/thisismyurl/plugin-wp-support-thisismyurl/releases
- Make sure the latest release is published (not a draft)

**Check 2:** Verify token is saved
- WP Support → GitHub Updates
- Token should be saved (shown as dots)

**Check 3:** Force update check
- WordPress → Plugins page
- Refresh your browser (Ctrl+Shift+R)

### Still Not Working?

Check WordPress debug log:
```
/wp-content/debug.log
```

Look for lines with "WPS GitHub" to see what the error is.

---

## How It Works (Technical)

- Plugin hooks into WordPress's `site_transient_update_plugins`
- Every 12 hours, WordPress checks for updates
- We fetch the latest release from GitHub API
- Version comparison: If GitHub version > installed version, update available
- User clicks "Update now" in Plugins page
- WordPress downloads ZIP, extracts, and replaces files

---

## Security

Your token is:
- ✅ Stored securely in WordPress database
- ✅ Only sent to official GitHub API (github.com)
- ✅ Set with minimal scope (`repo` only)
- ✅ Never logged in plain text
- ✅ Displayed as dots in admin UI

You can regenerate or delete the token anytime on GitHub.

---

## GitHub Token Scopes

Our implementation uses **only `repo` scope**, which allows:
- ✅ Read private repos (to check releases)
- ❌ No write access
- ❌ No access to user data
- ❌ No access to other repos

---

## Reference

- **Setup Time:** 5 minutes
- **Cost:** Free (GitHub token, WordPress built-in)
- **Manual Override:** Can update anytime from GitHub.com
- **Rate Limit:** 5,000 API calls/hour (with token)

For more details, see: `GITHUB-UPDATES-GUIDE.md` in the plugin folder
