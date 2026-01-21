# WPShadow Sensei Course - Duplicate Check & Hierarchy Fix

## Summary

Fixed the Sensei course structure to use the proper **Course → Module → Lesson** hierarchy, ensuring lessons are organized within modules as per Sensei LMS best practices.

## Problem

When checking the database, found:
- **3 duplicate courses** (IDs 222, 227, 232) all with the same title "Plugin Management Essentials"
- **Only 1 course had proper module structure** (ID 232 with modules 233, 234)
- **Other courses had incomplete structure** (no modules, no lessons)

## Solution

### 1. Database Check
✅ Verified that course ID 232 has the proper hierarchy:
```
Course 232: Plugin Management Essentials
├── Module 233: Plugin Discovery & Trust
│   ├── Lesson 235: Finding Trusted Plugins
│   └── Lesson 236: Evaluating Plugin Quality
└── Module 234: Safe Activation & Testing
    ├── Lesson 237: Safe Activation and Testing
    └── Lesson 238: Conflict Testing and Recovery
```

### 2. Cleanup
✅ Removed incomplete/duplicate courses from database (kept 232)

### 3. Code Updates

#### Updated: [blocks/sensei-course/render.php](blocks/sensei-course/render.php)

**Before:**
```php
// Get course lessons (only from course, not modules)
$lessons = get_posts([
    'post_type'      => 'lesson',
    'post_parent'    => $course_id,
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);
```

**After:**
```php
// Get course modules
$modules = get_posts([
    'post_type'      => 'module',
    'post_parent'    => $course_id,
    'posts_per_page' => -1,
    'post_status'    => 'publish',
]);

// Get all lessons for all modules
$lessons = [];
if (!empty($modules)) {
    foreach ($modules as $module) {
        $module_lessons = get_posts([
            'post_type'      => 'lesson',
            'post_parent'    => $module->ID,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);
        $lessons = array_merge($lessons, $module_lessons);
    }
} else {
    // Fallback: get lessons directly from course (for backwards compatibility)
    $lessons = get_posts([
        'post_type'      => 'lesson',
        'post_parent'    => $course_id,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ]);
}
```

**Key improvements:**
- Now queries modules first
- Gets lessons from each module
- Includes backwards compatibility fallback
- ✅ PHP syntax validated

#### Created: [docs/SENSEI_COURSE_STRUCTURE_REPORT.md](docs/SENSEI_COURSE_STRUCTURE_REPORT.md)

Complete documentation including:
- Course hierarchy diagram
- Database structure
- Block usage instructions
- Verification results
- Metadata reference

## Final Structure

### Course Details
- **ID**: 232
- **Title**: Plugin Management Essentials
- **Points**: 50
- **Badge**: Plugin Pro
- **Description**: Master WordPress plugin management from discovery to conflict resolution

### Module 1: Plugin Discovery & Trust (ID: 233)
| Lesson | ID | Duration | Points |
|--------|----|----|--------|
| Finding Trusted Plugins | 235 | 5 min | 10 |
| Evaluating Plugin Quality | 236 | 6 min | 10 |

### Module 2: Safe Activation & Testing (ID: 234)
| Lesson | ID | Duration | Points |
|--------|----|----|--------|
| Safe Activation and Testing | 237 | 7 min | 15 |
| Conflict Testing and Recovery | 238 | 8 min | 15 |

## Verification

```bash
# Run verification
docker exec wpshadow-test php /tmp/verify-course-structure.php

# Results:
# Courses: 1 (ID 232)
# Modules: 2 (IDs 233, 234)
# Lessons: 4 (IDs 235, 236, 237, 238)
# All properly hierarchical ✅
```

## Block Integration

The Sensei Course block will now properly display:
1. Course title and excerpt
2. Modules (organizing containers)
3. Lessons within each module
4. Duration and points metadata
5. Course badge and enrollment button

### Usage
```
In Gutenberg: courseId="232"
```

## Best Practices Followed

✅ **Sensei Recommended Hierarchy**
- Course as top level
- Modules as topic organizers
- Lessons as learning units

✅ **No Duplicates**
- Single authoritative course (ID 232)
- Clean database state

✅ **Backwards Compatible**
- Block handles both old (direct lesson) and new (module-based) structures
- Fallback for courses without modules

✅ **Proper Metadata**
- Duration on each lesson
- Points per lesson
- Course badge and total points

## Status

✅ **Complete & Production Ready**

The course structure is now:
- Properly hierarchical
- Free of duplicates
- Block-compatible
- Following Sensei best practices
- Fully documented

Ready to use in WordPress posts via the WPShadow Sensei Course block!
