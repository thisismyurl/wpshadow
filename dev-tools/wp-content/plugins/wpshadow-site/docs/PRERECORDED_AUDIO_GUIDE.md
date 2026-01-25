# Split & Mix Pre-Recorded Podcast Audio

Your podcast has been analyzed with these segments:

- **Intro:** 0:00 - 0:32 (32 seconds)
- **Main:** 0:32 - 1:56 (84 seconds)  
- **Outro:** 1:56 - 2:30 (34 seconds)

## Splitting the Audio

Two files have been created to help split your podcast:

1. **`includes/audio-splitter.php`** - Utility to split audio
2. **`includes/podcast-prerecorded-mixer.php`** - Mix pre-recorded segments

### Option 1: Using WP-CLI (Recommended)

```bash
wp wpshadow split-podcast
```

This will:
- Create `intro.mp3` (32 sec)
- Create `main.mp3` (84 sec)
- Create `outro.mp3` (34 sec)

All in: `/wp-content/plugins/wpshadow-site/assets/audio/`

### Option 2: Custom Timings

If your segments are different:

```bash
wp wpshadow split-podcast \
  --source=/path/to/podcast.mp3 \
  --intro-end=32 \
  --main-end=116 \
  --outro-end=150
```

### Option 3: PHP Code

```php
require_once( WP_CONTENT_DIR . '/plugins/wpshadow-site/includes/podcast-prerecorded-mixer.php' );

$segments = array(
    'intro' => array( 'start' => 0,   'end' => 32 ),    // 0:00 - 0:32
    'main'  => array( 'start' => 32,  'end' => 116 ),   // 0:32 - 1:56
    'outro' => array( 'start' => 116, 'end' => 150 ),   // 1:56 - 2:30
);

$result = wpshadow_split_podcast_audio(
    WP_CONTENT_DIR . '/plugins/wpshadow-site/assets/audio/podcast.mp3',
    $segments
);

if ( is_wp_error( $result ) ) {
    echo 'Error: ' . $result->get_error_message();
} else {
    echo 'Segments created successfully!';
}
```

## Mixing Pre-Recorded Segments

Once you have the three segments (intro.mp3, main.mp3, outro.mp3), you can mix them:

```php
require_once( WP_CONTENT_DIR . '/plugins/wpshadow-site/includes/podcast-prerecorded-mixer.php' );

$audio_dir = WP_CONTENT_DIR . '/plugins/wpshadow-site/assets/audio';

$result = wpshadow_mix_prerecorded_podcast(
    $audio_dir . '/intro.mp3',
    $audio_dir . '/main.mp3',
    $audio_dir . '/outro.mp3'
);

if ( is_wp_error( $result ) ) {
    echo 'Error: ' . $result->get_error_message();
} else {
    echo 'Final podcast: ' . $result['podcast_file'];
    // Upload to media library or use directly
}
```

## Integration with Studio Mixer

You can also use the pre-recorded main segment with the Studio Mixer for custom intros/outros:

```php
$mixer = new WPShadow_Podcast_Studio_Mixer();

// Use pre-recorded intro and outro, custom main
$result = $mixer->generate_professional_podcast( array(
    'speaker1_voice_id' => 'VOICE_ID_1',
    'speaker2_voice_id' => 'VOICE_ID_2',
    
    // Use your pre-recorded intro
    'intro_config' => array(
        'narration'  => '', // Not used - using pre-recorded
        'music_file' => 'assets/audio/intro.mp3', // Your pre-recorded intro
    ),
    
    // Or use pre-recorded main
    'episode_config' => array(
        'title'            => 'Episode Title',
        'description'      => 'Description',
        'content'          => '[SPEAKER 1]: ... [SPEAKER 2]: ...',
        'background_music' => 'assets/audio/main.mp3', // Pre-recorded main
    ),
    
    // Use pre-recorded outro
    'outro_config' => array(
        'narration'       => '', // Not used
        'sponsor_mention' => '',
        'music_file'      => 'assets/audio/outro.mp3', // Pre-recorded outro
    ),
) );
```

## Workflow

### Step 1: Split Audio (One Time)
```bash
wp wpshadow split-podcast
# Creates intro.mp3, main.mp3, outro.mp3
```

### Step 2: Verify Segments
```bash
ls -lh /wp-content/plugins/wpshadow-site/assets/audio/
# Should see: intro.mp3, main.mp3, outro.mp3
```

### Step 3: Mix into Final Podcast
```php
$result = wpshadow_mix_prerecorded_podcast(
    $intro_file,
    $main_file,
    $outro_file
);

echo $result['podcast_file']; // Ready to use!
```

### Step 4: Upload to Media Library (Optional)
```php
$attachment_id = media_handle_sideload(
    array(
        'name'     => 'podcast.mp3',
        'tmp_name' => $result['podcast_file'],
    ),
    0 // Parent post ID
);

update_post_meta( $post_id, '_podcast_id', $attachment_id );
```

## Time Estimates

| Task | Time |
|------|------|
| Split audio (WP-CLI) | 5-10 seconds |
| Mix segments (FFmpeg) | 2-5 seconds |
| Upload to media library | 2-10 seconds |
| **Total** | **~10-25 seconds** |

## Requirements

✅ FFmpeg installed on server  
✅ `wp-cli` installed (if using WP-CLI method)  
✅ Write permissions to `/wp-content/plugins/wpshadow-site/assets/audio/`  
✅ Write permissions to `/wp-content/uploads/wpshadow-podcast-audio/`  

## Troubleshooting

### "FFmpeg not found"
```bash
apt-get install ffmpeg  # Ubuntu/Debian
brew install ffmpeg     # macOS
apk add ffmpeg          # Alpine
```

### "Source file not found"
Verify the path:
```bash
ls -lh /wp-content/plugins/wpshadow-site/assets/audio/podcast.mp3
```

### "Permission denied"
Set write permissions:
```bash
chmod 755 /wp-content/plugins/wpshadow-site/assets/audio/
chmod 755 /wp-content/uploads/wpshadow-podcast-audio/
```

## What's Next?

1. **Split audio:** `wp wpshadow split-podcast`
2. **Verify segments:** Check they were created
3. **Mix podcast:** Use `wpshadow_mix_prerecorded_podcast()`
4. **Upload:** Add to media library
5. **Done!** 🎉

---

See STUDIO_MIXER_GUIDE.md for more information about the full podcast generation system.
