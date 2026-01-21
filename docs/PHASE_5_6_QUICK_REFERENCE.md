# Phase 5 & 6 Quick Reference Guide

## For Developers

### Phase 5: Knowledge Base & Training

#### Getting KB Articles
```php
use WPShadow\KnowledgeBase\KB_Library;
use WPShadow\KnowledgeBase\KB_Search;

// Get all articles (auto-generated + cached)
$articles = KB_Library::get_all_articles();

// Get one article
$article = KB_Library::get_article( 'diagnostic-ssl' );

// Search
$results = KB_Search::search( 'security headers' );

// Filter
$security_articles = KB_Library::get_by_category( 'security' );
$diagnostic_articles = KB_Library::get_by_type( 'diagnostic' );

// Clear cache (after adding new diagnostics)
KB_Library::clear_cache();
```

#### Training & Progress
```php
use WPShadow\KnowledgeBase\Training_Provider;
use WPShadow\KnowledgeBase\Training_Progress;

// Get courses
$courses = Training_Provider::get_courses();

// Get training for diagnostic
$training = Training_Provider::get_training_for_item( 'ssl', 'diagnostic' );

// Mark topic complete
Training_Progress::mark_topic_complete( $user_id, 'ssl' );

// Mark course complete
Training_Progress::mark_course_complete( $user_id, 'security-101' );

// Check progress
$progress = Training_Progress::get_total_progress( $user_id );
// Returns: ['total_courses' => 5, 'completed_courses' => 2, 'percent_complete' => 40, ...]

// Award badge
Training_Progress::award_badge( $user_id, 'security-expert' );
```

#### Formatting Content
```php
use WPShadow\KnowledgeBase\KB_Formatter;

// Markdown to HTML
$html = KB_Formatter::markdown_to_html( '# Hello\n\nThis is **bold**' );

// Generate table of contents
$toc = KB_Formatter::generate_toc( $html );

// Add anchors to headings
$html_with_anchors = KB_Formatter::add_heading_anchors( $html );
```

### Phase 6: Privacy & Consent

#### Privacy Policy
```php
use WPShadow\Privacy\Privacy_Policy_Manager;

// Get full policy
$policy = Privacy_Policy_Manager::get_policy();

// Display as HTML
$html = Privacy_Policy_Manager::get_policy_html();

// Store version (for auditing)
Privacy_Policy_Manager::store_version( '1.1', $html );

// Get version history
$history = Privacy_Policy_Manager::get_version_history();
```

#### Consent Management
```php
use WPShadow\Privacy\Consent_Preferences;

// Get user's current preferences
$prefs = Consent_Preferences::get_preferences( $user_id );

// Check if user consented to something
if ( Consent_Preferences::has_consented( $user_id, 'telemetry' ) ) {
    // User opted in to analytics
}

// Update preferences
Consent_Preferences::set_preferences( $user_id, [
    'anonymized_telemetry' => true
]);

// Get stats for dashboard
$stats = Consent_Preferences::get_consent_stats();
// Returns: ['total_users' => 10, 'users_consented' => 8, 'consent_rate' => 80]

// Export user data (GDPR)
$data = Consent_Preferences::export_consent_data( $user_id );
```

#### First-Run Consent Flow
```php
use WPShadow\Privacy\First_Run_Consent;

// Check if should show consent
if ( First_Run_Consent::should_show_consent( $user_id ) ) {
    echo First_Run_Consent::get_consent_html();
}

// User clicks "Save Preferences"
First_Run_Consent::save_consent( $user_id, [
    'anonymized_telemetry' => true
]);

// User clicks "Learn More" → dismisses for 30 days
First_Run_Consent::dismiss_consent( $user_id );
```

---

## For Site Admins

### First Admin Visit
1. Consent flow appears (bottom-right modal)
2. Read options: Essential, Error Reports, Analytics
3. Click "Save Preferences" or "Learn More"
4. Won't see again for 30 days

### Changing Preferences Later
1. Go to Settings → Privacy & Consent
2. Update checkboxes
3. Click Save
4. Changes take effect immediately

### Viewing Privacy Policy
1. Go to Settings → Privacy & Consent
2. Scroll to bottom for full policy
3. See version history if available

---

## AJAX Endpoints

### KB Search
```javascript
jQuery.post( wpshadow.ajaxurl, {
    action: 'wpshadow_kb_search',
    nonce: wpshadow.nonce,
    query: 'security',
    category: 'security',     // optional
    type: 'diagnostic'        // optional
}, function( response ) {
    console.log( response.data.results );
});
```

---

## Database Storage

### Phase 5 Storage
- `wpshadow_kb_articles_v1` - All articles (24h cache)
- `wpshadow_kb_search_index_v1` - Search index
- `wpshadow_kb_search_stats` - Popular searches
- `user_meta: wpshadow_training_progress` - User progress

### Phase 6 Storage
- `wpshadow_privacy_policy_versions` - Policy history
- `user_meta: wpshadow_consent_preferences` - Current prefs
- `user_meta: wpshadow_consent_history` - Audit trail
- `user_meta: wpshadow_consent_dismissed_until` - Dismiss timer

---

## Integration Checklist

### Add KB Links to Diagnostics
```php
// In diagnostic class
$this->kb_article_id = 'diagnostic-ssl';

// Then in UI
$article = KB_Library::get_article( $this->kb_article_id );
echo '<a href="#">Learn more: ' . $article['title'] . '</a>';
```

### Add Training Links to UI
```php
// In dashboard or finding view
$training = Training_Provider::get_training_for_item( $diagnostic_id );

if ( ! empty( $training ) ) {
    echo '<p>Want to learn more?</p>';
    foreach ( $training as $topic ) {
        echo '<a href="#">' . $topic['title'] . ' (' . $topic['duration'] . ')</a>';
    }
}
```

### Check Consent Before Collecting Data
```php
use WPShadow\Privacy\Consent_Preferences;

$user_id = get_current_user_id();

if ( Consent_Preferences::has_consented( $user_id, 'telemetry' ) ) {
    // Safe to collect telemetry
    send_usage_data();
}
```

---

## Common Patterns

### Clear KB Cache After Adding Diagnostic
```php
// After registering new diagnostic in registry
add_filter( 'wpshadow_diagnostic_registered', function() {
    \WPShadow\KnowledgeBase\KB_Library::clear_cache();
});
```

### Show Consent on Admin Pages
```php
add_action( 'admin_footer', function() {
    if ( current_user_can( 'manage_options' ) ) {
        if ( \WPShadow\Privacy\First_Run_Consent::should_show_consent( get_current_user_id() ) ) {
            echo \WPShadow\Privacy\First_Run_Consent::get_consent_html();
        }
    }
});
```

### Track Training for Engagement
```php
// When user completes diagnostic reading
Training_Progress::mark_topic_complete( $user_id, 'ssl' );

// Check if they're a "champion"
if ( Training_Progress::is_training_champion( $user_id ) ) {
    // Show badge/recognition
    echo '🏆 Training Champion!';
}
```

---

## Debugging

### Check Search Index
```php
$index = get_option( 'wpshadow_kb_search_index_v1' );
echo 'Indexed articles: ' . count( $index );
```

### View User Progress
```php
$progress = \WPShadow\KnowledgeBase\Training_Progress::get_total_progress( $user_id );
echo 'Complete: ' . $progress['percent_complete'] . '%';
```

### Check Consent Status
```php
$prefs = \WPShadow\Privacy\Consent_Preferences::get_preferences( $user_id );
echo wp_json_encode( $prefs, JSON_PRETTY_PRINT );
```

### Rebuild Search Index
```php
// Manual rebuild
\WPShadow\KnowledgeBase\KB_Search::build_index();
```

---

## Performance Tips

1. **Cache Articles:** Done automatically (24h)
2. **Cache Search Index:** Updated on article cache clear
3. **Lazy Load Training:** Only load when needed
4. **Use Hooks:** Filter results instead of loops

---

## Security Tips

1. **Always Verify Nonce** on AJAX calls
2. **Check Capabilities** before showing content
3. **Sanitize User Input** (KB search does this)
4. **Escape Output** when displaying articles
5. **Respect Consent** before collecting data

---

## What's Coming (Phase 7+)

- ✅ Video embedding in training
- ✅ Community Q&A section
- ✅ Article ratings
- ✅ Translated content
- ✅ Advanced telemetry dashboard
- ✅ GDPR automated requests
- ✅ Third-party service listing
