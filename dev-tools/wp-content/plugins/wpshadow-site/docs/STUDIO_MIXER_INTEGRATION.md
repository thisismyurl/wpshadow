# Studio Mixer Integration Guide

How to integrate `WPShadow_Podcast_Studio_Mixer` into your existing WPShadow plugin.

## Quick Start

### 1. Add the Mixer Class to Your Plugin

The file `includes/class-podcast-studio-mixer.php` is ready to use. Just include it in your main plugin file:

```php
// In wp-content/plugins/wpshadow-site/wpshadow-site.php

require_once plugin_dir_path( __FILE__ ) . 'includes/class-podcast-studio-mixer.php';
```

### 2. Update Your Podcast Settings

Add these options to your podcast settings panel:

```php
// In includes/podcast-settings.php, add new fields:

// Voice IDs (for Studio API)
'speaker1_voice_id' => 'Voice ID for Speaker 1 (Host)',
'speaker2_voice_id' => 'Voice ID for Speaker 2 (Guest)',

// Background Music
'background_music_id' => 'Media attachment ID for background music',

// Intro Configuration
'intro_narration' => 'Intro narration text (e.g., "Welcome to the show!")',
'intro_music_id' => 'Media attachment ID for intro music',

// Outro Configuration  
'outro_narration' => 'Outro narration text',
'sponsor_mention' => 'Sponsor mention text',
'outro_music_id' => 'Media attachment ID for outro music',
```

### 3. Update Your Podcast Generator

Modify your existing `WPShadow_Podcast_Generator` to use the Studio Mixer:

```php
// In includes/class-podcast-generator.php

public function generate_podcast( $post_id ) {
    $post = get_post( $post_id );
    $settings = $this->get_podcast_settings();
    
    // Check if using Studio Mixer (new method).
    $use_studio_mixer = get_post_meta( $post_id, '_use_studio_podcast', true );
    
    if ( ! $use_studio_mixer && ! $settings['use_studio_mixer'] ) {
        // Use original method.
        return $this->generate_podcast_original( $post_id );
    }
    
    // Use new Studio Mixer method.
    return $this->generate_podcast_with_studio_mixer( $post_id );
}

/**
 * Generate podcast using Studio Mixer.
 */
private function generate_podcast_with_studio_mixer( $post_id ) {
    $post = get_post( $post_id );
    $settings = $this->get_podcast_settings();
    
    // Prepare content (split into speakers or use existing format).
    $content = $this->prepare_studio_podcast_content( $post->post_content );
    
    // Create mixer instance.
    $mixer = new WPShadow_Podcast_Studio_Mixer();
    
    // Build configuration.
    $config = array(
        'speaker1_voice_id' => $settings['speaker1_voice_id'] ?? '',
        'speaker2_voice_id' => $settings['speaker2_voice_id'] ?? '',
        
        'intro_config' => array(
            'narration'  => $settings['intro_narration'] ?? 'Welcome to the episode!',
            'music_file' => $settings['intro_music_id'] ?? '',
        ),
        
        'episode_config' => array(
            'title'            => $post->post_title,
            'description'      => wp_strip_all_tags( substr( $post->post_content, 0, 200 ) ),
            'content'          => $content,
            'background_music' => $settings['background_music_id'] ?? '',
        ),
        
        'outro_config' => array(
            'narration'       => $settings['outro_narration'] ?? 'Thank you for listening!',
            'sponsor_mention' => $settings['sponsor_mention'] ?? '',
            'music_file'      => $settings['outro_music_id'] ?? '',
        ),
        
        'post_id' => $post_id,
    );
    
    // Generate podcast.
    $result = $mixer->generate_professional_podcast( $config );
    
    if ( is_wp_error( $result ) ) {
        return $result;
    }
    
    // Upload to media library.
    $podcast_id = $this->upload_podcast_to_media_library(
        $result['podcast_file'],
        $post_id,
        $post->post_title
    );
    
    if ( is_wp_error( $podcast_id ) ) {
        return $podcast_id;
    }
    
    // Store metadata.
    update_post_meta( $post_id, '_wpshadow_podcast_id', $podcast_id );
    update_post_meta( $post_id, '_wpshadow_podcast_generated', current_time( 'mysql' ) );
    update_post_meta( $post_id, '_wpshadow_podcast_type', 'studio' );
    
    return array( 'podcast_id' => $podcast_id );
}

/**
 * Prepare content for Studio Mixer.
 * Converts single narration to two-speaker format.
 */
private function prepare_studio_podcast_content( $content ) {
    $content = wp_strip_all_tags( $content );
    
    // Split into sentences.
    $sentences = preg_split( '/(?<=[.!?])\s+/', trim( $content ), -1, PREG_SPLIT_NO_EMPTY );
    
    if ( count( $sentences ) < 2 ) {
        return '[SPEAKER 1]: ' . $content;
    }
    
    // Alternate between speakers for conversation effect.
    $podcast_content = '';
    $speaker = 1;
    
    foreach ( $sentences as $sentence ) {
        $podcast_content .= '[SPEAKER ' . $speaker . ']: ' . trim( $sentence ) . ' ';
        $speaker = ( $speaker === 1 ) ? 2 : 1;
    }
    
    return $podcast_content;
}
```

### 4. Add Admin Settings UI

Add tabs or sections to your settings page:

```php
// In includes/podcast-settings.php

// New tab in settings form
<h2 class="nav-tab-wrapper">
    <a href="?page=wpshadow-site&tab=podcast" class="nav-tab">Podcast Generator</a>
    <a href="?page=wpshadow-site&tab=studio" class="nav-tab nav-tab-active">Studio Mixer (NEW)</a>
</h2>

// Studio Mixer settings form
if ( 'studio' === $tab ) {
    ?>
    <div class="wrap">
        <h2>Studio Mixer Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'wpshadow_studio_settings' ); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="speaker1_voice_id">Speaker 1 Voice ID (Host)</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="speaker1_voice_id" 
                               name="wpshadow_studio_settings[speaker1_voice_id]"
                               value="<?php echo esc_attr( $settings['speaker1_voice_id'] ?? '' ); ?>"
                               size="50">
                        <p class="description">Voice ID from your ElevenLabs account</p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="speaker2_voice_id">Speaker 2 Voice ID (Guest)</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="speaker2_voice_id" 
                               name="wpshadow_studio_settings[speaker2_voice_id]"
                               value="<?php echo esc_attr( $settings['speaker2_voice_id'] ?? '' ); ?>"
                               size="50">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="intro_narration">Intro Narration</label>
                    </th>
                    <td>
                        <textarea id="intro_narration" 
                                  name="wpshadow_studio_settings[intro_narration]"
                                  rows="3"
                                  style="width: 100%;"><?php echo esc_textarea( $settings['intro_narration'] ?? '' ); ?></textarea>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="intro_music">Intro Music</label>
                    </th>
                    <td>
                        <?php
                        wp_media_upload_input(
                            'intro_music',
                            $settings['intro_music_id'] ?? 0,
                            array( 'type' => 'audio' )
                        );
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="background_music">Background Music</label>
                    </th>
                    <td>
                        <?php
                        wp_media_upload_input(
                            'background_music',
                            $settings['background_music_id'] ?? 0,
                            array( 'type' => 'audio' )
                        );
                        ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="outro_narration">Outro Narration</label>
                    </th>
                    <td>
                        <textarea id="outro_narration" 
                                  name="wpshadow_studio_settings[outro_narration]"
                                  rows="3"
                                  style="width: 100%;"><?php echo esc_textarea( $settings['outro_narration'] ?? '' ); ?></textarea>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="sponsor_mention">Sponsor Mention</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="sponsor_mention" 
                               name="wpshadow_studio_settings[sponsor_mention]"
                               value="<?php echo esc_attr( $settings['sponsor_mention'] ?? '' ); ?>"
                               size="80">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="outro_music">Outro Music</label>
                    </th>
                    <td>
                        <?php
                        wp_media_upload_input(
                            'outro_music',
                            $settings['outro_music_id'] ?? 0,
                            array( 'type' => 'audio' )
                        );
                        ?>
                    </td>
                </tr>
            </table>
            
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
```

## File Structure

After integration, your plugin structure will be:

```
wp-content/plugins/wpshadow-site/
├── wpshadow-site.php                              (Main plugin file)
├── includes/
│   ├── class-podcast-generator.php                (Original, with studio mixer support)
│   ├── class-podcast-studio-mixer.php             (NEW: Studio Mixer class)
│   └── podcast-settings.php                       (Updated with Studio settings)
├── assets/admin.css                               (May need updates for UI)
├── STUDIO_MIXER_GUIDE.md                          (NEW: Complete documentation)
├── STUDIO_MIXER_EXAMPLES.php                      (NEW: Code examples)
├── STUDIO_MIXER_INTEGRATION.md                    (This file)
└── README.md                                      (Update this too)
```

## Testing

### 1. Test Basic Functionality

```php
// Test in WordPress admin or custom CLI command
function wpshadow_test_studio_mixer() {
    $mixer = new WPShadow_Podcast_Studio_Mixer( 'your-api-key' );
    
    $config = array(
        'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
        'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',
        
        'intro_config' => array(
            'narration'  => 'Test intro',
            'music_file' => 'test-music.mp3',
        ),
        
        'episode_config' => array(
            'title'       => 'Test Episode',
            'description' => 'Testing the mixer',
            'content'     => '[SPEAKER 1]: Test content',
        ),
        
        'outro_config' => array(
            'narration'       => 'Test outro',
            'sponsor_mention' => 'Test sponsor',
            'music_file'      => 'test-music.mp3',
        ),
    );
    
    $result = $mixer->generate_professional_podcast( $config );
    
    if ( is_wp_error( $result ) ) {
        wp_die( $result->get_error_message() );
    }
    
    echo 'Success! Podcast: ' . $result['podcast_file'];
}
```

### 2. Verify FFmpeg Installation

```bash
# In your server/container
which ffmpeg
ffmpeg -version
```

### 3. Check API Key

```php
// Verify API key is set
$api_key = get_option( 'wpshadow_elevenlabs_api_key', '' );
if ( empty( $api_key ) ) {
    wp_die( 'API key not configured!' );
}
echo 'API key found: ' . substr( $api_key, 0, 10 ) . '...';
```

## Troubleshooting Integration

### Issue: Class not found

**Solution:** Make sure to include the class file in your main plugin file:

```php
require_once plugin_dir_path( __FILE__ ) . 'includes/class-podcast-studio-mixer.php';
```

### Issue: FFmpeg errors

**Solution:** Ensure FFmpeg is installed on your server.

For Docker:
```dockerfile
RUN apk add --no-cache ffmpeg
```

### Issue: API authentication errors

**Solution:** Verify your ElevenLabs API key is correct and set in WordPress options:

```php
update_option( 'wpshadow_elevenlabs_api_key', 'your-actual-key' );
```

### Issue: Audio file not found

**Solution:** Use the correct path or attachment ID:

```php
// This works with both:
'music_file' => 456,                      // Attachment ID
'music_file' => '/uploads/music.mp3',     // File path
```

## Next Steps

1. **Test with real data** - Generate a podcast from an actual KB article
2. **Monitor performance** - Studio API calls may take 30 seconds to 2+ minutes
3. **Adjust audio levels** - Tweak volume percentages in FFmpeg filters if needed
4. **Customize UI** - Add media pickers for easy file selection in admin
5. **Add validation** - Check audio files exist before generation starts

## Documentation Files

| File | Purpose |
|------|---------|
| [STUDIO_MIXER_GUIDE.md](./STUDIO_MIXER_GUIDE.md) | Complete API documentation and examples |
| [STUDIO_MIXER_EXAMPLES.php](./STUDIO_MIXER_EXAMPLES.php) | 8 real-world code examples |
| [STUDIO_MIXER_INTEGRATION.md](./STUDIO_MIXER_INTEGRATION.md) | This file - integration steps |

## Support

For issues or questions:

1. Check the troubleshooting section above
2. Review STUDIO_MIXER_GUIDE.md for complete API docs
3. See STUDIO_MIXER_EXAMPLES.php for working code samples
4. Check ElevenLabs documentation: https://elevenlabs.io/docs/api-reference/studio/create-podcast
