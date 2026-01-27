# WPShadow Release Process

## Overview
The `.gitattributes` file automatically excludes development files from GitHub releases. When you create a release, users get a clean, production-ready plugin package.

## What's Included in Releases

✅ **Essential Plugin Files:**
- `wpshadow.php` (main plugin file)
- `includes/` (all functionality - 2,960+ files)
- `assets/` (CSS, JS, images)
- `readme.txt` (WordPress.org standard format)
- `LICENSE` (GPL)
- `README.md` (GitHub documentation)

## What's Excluded from Releases

❌ **Development Files:**
- `.github/` (workflows, issue templates)
- `.vscode/` (VS Code settings)
- `.git*` files (gitignore, gitattributes)
- `docs/` (60+ internal documentation files)
- `vendor/` (Composer dev dependencies)
- `composer.json` and `composer.lock`

❌ **Development Setup:**
- `.devcontainer/`
- `tests/`
- `wp-config-extra.php`
- `wp-content/` (test site)

❌ **Build/Development Tools:**
- `scripts/`
- `backup-utility-scripts/`
- `tmp/`
- `tools/` (build/utility scripts, not user-facing tools)
- Development markdown files (DESIGN_MIGRATION_COMPLETE.md, etc.)

❌ **Separate Products:**
- `pro-modules/` (WPShadow Pro is a separate addon)

## How to Create a Release

### Step 1: Update Version Number
Update version in two places:
1. `wpshadow.php` (line 5): `Version: 1.2601.2112`
2. `wpshadow.php` (line 13): `define( 'WPSHADOW_VERSION', '1.2601.2112' );`

Version format: `1.YYMM.DDHH` (e.g., 1.2601.2112 = Jan 21, 2026, 21:00)

### Step 2: Update Changelog
Update `readme.txt` with release notes for WordPress.org users.

### Step 3: Commit Changes
```bash
git add wpshadow.php readme.txt
git commit -m "chore: bump version to 1.2601.XXXX"
git push origin main
```

### Step 4: Create GitHub Release
1. Go to: https://github.com/thisismyurl/wpshadow/releases/new
2. Click "Choose a tag" → Create new tag: `v1.2601.XXXX`
3. Set "Target": `main` branch
4. **Release title**: `WPShadow v1.2601.XXXX`
5. **Description**: Add release notes (features, fixes, improvements)
6. Click "Generate release notes" to auto-populate from commits
7. ✅ Check "Set as the latest release"
8. Click "Publish release"

### Step 5: Verify Release Package
1. Download the release ZIP from GitHub
2. Extract and verify:
   - No `docs/` folder
   - No `vendor/` folder
   - No `.github/` folder
   - Has `wpshadow.php`, `includes/`, `assets/`, `readme.txt`, `LICENSE`

## Testing the Release Package Locally

```bash
# Create test archive (simulates GitHub release)
git archive --format=zip HEAD -o /tmp/wpshadow-test.zip

# Extract to test WordPress site
unzip /tmp/wpshadow-test.zip -d /path/to/wordpress/wp-content/plugins/wpshadow

# Or count files to verify size
unzip -l /tmp/wpshadow-test.zip | tail -1
# Should show ~2,962 files, ~6.4MB
```

## Release Checklist

- [ ] Version updated in `wpshadow.php` (2 places)
- [ ] Changelog updated in `readme.txt`
- [ ] All changes committed and pushed
- [ ] GitHub release created with tag
- [ ] Release package verified (no dev files)
- [ ] Release notes published
- [ ] WordPress.org updated (if applicable)

## Philosophy Compliance

Every release embodies our 11 commandments:
- ✅ Free forever (Commandment #2)
- ✅ No artificial limits (Commandment #2)
- ✅ Educational (Commandments #5, #6)
- ✅ Privacy-first (Commandment #10)
- ✅ Ridiculously good (Commandment #7)

## Troubleshooting

**Problem:** Development files appearing in release
**Solution:** Check `.gitattributes` syntax, ensure `export-ignore` is set

**Problem:** Essential files missing from release
**Solution:** Remove them from `.gitattributes` or don't mark with `export-ignore`

**Problem:** Release too large (>10MB)
**Solution:** Verify docs/, vendor/, and other dev files are excluded

## Current Release Status

**Latest Version:** 1.2601.2112  
**Last Release Date:** Pending  
**Next Version:** 1.2601.XXXX (today's release)

---

**Note:** The `.gitattributes` file is already committed and active. No cleanup needed before release—just create the release on GitHub!
