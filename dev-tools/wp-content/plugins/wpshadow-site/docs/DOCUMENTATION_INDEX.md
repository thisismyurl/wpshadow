# 🎙️ ElevenLabs Podcast Integration - Complete Documentation Index

## Quick Navigation

| Document | Purpose | Best For |
|----------|---------|----------|
| [QUICKSTART.md](#quickstartmd) | 30-min setup guide | New users |
| [README.md](#readmemd) | Feature overview | All users |
| [PODCAST_INTEGRATION_GUIDE.md](#podcast_integration_guidemd) | Complete reference | Developers |
| [PODCAST_EXAMPLES.php](#podcast_examplesphp) | Code examples | Implementation |
| [podcast-test-suite.php](#podcast-test-suitephp) | Diagnostics | Troubleshooting |

---

## QUICKSTART.md
**Path:** `wp-content/plugins/wpshadow-site/QUICKSTART.md`

### What's Inside
- ✅ Pre-setup requirements
- ✅ Step-by-step configuration (6 sections)
- ✅ ElevenLabs credential setup
- ✅ WordPress configuration walkthrough
- ✅ FFmpeg installation (optional)
- ✅ System testing
- ✅ Display setup
- ✅ Troubleshooting checklist
- ✅ Performance notes
- ✅ Security reminders

### Best For
- First-time setup
- Quick reference during installation
- Troubleshooting basic issues

### Read Time: 10-15 minutes

**Start here if:** You're setting up the plugin for the first time

---

## README.md
**Path:** `wp-content/plugins/wpshadow-site/README.md`

### What's Inside
- 📋 Overview of included files
- 🚀 Quick start (3-step version)
- 🎯 Key features breakdown
- 📋 Configuration options table
- 🔧 System requirements
- 📖 Documentation links
- 🔍 Troubleshooting table
- 🗄️ Database schema
- 📊 Performance metrics
- 🔌 Integration points
- 🛠️ Developer tips
- 🤝 Support resources
- 📝 Changelog

### Best For
- Feature overview
- Quick reference guide
- System requirements check
- Troubleshooting table

### Read Time: 5-10 minutes

**Start here if:** You want a high-level overview

---

## PODCAST_INTEGRATION_GUIDE.md
**Path:** `wp-content/plugins/wpshadow-site/PODCAST_INTEGRATION_GUIDE.md`

### What's Inside
- 📖 Complete feature documentation
- 🔍 How it works (workflow diagrams)
- 🛠️ Setup instructions (3 steps)
- 🔌 PHP API reference
- 🎯 Filters & hooks (3 custom hooks)
- 🐛 Troubleshooting guide
- 💾 Database schema details
- 📁 Storage locations
- ⚡ Performance notes
- 📊 API rate limit information
- 🚀 Future enhancements roadmap

### Best For
- Complete feature reference
- API documentation
- Filter/hook usage
- Advanced troubleshooting
- Performance optimization

### Read Time: 20-30 minutes

**Start here if:** You need complete API documentation

---

## PODCAST_EXAMPLES.php
**Path:** `wp-content/plugins/wpshadow-site/PODCAST_EXAMPLES.php`

### What's Inside
10 practical code examples:

1. **Display Podcast in Template** - Show audio player
2. **Create Shortcode** - User-friendly shortcode
3. **Gutenberg Block** - Block editor support
4. **Custom Processing** - Manual generation trigger
5. **Status Checking** - Query queue status
6. **Per-Article Voices** - Custom voice per article
7. **WP-CLI Commands** - Command-line tools
8. **REST API Integration** - API endpoint support
9. **Error Handling** - Try/catch patterns
10. **Email Notifications** - Completion alerts

### Best For
- Implementation patterns
- Copy-paste code
- Integration examples
- API usage reference

### Read Time: 15 minutes (to understand); varies for implementation

**Start here if:** You're integrating the system into your code

---

## podcast-test-suite.php
**Path:** `wp-content/plugins/wpshadow-site/podcast-test-suite.php`

### What's Inside
Diagnostic tools with 10 test categories:

1. **Plugin Status** - Class loading verification
2. **Database Setup** - Queue table check
3. **ElevenLabs Config** - API key & voice validation
4. **Podcast Settings** - Generator configuration check
5. **File Permissions** - Directory writability
6. **FFmpeg Availability** - Audio processor check
7. **WordPress Cron** - Scheduled events
8. **API Test** - Live ElevenLabs call
9. **KB Detection** - Article discovery
10. **Queue Status** - Generation state overview

### Best For
- System validation
- Troubleshooting issues
- Pre-launch checks
- Performance verification

### How to Use
```php
// Add to functions.php temporarily:
require_once '/path/to/podcast-test-suite.php';
wpshadow_podcast_test_suite();
```

**Start here if:** Something isn't working

---

## Core Plugin Files

### wpshadow-site.php
**Main plugin file** - Version 0.3.0

- Registers activation/deactivation hooks
- Loads podcast generator and settings modules
- Initializes WordPress admin integration
- Database table creation
- Cron management

**Key Functions:**
- `wpshadow_site_init_podcast_generator()` - Initialize system
- `wpshadow_site_activate()` - Create database table
- `wpshadow_site_deactivate()` - Cleanup cron

### includes/class-podcast-generator.php
**Core generator class** - ~500 lines

Main functionality:
- Hook into KB article publication
- Queue management
- Text-to-speech synthesis
- Audio stitching (FFmpeg + fallback)
- Media library uploads
- Error tracking

**Main Methods:**
- `queue_podcast_generation()` - Detect & queue articles
- `process_queue_item()` - Generate podcast
- `generate_podcast()` - Main generation logic
- `synthesize_text()` - Call ElevenLabs API
- `stitch_audio_segments()` - Combine audio files
- `upload_podcast_to_media_library()` - Store in media

### includes/podcast-settings.php
**Admin settings UI** - ~400 lines

Provides:
- Settings registration
- Admin form fields
- Media picker for audio files
- Settings sanitization
- Input validation

**Settings:**
- ElevenLabs API key
- Title voice ID
- Content voice ID
- Intro/outro audio selection

---

## Decision Tree

**I want to...**

### ...set up the plugin
→ Read [QUICKSTART.md](#quickstartmd)

### ...understand what it does
→ Read [README.md](#readmemd)

### ...integrate podcasts into my site
→ Read [PODCAST_EXAMPLES.php](#podcast_examplesphp)

### ...know all available features
→ Read [PODCAST_INTEGRATION_GUIDE.md](#podcast_integration_guidemd)

### ...troubleshoot an issue
→ Use [podcast-test-suite.php](#podcast-test-suitephp)
→ Then read troubleshooting section in [PODCAST_INTEGRATION_GUIDE.md](#podcast_integration_guidemd)

### ...understand the API
→ Read [PODCAST_INTEGRATION_GUIDE.md](#podcast_integration_guidemd)
→ See examples in [PODCAST_EXAMPLES.php](#podcast_examplesphp)

### ...implement custom features
→ Read [PODCAST_EXAMPLES.php](#podcast_examplesphp) examples
→ Reference [PODCAST_INTEGRATION_GUIDE.md](#podcast_integration_guidemd) for filters/hooks

---

## Getting Started (Right Now)

### Option 1: Fastest (5 minutes)
1. Read: [QUICKSTART.md](#quickstartmd) - Setup checklist section
2. Get: ElevenLabs API key
3. Go: to WordPress admin → WPShadow Site
4. Configure: API key & voices
5. Test: Publish a KB article

### Option 2: Thorough (15 minutes)
1. Read: [README.md](#readmemd) - Overview & features
2. Read: [QUICKSTART.md](#quickstartmd) - Full setup guide
3. Follow: Step-by-step instructions
4. Run: `podcast-test-suite.php` to validate
5. Display: Podcast in template using [PODCAST_EXAMPLES.php](#podcast_examplesphp) code

### Option 3: Complete (30 minutes)
1. Read: [README.md](#readmemd) - Feature overview
2. Read: [QUICKSTART.md](#quickstartmd) - Full setup
3. Read: [PODCAST_INTEGRATION_GUIDE.md](#podcast_integration_guidemd) - Deep dive
4. Review: [PODCAST_EXAMPLES.php](#podcast_examplesphp) - Implementation patterns
5. Setup: Step-by-step in WordPress
6. Validate: Run `podcast-test-suite.php`
7. Integrate: Code examples as needed

---

## File Locations

```
/workspaces/wpshadow/
├── ELEVENLABS_SETUP_COMPLETE.md        ← You are here
└── wp-content/plugins/wpshadow-site/
    ├── wpshadow-site.php               (Main plugin)
    ├── README.md                       (Overview)
    ├── QUICKSTART.md                   (Setup guide)
    ├── PODCAST_INTEGRATION_GUIDE.md    (Full docs)
    ├── PODCAST_EXAMPLES.php            (Code examples)
    ├── podcast-test-suite.php          (Diagnostics)
    ├── includes/
    │   ├── class-podcast-generator.php (Core system)
    │   └── podcast-settings.php        (Admin UI)
    ├── assets/
    │   └── admin.css                   (Styling)
    └── uninstall.php                   (Cleanup)
```

---

## Quick Reference Commands

### Run Diagnostics
```php
// Add to functions.php temporarily
require_once '/path/to/podcast-test-suite.php';
wpshadow_podcast_test_suite();
```

### Check Queue Status
```sql
SELECT status, COUNT(*)
FROM wp_wpshadow_podcast_queue
GROUP BY status;
```

### Process Queue Manually
```php
WPShadow_Podcast_Generator::trigger_queue_processing();
```

### Get Podcast for Article
```php
$podcast_id = get_post_meta( $post_id, '_wpshadow_podcast_id', true );
$podcast_url = wp_get_attachment_url( $podcast_id );
```

---

## Common Tasks

### Task: Set up for first time
**Documents:** [QUICKSTART.md](#quickstartmd) (30 min)

### Task: Display podcast on site
**Documents:** [PODCAST_EXAMPLES.php](#podcast_examplesphp) - Example 1 (5 min)

### Task: Create podcast shortcode
**Documents:** [PODCAST_EXAMPLES.php](#podcast_examplesphp) - Example 2 (10 min)

### Task: Troubleshoot generation failure
**Documents:** [podcast-test-suite.php](#podcast-test-suitephp) → [PODCAST_INTEGRATION_GUIDE.md](#podcast_integration_guidemd) - Troubleshooting (15 min)

### Task: Implement per-article voices
**Documents:** [PODCAST_EXAMPLES.php](#podcast_examplesphp) - Example 6 (20 min)

### Task: Send notifications on completion
**Documents:** [PODCAST_EXAMPLES.php](#podcast_examplesphp) - Example 10 (15 min)

### Task: Add podcast to REST API
**Documents:** [PODCAST_EXAMPLES.php](#podcast_examplesphp) - Example 8 (10 min)

---

## Support Resources

| Issue | Resource |
|-------|----------|
| Setup help | [QUICKSTART.md](#quickstartmd) |
| Feature questions | [README.md](#readmemd) |
| API documentation | [PODCAST_INTEGRATION_GUIDE.md](#podcast_integration_guidemd) |
| Code examples | [PODCAST_EXAMPLES.php](#podcast_examplesphp) |
| System check | [podcast-test-suite.php](#podcast-test-suitephp) |
| Troubleshooting | [PODCAST_INTEGRATION_GUIDE.md](#podcast_integration_guidemd) - Troubleshooting section |

---

## Summary

✅ **All documentation is in place**

- **Complete plugin** ready to use
- **5 documentation files** covering all aspects
- **10 code examples** for common tasks
- **Diagnostic tools** for troubleshooting
- **Setup guide** for first-time users
- **API reference** for developers

**Estimated setup time:** 30 minutes
**Estimated integration time:** Varies by use case

---

**Pick a document above and get started!** 🎙️
