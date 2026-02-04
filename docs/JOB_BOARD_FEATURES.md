# Job Board Features Documentation

## Overview

The WPShadow Job Board is a comprehensive, easy-to-use job listing and application management system. It provides everything needed to run a professional job board on WordPress.

## Features

### 1. Job Application Tracking (`Job_Application_Tracker`)

**Purpose:** Manages job applications and applicant tracking.

**Key Features:**
- Database table for storing applications
- AJAX form submission handling
- Application status tracking (new, reviewing, shortlisted, rejected, interviewed, offered, hired)
- Automatic confirmation emails to applicants
- Notification emails to job posters
- Application statistics and reporting
- Rating system for applications
- Notes field for internal comments

**Usage:**
```php
use WPShadow\JobPostings\Job_Application_Tracker;

// Get applications for a job
$applications = Job_Application_Tracker::get_job_applications($job_id);

// Update application status
Job_Application_Tracker::update_application_status($application_id, 'interviewed');

// Get statistics
$stats = Job_Application_Tracker::get_application_stats($job_id);
```

**Database Table:**
- `id` - Application ID
- `job_id` - Job post ID
- `applicant_name` - Full name
- `applicant_email` - Email address
- `applicant_phone` - Phone number
- `applicant_resume_url` - Resume file URL
- `cover_letter` - Cover letter text
- `status` - Application status
- `rating` - Applicant rating (0-5)
- `notes` - Internal notes
- `applied_at` - Application timestamp
- `updated_at` - Last update timestamp

### 2. Job Application Form Block (`Job_Application_Form_Block`)

**Purpose:** Renders a customizable application form in post content.

**Features:**
- Gutenberg block for easy insertion
- Customizable fields (name, email, phone, resume, cover letter)
- Resume file upload with type and size validation
- AJAX submission without page reload
- Success and error message displays
- Accessibility compliant

**Block Attributes:**
- `jobPostId` (number) - Target job post ID
- `allowCoverLetter` (boolean) - Show cover letter field
- `allowResumeUpload` (boolean) - Show resume upload
- `requirePhone` (boolean) - Make phone required
- `buttonText` (string) - Submit button label

**Usage:**
Insert the block in your job post template and set the job ID.

### 3. Job Board Settings (`Job_Board_Settings`)

**Purpose:** Centralized configuration for the job board.

**Settings Categories:**

**General:**
- `job_board_title` - Title displayed on job board
- `job_board_description` - Description text
- `jobs_per_page` - Pagination limit
- `allow_external_applications` - Support external URLs
- `allow_internal_applications` - Support form submissions

**File Upload:**
- `require_resume_upload` - Make resumes mandatory
- `allowed_file_types` - Comma-separated list
- `max_file_size_mb` - Maximum file size

**Email:**
- `application_notification_email` - Where to send notifications
- `send_applicant_confirmation` - Auto-send confirmation
- `send_rejection_emails` - Auto-send rejections

**Job Posting:**
- `default_job_status` - New jobs as draft or published
- `auto_expire_jobs` - Expire past deadline
- `auto_close_after_hire` - Close when hired
- `show_salary_in_listings` - Display salary range
- `featured_jobs_limit` - Max featured at once

**Filters:**
- `enable_location_filter` - Show location filter
- `enable_salary_filter` - Show salary filter
- `enable_experience_filter` - Show experience filter

**Usage:**
```php
use WPShadow\JobPostings\Job_Board_Settings;

// Get a setting
$per_page = Job_Board_Settings::get('jobs_per_page', 12);

// Update a setting
Job_Board_Settings::update('jobs_per_page', 20);

// Get email template
$template = Job_Board_Settings::get_email_template('applicant_confirmation');
```

### 4. Advanced Job Search Block (`Advanced_Job_Search_Block`)

**Purpose:** Provides powerful filtering for job listings.

**Search Filters:**
- Keyword search (job title, skills, description)
- Location-based search
- Job type filter
- Category filter
- Experience level filter (entry, mid, senior, executive)
- Salary range filter (min-max)

**Block Attributes:**
- `enableKeyword` (boolean) - Show keyword search
- `enableLocation` (boolean) - Show location filter
- `enableJobType` (boolean) - Show type filter
- `enableCategory` (boolean) - Show category filter
- `enableExperience` (boolean) - Show experience filter
- `enableSalary` (boolean) - Show salary filter
- `buttonText` (string) - Search button label

**URL Parameters:**
Search results are passed via query parameters:
- `keyword` - Search term
- `location` - Location filter
- `job_type` - Job type term ID
- `category` - Category term ID
- `experience` - Experience level
- `salary_min` - Minimum salary
- `salary_max` - Maximum salary

### 5. Featured Jobs Carousel Block (`Featured_Jobs_Carousel_Block`)

**Purpose:** Displays featured jobs in an attractive carousel.

**Features:**
- Auto-rotating carousel
- Manual navigation buttons
- Indicator dots
- Pause on hover
- Responsive design
- Configurable card fields

**Block Attributes:**
- `jobsToShow` (number) - Number of jobs to display
- `showSalary` (boolean) - Display salary on card
- `showLocation` (boolean) - Display location on card
- `showCategory` (boolean) - Display categories on card
- `autoPlay` (boolean) - Auto-rotate carousel
- `autoPlayInterval` (number) - Milliseconds between slides

**Meta Field Required:**
Jobs need `wps_job_featured` meta set to '1' to appear.

### 6. Job Bulk Operations (`Job_Bulk_Operations_Handler`)

**Purpose:** Perform actions on multiple jobs at once.

**Available Bulk Actions:**
- **Archive Jobs** - Move jobs to draft status
- **Duplicate Jobs** - Clone jobs with all meta and taxonomy
- **Close Jobs** - Mark jobs as closed (sets `wps_job_status` to 'closed')
- **Extend Deadline** - Add 7 days to job deadline
- **Send Notification** - Email job posters about their jobs

**Implementation:**
- Uses WordPress bulk actions API
- Hooks into post type edit screen
- Supports multiple post IDs
- Includes error handling and counting

### 7. Job Alerts System (`Job_Alerts_System`)

**Purpose:** Manages email job alerts for subscribers.

**Features:**
- Subscribe to job alerts by category, type, location
- Confirm subscription via email
- Auto-send alerts when matching jobs posted
- Configurable alert frequency
- Database tracking of subscribers

**Database Table:**
- `id` - Alert ID
- `email` - Subscriber email
- `job_category` - Taxonomy term ID
- `job_type` - Taxonomy term ID
- `location` - Location keyword
- `keywords` - Search keywords
- `status` - active/inactive
- `frequency` - Alert frequency
- `created_at` - Subscription date
- `updated_at` - Last update

**AJAX Endpoints:**
```
POST /wp-admin/admin-ajax.php
action: subscribe_job_alert
Parameters:
- email (required) - Subscriber email
- category (optional) - Job category term ID
- job_type (optional) - Job type term ID
- location (optional) - Location text
- keywords (optional) - Search keywords
- frequency (optional) - weekly/daily/immediately
- nonce - wpshadow_job_alert_nonce
```

**Hooks:**
- `publish_wps_job_posting` - Triggers alert matching and sending

### 8. Job Board Admin Dashboard (`Job_Board_Admin_Dashboard`)

**Purpose:** Admin interface for job board management.

**Admin Pages:**
1. **Dashboard** - Statistics and quick overview
2. **Applications** - Full applications list and management
3. **Settings** - Job board configuration

**Dashboard Stats:**
- Active jobs count
- Draft jobs count
- Total applications
- New applications (highlighted if >0)

**Widgets:**
- Recent applications table
- Active job postings list
- Quick action buttons

**Admin Menu:**
- Added as submenu under Job Postings post type
- Accessible at `admin.php?page=job-board-dashboard`

### 9. Dashboard Widget (`Job_Board_Quick_Stats_Widget`)

**Purpose:** Quick stats widget for WordPress dashboard.

**Displays:**
- Active jobs count
- Draft jobs count
- New applications count (red if >0)
- Total applications

**Quick Actions:**
- Post new job button
- Manage jobs link
- View applications link
- Dashboard link

**Styling:**
- Stats grid layout
- Icon-based design
- Responsive grid (1 column on mobile)
- Integrated action buttons

## Email Templates

The system includes email templates for:
1. **Applicant Confirmation** - Sent when application submitted
2. **Application Notification** - Sent to job poster
3. **Rejection** - Optional rejection email to applicant
4. **Interview Invitation** - Invitation to interview
5. **Job Offer** - Offer email template

Customize in `Job_Board_Settings::get_email_template()`.

## Database Tables

### wpshadow_job_applications
Stores all job applications with applicant info, status, and notes.

### wpshadow_job_alerts
Stores subscriber alert preferences for automated notifications.

## CSS Classes

All components use BEM-style CSS classes for easy customization:
- `.wpshadow-job-application-form`
- `.wpshadow-job-search-form`
- `.wpshadow-featured-jobs-carousel`
- `.wpshadow-stat-card`
- `.wpshadow-dashboard-widget`

## Accessibility Features

✅ **WCAG 2.1 AA Compliant**
- Semantic HTML
- ARIA labels on interactive elements
- Keyboard navigation
- Screen reader support
- Focus indicators
- Color contrast compliance

## Security

✅ **Security Features**
- Nonce verification on forms
- Capability checks on admin pages
- Input sanitization
- Output escaping
- SQL prepared statements
- File upload validation
- Email validation

## Customization

### Adding Custom Application Fields

1. Extend `Job_Application_Tracker` class
2. Modify `create_applications_table()` to add column
3. Update form block with new field
4. Handle in AJAX submission

### Styling

Override CSS in your theme's custom stylesheet:
```css
.wpshadow-btn-primary {
    background: #your-color !important;
}
```

### Email Templates

Modify email templates in settings or override in code:
```php
apply_filters('wpshadow_email_template_applicant_confirmation', $template)
```

## Performance Considerations

- Application table indexed on: job_id, email, status, applied_at
- Alerts table indexed on: email, status, job_category
- AJAX submissions use prepared statements
- Database queries optimized with proper indexes

## Future Enhancements

Potential improvements:
- Email template builder UI
- Application pipeline/kanban view
- Applicant feedback/rating system
- Job posting expiration automation
- Advanced reporting and analytics
- Integration with email marketing services
- Scheduled job alert digests

## Support

For issues or feature requests, please open an issue in the repository with:
- Job board version
- WordPress version
- Detailed error description
- Steps to reproduce
