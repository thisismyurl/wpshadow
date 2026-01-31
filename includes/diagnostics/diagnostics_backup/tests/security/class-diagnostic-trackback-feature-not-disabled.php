<?php
/**
 * Trackback Feature Not Disabled Diagnostic
 *
 * Checks if trackbacks are disabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Trackback Feature Not Disabled Diagnostic Class
 *
 * Detects enabled trackbacks.
 *
 * @since 1.2601.2330
 */
class Diagnostic_Trackback_Feature_Not_Disabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'trackback-feature-not-disabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Trackback Feature Not Disabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if trackbacks are disabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if trackbacks are enabled
		$default_trackback_status = get_option( 'default_trackback_status' );

		if ( 'open' === $default_trackback_status ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Trackbacks are enabled. Disable them to reduce spam and security risks.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/trackback-feature-not-disabled',
			);
		}

		return null;
	}
}
