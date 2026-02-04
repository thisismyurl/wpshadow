# Job Board Implementation Guide

## Quick Start

### 1. Enable Job Board Features

Add to your theme's `functions.php` or create a must-use plugin:

```php
<?php
// Load all job board classes
require_once( WP_CONTENT_DIR . '/path-to-wpshadow/includes/content/jobs/class-job-application-tracker.php' );
require_once( WP_CONTENT_DIR . '/path-to-wpshadow/includes/content/jobs/class-job-board-settings.php' );
require_once( WP_CONTENT_DIR . '/path-to-wpshadow/includes/content/jobs/class-job-alerts-system.php' );
require_once( WP_CONTENT_DIR . '/path-to-wpshadow/includes/content/jobs/class-job-bulk-operations-handler.php' );

// Load blocks
require_once( WP_CONTENT_DIR . '/path-to-wpshadow/includes/content/blocks/class-job-application-form-block.php' );
require_once( WP_CONTENT_DIR . '/path-to-wpshadow/includes/content/blocks/class-advanced-job-search-block.php' );
require_once( WP_CONTENT_DIR . '/path-to-wpshadow/includes/content/blocks/class-featured-jobs-carousel-block.php' );

// Load admin
require_once( WP_CONTENT_DIR . '/path-to-wpshadow/includes/admin/job-board/class-job-board-admin-dashboard.php' );

// Load widgets
require_once( WP_CONTENT_DIR . '/path-to-wpshadow/includes/content/widgets/class-job-board-quick-stats-widget.php' );
```

### 2. Register Post Type and Taxonomies

Add to your plugin or theme:

```php
<?php
// Register Job Posting CPT
register_post_type( 'wps_job_posting', array(
    'label'  => 'Jobs',
    'public' => true,
    'has_archive' => true,
    'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields' ),
    'menu_icon' => 'dashicons-businessman',
) );

// Register Taxonomies
register_taxonomy( 'wps_job_category', 'wps_job_posting', array(
    'label' => 'Job Categories',
    'hierarchical' => true,
) );

register_taxonomy( 'wps_job_type', 'wps_job_posting', array(
    'label' => 'Job Types',
    'hierarchical' => false,
) );
```

### 3. Create Job Board Page

**Via WordPress Admin:**
1. Create new page "Job Board" or "Jobs"
2. Add blocks to the page

**Or via code:**
```php
<?php
$page = wp_insert_post( array(
    'post_title'   => 'Job Board',
    'post_content' => '<!-- wp:wpshadow/advanced-job-search /-->
<!-- wp:wpshadow/featured-jobs-carousel {"jobsToShow": 3} /-->',
    'post_status'  => 'publish',
    'post_type'    => 'page',
) );
```

### 4. Create Job Post Template

Create `single-wps_job_posting.php` in your theme:

```php
<?php
get_header();
?>

<main>
    <?php
    while ( have_posts() ) {
        the_post();
        ?>
        <article class="job-post">
            <header>
                <h1><?php the_title(); ?></h1>
                <p class="job-meta">
                    <?php 
                    $location = get_post_meta( get_the_ID(), 'wps_job_location', true );
                    if ( $location ) {
                        echo 'Location: ' . esc_html( $location );
                    }
                    ?>
                </p>
            </header>

            <div class="job-content">
                <?php the_content(); ?>
            </div>

            <aside class="job-sidebar">
                <?php
                // Display job details
                $salary_min = get_post_meta( get_the_ID(), 'wps_job_salary_min', true );
                $salary_max = get_post_meta( get_the_ID(), 'wps_job_salary_max', true );
                $deadline = get_post_meta( get_the_ID(), 'wps_job_deadline', true );
                
                if ( $salary_min && $salary_max ) {
                    echo '<p><strong>Salary:</strong> $' . number_format( $salary_min ) . ' - $' . number_format( $salary_max ) . '</p>';
                }
                
                if ( $deadline ) {
                    echo '<p><strong>Apply By:</strong> ' . date_i18n( get_option( 'date_format' ), strtotime( $deadline ) ) . '</p>';
                }
                ?>
            </aside>
        </article>

        <!-- Application Form Block -->
        <?php
        echo do_blocks( '<!-- wp:wpshadow/job-application-form {"jobPostId": ' . get_the_ID() . '} /-->' );
        ?>
        <?php
    }
    ?>
</main>

<?php get_footer();
```

### 5. Add Meta Fields to Job Post

Register meta fields for job details:

```php
<?php
function register_job_meta_fields() {
    register_post_meta( 'wps_job_posting', 'wps_job_location', array(
        'type'          => 'string',
        'show_in_rest'  => true,
        'single'        => true,
    ) );
    
    register_post_meta( 'wps_job_posting', 'wps_job_salary_min', array(
        'type'          => 'integer',
        'show_in_rest'  => true,
        'single'        => true,
    ) );
    
    register_post_meta( 'wps_job_posting', 'wps_job_salary_max', array(
        'type'          => 'integer',
        'show_in_rest'  => true,
        'single'        => true,
    ) );
    
    register_post_meta( 'wps_job_posting', 'wps_job_deadline', array(
        'type'          => 'string',
        'show_in_rest'  => true,
        'single'        => true,
    ) );
    
    register_post_meta( 'wps_job_posting', 'wps_job_featured', array(
        'type'          => 'boolean',
        'show_in_rest'  => true,
        'single'        => true,
    ) );
    
    register_post_meta( 'wps_job_posting', 'wps_job_status', array(
        'type'          => 'string',
        'show_in_rest'  => true,
        'single'        => true,
        'default'       => 'open',
    ) );
}
add_action( 'init', 'register_job_meta_fields' );
```

### 6. Enqueue Styles

```php
<?php
function enqueue_job_board_styles() {
    wp_enqueue_style(
        'wpshadow-job-board',
        get_template_directory_uri() . '/assets/css/job-board.css',
        array(),
        '1.0.0'
    );
}
add_action( 'wp_enqueue_scripts', 'enqueue_job_board_styles' );
```

## Admin Configuration

### Dashboard Widget

The Job Board Quick Stats widget automatically appears on the WordPress dashboard showing:
- Active job count
- Draft job count
- New application count
- Total application count

### Admin Pages

Three new admin pages are added:

1. **Dashboard** (`admin.php?page=job-board-dashboard`)
   - Statistics overview
   - Recent applications
   - Active jobs

2. **Applications** (`admin.php?page=job-applications`)
   - Manage all applications
   - Change statuses
   - Add notes and ratings

3. **Settings** (`admin.php?page=job-board-settings`)
   - Configure board behavior
   - Email settings
   - File upload limits
   - Filter options

## Usage Examples

### Example 1: Display All Jobs

```php
<?php
$jobs = get_posts( array(
    'post_type'      => 'wps_job_posting',
    'posts_per_page' => 12,
    'post_status'    => 'publish',
) );

foreach ( $jobs as $job ) {
    echo '<h3>' . esc_html( $job->post_title ) . '</h3>';
    echo wp_kses_post( $job->post_excerpt );
}
?>
```

### Example 2: Display Job Details

```php
<?php
$job_id = get_the_ID();
$location = get_post_meta( $job_id, 'wps_job_location', true );
$salary_min = get_post_meta( $job_id, 'wps_job_salary_min', true );
$salary_max = get_post_meta( $job_id, 'wps_job_salary_max', true );
$deadline = get_post_meta( $job_id, 'wps_job_deadline', true );

echo '<strong>Location:</strong> ' . esc_html( $location ) . '<br>';
echo '<strong>Salary:</strong> $' . number_format( $salary_min ) . ' - $' . number_format( $salary_max ) . '<br>';
echo '<strong>Apply By:</strong> ' . date_i18n( get_option( 'date_format' ), strtotime( $deadline ) ) . '<br>';
?>
```

### Example 3: Featured Jobs

```php
<?php
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

foreach ( $featured as $job ) {
    echo '<div class="featured-job">';
    echo '<h4><a href="' . esc_url( get_permalink( $job->ID ) ) . '">' . esc_html( $job->post_title ) . '</a></h4>';
    echo '</div>';
}
?>
```

### Example 4: Application Count

```php
<?php
use WPShadow\JobPostings\Job_Application_Tracker;

$job_id = get_the_ID();
$stats = Job_Application_Tracker::get_application_stats( $job_id );

echo 'Total Applications: ' . absint( $stats['total'] );
echo 'New: ' . ( isset( $stats['by_status']['new']->count ) ? $stats['by_status']['new']->count : 0 );
?>
```

## Customization

### Custom Email Templates

Override email templates in `settings` menu or via code:

```php
<?php
function customize_job_alert_email( $email, $job ) {
    $subject = 'Custom: ' . $job->post_title;
    $message = 'Your custom message here...';
    return wp_mail( $email, $subject, $message );
}
add_action( 'wpshadow_send_job_alert_email', 'customize_job_alert_email', 10, 2 );
?>
```

### Add Custom Application Fields

```php
<?php
// Register custom meta
register_post_meta( 'wps_job_posting', 'wps_custom_field', array(
    'show_in_rest' => true,
    'single'       => true,
) );

// Hook into application submission
add_action( 'wp_ajax_submit_job_application', function() {
    $custom_value = sanitize_text_field( $_POST['custom_field'] ?? '' );
    // Handle your custom field
}, 5 );
?>
```

### Customize Block Styling

```css
/* Override in your theme CSS */
.wpshadow-job-card {
    background: linear-gradient(135deg, #your-color-1, #your-color-2) !important;
}

.wpshadow-btn-primary {
    background: #your-button-color !important;
}
```

## Troubleshooting

### Applications Table Not Created

Check if tables exist:
```sql
SHOW TABLES LIKE '%wpshadow_job%';
```

If missing, manually create or trigger the hook:
```php
<?php
do_action( 'plugins_loaded' );
?>
```

### Emails Not Sending

1. Check WordPress email settings
2. Verify `wp_mail()` is working: `wp_mail( 'test@example.com', 'Test', 'Test message' );`
3. Check spam folder
4. Enable email logging plugin for debugging

### AJAX Not Working

1. Verify `wp_localize_script()` is passing nonce
2. Check browser console for JavaScript errors
3. Ensure `wp-admin/admin-ajax.php` is accessible
4. Check server error logs

## Performance Tips

1. **Add Indexes** - Applications table already has indexes on common queries
2. **Limit Records** - Use pagination for large application lists
3. **Cache Queries** - Use transients for frequently-accessed data
4. **Clean Old Data** - Archive applications older than 12 months
5. **Monitor Uploads** - Clean up old resume files monthly

## Security Best Practices

1. ✅ Validate file types on resume uploads
2. ✅ Enforce file size limits
3. ✅ Sanitize all user input
4. ✅ Escape all output
5. ✅ Verify nonces on forms
6. ✅ Check user capabilities
7. ✅ Log sensitive actions
8. ✅ Backup database regularly

## Next Steps

After setup:
1. Create job categories and types
2. Post your first job
3. Customize email templates
4. Test application submission
5. Configure alert preferences
6. Train admin staff on dashboard
7. Monitor performance

## Support Resources

- **Documentation:** See `JOB_BOARD_FEATURES.md`
- **Code Examples:** Check individual class docblocks
- **CSS Customization:** See `assets/css/job-board.css`
- **Database Schema:** Check class creation methods

## Advanced Integration

### With Zapier/IFTTT

Send applications to external systems:
```php
<?php
add_action( 'wpshadow_application_submitted', function( $application_id, $job_id ) {
    $application = get_application( $application_id );
    // Send to external API
    wp_remote_post( 'https://zapier-webhook-url', array(
        'body' => json_encode( $application ),
    ) );
}, 10, 2 );
?>
```

### With CRM Integration

Connect applications to CRM:
```php
<?php
add_action( 'wpshadow_application_submitted', function( $application_id ) {
    // Send to HubSpot, Salesforce, etc.
}, 10, 1 );
?>
```

### With Email Marketing

Add applicants to email list:
```php
<?php
add_action( 'wpshadow_application_submitted', function( $application_id ) {
    $application = get_application( $application_id );
    // Add to Mailchimp, ConvertKit, etc.
}, 10, 1 );
?>
```

---

**Happy Job Boarding! 🚀**
