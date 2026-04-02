# Content Review Wizard System

## Overview

The Content Review Wizard is a comprehensive pre-publish content quality system that helps users review their posts, pages, and custom post types before publishing. It provides a guided wizard experience with AI suggestions, KB articles, training links, and user preference management.

## Features

### 1. **Pre-Publish Review Button**
- Review button added to post metabox on Edit Post screen
- Works with all post types that support the editor
- Non-intrusive - doesn't block publishing workflow

### 2. **Multi-Step Wizard**
The wizard guides users through content review with steps organized by category:
- **Introduction Step**: Overview of what will be checked
- **Content Quality**: Missing elements, length, structure
- **SEO & Search**: Keywords, metadata, internal linking
- **Accessibility**: Alt text, WCAG compliance, semantic HTML
- **Readability**: Sentence length, paragraph structure, vocabulary
- **Code Quality**: Best practices, performance implications
- **Summary**: Total issues found with action options

### 3. **Cloud AI Integration**
When registered with WPShadow Cloud Services:
- "Get AI Suggestion" button on each issue
- AI recommendations for improving content
- Real-time improvement suggestions at each step
- Powered by cloud-based language models

### 4. **Knowledge Base & Training**
- Links to relevant KB articles for each issue
- Training courses for each diagnostic family
- Educational context explaining why each matters
- Helps users learn WordPress best practices

### 5. **User Preferences**
- **Hide This Tip**: Dismiss specific diagnostic messages
- **Skip in Future**: Never show a specific diagnostic in content reviews
- **Show AI Tips**: Toggle cloud AI suggestions on/off
- **Show KB Links**: Toggle educational resources display
- Preferences saved per user per site

### 6. **Formal Reports**
From the Reports menu:
- "Content Quality Report" interface
- Filter posts by type, severity, or search term
- Generate reports for analyzing multiple posts at once
- Summary severity counts and issue breakdowns
- Deep dive into individual post analysis

## Architecture

### Core Classes

#### `Content_Review_Manager`
Main orchestrator for content reviews.

**Methods:**
- `get_instance()` - Get singleton instance
- `init()` - Initialize hooks
- `add_review_metabox()` - Add review button to post edit screen
- `get_content_diagnostics($post_id)` - Get all relevant diagnostics for a post
- `get_user_preferences($user_id)` - Get user's review preferences
- `save_user_preferences($user_id, $preferences)` - Update preferences
- `is_diagnostic_skipped($slug, $user_id)` - Check if diagnostic is skipped
- `skip_diagnostic($slug, $user_id)` - Skip a diagnostic
- `hide_tip($tip_id, $user_id)` - Hide a specific tip
- `get_related_kb_articles($slugs)` - Get KB articles for diagnostics
- `get_related_training($families)` - Get training courses for families

#### `Content_Review_Reports`
Integrates formal reporting into the Reports menu.

**Methods:**
- `init()` - Initialize reporting hooks
- `register_report_menu($items)` - Add report to menu
- `render_content_review_report()` - Render report UI
- `handle_get_review_data()` - AJAX handler for report data

### AJAX Handlers

All handlers in `/includes/admin/ajax/class-content-review-handlers.php`:

#### `Content_Review_Get_Data_Handler`
Fetches wizard data for a post.

**Endpoint:** `wpshadow_content_review_get_data`
**Parameters:**
- `post_id` (int, required) - Post ID to review
- `nonce` (string, required)

**Response:**
```php
{
    "post": {
        "id": 123,
        "title": "My Post",
        "url": "https://example.com/my-post/"
    },
    "diagnostics": {
        "seo": [...],
        "accessibility": [...],
        "content": [...]
    },
    "kb_articles": {...},
    "training": {...},
    "preferences": {...},
    "cloud_status": {
        "is_registered": true|false
    }
}
```

#### `Content_Review_Hide_Tip_Handler`
Marks a tip as hidden.

**Endpoint:** `wpshadow_content_review_hide_tip`

#### `Content_Review_Skip_Diagnostic_Handler`
Marks a diagnostic as skipped in future reviews.

**Endpoint:** `wpshadow_content_review_skip_diagnostic`

#### `Content_Review_AI_Improvement_Handler`
Requests AI improvement suggestions from cloud service.

**Endpoint:** `wpshadow_content_review_ai_improvement`
**Parameters:**
- `post_id` (int)
- `aspect` (string) - Category (seo, accessibility, content, etc.)

#### `Content_Review_Generate_Report_Handler`
Generates comprehensive report for a post.

**Endpoint:** `wpshadow_content_review_generate_report`

### Frontend (JavaScript)

#### `ContentReviewWizard` Class
Main wizard UI handler in `assets/js/content-review-wizard.js`:

**Key Methods:**
- `openWizard()` - Open the review modal
- `closeWizard()` - Close modal
- `nextStep()` / `prevStep()` - Navigate wizard
- `showStep(index)` - Display specific step
- `hideTip(tipId)` - Hide a tip
- `skipDiagnostic(slug)` - Skip a diagnostic
- `requestAIImprovement(aspect)` - Request AI suggestions
- `generateReport()` - Generate formal report

#### `ContentReviewReport` Class
Report page handler in `assets/js/content-review-report.js`:

**Key Methods:**
- `generateReport()` - Load posts for analysis
- `filterPosts()` - Filter by type/severity/search
- `renderPostsList()` - Render posts table
- `showPostDetail(postId)` - Show detailed post analysis

### Styling

- `assets/css/content-review-wizard.css` - Wizard modal styles
- `assets/css/content-review-report.css` - Report page styles

## Integration Points

### Filter Hooks

#### `wpshadow_registered_diagnostics`
Used to get all available diagnostics.

#### `wpshadow_kb_articles_for_diagnostic`
Get KB articles related to a diagnostic.

```php
add_filter( 'wpshadow_kb_articles_for_diagnostic', function( $articles, $slug ) {
    if ( 'content-missing-alt-text' === $slug ) {
        $articles[] = array(
            'title'   => 'How to Add Alt Text',
            'url'     => 'https://wpshadow.com/kb/alt-text/',
            'excerpt' => 'Alt text improves...',
        );
    }
    return $articles;
}, 10, 2 );
```

#### `wpshadow_training_courses_for_family`
Get training courses for a diagnostic family.

```php
add_filter( 'wpshadow_training_courses_for_family', function( $courses, $family ) {
    if ( 'seo' === $family ) {
        $courses[] = array(
            'title'       => 'WordPress SEO Fundamentals',
            'url'         => 'https://wpshadow.com/academy/',
            'duration'    => '45 minutes',
            'description' => 'Learn on-page SEO...',
        );
    }
    return $courses;
}, 10, 2 );
```

### Action Hooks

#### `wpshadow_content_report_generated`
Fires after a content report is generated.

```php
add_action( 'wpshadow_content_report_generated', function( $post_id, $report ) {
    // Log to external service, email user, etc.
}, 10, 2 );
```

## Diagnostic Families

The system checks diagnostics from these families:

- **content** - Content structure, length, completeness
- **seo** - Search engine optimization
- **accessibility** - WCAG compliance, assistive technology
- **readability** - Sentence/paragraph structure, vocabulary level
- **code-quality** - Best practices, performance

## User Preferences Storage

Preferences stored in user meta:

```php
$preferences = get_user_meta( $user_id, 'wpshadow_review_preferences', true );

// Structure:
{
    "hide_tips": ["slug1", "slug2", ...],
    "skip_diagnostics": ["slug1", "slug2", ...],
    "show_ai_tips": true|false,
    "show_kb_links": true|false
}
```

## Cloud Service Integration

### Registration Check
```php
use WPShadow\Integration\Cloud\Cloud_Service_Connector;

if ( Cloud_Service_Connector::is_registered() ) {
    // Show AI suggestion button
}
```

### API Endpoint
Cloud service improvement endpoint:
```
POST https://cloud.wpshadow.com/api/v1/improve-content
```

**Request:**
```json
{
    "aspect": "seo|accessibility|readability|etc",
    "content": {
        "title": "Post Title",
        "excerpt": "Post Excerpt",
        "content": "Post content..."
    },
    "post_id": 123,
    "site": "https://example.com"
}
```

**Response:**
```json
{
    "success": true,
    "improvements": [
        {
            "title": "Suggestion 1",
            "description": "Why this matters...",
            "example": "Here's how to do it..."
        }
    ]
}
```

## Usage Examples

### For Users

1. **Quick Pre-Publish Review:**
   - Edit post in WordPress
   - Click "Review Content" button in metabox
   - Follow wizard through each category
   - Fix issues or skip them
   - Publish

2. **Using AI Suggestions:**
   - During wizard, click "✨ Get AI Suggestion"
   - Review cloud-provided improvements
   - Apply suggestions or dismiss
   - Continue to next issue

3. **Formal Report Generation:**
   - Go to Reports → Content Quality Report
   - Filter by post type or search
   - Click "Generate Report"
   - View severity breakdown
   - Click "View Details" for individual posts

### For Developers

#### Extend with Custom KB Articles
```php
// Add custom KB article mapping
add_filter( 'wpshadow_kb_articles_for_diagnostic', function( $articles, $slug ) {
    if ( 'my-custom-diagnostic' === $slug ) {
        $articles[] = array(
            'title'   => 'My Custom Guide',
            'url'     => 'https://example.com/guide',
            'excerpt' => 'Information about this issue...',
        );
    }
    return $articles;
}, 10, 2 );
```

#### Log Content Report Generation
```php
// Log when reports are generated
add_action( 'wpshadow_content_report_generated', function( $post_id, $report ) {
    error_log( sprintf(
        'Content report generated for post %d: %d issues found',
        $post_id,
        $report['total_issues']
    ) );
}, 10, 2 );
```

## Performance Considerations

- Diagnostics are only run when user clicks "Review" button (on-demand)
- Results are not cached - fresh analysis on each review
- Cloud API calls are asynchronous via AJAX
- User preferences loaded once per session
- Report generation batches posts (50 per page)

## Accessibility

- Keyboard navigation fully supported
- Modal manages focus properly
- Screen reader friendly structure
- ARIA labels on all interactive elements
- Color not the only indicator of severity
- Respects `prefers-reduced-motion`

## Security

- All AJAX endpoints protected by nonces
- User capability checks (edit_posts minimum)
- Post edit permission verified
- Input sanitization on all parameters
- API key stored securely via Options API
- Cloud API communication via HTTPS only

## Future Enhancements

Potential additions:
- Bulk content review for multiple posts
- Scheduled content audits
- Content performance tracking over time
- A/B testing suggestions
- Content gap analysis
- Competitor content analysis (via cloud service)
- Social media preview optimization
- Email newsletter preview
- PDF report export
- Integration with third-party content tools

## Troubleshooting

### AI Suggestions Not Showing
- Verify cloud service registration
- Check API key in database: `get_option('wpshadow_cloud_api_key')`
- Verify network connectivity to cloud.wpshadow.com
- Check browser console for AJAX errors

### Missing Diagnostics in Wizard
- Verify diagnostic family is in `$content_families` array
- Check diagnostic is registered in Diagnostic_Registry
- Verify diagnostic `check()` method doesn't error
- Check user preferences aren't hiding all diagnostics

### User Preferences Not Saving
- Verify user has WordPress edit capability
- Check user meta in database
- Check for conflicting plugins modifying user meta
- Review error logs for save failures
