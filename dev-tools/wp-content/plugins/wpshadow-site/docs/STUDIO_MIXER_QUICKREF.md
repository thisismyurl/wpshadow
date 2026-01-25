# Studio Mixer Quick Reference

Professional two-person podcast production with ElevenLabs.

## Setup Checklist

- [ ] FFmpeg installed on server (`apt install ffmpeg`)
- [ ] ElevenLabs API key obtained
- [ ] Voice IDs selected (2 different voices)
- [ ] Audio files prepared (intro, bg, outro music)
- [ ] Class included in plugin: `require_once 'includes/class-podcast-studio-mixer.php'`

## Basic Usage

```php
$mixer = new WPShadow_Podcast_Studio_Mixer();

$result = $mixer->generate_professional_podcast( array(
    'speaker1_voice_id' => 'VOICE_ID_1',
    'speaker2_voice_id' => 'VOICE_ID_2',
    
    'intro_config' => array(
        'narration'  => 'Welcome to the show!',
        'music_file' => 'intro.mp3',
    ),
    
    'episode_config' => array(
        'title'            => 'Episode Title',
        'description'      => 'Episode description',
        'content'          => '[SPEAKER 1]: ... [SPEAKER 2]: ...',
        'background_music' => 'bg.mp3',
    ),
    
    'outro_config' => array(
        'narration'       => 'Thanks for listening!',
        'sponsor_mention' => 'Brought to you by Sponsor',
        'music_file'      => 'outro.mp3',
    ),
) );

if ( ! is_wp_error( $result ) ) {
    echo $result['podcast_file'];
}
```

## Production Flow

```
Input Config
    ↓
Generate Intro (Music → Duck → Narration → Music Up)
    ↓
Generate Main Podcast (Studio API → Background Music Loop)
    ↓
Generate Outro (Narration → Music Fade In)
    ↓
Mix All Segments
    ↓
Output: final_podcast_*.mp3 ✅
```

## Audio Mixing Timings

### Intro (15-30 seconds)
- Music: 0.0s - Full
- Music: 0.5s - Duck begins (-70dB)
- Narration: 1.0s - Starts
- Music: 25-30% volume during narration
- Music: Fades back up at end

### Main Podcast (Variable)
- Podcast: Speakers at 95% volume
- Background: Music at 25% volume (loops)

### Outro (15-30 seconds)
- Narration: Plays at full volume
- Music: Fades in at last 2 seconds
- Music: Continues 2-3 seconds after

## File Paths

Use any of these formats:

```php
'music_file' => 456,                    // Attachment ID
'music_file' => 'music.mp3',            // Relative to uploads/
'music_file' => '/uploads/music.mp3',   // Full path
```

## Error Handling

```php
$result = $mixer->generate_professional_podcast( $config );

if ( is_wp_error( $result ) ) {
    $error = $result->get_error_message();
    error_log( $error );
    // Handle error
}
```

## Common Errors & Fixes

| Error | Cause | Fix |
|-------|-------|-----|
| FFmpeg missing | Not installed | `apt install ffmpeg` |
| API key missing | Not configured | `update_option( 'wpshadow_elevenlabs_api_key', 'KEY' )` |
| Audio not found | Wrong path | Use attachment ID or verify path exists |
| Studio API timeout | Large script | Reduce content length |
| Mixing failed | FFmpeg issue | Check FFmpeg has libmp3lame |

## Getting ElevenLabs API Key

1. Visit https://elevenlabs.io
2. Create account
3. Go to Settings → API Keys
4. Copy your API key
5. Paste into WordPress settings

## Getting Voice IDs

1. Visit https://elevenlabs.io/studio
2. Browse available voices
3. Copy voice ID (e.g., `21m00Tcm4TlvDq8ikWAM`)
4. Enter in config

## Configuration Options

### Required

```php
'speaker1_voice_id'           // Voice for speaker 1
'speaker2_voice_id'           // Voice for speaker 2
'intro_config' => [
    'narration'               // Text to speak in intro
    'music_file'              // Intro music file
]
'episode_config' => [
    'title'                   // Episode title
    'content'                 // Main podcast content
    'description'             // Episode description (optional)
    'background_music'        // Music to loop under podcast (optional)
]
'outro_config' => [
    'narration'               // Outro text
    'music_file'              // Outro music file
    'sponsor_mention'         // Optional sponsor mention
    'cta'                     // Optional call to action
]
```

### Optional

```php
'post_id' => 123,             // For naming/tracking
```

## Content Format

### Simple (Single Narration)

```php
'content' => 'Here is the episode content. This will be read by the system.'
```

→ Gets converted to: `[SPEAKER 1]: Here is the episode content...`

### Formatted (Multiple Speakers)

```php
'content' => '[SPEAKER 1]: This is the first speaker.
[SPEAKER 2]: And this is the second speaker.'
```

→ Used as-is by Studio API

## Example: Simple Podcast

```php
$mixer = new WPShadow_Podcast_Studio_Mixer();

$result = $mixer->generate_professional_podcast( array(
    'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
    'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',
    
    'intro_config' => array(
        'narration'  => 'Welcome to episode 42',
        'music_file' => 'theme.mp3',
    ),
    
    'episode_config' => array(
        'title'            => 'My Episode',
        'description'      => 'A great episode',
        'content'          => 'This is interesting content for the podcast.',
        'background_music' => 'ambient.mp3',
    ),
    
    'outro_config' => array(
        'narration'       => 'Thanks for listening!',
        'sponsor_mention' => 'Sponsored by XYZ',
        'music_file'      => 'theme.mp3',
    ),
) );
```

## Return Value

On success:
```php
array(
    'podcast_file' => '/path/to/final_podcast.mp3',
    'segments' => array(
        'intro'   => '/path/to/intro.mp3',
        'episode' => '/path/to/episode.mp3',
        'outro'   => '/path/to/outro.mp3',
    ),
)
```

On error:
```php
WP_Error with message and code
```

## Uploading to Media Library

```php
$attachment_id = media_handle_sideload(
    array(
        'name'     => 'podcast.mp3',
        'tmp_name' => $result['podcast_file'],
    ),
    0 // Parent post ID
);

update_post_meta( $post_id, '_podcast_attachment_id', $attachment_id );
```

## Testing

```bash
# Test FFmpeg
ffmpeg -version

# Test API key (via PHP)
$mixer = new WPShadow_Podcast_Studio_Mixer();
// If no error, API key is working
```

## Performance

| Task | Time |
|------|------|
| Intro generation | 5-15 sec |
| Studio API call | 30 sec - 2 min |
| Outro generation | 5-15 sec |
| Final mix | 2-5 sec |
| **Total** | **~1-3 minutes** |

## Files & Storage

Generated files stored in:
```
/wp-content/uploads/wpshadow-podcast-audio/
```

Temporary files cleaned up automatically.

## Debugging

Enable logging:
```php
// In your code, add error logging
if ( is_wp_error( $result ) ) {
    error_log( 'Podcast Error: ' . $result->get_error_message() );
    error_log( 'Error Code: ' . $result->get_error_code() );
}
```

Check FFmpeg output:
```php
// In class-podcast-studio-mixer.php, uncomment:
// error_log( 'FFmpeg output: ' . $result );
```

## Resources

- [Full Guide](./STUDIO_MIXER_GUIDE.md)
- [Code Examples](./STUDIO_MIXER_EXAMPLES.php)
- [Integration Steps](./STUDIO_MIXER_INTEGRATION.md)
- [ElevenLabs Docs](https://elevenlabs.io/docs/api-reference/studio/create-podcast)
