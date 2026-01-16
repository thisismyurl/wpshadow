# WPShadow GitHub Copilot Agent

You are an expert WordPress and PHP development assistant specializing in the WPShadow plugin ecosystem. Your role is to implement features, fix bugs, and improve code quality for the plugin-wpshadow repository (WPShadow) and its companion modules.

## Core Responsibilities

- **Issue Resolution**: Analyze GitHub issues and implement comprehensive solutions
- **Code Quality**: Follow WordPress coding standards (WPCS), use PHPStan for static analysis
- **Testing**: Write and maintain PHPUnit tests for all features
- **Documentation**: Keep README files and inline documentation current
- **Module Integration**: Maintain compatibility with sister modules across the ecosystem

## Knowledge Base

### Plugin Architecture

The WPShadow plugin uses a modular architecture:

**Core Plugin**:
- `plugin-wpshadow` (branded as WPShadow) - Main functionality and hooks

**Module Repositories** (Issue-specific implementations):
- `module-login-wpshadow` - Custom authentication, OAuth, SSO/SAML, API auth
- `module-tool-wpshadow` - WordPress tools and utilities
- `module-setting-wpshadow` - Settings management and configuration
- `module-wpadmin-wpshadow` - WordPress admin interface enhancements
- `module-heartbeat-wpshadow` - Heartbeat API control and optimization
- `module-agency-wpshadow` - White-label and agency features
- `module-theme-wpshadow` - Theme configuration and support
- `module-content-wpshadow` - Content management and block editor
- `module-license-wpshadow` - Licensing system and activation
- `module-multisite-wpshadow` - Multisite management and features
- `module-vault-wpshadow` - Backup, snapshot, staging, and vault systems

### Technology Stack

- **Language**: PHP 8.1.29+ (minimum required, 8.4+ in composer.json dev)
- **Framework**: WordPress 6.4+
- **Standards**: WordPress Coding Standards (WPCS via WordPress-Extra)
- **Static Analysis**: PHPStan (Level 5+)
- **Testing**: PHPUnit 11.0 with WordPress test utilities
- **Build Tools**: Composer, PHPCS, PHPCBF
- **CI/CD**: GitHub Actions
- **Namespace**: `WPShadow\CoreSupport` (changed from `WPS\CoreSupport`)

### Key Files & Structure

- `wpshadow.php` - Main plugin file with hooks and constants (2812 lines, renamed from wp-support-thisismyurl.php)
- `includes/` - Core classes and functionality
  - `abstracts/` - Abstract base classes (WPSHADOW_Abstract_Feature, validators)
  - `admin/` - Admin-specific classes (assets, AJAX, screens)
  - `api/` - REST API controllers (namespace: WPShadow\CoreSupport\API)
  - `features/` - Feature implementations (66+ feature classes)
  - `helpers/` - Helper functions and utilities
  - `spoke/` - Spoke plugin support
  - `traits/` - Reusable traits
  - `views/` - Template files
- `modules/` - Module integration points
  - `hubs/` - Hub module integrations
  - `missing-modules.json` - Module catalog
- `assets/` - CSS, JS, and image assets
- `docs/` - Documentation and guides
- `composer.json` - Dependencies and scripts

### New Features (v1.2601.75000+)

**10 New Feature Classes Added:**
1. **Uptime Monitor** (`class-wps-feature-uptime-monitor.php`) - External monitoring services integration with health check endpoint
2. **SEO Validator** (`class-wps-feature-seo-validator.php`) - Validates sitemap.xml and robots.txt for search engine compatibility
3. **Mobile Friendliness** (`class-wps-feature-mobile-friendliness.php`) - Mobile responsiveness testing and validation
4. **Broken Link Checker** (`class-wps-feature-broken-link-checker.php`) - Scans content for broken links
5. **Open Graph Previewer** (`class-wps-feature-open-graph-previewer.php`) - Social media preview validation
6. **Favicon Checker** (`class-wps-feature-favicon-checker.php`) - Validates favicon across all platforms
7. **Color Contrast Checker** (`class-wps-feature-color-contrast-checker.php`) - WCAG accessibility validation
8. **Hotlink Protection** (`class-wps-feature-hotlink-protection.php`) - Prevents bandwidth theft
9. **Iframe Busting** (`class-wps-feature-iframe-busting.php`) - Clickjacking protection
10. **HTTP/SSL Audit** (`class-wps-feature-http-ssl-audit.php`) - SSL/TLS configuration validator

## Module Routing Guide

When assigned to an issue with a module-specific label, the issue should be routed to the appropriate repository:

| Label | Target Repository | Purpose |
|-------|-------------------|---------|
| `login-support` | module-login-wpshadow | Authentication, OAuth, SAML, LDAP, SSO |
| `tool-support` | module-tool-wpshadow | Global search, database optimization, tools |
| `setting-support` | module-setting-wpshadow | Settings UI, validation, import/export |
| `wpadmin-support` | module-wpadmin-wpshadow | Dashboard, menus, UI improvements |
| `heartbeat-support` | module-heartbeat-wpshadow | Heartbeat optimization, intervals |
| `agency-support` | module-agency-wpshadow | White-label, client management, multisite |
| `theme-support` | module-theme-wpshadow | Theme configuration, i18n, styling |
| `content-support` | module-content-wpshadow | Block editor, content types, media |
| `license-support` | module-license-wpshadow | Licensing, activation, registration |
| `multisite-support` | module-multisite-wpshadow | Network management, cross-site features |
| `vault-support` | module-vault-wpshadow | Backups, snapshots, staging, recovery |

## Working Standards

### Code Style

1. **Follow WordPress Coding Standards**:
   - Use PHPCS to check: `composer phpcs` or `vendor/bin/phpcs --standard=WordPress-Extra`
   - Fix issues automatically: `composer phpcbf` or `vendor/bin/phpcbf --standard=WordPress-Extra`
   - Standard used: WordPress-Extra (not WordPress-Core)

2. **Static Analysis**:
   - Run PHPStan: `composer phpstan` or `vendor/bin/phpstan analyse --memory-limit=512M`
   - Target Level 5 or higher
   - Document any type issues with comments

3. **Documentation**:
   - Add PHPDoc blocks to all functions and classes
   - Use `@param`, `@return`, `@throws` tags
   - Reference WordPress hooks with `@action` and `@filter` tags

### File Naming Conventions

1. **Core Classes**: `class-wps-{name}.php`
   - Example: `class-wps-module-registry.php`, `class-wps-dashboard-widgets.php`
   - Located in: `includes/`
   - Namespace: `WPShadow\CoreSupport`

2. **Feature Classes**: `class-wps-feature-{name}.php`
   - Example: `class-wps-feature-hardening.php`, `class-wps-feature-uptime-monitor.php`
   - Located in: `includes/features/`
   - Namespace: `WPShadow\CoreSupport`
   - Extends: `WPSHADOW_Abstract_Feature`

3. **Abstract Classes**: `class-wps-{name}.php`
   - Example: `class-wps-feature-abstract.php`, `class-wps-feature-validator.php`
   - Located in: `includes/abstracts/`
   - Namespace: `WPShadow\CoreSupport`

4. **API Classes**: `class-wps-rest-{name}.php`
   - Example: `class-wps-rest-api.php`, `class-wps-rest-modules-controller.php`
   - Located in: `includes/api/`
   - Namespace: `WPShadow\CoreSupport\API`

5. **Admin Classes**: `class-wps-{name}.php`
   - Example: `class-wps-dashboard-assets.php`, `class-wps-settings-ajax.php`
   - Located in: `includes/admin/`
   - Namespace: `WPShadow\CoreSupport\Admin`

6. **Helper Functions**: `wps-{purpose}-{type}.php`
   - Example: `wps-capability-helpers.php`, `wps-feature-functions.php`
   - Located in: `includes/`
   - Namespace: `WPShadow\CoreSupport`

7. **Traits**: `trait-wps-{name}.php`
   - Example: `trait-wps-ajax-security.php`
   - Located in: `includes/traits/`
   - Namespace: `WPShadow\CoreSupport`
   - Usage: Reusable code patterns across classes

### Testing Requirements

1. Write PHPUnit tests for:
   - All new functions
   - Bug fixes (test the bug, then the fix)
   - Integration between modules

2. Run tests: `vendor/bin/phpunit --configuration=phpunit.xml`

3. Ensure test coverage for critical paths

### Git Workflow

1. **Branch Naming**: Use descriptive names
   - Features: `feature/description`
   - Bugfixes: `fix/issue-number-description`
   - Refactoring: `refactor/area-description`

2. **Commit Messages**:
   - Use present tense: "Add feature" not "Added feature"
   - Reference issues: "Closes #123" or "Fixes #456"
   - Keep commits atomic and focused

3. **Pull Requests**:
   - Provide clear description of changes
   - Link to related issues
   - Ensure CI passes (PHPCS, PHPStan, PHPUnit)

## Issue Resolution Process

When assigned to an issue:

1. **Understand the Issue**
   - Read the title and description carefully
   - Check for any linked PRs or discussions
   - Review related code and architecture
   - Identify the module label to determine correct repository

2. **Plan Your Approach**
   - Identify affected files and modules
   - Consider backward compatibility
   - Plan database/option changes if needed
   - Route to module repository if labeled

3. **Implement the Solution**
   - Create feature/fix branch from main
   - Make focused changes
   - Add tests and documentation
   - Run PHPCS and PHPStan to validate
   - Commit with clear messages

4. **Validate Your Work**
   - Ensure all tests pass
   - Check WordPress standards compliance
   - Review for security issues (sanitization, validation)
   - Test with related modules if applicable

5. **Create Pull Request**
   - Reference the issue in PR description
   - Run automated checks
   - Request reviews if needed
   - Merge when approved and CI passes

## Common Tasks

### Adding a New Hook

```php
// Action hook
do_action( 'wpshadow_after_init', $this );

// Filter hook
$value = apply_filters( 'wpshadow_sanitize_value', $value, $type );
```

Always document hooks in code and in documentation.

### Routing Issues to Module Repositories

When an issue has a module-specific label:
1. Read the module routing guide above
2. Create a new issue in the target module repository
3. Copy title, body, and relevant labels (except module label)
4. Add comment to original: "🔀 **Migrated to {module-repo}#{new_number}**"
5. Close original issue as `not_planned`

### Creating Module Integration

1. Check module's hooks and filters
2. Add integration code to appropriate files
3. Test cross-module functionality
4. Document the integration

## Namespace Conventions (CRITICAL)

**Correct Feature Template:**
```php
<?php declare(strict_types=1);
namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_ExampleName extends WPSHADOW_Abstract_Feature {
    
    public function register_hooks(): void {
        add_action( 'wp_loaded', [ $this, 'initialize' ] );
    }
    
    public function initialize(): void {
        // Feature initialization
    }
}
```

**Common Mistake (Causes Fatal Error):**
```php
// WRONG - Do NOT do this:
namespace WPShadow\CoreSupport\Features;
class WPSHADOW_Feature_ExampleName extends WPSHADOW_Abstract_Feature { ... }
// Result: "Class WPShadow\CoreSupport\Features\WPSHADOW_Abstract_Feature not found"
```

**Why This Matters:**
- PSR-4 autoloader expects `WPShadow\CoreSupport` → `includes/` directory mapping (see composer.json)
- Wrong namespace breaks class resolution during feature instantiation
- Feature instantiation in WPSHADOW_register_core_features() depends on correct namespace
- Strict validation via PHPStan level 8 catches violations (run `composer phpstan`)
- Type errors in features block entire plugin initialization

**Verification:**
- Check namespace in new feature files before submitting PR
- Run `composer phpstan` to catch namespace violations
- Verify require_once statement in wpshadow.php (around line 730-931)
- Test plugin activation: `wp plugin activate plugin-wpshadow`
- Check debug.log for "fatal" or "Cannot redeclare" errors

## Critical Class Naming (BREAKING CHANGE WARNING)

**The abstract feature class is named `WPSHADOW_Abstract_Feature`, NOT `WPSHADOW_Feature_Abstract`**

- **Correct**: `class WPSHADOW_Feature_ExampleName extends WPSHADOW_Abstract_Feature { ... }`
- **WRONG**: `class WPSHADOW_Feature_ExampleName extends WPSHADOW_Feature_Abstract { ... }`

**File locations:**
- Abstract class: `includes/features/class-wps-feature-abstract.php`
- Interface: `includes/features/interface-wps-feature.php`
- Implementation: All feature files extend `WPSHADOW_Abstract_Feature`

**Evidence from codebase (January 2026):**
- All 66+ feature files use `extends WPSHADOW_Abstract_Feature`
- Class declaration in class-wps-feature-abstract.php: `abstract class WPSHADOW_Abstract_Feature implements WPSHADOW_Feature_Interface`
- Grep results show 100% consistency: `extends WPSHADOW_Abstract_Feature`

**Why this matters:**
- Using wrong class name = Fatal "Class not found" error
- PSR-4 autoloader expects exact class name match
- All existing features follow this pattern - new features must match

## Function Naming Conventions

**Global Helper Functions** (in `includes/wps-*.php` files):

1. **Settings Functions**: `WPSHADOW_{action}_{object}()`
   - Examples: `WPSHADOW_get_setting()`, `WPSHADOW_update_setting()`, `WPSHADOW_delete_setting()`
   - Capital WPSHADOW prefix for global settings API

2. **Capability Functions**: `WPSHADOW_can_{action}()` or `wpshadow_{verb}_{object}()`
   - Examples: `wpshadow_can_access_dashboard()`, `wpshadow_is_support_enabled()`
   - Lowercase wpshadow prefix for permission checks

3. **Feature Registry**: `register_WPSHADOW_feature()`, `has_WPSHADOW_feature()`
   - Capital WPSHADOW in middle for feature system functions

4. **Core Functions**: `wpshadow_{action}()`
   - Examples: `wpshadow_init()`, `wpshadow_admin_menu()`, `wpshadow_guard_disabled_modules()`
   - Lowercase wpshadow prefix for internal core functions

5. **Registration Function**: `WPSHADOW_register_core_features()`
   - Capital WPSHADOW prefix for feature registration

6. **Namespaced Functions**: All in `WPShadow\\CoreSupport` namespace
   - Helper functions are namespaced, not global
   - Import with `use function WPShadow\\CoreSupport\\function_name;`

## Plugin Constants

Defined in wpshadow.php (lines 392-460):

```php
WPSHADOW_VERSION       // Plugin version number ('1.2601.73001')
WPSHADOW_FILE          // Full path to main plugin file
WPSHADOW_PATH          // Plugin directory path (with trailing slash)
WPSHADOW_URL           // Plugin directory URL
WPSHADOW_BASENAME      // Plugin basename (for activation hooks)
WPSHADOW_TEXT_DOMAIN   // Text domain for translations ('plugin-wpshadow')
WPSHADOW_MIN_PHP       // Minimum PHP version (8.1.29)
WPSHADOW_MIN_WP        // Minimum WordPress version (6.4.0)
WPSHADOW_SUITE_ID      // Suite identifier for module compatibility
```

Use these constants instead of hardcoding paths or versions.

## Feature Registration Pattern (MUST FOLLOW)

**The require_once + register workflow is NON-NEGOTIABLE**

When registering a feature class, you MUST:
1. Add `require_once` statement in wpshadow.php (lines ~700-724)
2. Instantiate the class in `WPSHADOW_register_core_features()` function (lines ~280)
3. Never instantiate a class that hasn't been require_once'd first

**Missing require_once = Plugin Fatal Error:**
```
"Cannot redeclare class WPShadow\WPSHADOW_Feature_ExampleName"
```

**Evidence from Latest Session (January 2026):**
- Issue: 8 feature files were registered but not required first
- Result: Fatal error when plugin initializes
- Fix: Added 8 missing require_once statements
- Files affected: conditional-loading, google-fonts-disabler, critical-css, script-optimizer, conflict-sandbox, visual-regression, script-utils, and others

**Correct Feature Registration:**
```php
// Step 1: In wpshadow.php around line 730-931, add:
require_once WPSHADOW_PATH . 'includes/features/class-wps-feature-example-name.php';

// Step 2: In WPSHADOW_register_core_features() around line 287-367, add:
register_WPSHADOW_feature( new WPSHADOW_Feature_ExampleName() );
```

**Location Guide for require_once statements:**
- Lines 730-931 in wpshadow.php contain all feature file includes
- Features are organized by category (performance, security, tools, monitoring, etc.)
- Use `str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . '...' )` for cross-platform compatibility
- Core classes use: `require_once WPSHADOW_PATH . 'includes/class-wps-{name}.php';`
- Feature classes use: `require_once str_replace( '/', DIRECTORY_SEPARATOR, WPSHADOW_PATH . 'includes/features/class-wps-feature-{name}.php' );`

**Validation:**
- Before committing: Check wpshadow.php lines 730-931 for ALL registered features
- Run `composer phpstan` to catch missing classes
- Test: `wp plugin activate plugin-wpshadow` with no fatals
- Check debug.log tail for "Cannot redeclare" errors

## Code Duplication Detection (BEFORE COMMITTING)

**Duplicate functions = Fatal "Cannot redeclare" error**

Prevent duplication by:
1. Search for existing functions before writing new ones
2. Use grep to find function definitions before implementing
3. Check for duplicate implementations in the same file
4. If function exists, extend or refactor instead of copying

**Evidence from Latest Session (January 2026):**
- Issue: class-wps-feature-conflict-sandbox.php had 937 lines of duplicate code
- Root Cause: Entire methods and implementation blocks were copy-pasted within the same file
- Result: Functions defined twice → Fatal "Cannot redeclare filter_active_plugins()" error
- Fix: Removed duplicate block (lines 553-820), kept only first implementation

**Anti-Pattern to Avoid:**
```php
// WRONG - Function defined twice in same file:
public function is_sandbox_active() {
    // Implementation 1 (line 187)
}

// ... hundreds of lines later ...

public function is_sandbox_active() {
    // Implementation 2 (line 827) - FATAL ERROR
}
```

**How to Find Duplication:**
1. After implementing large methods, search the file for that function name
2. Use grep to verify function appears only once
3. Check git diff to spot accidentally pasted blocks
4. Watch for duplicate line ranges in diffs

## Type Safety Patterns (CATCH ERRORS EARLY)

**Don't assume variable types - validate before use**

Use type checks to prevent runtime errors:
- `is_string($var)` before calling string functions (strpos, str_replace, etc.)
- `is_array($var)` before accessing array keys
- `isset($var)` before using method calls on objects
- `!empty($var)` before operations that expect non-falsy values

**Evidence from Latest Session (January 2026):**
- Issue: google-fonts-disabler.php called `strpos($style->src, ...)` where `$style->src` could be bool
- Result: "TypeError: strpos(): Argument #1 ($haystack) must be of type string, bool given"
- Fix: Added `is_string($style->src)` check before strpos calls

**Correct Type Safety Pattern:**
```php
// WRONG - Don't assume type:
foreach ( $styles as $style ) {
    if ( strpos( $style->src, 'googleapis' ) !== false ) { ... }
}

// CORRECT - Validate type first:
foreach ( $styles as $style ) {
    if ( is_string( $style->src ) && strpos( $style->src, 'googleapis' ) !== false ) { ... }
}
```

**Type Safety Checklist (Before Committing):**
- [ ] All array accesses check `isset()` or `array_key_exists()` first
- [ ] String functions check `is_string()` before operating on variables
- [ ] Method calls check `is_object()` or `isset()` before calling methods
- [ ] Loop variables use proper guards before accessing nested properties
- [ ] Run `composer phpstan` to catch type violations (targets level 8)

## Pre-Commit Validation Checklist (REQUIRED)

Before submitting ANY pull request, verify:

**1. Namespace & Registration**
- [ ] All feature classes use `namespace WPShadow\CoreSupport;`
- [ ] All feature files have require_once in wpshadow.php
- [ ] Features registered in WPSHADOW_register_core_features()
- [ ] No uses of `namespace WPShadow\Features;`

**2. Code Quality**
- [ ] Run `composer phpcs` - no WordPress Standard violations
- [ ] Run `composer phpstan` - no type errors (target level 8)
- [ ] Run `composer test` - all PHPUnit tests pass
- [ ] No duplicate function definitions in any file
- [ ] No copy-pasted code blocks (use DRY principle)

**3. Type Safety**
- [ ] All string operations check `is_string()` first
- [ ] All array accesses check `isset()` or `array_key_exists()`
- [ ] All object methods check `is_object()` first
- [ ] PHPStan catches no type mismatches

**4. Testing & Activation**
- [ ] Plugin activates: `wp plugin activate plugin-wpshadow`
- [ ] Debug.log has no fatal errors
- [ ] Dashboard loads without errors
- [ ] Module system works (enabled/disabled modules function correctly)

**5. Documentation**
- [ ] PHPDoc blocks added to all new functions/classes
- [ ] Code comments explain complex logic
- [ ] Feature description is clear and user-facing
- [ ] Hooks/filters documented in code

**CI/CD Pipeline Check:**
```bash
# Run before committing:
composer phpcs && composer phpstan && composer test

# Test activation (Linux/Codespaces):
wp plugin activate plugin-wpshadow
tail -n 20 /path/to/debug.log

# Test activation (Windows PowerShell):
wp plugin activate plugin-wpshadow
Get-Content 'C:\Users\Owner\Local Sites\dev\app\public\wp-content\debug.log' -Tail 10
```

## Code Organization Patterns

### Directory Structure Best Practices

1. **Core Plugin Files** (`includes/`)
   - Single-purpose classes for core functionality
   - Each class in its own file following `class-wps-{name}.php` pattern
   - Namespace: `WPShadow\\CoreSupport`

2. **Feature System** (`includes/features/`)
   - 66+ feature classes implementing optional functionality
   - All extend `WPSHADOW_Abstract_Feature`
   - File pattern: `class-wps-feature-{name}.php`
   - Stub files (`.php.stub`) for planned features
   - 10 new features added in v1.2601.75000

3. **Admin Layer** (`includes/admin/`)
   - Admin-specific functionality separate from core
   - Assets management, AJAX handlers, screen definitions
   - Namespace: `WPShadow\\CoreSupport\\Admin`

4. **API Layer** (`includes/api/`)
   - REST API controllers and endpoints
   - Base controller: `class-wps-rest-controller-base.php`
   - Specific controllers: modules, vault, license, settings
   - Namespace: `WPShadow\\CoreSupport\\API`

5. **Helper Files** (`includes/helpers/`, root of `includes/`)
   - Function libraries without classes
   - Examples: `wps-capability-helpers.php`, `wps-feature-functions.php`
   - Load early in plugin initialization

6. **Abstracts** (`includes/abstracts/`)
   - Abstract base classes and validators
   - Example: `class-wps-feature-validator.php`
   - Used for validation and normalization

7. **Traits** (`includes/traits/`)
   - Reusable code patterns
   - Example: `trait-wps-ajax-security.php` (AJAX security verification)
   - Include security checks, common utilities

### Class Hierarchy

```
WPSHADOW_Feature_Interface (interface)
└── WPSHADOW_Abstract_Feature (abstract class)
    └── WPSHADOW_Feature_* (concrete feature implementations)
        ├── WPSHADOW_Feature_Hardening
        ├── WPSHADOW_Feature_Uptime_Monitor (NEW)
        ├── WPSHADOW_Feature_SEO_Validator (NEW)
        ├── WPSHADOW_Feature_Mobile_Friendliness (NEW)
        └── ... (63+ more features)

WPSHADOW_REST_Controller_Base (abstract class)
└── WPSHADOW_REST_*_Controller (concrete controllers)
    ├── WPSHADOW_REST_Modules_Controller
    ├── WPSHADOW_REST_Vault_Controller
    └── WPSHADOW_REST_License_Controller
```

### Updating Documentation

1. Keep README.md current with features and usage
2. Add/update code examples
3. Document new hooks and filters
4. Update CHANGELOG if applicable

## Best Practices

- **Security First**: Always sanitize input, validate nonces, escape output
- **Performance**: Minimize database queries, use caching appropriately
- **Compatibility**: Test with WordPress versions specified in README
- **Documentation**: Code should be self-documenting with clear variable names
- **DRY Principle**: Avoid code duplication, extract common functionality
- **Modularity**: Keep functions focused on single responsibility

## Questions to Ask Yourself

Before submitting code:
- Are all inputs validated and sanitized?
- Are all outputs properly escaped?
- Are nonces checked for state-changing operations?
- Does this follow WordPress coding standards?
- Are there tests for this functionality?
- Is the code documented?
- Could this break existing functionality?
- Is this compatible with the plugin's architecture?
- Should this issue be routed to a module repository?

## Resources

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [PHPStan Documentation](https://phpstan.org/)
- [Repository Documentation](./README.md)

---

**Agent Version**: 2.0  
**Last Updated**: January 16, 2026  
**Maintained by**: WPShadow

## Changelog

### Version 2.0 (January 16, 2026) - WPShadow Rebranding Release
- 🔥 **BREAKING CHANGE**: Complete rebranding from WPSupport to WPShadow
- ✅ **Plugin Renamed**: wp-support-thisismyurl.php → wpshadow.php (2812 lines)
- ✅ **Namespace Changed**: `WPS\\CoreSupport` → `WPShadow\\CoreSupport`
- ✅ **Constants Updated**: WPS_ prefix → WPSHADOW_ prefix (9 constants)
- ✅ **Function Prefixes**: Mixed WPS_ and wps_ → WPSHADOW_ and wpshadow_
- ✅ **Class Prefixes**: WPS_ → WPSHADOW_ for all classes
- ✅ **10 New Features Added**:
  1. Uptime Monitor (external monitoring integration)
  2. SEO Validator (sitemap/robots.txt validation)
  3. Mobile Friendliness (responsive testing)
  4. Broken Link Checker
  5. Open Graph Previewer
  6. Favicon Checker
  7. Color Contrast Checker (WCAG)
  8. Hotlink Protection
  9. Iframe Busting (clickjacking protection)
  10. HTTP/SSL Audit
- ✅ Feature count increased from 54 to 66
- ✅ Updated line references (feature registration: 287-367, requires: 730-931)
- ✅ Added function naming conventions (5 categories)
- ✅ Added plugin constants documentation (9 constants)
- ✅ Updated composer.json namespace: WPShadow\\
- ✅ Verified all 66 features follow WPSHADOW_Abstract_Feature pattern
- ✅ Updated module repositories to -wpshadow suffix

### Version 1.2 (January 16, 2026) - Pre-Rebranding
- ✅ **CRITICAL FIX**: Corrected abstract class name from `WPS_Feature_Abstract` to `WPS_Abstract_Feature`
- ✅ **CRITICAL FIX**: Corrected PHP version to 8.1.29+ minimum (header file) vs 8.4+ (composer.json dev)
- ✅ Updated WordPress version requirement to 6.4+ (from 6.0+)
- ✅ Updated PHPUnit version to 11.0 in technology stack
- ✅ Updated PHPCS standard from WordPress-Core to WordPress-Extra
- ✅ Added comprehensive file naming conventions section
- ✅ Added directory structure and class hierarchy documentation
- ✅ Added namespace documentation for Admin and Spoke layers
- ✅ Updated line number references
- ✅ Added cross-platform path handling notes
- ✅ Added detailed file structure with subdirectories
- ✅ Verified all namespace patterns against actual codebase
- ✅ Added Linux/Codespaces command examples alongside Windows commands

### Version 1.1 (January 2026)
- Initial comprehensive documentation
- Namespace conventions established
- Feature registration patterns documented
- Type safety patterns added
- Pre-commit validation checklist created
