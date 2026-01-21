# Issue Repository & Storage System - Developer Guide

## Overview

The WPSHADOW_Issue_Repository is the persistent storage layer for the Guardian Issue Detection System. It manages current detected issues and maintains historical snapshots, enabling reporting, notifications, and analytics.

**Key Responsibilities:**
- Store and retrieve detected issues
- Maintain daily snapshots for trend analysis
- Provide multisite-aware queries
- Manage automatic data cleanup
- Optimize storage with compression

---

## Architecture

### Class Hierarchy

```
WPSHADOW_Issue_Repository (final)
├── Private: Storage management
├── Private: Data serialization
├── Private: Validation
└── Private: Utility methods
```

### Storage Strategy

**WordPress Native Storage (No Custom Tables)**

```
wp_options Table
├── wpshadow_detected_issues (current issues)
├── wpshadow_report_YYYYMMDD (daily snapshots)
└── [other system options]

Data Format: JSON with optional gzip compression
Serialization: json_encode/json_decode
Cleanup: Automatic (trigger on store operations)
```

### Data Flow

```
Detection Phase:
Detectors → Issue Objects → Registry
                              ↓
Storage Phase:
Registry → Repository.store_issues() → wp_options
                ↓
Snapshot:
create_daily_snapshot() → wpshadow_report_YYYYMMDD

Retrieval Phase:
wp_options → Repository → Dashboard/Email/Analytics
```

---

## Core Operations

### 1. Storing Issues

#### Single Issue Storage
```php
$repository = new WPSHADOW_Issue_Repository();

$issue = array(
    'id' => 'detector-issue-001',
    'severity' => 'critical',
    'title' => 'Security Vulnerability',
    'description' => 'A serious security issue was detected',
    'detector_id' => 'security-checker',
    'confidence' => 0.98,
);

$success = $repository->store_issue( 'detector-issue-001', $issue );
// Returns: boolean
```

**Key Points:**
- Issue ID is passed separately and merged into array
- `detected_at` timestamp auto-generated if missing
- Replaces existing issue with same ID
- Triggers daily snapshot creation

#### Batch Storage
```php
$issues = array(
    'issue-1' => [ /* issue data */ ],
    'issue-2' => [ /* issue data */ ],
    'issue-3' => [ /* issue data */ ],
);

$repository->store_issues( $issues );
// Recommended for detector output
// More efficient than multiple store_issue() calls
```

**Batch Operations Optimization:**
- Single wp_option update
- Single snapshot creation
- All issues normalized together
- Compression applied once

### 2. Retrieving Issues

#### Get All Current Issues
```php
$all_issues = $repository->get_current_issues();

// Returns array keyed by issue ID:
// array(
//   'issue-1' => [ /* issue data */ ],
//   'issue-2' => [ /* issue data */ ],
// )
```

#### Get Specific Issue
```php
$issue = $repository->get_issue( 'detector-issue-001' );

if ( null === $issue ) {
    // Issue not found
} else {
    // Use issue data
    $severity = $issue['severity'];
}
```

#### Filter by Severity
```php
$critical_issues = $repository->get_issues_by_severity( 'critical' );
$high_issues = $repository->get_issues_by_severity( 'high' );

// Returns filtered array or empty array if none found
```

### 3. Deleting Issues

#### Delete Single Issue
```php
$deleted = $repository->delete_issue( 'detector-issue-001' );

if ( $deleted ) {
    // Successfully removed
} else {
    // Issue didn't exist or error occurred
}
```

#### Delete All Issues
```php
$repository->delete_all_current_issues();
// Clears entire wpshadow_detected_issues option
```

---

## Snapshot Management

### Understanding Snapshots

**Purpose:** Create historical record of issues at specific points in time

**Trigger:** Automatically created when storing issues

**Format:**
```php
array(
    'timestamp'          => 1234567890,      // Unix time
    'date'              => '20240115',       // YYYYMMDD format
    'total_issues'      => 5,                // Issue count
    'severity_breakdown' => array(           // Issues per severity
        'critical' => 2,
        'high'     => 1,
        'medium'   => 1,
        'low'      => 1,
    ),
    'issues'            => array(...),       // Snapshot of all issues
)
```

### Manual Snapshot Creation

```php
$issues = $registry->get_all_issues();

// Create custom snapshot
$repository->create_daily_snapshot( $issues );
// Returns: boolean
```

**Automatic Behavior:**
- Creates snapshot with today's date
- Only one snapshot per day
- Later calls same day overwrite previous
- Replaces old snapshot at key `wpshadow_report_YYYYMMDD`

### Retrieving Snapshots

#### Get Specific Date
```php
$snapshot = $repository->get_snapshot( '20240115' );

if ( null === $snapshot ) {
    // No snapshot for that date
} else {
    $total = $snapshot['total_issues'];
    $breakdown = $snapshot['severity_breakdown'];
}
```

#### Get Date Range
```php
$snapshots = $repository->get_snapshots_between( '2024-01-01', '2024-01-31' );

// Returns array keyed by YYYYMMDD:
// array(
//   '20240101' => [ /* snapshot */ ],
//   '20240102' => [ /* snapshot */ ],
//   ...
// )
```

#### Get Recent History
```php
// Last 7 days
$week = $repository->get_history( 7 );

// Last 30 days (default)
$month = $repository->get_history();

// Returns array newest first (today at key 0)
// array(
//   '20240115' => [ /* today */ ],
//   '20240114' => [ /* yesterday */ ],
//   '20240113' => [ /* 2 days ago */ ],
//   ...
// )
```

#### Get Most Recent
```php
$latest = $repository->get_latest_snapshot();

if ( null === $latest ) {
    // No snapshots yet
} else {
    // Most recent snapshot data
}
```

---

## Analytics & Reporting

### Issue Statistics

#### Current Issue Count
```php
$count = $repository->get_issue_count();
// Returns: int (0 if no issues)

// Alternative check
if ( $repository->has_issues() ) {
    // At least one issue exists
}
```

#### Severity Breakdown
```php
$breakdown = $repository->get_severity_breakdown();

// Returns:
// array(
//   'critical' => 2,  // Number of critical issues
//   'high'     => 3,  // Number of high issues
//   'medium'   => 1,  // Number of medium issues
//   'low'      => 0,  // Number of low issues
// )
```

#### Snapshot Analysis
```php
$stats = $repository->get_snapshot_statistics();

// Returns comprehensive analysis:
// array(
//   'total_snapshots' => 30,           // Number of snapshots
//   'date_range' => array(
//       'start' => '20240101',         // First snapshot
//       'end'   => '20240131',         // Most recent snapshot
//   ),
//   'average_issues'  => 5.2,          // Average issues per day
//   'peak_issues'     => 12,           // Highest issue count
//   'lowest_issues'   => 1,            // Lowest issue count
//   'trend'           => 'increasing', // 'increasing'|'decreasing'|'stable'
// )
```

**Trend Calculation:**
- Last day vs first day of range
- Increasing: >10% growth
- Decreasing: >10% decline
- Stable: ±10% variation

---

## Export & Format Conversion

### Export Snapshots

#### JSON Export
```php
$json = $repository->export_snapshot( '20240115', 'json' );

// Returns JSON string:
// {
//   "timestamp": 1234567890,
//   "date": "20240115",
//   "total_issues": 5,
//   "severity_breakdown": {...},
//   "issues": {...}
// }

// Use for API responses or external systems
```

#### CSV Export
```php
$csv = $repository->export_snapshot( '20240115', 'csv' );

// Returns CSV string:
// Date,Total Issues,Critical,High,Medium,Low
// 20240115,5,2,1,1,1
//
// Use for spreadsheet imports or reporting
```

#### Invalid Snapshot
```php
$json = $repository->export_snapshot( '19700101', 'json' );
// Returns empty string if snapshot doesn't exist
```

---

## Data Serialization

### Compression Strategy

**Automatic Compression:**
- Payloads >10KB automatically gzipped
- Base64 encoded for storage safety
- Prefix `gzipped:` marks compressed data
- Transparent decompression on retrieval

**Compression Benefits:**
- ~70% size reduction typical
- Faster database reads (less data to transfer)
- Automatic for large issue sets

**Example:**
```php
// 100 issues → ~50KB JSON
// After compression → ~15KB encoded
// Saves database and network overhead
```

### JSON Serialization

**All data must be JSON-serializable:**

✅ Supported:
- strings, integers, floats, booleans
- arrays (associative and indexed)
- null values
- timestamps (as integers)

❌ Not Supported (will cause issues):
- Objects (use array instead)
- Resources
- Closures/functions
- Circular references

**Validation:**
```php
// Repository automatically validates:
$issue = array(
    'custom_object' => new stdClass(), // ❌ ERROR
);

// Will be skipped or converted to array
```

---

## Multisite Support

### Automatic Context Detection

```php
public function __construct() {
    $this->multisite_enabled = is_multisite();
}
```

**Behavior:**
- Single site: Uses `update_option()` / `get_option()`
- Multisite: Uses `update_site_option()` / `get_site_option()`

### Multisite Queries

#### Get Site-Specific Issues
```php
// Current site
$current_issues = $repository->get_current_issues();

// Specific site (in network context)
$site_issues = $repository->get_multisite_issues( 123 );
```

#### Querying Other Sites
```php
// Switch context and query
$other_site_issues = $repository->get_multisite_issues( $site_id );

// Manual approach:
switch_to_blog( $site_id );
$issues = $repository->get_current_issues();
restore_current_blog();
```

### Network-Wide Reporting

```php
// Get all sites' issues
function get_all_sites_issues() {
    global $wpdb;
    $sites = get_sites();
    $all_issues = array();
    
    foreach ( $sites as $site ) {
        $issues = $repository->get_multisite_issues( $site->blog_id );
        $all_issues[ $site->blog_id ] = $issues;
    }
    
    return $all_issues;
}
```

---

## Cleanup & Maintenance

### Automatic Cleanup

**Retention Policy:**
- Snapshots older than 90 days deleted automatically
- Cleanup triggered on every snapshot creation
- No manual intervention needed

**Deletion Pattern:**
```
Today: 2024-01-15
Keep: 2023-10-17 through 2024-01-15 (90 days)
Delete: 2023-10-16 and older
```

### Manual Cleanup

```php
// Manually trigger cleanup
$deleted_count = $repository->cleanup_old_snapshots();
// Returns: int (number of options deleted)

// Check before cleanup
$stats = $repository->get_snapshot_statistics();
if ( $stats['total_snapshots'] > 100 ) {
    // Too many snapshots, consider cleanup or archiving
}
```

### Archival Strategy

For compliance/analytics, archive old data:

```php
// Export before cleanup
$old_snapshots = $repository->get_snapshots_between( '2023-01-01', '2023-12-31' );

foreach ( $old_snapshots as $date => $snapshot ) {
    $json = $repository->export_snapshot( $date, 'json' );
    // Save to external storage (S3, file, etc.)
}

// Then cleanup happens automatically
```

---

## Data Validation

### Automatic Validation

Repository performs validation on all storage:

```php
// Severity validation
$issue = array(
    'severity' => 'invalid-severity', // ❌ Invalid
);
$repository->store_issue( 'id', $issue );
$stored = $repository->get_issue( 'id' );
echo $stored['severity']; // Outputs: 'medium' (default)
```

### Severity Constants

```php
// Valid severity levels (from WPSHADOW_Issue_Detection):
WPSHADOW_Issue_Detection::SEVERITY_CRITICAL  // 'critical'
WPSHADOW_Issue_Detection::SEVERITY_HIGH      // 'high'
WPSHADOW_Issue_Detection::SEVERITY_MEDIUM    // 'medium'
WPSHADOW_Issue_Detection::SEVERITY_LOW       // 'low'
```

### Required Fields

```php
// Minimum required fields:
$issue = array(
    'id'       => 'unique-id',        // Required
    'severity' => 'critical',         // Required (validated)
);

// Auto-generated fields:
// - 'detected_at' => current_time (if missing)

// Optional fields:
// - 'title', 'description', 'detector_id', 'confidence', 'data', etc.
```

---

## Integration Examples

### With Detection System

```php
use WPShadow\CoreSupport\WPSHADOW_Issue_Registry;
use WPShadow\CoreSupport\WPSHADOW_Issue_Repository;

// Get detected issues
$registry = WPSHADOW_Issue_Registry::get_instance();
$detected = $registry->get_all_issues();

// Store in repository
$repository = new WPSHADOW_Issue_Repository();
$repository->store_issues( $detected );
```

### With Dashboard Widget

```php
// Get current status
$repository = new WPSHADOW_Issue_Repository();
$count = $repository->get_issue_count();
$breakdown = $repository->get_severity_breakdown();
$trend = $repository->get_snapshot_statistics()['trend'];

// Render dashboard
echo "Total Issues: " . $count;
echo "Critical: " . $breakdown['critical'];
echo "Trend: " . $trend;
```

### With Email Notifications

```php
// Get recent issues for notification
$repository = new WPSHADOW_Issue_Repository();
$critical = $repository->get_issues_by_severity( 'critical' );
$latest = $repository->get_latest_snapshot();

// Send email with critical issues and snapshot stats
$email_body = "Critical Issues: " . count( $critical );
$email_body .= "\nLatest Stats: " . json_encode( $latest['severity_breakdown'] );
```

### With Auto-Fix System

```php
// Get fixable issues
$repository = new WPSHADOW_Issue_Repository();
$all_issues = $repository->get_current_issues();

foreach ( $all_issues as $issue_id => $issue ) {
    if ( $issue['auto_fixable'] ) {
        // Apply fix
        $fixed = apply_auto_fix( $issue );
        
        if ( $fixed ) {
            // Remove from current issues
            $repository->delete_issue( $issue_id );
        }
    }
}
```

---

## Performance Considerations

### Query Performance

**Efficient Patterns:**
```php
// ✅ Good: Single batch operation
$repository->store_issues( $all_issues );

// ❌ Poor: Multiple individual calls
foreach ( $issues as $id => $data ) {
    $repository->store_issue( $id, $data );
}
```

**Why Batch is Better:**
- Single wp_option update
- Single snapshot creation
- Better for database performance

### Memory Optimization

**For Large Issue Sets (500+ issues):**

```php
// ✅ Efficient: Lazy-load history
$recent = $repository->get_history( 7 );  // Only last week
// Instead of:
$all = $repository->get_history( 365 );   // Full year

// ✅ Efficient: Filter after retrieval
$critical = array_filter( $issues, function( $i ) {
    return $i['severity'] === 'critical';
});
```

### Database Optimization

**Storage Strategy:**
- JSON data not queried directly (no WHERE clauses on issue fields)
- Compression reduces disk I/O
- Cleanup prevents unlimited growth
- Daily snapshots prevent excessive writes

---

## Testing

### Unit Test Coverage

All Repository methods tested:

```
✓ Store operations (single, batch, empty)
✓ Retrieve operations (all, single, filtered)
✓ Delete operations (single, all, non-existent)
✓ Snapshot operations (create, get, range, history)
✓ Analytics (count, breakdown, statistics)
✓ Export (JSON, CSV)
✓ Data validation and serialization
✓ Compression (large data)
✓ Multisite context
✓ Edge cases and error conditions
```

### Test Patterns

```php
// Testing storage
$repository->store_issue( 'test-id', $issue_data );
$retrieved = $repository->get_issue( 'test-id' );
$this->assertEquals( $issue_data['id'], $retrieved['id'] );

// Testing snapshots
$repository->create_daily_snapshot( $issues );
$snapshot = $repository->get_snapshot( gmdate( 'Ymd' ) );
$this->assertIsArray( $snapshot );
$this->assertEquals( count( $issues ), $snapshot['total_issues'] );
```

---

## Troubleshooting

### Issue Not Found

**Problem:** `get_issue()` returns null

**Solutions:**
1. Verify issue ID is correct: `$repository->get_current_issues();`
2. Check case sensitivity (IDs are case-sensitive)
3. Verify issue was stored: `$repository->has_issues();`

### Snapshot Not Available

**Problem:** `get_snapshot()` returns null

**Solutions:**
1. Verify date format is YYYYMMDD: `gmdate( 'Ymd' )`
2. Check if snapshot exists: `$repository->get_history( 90 );`
3. Create if missing: `$repository->create_daily_snapshot( $issues );`

### Compression Issues

**Problem:** Corrupted data after retrieval

**Solutions:**
1. Verify JSON serialization of custom data
2. Check for circular references (not allowed)
3. Clear options cache: `wp_cache_flush();`

### Multisite Issues

**Problem:** Wrong site data returned

**Solutions:**
1. Verify site ID correct: `get_current_blog_id()`
2. Use `switch_to_blog()` when needed
3. Confirm multisite enabled: `is_multisite()`

---

## Best Practices

### Do's ✅
- Use batch operations for multiple issues
- Manually cleanup old data before archiving
- Validate issue data before storing
- Use exported snapshots for external reporting
- Test with large datasets (500+ issues)

### Don'ts ❌
- Don't store unserialized objects
- Don't bypass validation for custom fields
- Don't ignore compression warnings
- Don't query issues directly from wp_options
- Don't mix multisite and single-site contexts

### Code Examples

**Good Pattern:**
```php
$issues = $detector->get_detected_issues();
$repository = new WPSHADOW_Issue_Repository();
$repository->store_issues( $issues );
$stats = $repository->get_snapshot_statistics();
```

**Avoid Pattern:**
```php
foreach ( $issues as $id => $issue ) {
    $repository->store_issue( $id, $issue );  // Multiple DB writes
}
```

---

## Summary

The WPSHADOW_Issue_Repository provides a robust, tested, and performant storage layer for the Guardian System. It handles:

- ✅ Persistent storage using WordPress native methods
- ✅ Historical tracking via daily snapshots
- ✅ Automatic cleanup and compression
- ✅ Multisite compatibility
- ✅ JSON serialization and validation
- ✅ Export for external reporting
- ✅ Analytics and trend detection

Use this guide to integrate storage across all Guardian System components (dashboard, email, auto-fix, etc.).
