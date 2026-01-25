<?php
/**
 * Setup Validation & Testing Script for Podcast Integration
 *
 * Run this in WordPress to validate your podcast setup:
 * 1. Add this file to your theme or plugin
 * 2. Visit: yoursite.com/?podcast_test=1 (adjust parameter as needed)
 * 3. Check the output for any issues
 */

// Uncomment to enable testing via URL parameter
// if ( isset( $_GET['podcast_test'] ) && current_user_can( 'manage_options' ) ) {
//     wpshadow_podcast_test_suite();
// }

/**
 * Run all podcast system tests
 */
function wpshadow_podcast_test_suite() {
	echo '<div style="font-family: monospace; padding: 20px; background: #f5f5f5; border: 1px solid #ddd;">';

	echo '<h2>🎙️ WPShadow Podcast System Diagnostics</h2>';

	$all_pass = true;

	// Test 1: Plugin activation
	echo '<h3>1. Plugin Status</h3>';
	if ( class_exists( 'WPShadow_Podcast_Generator' ) ) {
		echo '✅ Podcast Generator class loaded<br>';
	} else {
		echo '❌ Podcast Generator class NOT found<br>';
		$all_pass = false;
	}

	// Test 2: Database table
	echo '<h3>2. Database Setup</h3>';
	global $wpdb;
	$table = $wpdb->prefix . 'wpshadow_podcast_queue';
	$exists = $wpdb->get_var( "SHOW TABLES LIKE '$table'" );

	if ( $exists ) {
		echo '✅ Podcast queue table exists<br>';

		// Count items
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
		echo "   - Queue items: $count<br>";

		// Check for failed items
		$failed = $wpdb->get_var( "SELECT COUNT(*) FROM $table WHERE status = 'failed'" );
		if ( $failed > 0 ) {
			echo "   ⚠️  Failed items: $failed (check logs)<br>";
		}
	} else {
		echo '❌ Podcast queue table NOT found<br>';
		echo '   Run: WPShadow_Podcast_Generator::create_queue_table()<br>';
		$all_pass = false;
	}

	// Test 3: ElevenLabs API key
	echo '<h3>3. ElevenLabs Configuration</h3>';
	$el_settings = wpshadow_site_get_elevenlabs_settings();

	if ( ! empty( $el_settings['api_key'] ) ) {
		echo '✅ API key configured<br>';
	} else {
		echo '❌ API key NOT configured<br>';
		echo '   Set it in: WPShadow Site > ElevenLabs<br>';
		$all_pass = false;
	}

	if ( ! empty( $el_settings['voice_id'] ) ) {
		echo '✅ Default voice ID: ' . esc_html( substr( $el_settings['voice_id'], 0, 20 ) ) . '...<br>';
	} else {
		echo '❌ Default voice ID NOT configured<br>';
		$all_pass = false;
	}

	// Test 4: Podcast settings
	echo '<h3>4. Podcast Generator Settings</h3>';
	$pod_settings = wpshadow_site_get_podcast_settings();

	if ( $pod_settings['enabled'] ) {
		echo '✅ Podcast generation ENABLED<br>';
	} else {
		echo '⚠️  Podcast generation is DISABLED<br>';
		echo '   Enable it in: WPShadow Site > Podcast Generator<br>';
	}

	if ( ! empty( $pod_settings['title_voice_id'] ) ) {
		echo '✅ Title voice: ' . esc_html( substr( $pod_settings['title_voice_id'], 0, 20 ) ) . '...<br>';
	} else {
		echo '❌ Title voice NOT configured<br>';
		$all_pass = false;
	}

	if ( ! empty( $pod_settings['content_voice_id'] ) ) {
		echo '✅ Content voice: ' . esc_html( substr( $pod_settings['content_voice_id'], 0, 20 ) ) . '...<br>';
	} else {
		echo '❌ Content voice NOT configured<br>';
		$all_pass = false;
	}

	// Test 5: File permissions
	echo '<h3>5. Storage Permissions</h3>';
	$upload_dir = wp_upload_dir();
	$podcast_dir = $upload_dir['basedir'] . '/wpshadow-podcasts';

	if ( is_dir( $podcast_dir ) ) {
		echo '✅ Podcast directory exists: ' . esc_html( $podcast_dir ) . '<br>';

		if ( is_writable( $podcast_dir ) ) {
			echo '✅ Podcast directory is writable<br>';
		} else {
			echo '❌ Podcast directory is NOT writable<br>';
			$all_pass = false;
		}
	} else {
		echo '⚠️  Podcast directory will be created on first generation<br>';
	}

	// Test 6: FFmpeg availability
	echo '<h3>6. Audio Processing (FFmpeg)</h3>';
	$ffmpeg_path = shell_exec( 'which ffmpeg 2>/dev/null' );

	if ( ! empty( $ffmpeg_path ) ) {
		echo '✅ FFmpeg is available<br>';
		echo '   Path: ' . esc_html( trim( $ffmpeg_path ) ) . '<br>';
	} else {
		echo '⚠️  FFmpeg not found (optional)<br>';
		echo '   Install for better audio stitching: apt-get install ffmpeg<br>';
	}

	// Test 7: WordPress cron
	echo '<h3>7. WordPress Cron</h3>';
	$cron_hook = 'wpshadow_process_podcast_queue';
	$next_cron = wp_next_scheduled( $cron_hook );

	if ( $next_cron ) {
		$formatted = date( 'Y-m-d H:i:s', $next_cron );
		echo "✅ Cron scheduled for: $formatted<br>";
	} else {
		echo '⚠️  No cron scheduled (will be created on first generation)<br>';
	}

	// Test 8: Test API call
	echo '<h3>8. ElevenLabs API Test</h3>';
	if ( ! empty( $el_settings['api_key'] ) && ! empty( $el_settings['voice_id'] ) ) {
		$result = wpshadow_site_elevenlabs_tts( 'Test audio from WPShadow podcast system.' );

		if ( is_wp_error( $result ) ) {
			echo '❌ API test failed: ' . esc_html( $result->get_error_message() ) . '<br>';
			$all_pass = false;
		} else {
			echo '✅ API test passed (audio generated)<br>';
			echo '   Audio bytes: ' . strlen( $result['audio'] ) . ' bytes<br>';
		}
	} else {
		echo '⏭️  Skipped (API credentials not configured)<br>';
	}

	// Test 9: KB article detection
	echo '<h3>9. KB Article Detection</h3>';
	$kb_type = apply_filters( 'wpshadow_kb_article_post_type', 'kb_article' );
	$kb_articles = get_posts( array(
		'post_type' => $kb_type,
		'numberposts' => 5,
	) );

	if ( ! empty( $kb_articles ) ) {
		echo "✅ Found $kb_type articles: " . count( $kb_articles ) . '<br>';

		foreach ( $kb_articles as $article ) {
			$podcast_id = get_post_meta( $article->ID, '_wpshadow_podcast_id', true );
			$icon = $podcast_id ? '✅' : '⏳';
			echo "   $icon {$article->post_title} (ID: {$article->ID})<br>";
		}
	} else {
		echo "⚠️  No $kb_type articles found<br>";
	}

	// Test 10: Queue status
	echo '<h3>10. Queue Status</h3>';
	$statuses = array( 'pending' => '⏳', 'processing' => '🔄', 'completed' => '✅', 'failed' => '❌' );

	foreach ( $statuses as $status => $icon ) {
		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM $table WHERE status = %s",
			$status
		) );

		if ( $count > 0 ) {
			echo "$icon $status: $count<br>";
		}
	}

	// Summary
	echo '<h3>Summary</h3>';
	if ( $all_pass ) {
		echo '<div style="background: #d4edda; border: 1px solid #28a745; padding: 10px; color: #155724;">';
		echo '✅ All critical checks passed! System is ready.';
		echo '</div>';
	} else {
		echo '<div style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; color: #721c24;">';
		echo '❌ Some checks failed. See above for details.';
		echo '</div>';
	}

	echo '</div>';
}

/**
 * Manual test: Synthesize text with current settings
 */
function wpshadow_podcast_test_synthesize( $text = 'Hello from WPShadow podcast system' ) {
	echo '<div style="font-family: monospace; padding: 20px; background: #f5f5f5; border: 1px solid #ddd;">';
	echo '<h2>🎙️ Text-to-Speech Test</h2>';

	if ( ! function_exists( 'wpshadow_site_elevenlabs_tts' ) ) {
		echo '❌ ElevenLabs TTS function not found<br>';
		echo '</div>';
		return;
	}

	echo "Testing synthesis: \"$text\"<br><br>";

	$start = microtime( true );
	$result = wpshadow_site_elevenlabs_tts( $text );
	$elapsed = microtime( true ) - $start;

	if ( is_wp_error( $result ) ) {
		echo '❌ Synthesis failed: ' . esc_html( $result->get_error_message() ) . '<br>';
	} else {
		echo '✅ Synthesis successful<br>';
		echo 'Audio size: ' . strlen( $result['audio'] ) . ' bytes<br>';
		echo 'Content type: ' . esc_html( $result['content_type'] ) . '<br>';
		echo 'Time taken: ' . number_format( $elapsed, 2 ) . ' seconds<br>';

		// Save to test file
		$test_file = wp_upload_dir()['path'] . '/test_' . time() . '.mp3';
		if ( file_put_contents( $test_file, $result['audio'] ) ) {
			echo '<br>✅ Test file saved: ' . esc_html( basename( $test_file ) ) . '<br>';
		}
	}

	echo '</div>';
}

/**
 * Clean up test files
 */
function wpshadow_podcast_cleanup_tests() {
	$upload_dir = wp_upload_dir();
	$test_files = glob( $upload_dir['path'] . '/test_*.mp3' );

	foreach ( $test_files as $file ) {
		if ( file_exists( $file ) ) {
			unlink( $file );
		}
	}

	echo '<div style="padding: 20px;">';
	echo '✅ Cleaned up ' . count( $test_files ) . ' test files';
	echo '</div>';
}

// Example usage in WordPress:
// wpshadow_podcast_test_suite();
// wpshadow_podcast_test_synthesize();
// wpshadow_podcast_cleanup_tests();
