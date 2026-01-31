<?php
/**
 * Theme Update Notification Missing Diagnostic
 *
 * Checks if theme updates are monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2325
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Update Notification Missing Diagnostic Class
 *
 * Detects missing theme update notifications.
 *
 * @since 1.2601.2325
 */
class Diagnostic_Theme_Update_Notification_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-update-notification-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Update Notification Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme updates are monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2325
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if theme has updates available
		$update_themes = get_transient( 'update_themes' );

		if ( $update_themes && ! empty( $update_themes->response ) ) {
			$current_theme = get_template();
			if ( isset( $update_themes->response[ $current_theme ] ) ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Theme updates are available. Keep themes updated for security patches and new features.', 'wpshadow' ),
					'severity'      => 'medium',
					'threat_level'  => 40,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/theme-update-notification-missing',
				);
			}
		}

		return null;
	}
}
