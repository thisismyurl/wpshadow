<?php
/**
 * GDPR Cookie Compliance Not Fully Implemented Diagnostic
 *
 * Checks if GDPR compliance is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GDPR Cookie Compliance Not Fully Implemented Diagnostic Class
 *
 * Detects missing GDPR compliance.
 *
 * @since 1.2601.2352
 */
class Diagnostic_GDPR_Cookie_Compliance_Not_Fully_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-cookie-compliance-not-fully-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Cookie Compliance Not Fully Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if GDPR compliance is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for GDPR compliance plugins
		if ( ! is_plugin_active( 'gdpr-cookie-compliance/gdpr-cookie-compliance.php' ) && ! is_plugin_active( 'cookiebot/cookiebot.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'GDPR cookie compliance is not fully implemented. Configure cookie consent management to comply with GDPR and other privacy regulations.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gdpr-cookie-compliance-not-fully-implemented',
			);
		}

		return null;
	}
}
