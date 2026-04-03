<?php
/**
 * File Write Review Page
 *
 * Registers a hidden WPShadow admin page (accessible via notice link or direct
 * URL) that presents each pending file-write treatment with:
 *   - The exact text that would be inserted/modified in the target file
 *   - A side-by-side diff preview (via AJAX dry-run)
 *   - A one-click backup button (stores current file content in DB)
 *   - A restore button (restores from the stored backup)
 *   - An "Apply Fix" button that opens the SFTP acknowledgment modal first
 *
 * The page is deliberately hidden from the sidebar menu (remove_submenu_page)
 * to avoid confusion. It is always accessible via the notice link.
 *
 * Philosophy: Commandment #8 (Inspire Confidence) — full transparency before
 * any file is touched; Commandment #5 (Stay Out of the Way) — minimal friction
 * once the admin has read the instructions.
 *
 * @package WPShadow
 * @subpackage Admin\Pages
 * @since 0.6093.1300
 */

namespace WPShadow\Admin\Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File Write Review admin page.
 */
class File_Write_Review_Page {

	/**
	 * WordPress admin page slug.
	 */
	const PAGE_SLUG = 'wpshadow-file-review';

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_menu', [ __CLASS__, 'register_page' ] );
		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
	}

	/**
	 * Register the hidden admin submenu page.
	 *
	 * @return void
	 */
	public static function register_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Review File Changes', 'wpshadow' ),
			__( 'Review File Changes', 'wpshadow' ),
			'manage_options',
			self::PAGE_SLUG,
			[ __CLASS__, 'render' ]
		);

		// Hide from sidebar — accessible only via the notice link.
		remove_submenu_page( 'wpshadow', self::PAGE_SLUG );
	}

	/**
	 * Enqueue page-specific assets.
	 *
	 * @param string $hook Current admin page hook suffix.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		// Only load on this specific page.
		if ( false === strpos( $hook, self::PAGE_SLUG ) ) {
			return;
		}

		// Modal styling is shared when available.
		if ( class_exists( '\\WPShadow\\Core\\Admin_Asset_Registry' ) ) {
			\WPShadow\Core\Admin_Asset_Registry::enqueue_modal_assets();
		}

		// Page JS.
		wp_enqueue_script(
			'wpshadow-file-write-review',
			WPSHADOW_URL . 'assets/js/file-write-review.js',
			[ 'jquery' ],
			file_exists( WPSHADOW_PATH . 'assets/js/file-write-review.js' )
				? (string) filemtime( WPSHADOW_PATH . 'assets/js/file-write-review.js' )
				: WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-file-write-review',
			'wpshadowFileReview',
			[
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonces'     => [
					'backup'  => wp_create_nonce( 'wpshadow_file_write_backup' ),
					'dryRun'  => wp_create_nonce( 'wpshadow_file_write_dry_run' ),
					'apply'   => wp_create_nonce( 'wpshadow_file_write_apply' ),
					'restore' => wp_create_nonce( 'wpshadow_file_write_restore' ),
				],
				'i18n'       => [
					'backupSuccess'   => __( 'Backup created successfully.', 'wpshadow' ),
					'backupFailed'    => __( 'Backup failed. Please try again.', 'wpshadow' ),
					'dryRunPending'   => __( 'Running preview…', 'wpshadow' ),
					'applySuccess'    => __( 'Fix applied successfully.', 'wpshadow' ),
					'applyFailed'     => __( 'Fix could not be applied. Please check file permissions.', 'wpshadow' ),
					'restoreSuccess'  => __( 'File restored from backup.', 'wpshadow' ),
					'restoreFailed'   => __( 'Restore failed.', 'wpshadow' ),
					'confirmRestore'  => __( 'Restore the file to its state when the backup was created? The current file will be overwritten.', 'wpshadow' ),
					'ackRequired'     => __( 'Please read and acknowledge the recovery instructions before applying.', 'wpshadow' ),
				],
			]
		);
	}

	/**
	 * Render the page by loading the PHP view template.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'wpshadow' ) );
		}

		$view_file = WPSHADOW_PATH . 'includes/ui/views/file-write-review-page.php';

		if ( ! file_exists( $view_file ) ) {
			wp_die( esc_html__( 'View template not found.', 'wpshadow' ) );
		}

		// Collect pending file-write treatments for the view.
		$pending = \WPShadow\Admin\File_Write_Registry::get_pending();

		include $view_file;
	}
}
