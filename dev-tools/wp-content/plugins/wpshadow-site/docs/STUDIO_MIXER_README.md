# 🎙️ Professional Two-Person Podcast System

## Start Here ⭐

Welcome! You now have a complete system for generating professional two-person podcasts with ElevenLabs.

### In 2 minutes:

1. **Read:** [DELIVERY_SUMMARY.md](./DELIVERY_SUMMARY.md) - What you got
2. **Watch:** The [Quick Start](#quick-start) section below
3. **Follow:** [STUDIO_MIXER_INSTALLATION.md](./STUDIO_MIXER_INSTALLATION.md) to set up

---

## What This Does 🎵

Automatically creates professional podcasts like this:

```
[INTRO MUSIC - 20 seconds]
  Music starts LOUD
  Music DUCKS down to 25% volume

[HOST INTRODUCTION - 10 seconds]
  "Welcome to episode 42 of Tech Talk. Today we discuss AI."
  (speaking over soft background music)

[MAIN PODCAST - 15-60 minutes]
  SPEAKER 1: "So what do you think about AI?"
  SPEAKER 2: "I think transformer models are revolutionary..."
  SPEAKER 1: "Tell us more about that"
  SPEAKER 2: "Well, they enable..."

  (Background music plays softly at 25% volume throughout)

[OUTRO NARRATION - 20 seconds]
  Host thanks audience
  Mentions sponsor
  Asks for shares/likes

[OUTRO MUSIC - 5 seconds]
  Music fades in and loops

[SILENCE]

✅ Complete podcast ready!
```

---

## Features ✨

✅ **Professional Intro** - Music starts loud, ducks for narration
✅ **Two-Person Podcast** - Via ElevenLabs Studio API
✅ **Background Music** - Soft looping music under conversation
✅ **Sponsor Mentions** - Integrated outro with sponsor message
✅ **Call to Action** - Customizable message for shares/likes
✅ **Music Fades** - Professional audio fading and mixing
✅ **FFmpeg Mixing** - Precise audio control and ducking

---

## Quick Start 🚀

### Step 1: Install (5 minutes)

1. Copy `class-podcast-studio-mixer.php` to your includes folder
2. Add to main plugin file:
```php
require_once plugin_dir_path( __FILE__ ) . 'includes/class-podcast-studio-mixer.php';
```
3. Get API key: https://elevenlabs.io/account/billing/api-keys
4. Store in WordPress:
```php
update_option( 'wpshadow_elevenlabs_api_key', 'sk-...' );
```

### Step 2: Generate Podcast (2 minutes)

```php
$mixer = new WPShadow_Podcast_Studio_Mixer();

$result = $mixer->generate_professional_podcast( array(
    // Voices from ElevenLabs
    'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
    'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',

    // What to say in intro
    'intro_config' => array(
        'narration'  => 'Welcome to episode 42 of Tech Talk!',
        'music_file' => 'intro-music.mp3',
    ),

    // The podcast content
    'episode_config' => array(
        'title'            => 'The Future of AI',
        'description'      => 'A discussion about trends in AI',
        'content'          => '[SPEAKER 1]: Welcome! [SPEAKER 2]: Thanks for having me!',
        'background_music' => 'ambient-bg.mp3', // Optional
    ),

    // What to say at the end
    'outro_config' => array(
        'narration'       => 'Thank you for listening!',
        'sponsor_mention' => 'This episode brought to you by CloudCompute Pro',
        'cta'             => 'Please share and subscribe!',
        'music_file'      => 'outro-music.mp3',
    ),
) );

if ( ! is_wp_error( $result ) ) {
    echo 'Podcast ready: ' . $result['podcast_file'];
}
```

### Step 3: Done! ✅

Your podcast is generated and ready to use.

---

## Documentation Guide 📚

### For Quick Start (5-10 minutes)
→ Read: [STUDIO_MIXER_QUICKREF.md](./STUDIO_MIXER_QUICKREF.md)

### For Installation (10-30 minutes)
→ Follow: [STUDIO_MIXER_INSTALLATION.md](./STUDIO_MIXER_INSTALLATION.md)

### For Complete Reference (30 minutes)
→ Study: [STUDIO_MIXER_GUIDE.md](./STUDIO_MIXER_GUIDE.md)

### For Code Examples (15 minutes)
→ Review: [STUDIO_MIXER_EXAMPLES.php](./STUDIO_MIXER_EXAMPLES.php)

### For Integration (20 minutes)
→ Follow: [STUDIO_MIXER_INTEGRATION.md](./STUDIO_MIXER_INTEGRATION.md)

### For Overview (10 minutes)
→ Read: [STUDIO_MIXER_FEATURE_SUMMARY.md](./STUDIO_MIXER_FEATURE_SUMMARY.md)

### For Full Details
→ See: [DELIVERY_SUMMARY.md](./DELIVERY_SUMMARY.md)

---

## System Requirements ✅

Before you start, make sure you have:

- [ ] **PHP 7.0+** on your server
- [ ] **WordPress 5.0+** running
- [ ] **FFmpeg installed** (`apt install ffmpeg`)
- [ ] **ElevenLabs account** (https://elevenlabs.io)
- [ ] **API key** from ElevenLabs
- [ ] **2 voice IDs** selected from ElevenLabs Studio

---

## Architecture 🏗️

```
Your Podcast Config
        ↓
┌───────────────────────────────────┐
│ WPShadow_Podcast_Studio_Mixer     │
├───────────────────────────────────┤
│                                   │
│ 1. Intro Generation               │
│    Music + Narration + Ducking    │
│                                   │
│ 2. Studio Podcast                 │
│    ElevenLabs API + Background    │
│                                   │
│ 3. Outro Generation               │
│    Narration + Sponsor + Music    │
│                                   │
│ 4. Final Mix                      │
│    Concatenate all segments       │
│                                   │
└───────────────────────────────────┘
        ↓
    Podcast File ✅
```

---

## Audio Mixing 🎚️

### How Intro Works

```
Time:  0s    0.5s    1s      5s     10s    15s
       │      │      │       │      │      │
Music: ████████ ▄▄▄▄▄▄▄ ▄▄▄▄▄▄▄ ▄▄▄▄▄▄▄ ████████
         (100%)  (duck)     (narration plays)

Narra:        ════════════════════════════
              (starts at ~1 second)
```

### How Main Podcast Works

```
Speakers: ████████████████████████████████████████
          (95% volume - clear and dominant)

Background: ▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂▂
            (25% volume - soft, loops throughout)
```

### How Outro Works

```
Time:     0s    5s    10s   15s  18s  20s
          │     │     │     │    │    │
Narration: ════════════════════════════
           (host talks)        ▂▂  (fades out)

Music:     ▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▁▄▄▄▄▄▄▄
           (fades in)          (loops)
```

---

## Files Included 📦

### Core System
- **class-podcast-studio-mixer.php** (~600 lines)
  - Main class for podcast generation
  - Professional audio mixing
  - FFmpeg integration

### Documentation (5 guides)
- **STUDIO_MIXER_GUIDE.md** - Complete reference (~500 lines)
- **STUDIO_MIXER_QUICKREF.md** - Quick reference (~200 lines)
- **STUDIO_MIXER_INSTALLATION.md** - Setup guide (~250 lines)
- **STUDIO_MIXER_INTEGRATION.md** - Integration steps (~300 lines)
- **STUDIO_MIXER_FEATURE_SUMMARY.md** - Feature overview (~300 lines)

### Examples & Reference
- **STUDIO_MIXER_EXAMPLES.php** - 8 code examples (~400 lines)
- **DELIVERY_SUMMARY.md** - What you got (~400 lines)
- **This file** - Getting started guide

---

## Example: Simple Podcast 📝

```php
<?php
// Generate a simple two-person podcast

$mixer = new WPShadow_Podcast_Studio_Mixer();

$podcast = $mixer->generate_professional_podcast( array(
    'speaker1_voice_id' => '21m00Tcm4TlvDq8ikWAM',
    'speaker2_voice_id' => 'EXAVITQu4vr4xnSDxMaL',

    'intro_config' => array(
        'narration'  => 'Welcome to the Tech Talks podcast!',
        'music_file' => 'intro.mp3',
    ),

    'episode_config' => array(
        'title'       => 'AI in 2026',
        'description' => 'Latest trends in AI',
        'content'     => '[SPEAKER 1]: So what\'s new in AI?
[SPEAKER 2]: Transformers are getting more efficient.
[SPEAKER 1]: What does that mean for businesses?
[SPEAKER 2]: Faster inference, lower costs, better performance.',
        'background_music' => 'ambient.mp3',
    ),

    'outro_config' => array(
        'narration'       => 'Great discussion!',
        'sponsor_mention' => 'Thanks to our sponsor, CloudCompute.',
        'cta'             => 'Subscribe and share this episode!',
        'music_file'      => 'outro.mp3',
    ),
) );

if ( is_wp_error( $podcast ) ) {
    echo 'Error: ' . $podcast->get_error_message();
} else {
    echo 'Podcast created: ' . $podcast['podcast_file'];
}
?>
```

That's it! Your podcast is ready.

---

## Troubleshooting 🔧

### FFmpeg not found
```bash
apt-get install ffmpeg
```

### API key not working
```php
update_option( 'wpshadow_elevenlabs_api_key', 'your-api-key' );
```

### Audio file not found
Use correct path or attachment ID:
```php
'music_file' => 456,                  // Attachment ID
'music_file' => '/uploads/music.mp3', // File path
```

See [STUDIO_MIXER_QUICKREF.md#troubleshooting](./STUDIO_MIXER_QUICKREF.md#troubleshooting) for more help.

---

## Getting Help 💬

1. **Quick questions?** → [STUDIO_MIXER_QUICKREF.md](./STUDIO_MIXER_QUICKREF.md)
2. **How do I set it up?** → [STUDIO_MIXER_INSTALLATION.md](./STUDIO_MIXER_INSTALLATION.md)
3. **How does it work?** → [STUDIO_MIXER_GUIDE.md](./STUDIO_MIXER_GUIDE.md)
4. **Show me code examples** → [STUDIO_MIXER_EXAMPLES.php](./STUDIO_MIXER_EXAMPLES.php)
5. **Errors or not working?** → Check troubleshooting in guide files

---

## Next Steps 🎯

1. **Read:** [STUDIO_MIXER_QUICKREF.md](./STUDIO_MIXER_QUICKREF.md) (5 min)
2. **Follow:** [STUDIO_MIXER_INSTALLATION.md](./STUDIO_MIXER_INSTALLATION.md) (30 min)
3. **Review:** [STUDIO_MIXER_EXAMPLES.php](./STUDIO_MIXER_EXAMPLES.php) (15 min)
4. **Generate:** Your first podcast! (2 min)
5. **Celebrate:** 🎉

---

## Learning Path 📈

**Time Estimate: 2-3 hours for complete mastery**

1. **5 min** - This README
2. **5 min** - STUDIO_MIXER_QUICKREF.md
3. **30 min** - STUDIO_MIXER_INSTALLATION.md
4. **15 min** - STUDIO_MIXER_EXAMPLES.php
5. **30 min** - STUDIO_MIXER_GUIDE.md
6. **20 min** - STUDIO_MIXER_INTEGRATION.md

---

## Professional Results 🎙️

Generated podcasts are perfect for:

✅ Interview shows
✅ Co-hosted programs
✅ Educational content
✅ News briefings
✅ Product announcements
✅ Event coverage
✅ Storytelling
✅ Debates/discussions

---

## Performance ⚡

| Task | Time |
|------|------|
| Intro | 5-15 sec |
| Main | 30 sec - 2 min |
| Outro | 5-15 sec |
| Mix | 2-5 sec |
| **Total** | **1-3 min** |

---

## Support 📞

All documentation is self-contained. Start with:

1. [STUDIO_MIXER_QUICKREF.md](./STUDIO_MIXER_QUICKREF.md) - Quick answers
2. [STUDIO_MIXER_INSTALLATION.md](./STUDIO_MIXER_INSTALLATION.md) - Setup help
3. [STUDIO_MIXER_GUIDE.md](./STUDIO_MIXER_GUIDE.md) - Complete reference

---

## Summary 🎉

You now have:

✅ **Professional podcast generation system**
✅ **600+ lines of production code**
✅ **2700+ lines of documentation**
✅ **8 real-world code examples**
✅ **Complete troubleshooting guides**
✅ **Integration instructions**
✅ **Everything you need to create amazing podcasts**

**Ready to get started?** → [STUDIO_MIXER_INSTALLATION.md](./STUDIO_MIXER_INSTALLATION.md)

---

**Built with ❤️ for professional podcast creators**

*Last updated: 2026-01-25*
