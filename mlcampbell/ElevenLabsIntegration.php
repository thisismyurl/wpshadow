<?php
/**
 * ElevenLabs Audio Integration
 * 
 * Integrates with ElevenLabs API to convert podcast scripts to audio.
 * Supports multiple character voices and audio combining.
 * 
 * @author Your Name
 * @version 1.0
 */

declare(strict_types=1);

class ElevenLabsIntegration
{
    private string $api_key;
    private string $api_url = 'https://api.elevenlabs.io/v1';
    private array $character_voices = [];
    private array $voice_settings = [];
    
    /**
     * Character voice configuration
     * Maps character names to ElevenLabs voice IDs
     */
    const VOICE_PRESETS = [
        'margaret' => [
            'voice_id' => 'EXAVITQu4vr4xnSDxMaL',  // Rachel - Professional female
            'stability' => 0.5,
            'similarity_boost' => 0.75,
            'style' => 1.0,
            'use_speaker_boost' => true,
        ],
        'jordan' => [
            'voice_id' => '21m00Tcm4TlvDq8ikWAM',  // Bella - Younger female
            'stability' => 0.6,
            'similarity_boost' => 0.75,
            'style' => 0.8,
            'use_speaker_boost' => true,
        ],
        'david' => [
            'voice_id' => 'pNInz6obpgDQGcFmaJgB',  // Adam - Male conversational
            'stability' => 0.55,
            'similarity_boost' => 0.75,
            'style' => 0.9,
            'use_speaker_boost' => true,
        ],
        'expert' => [
            // Fallback to a widely available male voice to avoid 404 errors on lower tiers.
            'voice_id' => 'pNInz6obpgDQGcFmaJgB',  // Adam - Authoritative male
            'stability' => 0.5,
            'similarity_boost' => 0.8,
            'style' => 1.0,
            'use_speaker_boost' => true,
        ],
    ];
    
    public function __construct(string $api_key)
    {
        if (empty($api_key)) {
            throw new Exception('ElevenLabs API key is required');
        }
        $this->api_key = $api_key;
        $this->initializeCharacterVoices();
    }
    
    /**
     * Initialize character voice mappings
     */
    private function initializeCharacterVoices(): void
    {
        $this->character_voices = [
            'margaret' => 'Margaret Chen',
            'jordan' => 'Jordan Mills',
            'david' => 'David Rodriguez',
            'expert' => 'Dr. James Patterson',
        ];
        
        $this->voice_settings = self::VOICE_PRESETS;
    }
    
    /**
     * Test API connection
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('GET', '/user');
            return isset($response['subscription']);
        } catch (Exception $e) {
            echo "Connection test failed: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Get available voices from ElevenLabs
     */
    public function getAvailableVoices(): array
    {
        try {
            return $this->makeRequest('GET', '/voices');
        } catch (Exception $e) {
            echo "Error fetching voices: " . $e->getMessage() . "\n";
            return [];
        }
    }
    
    /**
     * Convert text to speech for a single character
     */
    public function generateCharacterAudio(
        string $character_key,
        string $text,
        string $output_path = ''
    ): ?string
    {
        if (!isset($this->voice_settings[$character_key])) {
            throw new Exception("Unknown character: {$character_key}");
        }
        
        if (empty($text)) {
            throw new Exception("Text cannot be empty");
        }
        
        try {
            $voice_config = $this->voice_settings[$character_key];
            $voice_id = $voice_config['voice_id'];
            
            $payload = [
                'text' => $text,
                'model_id' => 'eleven_turbo_v2_5',
                'voice_settings' => [
                    'stability' => $voice_config['stability'],
                    'similarity_boost' => $voice_config['similarity_boost'],
                    'style' => $voice_config['style'],
                    'use_speaker_boost' => $voice_config['use_speaker_boost'],
                ]
            ];
            
            $audio_data = $this->makeRequest(
                'POST',
                "/text-to-speech/{$voice_id}/stream",
                $payload,
                is_audio: true
            );
            
            if (!$output_path) {
                $output_path = $this->generateOutputPath($character_key);
            }
            
            file_put_contents($output_path, $audio_data);
            echo "✓ Audio generated for {$character_key}: {$output_path}\n";
            
            return $output_path;
        } catch (Exception $e) {
            echo "Error generating audio for {$character_key}: " . $e->getMessage() . "\n";
            return null;
        }
    }
    
    /**
     * Extract and generate audio for all character segments in a script
     */
    public function generateScriptAudio(
        string $script_content,
        string $output_directory = 'audio_segments'
    ): array
    {
        if (!is_dir($output_directory)) {
            mkdir($output_directory, 0755, true);
        }
        
        $segments = $this->parseScriptSegments($script_content);
        $audio_files = [];
        
        foreach ($segments as $segment) {
            // Normalize character key and map aliases to configured voices.
            $character_key = strtolower(trim($segment['character']));
            $character_key = rtrim($character_key, '.'); // remove trailing period from names like "dr. patterson"

            $aliases = array(
                'dr patterson'  => 'expert',
                'dr. patterson' => 'expert',
                'patterson'     => 'expert',
            );

            if (isset($aliases[$character_key])) {
                $character_key = $aliases[$character_key];
            }
            $text = $segment['text'];
            
            // Skip music and sound directions
            if (in_array($character_key, ['opening', 'transition', 'closing'])) {
                echo "⊘ Skipping production direction: {$character_key}\n";
                continue;
            }
            
            if (!isset($this->voice_settings[$character_key])) {
                echo "⚠ Unknown character: {$character_key}, skipping\n";
                continue;
            }
            
            $output_file = $output_directory . '/' . sprintf('%03d', $segment['index']) . "_{$character_key}.mp3";
            $audio_path = $this->generateCharacterAudio($character_key, $text, $output_file);
            
            if ($audio_path) {
                $audio_files[$segment['index']] = [
                    'character' => $segment['character'],
                    'character_key' => $character_key,
                    'file' => $audio_path,
                    'duration' => $this->estimateAudioDuration($text),
                ];
            }
        }
        
        return $audio_files;
    }
    
    /**
     * Parse podcast script into character segments
     */
    private function parseScriptSegments(string $script_content): array
    {
        $segments = [];
        $lines = explode("\n", $script_content);
        
        $current_character = null;
        $current_text = [];
        $segment_index = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines, headers, and music directions
            if (empty($line) || str_starts_with($line, '===') || str_starts_with($line, '---')) {
                continue;
            }
            
            // Check for character names (format: "CHARACTER (Role):")
            if (preg_match('/^([A-Z\s\.]+)\s*\(.*?\)\s*:\s*$/', $line, $matches)) {
                // Save previous segment
                if ($current_character && !empty($current_text)) {
                    $segments[] = [
                        'index' => $segment_index++,
                        'character' => $current_character,
                        'text' => implode(' ', $current_text),
                    ];
                    $current_text = [];
                }
                
                $current_character = $matches[1];
            } elseif ($current_character && !str_starts_with($line, '[')) {
                // Collect text lines (skip [MUSIC] or [ACTION] directives)
                $current_text[] = $line;
            }
        }
        
        // Don't forget the last segment
        if ($current_character && !empty($current_text)) {
            $segments[] = [
                'index' => $segment_index,
                'character' => $current_character,
                'text' => implode(' ', $current_text),
            ];
        }
        
        return $segments;
    }
    
    /**
     * Make HTTP request to ElevenLabs API
     */
    private function makeRequest(
        string $method,
        string $endpoint,
        array $payload = [],
        bool $is_audio = false
    ): mixed
    {
        $url = $this->api_url . $endpoint;
        
        $curl = curl_init();
        
        $headers = [
            'xi-api-key: ' . $this->api_key,
            'Content-Type: application/json',
        ];
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
        ]);
        
        if ($method === 'POST' && !empty($payload)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
        }
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($error) {
            throw new Exception("cURL Error: {$error}");
        }
        
        if ($http_code >= 400) {
            $error_data = json_decode($response, true);
            $error_message = $error_data['detail'] ?? $error_data['message'] ?? 'Unknown error';
            if (is_array($error_message)) {
                $error_message = json_encode($error_message);
            }
            throw new Exception("API Error ({$http_code}): {$error_message}");
        }
        
        if ($is_audio) {
            // For audio, return raw binary data
            return $response;
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Estimate audio duration based on text length
     * Average speaking rate: ~150 words per minute
     */
    private function estimateAudioDuration(string $text): float
    {
        $word_count = str_word_count($text);
        $duration_seconds = ($word_count / 150) * 60;
        return round($duration_seconds, 2);
    }
    
    /**
     * Generate output file path
     */
    private function generateOutputPath(string $character_key): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        return "audio_{$character_key}_{$timestamp}.mp3";
    }
    
    /**
     * Get character voice settings
     */
    public function getCharacterVoiceSettings(string $character_key): ?array
    {
        return $this->voice_settings[$character_key] ?? null;
    }
    
    /**
     * Update character voice settings
     */
    public function updateCharacterVoiceSettings(
        string $character_key,
        array $settings
    ): void
    {
        if (!isset($this->voice_settings[$character_key])) {
            throw new Exception("Unknown character: {$character_key}");
        }
        
        $this->voice_settings[$character_key] = array_merge(
            $this->voice_settings[$character_key],
            $settings
        );
    }
    
    /**
     * Get current API usage/credits
     */
    public function getAccountInfo(): ?array
    {
        try {
            return $this->makeRequest('GET', '/user');
        } catch (Exception $e) {
            echo "Error fetching account info: " . $e->getMessage() . "\n";
            return null;
        }
    }
}

// Example usage
if (php_sapi_name() === 'cli') {
    // Load API key from environment or config
    $api_key = getenv('ELEVENLABS_API_KEY');
    
    if (!$api_key) {
        echo "Error: ELEVENLABS_API_KEY environment variable not set\n";
        echo "\nTo test ElevenLabs integration:\n";
        echo "1. Set your API key: export ELEVENLABS_API_KEY='your-key-here'\n";
        echo "2. Ensure you have a script file: episode-001-sandpaper-grits.txt\n";
        echo "3. Run: php ElevenLabsIntegration.php\n";
        exit(1);
    }
    
    try {
        $elevenlabs = new ElevenLabsIntegration($api_key);
        
        // Test connection
        echo "Testing ElevenLabs connection...\n";
        if ($elevenlabs->testConnection()) {
            echo "✓ Connection successful\n\n";
            
            // Get account info
            $account = $elevenlabs->getAccountInfo();
            if ($account) {
                echo "Account Information:\n";
                echo "  - Tier: " . ($account['subscription']['tier'] ?? 'Unknown') . "\n";
                echo "  - Characters Used: " . ($account['character_count'] ?? '0') . "\n";
                echo "  - Character Limit: " . ($account['character_limit'] ?? 'Unknown') . "\n";
                echo "\n";
            }
            
            // Try to generate audio from script
            $script_file = $argv[1] ?? 'episode-001-sandpaper-grits.txt';
            if (file_exists($script_file)) {
                echo "Found script file: {$script_file}\n";
                echo "Reading and parsing script...\n\n";
                
                $script_content = file_get_contents($script_file);
                
                echo "Generating audio segments...\n";
                echo "Note: This will use API credits. Each character segment uses credits.\n";
                echo "Press Ctrl+C to cancel if needed.\n\n";
                
                // Create output directory
                $output_dir = 'audio_segments';
                
                // Generate audio for all segments
                $audio_files = $elevenlabs->generateScriptAudio($script_content, $output_dir);
                
                if (!empty($audio_files)) {
                    echo "\n✓ Audio generation complete!\n";
                    echo "\nGenerated audio files:\n";
                    foreach ($audio_files as $index => $file_info) {
                        echo "  [{$index}] {$file_info['character']}: {$file_info['file']} ";
                        echo "({$file_info['duration']}s)\n";
                    }
                    
                    echo "\nAll audio files saved to: {$output_dir}/\n";
                } else {
                    echo "\nNo audio files generated. Check the error messages above.\n";
                }
            } else {
                echo "Script file not found: {$script_file}\n";
                echo "Generate a script first using: php PodcastScriptGenerator.php\n";
            }
        } else {
            echo "✗ Connection failed. Check your API key.\n";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
