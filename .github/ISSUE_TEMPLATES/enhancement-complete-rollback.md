# [Enhancement] Complete Rollback/Undo Implementation for All Treatments

**Labels:** `enhancement`, `treatments`, `rollback`, `safety`
**Assignee:** TBD
**Milestone:** v1.3
**Priority:** High

## Current State
Rollback infrastructure **exists** in `Treatment_Base`:
- ✅ `record_rollback_info()` method
- ✅ `get_rollback_history()` method
- ✅ `undo()` abstract method
- ✅ `execute_undo()` wrapper with hooks
- ✅ `wpshadow_rollback_log` option (stores last 100 treatments)

**However**, many treatments show:
```php
// TODO: Implement real diagnostic logic here
```

This means the `undo()` method is **not implemented** in many treatment classes.

## Problem Statement
**User Safety Concern**: "What if the auto-fix breaks my site? Can I undo it?"

**Current State**:
- Rollback system exists architecturally
- Individual treatments may not implement `undo()`
- No UI button to trigger rollback
- Users don't know rollback is possible

## Proposed Solution
1. **Audit all treatments** - Identify which have `undo()` implemented
2. **Implement missing `undo()` methods** - Priority for high-risk treatments
3. **Add UI for rollback** - "Undo" button in Activity History
4. **Add safety checks** - Prevent undo if site state changed
5. **Document rollback behavior** - KB article explaining limitations

## Implementation Checklist

### Phase 1: Audit & Documentation
- [ ] Scan all treatment files for `undo()` implementation
- [ ] Create matrix: Treatment → Has Undo → Complexity → Priority
- [ ] Document which treatments are safe to undo
- [ ] Identify treatments that CANNOT be undone (e.g., deleted data)

### Phase 2: Implement Missing Undo Methods
Priority order:
1. **Critical** - Security treatments (SSL, permissions, exposed files)
2. **High** - Performance treatments (caching, database)
3. **Medium** - Configuration treatments (wp-config, htaccess)
4. **Low** - Cosmetic treatments (admin UI, branding)

### Phase 3: UI Implementation
- [ ] Add "Undo" button to Activity History entries
- [ ] Show button only for treatments with `undo()` implemented
- [ ] Add confirmation dialog: "Are you sure you want to undo this fix?"
- [ ] Show rollback result (success/failure message)
- [ ] Update activity log after successful undo
- [ ] Disable "Undo" if too much time has passed (safety check)

### Phase 4: Safety & Validation
- [ ] Add timestamp check: Only allow undo within 24 hours
- [ ] Add state validation: Check if site changed since treatment
- [ ] Add dry-run mode for undo (preview what will change)
- [ ] Add rollback confirmation requirements for critical changes
- [ ] Log all undo attempts (success and failures)

### Phase 5: Documentation
- [ ] Add KB article: "How Rollback Works"
- [ ] Document which treatments are undoable
- [ ] Explain limitations (time limits, state changes)
- [ ] Add inline help text in Activity History
- [ ] Update treatment descriptions to mention "Can be undone"

## Example Implementations

### File-Based Treatment
```php
// Treatment: Disable File Editing
public static function apply() {
    $wp_config = ABSPATH . 'wp-config.php';
    $content = file_get_contents( $wp_config );

    // Backup original
    update_option( 'wpshadow_backup_wpconfig_file_edit', $content );

    // Apply fix
    $new_content = str_replace(
        "<?php",
        "<?php\ndefine('DISALLOW_FILE_EDIT', true);",
        $content
    );
    file_put_contents( $wp_config, $new_content );

    return array( 'success' => true );
}

public static function undo() {
    $backup = get_option( 'wpshadow_backup_wpconfig_file_edit' );
    if ( ! $backup ) {
        return array( 'success' => false, 'message' => 'No backup found' );
    }

    $wp_config = ABSPATH . 'wp-config.php';
    file_put_contents( $wp_config, $backup );
    delete_option( 'wpshadow_backup_wpconfig_file_edit' );

    return array( 'success' => true, 'message' => 'Reverted to original' );
}
```

### Database-Based Treatment
```php
// Treatment: Update Memory Limit in DB
public static function apply() {
    $old_value = get_option( 'memory_limit' );
    update_option( 'wpshadow_backup_memory_limit', $old_value );
    update_option( 'memory_limit', '512M' );
    return array( 'success' => true );
}

public static function undo() {
    $backup = get_option( 'wpshadow_backup_memory_limit' );
    if ( false === $backup ) {
        return array( 'success' => false, 'message' => 'No backup found' );
    }
    update_option( 'memory_limit', $backup );
    delete_option( 'wpshadow_backup_memory_limit' );
    return array( 'success' => true );
}
```

### Non-Undoable Treatment
```php
// Treatment: Delete Expired Transients (can't undo)
public static function undo() {
    return array(
        'success' => false,
        'message' => __( 'This treatment cannot be undone. Expired transients were permanently deleted.', 'wpshadow' )
    );
}
```

## Philosophy Alignment
✅ **Philosophy #8**: Inspire confidence - users trust auto-fix more with undo
✅ **Philosophy #9**: Show value - track rollbacks to improve treatments
✅ **Philosophy #2**: Free feature (no external services required)

## User Stories
1. **Nervous User**: "I want to try the auto-fix but I'm scared it will break my site"
2. **Experimenter**: "Let me test this treatment and undo if it doesn't work"
3. **Regretful User**: "The fix made my site slower, I want to undo it"

## Audit Findings (Sample)
| Treatment | Has Undo | Priority | Complexity |
|-----------|----------|----------|------------|
| SSL Redirect | ❌ No | Critical | Medium |
| File Permissions | ❌ No | Critical | High |
| Transient Cleanup | ✅ N/A (can't undo) | Low | - |
| Memory Limit | ❌ No | High | Low |
| Debug Mode Disable | ❌ No | High | Low |

## Success Metrics
- % of treatments with `undo()` implemented (target: 80%+)
- Undo success rate (target: >95%)
- Undo usage rate (expect 5-10% of treatments to be undone)
- Support tickets about "broken site after fix" (target: -50%)

## Related Files
- `includes/core/class-treatment-base.php` - Base class with rollback infrastructure
- `includes/treatments/` - All treatment implementations
- `includes/views/activity-history.php` - UI for activity log
- `includes/admin/class-guardian-dashboard.php` - Treatment application UI

## Future Enhancements
- **Automatic rollback** - If site goes down after treatment, auto-undo
- **Rollback snapshots** - Full database/file backup before critical treatments
- **Undo history** - Show all rollbacks performed
- **Bulk undo** - Undo all treatments from a specific date
