# WPShadow Site Plugin - ElevenLabs Podcast Integration

## 🎙️ What's Included

This plugin scaffold now includes a complete **ElevenLabs podcast generation system** that automatically creates podcasts from knowledge base articles.

### Plugin Files

```
wpshadow-site/
├── wpshadow-site.php                    # Main plugin file (v0.3.0)
├── includes/
│   ├── class-podcast-generator.php      # Core generator class
│   └── podcast-settings.php             # Admin settings UI
├── assets/
│   └── admin.css                        # Admin interface styling
├── PODCAST_INTEGRATION_GUIDE.md         # Full feature documentation
├── PODCAST_EXAMPLES.php                 # Practical code examples
└── uninstall.php                        # Cleanup on removal
```

## 🚀 Quick Start

### 1. Get ElevenLabs API Key
- Sign up at [elevenlabs.io](https://elevenlabs.io)
- Get your API key from the console
- Choose voice IDs you want to use

### 2. Configure in WordPress Admin

1. Navigate to **WPShadow Site** menu
2. **ElevenLabs Tab**:
   - Enter API key
   - Set default Voice ID (for general use)
   - Save
3. **Podcast Generator Tab**:
   - ✅ Enable Podcast Generation
   - Set Title Voice ID
   - Set Content Voice ID
   - (Optional) Select intro/outro audio files
   - Save

### 3. Test with a KB Article

1. Go to KB Articles
2. Create or edit an article
3. Publish/Update it
4. Check **WPShadow Site** admin page for processing status
5. Podcast should appear in the article's media within minutes

## 🎯 Key Features

### Automatic Generation
- ✅ Triggers on KB article publication
- ✅ Queues for async processing (doesn't block publish)
- ✅ Shows status in admin notices

### Audio Stitching
- ✅ Combines title + content + intro/outro audio
- ✅ Uses FFmpeg when available for seamless quality
- ✅ Falls back to simple concatenation if needed

### Storage & Access
- ✅ Stores podcasts in WordPress Media Library
- ✅ Links podcast to original KB article via post meta
- ✅ Accessible via REST API and custom code

### Customization
- ✅ Per-article voice settings (via code)
- ✅ Configurable word limits
- ✅ Custom intro/outro for each podcast
- ✅ Filters for extensibility

## 📋 Configuration Options

| Option | Purpose | Example |
|--------|---------|---------|
| **API Key** | ElevenLabs authentication | `sk_...` |
| **Title Voice ID** | Voice for article headings | `21m00Tcm4TlvDq8ikWAM` |
| **Content Voice ID** | Voice for article body | `pNInz6obpgc4xHEEFXtD` |
| **Include Title** | Narrate title at start | ✓ |
| **Intro Audio** | Podcast intro file | Media ID |
| **Outro Audio** | Podcast outro file | Media ID |

## 🔧 System Requirements

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **ElevenLabs Account**: Free or paid tier
- **Optional**: FFmpeg (for better audio stitching)

### Install FFmpeg

```bash
# Alpine
apk add ffmpeg

# Ubuntu/Debian
apt-get install ffmpeg

# macOS
brew install ffmpeg
```

## 📖 Documentation

### Full Guides
- [**PODCAST_INTEGRATION_GUIDE.md**](./PODCAST_INTEGRATION_GUIDE.md) - Complete feature reference
- [**PODCAST_EXAMPLES.php**](./PODCAST_EXAMPLES.php) - Code examples & patterns

### Common Tasks

#### Display Podcast in Template
```php
$post_id = get_the_ID();
$podcast_id = get_post_meta( $post_id, '_wpshadow_podcast_id', true );

if ( $podcast_id ) {
    echo wp_audio_shortcode( array(
        'src' => wp_get_attachment_url( $podcast_id ),
    ) );
}
```

#### Create Podcast Shortcode
```
[kb_podcast id="123"]
```

#### Process Queue Manually
```php
WPShadow_Podcast_Generator::trigger_queue_processing();
```

#### Check Generation Status
```php
$status = get_post_meta( $post_id, '_wpshadow_podcast_generated', true );
```

## 🔍 Troubleshooting

### Podcast Not Generating

**Check 1:** Is generation enabled?
- Go to Podcast Generator tab and verify checkbox is checked

**Check 2:** Are voices configured?
- Both Title and Content Voice IDs must be set

**Check 3:** Is the KB post type correct?
- Default: `kb_article`
- Custom types: Use filter `wpshadow_kb_article_post_type`

**Check 4:** Is WordPress cron running?
```bash
wp cron test
```

### API Errors

**"API key is missing"**
- Go to ElevenLabs tab and enter your key

**"Voice ID is missing"**
- Configure both Title and Content voices in Podcast Generator

**"HTTP 401 Unauthorized"**
- Check API key validity and expiration
- Visit [elevenlabs.io](https://elevenlabs.io) console

## 🗄️ Database

### Queue Table
```sql
wp_wpshadow_podcast_queue (
    id, post_id, status, created_at, updated_at, error_message
)
```

**Status Values**: `pending`, `processing`, `completed`, `failed`

Created automatically on plugin activation.

## 📊 Performance

| Metric | Typical Value |
|--------|---------------|
| Generation Time | 30-120 sec per article |
| Podcast Size | 3-5 MB per article |
| Processing Delay | 0-5 min (cron dependent) |
| API Cost | ~$0.01-0.05 per article |

## 🔌 Integration Points

### Hooks
- `wpshadow_kb_article_post_type` - Change KB post type
- `wpshadow_podcast_max_words` - Limit content length
- `wpshadow_process_podcast_queue` - Hook on queue processing

### Filters
- `rest_prepare_kb_article` - Add podcast to REST API
- Custom WP-CLI commands (see examples)

### Metadata
- `_wpshadow_podcast_id` - Attachment ID of podcast
- `_wpshadow_podcast_generated` - Generation timestamp

## 🚨 Important Notes

### API Security
- API keys are stored in WordPress options table
- Consider using a secrets manager plugin
- Never commit real keys to version control

### Content Limits
- Default: First 5,000 words of article
- Longer content will be truncated with "..."
- Adjust via `wpshadow_podcast_max_words` filter

### Audio Quality
- Depends on ElevenLabs voice model chosen
- Test with different voices in settings
- Adjust voice settings for stability/similarity

### Storage
- Podcasts stored in `/wp-content/uploads/wpshadow-podcasts/`
- Final files in standard Media Library
- Cleanup on plugin deactivation is optional

## 🛠️ Developer Tips

1. **Custom Per-Article Settings**
   - Use post meta fields to override global settings
   - See PODCAST_EXAMPLES.php for implementation

2. **Monitor Generation**
   - Check admin notices
   - Query `wp_wpshadow_podcast_queue` table
   - Set up email notifications (see examples)

3. **Batch Processing**
   - Schedule periodic cron events at longer intervals
   - Prevents API rate limiting
   - Reduces server load

4. **Audio Customization**
   - Modify voice_settings in synthesize calls
   - Experiment with stability/similarity_boost values
   - Store preferences as post meta

## 🤝 Support

For issues:
1. Check [PODCAST_INTEGRATION_GUIDE.md](./PODCAST_INTEGRATION_GUIDE.md) troubleshooting section
2. Review error messages in admin notices
3. Check WordPress error logs
4. See [PODCAST_EXAMPLES.php](./PODCAST_EXAMPLES.php) for implementation patterns

## 📝 Changelog

### v0.3.0
- ✅ Added ElevenLabs Podcast Generator
- ✅ Async queue processing
- ✅ FFmpeg audio stitching
- ✅ Admin settings UI
- ✅ Media Library integration
- ✅ Status tracking & notifications
- ✅ Comprehensive documentation

### v0.2.0
- ✅ ElevenLabs TTS basic integration
- ✅ Admin settings panel

## 📄 License

GPL-2.0-or-later

---

**Ready to generate podcasts?** Start with the ElevenLabs tab and configure your API credentials! 🎙️
