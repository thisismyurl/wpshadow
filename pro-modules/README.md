# WPShadow Pro Modules (Staging)

This directory contains modules that will eventually be moved to the **WPShadow Pro** plugin repository.

## Purpose

These modules are broken out into separate, self-contained units to:
- Make future extraction to Pro easier
- Keep code organized and maintainable
- Allow independent development/testing
- Prepare for module-based architecture

## Structure

```
pro-modules/
├── faq/                    # FAQ Module
│   ├── module.php          # Module definition & metadata
│   ├── module-faq.php      # Main FAQ functionality
│   └── assets/
│       └── faq-block.js    # Block editor JavaScript
├── kb/                     # Knowledge Base Module
│   ├── module.php          # Module definition & metadata
│   └── includes/           # KB functionality classes
│       ├── class-kb-formatter.php
│       ├── class-kb-article-generator.php
│       ├── class-kb-library.php
│       ├── class-kb-search.php
│       ├── class-training-provider.php
│       └── class-training-progress.php
└── README.md               # This file
```

## Development Mode

For development, these modules can be loaded from Core using:

```php
// In wpshadow.php - TEMPORARY for development only
if ( defined( 'WPSHADOW_DEV_MODE' ) && WPSHADOW_DEV_MODE ) {
    // Load pro modules from staging area
    require_once plugin_dir_path( __FILE__ ) . 'pro-modules/faq/module.php';
    WPShadow_Pro\Modules\FAQ\Module::init();
    
    require_once plugin_dir_path( __FILE__ ) . 'pro-modules/kb/module.php';
    WPShadow_Pro\Modules\KB\Module::init();
}
```

Enable dev mode in wp-config.php:
```php
define( 'WPSHADOW_DEV_MODE', true );
```

## Future Migration

When migrating to WPShadow Pro:

1. **Copy entire module directories:**
   ```bash
   cp -r pro-modules/faq wpshadow-pro/modules/faq
   cp -r pro-modules/kb wpshadow-pro/modules/kb
   ```

2. **Update namespaces (already correct):**
   - Already using `WPShadow_Pro\Modules\{ModuleName}\*`
   - No namespace changes needed

3. **Update asset paths:**
   - Change plugin_dir_url() references to Pro plugin URL
   - Update script/style handles to include 'wpshadow-pro-' prefix

4. **Register in Pro plugin:**
   ```php
   // In wpshadow-pro.php
   WPShadow_Pro\Module_Manager::register_module( 'WPShadow_Pro\Modules\FAQ\Module' );
   WPShadow_Pro\Module_Manager::register_module( 'WPShadow_Pro\Modules\KB\Module' );
   ```

## Module Pattern

Each module follows this pattern:

```php
namespace WPShadow_Pro\Modules\{ModuleName};

class Module {
    public static function get_info(): array {
        return [
            'id'          => 'module-id',
            'name'        => 'Module Name',
            'description' => 'What this module does',
            'icon'        => '📝',
            'requires'    => [], // Dependencies
            'version'     => '1.0.0',
        ];
    }
    
    public static function init(): void {
        // Load module files and initialize
    }
    
    public static function can_activate() {
        // Check requirements, return true or error message
    }
    
    public static function on_activate(): void {
        // Run on module activation
    }
    
    public static function on_deactivate(): void {
        // Run on module deactivation
    }
}
```

## Philosophy Alignment

These modules follow WPShadow philosophy:

✅ **Commandment #7:** Ridiculously good - better than premium alternatives  
✅ **Included in Pro** - no per-module pricing  
✅ **Optional activation** - users choose what they need  
✅ **Self-contained** - can be developed/tested independently

## Do NOT

- ❌ Create direct dependencies between modules
- ❌ Modify original files in includes/faq or includes/knowledge-base
- ❌ Add these to version control without `.gitignore` entry (staging only)
- ❌ Deploy to production (these are Pro features)

## See Also

- [docs/PRODUCT_BREAKOUT_PLAN.md](../docs/PRODUCT_BREAKOUT_PLAN.md) - Complete architecture
- [docs/PRODUCT_PHILOSOPHY.md](../docs/PRODUCT_PHILOSOPHY.md) - Philosophy & principles
