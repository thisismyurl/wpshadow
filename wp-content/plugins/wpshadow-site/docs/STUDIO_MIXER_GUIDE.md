# Professional Two-Person Podcast Generator

Complete solution for creating professional two-person podcasts with ElevenLabs, featuring:

✅ **Intro with Music Ducking** - Music starts loud, ducks (reduces volume) during host introduction  
✅ **Two-Person Conversation** - Powered by ElevenLabs Studio API  
✅ **Background Music Loop** - Music plays softly under entire conversation  
✅ **Outro with Sponsor Mention** - Host thanks audience, mentions sponsor, asks for shares  
✅ **Music Fade Out** - Music fades back in and loops at the end  

## Architecture

```
Configuration
    ↓
[1] Generate Intro (Music Ducking)
    ├─ Music file starts at full volume
    ├─ Music volume reduces (duck) at ~0.5s
    ├─ Host narration plays at ~1s
    └─ Output: intro_mixed_*.mp3
    ↓
[2] Generate Two-Person Podcast (Studio API)
    ├─ Send script with [SPEAKER 1] and [SPEAKER 2] labels
    ├─ ElevenLabs handles voice separation
    ├─ Add looping background music (25% volume)
    └─ Output: studio_podcast_*.mp3 + background
    ↓
[3] Generate Outro (Music Fade)
    ├─ Host narration plays
    ├─ Music fades in over last 2 seconds of narration
    ├─ Music loops for ~2-3 seconds after
    └─ Output: outro_mixed_*.mp3
    ↓
[4] Final Mix (Concatenate All)
    ├─ Stitch intro → podcast → outro
    ├─ Seamless transitions via FFmpeg
    └─ Output: final_podcast_*.mp3 ✅
```

## Class: WPShadow_Podcast_Studio_Mixer

### Initialization

```php
// Initialize with API key
$mixer = new WPShadow_Podcast_Studio_Mixer( 'your-elevenlabs-api-key' );

// Or use key from WordPress options (recommended)
$mixer = new WPShadow_Podcast_Studio_Mixer();
// Key auto-loaded from: get_option( 'wpshadow_elevenlabs_api_key' )
```

### Main Method: generate_professional_podcast()

Generates a complete professional podcast with all segments.

#### Parameters

```php
$config = array(
    // Required: Voice IDs
    'speaker1_voice_id' => 'voice_id_for_host',
    'speaker2_voice_id' => 'voice_id_for_guest',
    
    // Required: Intro configuration
    'intro_config' => array(
        'narration'   => 'Welcome to episode 42! Today we\'re discussing...',
        'music_file'  => '/path/to/intro-music.mp3', // File path or attachment ID
    ),
    
    // Required: Episode configuration
    'episode_config' => array(
        'title'              => 'Episode 42: Advanced Topics',
        'description'        => 'A deep dive into podcast production',
        'content'            => '[SPEAKER 1]: First topic... [SPEAKER 2]: Great point...',
        'background_music'   => '/path/to/bg-music.mp3', // Optional
    ),
    
    // Required: Outro configuration
    'outro_config' => array(
        'narration'          => 'Thanks for tuning in today!',
        'sponsor_mention'    => 'This episode is brought to you by Sponsor Inc.',
        'cta'                => 'Please like, share, and subscribe for more!',
        'music_file'         => '/path/to/outro-music.mp3',
    ),
    
    // Optional
    'post_id' => 123, // For naming/tracking
);

// Generate podcast
$result = $mixer->generate_professional_podcast( $config );

if ( is_wp_error( $result ) ) {
    echo 'Error: ' . $result->get_error_message();
} else {
    echo 'Podcast created: ' . $result['podcast_file'];
    // Also contains $result['segments'] with individual files
}
```

#### Return Value

```php
array(
    'podcast_file' => '/path/to/final_podcast_123_abcd1234.mp3',
    'segments'     => array(
        'intro'   => '/path/to/intro_mixed_*.mp3',
        'episode' => '/path/to/studio_podcast_*.mp3',
        'outro'   => '/path/to/outro_mixed_*.mp3',
    ),
)
```

## Usage Examples

### Example 1: Basic Two-Person Podcast

```php
$mixer = new WPShadow_Podcast_Studio_Mixer();

$config = array(
    'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM', // Host voice
    'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL', // Guest voice
    
    'intro_config' => array(
        'narration'   => 'Welcome to Tech Talk Daily, episode 42. Today we\'re discussing AI trends with our guest expert.',
        'music_file'  => 'theme-music.mp3',
    ),
    
    'episode_config' => array(
        'title'       => 'AI Trends 2026',
        'description' => 'A conversation about the latest AI developments',
        'content'     => '[SPEAKER 1]: So tell us, what excites you most about AI? ' .
                        '[SPEAKER 2]: Great question. I think transformer models are...',
        'background_music' => 'ambient-music.mp3',
    ),
    
    'outro_config' => array(
        'narration'       => 'That was fantastic discussion!',
        'sponsor_mention' => 'This episode is brought to you by CloudServices Pro.',
        'cta'             => 'Subscribe and give us a five-star review!',
        'music_file'      => 'theme-music.mp3',
    ),
    
    'post_id' => 123,
);

$result = $mixer->generate_professional_podcast( $config );
```

### Example 2: Using Media Library Attachments

```php
// Assuming you've uploaded audio files to WordPress media library
$intro_music_id = 456;    // Attachment ID
$bg_music_id    = 457;
$outro_music_id = 458;

$config = array(
    'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
    'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',
    
    'intro_config' => array(
        'narration'   => 'Welcome to the podcast!',
        'music_file'  => $intro_music_id, // Use attachment ID directly
    ),
    
    'episode_config' => array(
        'title'            => 'Episode Title',
        'description'      => 'Description here',
        'content'          => '...',
        'background_music' => $bg_music_id, // Use attachment ID
    ),
    
    'outro_config' => array(
        'narration'       => 'Thanks for listening!',
        'sponsor_mention' => 'Sponsor: Company X',
        'music_file'      => $outro_music_id,
    ),
);

$result = $mixer->generate_professional_podcast( $config );
```

### Example 3: Integrating with Existing Podcast Generator

```php
// Extend the existing WPShadow_Podcast_Generator
class WPShadow_Podcast_Generator_Enhanced extends WPShadow_Podcast_Generator {
    
    /**
     * Override generate_podcast to use Studio Mixer.
     */
    protected function generate_podcast( $post_id ) {
        $post = get_post( $post_id );
        $settings = $this->get_podcast_settings();
        
        // Build mixer configuration from post and settings.
        $mixer = new WPShadow_Podcast_Studio_Mixer();
        
        $config = array(
            'speaker1_voice_id'  => $settings['title_voice_id'],
            'speaker2_voice_id'  => $settings['content_voice_id'],
            
            'intro_config' => array(
                'narration'   => sprintf(
                    'Welcome to episode about %s',
                    $post->post_title
                ),
                'music_file'  => $settings['intro_audio_id'],
            ),
            
            'episode_config' => array(
                'title'            => $post->post_title,
                'description'      => wp_strip_all_tags( substr( $post->post_content, 0, 200 ) ),
                'content'          => $this->prepare_content( $post->post_content ),
                'background_music' => $settings['background_music_id'] ?? null,
            ),
            
            'outro_config' => array(
                'narration'       => 'Thank you for listening to our podcast!',
                'sponsor_mention' => 'Thanks to our sponsor, ' . get_bloginfo( 'name' ),
                'cta'             => 'Please subscribe and share with your friends!',
                'music_file'      => $settings['outro_audio_id'],
            ),
            
            'post_id' => $post_id,
        );
        
        $result = $mixer->generate_professional_podcast( $config );
        
        if ( is_wp_error( $result ) ) {
            return $result;
        }
        
        // Upload to media library (existing method).
        return $this->upload_podcast_to_media_library(
            $result['podcast_file'],
            $post_id,
            $post->post_title
        );
    }
}
```

### Example 4: Custom Content Script with Multiple Speakers

```php
// Build a structured conversation script.
$podcast_content = '[SPEAKER 1]: Welcome back to the show! Today we have Dr. Smith with us.
[SPEAKER 2]: Thanks for having me! It\'s great to be here.
[SPEAKER 1]: Let\'s jump in. What\'s your take on the latest developments?
[SPEAKER 2]: Well, I think there are three key points...
[SPEAKER 1]: That\'s interesting. Can you elaborate on the first one?
[SPEAKER 2]: Of course! Essentially...';

$config = array(
    'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
    'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',
    
    'intro_config' => array(
        'narration'   => 'Welcome to episode 10 of Deep Discussions.',
        'music_file'  => 'intro.mp3',
    ),
    
    'episode_config' => array(
        'title'            => 'Deep Discussions: Episode 10',
        'description'      => 'In this episode, we discuss recent developments.',
        'content'          => $podcast_content,
        'background_music' => 'ambient.mp3',
    ),
    
    'outro_config' => array(
        'narration'       => 'That was a great discussion with Dr. Smith!',
        'sponsor_mention' => 'Special thanks to TechCorp for sponsoring this episode.',
        'cta'             => 'Share this episode with someone interested in the topic!',
        'music_file'      => 'outro.mp3',
    ),
);

$mixer = new WPShadow_Podcast_Studio_Mixer();
$result = $mixer->generate_professional_podcast( $config );
```

## Audio Mixing Details

### Intro Segment

**Pattern:**
1. Music fades in over 0.5 seconds (full volume)
2. Music begins ducking (volume reduction) at ~0.5 seconds
3. Narration starts at ~1 second (music at ~30% volume)
4. Narration plays while music is ducked
5. Music fades back to full volume as narration ends

**FFmpeg Filter:**
```
afade=t=in:st=0:d=0.5 (music fade in)
adelay=500ms (narration delayed to let music start)
amix=inputs=2 (combine music + narration)
```

### Main Podcast (With Background Music)

**Pattern:**
1. Two speakers talk (generated by Studio API)
2. Background music loops continuously underneath
3. Music volume set to 25% (podcasts are at 95%)
4. Creates professional "radio show" effect

**FFmpeg Filter:**
```
aloop=loop=-1 (loop music infinitely)
amix=inputs=2:duration=first (mix until podcast ends)
volume=0.25 (background at 25%)
```

### Outro Segment

**Pattern:**
1. Host narration plays at full volume
2. ~1 second before narration ends, music fades in
3. Narration fades out while music fades in
4. Music continues for 2-3 seconds after narration
5. Creates smooth transition out of podcast

**FFmpeg Filter:**
```
afade=t=out:st=(narration_duration-1):d=1.0 (narration fade)
afade=t=in:st=(narration_duration-1):d=1.0 (music fade in)
amix=inputs=2 (combine)
```

## Requirements

✅ **FFmpeg** - For audio mixing and ducking  
✅ **ElevenLabs API Key** - For text-to-speech synthesis  
✅ **Voice IDs** - Two distinct voice IDs from ElevenLabs  
✅ **Audio Files** - Intro/outro music files (WAV, MP3, etc.)  

### Install FFmpeg

**Ubuntu/Debian:**
```bash
sudo apt-get install ffmpeg
```

**macOS:**
```bash
brew install ffmpeg
```

**Docker:**
```dockerfile
RUN apk add --no-cache ffmpeg
```

## Configuration

### WordPress Admin

Store your ElevenLabs API key:
```php
update_option( 'wpshadow_elevenlabs_api_key', 'your-api-key' );
```

Store voice IDs in plugin settings:
```php
update_option( 'wpshadow_podcast_settings', array(
    'title_voice_id'     => '21m00Tcm4TlvDq8ikWAM',
    'content_voice_id'   => 'EXAVITQu4vr4xnSDxMaL',
    'background_music_id' => 456, // Attachment ID
) );
```

## Troubleshooting

### "FFmpeg is required for audio mixing"

**Solution:** Install FFmpeg on your server.

```bash
which ffmpeg  # Check if installed
ffmpeg -version  # Verify working
```

### "ElevenLabs API key not configured"

**Solution:** Set the API key in WordPress:

```php
update_option( 'wpshadow_elevenlabs_api_key', 'your-key-here' );
```

### "Audio file not found"

**Solution:** Ensure file paths are correct or use attachment IDs.

```php
// This works with both:
'music_file' => 456,                      // Attachment ID
'music_file' => '/path/to/music.mp3',    // Full path
'music_file' => 'uploads/music.mp3',     // Relative to upload dir
```

### "Failed to parse Studio API response"

**Solution:** Verify your ElevenLabs account has access to Studio API.

Visit: https://elevenlabs.io/studio

### Audio quality issues

**Solutions:**
- Use higher quality source audio (192kbps+)
- Adjust FFmpeg quality: Change `-q:a 2` to `-q:a 0` (0 = highest)
- Check volume levels: Ensure intro music isn't clipping

## API Reference

### WPShadow_Podcast_Studio_Mixer Methods

#### Public Methods

| Method | Purpose |
|--------|---------|
| `__construct( $api_key = '' )` | Initialize mixer |
| `generate_professional_podcast( $config )` | Main method - generates full podcast |

#### Private Methods (For Extension)

| Method | Purpose |
|--------|---------|
| `generate_intro_segment( $config )` | Create intro with music ducking |
| `generate_studio_podcast( $config )` | Call Studio API |
| `generate_outro_segment( $config )` | Create outro with music fade |
| `mix_intro_audio( $music, $narration, $id )` | Mix intro audio |
| `add_background_music( $podcast, $music, $id )` | Add looping background |
| `mix_outro_audio( $narration, $music, $id )` | Mix outro audio |
| `mix_podcast_segments( $segments, $config )` | Final stitch |
| `synthesize_audio( $text, $voice_id )` | Text-to-speech |
| `get_audio_duration( $file )` | Get file length in seconds |

## Performance Notes

- **Intro generation:** ~5-15 seconds (TTS + mixing)
- **Main podcast:** Depends on script length (ElevenLabs API time)
- **Outro generation:** ~5-15 seconds (TTS + mixing)
- **Final mix:** ~2-5 seconds (FFmpeg concatenation)

**Total time:** ~30 seconds to 2+ minutes depending on podcast length

## File Storage

All generated files stored in:
```
/wp-content/uploads/wpshadow-podcast-audio/
```

Includes:
- Temporary TTS files (cleaned up after use)
- Intro/outro mixed files
- Main podcast with background music
- Final podcast file
- Intermediate concat files (cleaned up)

## License

This component is part of WPShadow Site plugin.
