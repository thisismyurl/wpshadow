<?php
/**
 * Auto-Save Functionality Diagnostic
 *
 * Verifies WordPress auto-save is working correctly. Tests auto-save
 * intervals and data persistence.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1324
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auto-Save Functionality Diagnostic Class
 *
 * Verifies that WordPress auto-save feature is properly configured
 * and functioning for content preservation.
 *
 * @since 1.6033.1324
 */
class Diagnostic_Auto_Save_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'auto-save-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Auto-Save Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies WordPress auto-save is working correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1324
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if AUTOSAVE_INTERVAL is disabled.
		if ( defined( 'AUTOSAVE_INTERVAL' ) && AUTOSAVE_INTERVAL === false ) {
			$issues[] = __( 'Auto-save is completely disabled', 'wpshadow' );
		}

		// Check if auto-save interval is too high (default is 60 seconds).
		$autosave_interval = defined( 'AUTOSAVE_INTERVAL' ) ? AUTOSAVE_INTERVAL : 60;
		if ( is_numeric( $autosave_interval ) && $autosave_interval > 300 ) {
			$issues[] = sprintf(
				/* translators: %d: interval in seconds */
				__( 'Auto-save interval is very long (%d seconds)', 'wpshadow' ),
				$autosave_interval
			);
		}

		// Check for recent autosave posts (should exist if feature is working).
		$recent_autosaves = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_status = 'inherit'
				AND post_type = 'revision'
				AND post_name LIKE %s
				AND post_modified > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
				'%-autosave-%'
			)
		);

		// If there are active users but no recent autosaves, it might indicate a problem.
		$recent_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_modified > DATE_SUB(NOW(), INTERVAL 24 HOUR)
				AND post_status IN ('draft', 'pending')
				AND post_type IN ('post', 'page')"
			)
		);

		if ( $recent_posts > 0 && $recent_autosaves === 0 ) {
			$issues[] = __( 'No autosaves detected despite recent editing activity', 'wpshadow' );
		}

		// Check if Heartbeat API is disabled (required for auto-save).
		if ( defined( 'WP_ADMIN_HEARTBEAT_DISABLE' ) && WP_ADMIN_HEARTBEAT_DISABLE ) {
			$issues[] = __( 'Heartbeat API is disabled (required for auto-save)', 'wpshadow' );
		}

		// Check JavaScript availability (auto-save requires JS).
		$theme_has_wp_footer = false;
		$active_theme        = wp_get_theme();
		$theme_dir           = $active_theme->get_template_directory();
		$footer_file         = $theme_dir . '/footer.php';

		if ( file_exists( $footer_file ) ) {
			$footer_content      = file_get_contents( $footer_file );
			$theme_has_wp_footer = strpos( $footer_content, 'wp_footer' ) !== false;
		}

		if ( ! $theme_has_wp_footer ) {
			$issues[] = __( 'Theme may be missing wp_footer() call (breaks auto-save)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/auto-save-functionality',
			);
		}

		return null;
	}
}
