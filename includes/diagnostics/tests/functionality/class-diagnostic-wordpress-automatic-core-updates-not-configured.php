<?php
/**
 * WordPress Automatic Core Updates Not Configured Diagnostic
 *
 * Checks if automatic core updates are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Automatic Core Updates Not Configured Diagnostic Class
 *
 * Detects disabled automatic core updates.
 *
 * @since 1.2601.2315
 */
class Diagnostic_WordPress_Automatic_Core_Updates_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-automatic-core-updates-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Automatic Core Updates Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if automatic core updates are enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check automatic updates setting
		$auto_updates_core = get_option( 'auto_update_core_dev' ) || get_option( 'auto_update_core_minor' ) || get_option( 'auto_update_core_major' );

		if ( ! $auto_updates_core ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Automatic WordPress core updates are disabled. Keeping WordPress updated automatically improves security.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/wordpress-automatic-core-updates-not-configured',
			);
		}

		return null;
	}
}
