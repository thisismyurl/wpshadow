# WPShadow Product Architecture: Core → Pro → Modules

**Version:** 2.0  
**Date:** January 21, 2026  
**Status:** Implementation Plan

---

## Overview

This document outlines the tiered product architecture for WPShadow. Content-focused features are **modules within WPShadow Pro**, not separate plugins. This creates a clear upgrade path and simplifies the user experience.

---

## Product Architecture Strategy

### The Upgrade Path

```
┌─────────────────────────────────────────────────────────────┐
│ 1. WPShadow Core (FREE)                                     │
│    - 57 Diagnostics                                         │
│    - 44 Treatments                                          │
│    - Kanban Board                                           │
│    - Workflow Automation (basic)                            │
│    - KPI Tracking                                           │
│    WordPress.org + wpshadow.com                             │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 2. WPShadow Guardian (SaaS/AI)                              │
│    - Cloud AI diagnostics                                   │
│    - Cross-site monitoring                                  │
│    - Automated recommendations                              │
│    Registration Required + Paid Plans                       │
│    https://guardian.wpshadow.com                            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 3. WPShadow Pro (Commercial Plugin)                         │
│    - Unlimited workflows                                    │
│    - Client portal (white-label)                            │
│    - Agency dashboard                                       │
│    - Custom branding                                        │
│    - Priority support                                       │
│    - **Module Manager** (one-click install)                 │
│    $199/year - wpshadow.com                                 │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│ 4. WPShadow Pro Modules (Included in Pro)                   │
│    ✓ FAQ Module                                             │
│    ✓ Knowledge Base Module                                  │
│    ✓ Academy Module                                         │
│    ✓ Table of Contents Module                               │
│    ✓ SEO/Schema Module                                      │
│    Activated via WPShadow Pro → Modules page                │
└─────────────────────────────────────────────────────────────┘
```
**Repository:** thisismyurl/wpshadow  
**Distribution:** WordPress.org + wpshadow.com  
**License:** GPL v2+  
**Cost:** FREE FOREVER

**What Stays in Core:**
- 57 Diagnostics (security, performance, health)
- 44 Treatments (auto-fix with backup/undo)
- Kanban Board (finding management)
- Workflow Automation (IF/THEN rules)
- KPI Tracking (time saved, value delivered)
- Activity Logger (audit trail)
- 1200+ Contextual Tooltips
- Dashboard & Admin UI
- Settings & Preferences
- Multisite Support

**What's Removed:**
- FAQ custom post type → Moved to WPShadow FAQ plugin
- Knowledge Base features → Moved to WPShadow KB plugin
- Training/Academy integration → Moved to WPShadow Academy plugin
- SEO/Schema features → Moved to WPShadow SEO plugin

**Philosophy Alignment:**
✅ Everything local is free forever  
✅ No registration required  
✅ Full functionality without upsells

---

## 1. WPShadow Core (FREE)

### WPShadow Core (Main Plugin)
**Repository:** thisismyurl/wpshadow  
**Distribution:** WordPress.org + wpshadow.com  
**License:** GPL v2+  
**Cost:** FREE FOREVER

**What Stays in Core:**
- 57 Diagnostics (security, performance, health)
- 44 Treatments (auto-fix with backup/undo)
- Kanban Board (finding management)
- Workflow Automation (basic: 10 workflows max)
- KPI Tracking (time saved, value delivered)
- Activity Logger (audit trail)
- 1200+ Contextual Tooltips
- Dashboard & Admin UI
- Settings & Preferences
- Multisite Support

**What's Removed:**
- ~~FAQ custom post type~~ → Moved to Pro Module
- ~~Knowledge Base features~~ → Moved to Pro Module
- ~~Training/Academy integration~~ → Moved to Pro Module
- ~~SEO/Schema features~~ → Moved to Pro Module
- ~~Table of Contents~~ → Moved to Pro Module

**Philosophy Alignment:**
✅ Everything local is free forever  
✅ No registration required  
✅ Full functionality without upsells  
✅ Generous free tier (10 workflows)

---

## 2. WPShadow Guardian (SaaS/AI)

### Description
Cloud-based AI service for cross-site monitoring, automated diagnostics, and intelligent recommendations.

### Repository
**Private:** thisismyurl/wpshadow-guardian-client (WordPress plugin)  
**SaaS:** guardian.wpshadow.com (API service)

### Features
- AI-powered diagnostics across multiple sites
- Cross-site pattern detection
- Automated health monitoring
- Predictive issue detection
- Email alerts & notifications
- Cloud backup integration
- Performance benchmarking
- Security threat intelligence

### Pricing
- **Free Tier:** 1 site, daily scans, basic alerts
- **Starter:** $9/month - 5 sites, hourly scans
- **Professional:** $29/month - 25 sites, real-time monitoring
- **Agency:** $99/month - Unlimited sites, priority support

### Registration
- Requires free account at guardian.wpshadow.com
- API key stored in local site
- Data sent to cloud for analysis
- Privacy-first: minimal data collection, full transparency

### Philosophy Alignment
✅ **Commandment #3:** Register not pay - free tier available  
✅ **Commandment #10:** Privacy first - explicit consent, transparent data use  
✅ **Commandment #2:** Generous free tier (1 site forever free)

---

## 3. WPShadow Pro (Commercial Plugin)

### Description
Annual subscription plugin for agency/enterprise users with advanced automation, white-label options, client management, and **Module Manager**.

### Repository
**Private:** thisismyurl/wpshadow-pro

### Core Pro Features
**What Pro Adds to Core:**
- Unlimited workflow automation (Core limited to 10)
- Client portal (white-labeled)
- Agency dashboard (multi-client management)
- Custom branding options
- Advanced reporting/analytics
- Priority support
- License management
- Automated updates
- **Module Manager** (one-click install/activate modules)

**What Pro Does NOT Paywall:**
- Core diagnostics (still free)
- Core treatments (still free)
- Basic workflows (10 free in Core)
- Basic KPI tracking (still free)

### Module Manager UI

```
WPShadow Pro → Modules

┌────────────────────────────────────────────────────────────┐
│ WPShadow Pro Modules                                       │
│                                                            │
│ Manage optional modules included with your Pro license.   │
│ Click "Activate" to enable a module.                      │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ 📝 FAQ Module                                    [Activate]│
│ Create and manage FAQ content with Schema.org markup      │
│ Status: Not Activated                                     │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ 📚 Knowledge Base Module                         [Activate]│
│ Build comprehensive documentation for your WordPress site │
│ Status: Not Activated                                     │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ 🎓 Academy Module                               [Activated]│
│ Sensei LMS integration for training courses               │
│ Status: Active                              [Deactivate]  │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ 📑 Table of Contents Module                      [Activate]│
│ Auto-generate TOC from headings in posts/pages            │
│ Status: Not Activated                                     │
└────────────────────────────────────────────────────────────┘

┌────────────────────────────────────────────────────────────┐
│ 🔍 SEO/Schema Module                             [Activate]│
│ Schema.org structured data for better search results      │
│ Status: Not Activated                                     │
└────────────────────────────────────────────────────────────┘
```

### Technical Architecture

```php
// Pro provides module registration hook
do_action('wpshadow_pro_loaded');

// Pro includes all module files but doesn't activate
require_once WPSHADOW_PRO_PATH . 'modules/faq/module.php';
require_once WPSHADOW_PRO_PATH . 'modules/kb/module.php';
require_once WPSHADOW_PRO_PATH . 'modules/academy/module.php';
require_once WPSHADOW_PRO_PATH . 'modules/toc/module.php';
require_once WPSHADOW_PRO_PATH . 'modules/seo/module.php';

// Each module checks if it's activated
if (get_option('wpshadow_pro_module_faq_active')) {
    WPShadow_Pro\Modules\FAQ\Module::init();
}
```

### Distribution
- Annual subscription ($199/year)
- Available at wpshadow.com/pro
- Requires WPShadow Core (free)
- All 5 modules included in subscription
- No per-module pricing (all or nothing)

### Philosophy Alignment
✅ **Commandment #2:** Only paid because it serves agencies (B2B value)  
✅ **Commandment #3:** Core free forever, Pro for professionals  
✅ **Commandment #7:** Ridiculously good - modules included, not upsold  
✅ **Commandment #10:** Privacy-first - all data stays on client server

---

## 4. WPShadow Pro Modules

Modules are **included** in WPShadow Pro subscription. Users activate them via the Module Manager. They are NOT separate plugins and do NOT appear on the Plugins page.

### Module 1: FAQ

### Module 1: FAQ

### Description
Module for managing FAQ content with Schema.org markup, reusable FAQ blocks, and topic organization.

### Location in Pro
**Path:** `wpshadow-pro/modules/faq/`

### Features
- FAQ custom post type (`wpshadow_faq`)
- FAQ Topics taxonomy (`faq_topic`)
- FAQ List block (`wpshadow/faq-list`)
- ServerSideRender in Block Editor (shows actual FAQs)
- Schema.org FAQPage + Question/Answer markup
- Meta fields: tooltip, order, related links
- Reusable across posts/pages/CPTs

### Technical Details
```php
// In Pro's module manager
if (get_option('wpshadow_pro_module_faq_active')) {
    require_once WPSHADOW_PRO_PATH . 'modules/faq/class-faq-module.php';
    WPShadow_Pro\Modules\FAQ\FAQ_Module::init();
}

// Module hooks into Core
add_action('wpshadow_core_loaded', function() {
    // FAQ module adds submenu to WPShadow menu
    add_submenu_page('wpshadow', 'FAQs', 'FAQs', 'manage_options', 'wpshadow-faqs', [FAQ_Module::class, 'render']);
});
```

### Files to Move
```
FROM: includes/faq/class-faq-post-type.php
TO:   wpshadow-pro/modules/faq/class-faq-module.php

FROM: assets/js/faq-block.js
TO:   wpshadow-pro/modules/faq/assets/faq-block.js
```

### Philosophy Alignment
✅ **Commandment #7:** Ridiculously good - better than premium FAQ plugins, included in Pro  
✅ **Commandment #5:** Drive to KB - FAQs link to knowledge base  
✅ Part of Pro subscription, not separate purchase

---

### Module 2: Knowledge Base (KB)

### Module 2: Knowledge Base (KB)

### Description
Module for creating WordPress knowledge bases with KB articles, search, categories, and training integration.

### Location in Pro
**Path:** `wpshadow-pro/modules/kb/`

### Features
- KB Article custom post type (`wpshadow_kb`)
- KB Categories/Keywords/Glossary taxonomies
- Meta fields: read time, difficulty, category, last updated, points
- SEO fields: Google snippet, social preview
- Table of Contents auto-generation
- Related articles widget
- KB search functionality
- Training video integration hooks

### Technical Details
```php
// Module registers CPT when activated
if (get_option('wpshadow_pro_module_kb_active')) {
    require_once WPSHADOW_PRO_PATH . 'modules/kb/class-kb-module.php';
    WPShadow_Pro\Modules\KB\KB_Module::init();
}

// Core diagnostics can link to KB articles
$kb_url = admin_url('post-new.php?post_type=wpshadow_kb&diagnostic=ssl-check');
```

### Files to Move
```
FROM: includes/knowledge-base/class-kb-formatter.php
TO:   wpshadow-pro/modules/kb/includes/class-kb-formatter.php

FROM: includes/knowledge-base/class-kb-article-generator.php
TO:   wpshadow-pro/modules/kb/includes/class-kb-generator.php

FROM: includes/knowledge-base/class-kb-library.php
TO:   wpshadow-pro/modules/kb/includes/class-kb-library.php

FROM: includes/knowledge-base/class-kb-search.php
TO:   wpshadow-pro/modules/kb/includes/class-kb-search.php
```

### Philosophy Alignment
✅ **Commandment #5:** Drive to KB - free knowledge base for users with Pro  
✅ **Commandment #8:** Inspire confidence - learn while managing  
✅ **Commandment #11:** Talk-worthy - better than premium KB plugins, included

---

### Module 3: Academy

### Module 3: Academy

### Description
Module that integrates Sensei LMS for WordPress training courses, tracks progress, awards certificates, and gamifies learning.

### Location in Pro
**Path:** `wpshadow-pro/modules/academy/`

### Features
- Sensei LMS integration
- Course progress tracking
- Certificate generation
- Points/badges system
- Training video embedding
- Learning paths for different skill levels
- Quiz integration
- Community forum integration

### Technical Details
```php
// Module checks for Sensei dependency
if (get_option('wpshadow_pro_module_academy_active')) {
    if (!class_exists('Sensei_Main')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-warning"><p>';
            echo 'WPShadow Academy module requires Sensei LMS. ';
            echo '<a href="' . admin_url('plugin-install.php?s=sensei&tab=search') . '">Install Sensei</a>';
            echo '</p></div>';
        });
        return;
    }
    
    require_once WPSHADOW_PRO_PATH . 'modules/academy/class-academy-module.php';
    WPShadow_Pro\Modules\Academy\Academy_Module::init();
}
```

### Files to Move
```
FROM: includes/knowledge-base/class-training-provider.php
TO:   wpshadow-pro/modules/academy/includes/class-training-provider.php

FROM: includes/knowledge-base/class-training-progress.php
TO:   wpshadow-pro/modules/academy/includes/class-training-progress.php
```

### Philosophy Alignment
✅ **Commandment #6:** Drive to training - education-first approach  
✅ **Commandment #9:** Show value - certificates prove knowledge gained  
✅ Included in Pro, not separate purchase

---

### Module 4: Table of Contents

### Module 4: Table of Contents

### Description
Module for auto-generating table of contents from headings in posts/pages/CPTs with anchor links, smooth scrolling, and nested navigation.

### Location in Pro
**Path:** `wpshadow-pro/modules/toc/`

### Features
- Auto-detect headings (H2-H6)
- Generate anchor IDs
- Nested TOC markup
- Smooth scroll to anchors
- Sticky TOC option
- Collapsible sections
- "Jump to top" button
- Works with any post type

### Technical Details
```php
// Block: wpshadow/table-of-contents
if (get_option('wpshadow_pro_module_toc_active')) {
    register_block_type('wpshadow/table-of-contents', [
        'render_callback' => 'WPShadow_Pro\Modules\TOC\Module::render',
        'attributes' => [
            'maxDepth' => ['type' => 'number', 'default' => 3],
            'showNumbers' => ['type' => 'boolean', 'default' => true],
            'position' => ['type' => 'string', 'default' => 'top']
        ]
    ]);
}
```

### Philosophy Alignment
✅ **Commandment #7:** Ridiculously good - better than Easy TOC, included in Pro  
✅ **Commandment #8:** Inspire confidence - professional documentation  
✅ Enhances KB module articles

---

### Module 5: SEO/Schema

### Module 5: SEO/Schema

### Description
Module for Schema.org structured data markup specifically for technical content: FAQs, how-to guides, breadcrumbs, articles, courses.

### Location in Pro
**Path:** `wpshadow-pro/modules/seo/`

### Features
- FAQPage schema (when FAQ module active)
- Article schema (when KB module active)
- HowTo schema (for KB tutorials)
- Course schema (when Academy module active)
- BreadcrumbList schema
- Organization schema
- Schema validation in admin
- Google Rich Results preview

### Technical Details
```php
// SEO module detects other active modules and adds appropriate schema
if (get_option('wpshadow_pro_module_seo_active')) {
    add_action('wp_head', function() {
        if (is_singular('wpshadow_faq') && get_option('wpshadow_pro_module_faq_active')) {
            WPShadow_Pro\Modules\SEO\Schema::output_faq_schema(get_the_ID());
        }
        if (is_singular('wpshadow_kb') && get_option('wpshadow_pro_module_kb_active')) {
            WPShadow_Pro\Modules\SEO\Schema::output_article_schema(get_the_ID());
        }
        if (is_singular('course') && get_option('wpshadow_pro_module_academy_active')) {
            WPShadow_Pro\Modules\SEO\Schema::output_course_schema(get_the_ID());
        }
    });
}
```

### Philosophy Alignment
✅ **Commandment #7:** Ridiculously good - focused, fast, no bloat  
✅ **Commandment #9:** Show value - rich results = more traffic  
✅ Works in harmony with other modules

---

## Implementation Strategy

### Pro Module Architecture

**Directory Structure:**
```
wpshadow-pro/
├── wpshadow-pro.php           # Pro loader
├── includes/
│   ├── class-license-client.php
│   ├── class-module-manager.php  # NEW: Manages module activation
│   ├── class-agency-features.php
│   └── class-white-label.php
├── modules/                    # NEW: Module directory
│   ├── faq/
│   │   ├── module.php         # Module registration
│   │   ├── class-faq-module.php
│   │   └── assets/
│   ├── kb/
│   │   ├── module.php
│   │   ├── includes/
│   │   └── assets/
│   ├── academy/
│   │   ├── module.php
│   │   ├── includes/
│   │   └── assets/
│   ├── toc/
│   │   ├── module.php
│   │   └── assets/
│   └── seo/
│       ├── module.php
│       └── includes/
├── assets/
└── views/
    └── module-manager.php      # NEW: Module activation UI
```

**Module Registration Pattern:**
```php
// modules/faq/module.php
namespace WPShadow_Pro\Modules\FAQ;

class Module {
    public static function get_info() {
        return [
            'id' => 'faq',
            'name' => 'FAQ Module',
            'description' => 'Create and manage FAQ content with Schema.org markup',
            'icon' => '📝',
            'requires' => [], // No dependencies
            'version' => '1.0.0'
        ];
    }
    
    public static function init() {
        require_once __DIR__ . '/class-faq-module.php';
        FAQ_Module::register();
    }
}
```

**Module Manager Class:**
```php
// includes/class-module-manager.php
namespace WPShadow_Pro;

class Module_Manager {
    private static $modules = [];
    
    public static function register_module($module_class) {
        $info = $module_class::get_info();
        self::$modules[$info['id']] = [
            'class' => $module_class,
            'info' => $info,
            'active' => get_option("wpshadow_pro_module_{$info['id']}_active", false)
        ];
    }
    
    public static function activate_module($module_id) {
        if (!isset(self::$modules[$module_id])) {
            return false;
        }
        
        update_option("wpshadow_pro_module_{$module_id}_active", true);
        
        // Initialize module
        $module_class = self::$modules[$module_id]['class'];
        $module_class::init();
        
        return true;
    }
    
    public static function deactivate_module($module_id) {
        update_option("wpshadow_pro_module_{$module_id}_active", false);
        return true;
    }
    
    public static function get_all_modules() {
        return self::$modules;
    }
}
```

**Pro Main File:**
```php
// wpshadow-pro.php
if (!defined('ABSPATH')) exit;

// Require Core
if (!class_exists('WPShadow_Core')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo 'WPShadow Pro requires WPShadow Core (free plugin). ';
        echo '<a href="' . admin_url('plugin-install.php?s=wpshadow&tab=search') . '">Install WPShadow Core</a>';
        echo '</p></div>';
    });
    return;
}

// Load module manager
require_once __DIR__ . '/includes/class-module-manager.php';

// Register all modules (but don't activate yet)
require_once __DIR__ . '/modules/faq/module.php';
require_once __DIR__ . '/modules/kb/module.php';
require_once __DIR__ . '/modules/academy/module.php';
require_once __DIR__ . '/modules/toc/module.php';
require_once __DIR__ . '/modules/seo/module.php';

WPShadow_Pro\Module_Manager::register_module('WPShadow_Pro\Modules\FAQ\Module');
WPShadow_Pro\Module_Manager::register_module('WPShadow_Pro\Modules\KB\Module');
WPShadow_Pro\Module_Manager::register_module('WPShadow_Pro\Modules\Academy\Module');
WPShadow_Pro\Module_Manager::register_module('WPShadow_Pro\Modules\TOC\Module');
WPShadow_Pro\Module_Manager::register_module('WPShadow_Pro\Modules\SEO\Module');

// Initialize active modules
add_action('plugins_loaded', function() {
    foreach (WPShadow_Pro\Module_Manager::get_all_modules() as $module) {
        if ($module['active']) {
            $module['class']::init();
        }
    }
});

// Add Module Manager menu
add_action('admin_menu', function() {
    add_submenu_page(
        'wpshadow',
        'Pro Modules',
        'Pro Modules',
        'manage_options',
        'wpshadow-pro-modules',
        'WPShadow_Pro\Module_Manager::render_page'
    );
});
```

---

## Benefits of Module Architecture

### For Users
✅ **Simplicity:** One plugin (Pro), multiple features  
✅ **No Plugin Bloat:** Activate only modules you need  
✅ **Clean Plugins Page:** No clutter with 5+ separate plugins  
✅ **Clear Value:** All modules included in Pro subscription

### For Developers
✅ **Maintainability:** Modules isolated, easier to update  
✅ **Testing:** Test each module independently  
✅ **Releases:** Can update individual modules without full Pro release  
✅ **Code Reuse:** Modules share Pro infrastructure

### For Business
✅ **Clear Upgrade Path:** Core → Guardian → Pro → Modules  
✅ **Value Proposition:** "5 powerful modules included"  
✅ **No Nickel-and-Diming:** All modules in one price  
✅ **Professional Image:** Cohesive ecosystem, not scattered plugins

---

## Migration Path

### For Development Sites (like wpshadow_test)
1. Core already installed ✅
2. FAQ/KB features removed from Core ✅
3. Install Pro when available
4. Activate FAQ module via Module Manager
5. Activate KB module via Module Manager
6. Post 207 continues working (FAQ block already inserted)
7. FAQ posts 210-214 continue working

### For New Users
1. Install WPShadow Core (free) from WordPress.org
2. Use Core diagnostics/treatments
3. Upgrade to Pro ($199/year) when ready
4. Activate desired modules from Module Manager
5. Each module adds submenu to WPShadow menu

---

## Next Steps

1. ✅ Update wpshadow.php (COMPLETED)
2. ✅ Document module architecture (THIS FILE)
3. Create Module Manager class in Pro repo
4. Move FAQ files to Pro modules/faq/
5. Move KB files to Pro modules/kb/
6. Create module.php files for each module
7. Create Module Manager UI page
8. Test module activation/deactivation
9. Update Pro documentation

---

## Pricing Comparison

### WPShadow Core (FREE)
- 57 diagnostics
- 44 treatments
- 10 workflows
- Kanban board
- KPI tracking
- **$0 forever**

### WPShadow Guardian (SaaS)
- Cloud AI monitoring
- Cross-site analytics
- Automated alerts
- **$0-99/month** (1 site free forever)

### WPShadow Pro (Plugin)
- Unlimited workflows
- Agency features
- White-label
- **5 powerful modules included:**
  - FAQ Module
  - Knowledge Base Module
  - Academy Module
  - Table of Contents Module
  - SEO/Schema Module
- **$199/year**

**Total Value:** If each module were sold separately ($49 each = $245), plus Pro features ($199) = **$444 value for $199/year**

---

**Related Documents:**
- [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md)
- [PRODUCT_ECOSYSTEM.md](PRODUCT_ECOSYSTEM.md)
- [ROADMAP.md](ROADMAP.md)
- [ARCHITECTURE.md](ARCHITECTURE.md)
