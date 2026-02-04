# Job Board Quick Reference

## Classes & Their Purposes

| Class | File | Purpose |
|-------|------|---------|
| `Job_Application_Tracker` | `class-job-application-tracker.php` | Track applications, manage status, send emails |
| `Job_Application_Form_Block` | `class-job-application-form-block.php` | Gutenberg form block for applications |
| `Job_Board_Settings` | `class-job-board-settings.php` | Centralized settings management |
| `Advanced_Job_Search_Block` | `class-advanced-job-search-block.php` | Search/filter Gutenberg block |
| `Featured_Jobs_Carousel_Block` | `class-featured-jobs-carousel-block.php` | Carousel Gutenberg block |
| `Job_Bulk_Operations_Handler` | `class-job-bulk-operations-handler.php` | Bulk edit operations |
| `Job_Alerts_System` | `class-job-alerts-system.php` | Email alerts for subscribers |
| `Job_Board_Admin_Dashboard` | `class-job-board-admin-dashboard.php` | Admin pages & interface |
| `Job_Board_Quick_Stats_Widget` | `class-job-board-quick-stats-widget.php` | Dashboard widget |

## Key Methods

### Job_Application_Tracker

```php
// Get applications
Job_Application_Tracker::get_job_applications($job_id, $args);

// Update status
Job_Application_Tracker::update_application_status($app_id, $status, $notes);

// Get stats
Job_Application_Tracker::get_application_stats($job_id);
```

### Job_Board_Settings

```php
// Get setting
Job_Board_Settings::get('setting_name', $default);

// Update setting
Job_Board_Settings::update('setting_name', $value);

// Get email template
Job_Board_Settings::get_email_template('template_name');
```

### Job_Alerts_System

```php
// Get subscriber alerts
Job_Alerts_System::get_subscriber_alerts($email);

// Created on hooks:
// - plugins_loaded (creates table)
// - wp_ajax_subscribe_job_alert (handles subscription)
// - publish_wps_job_posting (sends alerts)
```

## Gutenberg Blocks

### Job Application Form
```
<!-- wp:wpshadow/job-application-form {
  "jobPostId": 123,
  "allowCoverLetter": true,
  "allowResumeUpload": true,
  "requirePhone": true,
  "buttonText": "Submit Application"
} /-->
```

### Advanced Job Search
```
<!-- wp:wpshadow/advanced-job-search {
  "enableKeyword": true,
  "enableLocation": true,
  "enableJobType": true,
  "enableCategory": true,
  "enableExperience": true,
  "enableSalary": true,
  "buttonText": "Search Jobs"
} /-->
```

### Featured Jobs Carousel
```
<!-- wp:wpshadow/featured-jobs-carousel {
  "jobsToShow": 3,
  "showSalary": true,
  "showLocation": true,
  "showCategory": true,
  "autoPlay": true,
  "autoPlayInterval": 5000
} /-->
```

## Database Queries

### Get job applications
```php
global $wpdb;
$apps = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}wpshadow_job_applications 
     WHERE job_id = %d AND status = %s",
    $job_id, 'new'
) );
```

### Get job alerts
```php
$alerts = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}wpshadow_job_alerts 
     WHERE email = %s AND status = %s",
    $email, 'active'
) );
```

## Post Meta Fields

### Recommended meta fields

```php
register_post_meta( 'wps_job_posting', 'wps_job_location' );
register_post_meta( 'wps_job_posting', 'wps_job_salary_min' );
register_post_meta( 'wps_job_posting', 'wps_job_salary_max' );
register_post_meta( 'wps_job_posting', 'wps_job_deadline' );
register_post_meta( 'wps_job_posting', 'wps_job_featured' );
register_post_meta( 'wps_job_posting', 'wps_job_status' );
register_post_meta( 'wps_job_posting', 'wps_job_experience_level' );
```

### Get job details
```php
$location = get_post_meta( $job_id, 'wps_job_location', true );
$salary_min = get_post_meta( $job_id, 'wps_job_salary_min', true );
$salary_max = get_post_meta( $job_id, 'wps_job_salary_max', true );
$deadline = get_post_meta( $job_id, 'wps_job_deadline', true );
$featured = get_post_meta( $job_id, 'wps_job_featured', true );
$status = get_post_meta( $job_id, 'wps_job_status', true );
```

## Settings

### Key settings

| Setting | Default | Type |
|---------|---------|------|
| `jobs_per_page` | 12 | integer |
| `allow_external_applications` | true | boolean |
| `allow_internal_applications` | true | boolean |
| `require_resume_upload` | true | boolean |
| `max_file_size_mb` | 5 | integer |
| `send_applicant_confirmation` | true | boolean |
| `show_salary_in_listings` | true | boolean |
| `auto_expire_jobs` | true | boolean |
| `featured_jobs_limit` | 5 | integer |

### Get settings
```php
use WPShadow\JobPostings\Job_Board_Settings;

$per_page = Job_Board_Settings::get( 'jobs_per_page', 12 );
$file_size = Job_Board_Settings::get( 'max_file_size_mb', 5 );
$require_resume = Job_Board_Settings::get( 'require_resume_upload', true );
```

## Application Statuses

```
new         - Just submitted
reviewing   - Under review
shortlisted - Candidate shortlisted
rejected    - Application rejected
interviewed - Interview scheduled/completed
offered     - Job offer made
hired       - Candidate hired
```

## AJAX Endpoints

### Submit application
```
POST /wp-admin/admin-ajax.php
action: submit_job_application
nonce: wpshadow_job_application_nonce
job_id: (integer)
applicant_name: (string)
applicant_email: (string)
applicant_phone: (string, optional)
resume: (file, optional)
cover_letter: (string, optional)
```

### Subscribe to alerts
```
POST /wp-admin/admin-ajax.php
action: subscribe_job_alert
nonce: wpshadow_job_alert_nonce
email: (string, required)
category: (integer, optional)
job_type: (integer, optional)
location: (string, optional)
keywords: (string, optional)
frequency: (string) - weekly/daily/immediately
```

## CSS Classes

### Form & Inputs
```css
.wpshadow-form-group
.wpshadow-form-control
.wpshadow-form-success
.wpshadow-form-error
```

### Buttons
```css
.wpshadow-btn
.wpshadow-btn-primary
.wpshadow-btn-secondary
.wpshadow-btn-small
```

### Alerts
```css
.wpshadow-alert
.wpshadow-alert-success
.wpshadow-alert-danger
.wpshadow-alert-info
```

### Search & Filters
```css
.wpshadow-advanced-job-search
.wpshadow-job-search-form
.wpshadow-search-field
.wpshadow-salary-range
```

### Carousel
```css
.wpshadow-featured-jobs-carousel
.wpshadow-carousel-track
.wpshadow-job-card
.wpshadow-carousel-nav
.wpshadow-carousel-indicator
```

### Dashboard
```css
.wpshadow-dashboard-grid
.wpshadow-stat-card
.wpshadow-dashboard-widget
.wpshadow-badge
.wpshadow-job-list
```

## Common Tasks

### Display featured jobs
```php
$featured = get_posts( array(
    'post_type'      => 'wps_job_posting',
    'posts_per_page' => 5,
    'post_status'    => 'publish',
    'meta_query'     => array(
        array(
            'key'     => 'wps_job_featured',
            'value'   => '1',
            'compare' => '=',
        ),
    ),
) );
```

### Get application count for job
```php
$stats = Job_Application_Tracker::get_application_stats( $job_id );
echo 'Total: ' . $stats['total'];
echo 'New: ' . ( $stats['by_status']['new']->count ?? 0 );
```

### Mark job as hired
```php
update_post_meta( $job_id, 'wps_job_status', 'closed' );
```

### Extend job deadline
```php
$current = get_post_meta( $job_id, 'wps_job_deadline', true );
$new_deadline = date( 'Y-m-d', strtotime( '+7 days', strtotime( $current ) ) );
update_post_meta( $job_id, 'wps_job_deadline', $new_deadline );
```

### Get active jobs
```php
$active = get_posts( array(
    'post_type'      => 'wps_job_posting',
    'posts_per_page' => 50,
    'post_status'    => 'publish',
    'meta_query'     => array(
        array(
            'key'     => 'wps_job_status',
            'value'   => 'closed',
            'compare' => '!=',
        ),
    ),
) );
```

## Hooks & Filters

### Actions
```php
'plugins_loaded'                    // Create tables
'wp_ajax_submit_job_application'    // Handle app submission
'wp_ajax_subscribe_job_alert'       // Handle alert subscription
'publish_wps_job_posting'           // Send alerts for new job
'admin_menu'                        // Add admin pages
'bulk_actions-edit-wps_job_posting' // Add bulk actions
'wp_dashboard_setup'                // Register widget
```

### Available filters (can add custom)
```php
apply_filters( 'wpshadow_job_board_settings', $settings )
apply_filters( 'wpshadow_email_template', $template )
apply_filters( 'wpshadow_application_status', $status )
apply_filters( 'wpshadow_job_search_args', $args )
```

## File Upload Handling

### Allowed file types (default)
- PDF
- DOC
- DOCX

### Configure in settings
```php
Job_Board_Settings::update( 'allowed_file_types', 'pdf,doc,docx,txt' );
Job_Board_Settings::update( 'max_file_size_mb', 10 );
```

## Security Checklist

- ✅ Nonces on all forms
- ✅ `wp_verify_nonce()` check
- ✅ `current_user_can()` check
- ✅ `sanitize_*()` on input
- ✅ `esc_*()` on output
- ✅ `$wpdb->prepare()` on queries
- ✅ File type validation
- ✅ File size limit enforcement

## Performance Tips

1. **Database Indexes** - Already added on job_id, email, status, applied_at
2. **Pagination** - Use `limit` and `offset` in queries
3. **Caching** - Cache application counts with transients
4. **Lazy Loading** - Carousel images lazy load
5. **Minification** - Minify job-board.css in production

## Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| Form not submitting | Nonce missing | Check form includes nonce field |
| Emails not sent | wp_mail() disabled | Check WordPress email settings |
| Carousel not working | JavaScript error | Check browser console |
| Table not created | Hook not fired | Manually run `create_applications_table()` |
| Admin pages missing | Classes not loaded | Include all required files |
| Styles not applying | CSS not enqueued | Use `wp_enqueue_style()` |

## Testing Commands

### Test email
```php
wp_mail( 'test@example.com', 'Test', 'Test message' );
```

### Check tables
```sql
SHOW TABLES LIKE '%wpshadow_job%';
DESCRIBE wp_wpshadow_job_applications;
DESCRIBE wp_wpshadow_job_alerts;
```

### Debug logs
```php
error_log( 'Debug: ' . print_r( $variable, true ) );
```

---

**Keep this reference handy while developing! 📝**
