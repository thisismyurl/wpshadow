<?php
/**
 * Podcast Generator for KB Articles
 *
 * Handles automatic podcast generation when KB articles are published.
 * Manages audio synthesis, stitching, and storage.
 *
 * @package WPShadow_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPShadow_Podcast_Generator
 */
class WPShadow_Podcast_Generator {

	/**
	 * Podcast storage directory.
	 *
	 * @var string
	 */
	private $podcast_dir;

	/**
	 * Queue table name.
	 *
	 * @var string
	 */
	private $queue_table;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->queue_table = $wpdb->prefix . 'wpshadow_podcast_queue';
		$upload_dir         = wp_upload_dir();
		$this->podcast_dir  = $upload_dir['basedir'] . '/wpshadow-podcasts';

		$this->init_hooks();
		$this->create_podcast_dir();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Hook into post save for KB articles (adjust post type as needed).
		add_action( 'save_post', array( $this, 'queue_podcast_generation' ), 10, 2 );

		// Process queue asynchronously.
		add_action( 'wpshadow_process_podcast_queue', array( $this, 'process_queue_item' ) );

		// Admin notices.
		add_action( 'admin_notices', array( $this, 'render_podcast_status' ) );
	}

	/**
	 * Create podcast storage directory if it doesn't exist.
	 */
	private function create_podcast_dir() {
		if ( ! file_exists( $this->podcast_dir ) ) {
			wp_mkdir_p( $this->podcast_dir );
		}
	}

	/**
	 * Queue a podcast for generation when KB article is published.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function queue_podcast_generation( $post_id, $post ) {
		// Adjust 'kb_article' to match your KB post type.
		$kb_post_type = apply_filters( 'wpshadow_kb_article_post_type', 'kb_article' );

		if ( 'publish' !== $post->post_status || $kb_post_type !== $post->post_type ) {
			return;
		}

		// Check if podcast already exists for this article.
		$podcast_meta = get_post_meta( $post_id, '_wpshadow_podcast_id', true );
		if ( $podcast_meta ) {
			return; // Podcast already generated.
		}

		$this->add_to_queue( $post_id );
	}

	/**
	 * Add post to podcast generation queue.
	 *
	 * @param int $post_id Post ID.
	 */
	private function add_to_queue( $post_id ) {
		global $wpdb;

		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$this->queue_table} WHERE post_id = %d AND status = %s",
				$post_id,
				'pending'
			)
		);

		if ( $existing ) {
			return; // Already queued.
		}

		$wpdb->insert(
			$this->queue_table,
			array(
				'post_id'    => $post_id,
				'status'     => 'pending',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s' )
		);

		// Schedule single cron event if not already scheduled.
		if ( ! wp_next_scheduled( 'wpshadow_process_podcast_queue' ) ) {
			wp_schedule_single_event( time() + 30, 'wpshadow_process_podcast_queue' );
		}
	}

	/**
	 * Process a single queue item (triggered by cron or manual call).
	 *
	 * @param int $queue_id Optional. Specific queue item to process. If null, processes next pending item.
	 */
	public function process_queue_item( $queue_id = null ) {
		global $wpdb;

		if ( null === $queue_id ) {
			// Get the next pending item.
			$queue_item = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, post_id FROM {$this->queue_table} WHERE status = %s ORDER BY created_at ASC LIMIT 1",
					'pending'
				)
			);

			if ( ! $queue_item ) {
				return;
			}

			$queue_id = $queue_item->id;
			$post_id  = $queue_item->post_id;
		} else {
			$queue_item = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, post_id FROM {$this->queue_table} WHERE id = %d",
					$queue_id
				)
			);

			if ( ! $queue_item ) {
				return;
			}

			$post_id = $queue_item->post_id;
		}

		$this->update_queue_status( $queue_id, 'processing' );

		// Generate podcast.
		$result = $this->generate_podcast( $post_id );

		if ( is_wp_error( $result ) ) {
			$this->update_queue_status( $queue_id, 'failed', $result->get_error_message() );
			return;
		}

		$this->update_queue_status( $queue_id, 'completed' );
	}

	/**
	 * Update queue item status.
	 *
	 * @param int    $queue_id Queue item ID.
	 * @param string $status   Status (pending, processing, completed, failed).
	 * @param string $message  Optional. Error message or details.
	 */
	private function update_queue_status( $queue_id, $status, $message = '' ) {
		global $wpdb;

		$data = array(
			'status'     => $status,
			'updated_at' => current_time( 'mysql' ),
		);

		if ( $message ) {
			$data['error_message'] = $message;
		}

		$wpdb->update( $this->queue_table, $data, array( 'id' => $queue_id ), array( '%s', '%s', '%s' ), array( '%d' ) );
	}

	/**
	 * Generate podcast for a KB article.
	 *
	 * @param int $post_id Post ID.
	 * @return array|WP_Error Array with podcast_id on success, WP_Error on failure.
	 */
	private function generate_podcast( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post ) {
			return new WP_Error( 'wpshadow-podcast-post-not-found', 'Post not found.' );
		}

		// Get podcast settings.
		$settings = $this->get_podcast_settings();

		// Extract and process content.
		$title   = $post->post_title;
		$content = $this->extract_podcast_content( $post->post_content );

		if ( empty( $content ) ) {
			return new WP_Error( 'wpshadow-podcast-empty-content', 'No content to synthesize.' );
		}

		// Generate audio segments.
		$segments = array();

		// Intro segment.
		if ( ! empty( $settings['intro_audio_id'] ) ) {
			$intro_audio = $this->get_audio_file_path( $settings['intro_audio_id'] );
			if ( $intro_audio && file_exists( $intro_audio ) ) {
				$segments['intro'] = $intro_audio;
			}
		}

		// Title audio.
		if ( $settings['include_title'] ) {
			$title_audio = $this->synthesize_text( $title, $settings['title_voice_id'] );
			if ( ! is_wp_error( $title_audio ) ) {
				$segments['title'] = $title_audio;
			}
		}

		// Content audio.
		$content_audio = $this->synthesize_text( $content, $settings['content_voice_id'] );
		if ( is_wp_error( $content_audio ) ) {
			return $content_audio;
		}
		$segments['content'] = $content_audio;

		// Outro segment.
		if ( ! empty( $settings['outro_audio_id'] ) ) {
			$outro_audio = $this->get_audio_file_path( $settings['outro_audio_id'] );
			if ( $outro_audio && file_exists( $outro_audio ) ) {
				$segments['outro'] = $outro_audio;
			}
		}

		// Stitch segments together.
		$podcast_file = $this->stitch_audio_segments( $segments, $post_id );
		if ( is_wp_error( $podcast_file ) ) {
			return $podcast_file;
		}

		// Upload to media library.
		$podcast_id = $this->upload_podcast_to_media_library( $podcast_file, $post_id, $title );
		if ( is_wp_error( $podcast_id ) ) {
			return $podcast_id;
		}

		// Store metadata.
		update_post_meta( $post_id, '_wpshadow_podcast_id', $podcast_id );
		update_post_meta( $post_id, '_wpshadow_podcast_generated', current_time( 'mysql' ) );

		return array( 'podcast_id' => $podcast_id );
	}

	/**
	 * Get podcast generation settings.
	 *
	 * @return array
	 */
	private function get_podcast_settings() {
		$defaults = array(
			'enabled'            => false,
			'title_voice_id'     => '',
			'content_voice_id'   => '',
			'intro_audio_id'     => '',
			'outro_audio_id'     => '',
			'include_title'      => true,
			'auto_create_post'   => true,
			'post_type'          => 'post', // Optional: create a separate podcast post.
			'category'           => 'podcast',
		);

		$stored = get_option( 'wpshadow_podcast_settings', array() );

		return wp_parse_args( is_array( $stored ) ? $stored : array(), $defaults );
	}

	/**
	 * Extract and prepare content for podcast.
	 *
	 * @param string $content Post content.
	 * @return string Cleaned content.
	 */
	private function extract_podcast_content( $content ) {
		// Remove shortcodes.
		$content = preg_replace( '/\[.*?\]/', '', $content );

		// Strip HTML tags.
		$content = wp_strip_all_tags( $content );

		// Remove extra whitespace.
		$content = preg_replace( '/\s+/', ' ', $content );
		$content = trim( $content );

		// Limit to reasonable length (e.g., 5000 words to avoid API limits).
		$words   = explode( ' ', $content );
		$max_words = apply_filters( 'wpshadow_podcast_max_words', 5000 );
		if ( count( $words ) > $max_words ) {
			$words   = array_slice( $words, 0, $max_words );
			$content = implode( ' ', $words ) . '...';
		}

		return $content;
	}

	/**
	 * Synthesize text using ElevenLabs TTS.
	 *
	 * @param string $text     Text to synthesize.
	 * @param string $voice_id Voice ID.
	 * @return string|WP_Error Path to audio file on success, WP_Error on failure.
	 */
	private function synthesize_text( $text, $voice_id ) {
		if ( ! function_exists( 'wpshadow_site_elevenlabs_tts' ) ) {
			return new WP_Error( 'wpshadow-podcast-function-missing', 'ElevenLabs TTS function not found.' );
		}

		if ( empty( $voice_id ) ) {
			return new WP_Error( 'wpshadow-podcast-no-voice-id', 'Voice ID not provided.' );
		}

		$result = wpshadow_site_elevenlabs_tts(
			$text,
			array( 'voice_id' => $voice_id )
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Save audio to temporary file.
		$temp_file = $this->podcast_dir . '/' . 'temp_' . md5( $text . time() ) . '.mp3';

		$bytes = file_put_contents( $temp_file, $result['audio'] );
		if ( false === $bytes ) {
			return new WP_Error( 'wpshadow-podcast-save-failed', 'Failed to save synthesized audio.' );
		}

		return $temp_file;
	}

	/**
	 * Stitch audio segments together.
	 *
	 * @param array $segments Array of audio file paths keyed by segment name.
	 * @param int   $post_id  Post ID for naming.
	 * @return string|WP_Error Path to stitched audio file on success, WP_Error on failure.
	 */
	private function stitch_audio_segments( $segments, $post_id ) {
		if ( empty( $segments ) ) {
			return new WP_Error( 'wpshadow-podcast-no-segments', 'No audio segments provided.' );
		}

		// Check if FFmpeg is available.
		$ffmpeg = $this->find_ffmpeg();
		if ( ! $ffmpeg ) {
			// Fallback: concatenate as-is (may have audio gaps).
			return $this->fallback_concatenate_audio( $segments, $post_id );
		}

		return $this->ffmpeg_stitch_audio( $segments, $post_id, $ffmpeg );
	}

	/**
	 * Find FFmpeg executable path.
	 *
	 * @return string|false FFmpeg path or false if not found.
	 */
	private function find_ffmpeg() {
		$commands = array( 'ffmpeg', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg' );

		foreach ( $commands as $cmd ) {
			$output = shell_exec( "which $cmd 2>/dev/null" );
			if ( ! empty( $output ) ) {
				return trim( $output );
			}
		}

		return false;
	}

	/**
	 * Stitch audio using FFmpeg.
	 *
	 * @param array  $segments Array of audio file paths.
	 * @param int    $post_id  Post ID.
	 * @param string $ffmpeg   FFmpeg path.
	 * @return string|WP_Error Output file path or WP_Error.
	 */
	private function ffmpeg_stitch_audio( $segments, $post_id, $ffmpeg ) {
		// Create concat demuxer file.
		$concat_file = $this->podcast_dir . '/' . 'concat_' . $post_id . '_' . time() . '.txt';
		$concat_list = '';

		foreach ( $segments as $segment ) {
			$concat_list .= "file '" . str_replace( "'", "'\\''", $segment ) . "'\n";
		}

		$written = file_put_contents( $concat_file, $concat_list );
		if ( false === $written ) {
			return new WP_Error( 'wpshadow-podcast-concat-write', 'Failed to write concat file.' );
		}

		// Output file.
		$output_file = $this->podcast_dir . '/' . 'podcast_' . $post_id . '_' . time() . '.mp3';

		// Build FFmpeg command.
		$cmd = sprintf(
			'%s -f concat -safe 0 -i %s -c copy %s 2>&1',
			escapeshellarg( $ffmpeg ),
			escapeshellarg( $concat_file ),
			escapeshellarg( $output_file )
		);

		// Execute FFmpeg.
		$output = shell_exec( $cmd );

		// Clean up concat file.
		unlink( $concat_file );

		// Clean up temporary segment files.
		foreach ( $segments as $segment ) {
			if ( strpos( $segment, 'temp_' ) !== false && file_exists( $segment ) ) {
				unlink( $segment );
			}
		}

		if ( ! file_exists( $output_file ) ) {
			return new WP_Error(
				'wpshadow-podcast-ffmpeg-failed',
				'FFmpeg audio stitching failed: ' . $output
			);
		}

		return $output_file;
	}

	/**
	 * Fallback: Concatenate audio files without stitching (simple binary concatenation).
	 *
	 * @param array $segments Array of audio file paths.
	 * @param int   $post_id  Post ID.
	 * @return string|WP_Error Output file path or WP_Error.
	 */
	private function fallback_concatenate_audio( $segments, $post_id ) {
		$output_file = $this->podcast_dir . '/' . 'podcast_' . $post_id . '_' . time() . '.mp3';
		$handle      = fopen( $output_file, 'wb' );

		if ( ! $handle ) {
			return new WP_Error( 'wpshadow-podcast-open-file', 'Failed to open output file for writing.' );
		}

		foreach ( $segments as $segment ) {
			if ( file_exists( $segment ) ) {
				$content = file_get_contents( $segment );
				if ( false !== $content ) {
					fwrite( $handle, $content );
				}

				// Clean up temporary files.
				if ( strpos( $segment, 'temp_' ) !== false ) {
					unlink( $segment );
				}
			}
		}

		fclose( $handle );

		return $output_file;
	}

	/**
	 * Upload podcast to media library.
	 *
	 * @param string $file_path Path to podcast file.
	 * @param int    $post_id   Associated post ID.
	 * @param string $title     Podcast title.
	 * @return int|WP_Error Attachment ID on success, WP_Error on failure.
	 */
	private function upload_podcast_to_media_library( $file_path, $post_id, $title ) {
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'wpshadow-podcast-file-not-found', 'Podcast file not found.' );
		}

		// Prepare file for upload.
		$file_name = 'podcast_' . $post_id . '_' . wp_get_current_user()->ID . '.mp3';

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Move file to uploads directory.
		$upload_dir = wp_upload_dir();
		$dest_path  = $upload_dir['path'] . '/' . $file_name;

		if ( ! copy( $file_path, $dest_path ) ) {
			return new WP_Error( 'wpshadow-podcast-copy-failed', 'Failed to copy podcast file to uploads.' );
		}

		// Create attachment.
		$attachment = array(
			'post_mime_type' => 'audio/mpeg',
			'post_title'     => $title . ' - Podcast',
			'post_content'   => '',
			'post_status'    => 'inherit',
		);

		$attachment_id = wp_insert_attachment( $attachment, $dest_path, $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		// Generate attachment metadata.
		$attach_data = wp_generate_attachment_metadata( $attachment_id, $dest_path );
		wp_update_attachment_metadata( $attachment_id, $attach_data );

		return $attachment_id;
	}

	/**
	 * Get audio file path from attachment ID.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string|false File path or false if not found.
	 */
	private function get_audio_file_path( $attachment_id ) {
		$file = get_attached_file( $attachment_id );

		return $file ? $file : false;
	}

	/**
	 * Render podcast status in admin notices.
	 */
	public function render_podcast_status() {
		global $wpdb;

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$pending_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->queue_table} WHERE status = %s",
				'pending'
			)
		);

		$failed_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$this->queue_table} WHERE status = %s",
				'failed'
			)
		);

		if ( $pending_count > 0 ) {
			echo '<div class="notice notice-info"><p>';
			printf(
				'<strong>WPShadow:</strong> %d podcast(s) queued for generation.',
				intval( $pending_count )
			);
			echo '</p></div>';
		}

		if ( $failed_count > 0 ) {
			echo '<div class="notice notice-warning"><p>';
			printf(
				'<strong>WPShadow:</strong> %d podcast generation(s) failed. Check settings.',
				intval( $failed_count )
			);
			echo '</p></div>';
		}
	}

	/**
	 * Create podcast queue table.
	 */
	public static function create_queue_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wpshadow_podcast_queue';
		$charset    = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			error_message longtext,
			PRIMARY KEY (id),
			KEY post_id (post_id),
			KEY status (status)
		) $charset;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Drop podcast queue table on deactivation.
	 */
	public static function drop_queue_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'wpshadow_podcast_queue';
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
	}

	/**
	 * Trigger podcast queue processing via cron or admin action.
	 */
	public static function trigger_queue_processing() {
		if ( ! wp_next_scheduled( 'wpshadow_process_podcast_queue' ) ) {
			wp_schedule_single_event( time(), 'wpshadow_process_podcast_queue' );
		}
	}
}
