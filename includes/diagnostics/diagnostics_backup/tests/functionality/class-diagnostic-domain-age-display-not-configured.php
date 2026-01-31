<?php
/**
 * Domain Age Display Not Configured Diagnostic
 *
 * Checks if domain age is displayed to users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Domain Age Display Not Configured Diagnostic Class
 *
 * Detects missing domain age information.
 *
 * @since 1.2601.2350
 */
class Diagnostic_Domain_Age_Display_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'domain-age-display-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Domain Age Display Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if domain age is displayed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site has footer info (typically shows age)
		if ( ! has_action( 'wp_footer', 'wp_footer_info' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Domain age is not displayed to users. Show domain age in footer to build trust and credibility.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/domain-age-display-not-configured',
			);
		}

		return null;
	}
}
