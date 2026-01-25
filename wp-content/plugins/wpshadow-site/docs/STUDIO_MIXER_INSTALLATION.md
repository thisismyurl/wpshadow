# Studio Mixer Installation Checklist

Quick setup guide to get professional podcasts running.

## ✅ Pre-Installation

- [ ] FFmpeg installed and available (`which ffmpeg`)
- [ ] PHP 7.0+ on server
- [ ] WordPress 5.0+ with WPShadow plugin installed
- [ ] ElevenLabs account created (elevenlabs.io)

## ✅ Installation Steps

### Step 1: Copy Files (1 minute)

- [ ] Download `class-podcast-studio-mixer.php`
- [ ] Place in: `wp-content/plugins/wpshadow-site/includes/`

### Step 2: Include in Plugin (1 minute)

Edit `wp-content/plugins/wpshadow-site/wpshadow-site.php`:

```php
// Add this line near the top of the file:
require_once plugin_dir_path( __FILE__ ) . 'includes/class-podcast-studio-mixer.php';
```

- [ ] Added require statement
- [ ] Plugin still loads without errors

### Step 3: Configure API Key (2 minutes)

Get your API key:
- [ ] Go to https://elevenlabs.io/account/billing/api-keys
- [ ] Copy your API key

Store in WordPress (via WP-CLI or database):
```bash
wp option add wpshadow_elevenlabs_api_key "sk-..." --allow-root
```

Or via WordPress admin:
```php
update_option( 'wpshadow_elevenlabs_api_key', 'sk-...' );
```

- [ ] API key stored in WordPress

### Step 4: Get Voice IDs (2 minutes)

- [ ] Go to https://elevenlabs.io/studio
- [ ] Browse voices for speaker 1
- [ ] Copy voice ID (e.g., `21m00Tcm4TlvDq8ikWAM`)
- [ ] Browse voices for speaker 2
- [ ] Copy second voice ID

Store in options:
```php
$settings = array(
    'speaker1_voice_id'   => '21m00Tcm4TlvDq8ikWAM',
    'speaker2_voice_id'   => 'EXAVITQu4vr4xnSDxMaL',
);
update_option( 'wpshadow_podcast_settings', $settings );
```

- [ ] Voice IDs stored

### Step 5: Prepare Audio Files (5 minutes)

Upload to WordPress media library:
- [ ] Intro music (10-20 seconds)
- [ ] Background music (30+ seconds)
- [ ] Outro music (5-10 seconds)

Or use file paths:
```php
'music_file' => '/wp-content/uploads/intro-music.mp3'
```

- [ ] Audio files ready

## ✅ Verification (5 minutes)

### Test FFmpeg
```bash
ffmpeg -version
which ffmpeg
```

- [ ] FFmpeg found and working

### Test API Key
Create a test file:

```php
<?php
// test-mixer.php (in WordPress root)

require 'wp-load.php';

$mixer = new WPShadow_Podcast_Studio_Mixer();

echo 'Mixer initialized successfully!';
```

Run:
```bash
php test-mixer.php
```

- [ ] "Mixer initialized successfully!" displays

### Test Full Generation
```php
$mixer = new WPShadow_Podcast_Studio_Mixer();

$result = $mixer->generate_professional_podcast( array(
    'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
    'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',
    
    'intro_config' => array(
        'narration'  => 'Welcome to test episode',
        'music_file' => 'intro.mp3', // Your file
    ),
    
    'episode_config' => array(
        'title'       => 'Test Episode',
        'description' => 'Testing the mixer',
        'content'     => '[SPEAKER 1]: This is a test.',
    ),
    
    'outro_config' => array(
        'narration'       => 'Thanks for listening',
        'sponsor_mention' => 'Test sponsor',
        'music_file'      => 'outro.mp3',
    ),
) );

if ( is_wp_error( $result ) ) {
    echo 'Error: ' . $result->get_error_message();
} else {
    echo 'Success: ' . $result['podcast_file'];
}
```

- [ ] Podcast generated without errors
- [ ] Output file exists
- [ ] File is playable

## ✅ Troubleshooting

### Issue: Class not found

**Check:**
```bash
ls wp-content/plugins/wpshadow-site/includes/class-podcast-studio-mixer.php
```

**Fix:** Copy file to correct location

- [ ] File exists in correct location
- [ ] Require statement added to main plugin

### Issue: FFmpeg not found

**Check:**
```bash
which ffmpeg
ffmpeg -version
```

**Fix:** Install FFmpeg
```bash
# Ubuntu/Debian
apt-get install ffmpeg

# Alpine
apk add ffmpeg
```

- [ ] FFmpeg installed and working

### Issue: API key not working

**Check:**
```php
echo get_option( 'wpshadow_elevenlabs_api_key' );
```

**Fix:** Set correct API key
```php
update_option( 'wpshadow_elevenlabs_api_key', 'sk-xxx' );
```

- [ ] API key is set correctly
- [ ] Key starts with "sk-"

### Issue: Audio files not found

**Check:**
```php
echo get_attached_file( 456 ); // Attachment ID
```

**Fix:** Use correct path or attachment ID

- [ ] File path exists or attachment ID is valid

## ✅ Configuration (Optional)

### Store Default Voice IDs
```php
update_option( 'wpshadow_podcast_settings', array(
    'enabled'                => true,
    'speaker1_voice_id'      => '21m00Tcm4TlvDq8ikWAM',
    'speaker2_voice_id'      => 'EXAVITQu4vr4xnSDxMaL',
    'background_music_id'    => 456, // Attachment ID
    'intro_audio_id'         => 457,
    'outro_audio_id'         => 458,
) );
```

- [ ] Default settings stored

### Add Admin UI

Optionally add a settings page with tabs:
- ElevenLabs settings
- Voice IDs
- Audio file pickers
- Narration text fields

See [STUDIO_MIXER_INTEGRATION.md](./STUDIO_MIXER_INTEGRATION.md) for code.

- [ ] Admin UI added (optional)

## ✅ Usage

### Basic Usage
```php
$mixer = new WPShadow_Podcast_Studio_Mixer();
$result = $mixer->generate_professional_podcast( $config );
```

### With Error Handling
```php
if ( is_wp_error( $result ) ) {
    error_log( $result->get_error_message() );
}
```

### Upload to Media Library
```php
$attachment_id = media_handle_sideload(
    array(
        'name'     => 'podcast.mp3',
        'tmp_name' => $result['podcast_file'],
    ),
    0
);
```

## ✅ Next Steps

- [ ] Review [STUDIO_MIXER_QUICKREF.md](./STUDIO_MIXER_QUICKREF.md)
- [ ] Read [STUDIO_MIXER_GUIDE.md](./STUDIO_MIXER_GUIDE.md)
- [ ] Study [STUDIO_MIXER_EXAMPLES.php](./STUDIO_MIXER_EXAMPLES.php)
- [ ] Test with real podcast content
- [ ] Configure admin UI
- [ ] Deploy to production

## ✅ Documentation

| Document | Purpose | Time |
|----------|---------|------|
| STUDIO_MIXER_QUICKREF.md | Quick reference | 5 min |
| STUDIO_MIXER_GUIDE.md | Complete docs | 30 min |
| STUDIO_MIXER_EXAMPLES.php | Code examples | 15 min |
| STUDIO_MIXER_INTEGRATION.md | Integration steps | 20 min |
| STUDIO_MIXER_FEATURE_SUMMARY.md | Overview | 10 min |

## ✅ Support

If you get stuck:

1. Check [STUDIO_MIXER_QUICKREF.md#troubleshooting](./STUDIO_MIXER_QUICKREF.md#troubleshooting)
2. Review error messages in error logs
3. Verify FFmpeg: `ffmpeg -version`
4. Verify API key: `wp option get wpshadow_elevenlabs_api_key`
5. Test basic TTS first before full podcast

## ✅ Production Readiness

Before going live:

- [ ] Tested with real podcast content
- [ ] Error handling in place
- [ ] Logging configured
- [ ] Audio quality verified
- [ ] Sponsor messages working
- [ ] Music files optimized
- [ ] Backup system in place
- [ ] Performance monitored

## Estimated Total Setup Time

- Installation: 10 minutes
- Verification: 10 minutes
- Configuration: 5-10 minutes
- Testing: 10-20 minutes

**Total: ~30-50 minutes**

---

**All set!** 🎉 You're ready to generate professional two-person podcasts with ElevenLabs!
