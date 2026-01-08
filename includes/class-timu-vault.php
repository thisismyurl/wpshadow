<?php
/**
 * Vault service for secure storage of originals.
 *
 * @package TIMU_CORE_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

use ZipArchive;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles ingest, integrity, and retrieval of originals into the Vault.
 */
class TIMU_Vault {

	private const OPTION_KEY             = 'timu_vault_settings';
	private const META_PATH              = '_timu_vault_path';
	private const META_MODE              = '_timu_vault_mode';
	private const META_HASH_RAW          = '_timu_vault_sha256_raw';
	private const META_HASH_STORE        = '_timu_vault_sha256_store';
	private const META_SIZE              = '_timu_vault_size';
	private const META_MIME              = '_timu_vault_mime';
	private const META_CREATED           = '_timu_vault_created';
	private const META_SIGNATURE         = '_timu_vault_signature';
	private const META_COMPRESSION       = '_timu_vault_compression';
	private const META_ENCRYPTED         = '_timu_vault_encrypted';
	private const META_KEY_ID            = '_timu_vault_key_id';
	private const META_JOURNAL           = '_timu_vault_journal';
	private const META_UPLOADER          = '_timu_vault_uploader_user_id';
	private const META_ANONYMIZED        = '_timu_vault_anonymized';
	private const DOWNLOAD_ACTION        = 'timu_vault_download';
	private const OPTION_ALLOW_OVERRIDE  = 'allow_site_override';
	private const KEY_ACTION             = 'timu_vault_key_action';
	private const ATTACHMENT_ACTION      = 'timu_vault_attachment_action';
	private const QUEUE_ACTION           = 'timu_vault_queue_action';
	private const QUEUE_OPTION           = 'timu_vault_queue_state';
	private const LOG_OPTION             = 'timu_vault_logs';
	private const LOG_LIMIT              = 50;
	private const LOG_MAX_ENTRIES        = 0; // Unlimited until manually cleared.
	private const LOG_RETENTION_DAYS     = 30;
	private const LEDGER_OPTION          = 'timu_vault_global_ledger';
	private const LEDGER_MAX_ENTRIES     = 10000;
	private const META_PENDING_REVIEW    = '_timu_pending_review';
	private const META_PENDING_OPTIMIZED = '_timu_pending_optimized';

	/**
	 * Bootstrap hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Ensure Vault directory exists and is protected before any ingest.
		self::ensure_vault_directory();
		add_action( 'add_attachment', array( __CLASS__, 'maybe_ingest_attachment' ), 20 );
		add_filter( 'wp_generate_attachment_metadata', array( __CLASS__, 'maybe_ingest_from_metadata' ), 20, 2 );
		add_action( 'admin_post_' . self::DOWNLOAD_ACTION, array( __CLASS__, 'handle_admin_download' ) );
		add_filter( 'wp_get_attachment_url', array( __CLASS__, 'maybe_rehydrate_on_url_request' ), 10, 2 );
		add_action( 'admin_post_' . self::ATTACHMENT_ACTION, array( __CLASS__, 'handle_attachment_action' ) );
		add_action( 'admin_post_' . self::QUEUE_ACTION, array( __CLASS__, 'handle_queue_action' ) );
		add_action( 'admin_post_' . self::KEY_ACTION, array( __CLASS__, 'handle_key_action' ) );
		// Logs actions: clear and export.
		add_action( 'admin_post_timu_vault_log_action', array( __CLASS__, 'maybe_handle_log_action' ) );
		add_action( 'admin_post_timu_vault_export_logs', array( __CLASS__, 'handle_export_logs' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'register_attachment_metabox' ) );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_render_notices' ) );
		add_action( 'timu_vault_queue_runner', array( __CLASS__, 'process_queue' ) );
		add_filter( 'manage_media_columns', array( __CLASS__, 'register_media_column' ) );
		add_action( 'manage_media_custom_column', array( __CLASS__, 'render_media_column' ), 10, 2 );
		// Content rewrites and 404 intercept.
		add_filter( 'the_content', array( __CLASS__, 'rewrite_vault_urls_in_content' ), 20 );
		add_action( 'template_redirect', array( __CLASS__, 'intercept_404_for_vault' ), 5 );

		if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( '\\WP_CLI' ) ) {
			\WP_CLI::add_command( 'timu vault rehydrate', array( __CLASS__, 'cli_rehydrate' ) );
			\WP_CLI::add_command( 'timu vault verify', array( __CLASS__, 'cli_verify' ) );
			\WP_CLI::add_command( 'timu vault status', array( __CLASS__, 'cli_status' ) );
			\WP_CLI::add_command( 'timu vault migrate', array( __CLASS__, 'cli_migrate' ) );
			\WP_CLI::add_command( 'timu vault erase-user-data', array( __CLASS__, 'cli_erase_user_data' ) );
		}
	}

	/**
	 * Handle settings form submission (site or network scope).
	 *
	 * @param bool $network Whether to save network-scoped settings.
	 * @return void
	 */
	public static function maybe_handle_settings_submission( bool $network = false ): void {
		if ( empty( $_POST['timu_vault_settings_nonce'] ) ) {
			return;
		}

		check_admin_referer( 'timu_vault_settings', 'timu_vault_settings_nonce' );

		// If network locks overrides, block site-level saves.
		if ( ! $network && ! self::site_override_allowed() ) {
			$redirect = add_query_arg( 'timu_vault_locked', '1', wp_get_referer() ?: admin_url() );
			wp_safe_redirect( $redirect );
			exit;
		}

		$enabled        = isset( $_POST['timu_vault_enabled'] ) && '1' === $_POST['timu_vault_enabled'];
		$mode           = isset( $_POST['timu_vault_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['timu_vault_mode'] ) ) : 'raw';
		$compression    = isset( $_POST['timu_vault_compression'] ) ? sanitize_text_field( wp_unslash( $_POST['timu_vault_compression'] ) ) : 'store';
		$download_ttl   = isset( $_POST['timu_vault_download_ttl'] ) ? (int) $_POST['timu_vault_download_ttl'] : 600;
		$encrypt        = isset( $_POST['timu_vault_encrypt'] ) && '1' === $_POST['timu_vault_encrypt'];
		$allow_override = isset( $_POST['timu_vault_allow_override'] ) && '1' === $_POST['timu_vault_allow_override'];

		$settings = array(
			'enabled'                   => $enabled,
			'mode'                      => $mode,
			'compression'               => $compression,
			'download_ttl'              => $download_ttl,
			'encrypt'                   => $encrypt,
			self::OPTION_ALLOW_OVERRIDE => $allow_override,
		);

		self::save_settings( $settings, $network );

		$redirect = add_query_arg( 'timu_vault_saved', '1', wp_get_referer() ?: admin_url() );
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle Vault tools (rehydrate/verify) submissions.
	 *
	 * @param bool $network Whether called from network admin.
	 * @return void
	 */
	public static function maybe_handle_tools_submission( bool $network = false ): void {
		if ( empty( $_POST['timu_vault_tools_nonce'] ) || empty( $_POST['timu_vault_tool_action'] ) ) {
			return;
		}

		check_admin_referer( 'timu_vault_tools', 'timu_vault_tools_nonce' );

		$action = sanitize_text_field( wp_unslash( $_POST['timu_vault_tool_action'] ) );
		$limit  = 25;

		if ( 'rehydrate_missing' === $action ) {
			$result = self::rehydrate_missing_attachments( $limit );
			if ( $result['fail'] > 0 ) {
				self::add_log( 'warning', 0, 'Rehydrate missing completed with failures: ' . $result['fail'] );
			}
			$redirect = add_query_arg(
				array(
					'timu_vault_tool' => 'rehydrate',
					'ok'              => $result['ok'],
					'fail'            => $result['fail'],
					'skipped'         => $result['skipped'],
				),
				wp_get_referer() ?: admin_url()
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		if ( 'verify_sample' === $action ) {
			$result = self::verify_sample( 10 );
			if ( $result['fail'] > 0 || $result['missing'] > 0 ) {
				self::add_log( 'warning', 0, 'Verify sample issues: fail ' . $result['fail'] . ' missing ' . $result['missing'] );
			}
			$redirect = add_query_arg(
				array(
					'timu_vault_tool' => 'verify',
					'ok'              => $result['ok'],
					'fail'            => $result['fail'],
					'missing'         => $result['missing'],
				),
				wp_get_referer() ?: admin_url()
			);
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Handle key management actions (rotate, re-encrypt sample) without CLI.
	 *
	 * @return void
	 */
	public static function handle_key_action(): void {
		if ( empty( $_POST['timu_vault_key_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['timu_vault_key_nonce'] ) ), self::KEY_ACTION ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		$cmd = isset( $_POST['timu_vault_key_cmd'] ) ? sanitize_text_field( wp_unslash( $_POST['timu_vault_key_cmd'] ) ) : '';
		if ( 'rotate' === $cmd ) {
			self::rotate_key();
			$redirect = add_query_arg( 'timu_vault_key_rotated', '1', wp_get_referer() ?: admin_url() );
			wp_safe_redirect( $redirect );
			exit;
		}

		if ( 'reencrypt_sample' === $cmd ) {
			$result   = self::batch_reencrypt_attachments( 25 );
			$redirect = add_query_arg(
				array(
					'timu_vault_reencrypt' => '1',
					'ok'                   => $result['ok'],
					'fail'                 => $result['fail'],
				),
				wp_get_referer() ?: admin_url()
			);
			wp_safe_redirect( $redirect );
			exit;
		}

		wp_safe_redirect( wp_get_referer() ?: admin_url() );
		exit;
	}

	/**
	 * Re-encrypt a batch of attachments.
	 *
	 * @param int $limit Number of items to process.
	 * @return array{ok:int,fail:int}
	 */
	private static function batch_reencrypt_attachments( int $limit ): array {
		$ok   = 0;
		$fail = 0;

		$q = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);

		foreach ( $q->posts as $attachment_id ) {
			$enc = (string) get_post_meta( $attachment_id, self::META_ENCRYPTED, true );
			if ( empty( $enc ) ) {
				continue; // Only re-encrypt items already encrypted.
			}
			$ok   = self::reencrypt_attachment( (int) $attachment_id ) ? ( $ok + 1 ) : $ok;
			$fail = ! self::reencrypt_attachment( (int) $attachment_id ) ? ( $fail + 1 ) : $fail;
		}

		return array(
			'ok'   => $ok,
			'fail' => $fail,
		);
	}

	/**
	 * Handle log clearing action from settings page.
	 *
	 * @return void
	 */
	public static function maybe_handle_log_action(): void {
		if ( empty( $_POST['timu_vault_log_nonce'] ) || empty( $_POST['timu_vault_log_action'] ) ) {
			return;
		}

		check_admin_referer( 'timu_vault_logs', 'timu_vault_log_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		$action = sanitize_text_field( wp_unslash( $_POST['timu_vault_log_action'] ) );

		if ( 'clear_all' === $action ) {
			self::clear_logs();
			$redirect = add_query_arg( 'timu_vault_logs_cleared', '1', wp_get_referer() ?: admin_url() );
			wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Attempt ingest when an attachment is created.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return void
	 */
	public static function maybe_ingest_attachment( int $attachment_id ): void {
			self::maybe_flag_pending_review( $attachment_id );

		if ( ! self::is_enabled() ) {
			return;
		}

		$post = get_post( $attachment_id );
		if ( ! $post || 'attachment' !== $post->post_type ) {
			return;
		}

		$source = get_attached_file( $attachment_id );
		if ( empty( $source ) || ! file_exists( $source ) ) {
			return;
		}

		$existing = get_post_meta( $attachment_id, self::META_PATH, true );
		if ( ! empty( $existing ) && file_exists( self::absolute_path( (string) $existing ) ) ) {
			return; // Already vaulted and present.
		}

		self::ingest( $attachment_id, $source );
	}

	/**
	 * Attempt ingest after metadata generation.
	 *
	 * @param array $metadata Attachment metadata.
	 * @param int   $attachment_id Attachment ID.
	 * @return array Metadata.
	 */
	public static function maybe_ingest_from_metadata( array $metadata, int $attachment_id ): array {
		self::maybe_ingest_attachment( $attachment_id );
		return $metadata;
	}

	/**
	 * Flag contributor uploads for editor review while keeping them optimized by default.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return void
	 */
	private static function maybe_flag_pending_review( int $attachment_id ): void {
		$post = get_post( $attachment_id );
		if ( ! $post || 'attachment' !== $post->post_type ) {
			return;
		}

		$user_id = (int) ( $post->post_author ?? 0 );
		if ( $user_id > 0 && user_can( $user_id, 'publish_posts' ) ) {
			return; // Editor+ uploads do not require review.
		}

		$already_pending = (string) get_post_meta( $attachment_id, self::META_PENDING_REVIEW, true );
		if ( '1' === $already_pending ) {
			return;
		}

		update_post_meta( $attachment_id, self::META_PENDING_REVIEW, '1' );
		update_post_meta( $attachment_id, self::META_PENDING_OPTIMIZED, '1' );

		$file      = wp_basename( (string) get_attached_file( $attachment_id ) );
		$user      = get_user_by( 'id', $user_id );
		$user_name = $user && $user->exists() ? $user->display_name : __( 'Unknown', 'core-support-thisismyurl' );

		self::add_log(
			'info',
			$attachment_id,
			'Pending contributor upload queued for review.',
			'upload_review',
			array(
				'task'    => 'pending_upload',
				'file'    => $file,
				'user'    => $user_name,
				'user_id' => $user_id,
			)
		);
	}

	/**
	 * Handle secure download of a vaulted file for admins.
	 *
	 * @return void
	 */
	public static function handle_admin_download(): void {
		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			wp_die(
				esc_html__( 'Insufficient permissions to access Vault files.', 'core-support-thisismyurl' ),
				esc_html__( 'Vault Access Denied', 'core-support-thisismyurl' ),
				array( 'response' => 403 )
			);
		}

		$attachment_id = isset( $_GET['attachment_id'] ) ? (int) wp_unslash( $_GET['attachment_id'] ) : 0;
		$expires       = isset( $_GET['expires'] ) ? (int) wp_unslash( $_GET['expires'] ) : 0;
		$token         = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

		if ( $attachment_id <= 0 || $expires <= time() ) {
			wp_die(
				esc_html__( 'Vault link expired or invalid.', 'core-support-thisismyurl' ),
				esc_html__( 'Vault Link Invalid', 'core-support-thisismyurl' ),
				array( 'response' => 400 )
			);
		}

		$path = (string) get_post_meta( $attachment_id, self::META_PATH, true );
		if ( empty( $path ) ) {
			wp_die(
				esc_html__( 'Vault record missing.', 'core-support-thisismyurl' ),
				esc_html__( 'Vault Record Missing', 'core-support-thisismyurl' ),
				array( 'response' => 404 )
			);
		}

		if ( ! self::verify_token( $attachment_id, $path, $expires, $token ) ) {
			wp_die(
				esc_html__( 'Invalid Vault token.', 'core-support-thisismyurl' ),
				esc_html__( 'Vault Token Invalid', 'core-support-thisismyurl' ),
				array( 'response' => 403 )
			);
		}

		$absolute = self::absolute_path( $path );
		if ( empty( $absolute ) || ! file_exists( $absolute ) ) {
			wp_die(
				esc_html__( 'Vault file not found.', 'core-support-thisismyurl' ),
				esc_html__( 'Vault File Missing', 'core-support-thisismyurl' ),
				array( 'response' => 404 )
			);
		}

		$expected_store_hash = (string) get_post_meta( $attachment_id, self::META_HASH_STORE, true );
		if ( $expected_store_hash ) {
			$current_store_hash = hash_file( 'sha256', $absolute ) ?: '';
			if ( $current_store_hash && ! hash_equals( $expected_store_hash, $current_store_hash ) ) {
				wp_die(
					esc_html__( 'Vault integrity check failed.', 'core-support-thisismyurl' ),
					esc_html__( 'Vault Integrity Failed', 'core-support-thisismyurl' ),
					array( 'response' => 409 )
				);
			}
		}

		self::stream_file( $absolute, basename( $absolute ) );
		exit;
	}

	/**
	 * Attempt on-demand rehydrate when a URL is requested and the file is missing.
	 *
	 * @param string|false $url Existing URL.
	 * @param int          $attachment_id Attachment ID.
	 * @return string|false
	 */
	public static function maybe_rehydrate_on_url_request( $url, int $attachment_id ) {
		if ( ! self::is_enabled() || $attachment_id <= 0 ) {
			return $url;
		}

		$file = get_attached_file( $attachment_id );
		if ( empty( $file ) ) {
			return $url;
		}

		if ( file_exists( $file ) ) {
			return $url; // All good.
		}

		$rehydrated = self::rehydrate( $attachment_id );
		if ( ! $rehydrated ) {
			return $url;
		}

		// Compute URL manually to avoid recursion.
		$uploads = wp_get_upload_dir();
		if ( empty( $uploads['basedir'] ) || empty( $uploads['baseurl'] ) ) {
			return $url;
		}

		$relative = ltrim( str_replace( $uploads['basedir'], '', $file ), '/\\' );
		return trailingslashit( $uploads['baseurl'] ) . $relative;
	}

	/**
	 * Register Vault metabox on attachment edit screen.
	 *
	 * @return void
	 */
	public static function register_attachment_metabox(): void {
		add_meta_box(
			'timu-vault-meta',
			__( 'Vault Status', 'core-support-thisismyurl' ),
			array( __CLASS__, 'render_attachment_metabox' ),
			'attachment',
			'side',
			'high'
		);
	}

	/**
	 * Render Vault metabox.
	 *
	 * @param \WP_Post $post Attachment post.
	 * @return void
	 */
	public static function render_attachment_metabox( \WP_Post $post ): void {
		$path        = (string) get_post_meta( $post->ID, self::META_PATH, true );
		$mode        = (string) get_post_meta( $post->ID, self::META_MODE, true );
		$compression = (string) get_post_meta( $post->ID, self::META_COMPRESSION, true );
		$size        = (int) get_post_meta( $post->ID, self::META_SIZE, true );
		$created     = (int) get_post_meta( $post->ID, self::META_CREATED, true );
		$hash_store  = (string) get_post_meta( $post->ID, self::META_HASH_STORE, true );
		$hash_raw    = (string) get_post_meta( $post->ID, self::META_HASH_RAW, true );
		$has_vault   = ! empty( $path );

		$locked = ! self::is_enabled();
		?>
		<p><strong><?php echo esc_html__( 'Status:', 'core-support-thisismyurl' ); ?></strong> <?php echo esc_html( $has_vault ? __( 'Stored in Vault', 'core-support-thisismyurl' ) : __( 'Not vaulted', 'core-support-thisismyurl' ) ); ?></p>
		<p>
			<strong><?php echo esc_html__( 'Mode:', 'core-support-thisismyurl' ); ?></strong>
			<?php echo esc_html( $mode ?: 'raw' ); ?>
			<?php if ( 'zip' === $mode && $compression ) : ?>
				<span style="display:block;color:#646970;"><?php echo esc_html( sprintf( __( 'Compression: %s', 'core-support-thisismyurl' ), $compression ) ); ?></span>
			<?php endif; ?>
		</p>
		<?php if ( $has_vault ) : ?>
			<p><strong><?php echo esc_html__( 'Path:', 'core-support-thisismyurl' ); ?></strong><br /><?php echo esc_html( $path ); ?></p>
			<p><strong><?php echo esc_html__( 'Size:', 'core-support-thisismyurl' ); ?></strong> <?php echo esc_html( size_format( max( $size, 0 ) ) ); ?></p>
			<?php if ( $created ) : ?>
				<p><strong><?php echo esc_html__( 'Stored:', 'core-support-thisismyurl' ); ?></strong> <?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $created ) ); ?></p>
			<?php endif; ?>
			<p><strong><?php echo esc_html__( 'Hashes:', 'core-support-thisismyurl' ); ?></strong><br />
				<span style="display:block;word-break:break-all;">Store: <?php echo esc_html( substr( $hash_store, 0, 16 ) ); ?>…</span>
				<span style="display:block;word-break:break-all;">Raw: <?php echo esc_html( substr( $hash_raw, 0, 16 ) ); ?>…</span>
			</p>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( self::ATTACHMENT_ACTION, 'timu_vault_attachment_nonce' ); ?>
			<input type="hidden" name="action" value="<?php echo esc_attr( self::ATTACHMENT_ACTION ); ?>" />
			<input type="hidden" name="attachment_id" value="<?php echo esc_attr( (string) $post->ID ); ?>" />
			<p>
				<button class="button" type="submit" name="timu_vault_attachment_cmd" value="rehydrate" <?php disabled( $locked ); ?>><?php echo esc_html__( 'Rehydrate', 'core-support-thisismyurl' ); ?></button>
			</p>
			<p>
				<button class="button" type="submit" name="timu_vault_attachment_cmd" value="verify" <?php disabled( $locked ); ?>><?php echo esc_html__( 'Verify', 'core-support-thisismyurl' ); ?></button>
			</p>
		</form>
		<?php
	}

	/**
	 * Handle attachment-level actions (rehydrate/verify).
	 *
	 * @return void
	 */
	public static function handle_attachment_action(): void {
		$cmd           = isset( $_POST['timu_vault_attachment_cmd'] ) ? sanitize_text_field( wp_unslash( $_POST['timu_vault_attachment_cmd'] ) ) : '';
		$attachment_id = isset( $_POST['attachment_id'] ) ? (int) $_POST['attachment_id'] : 0;

		if ( empty( $_POST['timu_vault_attachment_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['timu_vault_attachment_nonce'] ) ), self::ATTACHMENT_ACTION ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		if ( $attachment_id <= 0 || ! current_user_can( 'upload_files' ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		$result = array(
			'ok'      => 0,
			'fail'    => 0,
			'missing' => 0,
		);

		if ( 'rehydrate' === $cmd ) {
			$rehydrated     = self::rehydrate( $attachment_id );
			$result['ok']   = $rehydrated ? 1 : 0;
			$result['fail'] = $rehydrated ? 0 : 1;
			if ( ! $rehydrated ) {
				self::add_log( 'error', $attachment_id, 'Rehydrate failed from metabox action.' );
			}
		}

		if ( 'verify' === $cmd ) {
			$verify = self::verify_attachment_integrity( $attachment_id );
			if ( 'ok' === $verify['status'] ) {
				$result['ok'] = 1;
			} elseif ( 'missing' === $verify['status'] ) {
				$result['missing'] = 1;
				self::add_log( 'warning', $attachment_id, 'Vault file missing during attachment verify.' );
			} else {
				$result['fail'] = 1;
				self::add_log( 'error', $attachment_id, $verify['reason'] );
			}
		}

		$redirect = add_query_arg(
			array(
				'timu_vault_attachment' => $cmd,
				'ok'                    => $result['ok'],
				'fail'                  => $result['fail'],
				'missing'               => $result['missing'],
			),
			wp_get_referer() ?: admin_url()
		);

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Display admin notices for Vault actions.
	 *
	 * @return void
	 */
	public static function maybe_render_notices(): void {
		$action = isset( $_GET['timu_vault_attachment'] ) ? sanitize_text_field( wp_unslash( $_GET['timu_vault_attachment'] ) ) : '';
		$queue  = self::get_queue_state();

		if ( ! empty( $queue ) && ( $queue['status'] ?? '' ) === 'running' ) {
			printf(
				'<div class="notice notice-info"><p>%s</p></div>',
				esc_html(
					sprintf(
						__( 'Vault job running: %1$s. Processed %2$d of %3$s. OK %4$d, Fail %5$d, Missing %6$d, Skipped %7$d.', 'core-support-thisismyurl' ),
						$queue['type'],
						(int) ( $queue['processed'] ?? 0 ),
						( $queue['total'] ?? '∞' ),
						(int) ( $queue['ok'] ?? 0 ),
						(int) ( $queue['fail'] ?? 0 ),
						(int) ( $queue['missing'] ?? 0 ),
						(int) ( $queue['skipped'] ?? 0 )
					)
				)
			);
		}

		// Completion notice when a job has finished.
		if ( ! empty( $queue ) && ( $queue['status'] ?? '' ) === 'done' ) {
			$settings_url = is_network_admin() ? network_admin_url( 'admin.php?page=timu-core-network-settings' ) : admin_url( 'admin.php?page=timu-core-settings' );
			$summary      = sprintf(
				__( 'Vault job finished: %1$s. OK %2$d, Fail %3$d, Missing %4$d, Skipped %5$d.', 'core-support-thisismyurl' ),
				$queue['type'],
				(int) ( $queue['ok'] ?? 0 ),
				(int) ( $queue['fail'] ?? 0 ),
				(int) ( $queue['missing'] ?? 0 ),
				(int) ( $queue['skipped'] ?? 0 )
			);
			printf(
				'<div class="notice notice-success is-dismissible"><p>%1$s <a href="%2$s">%3$s</a></p></div>',
				esc_html( $summary ),
				esc_url( $settings_url ),
				esc_html__( 'View details', 'core-support-thisismyurl' )
			);
		}

		if ( empty( $action ) ) {
			return;
		}

		$ok      = isset( $_GET['ok'] ) ? (int) $_GET['ok'] : 0;
		$fail    = isset( $_GET['fail'] ) ? (int) $_GET['fail'] : 0;
		$missing = isset( $_GET['missing'] ) ? (int) $_GET['missing'] : 0;

		if ( 'rehydrate' === $action ) {
			printf(
				'<div class="notice notice-info is-dismissible"><p>%s</p></div>',
				esc_html( sprintf( __( 'Vault rehydrate: restored %1$d, failed %2$d.', 'core-support-thisismyurl' ), $ok, $fail ) )
			);
			return;
		}

		if ( 'verify' === $action ) {
			printf(
				'<div class="notice notice-info is-dismissible"><p>%s</p></div>',
				esc_html( sprintf( __( 'Vault verify: OK %1$d, Failed %2$d, Missing %3$d.', 'core-support-thisismyurl' ), $ok, $fail, $missing ) )
			);
		}
	}

	/**
	 * Rehydrate a missing attachment from the Vault.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool True on success.
	 */
	public static function rehydrate( int $attachment_id ): bool {
		$path = (string) get_post_meta( $attachment_id, self::META_PATH, true );
		if ( empty( $path ) ) {
			return false;
		}

		$absolute = self::absolute_path( $path );
		if ( empty( $absolute ) || ! file_exists( $absolute ) ) {
			return false;
		}

		$mode         = (string) get_post_meta( $attachment_id, self::META_MODE, true );
		$target       = get_attached_file( $attachment_id );
		$raw_hash     = (string) get_post_meta( $attachment_id, self::META_HASH_RAW, true );
		$mime_type    = (string) get_post_meta( $attachment_id, self::META_MIME, true );
		$enc_algo     = (string) get_post_meta( $attachment_id, self::META_ENCRYPTED, true );
		$is_encrypted = ! empty( $enc_algo );

		if ( empty( $target ) ) {
			return false;
		}

		// If encrypted, decrypt first.
		$work_path = $absolute;
		if ( $is_encrypted && extension_loaded( 'openssl' ) ) {
			$key = self::select_key_for_attachment( $attachment_id );
			if ( ! $key ) {
				error_log( 'TIMU Vault: Encryption key not available for decryption.' );
				return false;
			}

			$temp_path = wp_tempnam( 'timu_decrypt_' );
			if ( ! $temp_path ) {
				return false;
			}

			// Decrypt to temp file.
			$plaintext = self::decrypt_file( $absolute, $key );
			if ( false === $plaintext ) {
				unlink( $temp_path );
				error_log( 'TIMU Vault: Failed to decrypt vault file.' );
				return false;
			}

			if ( false === file_put_contents( $temp_path, $plaintext, LOCK_EX ) ) {
				unlink( $temp_path );
				return false;
			}

			$work_path = $temp_path;
		}

		if ( 'zip' === $mode ) {
			$result = self::extract_zip_to_path( $work_path, $target, $raw_hash );
		} else {
			$result = self::copy_raw_to_path( $work_path, $target, $raw_hash );
		}

		// Clean up temp decrypted file if needed.
		if ( $is_encrypted && $work_path !== $absolute && file_exists( $work_path ) ) {
			unlink( $work_path );
		}

		// Journal entry if successful.
		if ( $result ) {
			self::add_journal_entry(
				$attachment_id,
				array(
					'op'          => 'rehydrate',
					'args'        => array(
						'mode'      => $mode,
						'encrypted' => $is_encrypted,
					),
					'before_hash' => null,
					'after_hash'  => $raw_hash,
				)
			);
		}

		return $result;
	}

	/**
	 * Generate a signed, expiring download URL.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string URL or empty string.
	 */
	public static function get_download_url( int $attachment_id ): string {
		$settings = self::get_settings();
		$ttl      = (int) ( $settings['download_ttl'] ?? 600 );
		$expires  = time() + max( 60, $ttl );
		$path     = (string) get_post_meta( $attachment_id, self::META_PATH, true );

		if ( empty( $path ) ) {
			return '';
		}

		$token = self::build_token( $attachment_id, $path, $expires );
		if ( empty( $token ) ) {
			return '';
		}

		return add_query_arg(
			array(
				'action'        => self::DOWNLOAD_ACTION,
				'attachment_id' => $attachment_id,
				'expires'       => $expires,
				'token'         => $token,
			),
			admin_url( 'admin-post.php' )
		);
	}

	/**
	 * Persist an attachment into the Vault.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $source_path   Path to the original file.
	 * @return void
	 */
	private static function ingest( int $attachment_id, string $source_path ): void {
		$settings = self::get_settings();
		$mode     = self::resolve_mode( $settings['mode'] ?? 'raw' );
		$uploads  = wp_upload_dir();
		// Ensure vault directory is initialized and protected.
		self::ensure_vault_directory();
		$vault_dirname = (string) get_option( 'timu_vault_dirname' );
		$vault         = trailingslashit( $uploads['basedir'] ) . $vault_dirname;
		$subdir        = gmdate( 'Y/m' );
		$dest_dir      = trailingslashit( $vault ) . $subdir;

		if ( ! wp_mkdir_p( $dest_dir ) ) {
			error_log( 'TIMU Vault: Unable to create directory ' . $dest_dir );
			return;
		}

		$raw_hash = hash_file( 'sha256', $source_path ) ?: '';
		$ext      = pathinfo( $source_path, PATHINFO_EXTENSION );
		$base     = $raw_hash ?: uniqid( 'timu_vault_', true );

		$dest_path  = $dest_dir . $base . ( 'zip' === $mode ? '.zip' : ( $ext ? '.' . strtolower( (string) $ext ) : '' ) );
		$mime_type  = get_post_mime_type( $attachment_id ) ?: ( $ext ? wp_get_mime_types()[ strtolower( $ext ) ] ?? '' : '' );
		$start_time = time();

		if ( 'zip' === $mode && class_exists( ZipArchive::class ) ) {
			$compression = self::resolve_compression( $settings );
			$written     = self::write_zip( $source_path, $dest_path, $compression );
			$store_hash  = $written ? ( hash_file( 'sha256', $dest_path ) ?: '' ) : '';
		} else {
			$mode        = 'raw';
			$compression = 'none';
			$written     = self::write_raw( $source_path, $dest_path );
			$store_hash  = $written ? ( hash_file( 'sha256', $dest_path ) ?: '' ) : '';
		}

		if ( ! $written ) {
			return;
		}

		// Apply encryption if enabled and supported.
		$encrypt = isset( $settings['encrypt'] ) ? (bool) $settings['encrypt'] : false;
		if ( $encrypt && extension_loaded( 'openssl' ) ) {
			$key_info = self::get_encryption_key_info();
			if ( $key_info['key'] ) {
				self::encrypt_file_gcm( $dest_path, $key_info['key'] );
				update_post_meta( $attachment_id, self::META_ENCRYPTED, 'gcm' );
				update_post_meta( $attachment_id, self::META_KEY_ID, $key_info['id'] );
			}
		}

		$relative_path = ltrim( str_replace( $uploads['basedir'], '', $dest_path ), '/\\' );
		$signature     = self::sign_hash( $attachment_id, $raw_hash );

		update_post_meta( $attachment_id, self::META_PATH, $relative_path );
		update_post_meta( $attachment_id, self::META_MODE, $mode );
		update_post_meta( $attachment_id, self::META_HASH_RAW, $raw_hash );
		update_post_meta( $attachment_id, self::META_HASH_STORE, $store_hash );
		update_post_meta( $attachment_id, self::META_SIZE, filesize( $dest_path ) ?: 0 );
		update_post_meta( $attachment_id, self::META_MIME, $mime_type );
		update_post_meta( $attachment_id, self::META_CREATED, $start_time );
		update_post_meta( $attachment_id, self::META_SIGNATURE, $signature );
		update_post_meta( $attachment_id, self::META_COMPRESSION, $compression );
		// Record uploader for privacy erasure mapping.
		$uploader = get_current_user_id();
		update_post_meta( $attachment_id, self::META_UPLOADER, (int) $uploader );

		// Journal entry.
		self::add_journal_entry(
			$attachment_id,
			array(
				'op'          => 'ingest',
				'args'        => array(
					'mode'        => $mode,
					'encrypt'     => $encrypt,
					'key_id'      => $encrypt ? ( $key_info['id'] ?? 0 ) : null,
					'compression' => $compression,
				),
				'before_hash' => null,
				'after_hash'  => $raw_hash,
			)
		);
	}

	/**
	 * Strip EXIF/metadata from a single image file.
	 *
	 * @param string $file Absolute path.
	 * @return bool True when stripped or not applicable, false on failure.
	 */
	private static function strip_exif_from_file( string $file ): bool {
		if ( ! file_exists( $file ) ) {
			return false;
		}

		$ext = strtolower( (string) pathinfo( $file, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, array( 'jpg', 'jpeg', 'png', 'webp' ), true ) ) {
			// Non-image or unsupported format; treat as OK (no EXIF).
			return true;
		}

		// Prefer Imagick.
		if ( class_exists( '\\Imagick' ) ) {
			try {
				$img = new \Imagick( $file );
				$img->stripImage();
				$ok = $img->writeImage( $file );
				$img->clear();
				$img->destroy();
				return (bool) $ok;
			} catch ( \Throwable $e ) {
				return false;
			}
		}

		// Fallback: GD re-encode to drop metadata.
		$resource = null;
		if ( in_array( $ext, array( 'jpg', 'jpeg' ), true ) && function_exists( 'imagecreatefromjpeg' ) ) {
			$resource = @imagecreatefromjpeg( $file );
			if ( ! $resource ) {
				return false;
			}
			$ok = @imagejpeg( $resource, $file, 90 );
			imagedestroy( $resource );
			return (bool) $ok;
		}

		if ( 'png' === $ext && function_exists( 'imagecreatefrompng' ) ) {
			$resource = @imagecreatefrompng( $file );
			if ( ! $resource ) {
				return false;
			}
			// Force no ancillary chunks; GD save drops most metadata.
			$ok = @imagepng( $resource, $file );
			imagedestroy( $resource );
			return (bool) $ok;
		}

		if ( 'webp' === $ext && function_exists( 'imagecreatefromwebp' ) ) {
			$resource = @imagecreatefromwebp( $file );
			if ( ! $resource ) {
				return false;
			}
			$ok = @imagewebp( $resource, $file, 80 );
			imagedestroy( $resource );
			return (bool) $ok;
		}

		return true;
	}

	/**
	 * Strip EXIF/metadata from attachment and its derivatives.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array{ok:int,fail:int}
	 */
	private static function strip_exif_from_attachment( int $attachment_id ): array {
		$ok   = 0;
		$fail = 0;

		$main = get_attached_file( $attachment_id );
		if ( ! empty( $main ) && file_exists( $main ) ) {
			self::strip_exif_from_file( $main ) ? $ok++ : $fail++;
		}

		$meta = wp_get_attachment_metadata( $attachment_id );
		if ( is_array( $meta ) && ! empty( $meta['sizes'] ) ) {
			$uploads = wp_get_upload_dir();
			$base    = trailingslashit( $uploads['basedir'] );
			$folder  = trailingslashit( dirname( (string) $meta['file'] ) );
			foreach ( (array) $meta['sizes'] as $size ) {
				$path = $base . $folder . ( $size['file'] ?? '' );
				if ( ! empty( $path ) && file_exists( $path ) ) {
					self::strip_exif_from_file( $path ) ? $ok++ : $fail++;
				}
			}
		}

		return array(
			'ok'   => $ok,
			'fail' => $fail,
		);
	}

	/**
	 * Anonymize a single attachment: scrub uploader meta and journal user IDs; strip EXIF on derivatives.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @param int $user_id Target user to anonymize.
	 * @return bool Success.
	 */
	public static function anonymize_attachment( int $attachment_id, int $user_id ): bool {
		if ( $attachment_id < 1 || $user_id < 1 ) {
			return false;
		}

		// Strip EXIF from public derivatives.
		self::strip_exif_from_attachment( $attachment_id );

		// Scrub uploader mapping if matches.
		$uploader = (int) get_post_meta( $attachment_id, self::META_UPLOADER, true );
		if ( $uploader === $user_id ) {
			update_post_meta( $attachment_id, self::META_UPLOADER, 0 );
		}

		// Scrub journal user IDs.
		$journal = get_post_meta( $attachment_id, self::META_JOURNAL, true );
		if ( is_array( $journal ) && ! empty( $journal['operations'] ) ) {
			$changed = false;
			foreach ( $journal['operations'] as &$op ) {
				if ( isset( $op['user_id'] ) && (int) $op['user_id'] === $user_id ) {
					$op['user_id'] = 0; // anonymized
					$changed       = true;
				}
			}
			unset( $op );
			if ( $changed ) {
				update_post_meta( $attachment_id, self::META_JOURNAL, $journal );
			}
		}

		// Mark anonymized.
		update_post_meta( $attachment_id, self::META_ANONYMIZED, (string) gmdate( 'Y-m-d\TH:i:s\Z' ) );

		// Ledger entry.
		self::add_ledger_entry(
			array(
				'attachment_id' => $attachment_id,
				'op'            => 'erase_personal_data',
				'user_id'       => $user_id,
				'success'       => true,
			)
		);

		return true;
	}

	/**
	 * Batch anonymize attachments for a given user.
	 *
	 * @param int $user_id User ID.
	 * @param int $page    Page for batching (1-indexed).
	 * @param int $per_page Items per batch.
	 * @return array{items_removed:int,items_retained:int,messages:array,done:bool}
	 */
	public static function erase_user_personal_data( int $user_id, int $page = 1, int $per_page = 50 ): array {
		$items_removed  = 0; // count of personal-data elements scrubbed
		$items_retained = 0; // originals retained by policy
		$messages       = array();

		if ( $user_id < 1 ) {
			return array(
				'items_removed'  => 0,
				'items_retained' => 0,
				'messages'       => array( __( 'Invalid user.', 'core-support-thisismyurl' ) ),
				'done'           => true,
			);
		}

		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => max( 1, $per_page ),
				'paged'          => max( 1, $page ),
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => self::META_UPLOADER,
						'value'   => $user_id,
						'compare' => '=',
						'type'    => 'NUMERIC',
					),
				),
			)
		);

		foreach ( (array) $query->posts as $attachment_id ) {
			$ok = self::anonymize_attachment( (int) $attachment_id, $user_id );
			if ( $ok ) {
				++$items_removed;
				$messages[] = sprintf( /* translators: 1: attachment ID */ __( 'Anonymized attachment #%1$d.', 'core-support-thisismyurl' ), (int) $attachment_id );
			} else {
				++$items_retained;
				$messages[] = sprintf( /* translators: 1: attachment ID */ __( 'Failed to anonymize attachment #%1$d.', 'core-support-thisismyurl' ), (int) $attachment_id );
			}
		}

		$done = ( $query->post_count < $per_page );

		return array(
			'items_removed'  => $items_removed,
			'items_retained' => $items_retained,
			'messages'       => $messages,
			'done'           => (bool) $done,
		);
	}

	/**
	 * Write a raw copy into the Vault.
	 *
	 * @param string $source Source file.
	 * @param string $destination Destination file.
	 * @param string $expected_hash Expected SHA-256 of original.
	 * @return bool
	 */
	private static function copy_raw_to_path( string $source, string $destination, string $expected_hash ): bool {
		$dir = dirname( $destination );
		if ( ! wp_mkdir_p( $dir ) ) {
			return false;
		}

		if ( ! copy( $source, $destination ) ) {
			return false;
		}

		if ( $expected_hash ) {
			$current_hash = hash_file( 'sha256', $destination ) ?: '';
			if ( $current_hash && ! hash_equals( $expected_hash, $current_hash ) ) {
				unlink( $destination );
				return false;
			}
		}

		return true;
	}

	/**
	 * Extract a zip into the target path with hash verification.
	 *
	 * @param string $zip_path Vault zip path.
	 * @param string $destination Target file path.
	 * @param string $expected_hash Expected SHA-256 of the raw original.
	 * @return bool
	 */
	private static function extract_zip_to_path( string $zip_path, string $destination, string $expected_hash ): bool {
		if ( ! class_exists( ZipArchive::class ) ) {
			return false;
		}

		$temp_dir = wp_tempnam( 'timu_vault_extract_' );
		if ( ! $temp_dir ) {
			return false;
		}

		// wp_tempnam() creates a file; we need a directory.
		if ( file_exists( $temp_dir ) ) {
			unlink( $temp_dir );
		}
		if ( ! wp_mkdir_p( $temp_dir ) ) {
			return false;
		}

		$zip = new ZipArchive();
		if ( true !== $zip->open( $zip_path ) ) {
			return false;
		}

		if ( ! $zip->extractTo( $temp_dir ) ) {
			$zip->close();
			return false;
		}

		$zip->close();

		$files = glob( trailingslashit( $temp_dir ) . '*' );
		if ( empty( $files ) ) {
			return false;
		}

		$extracted = $files[0];
		if ( $expected_hash ) {
			$current_hash = hash_file( 'sha256', $extracted ) ?: '';
			if ( $current_hash && ! hash_equals( $expected_hash, $current_hash ) ) {
				self::cleanup_path( $temp_dir );
				return false;
			}
		}

		$dir = dirname( $destination );
		if ( ! wp_mkdir_p( $dir ) ) {
			self::cleanup_path( $temp_dir );
			return false;
		}

		$copied = copy( $extracted, $destination );
		self::cleanup_path( $temp_dir );

		return (bool) $copied;
	}

	/**
	 * Write a zip archive for the source file.
	 *
	 * @param string $source Source file path.
	 * @param string $destination Destination zip path.
	 * @param int    $compression ZipArchive compression constant.
	 * @return bool
	 */
	private static function write_zip( string $source, string $destination, int $compression ): bool {
		$zip = new ZipArchive();
		if ( true !== $zip->open( $destination, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
			return false;
		}

		$local_name = basename( $source );
		if ( ! $zip->addFile( $source, $local_name ) ) {
			$zip->close();
			return false;
		}

		if ( method_exists( $zip, 'setCompressionName' ) ) {
			$zip->setCompressionName( $local_name, $compression );
		}

		if ( method_exists( $zip, 'setMtimeName' ) ) {
			$zip->setMtimeName( $local_name, filemtime( $source ) ?: time() );
		}

		return (bool) $zip->close();
	}

	/**
	 * Write a raw copy.
	 *
	 * @param string $source Source file path.
	 * @param string $destination Destination path.
	 * @return bool
	 */
	private static function write_raw( string $source, string $destination ): bool {
		return (bool) copy( $source, $destination );
	}

	/**
	 * Build a signed token.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $path Relative path stored in meta.
	 * @param int    $expires Expiry timestamp.
	 * @return string Token.
	 */
	private static function build_token( int $attachment_id, string $path, int $expires ): string {
		$secret = self::get_hmac_key();
		if ( empty( $secret ) ) {
			return '';
		}

		$payload = $attachment_id . '|' . $path . '|' . $expires;
		return hash_hmac( 'sha256', $payload, $secret );
	}

	/**
	 * Verify a token.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $path Relative path stored in meta.
	 * @param int    $expires Expiry timestamp.
	 * @param string $token Provided token.
	 * @return bool
	 */
	private static function verify_token( int $attachment_id, string $path, int $expires, string $token ): bool {
		$expected = self::build_token( $attachment_id, $path, $expires );
		return ! empty( $expected ) && hash_equals( $expected, $token );
	}

	/**
	 * Sign the raw hash for tamper detection.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $raw_hash SHA-256 of the raw file.
	 * @return string Signature or empty string.
	 */
	private static function sign_hash( int $attachment_id, string $raw_hash ): string {
		$secret = self::get_hmac_key();
		if ( empty( $secret ) || empty( $raw_hash ) ) {
			return '';
		}

		return hash_hmac( 'sha256', $raw_hash . '|' . $attachment_id, $secret );
	}

	/**
	 * Resolve HMAC key.
	 *
	 * @return string Secret key.
	 */
	private static function get_hmac_key(): string {
		if ( defined( 'TIMU_VAULT_KEY' ) && TIMU_VAULT_KEY ) {
			return (string) TIMU_VAULT_KEY;
		}

		if ( defined( 'AUTH_KEY' ) && AUTH_KEY ) {
			return (string) AUTH_KEY;
		}

		return (string) wp_salt( 'auth' );
	}

	/**
	 * Resolve storage mode.
	 *
	 * @param string $mode Requested mode.
	 * @return string
	 */
	private static function resolve_mode( string $mode ): string {
		return ( 'zip' === strtolower( $mode ) ) ? 'zip' : 'raw';
	}

	/**
	 * Resolve compression constant.
	 *
	 * @param array $settings Vault settings.
	 * @return int ZipArchive compression mode.
	 */
	private static function resolve_compression( array $settings ): int {
		$pref = isset( $settings['compression'] ) ? strtolower( (string) $settings['compression'] ) : 'store';
		return ( 'deflate' === $pref ) ? ZipArchive::CM_DEFLATE : ZipArchive::CM_STORE;
	}

	/**
	 * Convert relative vault path to absolute path.
	 *
	 * @param string $relative Relative path stored in meta.
	 * @return string
	 */
	private static function absolute_path( string $relative ): string {
		$uploads = wp_upload_dir();
		return trailingslashit( $uploads['basedir'] ) . ltrim( $relative, '/\\' );
	}

	/**
	 * Get settings (network merged with site).
	 *
	 * @return array
	 */
	public static function get_settings(): array {
		$defaults = array(
			'enabled'                   => true,
			'mode'                      => 'raw',
			'compression'               => 'store', // store = bit-identical; deflate = smaller.
			'download_ttl'              => 600,
			'encrypt'                   => false,
			self::OPTION_ALLOW_OVERRIDE => true,
		);

		$site     = get_option( self::OPTION_KEY, array() );
		$network  = is_multisite() ? get_site_option( self::OPTION_KEY, array() ) : array();
		$settings = array_merge( $defaults, $network );

		// Only merge site settings when allowed by network.
		if ( self::site_override_allowed_from_array( $settings ) ) {
			$settings = array_merge( $settings, $site );
		}

		$settings['mode']         = self::resolve_mode( (string) ( $settings['mode'] ?? 'raw' ) );
		$settings['download_ttl'] = max( 60, (int) ( $settings['download_ttl'] ?? 600 ) );
		$settings['compression']  = in_array( strtolower( (string) ( $settings['compression'] ?? 'store' ) ), array( 'store', 'deflate' ), true ) ? strtolower( (string) $settings['compression'] ) : 'store';
		$settings['encrypt']      = ! empty( $settings['encrypt'] );

		return $settings;
	}

	/**
	 * Save settings to site or network scope.
	 *
	 * @param array $settings Settings payload.
	 * @param bool  $network  Save as network option when true.
	 * @return bool
	 */
	private static function save_settings( array $settings, bool $network ): bool {
		$merged = array_merge( self::get_settings(), $settings );
		if ( $network && is_multisite() ) {
			return update_site_option( self::OPTION_KEY, $merged );
		}

		return update_option( self::OPTION_KEY, $merged );
	}

	/**
	 * Check if site overrides are permitted based on saved settings.
	 *
	 * @return bool
	 */
	public static function site_override_allowed(): bool {
		$network = is_multisite() ? get_site_option( self::OPTION_KEY, array() ) : array();
		return self::site_override_allowed_from_array( array_merge( array( self::OPTION_ALLOW_OVERRIDE => true ), $network ) );
	}

	/**
	 * Helper to check override flag within a settings array.
	 *
	 * @param array $settings Settings array.
	 * @return bool
	 */
	private static function site_override_allowed_from_array( array $settings ): bool {
		return ! empty( $settings[ self::OPTION_ALLOW_OVERRIDE ] );
	}

	/**
	 * Rehydrate missing attachments up to a limit.
	 *
	 * @param int $limit Max items to process.
	 * @return array{ok:int,fail:int,skipped:int}
	 */
	private static function rehydrate_missing_attachments( int $limit ): array {
		$ok      = 0;
		$fail    = 0;
		$skipped = 0;

		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);

		foreach ( $query->posts as $attachment_id ) {
			$file = get_attached_file( $attachment_id );
			if ( empty( $file ) ) {
				++$skipped;
				continue;
			}

			if ( file_exists( $file ) ) {
				++$skipped;
				continue;
			}

			$rehydrated = self::rehydrate( (int) $attachment_id );
			if ( $rehydrated ) {
				++$ok;
			} else {
				++$fail;
			}
		}

		return array(
			'ok'      => $ok,
			'fail'    => $fail,
			'skipped' => $skipped,
		);
	}

	/**
	 * Verify a sample of vaulted attachments.
	 *
	 * @param int $limit Sample size.
	 * @return array{ok:int,fail:int,missing:int}
	 */
	private static function verify_sample( int $limit ): array {
		$ok      = 0;
		$fail    = 0;
		$missing = 0;

		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => self::META_PATH,
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $query->posts as $attachment_id ) {
			$result = self::verify_attachment_integrity( (int) $attachment_id );
			if ( $result['status'] === 'missing' ) {
				++$missing;
				continue;
			}
			if ( $result['status'] === 'ok' ) {
				++$ok;
				continue;
			}
			++$fail;
		}

		return array(
			'ok'      => $ok,
			'fail'    => $fail,
			'missing' => $missing,
		);
	}

	/**
	 * Handle queue start/stop actions from admin.
	 *
	 * @return void
	 */
	public static function handle_queue_action(): void {
		$cmd = isset( $_POST['timu_vault_queue_cmd'] ) ? sanitize_text_field( wp_unslash( $_POST['timu_vault_queue_cmd'] ) ) : '';
		if ( empty( $_POST['timu_vault_queue_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['timu_vault_queue_nonce'] ) ), self::QUEUE_ACTION ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		if ( 'start_rehydrate' === $cmd ) {
			self::start_queue( 'rehydrate_all' );
		}

		if ( 'start_verify' === $cmd ) {
			self::start_queue( 'verify_all' );
		}

		if ( 'start_reencrypt' === $cmd ) {
			$only_old = isset( $_POST['timu_reencrypt_only_old'] ) && '1' === $_POST['timu_reencrypt_only_old'];
			self::start_queue( 'reencrypt_all', array( 'only_old' => $only_old ) );
		}

		if ( 'start_migrate' === $cmd ) {
			self::start_queue( 'migrate_all', array( 'per_page' => 50 ) );
		}

		if ( 'stop' === $cmd ) {
			self::clear_queue();
		}

		wp_safe_redirect( wp_get_referer() ?: admin_url() );
		exit;
	}

	/**
	 * Start a background queue.
	 *
	 * @param string $type Queue type.
	 * @return void
	 */
	private static function start_queue( string $type, array $options = array() ): void {
		$state = array(
			'id'        => uniqid( 'timu_vault_', true ),
			'type'      => $type,
			'page'      => 1,
			'per_page'  => isset( $options['per_page'] ) ? max( 5, (int) $options['per_page'] ) : 25,
			'ok'        => 0,
			'fail'      => 0,
			'missing'   => 0,
			'skipped'   => 0,
			'processed' => 0,
			'total'     => null,
			'last_run'  => time(),
			'status'    => 'running',
		);

		if ( 'reencrypt_all' === $type ) {
			$state['only_old'] = ! empty( $options['only_old'] );
		}

		update_option( self::QUEUE_OPTION, $state );
		self::schedule_queue();
	}

	/**
	 * Retrieve pending contributor uploads that require editor review.
	 *
	 * @param int $limit Number of entries to return.
	 * @return array<int,array<string,mixed>>
	 */
	public static function get_pending_contributor_uploads( int $limit = 5 ): array {
		$query = new \WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'posts_per_page' => $limit,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'   => self::META_PENDING_REVIEW,
						'value' => '1',
					),
				),
			)
		);

		if ( empty( $query->posts ) ) {
			return array();
		}

		$items = array();
		foreach ( $query->posts as $attachment_id ) {
			$post         = get_post( (int) $attachment_id );
			$user_id      = $post ? (int) $post->post_author : 0;
			$user         = $user_id ? get_user_by( 'id', $user_id ) : null;
			$user_name    = $user && $user->exists() ? $user->display_name : __( 'Unknown', 'core-support-thisismyurl' );
			$file         = wp_basename( (string) get_attached_file( (int) $attachment_id ) );
			$is_optimized = (string) get_post_meta( (int) $attachment_id, self::META_PENDING_OPTIMIZED, true ) === '1';

			$items[] = array(
				'id'        => (int) $attachment_id,
				'title'     => $post ? $post->post_title : __( 'Untitled', 'core-support-thisismyurl' ),
				'user'      => $user_name,
				'user_id'   => $user_id,
				'date'      => $post ? $post->post_date_gmt : '',
				'file'      => $file,
				'optimized' => $is_optimized,
				'edit_link' => get_edit_post_link( (int) $attachment_id, '' ),
			);
		}

		return $items;
	}

	/**
	 * Clear queue state.
	 *
	 * @return void
	 */
	private static function clear_queue(): void {
		delete_option( self::QUEUE_OPTION );
	}

	/**
	 * Get queue state.
	 *
	 * @return array
	 */
	public static function get_queue_state(): array {
		$state = get_option( self::QUEUE_OPTION, array() );
		return is_array( $state ) ? $state : array();
	}

	/**
	 * Schedule queue runner.
	 *
	 * @return void
	 */
	private static function schedule_queue(): void {
		if ( ! wp_next_scheduled( 'timu_vault_queue_runner' ) ) {
			wp_schedule_single_event( time() + 5, 'timu_vault_queue_runner' );
		}
	}

	/**
	 * Process queue batch.
	 *
	 * @return void
	 */
	public static function process_queue(): void {
		$state = self::get_queue_state();
		if ( empty( $state ) || ( $state['status'] ?? '' ) !== 'running' ) {
			return;
		}

		$type     = $state['type'] ?? '';
		$page     = max( 1, (int) ( $state['page'] ?? 1 ) );
		$per_page = max( 5, (int) ( $state['per_page'] ?? 25 ) );

		$query_args = array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'fields'         => 'ids',
		);

		// For verify, only those with vault meta.
		if ( 'verify_all' === $type ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => self::META_PATH,
					'compare' => 'EXISTS',
				),
			);
		}

		// For migrate, only those without vault meta.
		if ( 'migrate_all' === $type ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => self::META_PATH,
					'compare' => 'NOT EXISTS',
				),
			);
		}

		// For reencrypt, only those with encrypted meta.
		if ( 'reencrypt_all' === $type ) {
			$query_args['meta_query'] = array(
				array(
					'key'     => self::META_ENCRYPTED,
					'compare' => 'EXISTS',
				),
			);
		}

		$query = new \WP_Query( $query_args );

		if ( 0 === $query->post_count ) {
			$state['status'] = 'done';
			update_option( self::QUEUE_OPTION, $state );
			return;
		}

		$only_old = ! empty( $state['only_old'] );
		$key_info = self::get_encryption_key_info();
		foreach ( $query->posts as $attachment_id ) {
			if ( 'migrate_all' === $type ) {
				$file = get_attached_file( $attachment_id );
				if ( empty( $file ) || ! file_exists( $file ) ) {
					$state['skipped'] = ( $state['skipped'] ?? 0 ) + 1;
					self::add_log( 'warning', (int) $attachment_id, 'Skip migrate: source file missing.', 'migrate' );
					continue;
				}
				$has_vault = (string) get_post_meta( $attachment_id, self::META_PATH, true );
				if ( ! empty( $has_vault ) ) {
					$state['skipped'] = ( $state['skipped'] ?? 0 ) + 1;
					continue;
				}
				try {
					self::ingest( (int) $attachment_id, $file );
					$state['ok'] = ( $state['ok'] ?? 0 ) + 1;
					self::add_log( 'info', (int) $attachment_id, 'Attachment migrated to Vault.', 'migrate' );
				} catch ( \Throwable $e ) {
					$state['fail'] = ( $state['fail'] ?? 0 ) + 1;
					self::add_log( 'error', (int) $attachment_id, 'Migrate failed: ' . $e->getMessage(), 'migrate' );
				}
			}
			if ( 'rehydrate_all' === $type ) {
				$file = get_attached_file( $attachment_id );
				if ( empty( $file ) ) {
					$state['skipped'] = ( $state['skipped'] ?? 0 ) + 1;
					continue;
				}
				if ( file_exists( $file ) ) {
					$state['skipped'] = ( $state['skipped'] ?? 0 ) + 1;
					continue;
				}
				$rehydrated = self::rehydrate( (int) $attachment_id );
				if ( $rehydrated ) {
					$state['ok'] = ( $state['ok'] ?? 0 ) + 1;
				} else {
					$state['fail'] = ( $state['fail'] ?? 0 ) + 1;
				}
			}

			if ( 'verify_all' === $type ) {
				$result = self::verify_attachment_integrity( (int) $attachment_id );
				if ( 'ok' === $result['status'] ) {
					$state['ok'] = ( $state['ok'] ?? 0 ) + 1;
				} elseif ( 'missing' === $result['status'] ) {
					$state['missing'] = ( $state['missing'] ?? 0 ) + 1;
					self::add_log( 'warning', $attachment_id, 'Vault file missing during verify.' );
				} else {
					$state['fail'] = ( $state['fail'] ?? 0 ) + 1;
					self::add_log( 'error', $attachment_id, $result['reason'] );
				}
			}

			if ( 'reencrypt_all' === $type ) {
				$enc = (string) get_post_meta( $attachment_id, self::META_ENCRYPTED, true );
				if ( empty( $enc ) ) {
					$state['skipped'] = ( $state['skipped'] ?? 0 ) + 1;
					continue;
				}
				if ( $only_old ) {
					$kid = (string) get_post_meta( $attachment_id, self::META_KEY_ID, true );
					if ( ! empty( $kid ) && $kid === (string) ( $key_info['id'] ?? '' ) ) {
						$state['skipped'] = ( $state['skipped'] ?? 0 ) + 1;
						continue;
					}
				}
				$ok = self::reencrypt_attachment( (int) $attachment_id );
				if ( $ok ) {
					$state['ok'] = ( $state['ok'] ?? 0 ) + 1;
					self::add_log( 'info', (int) $attachment_id, 'Attachment re-encrypted to current key.', 'reencrypt' );
				} else {
					$state['fail'] = ( $state['fail'] ?? 0 ) + 1;
					self::add_log( 'error', (int) $attachment_id, 'Re-encrypt failed.', 'reencrypt' );
				}
			}

			$state['processed'] = ( $state['processed'] ?? 0 ) + 1;
		}

		$state['last_run'] = time();
		$state['page']     = $page + 1;
		$state['total']    = $query->found_posts;

		update_option( self::QUEUE_OPTION, $state );
		self::schedule_queue();
	}

	/**
	 * Add an event log entry.
	 *
	 * @param string $level Level (error|warning|info).
	 * @param int    $attachment_id Attachment ID.
	 * @param string $reason Human-readable reason.
	 * @param string $operation Optional operation name.
	 * @param array  $context Optional context (task, file, user, user_id).
	 * @return void
	 */
	public static function add_log( string $level, int $attachment_id, string $reason, string $operation = '', array $context = array() ): void {
		$logs = (array) get_option( self::LOG_OPTION, array() );

		$entry = array(
			'timestamp'     => current_time( 'mysql' ),
			'level'         => sanitize_text_field( $level ),
			'attachment_id' => $attachment_id,
			'reason'        => sanitize_text_field( $reason ),
			'operation'     => sanitize_text_field( $operation ),
			'task'          => isset( $context['task'] ) ? sanitize_text_field( (string) $context['task'] ) : '',
			'file'          => isset( $context['file'] ) ? sanitize_text_field( (string) $context['file'] ) : '',
			'user'          => isset( $context['user'] ) ? sanitize_text_field( (string) $context['user'] ) : '',
			'user_id'       => isset( $context['user_id'] ) ? (int) $context['user_id'] : 0,
		);

		// Prepend new entry.
		array_unshift( $logs, $entry );

		// Trim to max entries only when explicitly configured.
		if ( self::LOG_MAX_ENTRIES > 0 && count( $logs ) > self::LOG_MAX_ENTRIES ) {
			$logs = array_slice( $logs, 0, self::LOG_MAX_ENTRIES );
		}

		update_option( self::LOG_OPTION, $logs );
	}

	/**
	 * Retrieve logs with optional filtering.
	 *
	 * @param int    $offset Offset for pagination.
	 * @param int    $limit Limit per page.
	 * @param string $level_filter Optional level filter (error|warning|info).
	 * @return array Array of log entries.
	 */
	public static function get_logs( int $offset = 0, int $limit = 50, string $level_filter = '' ): array {
		$logs = (array) get_option( self::LOG_OPTION, array() );

		if ( ! empty( $level_filter ) ) {
			$logs = array_filter( $logs, fn( $entry ) => ( $entry['level'] ?? '' ) === $level_filter );
		}

		// Re-index after filter.
		$logs = array_values( $logs );

		return array_slice( $logs, $offset, $limit );
	}

	/**
	 * Get total count of logs with optional level filter.
	 *
	 * @param string $level_filter Optional level filter.
	 * @return int
	 */
	public static function get_log_count( string $level_filter = '' ): int {
		$logs = (array) get_option( self::LOG_OPTION, array() );

		if ( ! empty( $level_filter ) ) {
			$logs = array_filter( $logs, fn( $entry ) => ( $entry['level'] ?? '' ) === $level_filter );
		}

		return count( $logs );
	}

	/**
	 * Clear all logs.
	 *
	 * @return void
	 */
	public static function clear_logs(): void {
		delete_option( self::LOG_OPTION );
	}

	/**
	 * Prune logs older than retention period.
	 *
	 * @return int Number of pruned entries.
	 */
	private static function prune_logs(): int {
		$logs = (array) get_option( self::LOG_OPTION, array() );

		if ( empty( $logs ) ) {
			return 0;
		}

		$cutoff = strtotime( '-' . self::LOG_RETENTION_DAYS . ' days' );
		$pruned = 0;

		foreach ( $logs as $index => $entry ) {
			$timestamp = strtotime( $entry['timestamp'] ?? '' );
			if ( $timestamp && $timestamp < $cutoff ) {
				unset( $logs[ $index ] );
				++$pruned;
			}
		}

		if ( $pruned > 0 ) {
			update_option( self::LOG_OPTION, array_values( $logs ) );
		}

		return $pruned;
	}

	/**
	 * Determine if Vault ingest is enabled.
	 *
	 * @return bool
	 */
	private static function is_enabled(): bool {
		$settings = self::get_settings();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Handle log clearing from settings.
	 *
	 * @return void
	 */
	public static function handle_clear_logs(): void {
		if ( empty( $_POST['timu_vault_log_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['timu_vault_log_nonce'] ) ), 'timu_vault_logs' ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		$cmd = isset( $_POST['timu_vault_log_action'] ) ? sanitize_text_field( wp_unslash( $_POST['timu_vault_log_action'] ) ) : '';
		if ( 'clear_all' === $cmd ) {
			self::clear_logs();
		}

		$redirect = add_query_arg( 'timu_vault_logs_cleared', '1', wp_get_referer() ?: admin_url() );
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Handle export logs as a CSV download.
	 *
	 * @return void
	 */
	public static function handle_export_logs(): void {
		$nonce = isset( $_GET['timu_vault_export_nonce'] ) ? sanitize_text_field( wp_unslash( $_GET['timu_vault_export_nonce'] ) ) : '';
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'timu_vault_export' ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		$logs = (array) get_option( self::LOG_OPTION, array() );

		$filename = 'timu-vault-logs-' . gmdate( 'Ymd-His' ) . '.csv';
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		$fh = fopen( 'php://output', 'w' );
		if ( false === $fh ) {
			// Fallback: redirect back if stream cannot be opened.
			wp_safe_redirect( wp_get_referer() ?: admin_url() );
			exit;
		}

		// Header row.
		fputcsv( $fh, array( 'timestamp', 'level', 'attachment_id', 'reason', 'operation' ) );

		foreach ( $logs as $entry ) {
			fputcsv(
				$fh,
				array(
					$entry['timestamp'] ?? '',
					$entry['level'] ?? '',
					(string) ( $entry['attachment_id'] ?? '' ),
					$entry['reason'] ?? '',
					$entry['operation'] ?? '',
				)
			);
		}

		fclose( $fh );
		exit;
	}

	/**
	 * Verify attachment integrity including raw hash for zip mode.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array{status:string,reason:string}
	 */
	private static function verify_attachment_integrity( int $attachment_id ): array {
		$path = (string) get_post_meta( $attachment_id, self::META_PATH, true );
		if ( empty( $path ) ) {
			return array(
				'status' => 'missing',
				'reason' => 'Vault path missing.',
			);
		}

		$absolute = self::absolute_path( $path );
		if ( ! file_exists( $absolute ) ) {
			return array(
				'status' => 'missing',
				'reason' => 'Vault file missing on disk.',
			);
		}

		$expected_store = (string) get_post_meta( $attachment_id, self::META_HASH_STORE, true );
		$current_store  = hash_file( 'sha256', $absolute ) ?: '';
		if ( $expected_store && $current_store && ! hash_equals( $expected_store, $current_store ) ) {
			return array(
				'status' => 'fail',
				'reason' => 'Stored hash mismatch.',
			);
		}

		$mode          = (string) get_post_meta( $attachment_id, self::META_MODE, true );
		$expected_raw  = (string) get_post_meta( $attachment_id, self::META_HASH_RAW, true );
		$raw_validated = true;

		if ( 'zip' === $mode && $expected_raw ) {
			$raw_validated = self::validate_raw_hash_from_zip( $absolute, $expected_raw );
		}

		if ( 'raw' === $mode && $expected_raw ) {
			$current_raw   = $current_store ?: hash_file( 'sha256', $absolute );
			$raw_validated = $current_raw && hash_equals( $expected_raw, (string) $current_raw );
		}

		if ( ! $raw_validated ) {
			return array(
				'status' => 'fail',
				'reason' => 'Raw hash mismatch.',
			);
		}

		// Journal entry for successful verification.
		self::add_journal_entry(
			$attachment_id,
			array(
				'op'          => 'verify',
				'args'        => array(
					'result'        => 'ok',
					'expected_hash' => $expected_raw,
				),
				'before_hash' => $expected_raw,
				'after_hash'  => $expected_raw,
			)
		);

		return array(
			'status' => 'ok',
			'reason' => '',
		);
	}

	/**
	 * Validate raw hash by extracting a zip to temp.
	 *
	 * @param string $zip_path Zip path.
	 * @param string $expected_raw Expected hash.
	 * @return bool
	 */
	private static function validate_raw_hash_from_zip( string $zip_path, string $expected_raw ): bool {
		if ( ! class_exists( ZipArchive::class ) ) {
			return false;
		}

		$zip = new ZipArchive();
		if ( true !== $zip->open( $zip_path ) ) {
			return false;
		}

		if ( $zip->numFiles < 1 ) {
			$zip->close();
			return false;
		}

		$first  = $zip->getNameIndex( 0 );
		$stream = $zip->getStream( $first );
		if ( ! $stream ) {
			$zip->close();
			return false;
		}

		$temp = wp_tempnam( 'timu_vault_zip_hash_' );
		if ( ! $temp ) {
			fclose( $stream );
			$zip->close();
			return false;
		}

		$fp = fopen( $temp, 'wb' );
		if ( ! $fp ) {
			fclose( $stream );
			$zip->close();
			return false;
		}

		while ( ! feof( $stream ) ) {
			$data = fread( $stream, 8192 );
			if ( false === $data ) {
				break;
			}
			fwrite( $fp, $data );
		}

		fclose( $fp );
		fclose( $stream );
		$zip->close();

		$current = hash_file( 'sha256', $temp ) ?: '';
		unlink( $temp );

		return (bool) ( $current && hash_equals( $expected_raw, $current ) );
	}

	/**
	 * Stream file with headers.
	 *
	 * @param string $path Absolute path.
	 * @param string $download_name Suggested download name.
	 * @return void
	 */
	private static function stream_file( string $path, string $download_name ): void {
		if ( headers_sent() ) {
			return;
		}

		$clean_name = sanitize_file_name( basename( $download_name ) );
		$mime       = wp_check_filetype( $clean_name );
		$mime       = $mime['type'] ?? 'application/octet-stream';

		header( 'Content-Type: ' . $mime );
		header( 'Content-Length: ' . (string) filesize( $path ) );
		header( 'Content-Disposition: attachment; filename="' . $clean_name . '"' );
		header( 'X-TIMU-Vault: 1' );

		$fp = fopen( $path, 'rb' );
		if ( $fp ) {
			fpassthru( $fp );
			fclose( $fp );
		}
	}

	/**
	 * CLI: rehydrate a specific attachment.
	 *
	 * @param array $args Positional args [attachment_id].
	 * @return void
	 */
	public static function cli_rehydrate( array $args ): void {
		$attachment_id = isset( $args[0] ) ? (int) $args[0] : 0;
		if ( $attachment_id <= 0 ) {
			\WP_CLI::error( 'Please provide an attachment ID.' );
		}

		$result = self::rehydrate( $attachment_id );
		if ( $result ) {
			\WP_CLI::success( 'Attachment rehydrated from Vault.' );
			return;
		}

		\WP_CLI::error( 'Rehydrate failed. Ensure the Vault copy exists.' );
	}

	/**
	 * CLI: verify integrity of a stored attachment.
	 *
	 * @param array $args Positional args [attachment_id].
	 * @return void
	 */
	public static function cli_verify( array $args ): void {
		$attachment_id = isset( $args[0] ) ? (int) $args[0] : 0;
		if ( $attachment_id <= 0 ) {
			\WP_CLI::error( 'Please provide an attachment ID.' );
		}

		$result = self::verify_attachment_integrity( $attachment_id );
		if ( 'ok' === $result['status'] ) {
			\WP_CLI::success( 'Integrity OK.' );
			return;
		}
		\WP_CLI::error( $result['reason'] );
	}

	/**
	 * WP-CLI: Show Vault status (settings + queue state).
	 *
	 * @param array $args Positional args (unused).
	 * @param array $assoc Assoc args (unused for now).
	 * @return void
	 */
	public static function cli_status( array $args, array $assoc = array() ): void {
		$settings = self::get_settings();
		$queue    = self::get_queue_state();
		$vault    = self::get_vault_path();

		\WP_CLI::line( 'Vault: ' . ( $vault ?: '(not initialized yet)' ) );
		\WP_CLI::line( 'Enabled: ' . ( ! empty( $settings['enabled'] ) ? 'yes' : 'no' ) );
		\WP_CLI::line( 'Encrypt (AES-GCM): ' . ( ! empty( $settings['encrypt'] ) ? 'yes' : 'no' ) );
		\WP_CLI::line( 'Mode: ' . ( $settings['mode'] ?? 'raw' ) );

		if ( ! empty( $queue ) ) {
			\WP_CLI::line( 'Queue: ' . ( $queue['type'] ?? '?' ) . ' (' . ( $queue['status'] ?? 'idle' ) . ')' );
			\WP_CLI::line( 'Processed: ' . (int) ( $queue['processed'] ?? 0 ) . ' of ' . ( $queue['total'] ?? '∞' ) );
			\WP_CLI::line( 'OK/Fail/Missing/Skipped: ' . (int) ( $queue['ok'] ?? 0 ) . '/' . (int) ( $queue['fail'] ?? 0 ) . '/' . (int) ( $queue['missing'] ?? 0 ) . '/' . (int) ( $queue['skipped'] ?? 0 ) );
		} else {
			\WP_CLI::line( 'Queue: idle' );
		}

		\WP_CLI::success( 'Status reported.' );
	}

	/**
	 * WP-CLI: Queue a migration of existing attachments into the Vault.
	 *
	 * @param array $args Positional args (unused).
	 * @param array $assoc Assoc args: per-page (int).
	 * @return void
	 */
	public static function cli_migrate( array $args, array $assoc = array() ): void {
		$per_page = isset( $assoc['per-page'] ) ? max( 5, (int) $assoc['per-page'] ) : 50;
		self::start_queue( 'migrate_all', array( 'per_page' => $per_page ) );
		\WP_CLI::success( 'Queued migrate job (per-page: ' . $per_page . ').' );
	}

	/**
	 * WP-CLI: Anonymize attachments for a given user (GDPR erasure).
	 * Retains originals in Vault; scrubs personal data.
	 *
	 * @param array $args Positional args: [user-id or email].
	 * @param array $assoc Assoc args: batch, verbose.
	 * @return void
	 */
	public static function cli_erase_user_data( array $args, array $assoc = array() ): void {
		if ( empty( $args[0] ) ) {
			\WP_CLI::error( 'Usage: wp timu vault erase-user-data <user-id-or-email> [--batch=50] [--verbose]' );
		}

		$user_input = (string) $args[0];
		$batch_size = isset( $assoc['batch'] ) ? max( 1, (int) $assoc['batch'] ) : 50;
		$verbose    = isset( $assoc['verbose'] ) && $assoc['verbose'];

		// Resolve user.
		if ( is_numeric( $user_input ) ) {
			$user = get_user_by( 'id', (int) $user_input );
		} else {
			$user = get_user_by( 'email', (string) $user_input );
		}

		if ( ! $user || ! $user->exists() ) {
			\WP_CLI::error( 'User not found: ' . $user_input );
		}

		$user_id = (int) $user->ID;

		\WP_CLI::log( 'Anonymizing attachments for user: ' . $user->user_login . ' (ID: ' . $user_id . ', Email: ' . $user->user_email . ')' );
		\WP_CLI::log( 'Policy: Retain originals in Vault, anonymize personal data.' );

		$page          = 1;
		$total_removed = 0;
		$total_retain  = 0;

		while ( true ) {
			$result = self::erase_user_personal_data( $user_id, $page, $batch_size );

			$items_removed = (int) ( $result['items_removed'] ?? 0 );
			$items_retain  = (int) ( $result['items_retained'] ?? 0 );
			$done          = (bool) ( $result['done'] ?? true );

			$total_removed += $items_removed;
			$total_retain  += $items_retain;

			if ( $verbose ) {
				foreach ( (array) ( $result['messages'] ?? array() ) as $msg ) {
					\WP_CLI::log( '  ' . $msg );
				}
			}

			\WP_CLI::log( "Page $page: Removed $items_removed, Retained $items_retain" );

			if ( $done ) {
				break;
			}

			++$page;
		}

		\WP_CLI::success( "Completed: Anonymized $total_removed attachments, $total_retain failed. Originals retained in Vault." );
	}

	/**
	 * Rewrite Vault URLs in post content (the_content filter).
	 *
	 * @param string $content Post content.
	 * @return string Modified content.
	 */
	public static function rewrite_vault_urls_in_content( string $content ): string {
		// Only rewrite in main query, in the loop, for singular posts.
		if ( ! is_main_query() || ! in_the_loop() || ! is_singular() ) {
			return $content;
		}

		$uploads = wp_upload_dir();
		if ( empty( $uploads['baseurl'] ) ) {
			return $content;
		}

		$vault_dirname = (string) get_option( 'timu_vault_dirname', '' );
		if ( empty( $vault_dirname ) ) {
			return $content;
		}

		// Pattern: match URLs pointing into the vault directory.
		$vault_url_pattern = preg_quote( trailingslashit( $uploads['baseurl'] ) . $vault_dirname, '/' );
		$pattern           = '/(https?:\/\/[^\/]+\/' . $vault_url_pattern . '\/[^\s"\'<>]+)/i';

		// Replace vault URLs with finalized attachment URLs if possible.
		$content = preg_replace_callback(
			$pattern,
			function ( $matches ) {
				$vault_url = $matches[0];
				// Attempt to find the attachment ID by vault path.
				$attachment_id = self::find_attachment_by_vault_url( $vault_url );
				if ( $attachment_id ) {
					$finalized = wp_get_attachment_url( $attachment_id );
					if ( $finalized && $finalized !== $vault_url ) {
						return $finalized;
					}
				}
				return $vault_url;
			},
			$content
		);

		return $content;
	}

	/**
	 * Intercept 404 requests for legacy Vault paths and serve the finalized asset.
	 *
	 * @return void
	 */
	public static function intercept_404_for_vault(): void {
		if ( ! is_404() ) {
			return;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( empty( $request_uri ) ) {
			return;
		}

		$uploads       = wp_upload_dir();
		$vault_dirname = (string) get_option( 'timu_vault_dirname', '' );

		if ( empty( $vault_dirname ) || empty( $uploads['basedir'] ) ) {
			return;
		}

		// Check if request URI matches vault directory pattern.
		$vault_path_pattern = '/' . preg_quote( $vault_dirname, '/' ) . '\\/';
		if ( ! preg_match( $vault_path_pattern, $request_uri ) ) {
			return;
		}

		// Attempt to find the attachment by vault URL.
		$full_url      = ( is_ssl() ? 'https://' : 'http://' ) . ( $_SERVER['HTTP_HOST'] ?? '' ) . $request_uri;
		$attachment_id = self::find_attachment_by_vault_url( $full_url );

		if ( ! $attachment_id ) {
			return;
		}

		$file = get_attached_file( $attachment_id );
		if ( empty( $file ) || ! file_exists( $file ) ) {
			// Attempt rehydrate.
			if ( self::rehydrate( $attachment_id ) ) {
				$file = get_attached_file( $attachment_id );
			}
		}

		if ( ! empty( $file ) && file_exists( $file ) ) {
			// Serve the finalized file directly (no redirect).
			status_header( 200 );
			$mime_type = wp_check_filetype( $file );
			if ( ! empty( $mime_type['type'] ) ) {
				header( 'Content-Type: ' . $mime_type['type'] );
			}
			header( 'Content-Length: ' . filesize( $file ) );
			readfile( $file );
			exit;
		}
	}

	/**
	 * Find attachment ID by vault URL.
	 *
	 * @param string $vault_url Vault URL.
	 * @return int Attachment ID or 0.
	 */
	private static function find_attachment_by_vault_url( string $vault_url ): int {
		global $wpdb;

		$uploads = wp_upload_dir();
		if ( empty( $uploads['baseurl'] ) ) {
			return 0;
		}

		// Convert URL to relative path.
		$relative = str_replace( trailingslashit( $uploads['baseurl'] ), '', $vault_url );

		// Query for attachment with matching vault path meta.
		$attachment_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1",
				self::META_PATH,
				$relative
			)
		);

		return $attachment_id;
	}

	/**
	 * Cleanup helper for temp paths.
	 *
	 * @param string $path File or directory.
	 * @return void
	 */
	private static function cleanup_path( string $path ): void {
		if ( is_dir( $path ) ) {
			$files = scandir( $path );
			if ( is_array( $files ) ) {
				foreach ( $files as $file ) {
					if ( '.' === $file || '..' === $file ) {
						continue;
					}
					$full = $path . DIRECTORY_SEPARATOR . $file;
					if ( is_dir( $full ) ) {
						self::cleanup_path( $full );
					} else {
						unlink( $full );
					}
				}
			}
			rmdir( $path );
			return;
		}

		if ( file_exists( $path ) ) {
			unlink( $path );
		}
	}

	/**
	 * Encrypt file contents (wrapper) using AES-256-GCM.
	 *
	 * @param string $file_path Path to file to encrypt.
	 * @param string $key Encryption key material.
	 * @return bool True on success, false on failure.
	 */
	public static function encrypt_file( string $file_path, string $key ): bool {
		return self::encrypt_file_gcm( $file_path, $key );
	}

	/**
	 * Decrypt file contents, auto-detecting AES-256-GCM format.
	 * Falls back to legacy CBC format if header not present.
	 *
	 * @param string $file_path Path to encrypted file.
	 * @param string $key Encryption key material.
	 * @return string|bool Decrypted plaintext, or false on failure.
	 */
	public static function decrypt_file( string $file_path, string $key ) {
		if ( ! extension_loaded( 'openssl' ) || ! file_exists( $file_path ) ) {
			return false;
		}

		$encrypted = file_get_contents( $file_path );
		if ( false === $encrypted ) {
			return false;
		}

		$header = 'TIMU:GCM:1\n';
		if ( 0 === strpos( $encrypted, $header ) ) {
			$payload = substr( $encrypted, strlen( $header ) );
			if ( strlen( $payload ) < 28 ) {
				return false;
			}
			$iv         = substr( $payload, 0, 12 );
			$tag        = substr( $payload, 12, 16 );
			$ciphertext = substr( $payload, 28 );
			$hash_key   = hash( 'sha256', $key, true );
			return openssl_decrypt( $ciphertext, 'aes-256-gcm', $hash_key, OPENSSL_RAW_DATA, $iv, $tag );
		}

		// Legacy CBC format: [16-byte IV][ciphertext].
		if ( strlen( $encrypted ) < 16 ) {
			return false;
		}
		$iv         = substr( $encrypted, 0, 16 );
		$ciphertext = substr( $encrypted, 16 );
		$hash_key   = hash( 'sha256', $key, true );
		return openssl_decrypt( $ciphertext, 'AES-256-CBC', $hash_key, OPENSSL_RAW_DATA, $iv );
	}

	/**
	 * Encrypt file contents using AES-256-GCM with an auth tag.
	 * Layout: "TIMU:GCM:1\n" + IV(12) + TAG(16) + ciphertext.
	 *
	 * @param string $file_path Path to file to encrypt.
	 * @param string $key Encryption key material.
	 * @return bool
	 */
	public static function encrypt_file_gcm( string $file_path, string $key ): bool {
		if ( ! extension_loaded( 'openssl' ) || ! file_exists( $file_path ) ) {
			return false;
		}

		$plaintext = file_get_contents( $file_path );
		if ( false === $plaintext ) {
			return false;
		}

		$hash_key = hash( 'sha256', $key, true );
		$iv       = openssl_random_pseudo_bytes( 12 );
		if ( false === $iv ) {
			return false;
		}

		$tag        = '';
		$ciphertext = openssl_encrypt( $plaintext, 'aes-256-gcm', $hash_key, OPENSSL_RAW_DATA, $iv, $tag );
		if ( false === $ciphertext || empty( $tag ) ) {
			return false;
		}

		$header    = 'TIMU:GCM:1\n';
		$encrypted = $header . $iv . $tag . $ciphertext;
		return false !== file_put_contents( $file_path, $encrypted, LOCK_EX );
	}

	/**
	 * Check if encryption is supported.
	 *
	 * @return bool True if openssl extension is loaded.
	 */
	public static function is_encryption_supported(): bool {
		return extension_loaded( 'openssl' );
	}

	/**
	 * Get active encryption key info, generating one if missing.
	 *
	 * @return array{key:string,id:string,prev_key:string,prev_id:string}
	 */
	private static function get_encryption_key_info(): array {
		$curr_key = '';
		$curr_id  = '';
		$prev_key = (string) get_option( 'timu_vault_prev_key', '' );
		$prev_id  = (string) get_option( 'timu_vault_prev_key_id', '' );

		if ( defined( 'TIMU_VAULT_KEY' ) && TIMU_VAULT_KEY ) {
			$curr_key = (string) TIMU_VAULT_KEY;
			$curr_id  = (string) get_option( 'timu_vault_key_id', 'const' );
		} else {
			$curr_key = (string) get_option( 'timu_vault_enc_key', '' );
			$curr_id  = (string) get_option( 'timu_vault_key_id', '' );
			if ( empty( $curr_key ) ) {
				$key_bytes = function_exists( 'random_bytes' ) ? random_bytes( 32 ) : openssl_random_pseudo_bytes( 32 );
				$curr_key  = bin2hex( $key_bytes ?: uniqid( 'timu', true ) );
				$curr_id   = 'k_' . (string) wp_generate_password( 12, false, false );
				update_option( 'timu_vault_enc_key', $curr_key, false );
				update_option( 'timu_vault_key_id', $curr_id, false );
			}
		}

		return array(
			'key'      => $curr_key,
			'id'       => $curr_id ?: 'default',
			'prev_key' => $prev_key,
			'prev_id'  => $prev_id,
		);
	}

	/**
	 * Select the correct key for an attachment based on stored key id.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return string|false Key material or false if unavailable.
	 */
	private static function select_key_for_attachment( int $attachment_id ) {
		$key_info  = self::get_encryption_key_info();
		$stored_id = (string) get_post_meta( $attachment_id, self::META_KEY_ID, true );
		if ( empty( $stored_id ) || $stored_id === $key_info['id'] ) {
			return $key_info['key'];
		}
		if ( $stored_id === $key_info['prev_id'] ) {
			return $key_info['prev_key'] ?: false;
		}
		return false;
	}

	/**
	 * Rotate encryption key: set a new active key and preserve previous.
	 * Does not re-encrypt existing files; use reencrypt_attachment() for that.
	 *
	 * @param string $new_key Optional explicit new key.
	 * @return bool
	 */
	public static function rotate_key( string $new_key = '' ): bool {
		$info = self::get_encryption_key_info();
		update_option( 'timu_vault_prev_key', $info['key'], false );
		update_option( 'timu_vault_prev_key_id', $info['id'], false );

		if ( empty( $new_key ) ) {
			$key_bytes = function_exists( 'random_bytes' ) ? random_bytes( 32 ) : openssl_random_pseudo_bytes( 32 );
			$new_key   = bin2hex( $key_bytes ?: uniqid( 'timu', true ) );
		}

		$new_id = 'k_' . (string) wp_generate_password( 12, false, false );
		update_option( 'timu_vault_enc_key', $new_key, false );
		update_option( 'timu_vault_key_id', $new_id, false );
		return true;
	}

	/**
	 * Re-encrypt a single vaulted attachment with the current active key.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	public static function reencrypt_attachment( int $attachment_id ): bool {
		$path = (string) get_post_meta( $attachment_id, self::META_PATH, true );
		if ( empty( $path ) ) {
			return false;
		}
		$absolute = self::absolute_path( $path );
		if ( ! file_exists( $absolute ) ) {
			return false;
		}

		$key_curr = self::get_encryption_key_info();
		$key_old  = self::select_key_for_attachment( $attachment_id );
		if ( ! $key_old ) {
			$key_old = $key_curr['prev_key'] ?: $key_curr['key'];
		}

		$plaintext = self::decrypt_file( $absolute, $key_old );
		if ( false === $plaintext ) {
			return false;
		}

		$temp = wp_tempnam( 'timu_reenc_' );
		if ( ! $temp ) {
			return false;
		}
		if ( false === file_put_contents( $temp, $plaintext, LOCK_EX ) ) {
			unlink( $temp );
			return false;
		}

		$ok = self::encrypt_file_gcm( $temp, $key_curr['key'] );
		if ( ! $ok ) {
			unlink( $temp );
			return false;
		}

		$copied = copy( $temp, $absolute );
		unlink( $temp );
		if ( ! $copied ) {
			return false;
		}

		update_post_meta( $attachment_id, self::META_ENCRYPTED, 'gcm' );
		update_post_meta( $attachment_id, self::META_KEY_ID, $key_curr['id'] );
		return true;
	}

	/**
	 * Get vault directory path.
	 *
	 * @return string|null Vault directory path, or null if not found.
	 */
	public static function get_vault_path(): ?string {
		$vault_dirname = get_option( 'timu_vault_dirname' );
		if ( empty( $vault_dirname ) ) {
			return null;
		}

		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'] . DIRECTORY_SEPARATOR . $vault_dirname;
	}

	/**
	 * Get vault URL for download endpoint reference.
	 *
	 * @return string|null Vault directory URL, or null if not found.
	 */
	public static function get_vault_url(): ?string {
		$vault_dirname = get_option( 'timu_vault_dirname' );
		if ( empty( $vault_dirname ) ) {
			return null;
		}

		$upload_dir = wp_upload_dir();
		return $upload_dir['baseurl'] . '/' . $vault_dirname;
	}

	/**
	 * Ensure Vault directory is initialized with a randomized name and protected from web access.
	 *
	 * @return void
	 */
	private static function ensure_vault_directory(): void {
		$vault_dirname = (string) get_option( 'timu_vault_dirname' );
		if ( empty( $vault_dirname ) ) {
			$vault_dirname = self::generate_vault_dirname();
			update_option( 'timu_vault_dirname', $vault_dirname, false );
		}

		$uploads = wp_upload_dir();
		$vault   = trailingslashit( $uploads['basedir'] ) . $vault_dirname;

		// Create base vault directory.
		wp_mkdir_p( $vault );

		// Add minimal index.php to prevent directory listing.
		$index_path = trailingslashit( $vault ) . 'index.php';
		if ( ! file_exists( $index_path ) ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@file_put_contents( $index_path, '<?php http_response_code(404); exit;' );
		}

		// Add .htaccess rules to deny all web access.
		$htaccess_path = trailingslashit( $vault ) . '.htaccess';
		if ( ! file_exists( $htaccess_path ) ) {
			$htaccess = "Options -Indexes\n" .
				"<IfModule mod_authz_core.c>\n" .
				"    Require all denied\n" .
				"</IfModule>\n" .
				"<IfModule !mod_authz_core.c>\n" .
				"    Deny from all\n" .
				"</IfModule>\n";
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@file_put_contents( $htaccess_path, $htaccess );
		}

		// Add IIS web.config to deny all.
		$webconfig_path = trailingslashit( $vault ) . 'web.config';
		if ( ! file_exists( $webconfig_path ) ) {
			$webconfig = '<configuration><system.webServer><security><authorization>' .
				'<remove users="*" roles="" verbs=""/>' .
				'<add accessType="Deny" users="*"/></authorization></security>' .
				'<directoryBrowse enabled="false"/></system.webServer></configuration>';
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@file_put_contents( $webconfig_path, $webconfig );
		}
	}

	/**
	 * Add entry to attachment journal and global ledger.
	 *
	 * @param int   $attachment_id Attachment post ID.
	 * @param array $entry         Journal entry with op, user_id, args, before_hash, after_hash.
	 * @return bool Success status.
	 */
	public static function add_journal_entry( int $attachment_id, array $entry ): bool {
		if ( ! $attachment_id || $attachment_id < 1 ) {
			return false;
		}

		// Validate required fields.
		if ( empty( $entry['op'] ) ) {
			return false;
		}

		// Build complete entry.
		$journal_entry = array(
			'ts'          => gmdate( 'Y-m-d\TH:i:s\Z' ),
			'user_id'     => $entry['user_id'] ?? get_current_user_id(),
			'op'          => sanitize_key( $entry['op'] ),
			'args'        => $entry['args'] ?? array(),
			'before_hash' => $entry['before_hash'] ?? null,
			'after_hash'  => $entry['after_hash'] ?? null,
		);

		// Get existing journal.
		$journal = get_post_meta( $attachment_id, self::META_JOURNAL, true );
		if ( ! is_array( $journal ) || empty( $journal['operations'] ) ) {
			$journal = array(
				'attachment_id' => $attachment_id,
				'created'       => gmdate( 'Y-m-d\TH:i:s\Z' ),
				'operations'    => array(),
			);
		}

		// Append entry.
		$journal['operations'][] = $journal_entry;

		// Save journal.
		update_post_meta( $attachment_id, self::META_JOURNAL, $journal );

		// Add to global ledger.
		self::add_ledger_entry(
			array(
				'attachment_id' => $attachment_id,
				'op'            => $journal_entry['op'],
				'user_id'       => $journal_entry['user_id'],
				'success'       => true,
			)
		);

		return true;
	}

	/**
	 * Get journal for attachment.
	 *
	 * @param int $attachment_id Attachment post ID.
	 * @return array|null Journal array or null if not found.
	 */
	public static function get_journal( int $attachment_id ): ?array {
		if ( ! $attachment_id || $attachment_id < 1 ) {
			return null;
		}

		$journal = get_post_meta( $attachment_id, self::META_JOURNAL, true );

		if ( ! is_array( $journal ) || empty( $journal['operations'] ) ) {
			return null;
		}

		return $journal;
	}

	/**
	 * Add entry to global ledger.
	 *
	 * @param array $entry Ledger entry with attachment_id, op, user_id, success.
	 * @return void
	 */
	private static function add_ledger_entry( array $entry ): void {
		$ledger = get_option( self::LEDGER_OPTION, array() );

		if ( ! is_array( $ledger ) ) {
			$ledger = array();
		}

		// Build complete entry.
		$ledger_entry = array(
			'ts'            => gmdate( 'Y-m-d\TH:i:s\Z' ),
			'site_id'       => get_current_blog_id(),
			'attachment_id' => $entry['attachment_id'] ?? 0,
			'user_id'       => $entry['user_id'] ?? 0,
			'op'            => sanitize_key( $entry['op'] ?? '' ),
			'success'       => (bool) ( $entry['success'] ?? false ),
		);

		// Append entry.
		$ledger[] = $ledger_entry;

		// Rotate if exceeds limit.
		if ( count( $ledger ) > self::LEDGER_MAX_ENTRIES ) {
			// Keep most recent entries.
			$ledger = array_slice( $ledger, -self::LEDGER_MAX_ENTRIES );
		}

		update_option( self::LEDGER_OPTION, $ledger, false );
	}

	/**
	 * Get global ledger entries.
	 *
	 * @param array $args Optional filters: since, until, op, attachment_id, limit.
	 * @return array Ledger entries.
	 */
	public static function get_global_ledger( array $args = array() ): array {
		$ledger = get_option( self::LEDGER_OPTION, array() );

		if ( ! is_array( $ledger ) ) {
			return array();
		}

		// Apply filters.
		if ( ! empty( $args['since'] ) ) {
			$ledger = array_filter(
				$ledger,
				function ( $entry ) use ( $args ) {
					return isset( $entry['ts'] ) && $entry['ts'] >= $args['since'];
				}
			);
		}

		if ( ! empty( $args['until'] ) ) {
			$ledger = array_filter(
				$ledger,
				function ( $entry ) use ( $args ) {
					return isset( $entry['ts'] ) && $entry['ts'] <= $args['until'];
				}
			);
		}

		if ( ! empty( $args['op'] ) ) {
			$ledger = array_filter(
				$ledger,
				function ( $entry ) use ( $args ) {
					return isset( $entry['op'] ) && $entry['op'] === $args['op'];
				}
			);
		}

		if ( ! empty( $args['attachment_id'] ) ) {
			$ledger = array_filter(
				$ledger,
				function ( $entry ) use ( $args ) {
					return isset( $entry['attachment_id'] ) && (int) $entry['attachment_id'] === (int) $args['attachment_id'];
				}
			);
		}

		// Apply limit.
		if ( ! empty( $args['limit'] ) && is_int( $args['limit'] ) ) {
			$ledger = array_slice( $ledger, -abs( $args['limit'] ) );
		}

		return array_values( $ledger );
	}

	/**
	 * Generate a randomized, hard-to-guess vault directory name.
	 *
	 * @return string
	 */
	private static function generate_vault_dirname(): string {
		$rand = '';
		if ( function_exists( 'random_bytes' ) ) {
			$rand = bin2hex( random_bytes( 16 ) );
		} else {
			$rand = wp_generate_password( 32, false, false );
		}
		$rand   = strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '', (string) $rand ) );
		$prefix = 'vault-';
		return $prefix . $rand;
	}
}
