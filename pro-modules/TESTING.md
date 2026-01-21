# Pro Modules: Development Testing

## Status Check

After enabling `WPSHADOW_DEV_MODE` in wp-config-extra.php, verify modules loaded:

### FAQ Module
- **Post Type:** `wpshadow_faq`
- **Taxonomy:** `faq_topic`
- **Block:** `wpshadow/faq-list`
- **JS Asset:** `pro-modules/faq/assets/faq-block.js`
- **Test:** Post 207 should render FAQ block without error

### KB Module
- **Classes:** KB_Formatter, KB_Article_Generator, KB_Library, KB_Search, Training_Provider, Training_Progress
- **Post Type:** `wpshadow_kb` (if registered)
- **Test:** Check if KB classes are loaded

## Testing Checklist

- [ ] FAQ post type appears in admin menu
- [ ] FAQ block available in Block Editor
- [ ] Post 207 renders without "block not supported" error
- [ ] FAQ posts 210-214 are accessible
- [ ] FAQ block shows actual FAQ content (ServerSideRender)

## Verification Commands

```bash
# Check if pro-modules exists in container
docker exec wpshadow-test ls -la /var/www/html/wp-content/plugins/wpshadow/pro-modules/

# Check PHP errors
docker logs wpshadow-test 2>&1 | grep -i "error\|warning" | tail -20

# Check if WPSHADOW_DEV_MODE is defined
docker exec wpshadow-test grep -r "WPSHADOW_DEV_MODE" /var/www/html/wp-config*.php
```

## Expected Behavior

### Before Dev Mode
- ❌ FAQ block shows "This block contains unexpected or invalid content"
- ❌ Post type `wpshadow_faq` not registered
- ❌ Block `wpshadow/faq-list` not available

### After Dev Mode
- ✅ FAQ block renders with ServerSideRender
- ✅ Post type `wpshadow_faq` in admin menu
- ✅ Block `wpshadow/faq-list` available in inserter
- ✅ Post 207 displays FAQ block content
- ✅ FAQ posts 210-214 visible in FAQ admin

## File Structure

```
pro-modules/
├── faq/
│   ├── module.php              # Module metadata & loader
│   ├── module-faq.php          # FAQ functionality (copied from includes/faq/)
│   └── assets/
│       └── faq-block.js        # Block editor JS
└── kb/
    ├── module.php              # Module metadata & loader
    └── includes/               # KB classes (copied from includes/knowledge-base/)
        ├── class-kb-formatter.php
        ├── class-kb-article-generator.php
        ├── class-kb-library.php
        ├── class-kb-search.php
        ├── class-training-provider.php
        └── class-training-progress.php
```

## Troubleshooting

### FAQ Block Not Found

**Symptom:** "This block contains unexpected or invalid content"

**Causes:**
1. WPSHADOW_DEV_MODE not defined in wp-config-extra.php
2. Container not restarted after adding define
3. Block JS asset path incorrect
4. Namespace mismatch

**Solution:**
```bash
# 1. Check dev mode is enabled
docker exec wpshadow-test grep WPSHADOW_DEV_MODE /var/www/html/wp-config-extra.php

# 2. Restart container
docker restart wpshadow-test

# 3. Check if FAQ module loads
docker exec wpshadow-test ls -la /var/www/html/wp-content/plugins/wpshadow/pro-modules/faq/

# 4. Check PHP errors
docker logs wpshadow-test 2>&1 | grep -A5 "Fatal\|Parse"
```

### Module Not Loading

**Check wpshadow.php around line 1260:**
```php
if ( defined( 'WPSHADOW_DEV_MODE' ) && WPSHADOW_DEV_MODE ) {
    // FAQ Module (staged in pro-modules/faq/)
    if ( file_exists( plugin_dir_path( __FILE__ ) . 'pro-modules/faq/module.php' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'pro-modules/faq/module.php';
        \WPShadow_Pro\Modules\FAQ\Module::init();
    }
}
```

### Namespace Issues

**FAQ Module uses:** `WPShadow_Pro\Modules\FAQ\`
**KB Module uses:** `WPShadow_Pro\Modules\KB\`

**Inside module-faq.php:** `namespace WPShadow\FAQ;`
**Inside module.php:** `namespace WPShadow_Pro\Modules\FAQ;`

This is intentional - `module.php` is the Pro wrapper, `module-faq.php` is the actual feature code.

## Next Steps

Once modules are confirmed working:

1. ✅ Test FAQ block in post 207
2. ✅ Test creating new FAQ posts
3. ✅ Test FAQ topic taxonomy
4. ⏳ Create similar modules for TOC, SEO, Academy
5. ⏳ Move entire pro-modules/ directory to wpshadow-pro repo
6. ⏳ Update Pro plugin to use Module Manager
7. ⏳ Remove pro-modules/ from core repo

## Philosophy Alignment

✅ **Development Separation:** Modules isolated for easy extraction  
✅ **No Production Use:** Dev mode only, not deployed to users  
✅ **Pro Value:** These features justify Pro subscription  
✅ **Free Core:** Core remains focused on diagnostics/treatments
