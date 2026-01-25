# 🚀 Podcast Integration - Quick Start Checklist

## Pre-Setup Requirements

- [ ] WordPress 5.0+
- [ ] PHP 7.4+
- [ ] Active ElevenLabs account
- [ ] ElevenLabs API key ready
- [ ] Selected voice IDs for title and content

## Step 1: Get ElevenLabs Credentials (5 min)

- [ ] Create account at [elevenlabs.io](https://elevenlabs.io)
- [ ] Log in to console
- [ ] Copy your **API Key** from settings
- [ ] Visit **Voices** section
- [ ] Note down at least 2 voice IDs:
  - One for **titles** (e.g., professional/narrator)
  - One for **content** (e.g., conversational/engaging)

**Useful voices:**
- Professional: `21m00Tcm4TlvDq8ikWAM` (Adam)
- Female: `pNInz6obpgc4xHEEFXtD` (Lily)
- British: `i51z3mQpzVP4nGR7P2j4` (Michael)

## Step 2: WordPress Configuration (10 min)

### A. ElevenLabs Settings
1. Go to **WPShadow Site** menu in admin
2. Click **ElevenLabs** tab
3. Enter your **API Key**
4. Enter a **Voice ID** for default use
5. Click **Save Settings**

### B. Podcast Generator Settings
1. Go to **WPShadow Site** → **Podcast Generator**
2. ✅ Check **Enable Podcast Generation**
3. Enter **Title Voice ID**
4. Enter **Content Voice ID**
5. ✅ Check **Include Article Title in Podcast**
6. (Optional) Select intro/outro audio files
7. Click **Save Podcast Settings**

## Step 3: Optional - Install FFmpeg (5 min)

For best audio quality with seamless stitching:

```bash
# Alpine
apk add ffmpeg

# Ubuntu/Debian
apt-get install ffmpeg

# macOS
brew install ffmpeg
```

If not installed, system will use fallback method (still works, may have slight gaps).

## Step 4: Test the System (5 min)

### Quick Test
1. Go to KB Articles
2. Create a test article with title and content
3. Publish it
4. Check **WPShadow Site** admin page
5. Look for admin notice about podcast generation
6. Wait 1-5 minutes for processing

### Validate Setup
1. In plugin folder, find `podcast-test-suite.php`
2. Add this to your theme's `functions.php`:
   ```php
   require_once '/path/to/podcast-test-suite.php';
   wpshadow_podcast_test_suite();
   ```
3. Visit your admin dashboard
4. Check results for any issues marked ❌

## Step 5: Monitor First Generation (10 min)

1. Publish a KB article
2. Go to **WPShadow Site** admin page
3. Check admin notice for status:
   - ⏳ "1 podcast(s) queued for generation"
   - ✅ Notice disappears (completed)
4. Visit the article post
5. Check for podcast in media (optional)

## Step 6: Display Podcast (Optional - 5 min)

Add to your KB article template or use shortcode:

### Option A: Shortcode (Easiest)
```
[kb_podcast id="123"]
```

### Option B: Template Code
```php
<?php
$post_id = get_the_ID();
$podcast_id = get_post_meta( $post_id, '_wpshadow_podcast_id', true );

if ( $podcast_id ) {
    echo wp_audio_shortcode( array(
        'src' => wp_get_attachment_url( $podcast_id ),
    ) );
}
?>
```

## Troubleshooting Checklist

### Podcast Not Generating?

1. **Enabled?**
   - [ ] Checked **Enable Podcast Generation** box

2. **Voices Set?**
   - [ ] Title Voice ID is not empty
   - [ ] Content Voice ID is not empty

3. **API Key Valid?**
   - [ ] Test on [elevenlabs.io](https://elevenlabs.io)
   - [ ] Key hasn't expired
   - [ ] Key is in right account

4. **Processing?**
   - [ ] WordPress cron is running
   - [ ] Check WP admin for notices
   - [ ] Try: `wp cron test` (CLI)

5. **File Permissions?**
   - [ ] `/wp-content/uploads/` is writable
   - [ ] `/wp-content/uploads/wpshadow-podcasts/` exists and writable

### API Errors?

| Error | Solution |
|-------|----------|
| "API key missing" | Enter key in ElevenLabs tab |
| "Voice ID missing" | Configure both Title & Content voices |
| "HTTP 401" | Check API key validity |
| "Rate limited" | Wait before trying again |

## Performance Notes

| Metric | Expectation |
|--------|------------|
| **Generation Time** | 30-120 seconds per article |
| **Article Limit** | First 5,000 words |
| **File Size** | 3-5 MB per podcast |
| **API Cost** | ~$0.01-0.05 per article |
| **Cron Delay** | 0-5 minutes (WordPress cron) |

## Security Reminders

- [ ] Never commit API key to git
- [ ] Use strong unique key per environment
- [ ] Consider secrets manager plugin for production
- [ ] Keys stored as plain text in database (use plugin to encrypt if needed)

## Next Steps

After basic setup works:

1. **Customize Voices** (per article)
   - See `PODCAST_EXAMPLES.php` - Example 6
   - Add custom voice per KB article

2. **Track Statistics**
   - Query `wp_wpshadow_podcast_queue` table
   - Monitor generation success rate

3. **Add Notifications**
   - See `PODCAST_EXAMPLES.php` - Example 10
   - Email admins when podcasts complete

4. **Create Feed**
   - See documentation for podcast RSS feed
   - Submit to podcast platforms (Spotify, Apple Podcasts, etc.)

5. **REST API Integration**
   - See `PODCAST_EXAMPLES.php` - Example 8
   - Include podcasts in API responses

## File Structure

```
wpshadow-site/
├── wpshadow-site.php                    ← Main plugin
├── README.md                            ← This guide
├── PODCAST_INTEGRATION_GUIDE.md         ← Full documentation
├── PODCAST_EXAMPLES.php                 ← Code examples
├── podcast-test-suite.php               ← Diagnostics
├── includes/
│   ├── class-podcast-generator.php      ← Core system
│   └── podcast-settings.php             ← Admin UI
└── assets/
    └── admin.css                        ← Styling
```

## Getting Help

1. **Check docs:**
   - `PODCAST_INTEGRATION_GUIDE.md` - Feature reference
   - `PODCAST_EXAMPLES.php` - Code patterns

2. **Debug:**
   - Run `podcast-test-suite.php`
   - Check WordPress error logs
   - Monitor `wp_wpshadow_podcast_queue` table

3. **Issues:**
   - Check admin notices
   - Verify API key and voices
   - Test with `podcast-test-suite.php`

## Quick Reference

### Admin Pages
- **WPShadow Site** → **ElevenLabs**: API & voice config
- **WPShadow Site** → **Podcast Generator**: Generation settings

### Key Files to Check
- `wp_wpshadow_podcast_queue` - Generation status
- Post meta `_wpshadow_podcast_id` - Links article to podcast
- `/wp-content/uploads/` - Podcast storage

### Useful Commands
```bash
# Test WordPress cron
wp cron test

# Check generation queue
wp db query "SELECT * FROM wp_wpshadow_podcast_queue"

# Process queue manually
wp eval 'WPShadow_Podcast_Generator::trigger_queue_processing();'
```

---

**Estimated total time to full setup: 30 minutes** ⏱️

**You're ready! Publish your first KB article and watch the magic happen.** 🎙️
