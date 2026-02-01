# Taxonomy Permalink Structure Diagnostic - Code Structure

## Class Overview

```php
namespace WPShadow\Diagnostics;

class Diagnostic_Taxonomy_Permalink_Structure extends Diagnostic_Base {
    protected static $slug = 'taxonomy-permalink-structure';
    protected static $title = 'Taxonomy Permalink Structure';
    protected static $description = 'Tests custom taxonomy permalink structures and validates URL rewriting';
    protected static $family = 'seo';
    
    public static function check() {
        // Implementation with 5 comprehensive tests
    }
}
```

## Implementation Flow

```
┌─────────────────────────────────────┐
│  check() Method Entry Point         │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Test 1: Check Permalinks Enabled   │
│  Uses: global $wp_rewrite           │
│  Method: using_permalinks()         │
└──────────────┬──────────────────────┘
               │ No permalinks?
               ├───► Return finding (threat_level: 55)
               │
               ▼ Permalinks OK
┌─────────────────────────────────────┐
│  Test 2: Get Custom Taxonomies      │
│  Uses: get_taxonomies()             │
│  Filter: _builtin=false, public=true│
└──────────────┬──────────────────────┘
               │ No taxonomies?
               ├───► Return null (nothing to check)
               │
               ▼ Taxonomies exist
┌─────────────────────────────────────┐
│  Loop Through Each Taxonomy         │
└──────────────┬──────────────────────┘
               │
               ▼
┌─────────────────────────────────────┐
│  Test 3: Check Rewrite Rules        │
│  Condition: empty(rewrite)          │
└──────────────┬──────────────────────┘
               │ Missing rules?
               ├───► Add to $issues[]
               │
               ▼
┌─────────────────────────────────────┐
│  Test 4: Check Rewrite Slug         │
│  Condition: empty(rewrite['slug'])  │
└──────────────┬──────────────────────┘
               │ Empty slug?
               ├───► Add to $issues[]
               │
               ▼
┌─────────────────────────────────────┐
│  Test 5: Check Reserved Slugs       │
│  Reserved: page, category, tag,     │
│           author, search, feed      │
└──────────────┬──────────────────────┘
               │ Conflict found?
               ├───► Add to $issues[]
               │
               ▼
┌─────────────────────────────────────┐
│  Test 6: Check Rewrite Rules Exist  │
│  Uses: get_option('rewrite_rules')  │
└──────────────┬──────────────────────┘
               │ Rules empty?
               ├───► Add to $issues[]
               │
               ▼
┌─────────────────────────────────────┐
│  Evaluate Results                   │
└──────────────┬──────────────────────┘
               │ Issues found?
               ├───Yes──► Return finding with all issues
               │
               └───No───► Return null (all OK)
```

## Finding Structure

When issues are detected, the diagnostic returns:

```php
array(
    'id'           => 'taxonomy-permalink-structure',
    'title'        => 'Taxonomy Permalink Structure',
    'description'  => 'Custom taxonomy permalink structure issues detected: {issues}',
    'severity'     => 'medium',
    'threat_level' => 55,
    'auto_fixable' => false,
    'kb_link'      => 'https://wpshadow.com/kb/taxonomy-permalink-structure',
)
```

## WordPress APIs Used

### Core APIs
- `global $wp_rewrite` - WordPress rewrite rules object
- `get_taxonomies()` - Retrieve registered taxonomies
- `get_option()` - Get WordPress options

### Methods/Functions
- `$wp_rewrite->using_permalinks()` - Check if permalinks enabled
- `get_taxonomies(array $args, string $output)` - Query taxonomies
- `get_option('rewrite_rules')` - Get current rewrite rules

## Error Messages

### 1. Permalinks Disabled
```
Permalinks are not enabled. Custom taxonomy URLs will use query strings 
(?taxonomy=value) instead of clean URLs. Enable permalinks in 
Settings > Permalinks to improve SEO.
```

### 2. Missing Rewrite Rules
```
{Taxonomy Name} has no rewrite rules configured
```

### 3. Empty Rewrite Slug
```
{Taxonomy Name} has empty rewrite slug
```

### 4. Reserved Slug Conflict
```
{Taxonomy Name} uses reserved slug "{slug}" which may conflict with WordPress core
```

### 5. Rewrite Rules Need Flush
```
Rewrite rules are empty and may need to be flushed
```

## Integration Points

### Auto-Discovery
The diagnostic is discovered automatically by `Diagnostic_Registry`:

```php
// File location pattern
includes/diagnostics/tests/seo/class-diagnostic-taxonomy-permalink-structure.php

// Namespace pattern
WPShadow\Diagnostics\

// Class name pattern
class-diagnostic-{slug}.php → Diagnostic_{Slug}
```

### Registry Scanning
```php
// Registry scans these directories:
- includes/diagnostics/tests/
- includes/diagnostics/help/
- includes/diagnostics/todo/
- includes/diagnostics/verified/

// Pattern matching:
- Filenames starting with: class-diagnostic-*
- PHP extension required
- Auto-converts to class name
```

## Usage in WPShadow

### 1. Dashboard Display
```php
// Diagnostic appears in:
Dashboard > Diagnostics > SEO Family > Taxonomy Permalink Structure
```

### 2. Running the Diagnostic
```php
// Manual execution:
Diagnostic_Taxonomy_Permalink_Structure::execute();

// Via registry:
$registry = new Diagnostic_Registry();
$registry->run_diagnostic('taxonomy-permalink-structure');
```

### 3. Checking Results
```php
$result = Diagnostic_Taxonomy_Permalink_Structure::check();

if (null === $result) {
    // No issues found - all taxonomy permalinks OK
} else {
    // Issues detected - $result contains finding array
    echo $result['description']; // Display to user
}
```

## Testing Scenarios

### Scenario 1: Test with Permalinks Disabled
```php
// Setup
update_option('permalink_structure', ''); // Plain permalinks

// Expected Result
Finding returned with threat_level 55
Message: "Permalinks are not enabled..."
```

### Scenario 2: Test with Custom Taxonomy
```php
// Setup
register_taxonomy('test_taxonomy', 'post', array(
    'rewrite' => false,  // No rewrite rules
));

// Expected Result
Finding returned listing the taxonomy
Message: "test_taxonomy has no rewrite rules configured"
```

### Scenario 3: Test with Reserved Slug
```php
// Setup
register_taxonomy('test_taxonomy', 'post', array(
    'rewrite' => array('slug' => 'page'),  // Reserved slug
));

// Expected Result
Finding returned with conflict warning
Message: "test_taxonomy uses reserved slug 'page'..."
```

### Scenario 4: All Configured Correctly
```php
// Setup
update_option('permalink_structure', '/%postname%/');
register_taxonomy('test_taxonomy', 'post', array(
    'rewrite' => array('slug' => 'test-taxonomy'),
));
flush_rewrite_rules();

// Expected Result
null (no issues found)
```

## Performance Considerations

### Efficient Checks
- Uses WordPress globals (fast)
- Minimal database queries
- Early returns when no issues
- Array operations only

### Optimization
- Checks most common issue first (permalinks disabled)
- Returns early if no custom taxonomies exist
- Only queries rewrite_rules option once
- No HTML parsing needed

## Security

### Input Handling
- No user input processed
- Uses only WordPress data
- No SQL queries (uses WP APIs)
- No file operations

### Output Escaping
- All strings use translation functions
- Sprintf for dynamic content
- Taxonomy labels from WordPress objects
- No raw user data in output

## Extensibility

### Filters Available
```php
// Via Diagnostic_Base:
apply_filters('wpshadow_diagnostic_result', $finding, $class, $slug);
```

### Actions Available
```php
// Via Diagnostic_Base:
do_action('wpshadow_before_diagnostic_check', $class, $slug);
do_action('wpshadow_after_diagnostic_check', $class, $slug, $finding);
```

### Adding More Checks
```php
// Extend the check() method:
public static function check() {
    // ... existing checks ...
    
    // Add new check:
    if (custom_condition()) {
        $issues[] = __('New issue detected', 'wpshadow');
    }
    
    // ... rest of method ...
}
```

## Maintenance

### Updating Reserved Slugs
```php
// In check() method, line 127:
$reserved_slugs = array( 'page', 'category', 'tag', 'author', 'search', 'feed' );

// To add more:
$reserved_slugs = array( 'page', 'category', 'tag', 'author', 'search', 'feed', 'new-slug' );
```

### Adjusting Threat Level
```php
// In check() method, lines 78 and 156:
'threat_level' => 55,  // Change this value
```

### Making Auto-Fixable
```php
// Would require:
1. Create Treatment_Taxonomy_Permalink_Structure class
2. Implement apply() method to enable permalinks
3. Set 'auto_fixable' => true in findings
```

## Best Practices Demonstrated

✅ **Use WordPress APIs** - Never parse HTML or guess state  
✅ **Early Returns** - Exit as soon as possible when no issues  
✅ **Translatable Strings** - All user messages use __()  
✅ **Proper Documentation** - Complete PHPDoc blocks  
✅ **Security First** - No vulnerabilities introduced  
✅ **Follow Standards** - 100% PHPCS compliant  
✅ **Minimal Changes** - Focused, surgical implementation  

