# External Reviewer API

The Pre-Publish Review feature now supports **external reviewers** - allowing other WPShadow features to register custom content checks that run before publishing.

## Overview

The Publishing Assistant framework has been integrated into the Pre-Publish Review feature, providing an extensible API for content validation. Other features can now register reviewers that will:

- Run automatically when content is checked before publishing
- Display results in the pre-publish review panel
- Integrate seamlessly with existing built-in checks

## Usage

### Registering a Reviewer

Call `WPSHADOW_Feature_Pre_Publish_Review::register_external_reviewer()` from your feature's initialization:

```php
<?php
use WPShadow\CoreSupport\WPSHADOW_Feature_Pre_Publish_Review;

// Register your reviewer
WPSHADOW_Feature_Pre_Publish_Review::register_external_reviewer(
    'my-feature-seo-check',
    array(
        'name'        => __( 'SEO Analysis', 'wpshadow' ),
        'description' => __( 'Checks SEO optimization', 'wpshadow' ),
        'priority'    => 5,  // Lower = runs earlier
        'post_types'  => array( 'post', 'page' ),
        'severity'    => 'warning', // 'info', 'warning', or 'error'
        'callback'    => 'my_feature_review_callback',
    )
);

/**
 * Reviewer callback function.
 *
 * @param WP_Post $post Post being reviewed.
 * @return array Review results.
 */
function my_feature_review_callback( $post ) {
    $issues = array();
    
    // Example: Check for missing meta description
    $meta_desc = get_post_meta( $post->ID, '_meta_description', true );
    if ( empty( $meta_desc ) ) {
        $issues[] = array(
            'type'    => 'warning',
            'message' => __( 'Missing meta description for SEO', 'wpshadow' ),
        );
    }
    
    // Return results
    return array(
        'passed' => empty( $issues ),
        'issues' => $issues,
    );
}
```

## Reviewer Configuration

### Required Parameters

- **callback** (callable): Function to execute the review. Must accept `WP_Post` parameter and return an array.

### Optional Parameters

- **name** (string): Display name for the reviewer. Defaults to reviewer ID.
- **description** (string): Description of what the reviewer checks.
- **priority** (int): Display/execution priority. Lower numbers run first. Default: 10.
- **post_types** (array): Post types this reviewer applies to. Default: `['post', 'page']`.
- **severity** (string): Default severity level: `'info'`, `'warning'`, or `'error'`. Default: `'info'`.

## Callback Return Format

Your callback should return an array with:

```php
array(
    'passed' => true,  // Boolean: Did the check pass?
    'issues' => array(
        array(
            'type'    => 'warning',  // 'info', 'warning', 'error'
            'message' => 'Issue description',
        ),
        // ... more issues
    ),
)
```

## Helper Methods

### Get Reviewers for Post Type

```php
$reviewers = WPSHADOW_Feature_Pre_Publish_Review::get_external_reviewers_for_post_type( 'post' );
```

### Get All Reviewers

```php
$all_reviewers = WPSHADOW_Feature_Pre_Publish_Review::get_all_external_reviewers();
```

### Manually Run Reviews

```php
$post = get_post( $post_id );
$results = WPSHADOW_Feature_Pre_Publish_Review::run_external_reviews( $post );
```

## Example: SEO Analyzer Integration

```php
<?php
class My_SEO_Feature {
    
    public function init() {
        // Register SEO reviewer
        add_action( 'init', array( $this, 'register_seo_reviewer' ) );
    }
    
    public function register_seo_reviewer() {
        WPSHADOW_Feature_Pre_Publish_Review::register_external_reviewer(
            'seo-analyzer',
            array(
                'name'        => __( 'SEO Analysis', 'wpshadow' ),
                'description' => __( 'Analyzes content for SEO best practices', 'wpshadow' ),
                'priority'    => 8,
                'post_types'  => array( 'post' ),
                'severity'    => 'warning',
                'callback'    => array( $this, 'review_seo' ),
            )
        );
    }
    
    public function review_seo( $post ) {
        $issues = array();
        
        // Check title length
        $title_length = strlen( $post->post_title );
        if ( $title_length < 30 || $title_length > 60 ) {
            $issues[] = array(
                'type'    => 'warning',
                'message' => sprintf(
                    __( 'Title length is %d characters. Optimal range is 30-60.', 'wpshadow' ),
                    $title_length
                ),
            );
        }
        
        // Check for focus keyword in title
        $focus_keyword = get_post_meta( $post->ID, '_focus_keyword', true );
        if ( ! empty( $focus_keyword ) && stripos( $post->post_title, $focus_keyword ) === false ) {
            $issues[] = array(
                'type'    => 'error',
                'message' => __( 'Focus keyword not found in title', 'wpshadow' ),
            );
        }
        
        // Check content length
        $word_count = str_word_count( strip_tags( $post->post_content ) );
        if ( $word_count < 300 ) {
            $issues[] = array(
                'type'    => 'info',
                'message' => sprintf(
                    __( 'Content is %d words. Consider adding more content for better SEO.', 'wpshadow' ),
                    $word_count
                ),
            );
        }
        
        return array(
            'passed' => empty( $issues ),
            'issues' => $issues,
        );
    }
}
```

## Integration with Built-in Checks

External reviewers run automatically alongside built-in checks:

1. Built-in checks run (broken links, alt text, etc.)
2. External reviewers execute in priority order
3. All results merge into the pre-publish review panel
4. Users see a unified list of all issues

## Best Practices

1. **Keep checks fast**: Reviews run on every content check. Keep processing lightweight.
2. **Use appropriate severity**: Reserve `'error'` for critical issues that should block publishing.
3. **Clear messages**: Make issue messages actionable and understandable.
4. **Handle errors**: Wrap checks in try-catch to prevent breaking other reviewers.
5. **Test with post types**: Ensure your callback handles all specified post types correctly.

## Migration from Publishing Assistant

If you were using the disabled `class-wps-publishing-assistant.php`, migrate to this new API:

**Old (Publishing Assistant):**
```php
WPSHADOW_Publishing_Assistant::register_reviewer( 'my-check', $config );
```

**New (Pre-Publish Review):**
```php
WPSHADOW_Feature_Pre_Publish_Review::register_external_reviewer( 'my-check', $config );
```

The API is nearly identical, with these enhancements:
- Integrated with the active Pre-Publish Review feature
- Better error handling
- Unified with built-in checks

## Changelog

**Version 1.2601.75000**
- Added external reviewer registration API
- Integrated Publishing Assistant framework into Pre-Publish Review
- Added `register_external_reviewer()`, `get_external_reviewers_for_post_type()`, `run_external_reviews()` methods
