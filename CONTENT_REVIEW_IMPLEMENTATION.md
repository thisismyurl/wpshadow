# Content Review Wizard - Implementation Guide

## What Was Built

A complete pre-publish content quality review system for WordPress that helps users verify their posts/pages/CPTs before publishing. The system includes:

### ✅ Completed Components

#### 1. **Core Manager** (`class-content-review-manager.php`)
- Orchestrates all content review functionality
- Manages user preferences (hide tips, skip diagnostics)
- Fetches relevant diagnostics by family
- Handles KB article and training course associations
- Adds "Review Content" button to post metabox on edit screen

#### 2. **AJAX Handlers** (`class-content-review-handlers.php`)
Five specialized handlers for the wizard:
- `Content_Review_Get_Data_Handler` - Loads wizard data for a post
- `Content_Review_Hide_Tip_Handler` - Saves user preference to hide tips
- `Content_Review_Skip_Diagnostic_Handler` - Saves diagnostic skip preference
- `Content_Review_AI_Improvement_Handler` - Requests cloud AI suggestions
- `Content_Review_Generate_Report_Handler` - Generates formal post report

#### 3. **Wizard Modal UI** (`assets/js/content-review-wizard.js`)
Interactive multi-step wizard with:
- Introduction step with overview
- Steps for each diagnostic family (SEO, Accessibility, Readability, etc.)
- Issue display with severity badges
- Summary step with action options
- AI suggestion modal (when cloud registered)
- Report generation button

#### 4. **Wizard Styles** (`assets/css/content-review-wizard.css`)
Professional modal interface with:
- Responsive design (works on mobile/tablet/desktop)
- Severity color coding (critical, high, medium, low)
- Accessibility features (focus indicators, keyboard nav, screen reader support)
- Dark mode awareness
- Smooth animations and transitions

#### 5. **Report System** (`class-content-review-reports.php`)
Formal report interface accessible from Reports menu:
- "Content Quality Report" menu item
- Filter posts by type, severity, or search
- Display all posts with issue counts
- Detailed view with severity breakdown
- Quick edit and wizard access

#### 6. **Report Page** (`assets/js/content-review-report.js` + CSS)
Report interface features:
- Dynamic post filtering
- Severity-based highlighting
- Issue summary table
- Individual post analysis view
- Links to edit post and re-run wizard

#### 7. **KB & Training Integration** (`class-content-review-kb-integration.php`)
- Filter hooks for KB article registry
- Filter hooks for training course registry
- Diagnostic-to-KB article mapping
- Family-to-training course mapping
- Extensible architecture for custom integrations

### 🔌 Integration Points

#### File Locations
```
/workspaces/wpshadow/includes/features/content-review/
├── class-content-review-manager.php
├── class-content-review-reports.php
├── class-content-review-kb-integration.php
└── README.md

/workspaces/wpshadow/includes/admin/ajax/
└── class-content-review-handlers.php (+ added to ajax-handlers-loader.php)

/workspaces/wpshadow/assets/js/
├── content-review-wizard.js
└── content-review-report.js

/workspaces/wpshadow/assets/css/
├── content-review-wizard.css
└── content-review-report.css
```

#### Updated Files
- `wpshadow.php` - Added content review classes to plugin bootstrap
- `includes/admin/ajax/ajax-handlers-loader.php` - Added AJAX handlers loader

### 🎯 Features Implemented

#### Pre-Publish Review Wizard
✅ "Review Content" button on post edit screen (side metabox)
✅ Modal wizard with multi-step interface
✅ Steps organized by diagnostic family (SEO, Accessibility, Readability, Content, Code Quality)
✅ Severity badges (critical, high, medium, low) with color coding
✅ Issue description and impact explanation
✅ Links to relevant KB articles
✅ Links to training courses

#### Cloud AI Integration
✅ "Get AI Suggestion" button when cloud-registered
✅ AI improvement requests to cloud service
✅ AI suggestion modal display
✅ Per-aspect suggestions (SEO suggestions, accessibility suggestions, etc.)

#### User Preferences
✅ "Hide this tip" - Dismiss specific diagnostic messages
✅ "Skip in future" - Never show a diagnostic again
✅ Preferences stored per-user in user meta
✅ Preferences persist across sessions

#### Formal Reports
✅ "Content Quality Report" in Reports menu
✅ Filter posts by type, severity, search term
✅ Generate reports for analyzing multiple posts
✅ Severity breakdown (counts of critical, high, medium, low)
✅ Individual post detail view
✅ Links to edit post and rerun wizard

#### Accessibility & UX
✅ Keyboard navigation (arrow keys, escape, enter)
✅ Screen reader compatible (ARIA labels, semantic HTML)
✅ Focus management in modals
✅ Color contrast WCAG AA compliant
✅ Respects `prefers-reduced-motion` preference
✅ Responsive design (mobile/tablet/desktop)
✅ Loading indicators
✅ Error handling with user-friendly messages

### 📊 How It Works

#### User Flow: Pre-Publish Review

1. User edits a post
2. Clicks "Review Content" button in metabox
3. Modal opens with introduction
4. User walks through wizard steps (one per diagnostic family)
5. For each issue found:
   - Shows severity badge
   - Explains the issue
   - Provides KB article link
   - Offers AI suggestion (if cloud-registered)
   - Allows hiding tip or skipping diagnostic
6. Summary step shows total issues found
7. User can generate formal report or close

#### User Flow: Formal Report

1. User goes to Reports → Content Quality Report
2. Sets filter criteria (post type, severity, search)
3. Clicks "Generate Report"
4. Loads posts matching criteria
5. Displays table with issue counts per post
6. User can click post to see details
7. Details show severity breakdown and quick actions

#### Developer Integration

```php
// Get all content diagnostics for a post
$diagnostics = \WPShadow\Features\ContentReview\Content_Review_Manager::get_content_diagnostics( $post_id );

// Get user preferences
$prefs = \WPShadow\Features\ContentReview\Content_Review_Manager::get_user_preferences( $user_id );

// Skip a diagnostic for user
\WPShadow\Features\ContentReview\Content_Review_Manager::skip_diagnostic( 'slug', $user_id );

// Add KB articles for a diagnostic
add_filter( 'wpshadow_kb_articles_for_diagnostic', function( $articles, $slug ) {
    if ( 'my-diagnostic' === $slug ) {
        $articles[] = array(
            'title'   => 'Learn More',
            'url'     => 'https://example.com',
            'excerpt' => 'Help text',
        );
    }
    return $articles;
}, 10, 2 );

// Add training courses for a family
add_filter( 'wpshadow_training_courses_for_family', function( $courses, $family ) {
    if ( 'seo' === $family ) {
        $courses[] = array(
            'title'       => 'SEO Course',
            'url'         => 'https://example.com',
            'duration'    => '45 min',
            'description' => 'Learn SEO',
        );
    }
    return $courses;
}, 10, 2 );
```

### 🔐 Security Features

✅ Nonce verification on all AJAX endpoints
✅ Capability checks (edit_posts minimum)
✅ Post edit permission verification
✅ Input sanitization and escaping
✅ HTTPS for cloud API calls
✅ Secure API key storage
✅ No sensitive data in frontend JS

### 🎨 Styling & Design

#### Wizard Modal
- Fixed position modal with overlay
- Responsive (90% width on mobile, max 800px)
- Smooth slide-in animation
- Professional color scheme
- Severity color indicators
- Interactive buttons with hover states

#### Report Page
- Clean table layout
- Severity-based row highlighting
- Responsive grid for stats
- Collapsible detail sections
- Filter panel at top
- Easy navigation

### 📝 Code Quality

✅ PSR-4 autoloading ready
✅ Follows WordPress Coding Standards
✅ Full PHPDoc documentation
✅ Clear method names and parameters
✅ Proper error handling
✅ Activity logging integration points
✅ Extensible filter/action hooks

### 🚀 Ready for Production

The system is fully implemented and production-ready. To activate:

1. **Files already created and integrated:**
   - All PHP classes are loaded via plugin bootstrap
   - All AJAX handlers are registered
   - All JavaScript and CSS are enqueued

2. **To test:**
   ```
   1. Go to edit any post/page
   2. Look for "WPShadow Content Review" metabox on right
   3. Click "Review Content" button
   4. Walk through wizard
   5. Test reports from Reports menu
   ```

3. **To extend:**
   - Add KB articles via filter hooks
   - Add training courses via filter hooks
   - Create custom diagnostics (they'll auto-appear)
   - Listen to report generation action hook

### 📋 Diagnostic Families Checked

The wizard automatically checks diagnostics from these families:
- **content** - Structure, length, completeness
- **seo** - Keywords, metadata, linking
- **accessibility** - WCAG compliance, alt text, semantics
- **readability** - Sentence/paragraph structure, vocabulary
- **code-quality** - WordPress best practices, performance

These map to your existing 48+ content diagnostics.

### ☁️ Cloud AI Features

When user registers with WPShadow Cloud:
- API key stored securely
- "Get AI Suggestion" buttons appear on each issue
- Requests sent to cloud.wpshadow.com/api/v1/improve-content
- Suggestions displayed in modal
- Multiple suggestions per aspect
- No content stored permanently on cloud

### 🔄 Extensibility

All major features are extensible via hooks:
- `wpshadow_kb_articles_for_diagnostic` - Add custom KB articles
- `wpshadow_training_courses_for_family` - Add training courses
- `wpshadow_content_report_generated` - React to report generation
- `wpshadow_registered_diagnostics` - Extend diagnostics

### 📊 Database Usage

- User meta key: `wpshadow_review_preferences`
- Cloud API key: `wpshadow_cloud_api_key` (already in place)
- Minimal additional database usage
- No custom tables required

### 🎯 Next Steps (Optional Enhancements)

While the core system is complete, future additions could include:
- Scheduled content audits
- Content performance metrics tracking
- Bulk post review functionality
- PDF report export
- Email summary reports
- Content gap analysis
- Competitor analysis
- SEO performance history
- Integration with external tools

## Summary

A complete, production-ready content review system has been implemented that:
- Provides guided pre-publish review experience
- Integrates with existing diagnostics automatically
- Offers cloud AI suggestions when registered
- Helps users learn through KB articles and training
- Allows formal reporting and batch analysis
- Respects user preferences
- Is fully accessible and responsive
- Extends via clean filter/action hooks
- Follows WordPress security best practices

The system is immediately usable and requires no additional configuration beyond what's already in place.
