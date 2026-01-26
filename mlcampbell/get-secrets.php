#!/usr/bin/env php
<?php
/**
 * Get secrets from GitHub or environment
 * 
 * Usage: php get-secrets.php
 */

declare(strict_types=1);

class SecretManager
{
    /**
     * Get ELEVENLABS_API_KEY from multiple sources
     */
    public static function getElevenLabsKey(): ?string
    {
        // Try environment variable first
        if ($key = getenv('ELEVENLABS_API_KEY')) {
            return $key;
        }
        
        // Try GitHub CLI if available
        if (function_exists('exec')) {
            $output = [];
            $return_var = 0;
            @exec('gh secret list 2>/dev/null', $output, $return_var);
            
            if ($return_var === 0) {
                foreach ($output as $line) {
                    if (stripos($line, 'ELEVENLABS') !== false) {
                        // Try to get the secret value
                        $secret_output = [];
                        @exec('gh secret get ELEVENLABS_API_KEY 2>/dev/null', $secret_output);
                        if (!empty($secret_output)) {
                            return trim(implode('', $secret_output));
                        }
                    }
                }
            }
        }
        
        // Try .env file
        if (file_exists('.env')) {
            $env_content = file_get_contents('.env');
            if (preg_match('/ELEVENLABS_API_KEY\s*=\s*(.+)/', $env_content, $matches)) {
                return trim($matches[1], '\'"');
            }
        }
        
        return null;
    }
    
    /**
     * Test if API key works
     */
    public static function testKey(string $api_key): bool
    {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.elevenlabs.io/v1/user',
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'xi-api-key: ' . $api_key,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return $http_code === 200;
    }
}

// Main
echo "Checking for ElevenLabs API key...\n\n";

$key = SecretManager::getElevenLabsKey();

if ($key) {
    echo "✓ API Key found!\n";
    echo "Key preview: " . substr($key, 0, 10) . "...\n\n";
    
    echo "Testing connection...\n";
    if (SecretManager::testKey($key)) {
        echo "✓ Connection successful! API key is valid.\n\n";
        echo "You can now run:\n";
        echo "  php ElevenLabsIntegration.php\n";
        exit(0);
    } else {
        echo "✗ Connection failed. API key may be invalid.\n";
        exit(1);
    }
} else {
    echo "✗ API key not found in:\n";
    echo "  1. Environment variables (ELEVENLABS_API_KEY)\n";
    echo "  2. GitHub secrets\n";
    echo "  3. .env file\n\n";
    echo "Options:\n";
    echo "  a) Set environment: export ELEVENLABS_API_KEY='your-key'\n";
    echo "  b) Create .env file: echo 'ELEVENLABS_API_KEY=your-key' > .env\n";
    echo "  c) Use GitHub CLI: gh secret set ELEVENLABS_API_KEY\n";
    exit(1);
}
