<?php
/**
 * 404 Monitor Diagnostic
 *
 * Tracks 404 errors and detects broken internal links.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26030.2000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 404 Monitor Diagnostic
 *
 * Detects high rates of 404 errors which indicate broken links or deleted content.
 *
 * @since 1.26030.2000
 */
class Diagnostic_404_Monitor extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = '404-monitor';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = '404 Error Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors 404 errors and identifies broken links';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'utilities';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26030.2000
	 * @return array|null Finding array if excessive 404s detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check for 404 monitoring capability (would be populated by logging)
		$four_oh_fours = get_option( 'wpshadow_404_count_24h', 0 );

		// Flag if we've had more than 10 404s in the last 24 hours
		if ( $four_oh_fours > 10 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of 404 errors */
					__( 'Detected %d 404 errors in the last 24 hours. Broken links hurt SEO and user experience. Use the 404 Monitor to identify and fix them.', 'wpshadow' ),
					$four_oh_fours
				),
				'severity'    => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/404-monitor',
			);
		}

		return null;
	}
}
