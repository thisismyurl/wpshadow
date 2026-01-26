#!/usr/bin/env php
<?php
/**
 * MLCampbell Test & Demo Script
 * 
 * This script demonstrates the capabilities of the podcast generator
 * and ElevenLabs integration without requiring a real API key.
 * 
 * Usage:
 *   php test.php
 */

declare(strict_types=1);

// Load configuration
require 'EnvLoader.php';
require 'PodcastScriptGenerator.php';

class MLCampbellTest
{
    public function run(): void
    {
        echo "\n";
        echo str_repeat("=", 80) . "\n";
        echo "MLCampbell Podcast Generator - Test Suite\n";
        echo str_repeat("=", 80) . "\n\n";
        
        $this->testScriptGenerator();
        $this->displaySetupInstructions();
        $this->listAvailableFeatures();
    }
    
    /**
     * Test the script generator
     */
    private function testScriptGenerator(): void
    {
        echo "📝 Testing Script Generator\n";
        echo str_repeat("-", 80) . "\n\n";
        
        try {
            $generator = new PodcastScriptGenerator();
            
            $generator
                ->setEpisodeNumber('TEST')
                ->setTopic('Testing the Script Generator');
            
            echo "✓ Script generator initialized successfully\n";
            echo "✓ Episode number: TEST\n";
            echo "✓ Topic: Testing the Script Generator\n\n";
            
            // Test character access
            echo "Character Configuration:\n";
            foreach (['host', 'help', 'sidekick', 'expert'] as $type) {
                $char = $generator->getCharacter($type);
                if ($char) {
                    echo "  ✓ {$char->role}: {$char->name} (age {$char->age})\n";
                }
            }
            
            echo "\n";
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n\n";
        }
    }
    
    /**
     * Display setup instructions
     */
    private function displaySetupInstructions(): void
    {
        echo "🔧 Setup Instructions\n";
        echo str_repeat("-", 80) . "\n\n";
        
        $api_key = getenv('ELEVENLABS_API_KEY');
        
        if ($api_key) {
            echo "✓ ELEVENLABS_API_KEY is set\n";
            echo "  • Key preview: " . substr($api_key, 0, 10) . "...\n\n";
            echo "You're ready to generate audio! Run:\n";
            echo "  php ElevenLabsIntegration.php\n\n";
        } else {
            echo "⚠ ELEVENLABS_API_KEY is not set\n\n";
            echo "To enable audio generation:\n";
            echo "  1. Go to https://elevenlabs.io/app/settings/api-keys\n";
            echo "  2. Copy your API key\n";
            echo "  3. Create or edit .env file:\n";
            echo "     cp .env.example .env\n";
            echo "     nano .env\n";
            echo "  4. Add: ELEVENLABS_API_KEY=your_key_here\n";
            echo "  5. Save and run: php ElevenLabsIntegration.php\n\n";
        }
    }
    
    /**
     * List available features
     */
    private function listAvailableFeatures(): void
    {
        echo "✨ Available Features\n";
        echo str_repeat("-", 80) . "\n\n";
        
        echo "Script Generation:\n";
        echo "  ✓ Generate podcast scripts with 4 distinct characters\n";
        echo "  ✓ Customize episode number and topic\n";
        echo "  ✓ Save scripts to plain text files\n";
        echo "  ✓ Professional panel discussion format\n\n";
        
        echo "Character Voices:\n";
        echo "  ✓ Margaret Chen (The Host) - Professional, authoritative\n";
        echo "  ✓ Jordan Mills (The Help) - Idealistic, curious\n";
        echo "  ✓ David Rodriguez (The Sidekick) - Collaborative\n";
        echo "  ✓ Dr. James Patterson (The Expert) - Deep knowledge\n\n";
        
        echo "Audio Generation (requires API key):\n";
        echo "  ✓ Convert scripts to professional audio\n";
        echo "  ✓ Individual character voice generation\n";
        echo "  ✓ Automatic script parsing\n";
        echo "  ✓ MP3 output format\n";
        echo "  ✓ Voice customization per character\n\n";
        
        echo "Configuration:\n";
        echo "  ✓ Custom voice settings (stability, similarity, style)\n";
        echo "  ✓ Multiple ElevenLabs models support\n";
        echo "  ✓ Episode metadata and logging\n";
        echo "  ✓ Batch processing ready\n\n";
    }
}

// Run tests
$test = new MLCampbellTest();
$test->run();

echo str_repeat("=", 80) . "\n";
echo "Documentation:\n";
echo "  📖 Quick Start:  QUICKSTART.md\n";
echo "  📚 Full Docs:    README.md\n";
echo "  ⚙️  Config:      config.php\n";
echo "\n";
echo "Next Steps:\n";
echo "  1. If you have an ElevenLabs API key, set it in .env\n";
echo "  2. Run: php PodcastScriptGenerator.php\n";
echo "  3. Run: php ElevenLabsIntegration.php\n";
echo "\n";
echo str_repeat("=", 80) . "\n\n";
