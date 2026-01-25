# WPShadow Onboarding Agent

**Version:** 1.2601.2148  
**Status:** ✅ Production Ready  
**Last Updated:** January 25, 2026

---

## Overview

The WPShadow Onboarding Agent helps users transition from other platforms (Word, Google Docs, Wix, Squarespace, Moodle, Notion) to WordPress through a friendly, progressive learning experience.

**Philosophy Alignment:**
- **Commandment #1**: Helpful Neighbor - Guide without judgment
- **Commandment #8**: Inspire Confidence - Make WordPress approachable
- **Commandment #5**: Drive to KB - Educational journey
- **Commandment #6**: Drive to Training - Progressive learning
- **Pillar #2**: Learning Inclusive - Accessible to learners at all levels

---

## Features

### 1. **5-Step Onboarding Wizard**

The wizard guides new users through a personalized setup:

1. **Platform Selection** - Where are you coming from?
   - WordPress (experienced)
   - Microsoft Word
   - Google Docs
   - Wix
   - Squarespace
   - Moodle
   - Notion
   - New to all of this

2. **Comfort Level** - How do you learn best?
   - I like to take my time (detailed explanations)
   - I can figure things out (balanced guidance)
   - I dive right in (minimal hand-holding)

3. **Configuration** - Setup preferences
   - Automatic health checks
   - Helpful tips & guidance
   - Track progress

4. **Privacy** - Communication preferences
   - Email notifications (critical/weekly)
   - Anonymous usage data sharing
   - Newsletter subscription (optional)

5. **Confirmation** - Review and finalize

### 2. **Platform Terminology Translation**

The system translates WordPress terms into familiar language from the user's previous platform:

| WordPress Term | Word | Wix | Moodle |
|---------------|------|-----|--------|
| Post | Document | Blog Post | Lesson |
| Plugin | Add-in | App | Plugin |
| Theme | Template | Template | Theme |
| Category | Folder | Category | Category |
| Media Library | Pictures | Media Manager | Files |

**Files:** `includes/onboarding/data/terminology-*.php`

### 3. **Simplified UI Mode**

Users from non-WordPress platforms automatically start with a simplified interface that:
- Hides advanced features initially
- Shows only essential WordPress functionality
- Uses platform-specific terminology
- Gradually introduces WordPress concepts

### 4. **Graduation System**

After 20+ meaningful actions (posts created, settings changed, comments made), users are offered graduation:
- Prompt to "Show All Features"
- Can postpone or accept
- Tracks learning progress
- Celebrates accomplishment

---

## Architecture

### Files Structure

```
includes/onboarding/
├── class-onboarding-wizard.php      # Main wizard controller
├── class-onboarding-manager.php     # User state & preferences
├── class-platform-translator.php    # Terminology translation
└── data/
    ├── terminology-word.php         # Microsoft Word terms
    ├── terminology-google-docs.php  # Google Docs terms
    ├── terminology-wix.php          # Wix terms
    ├── terminology-squarespace.php  # Squarespace terms
    ├── terminology-moodle.php       # Moodle terms
    └── terminology-notion.php       # Notion terms

includes/views/onboarding/
└── wizard.php                       # Wizard UI template

includes/admin/ajax/
├── class-save-onboarding-handler.php      # Save preferences
├── class-skip-onboarding-handler.php      # Skip wizard
├── class-show-all-features-handler.php    # Graduate user
├── class-dismiss-graduation-handler.php   # Postpone graduation
└── class-dismiss-term-handler.php         # Hide term tooltip
```

### User Meta Keys

| Meta Key | Purpose | Type |
|----------|---------|------|
| `wpshadow_onboarding_complete` | Timestamp of completion | int |
| `wpshadow_onboarding_platform` | Selected platform | string |
| `wpshadow_onboarding_comfort_level` | Learning style | string |
| `wpshadow_onboarding_ui_simplified` | UI mode | bool |
| `wpshadow_onboarding_action_count` | Actions taken | int |
| `wpshadow_onboarding_dismissed_terms` | Hidden tooltips | array |
| `wpshadow_config_preferences` | Config choices | array |
| `wpshadow_privacy_preferences` | Privacy choices | array |

---

## Usage

### For Users

**First-Time Experience:**
1. Install and activate WPShadow plugin
2. Visit any WordPress admin page
3. Automatically redirected to onboarding wizard
4. Complete 5-step wizard (or skip)
5. Start using WordPress with simplified interface

**Graduation:**
- After 20+ actions, see graduation notice
- Click "Show Me Everything" to see full WordPress
- Or "Maybe Later" to postpone

**Restart Onboarding:**
- Go to WPShadow → Settings → Onboarding
- Click "Change" next to "Your Background"
- Or visit: `wp-admin/admin.php?page=wpshadow&onboarding=restart`

### For Developers

**Initialize Wizard:**
```php
// Automatically loaded by Plugin_Bootstrap
\WPShadow\Onboarding\Onboarding_Wizard::init();
```

**Check User Status:**
```php
// Check if needs onboarding
$needs_onboarding = \WPShadow\Onboarding\Onboarding_Manager::needs_onboarding();

// Get user's platform
$platform = \WPShadow\Onboarding\Onboarding_Manager::get_user_platform();

// Check if UI simplified
$simplified = \WPShadow\Onboarding\Onboarding_Manager::is_ui_simplified();
```

**Translate Terms:**
```php
// Get translated term for user
$translated = \WPShadow\Onboarding\Platform_Translator::get_term('post');
// Returns: "Document" for Word users, "Blog Post" for Wix users

// Get tooltip HTML
$tooltip = \WPShadow\Onboarding\Platform_Translator::get_term_tooltip('plugin');
// Returns: HTML with platform-specific explanation
```

**Add New Platform:**
1. Create file: `includes/onboarding/data/terminology-PLATFORM.php`
2. Return array with platform info and term mappings
3. Add platform to `Platform_Translator::get_platforms()`
4. Add platform card to wizard view

---

## Hooks & Filters

### Actions

```php
// Fired after onboarding completed
do_action( 'wpshadow_onboarding_completed', $user_id, $platform, $comfort_level, $config, $privacy );

// Fired after wizard assets enqueued
do_action( 'wpshadow_onboarding_wizard_assets' );

// Fired when newsletter subscription requested
do_action( 'wpshadow_newsletter_subscribe', $email, $context );
```

### Filters

No filters currently implemented. Future enhancements may add:
- Filter available platforms
- Filter terminology mappings
- Customize graduation threshold

---

## KPI Tracking

The onboarding system tracks:
- **onboarding_completed** - User finished wizard
  - `platform`: Selected platform
  - `comfort_level`: Learning style
  - `config`: Configuration choices
- **onboarding_graduated** - User graduated to full UI
  - `action_count`: Number of actions before graduation

---

## Testing

### Manual Test Flow

1. **Fresh Install:**
   ```bash
   # Reset onboarding for current user
   wp user meta delete $(wp user get admin --field=ID) wpshadow_onboarding_complete
   ```

2. **Visit Admin:**
   - Should auto-redirect to wizard
   - Should show 5-step process

3. **Complete Wizard:**
   - Select platform (e.g., "Word")
   - Select comfort level
   - Choose preferences
   - Review confirmation
   - Click "Let's Go!"

4. **Verify State:**
   - Should see simplified UI
   - Should see platform-specific terms
   - Should not see wizard again

5. **Test Graduation:**
   - Perform 20+ actions (create posts, change settings)
   - Should see graduation notice
   - Click "Show Me Everything"
   - Should see full WordPress interface

### Unit Tests

No unit tests currently implemented. Future enhancement opportunity.

---

## Accessibility

The onboarding wizard is designed with accessibility in mind:

- ✅ Keyboard navigation supported
- ✅ Screen reader friendly labels
- ✅ High contrast colors (WCAG AA compliant)
- ✅ Clear, jargon-free language
- ✅ Non-judgmental tone throughout
- ✅ Progressive disclosure of complexity

---

## Roadmap

### Future Enhancements

1. **More Platforms**
   - Drupal
   - Joomla
   - Shopify
   - Teams/SharePoint

2. **Interactive Tutorials**
   - First post creation walkthrough
   - Theme customization guide
   - Plugin installation tutorial

3. **Progress Dashboard**
   - Visual learning progress
   - Achievements/badges
   - Next recommended actions

4. **Smart Recommendations**
   - Suggest plugins based on platform
   - Theme recommendations
   - Content structure guidance

5. **Video Integration**
   - Platform-specific video tutorials
   - In-wizard video explanations
   - Link to WPShadow Academy

---

## Troubleshooting

### Wizard Not Showing

**Symptom:** Wizard doesn't appear on first admin visit

**Solutions:**
1. Check user capability: `current_user_can('edit_posts')`
2. Verify meta not set: `get_user_meta($user_id, 'wpshadow_onboarding_complete')`
3. Check for redirect loops: Clear transient `wpshadow_onboarding_shown_*`
4. Verify file exists: `includes/onboarding/class-onboarding-wizard.php`

### AJAX Errors

**Symptom:** "Connection error" when completing wizard

**Solutions:**
1. Check nonce: `wp_create_nonce('wpshadow_onboarding')`
2. Verify handlers registered: Check `class-ajax-router.php`
3. Check browser console for JavaScript errors
4. Verify AJAX URL: Should be `admin-ajax.php`

### Graduation Not Triggering

**Symptom:** User performed 20+ actions but no notice

**Solutions:**
1. Check action count: `get_user_meta($user_id, 'wpshadow_onboarding_action_count')`
2. Verify UI simplified: `is_ui_simplified()` must be true
3. Check dismissed flag: `get_user_meta($user_id, 'wpshadow_graduation_dismissed')`
4. Verify hooks: `save_post`, `updated_option`, `wp_insert_comment`

---

## Credits

**Philosophy:** Built on WPShadow's 11 Commandments and 3 Foundational Pillars  
**Inspiration:** iPhone→Android, Windows→Mac transition experiences  
**Approach:** Respect user's existing knowledge while teaching new concepts

---

## See Also

- [PRODUCT_PHILOSOPHY.md](PRODUCT_PHILOSOPHY.md) - Core principles
- [ACCESSIBILITY_AND_INCLUSIVITY_CANON.md](ACCESSIBILITY_AND_INCLUSIVITY_CANON.md) - Design guidelines
- [FEATURE_MATRIX_DIAGNOSTICS.md](FEATURE_MATRIX_DIAGNOSTICS.md) - All diagnostics
- [ARCHITECTURE.md](ARCHITECTURE.md) - System design
