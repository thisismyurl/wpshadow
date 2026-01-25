# ElevenLabs Podcast Generation Integration

## Overview

This integration automatically generates podcasts from KB articles when they are published. It handles:

- **Text-to-Speech Synthesis**: Converts article titles and content to audio using ElevenLabs API
- **Audio Stitching**: Combines title, content, and optional intro/outro audio using FFmpeg
- **Media Management**: Stores generated podcasts in WordPress media library
- **Queue Processing**: Handles podcast generation asynchronously to avoid blocking post publication
- **Status Tracking**: Admin notices show pending and failed generation tasks

## Features

### 1. **Dual Voice Support**
- Separate voice IDs for article titles and content
- Allows variety in podcast narration for better listener engagement

### 2. **Intro/Outro Audio**
- Optional custom intro audio (e.g., podcast theme, disclaimer)
- Optional custom outro audio (e.g., call-to-action, credits)
- Automatically selected from media library

### 3. **Intelligent Content Processing**
- Removes HTML, shortcodes, and extra whitespace
- Limits content to 5000 words to manage API costs
- Includes configurable word limit

### 4. **Audio Stitching**
- **FFmpeg Support**: Uses FFmpeg for seamless audio concatenation when available
- **Fallback Mode**: Simple binary concatenation if FFmpeg not installed
- Cleans up temporary files automatically

### 5. **Queue-Based Processing**
- Posts queued for generation don't block publication
- Automatic WordPress cron triggers processing
- Manual trigger available for immediate processing

## Setup

### Step 1: Configure ElevenLabs API

1. Go to **WPShadow Site** → **ElevenLabs** tab
2. Enter your ElevenLabs API key
3. Set a default Voice ID for general use
4. (Optional) Adjust Model ID if needed
5. Click **Save Settings**

**Get API Key**: [ElevenLabs Console](https://elevenlabs.io/app/speech-synthesis)
**Get Voice IDs**: Available in your ElevenLabs account under Voices

### Step 2: Configure Podcast Generation

1. Go to **WPShadow Site** → **Podcast Generator** tab
2. **Enable Podcast Generation**: Check this box
3. **Title Voice**: Enter the Voice ID for article title narration
4. **Content Voice**: Enter the Voice ID for article body narration
5. **Include Article Title**: Check to include title as audio at start
6. **Intro Audio** (optional):
   - Click "Select Intro Audio"
   - Choose or upload an audio file from media library
7. **Outro Audio** (optional):
   - Click "Select Outro Audio"
   - Choose or upload an audio file from media library
8. **Auto-Create Podcast Post** (optional): Check to create linked podcast posts
9. Click **Save Podcast Settings**

### Step 3: Install FFmpeg (Recommended)

For best audio stitching quality, install FFmpeg:

```bash
# Alpine Linux
apk add ffmpeg

# Ubuntu/Debian
apt-get install ffmpeg

# macOS
brew install ffmpeg
```

If FFmpeg is not available, the system will fall back to simple audio concatenation.

## How It Works

### Publication Workflow

```
KB Article Published
    ↓
Auto-queued for Podcast Generation
    ↓
WordPress Cron Triggers Processing
    ↓
Content Extracted & Cleaned
    ↓
Audio Synthesis (Title + Content)
    ↓
Audio Stitching (Intro + Title + Content + Outro)
    ↓
Upload to Media Library
    ↓
Store Attachment ID in Post Meta
    ↓
Admin Notice Confirms Completion
```

### Queue System

The plugin maintains a `wp_wpshadow_podcast_queue` table tracking generation status:

- **pending**: Queued, waiting for processing
- **processing**: Currently being generated
- **completed**: Successfully generated
- **failed**: Generation failed (error message stored)

Admin notices display:
- Count of pending podcasts
- Count of failed generations (with link to review logs)

## PHP API

### Process Queue Item Manually

```php
// Get the instance
global $wpshadow_podcast_generator;

// Process next pending item
$wpshadow_podcast_generator->process_queue_item();

// Or process specific queue item by ID
$wpshadow_podcast_generator->process_queue_item( $queue_id );
```

### Trigger Queue Processing

```php
// Immediately process queue
WPShadow_Podcast_Generator::trigger_queue_processing();
```

### Get Podcast Attachment

```php
// For a published KB article
$post_id = 123;
$podcast_id = get_post_meta( $post_id, '_wpshadow_podcast_id', true );

if ( $podcast_id ) {
    $podcast_url = wp_get_attachment_url( $podcast_id );
    // Now you can display the podcast player
}
```

### Display Podcast in Frontend

```php
// Add to KB article template
$post_id = get_the_ID();
$podcast_id = get_post_meta( $post_id, '_wpshadow_podcast_id', true );

if ( $podcast_id ) {
    echo wp_audio_shortcode( array( 'src' => wp_get_attachment_url( $podcast_id ) ) );
}
```

## Filters & Hooks

### `wpshadow_kb_article_post_type`

Change the KB post type (default: `kb_article`):

```php
add_filter( 'wpshadow_kb_article_post_type', function() {
    return 'my_custom_kb_type';
});
```

### `wpshadow_podcast_max_words`

Limit maximum words for content synthesis (default: 5000):

```php
add_filter( 'wpshadow_podcast_max_words', function() {
    return 3000; // Process only first 3000 words
});
```

### `wpshadow_process_podcast_queue`

Hook triggered when queue is processed:

```php
add_action( 'wpshadow_process_podcast_queue', function() {
    error_log( 'Podcast queue processed!' );
});
```

## Troubleshooting

### "ElevenLabs API key is missing"
- Go to **WPShadow Site** → **ElevenLabs** tab
- Enter your API key and save

### "ElevenLabs voice ID is missing"
- Configure Voice IDs in **Podcast Generator** tab
- Ensure both Title and Content voices are set

### Podcasts not generating
1. Check admin notices for failure messages
2. Verify WordPress cron is running:
   ```bash
   # Check if cron jobs exist
   wp cron test
   ```
3. Manually trigger processing:
   ```php
   WPShadow_Podcast_Generator::trigger_queue_processing();
   ```
4. Check server error logs for FFmpeg or API issues

### Audio quality issues
- Verify ElevenLabs API key and voice IDs are correct
- Try different `voice_settings` by calling `wpshadow_site_elevenlabs_tts()` with custom settings:
  ```php
  $result = wpshadow_site_elevenlabs_tts(
      'Your text here',
      array(
          'voice_id' => 'your-voice-id',
          'voice_settings' => array(
              'stability' => 0.7,
              'similarity_boost' => 0.85
          )
      )
  );
  ```

### Stitching produces silent gaps
- Install FFmpeg for proper audio concatenation
- Without FFmpeg, intro/outro may not align perfectly with content

## Database

### Queue Table Schema

```sql
CREATE TABLE wp_wpshadow_podcast_queue (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    error_message LONGTEXT,
    KEY (post_id),
    KEY (status)
);
```

This table is created automatically on plugin activation.

## Storage

Podcasts are stored in:
- **Location**: `/wp-content/uploads/wpshadow-podcasts/` (temporary during processing)
- **Final**: WordPress Media Library (attachment posts)
- **Metadata**: Post meta key `_wpshadow_podcast_id` links KB articles to podcast attachments

## Performance Notes

- Average generation time per KB article: 30-120 seconds (depending on article length)
- API costs: Based on ElevenLabs pricing per character synthesized
- Storage: Each podcast ~3-5 MB for typical KB articles
- FFmpeg stitching: ~5-10 seconds additional per podcast

## API Rate Limits

ElevenLabs has rate limits based on your subscription. Monitor:
- Characters per minute
- Concurrent requests
- Monthly quota

Implement backoff/retry logic if hitting limits:

```php
// Add custom cron schedule for slow processing
add_filter( 'cron_schedules', function( $schedules ) {
    $schedules['every_5_minutes'] = array(
        'interval' => 300,
        'display'  => '5 Minutes'
    );
    return $schedules;
});

// Schedule periodic processing instead of immediate
if ( ! wp_next_scheduled( 'wpshadow_process_podcast_queue' ) ) {
    wp_schedule_event( time(), 'every_5_minutes', 'wpshadow_process_podcast_queue' );
}
```

## Future Enhancements

- [ ] Batch processing for multiple KB articles
- [ ] Support for multiple voices per article (speaker rotation)
- [ ] Podcast feed generation (RSS/iTunes)
- [ ] Custom intro/outro per KB category
- [ ] Audio format options (WAV, FLAC, M4A)
- [ ] Podcast metadata (chapters, timestamps)
- [ ] Admin dashboard with generation statistics
- [ ] Integration with podcast hosting services
