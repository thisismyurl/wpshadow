# Professional Two-Person Podcast Production with ElevenLabs

Complete guide for creating professional podcasts with background music, intro/outro narration, and automatic two-person conversation.

## Overview

This system extends the WPShadow ElevenLabs integration to create production-grade podcasts with:

✅ **Intro Sequence**
- Intro music plays at full volume (2-3 seconds)
- Host introduces episode number and subject
- Music ducks (volume reduces) while host speaks

✅ **Main Content**
- Two-person conversation generated from KB article content
- Background music loops underneath at reduced volume
- Natural conversational flow between speakers

✅ **Outro Sequence**
- Host thanks audience for listening
- Sponsor thank you and mention
- Call-to-action (like, share, subscribe)
- Music ducks during speech, then fades out
- Outro music plays and fades (2-3 seconds)

## How It Works

### Audio Processing Pipeline

```
ElevenLabs Text-to-Speech Synthesis
    ↓
Intro Music (full volume) ──────────────┐
Intro Narration (host) ────────────────┤
                                      ├──→ FFmpeg Professional Mix
Main Podcast Audio ────────┐          │
Background Music Loop ────┤──────────┤
                          ↓          │
Outro Music (full volume) ────────────┤
Outro Narration (host) ────────────────┤
                                      ↓
                           Final Podcast MP3
```

### Key Components

#### 1. Audio Mixer Class (`class-podcast-audio-mixer.php`)
Handles sophisticated FFmpeg audio mixing with:
- Volume ducking (reducing music under narration)
- Audio looping (background music repeats to match content length)
- Precise timing and sequencing
- Fade in/out effects

**Key Methods:**
- `mix_professional_podcast()` - Main mixing function
- `build_filter_graph()` - Creates complex FFmpeg filter chain
- `get_audio_duration()` - Calculates audio file lengths

#### 2. Professional Generator Class (`class-professional-podcast-generator.php`)
Orchestrates the entire podcast generation:
- Generates intro narration (episode number + subject)
- Splits article content for two-person conversation
- Generates main content with alternating speakers
- Generates outro narration (thanks, sponsor, CTA)
- Coordinates all audio mixing via the mixer class

**Key Methods:**
- `generate_professional_podcast()` - Entry point
- `generate_intro_narration()` - Creates intro audio
- `generate_two_person_podcast()` - Creates main content
- `generate_outro_narration()` - Creates outro audio
- `split_content_for_speakers()` - Divides content between speakers

## Setup & Configuration

### 1. Prerequisites

- **FFmpeg** installed on server (required for audio mixing)
  ```bash
  # Ubuntu/Debian
  sudo apt-get install ffmpeg
  
  # CentOS/RHEL
  sudo yum install ffmpeg
  
  # macOS
  brew install ffmpeg
  ```

- **ElevenLabs API Key** (for text-to-speech)
- **Multiple Voice IDs** from ElevenLabs:
  - Host voice (intro/outro)
  - Speaker 1 voice (main content)
  - Speaker 2 voice (main content)

### 2. Configure in WordPress Admin

Navigate to: **WPShadow Site → Professional Podcast Settings**

**Required Fields:**
- Host Voice ID - for intro/outro narration
- Speaker 1 Voice ID - for first speaker in main content
- Speaker 2 Voice ID - for second speaker in main content
- Intro Music - upload or select from media library
- Background Music - loops under main content
- Outro Music - plays during/after outro

**Optional Settings:**
- Sponsor Name - mentioned in outro (default: "our sponsors")
- Music Ducking Level - volume reduction during narration (0.0-1.0, default: 0.3)
- Background Music Level - volume reduction under content (0.0-1.0, default: 0.2)

### 3. Audio File Recommendations

**Intro Music**
- Duration: 2-3 seconds
- Format: MP3, WAV, or OGG
- Loudness: -3 to -6 dB (leaves room for ducking)
- Content: Energetic, professional intro theme

**Background Music**
- Duration: 30-60 seconds (will loop)
- Format: MP3, WAV, or OGG
- Loudness: -12 to -15 dB (subtle background)
- Content: Instrumental, non-intrusive

**Outro Music**
- Duration: 2-3 seconds
- Format: MP3, WAV, or OGG
- Loudness: -3 to -6 dB
- Content: Fade-out outro or closing theme

## Usage

### Programmatic Generation

```php
<?php
// Load the professional generator.
require_once( dirname( __FILE__ ) . '/includes/class-professional-podcast-generator.php' );

$generator = new WPShadow_Professional_Podcast_Generator();

// Generate podcast for a KB article.
$result = $generator->generate_professional_podcast(
    $post_id,  // Your KB article post ID
    array(
        'episode_number'         => 42,
        'host_voice_id'          => 'voice_abc123',  // Optional - uses default if not provided
        'speaker_1_voice_id'     => 'voice_def456',  // Optional
        'speaker_2_voice_id'     => 'voice_ghi789',  // Optional
        'sponsor_name'           => 'TechCorp Inc',  // Optional
        'music_ducking_level'    => 0.3,             // Optional
        'background_music_level' => 0.2,             // Optional
    )
);

if ( is_wp_error( $result ) ) {
    echo 'Error: ' . $result->get_error_message();
} else {
    echo 'Podcast generated! Attachment ID: ' . $result['podcast_id'];
}
?>
```

### Via WordPress Cron (Automatic)

Add to your plugin initialization:

```php
<?php
// Hook into KB article publication.
add_action( 'publish_kb_article', function( $post_id ) {
    require_once( dirname( __FILE__ ) . '/includes/class-professional-podcast-generator.php' );
    
    $generator = new WPShadow_Professional_Podcast_Generator();
    $result = $generator->generate_professional_podcast( $post_id );
    
    if ( is_wp_error( $result ) ) {
        error_log( 'Podcast generation failed: ' . $result->get_error_message() );
    }
}, 10, 1 );
?>
```

### Via WordPress CLI

```bash
wp eval-file - <<'PHP'
<?php
require_once( '/path/to/class-professional-podcast-generator.php' );

$generator = new WPShadow_Professional_Podcast_Generator();
$result = $generator->generate_professional_podcast( 123 );

if ( is_wp_error( $result ) ) {
    WP_CLI::error( $result->get_error_message() );
} else {
    WP_CLI::success( 'Podcast generated: ' . $result['podcast_id'] );
}
?>
PHP
```

## Audio Mixing Details

### Music Ducking

**What it does:** Automatically reduces background music volume when the host is narrating.

**When it occurs:**
1. During intro narration (host introduces episode)
2. During outro narration (host thanks audience)

**How to adjust:**
- `music_ducking_level`: 0.0 (silent) to 1.0 (full volume)
- Default: 0.3 (music plays at 30% volume)
- Recommended: 0.2-0.4 for clear narration

### Background Music Loop

**What it does:** Loops background music to match the main content length.

**How it works:**
1. Measures main podcast duration
2. Repeats background music to fill entire duration
3. Applies volume reduction (20-30% by default)
4. Overlays main podcast audio on top

**How to adjust:**
- `background_music_level`: 0.0 (silent) to 1.0 (full volume)
- Default: 0.2 (music plays at 20% volume)
- Recommended: 0.15-0.25 for professional sound

### Audio Fade Effects

**Intro Music**
- Fade in: 0.5 seconds (from start)
- Plays at full volume during intro narration
- Fades out over 0.5 seconds when main content starts

**Outro Music**
- Starts after outro narration ends
- Fades out over 2 seconds
- Total duration: 2-3 seconds

## Output Structure

Final podcast audio sequence:

```
[0.0s]  Intro music starts (fade in 0.5s)
[2.5s]  Host intro: "Welcome to the podcast, episode 42..."
[7.5s]  Intro music fades out, main content begins
[7.5s]  Speaker 1: "Let's discuss today's topic..."
[45.0s] Speaker 2: "That's a great point..."
[60.0s] Background music fades, outro narration starts
[60.0s] Host: "Thanks for listening, check out TechCorp..."
[75.0s] Outro music starts, music fades out
[77.5s] End of podcast
```

## Troubleshooting

### FFmpeg Not Found

**Error:** "FFmpeg is required for professional podcast mixing"

**Solution:**
```bash
# Check if installed
which ffmpeg

# Install on Ubuntu/Debian
sudo apt-get install ffmpeg

# Verify installation
ffmpeg -version
```

### Audio Sync Issues

**Problem:** Music doesn't properly loop or segments don't sync

**Solution:**
1. Ensure all audio files are in MP3 or WAV format
2. Check file durations: `ffmpeg -i yourfile.mp3`
3. Test with smaller content first

### Speaker Balance Uneven

**Problem:** One speaker much louder/quieter than the other

**Solution:**
1. Different voice IDs may have different loudness
2. Normalize audio files: 
   ```bash
   ffmpeg-normalize input.mp3 -o output.mp3
   ```
3. Or adjust voice settings in ElevenLabs

### Memory Issues with Long Content

**Problem:** Generation fails on long articles

**Solution:**
1. Default limit is 5000 words - increase if needed:
   ```php
   add_filter( 'wpshadow_podcast_max_words', function() {
       return 10000; // Allow up to 10,000 words
   });
   ```
2. Or split long articles into multiple episodes

## Advanced Examples

### Custom Audio Levels Per Episode

```php
<?php
$generator = new WPShadow_Professional_Podcast_Generator();

// Quiet main content, subtle background music
$result = $generator->generate_professional_podcast(
    $post_id,
    array(
        'music_ducking_level'    => 0.25,  // Quieter ducking
        'background_music_level' => 0.15,  // Very subtle background
    )
);
?>
```

### Different Voices Per Episode

```php
<?php
$generator = new WPShadow_Professional_Podcast_Generator();

// Use different voice combinations
$result = $generator->generate_professional_podcast(
    $post_id,
    array(
        'host_voice_id'      => 'voice_authority_male',
        'speaker_1_voice_id' => 'voice_casual_female',
        'speaker_2_voice_id' => 'voice_technical_male',
    )
);
?>
```

### Automatic Episode Numbering

```php
<?php
// Get episode count
$args = array(
    'post_type'   => 'kb_article',
    'post_status' => 'publish',
    'numberposts' => 1,
    'orderby'     => 'date',
    'order'       => 'DESC',
);
$latest = get_posts( $args );
$episode_number = count( $latest ) + 1;

$generator = new WPShadow_Professional_Podcast_Generator();
$result = $generator->generate_professional_podcast(
    $post_id,
    array( 'episode_number' => $episode_number )
);
?>
```

## Performance Notes

- **Generation time:** 2-5 minutes per episode (depends on content length)
- **File size:** ~3-4 MB for 30-minute episode
- **Server CPU:** Minimal - FFmpeg handles most processing
- **Recommended:** Run via async queue/cron job, not on page load

## Next Steps

1. Upload intro/background/outro music files to media library
2. Configure voice IDs and music selections in settings
3. Test with a short KB article first
4. Monitor the admin notice for podcast generation status
5. Scale up to automatic generation for all new articles

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review FFmpeg installation for your OS
3. Verify audio file formats and durations
4. Check WordPress error logs: `/wp-content/debug.log`
