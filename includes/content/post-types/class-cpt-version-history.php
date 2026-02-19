<?php
/**
 * CPT Version History (Vault Lite Feature)
 *
 * Provides content versioning for custom post types.
 * Part of Vault Lite backup functionality.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since      1.6090.2359
 */

declare(strict_types=1);

namespace WPShadow\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT_Version_History Class
 *
 * Tracks and manages content versions for rollback capability.
 *
 * @since 1.6090.2359
 */
class CPT_Version_History {

	/**
	 * Maximum versions to keep per post.
	 *
	 * @var int
	 */
	const MAX_VERSIONS = 10;

	/**
	 * Initialize version history system.
	 *
	 * @since 1.6034.1415
	 * @return void
	 */
	public static function init() {
		add_action( 'save_post', array( __CLASS__, 'save_version' ), 10, 2 );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_version_meta_box' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_restore_version', array( __CLASS__, 'handle_restore_version' ) );
		add_action( 'wp_ajax_wpshadow_delete_version', array( __CLASS__, 'handle_delete_version' ) );
	}

	/**
	 * Save a new version on post update.
	 *
	 * @since  1.6034.1415
	 * @param  int      $post_id Post ID.
	 * @param  \WP_Post $post    Post object.
	 * @return void
	 */
	public static function save_version( $post_id, $post ) {
		// Skip autosaves and revisions.
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Verify post type exists.
		if ( ! post_type_exists( $post->post_type ) ) {
			return;
		}

		// Only for our CPTs.
		$supported = array( 'testimonial', 'team_member', 'portfolio_item', 'wps_event', 'resource', 'case_study', 'service', 'location', 'documentation', 'wps_product' );
		if ( ! in_array( $post->post_type, $supported, true ) ) {
			return;
		}

		// Get existing versions.
		$versions = get_post_meta( $post_id, '_wpshadow_versions', true );
		$versions = $versions ? $versions : array();

		// Create new version.
		$version = array(
			'timestamp'     => current_time( 'timestamp' ),
			'user_id'       => get_current_user_id(),
			'post_title'    => $post->post_title,
			'post_content'  => $post->post_content,
			'post_excerpt'  => $post->post_excerpt,
			'post_status'   => $post->post_status,
			'post_meta'     => get_post_meta( $post_id ),
		);

		// Add to versions array.
		array_unshift( $versions, $version );

		// Limit versions.
		$versions = array_slice( $versions, 0, self::MAX_VERSIONS );

		// Save versions.
		update_post_meta( $post_id, '_wpshadow_versions', $versions );
	}

	/**
	 * Add version history meta box.
	 *
	 * @since 1.6034.1415
	 * @return void
	 */
	public static function add_version_meta_box() {
		$post_types = array( 'testimonial', 'team_member', 'portfolio_item', 'wps_event', 'resource', 'case_study', 'service', 'location', 'documentation', 'wps_product' );

		foreach ( $post_types as $post_type ) {
			// Only add meta box if post type exists.
			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			add_meta_box(
				'wpshadow_version_history',
				__( 'Version History', 'wpshadow' ) . ' <span class="wpshadow-vault-badge">Vault Lite</span>',
				array( __CLASS__, 'render_version_meta_box' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render version history meta box.
	 *
	 * @since  1.6034.1415
	 * @param  \WP_Post $post Current post object.
	 * @return void
	 */
	public static function render_version_meta_box( $post ) {
		$versions = get_post_meta( $post->ID, '_wpshadow_versions', true );
		$versions = $versions ? $versions : array();
		?>
		<div class="wpshadow-version-history">
			<?php if ( empty( $versions ) ) : ?>
				<p><?php esc_html_e( 'No versions saved yet.', 'wpshadow' ); ?></p>
			<?php else : ?>
				<ul class="wpshadow-versions-list">
					<?php foreach ( $versions as $index => $version ) : ?>
						<li class="wpshadow-version-item" data-index="<?php echo esc_attr( $index ); ?>">
							<div class="version-info">
								<strong><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $version['timestamp'] ) ); ?></strong>
								<br>
								<span class="version-user">
									<?php
									$user = get_userdata( $version['user_id'] );
									echo esc_html( $user ? $user->display_name : __( 'Unknown', 'wpshadow' ) );
									?>
								</span>
							</div>
							<div class="version-actions">
								<button type="button" class="button button-small wpshadow-restore-version" 
									data-post-id="<?php echo esc_attr( $post->ID ); ?>"
									data-version-index="<?php echo esc_attr( $index ); ?>">
									<?php esc_html_e( 'Restore', 'wpshadow' ); ?>
								</button>
								<button type="button" class="button button-small button-link-delete wpshadow-delete-version"
									data-post-id="<?php echo esc_attr( $post->ID ); ?>"
									data-version-index="<?php echo esc_attr( $index ); ?>">
									<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
								</button>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
				<p class="description">
					<?php
					/* translators: %d: Maximum number of versions */
					printf( esc_html__( 'Keeping last %d versions.', 'wpshadow' ), self::MAX_VERSIONS );
					?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Enqueue version history assets.
	 *
	 * @since  1.6034.1415
	 * @param  string $hook Current page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-version-history',
			WPSHADOW_URL . 'assets/js/cpt-version-history.js',
			array( 'jquery', 'wp-util' ),
			WPSHADOW_VERSION,
			true
		);

		\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
			'wpshadow-version-history',
			'wpShadowVersions',
			'wpshadow_version_history'
		);

		wp_enqueue_style(
			'wpshadow-version-history',
			WPSHADOW_URL . 'assets/css/cpt-version-history.css',
			array(),
			WPSHADOW_VERSION
		);
	}

	/**
	 * Handle restore version AJAX request.
	 *
	 * @since 1.6034.1415
	 * @return void
	 */
	public static function handle_restore_version() {
		check_ajax_referer( 'wpshadow_version_history', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$version_index = isset( $_POST['version_index'] ) ? absint( $_POST['version_index'] ) : -1;

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$versions = get_post_meta( $post_id, '_wpshadow_versions', true );

		if ( ! isset( $versions[ $version_index ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Version not found', 'wpshadow' ) ) );
		}

		$version = $versions[ $version_index ];

		// Restore post content.
		$result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_title'   => $version['post_title'],
				'post_content' => $version['post_content'],
				'post_excerpt' => $version['post_excerpt'],
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		// Restore meta (excluding versions meta itself).
		if ( ! empty( $version['post_meta'] ) ) {
			foreach ( $version['post_meta'] as $meta_key => $meta_value ) {
				if ( '_wpshadow_versions' === $meta_key ) {
					continue;
				}
				update_post_meta( $post_id, $meta_key, $meta_value[0] );
			}
		}

		wp_send_json_success( array(
			'message' => __( 'Version restored successfully', 'wpshadow' ),
		) );
	}

	/**
	 * Handle delete version AJAX request.
	 *
	 * @since 1.6034.1415
	 * @return void
	 */
	public static function handle_delete_version() {
		check_ajax_referer( 'wpshadow_version_history', 'nonce' );

		$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		$version_index = isset( $_POST['version_index'] ) ? absint( $_POST['version_index'] ) : -1;

		if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$versions = get_post_meta( $post_id, '_wpshadow_versions', true );

		if ( ! isset( $versions[ $version_index ] ) ) {
			wp_send_json_error( array( 'message' => __( 'Version not found', 'wpshadow' ) ) );
		}

		// Remove version.
		unset( $versions[ $version_index ] );
		$versions = array_values( $versions ); // Re-index array.

		update_post_meta( $post_id, '_wpshadow_versions', $versions );

		wp_send_json_success( array(
			'message' => __( 'Version deleted successfully', 'wpshadow' ),
		) );
	}
}
