# MLCampbell - Using GitHub Secrets with ElevenLabs API

## Secret Already Added ✓

You've added the `ELEVENLABS_API_KEY` secret to your repository. Here's how to use it:

---

## Option 1: GitHub Actions (Recommended for CI/CD)

The secret is automatically available in GitHub Actions workflows.

### Running the Workflow

1. Go to **Actions** → **Generate Podcast Episodes**
2. Click **Run workflow**
3. Fill in:
   - Episode number (e.g., `001`)
   - Topic (e.g., `Woodworking Best Practices`)
4. Click **Run workflow**

The workflow will:
- ✓ Generate the podcast script
- ✓ Use your stored API key automatically
- ✓ Generate audio files
- ✓ Upload as artifacts
- ✓ Create a release (optional)

**Workflow file:** `.github/workflows/podcast.yml`

---

## Option 2: Local Development in Codespace

GitHub repository secrets are **not** directly available in Codespace. You have three choices:

### Choice A: Setup Script (Recommended)

```bash
cd /workspaces/wpshadow/mlcampbell
bash setup.sh
```

This will:
1. Prompt you for your API key
2. Create `.env` file with the key
3. Test the connection
4. Ready to generate audio

### Choice B: Manual Setup

```bash
cd /workspaces/wpshadow/mlcampbell

# Create .env file
cp .env.example .env

# Edit with your API key
nano .env

# Add this line:
# ELEVENLABS_API_KEY=your_key_here

# Save and test
php ElevenLabsIntegration.php
```

### Choice C: Set Environment Variable

```bash
# In the terminal
export ELEVENLABS_API_KEY="your_key_here"

# Then run
cd /workspaces/wpshadow/mlcampbell
php ElevenLabsIntegration.php
```

---

## Workflow File Details

**Location:** `.github/workflows/podcast.yml`

**Triggers:**
- Manual dispatch: **Actions** → **Run workflow**
- Auto on code changes to `mlcampbell/` or workflow file

**What It Does:**
```
1. Setup PHP environment
2. Create .env with GitHub secret
3. Test API connection
4. Generate podcast script
5. Generate audio from script
6. Upload audio as artifacts
7. Create GitHub release (optional)
```

**Duration:** ~2-5 minutes per episode

---

## Security Notes

✅ **Secret Security:**
- Never appears in logs
- Encrypted at rest
- Only available to authorized workflows
- `.env` file is gitignored

✅ **Local Development:**
- `.env` is in `.gitignore`
- Won't be committed to repository
- Safe to enter key locally

✅ **Best Practices:**
- Keep your key private
- Rotate key periodically
- Use GitHub's secret masking
- Review workflow permissions

---

## Quick Start Paths

### I Want to Generate Audio via GitHub Actions:

```bash
# 1. Push code
git add .
git commit -m "Add podcast generator"
git push

# 2. Go to GitHub
# Actions → Generate Podcast Episodes

# 3. Run workflow
# Fill in episode number and topic
# Click "Run workflow"

# 4. Wait 2-5 minutes

# 5. Download audio from artifacts
```

### I Want to Generate Audio Locally in Codespace:

```bash
# 1. Run setup
cd /workspaces/wpshadow/mlcampbell
bash setup.sh

# 2. Enter your API key when prompted
# (You'll need to manually provide it - 
#  can't auto-access from repo secret in Codespace)

# 3. Generate podcast
php PodcastScriptGenerator.php

# 4. Generate audio
php ElevenLabsIntegration.php

# 5. Audio in: audio_segments/
```

---

## Accessing the Secret in Codespace

If you need the key in Codespace, you have options:

### Option 1: Use setup.sh (Easiest)
```bash
bash setup.sh
# Prompts you to enter your API key once
```

### Option 2: Manual Environment Variable
```bash
# Get key from somewhere safe and run:
export ELEVENLABS_API_KEY="your_key"

# Or add to ~/.bashrc for persistence:
echo 'export ELEVENLABS_API_KEY="your_key"' >> ~/.bashrc
```

### Option 3: Create .env File
```bash
echo "ELEVENLABS_API_KEY=your_key" > .env
```

### Option 4: Use gh CLI (if available)
```bash
# GitHub CLI doesn't expose secrets to Codespace
# But you can use personal access token instead:
gh auth status
```

---

## Testing the Connection

After setting up your key:

```bash
cd /workspaces/wpshadow/mlcampbell

# Quick test
php -r "
require 'ElevenLabsIntegration.php';
\$api = new ElevenLabsIntegration(getenv('ELEVENLABS_API_KEY'));
if (\$api->testConnection()) {
    echo \"✓ Connected!\\n\";
} else {
    echo \"✗ Failed\\n\";
}
"
```

---

## Workflow Status

Check workflow runs:
1. Go to **Actions** in your GitHub repo
2. Click **Generate Podcast Episodes**
3. See run history and logs

---

## Troubleshooting

### "API key not found"

**In Codespace:**
- Use `bash setup.sh` to configure
- Or manually create `.env` file
- Secret isn't auto-available in Codespace

**In Actions:**
- Verify secret exists: Settings → Secrets
- Verify workflow uses: `${{ secrets.ELEVENLABS_API_KEY }}`
- Check workflow logs for errors

### "Connection failed"

- Verify API key is correct
- Check ElevenLabs status: https://status.elevenlabs.io/
- Ensure you have API credits remaining
- Test key in browser: https://elevenlabs.io/app/settings/api-keys

### "No audio generated"

- Check `.env` or environment variable is set
- Verify API key works with test connection
- Check episode script was generated
- Review error messages in console

---

## File Structure

```
wpshadow/
├── .github/
│   └── workflows/
│       └── podcast.yml              ← Workflow for GitHub Actions
│
└── mlcampbell/
    ├── setup.sh                     ← Local setup script
    ├── .env.example                 ← Template
    ├── .env                         ← Local (git-ignored)
    ├── PodcastScriptGenerator.php
    ├── ElevenLabsIntegration.php
    └── audio_segments/              ← Output directory
```

---

## Next Steps

1. **For GitHub Actions:**
   - Go to Actions tab
   - Run "Generate Podcast Episodes"
   - Download audio from artifacts

2. **For Local Development:**
   - Run `bash setup.sh`
   - Enter your API key when prompted
   - Run `php PodcastScriptGenerator.php`
   - Run `php ElevenLabsIntegration.php`

3. **For Batch Processing:**
   - Create multiple episodes in workflow
   - Or loop locally with custom script

---

## Integration Points

The secret is available in:

✅ **GitHub Actions workflows** - via `${{ secrets.ELEVENLABS_API_KEY }}`  
⚠️ **Codespace terminal** - requires manual setup (security feature)  
✓ **Local development** - via `.env` file (git-ignored)  
✓ **Environment variable** - via `export ELEVENLABS_API_KEY=`  

---

**Secret Status:** ✅ Configured and Ready  
**Workflows:** ✅ Created and Ready  
**Local Setup:** ✅ Automated via setup.sh  

You're all set! 🎉
