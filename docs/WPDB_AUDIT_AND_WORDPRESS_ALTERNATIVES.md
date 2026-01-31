# WPShadow $wpdb Audit & WordPress-Friendly Alternatives

**Status:** Comprehensive audit of $wpdb usage across the plugin  
**Purpose:** Identify opportunities to replace direct database queries with WordPress APIs  
**Date:** January 31, 2026

---

## Executive Summary

The plugin currently contains **45+ instances** of `$wpdb` usage across approximately **12 key files**. While all queries are properly prepared and escaped, many could be replaced with WordPress-native functions for better maintainability, plugin compatibility, and future-proofing.

**Overall Assessment:** ✅ Security-wise solid (all prepared), but WordPress-unfriendly

---

## Audit Findings

### Category 1: High-Priority Replacements (Best Candidates)

These can be replaced with simple WordPress functions with minimal refactoring.

#### 1.1 Options Management (`bulk-find-replace-handler.php`, `diagnostic-scheduler.php`)

**Current Usage:**
```php
// Search for options by pattern
$option_names = $wpdb->get_col(
    $wpdb->prepare(
        "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like( $prefix ) . '%'
    )
);

// Update option value
$wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = %s WHERE option_name = %s",
        $new_value,
        $option_name
    )
);
```

**WordPress Alternatives:**
- ✅ `get_option()` - Get single option
- ✅ `update_option()` - Update single option  
- ✅ `delete_option()` - Delete single option
- ✅ `wp_load_alloptions()` - Get all options (cached)

**Recommendation:** Use `wp_load_alloptions()` with filtering for pattern searches. Loop through and call `get_option()` for individual items.

**Files Affected:**
- [includes/admin/ajax/bulk-find-replace-handler.php](includes/admin/ajax/bulk-find-replace-handler.php#L324-L337) (lines 303-337)
- [includes/utils/class-diagnostic-scheduler.php](includes/utils/class-diagnostic-scheduler.php#L396-L400) (lines 396-400)

**Impact:** Medium effort, high clarity gain

---

#### 1.2 Post Meta Operations (`bulk-find-replace-handler.php`, `.tmp-vault/includes/class-timu-vault.php`)

**Current Usage:**
```php
// Count posts with meta matching pattern
$matches = (int) $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_value LIKE %s",
        $like_pattern
    )
);

// Update post meta values
$replaced = $wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->postmeta} SET meta_value = %s WHERE meta_value LIKE %s",
        $new_value,
        $like_pattern
    )
);
```

**WordPress Alternatives:**
- ✅ `get_post_meta()` - Get post meta
- ✅ `update_post_meta()` - Update post meta
- ✅ `delete_post_meta()` - Delete post meta
- ✅ `get_posts()` with `meta_query` - Query posts by meta

**Recommendation:** Use `get_posts()` with `meta_query` parameter for searches, then loop through posts and update with `update_post_meta()`.

**Code Pattern:**
```php
$posts = get_posts( array(
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key'     => $meta_key,
            'value'   => $find,
            'compare' => 'LIKE'
        )
    )
) );

foreach ( $posts as $post ) {
    $old_value = get_post_meta( $post->ID, $meta_key, true );
    if ( false !== strpos( $old_value, $find ) ) {
        update_post_meta( 
            $post->ID, 
            $meta_key, 
            str_replace( $find, $replace, $old_value )
        );
    }
}
```

**Files Affected:**
- [includes/admin/ajax/bulk-find-replace-handler.php](includes/admin/ajax/bulk-find-replace-handler.php#L241-L275) (lines 241-275)
- [.tmp-vault/includes/class-timu-vault.php](../.tmp-vault/includes/class-timu-vault.php#L2812-L2825) (lines 2812-2825)

**Impact:** High effort, high safety gain, performance trade-off

**⚠️ Warning:** Post meta queries in bulk will be slower than direct DB queries but gain caching benefits

---

#### 1.3 Comment Operations (`bulk-find-replace-handler.php`)

**Current Usage:**
```php
// Update comment content
$replaced = $wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->comments} SET comment_content = %s WHERE comment_content LIKE %s",
        $new_value,
        $like_pattern
    )
);
```

**WordPress Alternatives:**
- ✅ `get_comments()` - Query comments
- ✅ `wp_update_comment()` - Update comment

**Code Pattern:**
```php
$comments = get_comments( array(
    'search' => $find,
    'number' => -1
) );

foreach ( $comments as $comment ) {
    if ( false !== strpos( $comment->comment_content, $find ) ) {
        wp_update_comment( array(
            'comment_ID'      => $comment->comment_ID,
            'comment_content' => str_replace( $find, $replace, $comment->comment_content )
        ) );
    }
}
```

**Files Affected:**
- [includes/admin/ajax/bulk-find-replace-handler.php](includes/admin/ajax/bulk-find-replace-handler.php#L365-L399) (lines 365-399)

**Impact:** Medium effort, high clarity, minor performance trade-off

---

#### 1.4 Post Content Operations (`bulk-find-replace-handler.php`)

**Current Usage:**
```php
// Update post content
$replaced = $wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->posts} SET post_content = %s WHERE post_content LIKE %s",
        $new_value,
        $like_pattern
    )
);
```

**WordPress Alternatives:**
- ✅ `get_posts()` - Get posts matching criteria
- ✅ `wp_update_post()` - Update post

**Code Pattern:**
```php
$posts = get_posts( array(
    'posts_per_page' => -1,
    's'              => $find
) );

foreach ( $posts as $post ) {
    if ( false !== strpos( $post->post_content, $find ) ) {
        wp_update_post( array(
            'ID'           => $post->ID,
            'post_content' => str_replace( $find, $replace, $post->post_content )
        ) );
    }
}
```

**Files Affected:**
- [includes/admin/ajax/bulk-find-replace-handler.php](includes/admin/ajax/bulk-find-replace-handler.php#L176-L225) (lines 176-225)

**Impact:** High effort, high clarity, performance trade-off

---

### Category 2: Medium-Priority (Complex but Valuable)

These require more careful refactoring but provide significant benefits.

#### 2.1 Transient Management (`class-treatment-database-transient-cleanup.php`)

**Current Usage:**
```php
// Direct deletion of expired transients
$deleted = $wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} 
         WHERE option_name LIKE %s 
         AND option_value < %d",
        $wpdb->esc_like( '_transient_timeout_' ) . '%',
        time()
    )
);
```

**WordPress Alternatives:**
- ✅ `get_transient()` - Get transient (won't work for finding expired ones)
- ✅ `delete_transient()` - Delete specific transient
- ⚠️ `wp_load_alloptions()` - For finding transient names

**Challenge:** WordPress doesn't provide a bulk "delete expired transients" API. This is one of the few cases where direct DB access is justified.

**Recommendation:** Keep this one as-is OR create a helper function that loops through all transients:

```php
public static function cleanup_expired_transients() {
    $all_options = wp_load_alloptions();
    $deleted = 0;
    
    foreach ( $all_options as $name => $value ) {
        if ( strpos( $name, '_transient_timeout_' ) === 0 ) {
            if ( (int) $value < time() ) {
                $transient_name = substr( $name, 19 ); // Remove '_transient_timeout_' prefix
                delete_transient( $transient_name );
                $deleted++;
            }
        }
    }
    
    return $deleted;
}
```

**Files Affected:**
- [includes/treatments/class-treatment-database-transient-cleanup.php](includes/treatments/class-treatment-database-transient-cleanup.php#L56-L123)

**Impact:** Medium effort, medium gain (more maintainable), performance trade-off

---

#### 2.2 Exit Followups Custom Table (`exit-followup-handlers.php`)

**Current Usage:**
```php
global $wpdb;
$table = $wpdb->prefix . 'wpshadow_exit_followups';

$followups = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}wpshadow_exit_followups f
     INNER JOIN {$wpdb->prefix}wpshadow_exit_interviews i ON f.interview_id = i.id"
);
```

**Status:** ✅ **No change recommended** - Custom tables require direct `$wpdb` access. This is appropriate usage.

**Best Practice:** Ensure table creation is done via `dbDelta()` in the appropriate hook.

---

### Category 3: Architecture/Low-Priority

These are complex or foundational and may not be worth changing.

#### 3.1 Table Cloning (`sync-clone-handler.php`)

**Current Usage:**
```php
// Get all WordPress tables
$tables = $wpdb->get_col(
    $wpdb->prepare( 
        'SHOW TABLES LIKE %s', 
        $wpdb->esc_like( $wpdb->prefix ) . '%' 
    )
);

// Clone table structure
$wpdb->query( "CREATE TABLE `{$new_table}` LIKE `{$table}`" );
```

**Status:** ✅ **No change recommended** - Table operations require direct SQL. This is necessary.

**Note:** These queries are intentionally not fully prepared (DDL doesn't support prepared statements). The table names are properly escaped via `$wpdb->esc_like()`.

---

## Detailed Recommendations by File

### Priority 1: High Impact, Medium Effort

| File | Lines | Current | WordPress Alternative | Benefit | Effort |
|------|-------|---------|----------------------|---------|--------|
| [bulk-find-replace-handler.php](includes/admin/ajax/bulk-find-replace-handler.php) | 176-225 | UPDATE posts | wp_update_post() loop | Consistency | High |
| [bulk-find-replace-handler.php](includes/admin/ajax/bulk-find-replace-handler.php) | 241-275 | UPDATE postmeta | update_post_meta() loop | Consistency | High |
| [bulk-find-replace-handler.php](includes/admin/ajax/bulk-find-replace-handler.php) | 303-337 | UPDATE options | update_option() loop | Clarity | Medium |
| [bulk-find-replace-handler.php](includes/admin/ajax/bulk-find-replace-handler.php) | 365-399 | UPDATE comments | wp_update_comment() loop | Consistency | Medium |

### Priority 2: Medium Impact, Low-Medium Effort

| File | Lines | Current | WordPress Alternative | Benefit | Effort |
|------|-------|---------|----------------------|---------|--------|
| [class-treatment-database-transient-cleanup.php](includes/treatments/class-treatment-database-transient-cleanup.php) | 56-123 | Raw DELETE | delete_transient() loop | Maintainability | Medium |
| [class-diagnostic-scheduler.php](includes/utils/class-diagnostic-scheduler.php) | 396-400 | Raw SELECT | wp_load_alloptions() | Clarity | Low |

### Priority 3: Keep As-Is (Justified)

| File | Reason |
|------|--------|
| [sync-clone-handler.php](includes/admin/ajax/sync-clone-handler.php) | DDL operations require direct SQL |
| [exit-followup-handlers.php](includes/admin/ajax/exit-followup-handlers.php) | Custom tables require $wpdb |
| [class-timu-vault.php](../.tmp-vault/includes/class-timu-vault.php) | Pro module - keep as-is |

---

## Performance Considerations

### Trade-offs Summary

| Approach | Speed | Caching | Filters | Maintainability |
|----------|-------|---------|---------|-----------------|
| Direct `$wpdb` | ⚡⚡⚡ Fast | ❌ No | ❌ No | ⚠️ Medium |
| WordPress APIs | ⚡ Slower | ✅ Yes | ✅ Yes | ✅ High |
| Hybrid (both) | ⚡⚡ Medium | ✅ Partial | ✅ Yes | ✅ High |

**Recommendation:** 
- Use WordPress APIs for general operations (posts, meta, comments, options)
- Keep `$wpdb` for: custom tables, advanced queries, bulk transient cleanup, DDL
- Profile bulk operations after refactoring

---

## Implementation Roadmap

### Phase 1: Low-Risk Changes (Week 1)
1. Replace `diagnostic-scheduler.php` options query with `wp_load_alloptions()`
2. Replace `bulk-find-replace-handler.php` options updates with `update_option()` loop
3. Run tests - ensure no regressions

### Phase 2: Medium-Risk Changes (Week 2)
1. Refactor comment updates to use `get_comments()` + `wp_update_comment()`
2. Add custom filter hooks for plugin compatibility
3. Run integration tests

### Phase 3: High-Risk Changes (Week 3-4)
1. Refactor post updates to use WordPress APIs
2. Refactor post meta updates to use WordPress APIs
3. Add caching layer if performance degrades
4. Performance benchmarking against original

### Phase 4: Custom Operations (Ongoing)
1. Review custom table operations
2. Ensure proper `dbDelta()` usage
3. Document justifications for `$wpdb` usage

---

## Code Examples

### Example 1: Options Search & Update

**Before (Direct $wpdb):**
```php
global $wpdb;
$like_pattern = '%' . $wpdb->esc_like( $find ) . '%';

$count = (int) $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_value LIKE %s",
        $like_pattern
    )
);

$wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->options} SET option_value = %s WHERE option_value LIKE %s",
        $new_value,
        $like_pattern
    )
);
```

**After (WordPress APIs):**
```php
$all_options = wp_load_alloptions();
$count = 0;

foreach ( $all_options as $name => $value ) {
    if ( false !== strpos( $value, $find ) ) {
        $new_val = str_replace( $find, $replace, $value );
        update_option( $name, $new_val );
        $count++;
    }
}
```

**Benefits:**
- ✅ Uses WordPress APIs
- ✅ Caching-aware
- ✅ Allows filters/hooks
- ✅ More readable

**Trade-off:**
- ⚠️ Slightly slower (but negligible for most sites)

---

### Example 2: Post Content Bulk Replace

**Before (Direct $wpdb):**
```php
global $wpdb;

$replaced = $wpdb->query(
    $wpdb->prepare(
        "UPDATE {$wpdb->posts} 
         SET post_content = REPLACE(post_content, %s, %s),
             post_title = REPLACE(post_title, %s, %s)
         WHERE (post_content LIKE %s OR post_title LIKE %s)
         AND post_type = %s",
        $find, $replace,
        $find, $replace,
        '%' . $wpdb->esc_like( $find ) . '%',
        '%' . $wpdb->esc_like( $find ) . '%',
        'post'
    )
);
```

**After (WordPress APIs):**
```php
$posts = get_posts( array(
    'posts_per_page' => -1,
    'post_type'      => 'post',
    's'              => $find
) );

$replaced = 0;
foreach ( $posts as $post ) {
    $updated = false;
    
    $content = $post->post_content;
    if ( false !== strpos( $content, $find ) ) {
        $content = str_replace( $find, $replace, $content );
        $updated = true;
    }
    
    $title = $post->post_title;
    if ( false !== strpos( $title, $find ) ) {
        $title = str_replace( $find, $replace, $title );
        $updated = true;
    }
    
    if ( $updated ) {
        wp_update_post( array(
            'ID'           => $post->ID,
            'post_content' => $content,
            'post_title'   => $title
        ) );
        $replaced++;
    }
}
```

**Benefits:**
- ✅ Hooks fire (pre_post_update, post_updated, etc.)
- ✅ Revisions created
- ✅ Full WordPress integration
- ✅ Plugins can filter/intercept

**Trade-off:**
- ⚠️ ~10-20% slower per post
- ⚠️ More memory usage (array of post objects)

**Optimization Options:**
1. Use `wp_update_posts()` for faster bulk updates (if available)
2. Disable revisions temporarily: `add_filter( 'wp_revisions_to_keep', '__return_zero' )`
3. Batch in chunks of 100

---

## Best Practices Going Forward

### Rule 1: WordPress APIs First
```php
// ✅ DO THIS (WordPress API first)
$posts = get_posts( array( 'meta_key' => 'my_meta', 'meta_value' => 'value' ) );

// ❌ DON'T DO THIS (unless no API exists)
global $wpdb;
$posts = $wpdb->get_results( "SELECT * FROM $wpdb->posts..." );
```

### Rule 2: $wpdb Only When Necessary
```php
// ✅ OK: Custom table or performance-critical operation
$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}custom_table" );

// ✅ OK: Bulk operations that would be too slow with APIs
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE..." );

// ❌ NOT OK: When WordPress API exists
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE..." );
// Use: wp_delete_post() instead
```

### Rule 3: Always Use $wpdb->prepare()
```php
// ✅ DO THIS
$wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->posts} WHERE ID = %d",
        $post_id
    )
);

// ❌ NEVER DO THIS
$wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ID = $post_id" );
```

### Rule 4: Document Justifications
```php
/**
 * Get all posts with custom meta using direct query.
 * 
 * Using $wpdb directly instead of get_posts() because:
 * - Needs to filter by 3+ custom meta fields
 * - Performance critical (1000+ posts)
 * - get_posts() would require 3 separate meta_query arrays
 * 
 * @since 1.2601.2200
 */
global $wpdb;
$posts = $wpdb->get_results( $wpdb->prepare( ... ) );
```

---

## Monitoring & Validation

### Checklist Before Refactoring
- [ ] All existing tests pass
- [ ] No `phpcs` violations
- [ ] No `WP_DEBUG` errors/warnings
- [ ] Performance tested (compare before/after)

### After Refactoring
- [ ] Run full test suite
- [ ] Check for new filter hooks used by plugins
- [ ] Monitor error logs (WP_DEBUG enabled)
- [ ] Load test bulk operations

---

## Summary & Next Steps

**Current State:**
- ✅ All $wpdb queries are secure (properly prepared)
- ⚠️ Many could use WordPress APIs for better integration
- ⚠️ Some code is harder to maintain than necessary

**Recommendation:**
Phase in WordPress API replacements starting with **Phase 1** items:
1. `diagnostic-scheduler.php` options query
2. `bulk-find-replace-handler.php` options updates

**Timeline:** 2-4 weeks for full implementation

**Expected Benefits:**
- Better plugin ecosystem integration
- Easier to maintain and debug
- Hooks/filters enable other plugins to extend
- Future-proof for WordPress changes

---

**Document Status:** Ready for implementation  
**Last Updated:** January 31, 2026  
**Prepared by:** GitHub Copilot

