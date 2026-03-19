<?php
/**
 * Rate Limiting On Forms Not Configured Diagnostic
 *
 * Checks form rate limiting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Rate_Limiting_On_Forms_Not_Configured Class
 *
 * Performs diagnostic check for Rate Limiting On Forms Not Configured.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Rate_Limiting_On_Forms_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rate-limiting-on-forms-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Rate Limiting On Forms Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks form rate limiting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'wp_authenticate', 'check_login_rate_limit' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Rate limiting on form-related authentication does not appear configured. Limiting repeated attempts helps reduce brute-force abuse.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/rate-limiting-on-forms-not-configured',
			);
		}

		return null;
	}
}
