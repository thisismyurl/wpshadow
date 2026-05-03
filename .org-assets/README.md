# WordPress.org listing assets

These files belong in the **`/assets/` directory at the SVN repository root**
on `plugins.svn.wordpress.org/thisismyurl-shadow/`, NOT inside `/trunk/`.
They are excluded from the plugin distribution zip via `.distignore`.

## Files

| File | Purpose | Spec |
|---|---|---|
| `banner-1544x500.png` | Listing-page hero banner (high-DPI) | 1544 × 500 |
| `banner-772x250.png`  | Listing-page hero banner (standard) | 772 × 250 |
| `icon-256x256.png`    | Plugin icon (high-DPI)              | 256 × 256 |
| `icon-128x128.png`    | Plugin icon (standard)              | 128 × 128 |

Screenshots (`screenshot-1.png` through `screenshot-4.png` referenced in
`readme.txt`) are NOT in this directory yet — they need to be captured from
the actual plugin admin UI in WordPress. Add them here once captured at
≤ 1280 × 960.

## How to publish to .org SVN

After the plugin is approved on WordPress.org and SVN access is granted:

```bash
# One-time SVN checkout
svn checkout https://plugins.svn.wordpress.org/thisismyurl-shadow svn-thisismyurl-shadow
cd svn-thisismyurl-shadow

# Copy the assets in
cp /workspaces/site-thisismyurl/wp-content/plugins/thisismyurl-shadow/.org-assets/{banner,icon,screenshot}-*.png assets/

# Commit
svn add assets/* --force
svn commit -m "Add listing assets: banner + icon"
```

The .org listing page will pick up the new assets within minutes.

## Regenerating

To regenerate the banner and icon (e.g. after a brand refresh):

```bash
cd .org-assets
set -a && source /workspaces/site-thisismyurl/.env && set +a
python3 generate.py
```

The script uses OpenAI `gpt-image-1` to produce the visual mark and
background, then composites the wordmark + tagline using Liberation Sans
Bold via PIL. Cost: ~$0.10 per full regeneration.

The raw OpenAI outputs are cached in `.org-assets/raw/` and are not
regenerated unless deleted, so re-running the script after a layout-only
change (font size, text position) is free.
