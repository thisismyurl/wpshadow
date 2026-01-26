# MLCampbell Podcast Script Generator with ElevenLabs Integration

A PHP-based podcast script generator that creates conversational, educational content with four distinct characters, and integrates with ElevenLabs for audio generation.

## Quick Start

### 1. Setup ElevenLabs API Key

```bash
# Get your API key from: https://elevenlabs.io/app/settings/api-keys

# Set environment variable
export ELEVENLABS_API_KEY="your-api-key-here"

# Or create a .env file in this directory
echo "ELEVENLABS_API_KEY=your-api-key-here" > .env
```

### 2. Generate a Podcast Script

```bash
php PodcastScriptGenerator.php
```

This creates a plain text script file: `episode-001-sandpaper-grits.txt`

### 3. Convert Script to Audio

```bash
php ElevenLabsIntegration.php
```

This will:
- Parse the script file
- Extract all character segments
- Generate individual MP3 files for each character
- Save audio files to `audio_segments/` directory

## Directory Structure

```
mlcampbell/
├── PodcastScriptGenerator.php      # Generate podcast scripts
├── ElevenLabsIntegration.php       # Convert scripts to audio
├── config.php                       # Configuration and settings
├── README.md                        # This file
├── episode-*.txt                    # Generated script files
└── audio_segments/                  # Generated audio files
    ├── 001_margaret.mp3
    ├── 002_david.mp3
    ├── 003_jordan.mp3
    ├── 004_expert.mp3
    └── ...
```

## The Four Characters

### 1. **Margaret Chen** - The Host
- **Age:** 52
- **Role:** Professional NPR/CBC style podcast host
- **Style:** Authoritative, well-spoken, instills confidence
- **Voice:** Rachel (warm, professional)
- **Task:** Guides conversations, provides structure

### 2. **Jordan Mills** - The Help
- **Age:** 28
- **Role:** Audience proxy
- **Style:** Idealistic, curious, energetic
- **Voice:** Bella (younger, friendly)
- **Task:** Asks questions listeners want answered

### 3. **David Rodriguez** - The Sidekick
- **Age:** 30
- **Role:** Co-researcher and reinforcer
- **Style:** Conversational, collaborative, knowledgeable
- **Voice:** Adam (natural, conversational)
- **Task:** Reinforces perspectives, adds secondary insights

### 4. **Dr. James Patterson** - The Expert
- **Age:** 50
- **Role:** Subject matter expert
- **Style:** Authoritative, mentor-like, deep knowledge
- **Voice:** Sam (authoritative, trusted)
- **Task:** Provides expert insights and deep dives

## Usage Examples

### Generate a Simple Script

```php
<?php
require 'PodcastScriptGenerator.php';

$generator = new PodcastScriptGenerator();
$script = $generator
    ->setEpisodeNumber('002')
    ->setTopic('Best Practices for Woodworking')
    ->generateCompleteScript();

// Display or save
$generator->displayScript($script);
```

### Convert Script to Audio (Basic)

```php
<?php
require 'ElevenLabsIntegration.php';

$api_key = getenv('ELEVENLABS_API_KEY');
$elevenlabs = new ElevenLabsIntegration($api_key);

// Read a script
$script = file_get_contents('episode-001-sandpaper-grits.txt');

// Generate audio
$audio_files = $elevenlabs->generateScriptAudio(
    $script,
    'audio_segments'
);

// $audio_files now contains paths to all character audio files
```

### Customize Character Voices

```php
<?php
require 'ElevenLabsIntegration.php';

$elevenlabs = new ElevenLabsIntegration($api_key);

// Get current voice settings
$margaret_settings = $elevenlabs->getCharacterVoiceSettings('margaret');

// Update voice settings
$elevenlabs->updateCharacterVoiceSettings('margaret', [
    'stability' => 0.6,
    'similarity_boost' => 0.8,
    'style' => 1.2,
]);

// Generate audio with new settings
$audio = $elevenlabs->generateCharacterAudio(
    'margaret',
    'This is a test with custom voice settings.'
);
```

### Generate Audio for Specific Character

```php
<?php
require 'ElevenLabsIntegration.php';

$elevenlabs = new ElevenLabsIntegration($api_key);

$text = "Understanding sandpaper grits is crucial for achieving professional finishes.";

$audio_file = $elevenlabs->generateCharacterAudio(
    'expert',
    $text,
    'output/expert_audio.mp3'
);

echo "Audio saved to: {$audio_file}\n";
```

## Configuration

Edit `config.php` to customize:

### API Settings
```php
'elevenlabs' => [
    'api_key' => getenv('ELEVENLABS_API_KEY'),
    'model' => 'eleven_turbo_v2_5',  // Model choice
    'timeout' => 60,
],
```

### Voice Settings per Character
```php
'characters' => [
    'margaret' => [
        'voice_id' => 'EXAVITQu4vr4xnSDxMaL',
        'voice_settings' => [
            'stability' => 0.5,           // 0-1: More stable = more consistent
            'similarity_boost' => 0.75,   // 0-1: More similarity = more like voice
            'style' => 1.0,               // 0-2: Emotion/energy level
            'use_speaker_boost' => true,
        ],
    ],
],
```

### Audio Output Settings
```php
'audio' => [
    'format' => 'mp3',
    'output_directory' => 'audio_segments',
    'quality' => 'high',
    'sample_rate' => 22050,
],
```

## Available ElevenLabs Models

- **eleven_turbo_v2_5** (Recommended)
  - Fastest generation
  - Lowest cost
  - Excellent quality
  - Suitable for most use cases

- **eleven_multilingual_v2**
  - Standard quality
  - Supports 29 languages
  - Good balance of speed and quality

- **eleven_multilingual_v1**
  - Higher quality
  - Slower generation
  - More expensive
  - Best for premium content

## Troubleshooting

### "API key is required"
- Make sure environment variable is set: `export ELEVENLABS_API_KEY='your-key'`
- Or add to `config.php`

### "Connection failed"
- Verify your API key is correct
- Check ElevenLabs status: https://status.elevenlabs.io/
- Ensure you have API credits

### Audio generation is slow
- This is normal - ElevenLabs takes time to generate audio
- Enable parallel processing in config for faster multi-segment generation
- Use the faster `eleven_turbo_v2_5` model

### "Unknown character" error
- Character key must be lowercase: 'margaret', 'jordan', 'david', 'expert'
- Check character names in `config.php`

### Script parsing issues
- Ensure script follows the format from `PodcastScriptGenerator.php`
- Character names must be in format: `CHARACTER (Role):`
- Dialog follows on next lines

## ElevenLabs Pricing & Limits

**Free Tier:**
- 10,000 characters/month
- Access to all voices
- Standard quality models

**Pro Tier:**
- 100,000+ characters/month
- Priority processing
- All models available

Check current pricing and limits at: https://elevenlabs.io/pricing

## Script Format Reference

Scripts must follow this format for parsing:

```
MARGARET (The Host):
This is what Margaret says. It can span
multiple lines and paragraphs.

JORDAN (The Help):
This is what Jordan says.

[TRANSITION MUSIC]

DAVID (The Sidekick):
This is what David says.

DR. PATTERSON (The Expert):
This is what the expert says.
```

**Important:**
- Character names in ALL CAPS
- `(Role)` in parentheses
- Colon after the role
- Music/sound directions in brackets (these are skipped)

## Advanced Features

### Batch Episode Generation

```php
<?php
$topics = [
    '001' => 'Sandpaper Grits: P400 vs 400',
    '002' => 'Best Woodworking Practices',
    '003' => 'Understanding Finishes',
];

foreach ($topics as $episode_num => $topic) {
    $generator->setEpisodeNumber($episode_num)
              ->setTopic($topic);
    
    $script = $generator->generateCompleteScript();
    $generator->saveScript($script, "episode-{$episode_num}.txt");
    
    // Generate audio
    $audio_files = $elevenlabs->generateScriptAudio($script);
}
```

### Custom Voice Mixing

```php
// Override default voices per-episode
$custom_voices = [
    'margaret' => 'different-voice-id-123',
    'expert' => 'another-voice-id-456',
];

$elevenlabs->updateCharacterVoiceSettings('margaret', [
    'voice_id' => $custom_voices['margaret'],
]);
```

### Export Audio Metadata

```php
$audio_files = $elevenlabs->generateScriptAudio($script);

$metadata = [
    'episode' => '001',
    'topic' => 'Sandpaper Grits',
    'generated_at' => date('Y-m-d H:i:s'),
    'segments' => $audio_files,
];

file_put_contents('episode-001-metadata.json', json_encode($metadata, JSON_PRETTY_PRINT));
```

## API Reference

### PodcastScriptGenerator

```php
$generator = new PodcastScriptGenerator();

// Fluent setters
$generator->setEpisodeNumber('001');
$generator->setTopic('Topic Here');

// Generate
$script = $generator->generateCompleteScript();

// Save/Display
$generator->saveScript($script, 'filename.txt');
$generator->displayScript($script);

// Individual segments
$opening = $generator->generateOpening();
$closing = $generator->generateClosing();
$exchange = $generator->generateDiscussionExchange(...);
```

### ElevenLabsIntegration

```php
$api = new ElevenLabsIntegration($api_key);

// Connection & info
$api->testConnection();
$api->getAccountInfo();
$api->getAvailableVoices();

// Audio generation
$api->generateCharacterAudio($character_key, $text, $output_path);
$api->generateScriptAudio($script_content, $output_directory);

// Voice settings
$api->getCharacterVoiceSettings($character_key);
$api->updateCharacterVoiceSettings($character_key, $settings);
```

## Next Steps

1. **Combine Audio Segments** - Merge character audio with music transitions
2. **Add Music/SFX** - Integrate intro/outro music and sound effects
3. **Publish Pipeline** - Auto-upload to podcast platforms (Spotify, Apple Podcasts, etc.)
4. **Web Dashboard** - Build UI for managing episodes and audio
5. **AI Script Enhancement** - Use LLMs to improve script quality dynamically

## License

This project is independent and can be migrated to its own repository.

## Support

For ElevenLabs API issues: https://elevenlabs.io/docs
For this generator: Check inline documentation in PHP files
