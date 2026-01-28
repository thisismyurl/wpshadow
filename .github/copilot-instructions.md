# Copilot Instructions for WPShadow Core Plugin

## 🎯 Quick Start: Essential Patterns (Read This First!)

**For AI Agents:** These patterns have been validated through hundreds of successful implementations. Use them as your default approach.

### Pattern 1: Always Use WordPress APIs First
```php
// ❌ DON'T: Parse HTML to check enqueued scripts
$html = Admin_Page_Scanner::capture_admin_page();
preg_match_all('/script/', $html, $matches);

// ✅ DO: Use WordPress globals
global $wp_scripts;
$is_enqueued = $wp_scripts->is_enqueued('handle');
```

**Available WordPress APIs:**
- `global $menu, $submenu` - Admin menu structure
- `global $wp_scripts, $wp_styles` - Enqueued assets
- `global $wp_settings_fields` - Settings API fields
- `global $wp_filter` - Registered hooks
- `global $wp_admin_bar` - Admin toolbar

### Pattern 2: HTML Parsing is ONLY for DOM Validation
Use `Admin_Page_Scanner::capture_admin_page()` ONLY when you need to:
- Validate rendered HTML structure
- Check CSS classes applied to elements
- Test JavaScript interactions
- Detect malformed markup

**Rule:** If WordPress has an API for it, use the API. HTML parsing is 20-67x slower.

### Pattern 3: Security is Non-Negotiable
```php
// ✅ ALWAYS do all three: nonce, capability, sanitize
self::verify_request( 'wpshadow_action', 'manage_options' );
$value = self::get_post_param( 'field', 'text', '', true );

// ✅ ALWAYS use $wpdb->prepare()
$wpdb->query( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $id ) );

// ✅ ALWAYS escape output
echo esc_html( $user_input );
echo esc_url( $link );
echo esc_attr( $attribute );
```

### Pattern 4: Multi-Edit Operations
When making multiple independent edits, use `multi_replace_string_in_file` instead of sequential `replace_string_in_file` calls. This is ~5x faster and more cost-effective for users.

### Pattern 5: Documentation Comments
Every public method needs:
```php
/**
 * Brief one-line description.
 *
 * Longer explanation of behavior and usage.
 *
 * @since  1.YDDD.HHMM
 * @param  string $param Description.
 * @return array {
 *     Description of return structure.
 *
 *     @type string $key Description.
 * }
 */
```

### Pattern 6: File Creation Template
```php
<?php
/**
 * [Feature Name]
 *
 * [Detailed description of what this file does]
 *
 * @package    WPShadow
 * @subpackage [Subsystem]
 * @since      1.YDDD.HHMM
 */

declare(strict_types=1);

namespace WPShadow\[Namespace];

use WPShadow\Core\[BaseClass];

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * [Class Name] Class
 *
 * [Detailed class description]
 *
 * @since 1.YDDD.HHMM
 */
class [Class_Name] extends [Base_Class] {
    // Implementation
}
```

### Pattern 7: Diagnostic Implementation Checklist
When creating a new diagnostic:
1. ✅ Check if WordPress has an API for what you're checking
2. ✅ Extend `Diagnostic_Base`
3. ✅ Set `$slug`, `$title`, `$description`, `$family`
4. ✅ Implement `check()` method
5. ✅ Return `array` with finding or `null` if no issue
6. ✅ Use WordPress APIs (not HTML parsing) when possible
7. ✅ Register in `includes/diagnostics/class-diagnostic-registry.php`

---

## Repository Context
**Repository:** `thisismyurl/wpshadow`  
**Purpose:** Foundational WordPress plugin providing the architecture for all wpshadow-pro-* modules  
**Type:** Core Plugin (Free)  
**WordPress.org:** https://wordpress.org/plugins/wpshadow/  
**Version:** 1.YDDD.HHMM (Format: 1.{last year digit}{julian day}.{hour}{minute} in Toronto time)  
**PHP:** 8.1+  
**WordPress:** 6.4+  

## Architecture Overview

### Hub & Spoke Model
WPShadow uses a hub-and-spoke architecture across the ecosystem:

- **This repo (wpshadow)**: Core hub providing base classes, architecture, and free features
- **Pro modules (wpshadow-pro-*)**: Spoke plugins that extend core functionality
  - wpshadow-pro-security
  - wpshadow-pro-performance
  - wpshadow-pro-backup
  - Additional modules in development
- **Pro modules (local)**: Integrated pro modules in `pro-modules/` directory
  - FAQ, Links, LMS, Glossary, KB (Knowledge Base)
- **Theme (theme-wpshadow)**: Official companion theme

### Base Classes (includes/core/)
All features extend these base classes:

**`Diagnostic_Base`** - Health check diagnostics
```php
namespace WPShadow\Core;

abstract class Diagnostic_Base {
    protected static $slug = '';
    protected static $title = '';
    protected static $description = '';
    protected static $family = '';  // Groups related diagnostics
    
    abstract public static function check();  // Returns finding array or null
    public static function execute();         // Wraps check() with hooks
}
```

**`Treatment_Base`** - Fix implementations
```php
namespace WPShadow\Core;

abstract class Treatment_Base implements Treatment_Interface {
    abstract public static function get_finding_id();  // Links to diagnostic
    abstract public static function apply();           // Implements the fix
    public static function can_apply();                // Permission check
    public static function execute( $dry_run = false ); // Wraps apply() with hooks
}
```

**`AJAX_Handler_Base`** - AJAX endpoints with built-in security
```php
namespace WPShadow\Core;

abstract class AJAX_Handler_Base {
    protected static function verify_request( $nonce_action, $capability = 'manage_options', $nonce_field = 'nonce' );
    protected static function get_post_param( $key, $type = 'text', $default = '', $required = false );
    protected static function send_success( $data = array() );
    protected static function send_error( $message, $data = array() );
}
```

**`Settings_Registry`** - Centralized settings management
```php
namespace WPShadow\Core;

class Settings_Registry {
    public static function register();              // Register all settings with WordPress
    public static function get( $key, $default );   // Get setting value
    public static function set( $key, $value );     // Set setting value
}
```

### Namespace Convention
- **Core:** `WPShadow\Core\`, `WPShadow\Diagnostics\`, `WPShadow\Treatments\`, `WPShadow\Admin\`, `WPShadow\Workflow\`
- **Pro modules (external):** `WPShadow\Pro\{ModuleName}\`
- **Pro modules (local):** `WPShadow\{ModuleName}\` (e.g., `WPShadow\KnowledgeBase\`, `WPShadow\FAQ\`)

### File Naming Convention
- Classes: `class-{lowercase-with-dashes}.php`
- Functions: `functions-{purpose}.php`
- Namespaces mirror directory structure
```
includes/diagnostics/class-diagnostic-memory-limit.php → WPShadow\Diagnostics\Diagnostic_Memory_Limit
includes/treatments/class-treatment-memory-limit.php   → WPShadow\Treatments\Treatment_Memory_Limit
includes/core/class-settings-registry.php              → WPShadow\Core\Settings_Registry
```

## Coding Standards (CRITICAL)

### WordPress Coding Standards
- Follow **WordPress-Extra** coding standards (enforced by PHPCS via `phpcs.xml.dist`)
- **Tabs for indentation, spaces for alignment**
- `snake_case` for functions and methods
- `PascalCase` for classes (with underscores: `Class_Name`)
- **Yoda conditions:** `if ( 'value' === $variable )` not `if ( $variable === 'value' )`
- Strict types: `declare(strict_types=1);` at top of all new PHP files
- Single quotes for strings (unless interpolation needed)
- Space after `if`, `for`, `foreach`, `while`, `switch`
- No space after function names: `function_name()` not `function_name ()`

### Security Requirements (NON-NEGOTIABLE)

**SQL Injection Prevention:**
```php
// ❌ NEVER DO THIS
$wpdb->query( "SELECT * FROM {$wpdb->posts} WHERE ID = {$post_id}" );
$wpdb->query( "SELECT * FROM {$wpdb->posts} WHERE post_title = '{$title}'" );

// ✅ ALWAYS DO THIS
$wpdb->query( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $post_id ) );
$wpdb->query( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_title = %s", $title ) );

// ✅ Multiple placeholders
$wpdb->query( $wpdb->prepare( 
    "SELECT * FROM {$wpdb->posts} WHERE ID = %d AND post_status = %s",
    $post_id,
    $status 
) );
```

**Output Escaping:**
```php
// ❌ NEVER DO THIS
echo $user_input;
echo "<a href='{$url}'>Link</a>";

// ✅ ALWAYS DO THIS
echo esc_html( $user_input );              // HTML content
echo esc_attr( $attribute_value );         // HTML attributes
echo esc_url( $url );                      // URLs
echo esc_js( $javascript_string );         // JavaScript strings
echo wp_kses_post( $html_content );        // Allow safe HTML tags

// ✅ Contextual escaping in templates
<div class="notice">
    <p><?php echo esc_html( $message ); ?></p>
    <a href="<?php echo esc_url( $link ); ?>" class="button">
        <?php echo esc_html( $button_text ); ?>
    </a>
</div>
```

**Input Sanitization:**
```php
// ✅ ALWAYS sanitize $_POST/$_GET/$_REQUEST
$text_field  = isset( $_POST['field'] ) ? sanitize_text_field( wp_unslash( $_POST['field'] ) ) : '';
$email       = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
$url         = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
$key         = isset( $_POST['key'] ) ? sanitize_key( $_POST['key'] ) : '';
$textarea    = isset( $_POST['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['content'] ) ) : '';
$integer     = isset( $_POST['count'] ) ? absint( $_POST['count'] ) : 0;
$boolean     = isset( $_POST['enabled'] ) ? rest_sanitize_boolean( $_POST['enabled'] ) : false;

// ✅ Always wp_unslash() first (WordPress adds slashes)
$value = sanitize_text_field( wp_unslash( $_POST['value'] ) );

// ✅ Use AJAX_Handler_Base helper in AJAX handlers
$value = self::get_post_param( 'field_name', 'text', '', true );
```

**Nonce Verification:**
```php
// ✅ ALWAYS verify nonces on form submissions
if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'action_name' ) ) {
    wp_die( __( 'Security check failed', 'wpshadow' ) );
}

// ✅ AJAX nonce verification
check_ajax_referer( 'action_name', 'nonce' );

// ✅ Use AJAX_Handler_Base for automatic verification
self::verify_request( 'wpshadow_action_name', 'manage_options' );

// ✅ Creating nonces in forms
<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'action_name' ) ); ?>" />

// ✅ Creating nonces in JavaScript
wp_localize_script( 'my-script', 'wpShadowData', array(
    'nonce' => wp_create_nonce( 'action_name' ),
) );
```

**Capability Checks:**
```php
// ✅ ALWAYS check user permissions
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( __( 'Insufficient permissions', 'wpshadow' ) );
}

// ✅ Check before any admin action
if ( ! current_user_can( 'manage_options' ) ) {
    wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
}

// ✅ Multisite network admin check
if ( is_multisite() && is_network_admin() ) {
    if ( ! current_user_can( 'manage_network_options' ) ) {
        wp_die( __( 'Insufficient permissions', 'wpshadow' ) );
    }
}

// ✅ Use Treatment_Base for automatic capability check
public static function can_apply() {
    return parent::can_apply(); // Handles single-site and multisite
}
```

**File Operations:**
```php
// ✅ ALWAYS validate file paths
$upload_dir = wp_upload_dir();
$file_path = $upload_dir['basedir'] . '/wpshadow/' . sanitize_file_name( $filename );

// ✅ Check if path is within allowed directory
if ( strpos( realpath( $file_path ), realpath( $upload_dir['basedir'] ) ) !== 0 ) {
    wp_die( __( 'Invalid file path', 'wpshadow' ) );
}

// ✅ Use WordPress filesystem API
require_once ABSPATH . 'wp-admin/includes/file.php';
WP_Filesystem();
global $wp_filesystem;
$wp_filesystem->put_contents( $file_path, $content, FS_CHMOD_FILE );
```

### Documentation Requirements

**Every class:**
```php
/**
 * Brief description of what this class does.
 *
 * Longer description providing context and usage examples.
 * Explain the purpose, when to use it, and key patterns.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */
class Example_Diagnostic extends Diagnostic_Base {
```

**Every public method:**
```php
/**
 * Brief description of what this method does.
 *
 * Longer description explaining the behavior, side effects,
 * and any important implementation details.
 *
 * @since  1.2601.2148
 * @param  string $param1 Description of parameter.
 * @param  int    $param2 Optional. Description. Default 0.
 * @return array {
 *     Array of results with specific structure.
 *
 *     @type string $status  Status of operation ('success'|'error').
 *     @type string $message Human-readable message.
 *     @type array  $data    Optional. Additional data.
 * }
 */
public function example_method( $param1, $param2 = 0 ) {
```

**Every hook (action/filter):**
```php
/**
 * Fires after a diagnostic check completes.
 *
 * @since 1.2601.2148
 *
 * @param string     $class   Diagnostic class name.
 * @param string     $slug    Diagnostic slug/identifier.
 * @param array|null $finding Finding result (null if no issues).
 */
do_action( 'wpshadow_after_diagnostic_check', $class, $slug, $finding );

/**
 * Filters the list of registered diagnostics.
 *
 * @since 1.2601.2148
 *
 * @param array $diagnostics Array of diagnostic class names keyed by slug.
 */
$diagnostics = apply_filters( 'wpshadow_registered_diagnostics', $diagnostics );
```

### Text Domain
- **Always use:** `'wpshadow'`
- **Never hardcode strings:** Use translation functions
- **All user-facing strings must be translatable**

```php
// ✅ Correct
__( 'Translatable text', 'wpshadow' )
_e( 'Translatable text', 'wpshadow' )
esc_html__( 'Translatable text', 'wpshadow' )
esc_html_e( 'Translatable text', 'wpshadow' )
esc_attr__( 'Translatable text', 'wpshadow' )

// ✅ With placeholders
sprintf( 
    /* translators: %s: plugin name */
    __( 'The %s plugin is active', 'wpshadow' ), 
    'WPShadow' 
)

// ✅ Pluralization
sprintf(
    /* translators: %d: number of items */
    _n( 
        '%d item found', 
        '%d items found', 
        $count, 
        'wpshadow' 
    ),
    number_format_i18n( $count )
)

// ❌ Never do this
echo 'Click here';
echo "The plugin found $count issues";
```

### Error Handling
```php
// ✅ Use Error_Handler for exceptions
use WPShadow\Core\Error_Handler;

try {
    // Risky operation
} catch ( \Exception $e ) {
    Error_Handler::log_error( $e->getMessage(), $e );
    return array(
        'success' => false,
        'message' => __( 'Operation failed', 'wpshadow' ),
    );
}

// ✅ Log important events
Error_Handler::log_info( 'Treatment applied successfully', array(
    'treatment' => $treatment_id,
    'user_id'   => get_current_user_id(),
) );
```

## WPShadow Philosophy (The 11 Commandments)

When suggesting features or code, align with these principles:

### 1. **Helpful Neighbor Experience**
Code should be friendly and educational, like advice from a trusted friend.

**In Practice:**
- Error messages explain WHY and provide solutions
- Admin notices are conversational, not robotic
- UI text guides users like a trusted friend
- Link to knowledge base articles, not sales pages
- Show impact: "This could slow your site by 30%" vs "Fix this now"

**Examples:**
```php
// ❌ Robotic
return array( 'message' => 'Operation failed' );

// ✅ Helpful Neighbor
return array( 
    'message' => __( 
        'We couldn\'t update your memory limit because wp-config.php is read-only. 
        Here\'s how to fix it yourself: [link to guide]', 
        'wpshadow' 
    ) 
);
```

### 2. **Free as Possible**
No artificial limitations in free version. Core features are fully functional.

**Free Forever:**
- All diagnostics (every security, performance, health check)
- All auto-fix treatments (with backup and rollback)
- Dashboard, Kanban board, activity logging
- KPI tracking and value demonstration
- Workflow automation (local execution)
- Integration with WordPress Site Health

**Paid Only When Necessary:**
- External scanning services (requires server costs)
- Cloud sync across multiple sites (storage costs)
- Email notifications (email service costs)

### 3. **Register, Don't Pay**
Fair exchange model with generous free tiers.

- Registration required but free tier is generous
- No dark patterns or tricks
- Clear value proposition
- "First 100 scans/month free"

### 4. **Advice, Not Sales**
Educational over promotional.

**Writing Guidelines:**
```php
// ❌ Sales Talk
__( 'Upgrade to Pro to fix critical issues!', 'wpshadow' )

// ✅ Advice
__( 'We found 5 security concerns. We can fix 3 automatically right now (free). 
For the other 2, here\'s how to fix them yourself: [link]. 
Want us to handle it? Our Pro addon can help too.', 'wpshadow' )
```

### 5. **Drive to Knowledge Base**
Link to KB articles for education.

- Feature descriptions link to docs
- Error messages link to troubleshooting guides
- Every diagnostic has a KB link
- Articles explain WHY and HOW, not just WHAT

### 6. **Drive to Free Training**
Offer free training, not sales funnels.

- Onboarding flows include training links
- Video tutorials embedded where relevant
- "Want to learn more? [5-minute free course]"
- No email required to view lessons

### 7. **Ridiculously Good for Free**
Quality bar that makes users question why it's free.

- Better UX than premium plugins
- Modern, slick design
- Faster and more intuitive
- Documentation that actually helps
- No nagware or constant upgrade prompts

### 8. **Inspire Confidence**
Clear feedback on actions, users feel empowered.

- Operations have success/failure messages
- Dangerous actions have confirmation prompts
- Progress indicators for long operations
- Always show: "We backed up your files first"
- Undo button always visible

### 9. **Everything Has a KPI**
Track and measure impact.

- Features log to Activity Logger
- Performance metrics tracked
- User impact measurable
- Before/after comparisons

**Example:**
```php
// ✅ Log KPI after treatment
\WPShadow\Core\Activity_Logger::log(
    'treatment_applied',
    array(
        'treatment_id' => $treatment_id,
        'before_value' => $before,
        'after_value'  => $after,
        'time_saved'   => $time_saved_seconds,
    )
);
```

### 10. **Beyond Pure (Privacy First)**
Privacy by design, no tracking without consent.

- No third-party API calls without consent
- No tracking without explicit opt-in
- User data encrypted and anonymized
- GDPR compliant by default
- Clear privacy policy links

### 11. **Talk-About-Worthy**
Shareable features users want to recommend.

- Features that solve real problems
- "Wow" moments that surprise users
- Social proof and testimonials encouraged
- Success stories highlighted

## CANON Pillars (Accessibility, Learning, Culture)

### 🌍 Accessibility First
**"No feature complete until accessible"**

**Requirements:**
- WCAG AA compliance required
- Keyboard navigation must work (no mouse-only interactions)
- Screen reader compatible (ARIA labels, semantic HTML)
- Color contrast validated (4.5:1 for normal text, 3:1 for large text)
- Focus indicators visible
- No time limits on interactions
- No auto-play or flashing content

**HTML Patterns:**
```php
// ✅ Always include ARIA labels and semantic HTML
<button 
    type="button" 
    class="wpshadow-action-button"
    aria-label="<?php echo esc_attr__( 'Apply security fix for SSL configuration', 'wpshadow' ); ?>"
    data-action="apply-treatment"
    data-treatment-id="ssl-redirect"
>
    <?php esc_html_e( 'Apply Fix', 'wpshadow' ); ?>
</button>

// ✅ Proper heading hierarchy
<h1><?php esc_html_e( 'WPShadow Dashboard', 'wpshadow' ); ?></h1>
<section aria-labelledby="diagnostics-heading">
    <h2 id="diagnostics-heading"><?php esc_html_e( 'Security Diagnostics', 'wpshadow' ); ?></h2>
    <h3><?php esc_html_e( 'Critical Issues', 'wpshadow' ); ?></h3>
</section>

// ✅ Form labels and descriptions
<label for="memory-limit">
    <?php esc_html_e( 'PHP Memory Limit', 'wpshadow' ); ?>
</label>
<input 
    type="text" 
    id="memory-limit" 
    name="memory_limit"
    aria-describedby="memory-limit-description"
    value="<?php echo esc_attr( $value ); ?>"
/>
<p id="memory-limit-description">
    <?php esc_html_e( 'Recommended: 256M or higher for optimal performance', 'wpshadow' ); ?>
</p>

// ✅ Status messages for screen readers
<div role="status" aria-live="polite" class="wpshadow-status">
    <?php esc_html_e( 'Treatment applied successfully', 'wpshadow' ); ?>
</div>

// ✅ Loading states
<button 
    type="button" 
    aria-busy="true" 
    aria-label="<?php echo esc_attr__( 'Scanning in progress', 'wpshadow' ); ?>"
>
    <span class="spinner" aria-hidden="true"></span>
    <?php esc_html_e( 'Scanning...', 'wpshadow' ); ?>
</button>
```

**JavaScript Patterns:**
```javascript
// ✅ Keyboard navigation support
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
    if (e.key === 'Enter' && e.target.matches('.wpshadow-action')) {
        e.preventDefault();
        executeAction(e.target);
    }
});

// ✅ Focus management in modals
function openModal() {
    const modal = document.getElementById('wpshadow-modal');
    const firstFocusable = modal.querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
    modal.style.display = 'block';
    firstFocusable.focus();
}

// ✅ Announce dynamic content changes
function updateStatus(message) {
    const statusEl = document.getElementById('wpshadow-status');
    statusEl.textContent = message;
    statusEl.setAttribute('role', 'status');
    statusEl.setAttribute('aria-live', 'polite');
}
```

**CSS Patterns:**
```css
/* ✅ Focus indicators (never remove!) */
.wpshadow-button:focus {
    outline: 2px solid #0073aa;
    outline-offset: 2px;
}

/* ✅ Color contrast WCAG AA compliant */
.wpshadow-error {
    color: #dc3232; /* Contrast ratio 4.5:1 on white */
    background: #fff;
}

/* ✅ Hidden but accessible to screen readers */
.screen-reader-text {
    position: absolute;
    width: 1px;
    height: 1px;
    overflow: hidden;
    clip: rect(1px, 1px, 1px, 1px);
    white-space: nowrap;
}

/* ✅ Respect user preferences */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
```

### 🎓 Learning Inclusive
**"Everyone learns differently. Support all learning styles."**

**Multiple Documentation Formats:**
- Text (for readers/writers)
- Video (for visual/auditory learners)
- Interactive demos (for kinesthetic learners)
- Screenshots and diagrams (for visual learners)

**Neurodiversity Support:**
- **ADHD:** Clear priorities, progress indicators, save-in-progress
- **Dyslexia:** Readable fonts (sans-serif), good line spacing, text-to-speech support
- **Autism:** Predictable patterns, explicit instructions, low sensory load
- **Anxiety:** Error recovery, preview before committing, undo functionality

**Documentation Pattern:**
```php
// ✅ Include tooltips with KB links
<span class="wpshadow-tooltip" 
      data-tooltip="<?php echo esc_attr__( 'PHP memory limit controls how much memory WordPress can use', 'wpshadow' ); ?>"
      data-kb-link="<?php echo esc_url( 'https://wpshadow.com/kb/php-memory-limit' ); ?>">
    <?php esc_html_e( 'Memory Limit', 'wpshadow' ); ?>
</span>
```

### 🌐 Culturally Respectful
**"Design for global communities, not just Western users."**

**Requirements:**
- Simple, clear English (no idioms like "break a leg" or "piece of cake")
- RTL (right-to-left) language support
- Flexible date/time/number formats
- No cultural assumptions
- Translation-ready strings (always use text domain)

**RTL Support:**
```css
/* ✅ RTL-aware CSS */
.wpshadow-panel {
    margin-inline-start: 20px; /* Use logical properties */
    padding-inline-end: 15px;
}

/* ✅ RTL overrides when needed */
[dir="rtl"] .wpshadow-icon {
    transform: scaleX(-1); /* Mirror directional icons */
}
```

**Date/Time Formatting:**
```php
// ✅ Use WordPress localization functions
$formatted_date = date_i18n( get_option( 'date_format' ), $timestamp );
$formatted_time = date_i18n( get_option( 'time_format' ), $timestamp );

// ✅ Respect timezone
$timezone_string = get_option( 'timezone_string' );
$datetime = new DateTime( 'now', new DateTimeZone( $timezone_string ) );
```

## Cross-Repository Relationships

### Ecosystem Structure
```
wpshadow/ (this repo - core hub)
├── Base classes that all modules extend
├── Free diagnostics and treatments
├── Dashboard and UI framework
├── Activity logging and KPI tracking
└── Integration points for pro modules

wpshadow-pro-*/ (spoke repos - extend core)
├── wpshadow-pro-security
├── wpshadow-pro-performance
├── wpshadow-pro-backup
└── Additional modules...

theme-wpshadow/ (companion theme)
└── Integrates with core features
```

### Integration Points

**Pro Module Structure:**
```php
// Pro modules extend core base classes
namespace WPShadow\Pro\Security;

class Diagnostic_Advanced_Firewall extends \WPShadow\Core\Diagnostic_Base {
    // Inherits all core functionality
    // Adds pro-specific features
}
```

**Module Registration:**
```php
// Pro modules register with core
add_filter( 'wpshadow_registered_diagnostics', function( $diagnostics ) {
    $diagnostics['advanced-firewall'] = \WPShadow\Pro\Security\Diagnostic_Advanced_Firewall::class;
    return $diagnostics;
} );
```

**Feature Detection:**
```php
// Core checks if pro modules are active
if ( class_exists( 'WPShadow\Pro\Security\Module' ) ) {
    // Pro security module is active
    // Enable advanced features
}

if ( function_exists( 'wpshadow_pro_backup_enabled' ) ) {
    // Pro backup module available
}
```

## Common Patterns

### Creating a New Diagnostic

**1. Create the class file:**
`includes/diagnostics/tests/class-diagnostic-example-check.php`

```php
<?php
/**
 * Example Diagnostic Check
 *
 * Checks for a specific WordPress configuration issue.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Example Diagnostic Class
 *
 * Detects when example setting is not optimally configured.
 */
class Diagnostic_Example_Check extends Diagnostic_Base {

    /**
     * The diagnostic slug
     *
     * @var string
     */
    protected static $slug = 'example-check';

    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Example Configuration Check';

    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Verifies example setting is properly configured';

    /**
     * The family this diagnostic belongs to
     *
     * @var string
     */
    protected static $family = 'configuration';

    /**
     * Run the diagnostic check.
     *
     * @since  1.2601.2148
     * @return array|null Finding array if issue found, null otherwise.
     */
    public static function check() {
        // Perform the check
        $current_value = get_option( 'example_setting', '' );
        
        if ( empty( $current_value ) ) {
            return array(
                'id'          => self::$slug,
                'title'       => self::$title,
                'description' => __( 'Example setting is not configured', 'wpshadow' ),
                'severity'    => 'medium',
                'threat_level' => 50,
                'auto_fixable' => true,
                'kb_link'     => 'https://wpshadow.com/kb/example-setting',
            );
        }
        
        return null; // No issue found
    }
}
```

**2. Register the diagnostic:**
`includes/diagnostics/class-diagnostic-registry.php`

```php
self::register( 'example-check', Diagnostic_Example_Check::class );
```

**3. Create corresponding treatment (if auto-fixable):**
`includes/treatments/class-treatment-example-check.php`

```php
<?php
/**
 * Treatment for Example Configuration
 *
 * Fixes the example setting configuration issue.
 *
 * @since   1.2601.2148
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Treatment_Example_Check Class
 */
class Treatment_Example_Check extends Treatment_Base {

    /**
     * Get the finding ID this treatment addresses.
     *
     * @since  1.2601.2148
     * @return string Finding ID.
     */
    public static function get_finding_id() {
        return 'example-check';
    }

    /**
     * Apply the treatment.
     *
     * @since  1.2601.2148
     * @return array {
     *     Result array.
     *
     *     @type bool   $success Whether treatment succeeded.
     *     @type string $message Human-readable result message.
     * }
     */
    public static function apply() {
        // Create backup if modifying files
        // (Not needed for options)
        
        // Apply the fix
        $result = update_option( 'example_setting', 'recommended_value' );
        
        if ( $result ) {
            return array(
                'success' => true,
                'message' => __( 'Example setting updated successfully', 'wpshadow' ),
            );
        }
        
        return array(
            'success' => false,
            'message' => __( 'Failed to update example setting', 'wpshadow' ),
        );
    }
}
```

### Creating an AJAX Handler

```php
<?php
/**
 * AJAX Handler for Example Action
 *
 * @since   1.2601.2148
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Example AJAX Handler
 */
class AJAX_Example_Action extends AJAX_Handler_Base {

    /**
     * Handle the AJAX request.
     *
     * @since 1.2601.2148
     * @return void Dies after sending JSON response.
     */
    public static function handle() {
        // Verify nonce and capability
        self::verify_request( 'wpshadow_example_action', 'manage_options' );
        
        // Get and sanitize parameters
        $param1 = self::get_post_param( 'param1', 'text', '', true );
        $param2 = self::get_post_param( 'param2', 'int', 0 );
        
        // Perform the action
        $result = self::do_something( $param1, $param2 );
        
        if ( $result ) {
            self::send_success( array(
                'message' => __( 'Action completed successfully', 'wpshadow' ),
                'data'    => $result,
            ) );
        } else {
            self::send_error( __( 'Action failed', 'wpshadow' ) );
        }
    }
    
    /**
     * Perform the actual operation.
     *
     * @since  1.2601.2148
     * @param  string $param1 First parameter.
     * @param  int    $param2 Second parameter.
     * @return mixed Result of operation.
     */
    private static function do_something( $param1, $param2 ) {
        // Implementation
        return true;
    }
}

// Register AJAX handler
add_action( 'wp_ajax_wpshadow_example_action', array( 'WPShadow\Admin\AJAX_Example_Action', 'handle' ) );
```

### Creating a Workflow Action

```php
<?php
/**
 * Example Workflow Action
 *
 * @since   1.2601.2148
 * @package WPShadow\Workflow
 */

declare(strict_types=1);

namespace WPShadow\Workflow;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Example Action Class
 */
class Action_Example {

    /**
     * Execute the action.
     *
     * @since  1.2601.2148
     * @param  array $config Action configuration.
     * @return array {
     *     Execution result.
     *
     *     @type bool   $success Whether action succeeded.
     *     @type string $message Result message.
     * }
     */
    public static function execute( $config ) {
        // Perform the action
        $result = self::do_action( $config );
        
        // Log to activity
        \WPShadow\Core\Activity_Logger::log(
            'workflow_action_executed',
            array(
                'action' => 'example',
                'config' => $config,
                'result' => $result,
            )
        );
        
        return array(
            'success' => true,
            'message' => __( 'Action executed successfully', 'wpshadow' ),
        );
    }
}
```

## Testing

### Manual Testing Checklist
When creating or modifying features:

- [ ] Test with keyboard only (no mouse)
- [ ] Test with screen reader (NVDA/JAWS/VoiceOver)
- [ ] Verify color contrast (use browser DevTools)
- [ ] Test on mobile devices
- [ ] Test with RTL language (Hebrew/Arabic)
- [ ] Verify all strings use text domain
- [ ] Check error messages are helpful
- [ ] Verify nonce and capability checks
- [ ] Test with WP_DEBUG enabled
- [ ] Run PHPCS: `composer phpcs`

### PHPCS (Code Standards)
```bash
# Check all files
composer phpcs

# Check specific file
vendor/bin/phpcs includes/diagnostics/class-diagnostic-example.php

# Auto-fix (where possible)
composer phpcbf
```

## Quick Reference

### Constants
```php
WPSHADOW_VERSION      // Plugin version
WPSHADOW_BASENAME     // Plugin basename
WPSHADOW_PATH         // Plugin directory path
WPSHADOW_URL          // Plugin directory URL
```

### Common Functions
```php
wpshadow_get_finding( $finding_id )         // Get finding details
wpshadow_clear_findings_cache()             // Clear diagnostics cache
wpshadow_get_treatment( $treatment_id )     // Get treatment instance
wpshadow_apply_treatment( $treatment_id )   // Apply a treatment
```

### Hooks (Actions)
```php
do_action( 'wpshadow_before_diagnostic_check', $class, $slug );
do_action( 'wpshadow_after_diagnostic_check', $class, $slug, $finding );
do_action( 'wpshadow_before_treatment_apply', $class, $finding_id, $dry_run );
do_action( 'wpshadow_after_treatment_apply', $class, $finding_id, $result );
```

### Hooks (Filters)
```php
apply_filters( 'wpshadow_registered_diagnostics', $diagnostics );
apply_filters( 'wpshadow_registered_treatments', $treatments );
apply_filters( 'wpshadow_finding_data', $finding, $diagnostic_id );
apply_filters( 'wpshadow_treatment_result', $result, $treatment_id );
```

## Common Pitfalls & Solutions

### Pitfall 1: "I'll just parse the HTML to check if a script is loaded"
**Problem:** HTML parsing is 20-67x slower than WordPress APIs  
**Solution:** Check `global $wp_scripts` first. ALWAYS.

### Pitfall 2: "I'll create multiple sequential edits"
**Problem:** Each `replace_string_in_file` call costs time and tokens  
**Solution:** Use `multi_replace_string_in_file` for independent edits

### Pitfall 3: "I'll sanitize input later"
**Problem:** Security vulnerabilities are introduced  
**Solution:** Sanitize at input, escape at output, ALWAYS

### Pitfall 4: "I don't need to document this simple method"
**Problem:** Code becomes unmaintainable  
**Solution:** Every public method gets a docblock with @since

### Pitfall 5: "I'll just assume the file structure"
**Problem:** Wrong assumptions break code  
**Solution:** Use `file_search` and `grep_search` to verify first

### Pitfall 6: "Let me make a file to document my changes"
**Problem:** Creates documentation bloat  
**Solution:** Only create docs when explicitly requested

## Workflow Efficiency Tips

### For Multi-File Operations:
1. **Plan first** - Use `manage_todo_list` to break down work
2. **Batch reads** - Read all needed files in parallel when possible
3. **Batch edits** - Use `multi_replace_string_in_file` for independent changes
4. **Verify after** - Run `get_errors` to check for issues

### For Diagnostic Creation:
1. **Search existing** - Use `grep_search` to find similar diagnostics
2. **Check WordPress APIs** - See Pattern 1 above
3. **Extend base class** - Always extend `Diagnostic_Base`
4. **Register immediately** - Add to `Diagnostic_Registry` right away
5. **Test the pattern** - Read the file back to verify structure

### For Issue Management:
1. **Batch status checks** - Check multiple issues at once
2. **Batch closures** - Use loops with GitHub API
3. **Add meaningful comments** - Explain WHY the issue was closed
4. **Verify results** - Re-query to confirm changes

## Performance Benchmarks (Real Data)

**Optimization Project Results (Jan 2026):**
- 11 diagnostics optimized from HTML parsing → WordPress APIs
- Average speed improvement: **50x faster**
- Memory reduction: **90%**
- Time saved per scan: **10.78 seconds**
- Files modified: 11
- Regressions introduced: **0**

**Key Learnings:**
- Always check WordPress globals before HTML parsing
- `global $wp_settings_fields` is incredibly powerful (used by 10+ diagnostics)
- HTML parsing is correct for DOM validation, wrong for everything else
- Performance gains compound across the entire plugin

## Resources

**Documentation:**
- [Product Philosophy](../docs/PRODUCT_PHILOSOPHY.md) - The 11 Commandments
- [Accessibility Canon](../docs/ACCESSIBILITY_AND_INCLUSIVITY_CANON.md) - The 3 Pillars
- [Coding Standards](../docs/CODING_STANDARDS.md) - Naming conventions
- [Architecture](../docs/ARCHITECTURE.md) - System design
- [Feature Matrix](../docs/FEATURE_MATRIX_DIAGNOSTICS.md) - All diagnostics
- [Admin Diagnostics Optimization](../docs/ADMIN_DIAGNOSTICS_OPTIMIZATION_COMPLETE.md) - Performance case study

**External Resources:**
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [WordPress Accessibility Handbook](https://make.wordpress.org/accessibility/handbook/)

---

**Remember:** Every feature should embody the "Helpful Neighbor" principle. Code should be secure, accessible, well-documented, and genuinely helpful to users.

**Pro Tip for AI Agents:** When in doubt, search the codebase for existing patterns. This project has 48 admin diagnostics already implemented - use them as templates!
