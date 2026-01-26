# MLCampbell - Quick Start Guide

## 5-Minute Setup

### Step 1: Get Your ElevenLabs API Key (1 min)

1. Go to https://elevenlabs.io/
2. Sign up for a free account
3. Click **Settings** → **API Keys**
4. Copy your API key

### Step 2: Set Up Environment (2 min)

```bash
cd /workspaces/wpshadow/mlcampbell

# Copy the example config
cp .env.example .env

# Edit .env and paste your API key
# On Linux/Mac:
nano .env
# On Windows, use any text editor

# Result should look like:
# ELEVENLABS_API_KEY=sk_your_key_here
```

### Step 3: Generate Your First Podcast Script (1 min)

```bash
php PodcastScriptGenerator.php
```

This creates: `episode-001-sandpaper-grits.txt`

### Step 4: Convert to Audio (1 min)

```bash
php ElevenLabsIntegration.php
```

This creates MP3 files in the `audio_segments/` folder:
- `001_margaret.mp3` - Host intro
- `002_david.mp3` - Sidekick perspective
- `003_jordan.mp3` - Audience question
- etc.

**Done!** You now have a podcast episode in audio form.

---

## Understanding the Output

### Script File Structure

Your generated script looks like this:

```
PODCAST SCRIPT - EPISODE 001
TOPIC: Understanding Sandpaper Grits: P400 vs 400
================================================================================

[OPENING THEME MUSIC FADES]

MARGARET (The Host):
Welcome back to In Depth. I'm Margaret Chen...

DAVID (The Sidekick):
Yeah, it's the size of the particles...

JORDAN (The Help):
Wait, so a higher number is actually smoother?

DR. PATTERSON (The Expert):
It does seem backwards, and that's because...
```

### Audio Files Generated

```
audio_segments/
├── 001_margaret.mp3      (2.1 MB - 45 seconds)
├── 002_david.mp3         (1.8 MB - 38 seconds)
├── 003_jordan.mp3        (1.2 MB - 25 seconds)
├── 004_expert.mp3        (3.5 MB - 75 seconds)
└── ... (more segments)
```

Each file is ready to be combined with music and effects for your final episode.

---

## Common Tasks

### Generate a Different Topic

Edit the example in `PodcastScriptGenerator.php`:

```php
$generator
    ->setEpisodeNumber('002')
    ->setTopic('Best Woodworking Practices');

$script = $generator->generateCompleteScript();
```

### Use Custom Character Voices

Before generating audio:

```php
$elevenlabs->updateCharacterVoiceSettings('margaret', [
    'voice_id' => 'different_voice_id',
    'stability' => 0.6,
    'similarity_boost' => 0.8,
]);
```

### Check Your API Usage

```bash
php -r "
require 'ElevenLabsIntegration.php';
\$api = new ElevenLabsIntegration(getenv('ELEVENLABS_API_KEY'));
\$info = \$api->getAccountInfo();
echo 'Characters used: ' . \$info['character_count'] . ' / ' . \$info['character_limit'];
"
```

### Delete Previous Audio and Regenerate

```bash
# Clear old audio
rm -rf audio_segments/*

# Regenerate
php ElevenLabsIntegration.php
```

---

## Customizing Your Podcast

### Change Character Names/Ages

Edit `config.php`:

```php
'characters' => [
    'margaret' => [
        'name' => 'Your Host Name',
        'age' => 45,
        'voice_id' => 'different-voice',
        // ... other settings
    ],
],
```

### Adjust Voice Settings

The voice settings control how characters sound:

| Setting | Range | Effect |
|---------|-------|--------|
| `stability` | 0-1 | Higher = more consistent, lower = more emotional |
| `similarity_boost` | 0-1 | Higher = closer match to voice, lower = more variation |
| `style` | 0-2 | Controls emotion/energy (0=calm, 2=energetic) |
| `use_speaker_boost` | true/false | Makes voice clearer and more focused |

### Adjust Speech Pacing

In `config.php`:

```php
'processing' => [
    'speech_rate' => 150,  // Words per minute (default: 150)
    // Lower = slower, Higher = faster
],
```

---

## Troubleshooting

### "Connection failed"

```bash
# Check your API key
echo $ELEVENLABS_API_KEY

# Should show your key, not empty
# If empty, load your .env file:
source .env
echo $ELEVENLABS_API_KEY
```

### "Unknown character"

Make sure character key is lowercase:
- ✓ `'margaret'`
- ✗ `'Margaret'`
- ✗ `'MARGARET'`

### "Script file not found"

Run this first to generate the script:

```bash
php PodcastScriptGenerator.php
```

### "No audio generated"

Check error messages in the output. Common causes:
- API key is invalid
- No API credits remaining
- Text is too long (max ~1000 characters per request)
- Network connection issue

### "Audio quality is poor"

Try a different model in `config.php`:

```php
'elevenlabs' => [
    'model' => 'eleven_multilingual_v2',  // Better quality
],
```

---

## File Organization

```
mlcampbell/
├── PodcastScriptGenerator.php     ← Script creation
├── ElevenLabsIntegration.php      ← Audio generation
├── EnvLoader.php                  ← Configuration helper
├── config.php                     ← Settings
├── .env                           ← Your API key (don't commit)
├── .env.example                   ← Template
├── README.md                      ← Full documentation
├── QUICKSTART.md                  ← This file
│
├── episode-001-*.txt              ← Generated scripts
├── audio_segments/                ← Generated audio files
│   ├── 001_margaret.mp3
│   ├── 002_david.mp3
│   └── ...
│
└── logs/                          ← Debug logs (optional)
    ├── elevenlabs.log
    └── parsed_script.json
```

---

## Next Steps

1. **Generate multiple episodes** - Create content on different topics
2. **Customize voices** - Find voice combinations that work for your style
3. **Add music** - Integrate intro/outro music with your audio
4. **Share your podcast** - Use your audio files to start your podcast
5. **Build automation** - Create scripts to batch-generate episodes

---

## Pricing & Limits

**Free Tier (what you get starting out):**
- 10,000 characters/month
- All voices available
- Standard quality models
- ~8-10 full podcast episodes per month

**Estimate:**
- 10 minutes of dialogue = ~2,000 characters
- One episode = ~10,000 characters (max for free tier)

Check current pricing: https://elevenlabs.io/pricing

---

## Performance Tips

- **Faster generation:** Use `eleven_turbo_v2_5` model (already default)
- **Better quality:** Use `eleven_multilingual_v2` (slower, same cost)
- **Save API calls:** Generate once, keep audio files
- **Parallel processing:** Can be enabled in config for future releases

---

## Getting Help

1. **Script not generating?** Check `PodcastScriptGenerator.php` for errors
2. **Audio not generating?** Check `ElevenLabsIntegration.php` error messages
3. **API issues?** Check https://status.elevenlabs.io/
4. **ElevenLabs docs?** Go to https://elevenlabs.io/docs

---

## Pro Tips

✨ **Tip 1:** Generate scripts first, then batch-generate audio overnight  
✨ **Tip 2:** Keep character voice settings consistent across episodes  
✨ **Tip 3:** Test with short text before full scripts  
✨ **Tip 4:** Save your best voice configurations in config.php  
✨ **Tip 5:** Use a CI/CD pipeline to auto-generate and upload episodes  

---

**Ready to create your first podcast?** Start with Step 1 above!
