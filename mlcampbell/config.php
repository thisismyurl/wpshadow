<?php
/**
 * Configuration for ElevenLabs Integration
 * 
 * Store API keys and settings here.
 * IMPORTANT: Never commit actual API keys to version control!
 * Use environment variables in production.
 */

return [
    // ElevenLabs API Configuration
    'elevenlabs' => [
        // Get your API key from: https://elevenlabs.io/app/settings/api-keys
        'api_key' => getenv('ELEVENLABS_API_KEY') ?: '',
        
        // API Configuration
        'api_url' => 'https://api.elevenlabs.io/v1',
        'timeout' => 60,
        
        // Voice Models (default model for all generations)
        'model' => 'eleven_turbo_v2_5',
        'models' => [
            'turbo' => 'eleven_turbo_v2_5',      // Faster, lower cost
            'standard' => 'eleven_multilingual_v2', // Standard quality
            'premium' => 'eleven_multilingual_v1',  // Higher quality
        ],
    ],
    
    // Character Voice Configuration
    'characters' => [
        'margaret' => [
            'name' => 'Margaret Chen',
            'role' => 'The Host',
            'voice_id' => 'EXAVITQu4vr4xnSDxMaL',  // Rachel
            'age' => 52,
            'tone' => 'Professional, authoritative, warm',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.75,
                'style' => 1.0,
                'use_speaker_boost' => true,
            ],
            'description' => 'NPR/CBC style podcast host',
        ],
        'jordan' => [
            'name' => 'Jordan Mills',
            'role' => 'The Help',
            'voice_id' => '21m00Tcm4TlvDq8ikWAM',  // Bella
            'age' => 28,
            'tone' => 'Idealistic, curious, energetic',
            'voice_settings' => [
                'stability' => 0.6,
                'similarity_boost' => 0.75,
                'style' => 0.8,
                'use_speaker_boost' => true,
            ],
            'description' => 'Younger audience proxy, asks great questions',
        ],
        'david' => [
            'name' => 'David Rodriguez',
            'role' => 'The Sidekick',
            'voice_id' => 'pNInz6obpgDQGcFmaJgB',  // Adam
            'age' => 30,
            'tone' => 'Conversational, collaborative, knowledgeable',
            'voice_settings' => [
                'stability' => 0.55,
                'similarity_boost' => 0.75,
                'style' => 0.9,
                'use_speaker_boost' => true,
            ],
            'description' => 'Co-host, reinforces perspectives, adds insights',
        ],
        'expert' => [
            'name' => 'Dr. James Patterson',
            'role' => 'The Expert',
            'voice_id' => 'ErXwobaYp3GY4tUvEQld',  // Sam
            'age' => 50,
            'tone' => 'Authoritative, knowledgeable, mentor-like',
            'voice_settings' => [
                'stability' => 0.5,
                'similarity_boost' => 0.8,
                'style' => 1.0,
                'use_speaker_boost' => true,
            ],
            'description' => 'Subject matter expert, deep dives',
        ],
    ],
    
    // Audio Output Configuration
    'audio' => [
        'format' => 'mp3',
        'output_directory' => 'audio_segments',
        'quality' => 'high',  // Options: low, medium, high
        'sample_rate' => 22050,
        
        // Episode naming
        'episode_naming' => 'episode-{number}-{slug}',
        'segment_naming' => '{index:03d}_{character_key}.mp3',
    ],
    
    // Processing Configuration
    'processing' => [
        // Words per minute for duration estimation
        'speech_rate' => 150,
        
        // Maximum text length per request (ElevenLabs has limits)
        'max_text_length' => 1000,
        
        // Retry configuration
        'max_retries' => 3,
        'retry_delay' => 2, // seconds
        
        // Parallel processing
        'enable_parallel' => false,
        'max_concurrent_requests' => 2,
    ],
    
    // Logging and Debugging
    'debug' => [
        'enabled' => getenv('DEBUG') === 'true',
        'log_file' => 'logs/elevenlabs.log',
        'log_level' => 'info', // debug, info, warning, error
        'save_parsed_script' => true,
        'script_output_file' => 'logs/parsed_script.json',
    ],
];
