# Ghost Features System

## Overview

The Ghost Features System allows modules to declare their capabilities **even when not installed**, creating a discoverable ecosystem that shows users what features are available across the entire WP Support plugin suite.

## What Are Ghost Features?

Ghost features are "phantom" capabilities that:
- ✅ Appear in the dashboard even when the module is not installed
- ✅ Show users the benefits of installing additional modules
- ✅ Provide one-click installation links
- ✅ Create a natural upgrade funnel
- ✅ Never block functionality - only enhance awareness

## Architecture

```
┌─────────────────────────────────────────────────┐
│ Core Plugin (plugin-wp-support-thisismyurl)    │
│                                                  │
│  ┌────────────────────────────────────────┐   │
│  │ WPS_Module_Registry                     │   │
│  │ - Loads catalog of all modules          │   │
│  │ - Includes ghost_features array         │   │
│  └────────────────────────────────────────┘   │
│              ↓                                  │
│  ┌────────────────────────────────────────┐   │
│  │ WPS_Ghost_Features                      │   │
│  │ - Registers features from catalog       │   │
│  │ - Detects installed vs unavailable      │   │
│  │ - Provides render methods               │   │
│  └────────────────────────────────────────┘   │
│              ↓                                  │
│  ┌────────────────────────────────────────┐   │
│  │ Dashboard Widgets                       │   │
│  │ - Shows active features (green)         │   │
│  │ - Shows ghost features (yellow)         │   │
│  │ - One-click install buttons             │   │
│  └────────────────────────────────────────┘   │
└─────────────────────────────────────────────────┘
```

## For Module Developers: How to Declare Ghost Features

### Method 1: In Module Catalog Entry (Recommended)

Add a `ghost_features` array to your module's catalog entry in `/includes/class-wps-module-registry.php`:

```json
{
  "slug": "vault-support-thisismyurl",
  "type": "hub",
  "name": "Vault",
  "description": "Secure storage with encryption and compression",
  "version": "1.0.0",
  "ghost_features": [
    {
      "key": "encrypted_backups",
      "title": "Encrypted Backup Storage",
      "description": "AES-256 encrypted backups for GDPR compliance",
      "icon": "dashicons-lock",
      "category": "backup",
      "priority": 10,
      "benefits": [
        "Military-grade encryption",
        "GDPR and HIPAA compliance",
        "Secure offsite storage"
      ],
      "use_cases": [
        "Healthcare websites",
        "E-commerce sites",
        "Legal firms"
      ]
    },
    {
      "key": "cloud_offload",
      "title": "Automatic Cloud Offload",
      "description": "Sync to S3, Wasabi, or Backblaze automatically",
      "icon": "dashicons-cloud-upload",
      "category": "backup",
      "priority": 20,
      "benefits": [
        "Multiple cloud providers",
        "Disaster recovery",
        "Geographic redundancy"
      ]
    }
  ]
}
```

### Method 2: Hook into Ghost Features System

In your module's initialization:

```php
add_action( 'WPS_register_ghost_features', function() {
    WPS\CoreSupport\WPS_Ghost_Features::register_feature([
        'key'         => 'my_awesome_feature',
        'title'       => __( 'My Awesome Feature', 'my-module' ),
        'description' => __( 'Does something amazing', 'my-module' ),
        'icon'        => 'dashicons-star-filled',
        'category'    => 'media', // backup, media, security, storage, performance
        'priority'    => 10,
        'module_slug' => 'my-module-support-thisismyurl',
        'module_name' => 'My Module',
        'benefits'    => [
            __( 'Benefit 1', 'my-module' ),
            __( 'Benefit 2', 'my-module' ),
        ],
        'use_cases'   => [
            __( 'Use case 1', 'my-module' ),
            __( 'Use case 2', 'my-module' ),
        ],
    ]);
});
```

## Feature Schema

### Required Fields

| Field | Type | Description |
|-------|------|-------------|
| `key` | string | Unique identifier for the feature |
| `title` | string | Display name of the feature |
| `description` | string | One-sentence description (50-100 chars) |
| `module_slug` | string | Module slug (e.g., `vault-support-thisismyurl`) |

### Optional Fields

| Field | Type | Default | Description |
|-------|------|---------|-------------|
| `icon` | string | `dashicons-star-filled` | Dashicons class name |
| `category` | string | `general` | Category: `backup`, `media`, `security`, `storage`, `performance` |
| `priority` | int | `10` | Display order (lower = higher priority) |
| `module_name` | string | From module | Human-readable module name |
| `module_type` | string | `spoke` | Module type: `hub`, `spoke`, `core` |
| `benefits` | array | `[]` | Bullet points explaining benefits |
| `use_cases` | array | `[]` | Real-world use cases |

## Feature Categories

### `backup`
Backup, restore, verification, disaster recovery

### `media`
Image processing, format conversion, media library management

### `security`
Encryption, sanitization, access control, compliance

### `storage`
File storage, compression, deduplication, cloud offload

### `performance`
Speed optimization, caching, lazy loading, CDN

### `general`
Catch-all for features that don't fit other categories

## Usage Examples

### Display All Features by Category

```php
// Show all backup features (installed and ghost)
WPS\CoreSupport\WPS_Ghost_Features::render_category_features( 'backup', [
    'include_installed' => true,
    'show_install_button' => true,
    'show_benefits' => true,
    'columns' => 2,
]);
```

### Display Dashboard Summary

```php
// Show complete features overview with stats
WPS\CoreSupport\WPS_Ghost_Features::render_dashboard_summary();
```

### Get Ghost Features Programmatically

```php
// Get only unavailable features
$ghost_features = WPS\CoreSupport\WPS_Ghost_Features::get_ghost_features();

// Get features for specific module
$vault_features = WPS\CoreSupport\WPS_Ghost_Features::get_module_features( 'vault-support-thisismyurl' );

// Get backup category features
$backup_features = WPS\CoreSupport\WPS_Ghost_Features::get_features_by_category( 'backup', false );

// Check if specific feature is available
$is_available = WPS\CoreSupport\WPS_Ghost_Features::has_feature( 'encrypted_backups' );
```

## Visual Design

### Active Feature Card (Installed Module)
```
┌──────────────────────────────────────────┐
│ ✓ Encrypted Backup Storage      [ACTIVE]│
│   from Vault module                      │
│                                           │
│ AES-256 encrypted backups for GDPR       │
│ compliance and data protection.          │
│                                           │
│ • Military-grade encryption               │
│ • GDPR and HIPAA compliance               │
│ • Secure offsite storage                  │
└──────────────────────────────────────────┘
   Green border, full opacity
```

### Ghost Feature Card (Not Installed)
```
┌──────────────────────────────────────────┐
│ 🔒 Encrypted Backup Storage    [INSTALL] │
│   from Vault module                      │
│                                           │
│ AES-256 encrypted backups for GDPR       │
│ compliance and data protection.          │
│                                           │
│ • Military-grade encryption               │
│ • GDPR and HIPAA compliance               │
│ • Secure offsite storage                  │
│                                           │
│ ──────────────────────────────────────   │
│ [Install Vault]  [Learn More]            │
└──────────────────────────────────────────┘
   Yellow border, 70% opacity
```

## Best Practices

### ✅ DO

- Keep descriptions concise (50-100 characters)
- List 2-4 benefits per feature
- Use action-oriented language
- Include real use cases
- Set appropriate priorities (10-100 range)
- Use standard Dashicons

### ❌ DON'T

- Make ghost features sound like they're active
- Use salesy language or hype
- Create fake features that don't exist
- Block functionality behind ghost features
- Spam users with too many features (5-8 max per module)
- Use misleading descriptions

## Integration with Feature Detector

Ghost Features work seamlessly with the Feature Detector:

```php
// Feature Detector checks if module is installed
$has_vault = WPS_Feature_Detector::has_vault();

// Ghost Features automatically reflects this
$vault_features = WPS_Ghost_Features::get_module_features( 'vault-support-thisismyurl' );
// Each feature has 'is_available' => true/false
```

## Performance Considerations

- Ghost features are cached in memory
- Catalog loaded once per request
- Module detection uses WordPress plugin API (fast)
- No database queries required
- Lazy-loaded when needed

## Future Enhancements

- [ ] Feature search/filtering
- [ ] Compare features across modules
- [ ] Feature dependency graphs
- [ ] User-specific feature recommendations
- [ ] A/B testing for feature messaging
- [ ] Analytics on feature discovery

## Example Implementation

See `/includes/ghost-features-catalog.php` for complete examples of:
- Vault module ghost features
- Media module ghost features
- Image module ghost features
