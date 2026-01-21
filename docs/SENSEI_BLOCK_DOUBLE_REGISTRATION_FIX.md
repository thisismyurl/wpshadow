# WPShadow Sensei Course Block - Double Registration Fix

## Issue Summary

**Error**: `Function WP_Block_Type_Registry::register was called incorrectly. Block type "wpshadow/sensei-course" is already registered.`

**Symptom**: WordPress admin would not load correctly due to header output errors caused by the registration notice.

---

## Root Cause

Two registration mechanisms were active simultaneously:

1. **PHP Class Approach** (OLD)
   - File: `includes/blocks/class-sensei-course-block.php`
   - Method: `add_action('init', [Sensei_Course_Block::class, 'register'])`
   - Called: `register_block_type('wpshadow/sensei-course', [...])`

2. **block.json Approach** (MODERN)
   - File: `blocks/sensei-course/block.json`
   - Registration: `register_block_type(plugin_dir . 'blocks/sensei-course')`
   - Hook: `add_action('init', ..., 5)` in `wpshadow.php`

Both fired on the `init` hook, causing WordPress to register the block twice.

---

## Solution Implemented

### Changes Made

1. **Deleted**: `includes/blocks/class-sensei-course-block.php`
   - Removed the redundant class-based registration

2. **Edited**: `wpshadow.php`
   - Line 1351: Removed `require_once` for the deleted class file

### Why This Approach?

The **block.json approach** is:
- ✅ Modern WordPress standard (5.3+)
- ✅ Simpler and cleaner
- ✅ Supports static rendering
- ✅ Better for block discovery
- ✅ Single point of registration

---

## Final Architecture

```
wpshadow.php (init hook, priority 5)
  └── register_block_type('blocks/sensei-course')
      └── Reads: blocks/sensei-course/block.json
      └── Calls: blocks/sensei-course/render.php
```

**Single registration point**. Clean execution.

---

## Files Changed

| File | Action | Reason |
|------|--------|--------|
| `includes/blocks/class-sensei-course-block.php` | DELETED | Redundant registration |
| `wpshadow.php` line 1351 | REMOVED | Removes require_once for deleted file |

---

## Verification

✅ No "already registered" errors  
✅ No header modification warnings  
✅ Block registers cleanly in Gutenberg  
✅ Course displays correctly (ID 232, 2 modules, 4 lessons)  
✅ wpshadow.php PHP syntax valid  

---

## Block File Structure (Preserved)

```
blocks/sensei-course/
├── block.json           Configuration (title, description, attributes)
├── index.js             Gutenberg editor component
├── render.php           Server-side rendering logic
├── editor.css           Editor-only styles
├── style.css            Frontend wrapper styles
└── view.js              Frontend interactivity
```

---

## Testing

Run verification script:
```bash
docker exec wpshadow-test php /tmp/verify-course-structure.php
```

Expected output:
- No PHP notices/warnings about registration
- Course displays with full hierarchy
- All lessons visible with metadata

---

## Impact

- ✅ WordPress admin loads without errors
- ✅ Headers sent correctly
- ✅ Block editor functions properly
- ✅ No performance impact
- ✅ Zero breaking changes

---

**Status**: ✅ FIXED & PRODUCTION READY  
**Date**: January 21, 2026  
**Affected Block**: wpshadow/sensei-course
