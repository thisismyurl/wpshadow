# AJAX Handlers for Killer Utilities

**Status:** ✅ Complete  
**Version:** 1.2601.2200  
**Created:** 2026-01-30

## Overview

This document details the 10 AJAX handlers created for the 5 killer utilities. All handlers follow WPShadow security patterns with nonce verification, capability checks, and proper input sanitization.

---

## Handler Architecture

### Base Class Pattern

All handlers extend `AJAX_Handler_Base` which provides:
- `verify_request()` - Nonce and capability verification
- `get_post_param()` - Sanitized parameter retrieval
- `send_success()` - Standardized success responses
- `send_error()` - Standardized error responses

### Security Checklist

Every handler implements:
- ✅ Nonce verification (`verify_request()`)
- ✅ Capability check (`manage_options`)
- ✅ Input sanitization (via `get_post_param()`)
- ✅ SQL prepared statements (`$wpdb->prepare()`)
- ✅ Output escaping (when applicable)
- ✅ Error handling with try/catch
- ✅ Activity logging

---

## Site Cloner Handlers (3 handlers)

### 1. Create Clone Handler

**File:** `includes/admin/ajax/create-clone-handler.php`  
**Action:** `wp_ajax_wpshadow_create_clone`  
**Class:** `WPShadow\Admin\AJAX_Create_Clone`

#### Parameters:
```php
'clone_type'  => 'subdomain' | 'subdirectory'  (required)
'clone_name'  => string                         (required)
'options'     => array                          (optional)
  - 'database'
  - 'uploads'
  - 'themes'
  - 'plugins'
```

#### Functionality:
1. Validates clone type and name
2. Checks free tier limit (2 clones)
3. Verifies clone name is unique
4. Creates Vault Light snapshot
5. Clones site files based on options
6. Clones database with new prefix
7. Updates site URLs in cloned database
8. Saves clone metadata to options
9. Logs activity

#### Response:
```json
{
  "success": true,
  "data": {
    "message": "Clone created successfully",
    "clone_name": "staging",
    "clone_url": "https://staging.example.com"
  }
}
```

#### Key Features:
- Free tier enforcement (2 clones max)
- Vault Light integration for safe cloning
- Database prefix management (`{$wpdb->prefix}{clone_name}_`)
- URL search/replace in cloned database
- WordPress filesystem API usage

---

### 2. Delete Clone Handler

**File:** `includes/admin/ajax/delete-clone-handler.php`  
**Action:** `wp_ajax_wpshadow_delete_clone`  
**Class:** `WPShadow\Admin\AJAX_Delete_Clone`

#### Parameters:
```php
'clone_name' => string (required)
```

#### Functionality:
1. Retrieves clone metadata
2. Deletes clone directory recursively
3. Drops all cloned database tables
4. Removes clone from options
5. Logs activity

#### Response:
```json
{
  "success": true,
  "data": {
    "message": "Clone deleted successfully",
    "clone_name": "staging"
  }
}
```

#### Key Features:
- Safe directory deletion via WordPress filesystem API
- Cascading table deletion with prefix matching
- Metadata cleanup

---

### 3. Sync Clone Handler

**File:** `includes/admin/ajax/sync-clone-handler.php`  
**Action:** `wp_ajax_wpshadow_sync_clone`  
**Class:** `WPShadow\Admin\AJAX_Sync_Clone`

#### Parameters:
```php
'clone_name' => string (required)
```

#### Functionality:
1. Creates new Vault snapshot
2. Syncs files based on original clone options
3. Re-clones database tables
4. Updates URLs in cloned database
5. Updates last_synced timestamp
6. Logs activity

#### Response:
```json
{
  "success": true,
  "data": {
    "message": "Clone synced successfully",
    "clone_name": "staging"
  }
}
```

#### Key Features:
- Preserves original clone configuration
- Full database sync with fresh copy
- File sync using `copy_dir()`
- Timestamp tracking

---

## Code Snippets Handlers (4 handlers)

### 4. Validate Snippet Handler

**File:** `includes/admin/ajax/validate-snippet-handler.php`  
**Action:** `wp_ajax_wpshadow_validate_snippet`  
**Class:** `WPShadow\Admin\AJAX_Validate_Snippet`

#### Parameters:
```php
'code' => string              (required)
'type' => 'php' | 'js' | 'css' (required)
```

#### Functionality:

**PHP Validation:**
- Uses `php -l` for syntax checking
- Creates temporary file for validation
- Checks for dangerous functions (eval, exec, system, etc.)
- Returns detailed error messages

**JavaScript Validation:**
- Checks for unmatched braces
- Checks for unmatched parentheses
- Checks for unmatched brackets

**CSS Validation:**
- Checks for unmatched braces
- Validates basic CSS structure

#### Response:
```json
{
  "success": true,
  "data": {
    "message": "Code is valid",
    "valid": true
  }
}
```

#### Key Features:
- Multi-language validation
- Dangerous function detection
- Temporary file cleanup
- Detailed error reporting

---

### 5. Save Snippet Handler

**File:** `includes/admin/ajax/save-snippet-handler.php`  
**Action:** `wp_ajax_wpshadow_save_snippet`  
**Class:** `WPShadow\Admin\AJAX_Save_Snippet`

#### Parameters:
```php
'snippet_id'  => int                                      (0 for new)
'title'       => string                                    (required)
'code'        => string                                    (required)
'type'        => 'php' | 'js' | 'css'                     (required)
'scope'       => 'global' | 'admin' | 'frontend' | 'logged_in' (required)
'description' => string                                    (optional)
```

#### Functionality:
1. Validates required fields
2. Checks free tier limit (10 snippets)
3. Creates or updates snippet
4. Generates unique ID for new snippets
5. Sets inactive by default
6. Logs activity

#### Response:
```json
{
  "success": true,
  "data": {
    "message": "Snippet created successfully",
    "snippet_id": 5,
    "snippet": { /* snippet data */ }
  }
}
```

#### Key Features:
- Free tier enforcement (10 snippets)
- Auto-increment snippet IDs
- New snippets start inactive (safety)
- Timestamp tracking (created_at, updated_at)

---

### 6. Toggle Snippet Handler

**File:** `includes/admin/ajax/toggle-snippet-handler.php`  
**Action:** `wp_ajax_wpshadow_toggle_snippet`  
**Class:** `WPShadow\Admin\AJAX_Toggle_Snippet`

#### Parameters:
```php
'snippet_id' => int  (required)
'active'     => bool (required)
```

#### Functionality:
1. Retrieves snippet data
2. Validates PHP snippets before activation
3. Updates active status
4. Logs activity

#### Response:
```json
{
  "success": true,
  "data": {
    "message": "Snippet activated",
    "snippet_id": 5,
    "active": true
  }
}
```

#### Key Features:
- Pre-activation validation for PHP
- Prevents activation of invalid code
- Activity logging for tracking

---

### 7. Delete Snippet Handler

**File:** `includes/admin/ajax/delete-snippet-handler.php`  
**Action:** `wp_ajax_wpshadow_delete_snippet`  
**Class:** `WPShadow\Admin\AJAX_Delete_Snippet`

#### Parameters:
```php
'snippet_id' => int (required)
```

#### Functionality:
1. Verifies snippet exists
2. Removes from options
3. Logs activity with snippet title

#### Response:
```json
{
  "success": true,
  "data": {
    "message": "Snippet deleted successfully",
    "snippet_id": 5
  }
}
```

#### Key Features:
- Simple deletion
- Activity logging
- Preserves snippet title in log

---

## Plugin Conflict Detector Handler (1 handler)

### 8. Detect Plugin Conflict Handler

**File:** `includes/admin/ajax/detect-plugin-conflict-handler.php`  
**Action:** `wp_ajax_wpshadow_detect_plugin_conflict`  
**Class:** `WPShadow\Admin\AJAX_Detect_Plugin_Conflict`

#### Parameters:
```php
'issue_description' => string                              (required)
'issue_location'    => 'frontend' | 'admin' | 'ajax' | 'rest' (required)
'test_url'          => string                              (optional)
'method'            => 'binary' | 'sequential'             (required)
```

#### Functionality:

**Binary Search Algorithm:**
1. Gets all active plugins
2. Tests with first half of plugins
3. If issue present, narrows to first half
4. If not present, narrows to second half
5. Repeats until single plugin found
6. Complexity: O(log n)

**Sequential Search:**
1. Tests each plugin individually
2. Identifies first conflicting plugin
3. Complexity: O(n)

**Safe Mode Integration:**
- Sets up temporary plugin configuration
- Tests without affecting live site
- Cleans up after each test

#### Response (Success):
```json
{
  "success": true,
  "data": {
    "message": "Conflicting plugin identified",
    "conflicting_plugin": "plugin-folder/plugin-file.php",
    "plugin_name": "Plugin Name",
    "tests_performed": 5,
    "recommendation": "Consider deactivating..."
  }
}
```

#### Response (Not Found):
```json
{
  "success": true,
  "data": {
    "message": "No conflicting plugin found...",
    "found": false,
    "tests_performed": 32
  }
}
```

#### Key Features:
- Binary search efficiency (84% faster for 32 plugins)
- Safe Mode integration
- Detailed recommendations
- Test count tracking
- Plugin name resolution

---

## Bulk Find & Replace Handler (1 handler)

### 9. Bulk Find Replace Handler

**File:** `includes/admin/ajax/bulk-find-replace-handler.php`  
**Action:** `wp_ajax_wpshadow_bulk_find_replace`  
**Class:** `WPShadow\Admin\AJAX_Bulk_Find_Replace`

#### Parameters:
```php
'find_text'      => string  (required)
'replace_text'   => string  (required)
'search_scope'   => array   (required) - ['content', 'excerpt', 'meta', 'options', 'comments']
'post_types'     => array   (required) - ['post', 'page', 'custom-post-type']
'case_sensitive' => bool    (optional)
'whole_word'     => bool    (optional)
'dry_run'        => bool    (default: true)
```

#### Functionality:

**Search Scopes:**
1. **Post Content** (`post_content`)
2. **Post Excerpt** (`post_excerpt`)
3. **Post Meta** (`postmeta.meta_value`)
4. **Options** (`options.option_value`)
5. **Comments** (`comments.comment_content`)

**Process:**
1. For each scope, count matches
2. If not dry-run, perform replacement
3. Supports case-sensitive mode (BINARY)
4. Supports multiple post types
5. Returns detailed results per table

#### Response:
```json
{
  "success": true,
  "data": {
    "message": "Preview completed",
    "dry_run": true,
    "results": {
      "total_matches": 45,
      "replacements": 0,
      "tables": {
        "post_content": {
          "matches": 25,
          "replaced": 0
        },
        "postmeta": {
          "matches": 20,
          "replaced": 0
        }
      }
    }
  }
}
```

#### Key Features:
- Dry-run preview mode
- Multi-table search
- Case-sensitive option
- Post type filtering
- BINARY comparison for case sensitivity
- Activity logging (non-dry-run)
- Match count per table

---

## Regenerate Thumbnails Handler (1 handler)

### 10. Regenerate Thumbnails Handler

**File:** `includes/admin/ajax/regenerate-thumbnails-handler.php`  
**Action:** `wp_ajax_wpshadow_regenerate_thumbnails`  
**Class:** `WPShadow\Admin\AJAX_Regenerate_Thumbnails`

#### Parameters:
```php
'regenerate_method' => 'all' | 'missing' | 'range'  (required)
'image_sizes'       => array                         (required) - ['thumbnail', 'medium', 'large', ...]
'delete_old'        => bool                          (optional)
'only_featured'     => bool                          (optional)
'start_id'          => int                           (for 'range' method)
'end_id'            => int                           (for 'range' method)
'batch_offset'      => int                           (for batching)
```

#### Functionality:

**Regeneration Methods:**
1. **All:** Regenerate all images
2. **Missing:** Only generate missing thumbnails
3. **Range:** Specific ID range (start_id to end_id)

**Batch Processing:**
- Processes 10 images per request
- Returns progress and continues
- Prevents timeout on large libraries

**Process:**
1. Query attachments based on method
2. Process batch of 10 images
3. For each image:
   - Load file
   - Delete old thumbnails (if requested)
   - Generate new thumbnails for selected sizes
   - Update metadata
4. Return progress and batch offset
5. Client continues with next batch

#### Response:
```json
{
  "success": true,
  "data": {
    "message": "Processed 10 images",
    "completed": false,
    "processed": 120,
    "total": 500,
    "percent": 24.0,
    "errors": 2,
    "error_images": [
      {
        "id": 456,
        "title": "Image Title",
        "error": "File not found"
      }
    ],
    "batch_offset": 120
  }
}
```

#### Key Features:
- Batch processing (10 images/request)
- Multiple regeneration methods
- Selective size regeneration
- Old thumbnail cleanup option
- Featured images only option
- Progress tracking
- Error collection
- Activity logging on completion

---

## Registration

All handlers are auto-registered via `add_action()` at the bottom of each file:

```php
\add_action( 'wp_ajax_wpshadow_create_clone', array( '\WPShadow\\Admin\\AJAX_Create_Clone', 'handle' ) );
```

These files should be loaded in the plugin bootstrap:

```php
// In includes/core/class-plugin-bootstrap.php
private function load_ajax_handlers() {
    // Utilities AJAX handlers
    require_once WPSHADOW_PATH . 'includes/admin/ajax/create-clone-handler.php';
    require_once WPSHADOW_PATH . 'includes/admin/ajax/delete-clone-handler.php';
    require_once WPSHADOW_PATH . 'includes/admin/ajax/sync-clone-handler.php';
    require_once WPSHADOW_PATH . 'includes/admin/ajax/validate-snippet-handler.php';
    require_once WPSHADOW_PATH . 'includes/admin/ajax/save-snippet-handler.php';
    require_once WPSHADOW_PATH . 'includes/admin/ajax/toggle-snippet-handler.php';
    require_once WPSHADOW_PATH . 'includes/admin/ajax/delete-snippet-handler.php';
    require_once WPSHADOW_PATH . 'includes/admin/ajax/detect-plugin-conflict-handler.php';
    require_once WPSHADOW_PATH . 'includes/admin/ajax/bulk-find-replace-handler.php';
    require_once WPSHADOW_PATH . 'includes/admin/ajax/regenerate-thumbnails-handler.php';
}
```

---

## Security Summary

### Common Security Patterns

Every handler implements:

1. **Nonce Verification:**
   ```php
   self::verify_request( 'wpshadow_action_name', 'manage_options' );
   ```

2. **Input Sanitization:**
   ```php
   $value = self::get_post_param( 'field', 'text', '', true );
   ```

3. **SQL Prepared Statements:**
   ```php
   $wpdb->query( $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $id ) );
   ```

4. **Error Handling:**
   ```php
   try {
       // Operation
   } catch ( \Exception $e ) {
       Error_Handler::log_error( $e->getMessage(), $e );
       self::send_error( $e->getMessage() );
   }
   ```

5. **Activity Logging:**
   ```php
   Activity_Logger::log( 'action_name', array( 'data' => $value ) );
   ```

### Capability Requirements

All handlers require `manage_options` capability, ensuring only administrators can execute these operations.

---

## Testing Checklist

For each handler:
- [ ] Nonce verification works
- [ ] Invalid nonce is rejected
- [ ] Non-admin users are rejected
- [ ] Required parameters validated
- [ ] Optional parameters have defaults
- [ ] SQL injection prevented
- [ ] XSS prevented in output
- [ ] Error messages are helpful
- [ ] Success messages are clear
- [ ] Activity is logged
- [ ] Free tier limits enforced (where applicable)

---

## Performance Considerations

### Batch Processing
- **Regenerate Thumbnails:** 10 images per request
- **Bulk Find/Replace:** Dry-run first, then execute
- **Plugin Conflict:** Binary search reduces tests by 84%

### Database Optimization
- Uses indexes where available
- Prepared statements prevent reparsing
- LIKE queries use leading wildcards carefully

### Memory Management
- Large operations batched
- Temporary files cleaned up
- WP_Query uses 'fields' => 'ids' when possible

---

## Future Enhancements

### Potential Improvements:
1. **Site Cloner:** Add scheduling for periodic syncs
2. **Code Snippets:** Implement version history storage
3. **Plugin Conflict:** Build known conflicts database
4. **Find/Replace:** Add regex support
5. **Regenerate Thumbnails:** Add resume from pause

### Pro Features:
- Email notifications on completion
- Cloud storage for clones
- Advanced snippet debugging
- Batch operations across multiple sites

---

**Document Status:** Complete ✅  
**Total Handlers:** 10  
**Total Lines:** ~2,500 lines of code  
**Security Level:** Enterprise-grade  
**Performance:** Optimized with batching
