<?php
/**
 * API Rate Limiting Not Enforced Diagnostic
 *
 * Checks rate limiting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_API_Rate_Limiting_Not_Enforced Class
 *
 * Performs diagnostic check for API rate limiting enforcement.
 *
 * @since 0.6093.1200
 */
class Diagnostic_API_Rate_Limiting_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'api-rate-limiting-not-enforced';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'API Rate Limiting Not Enforced';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks rate limiting';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'rest_dispatch_request', 'check_rate_limit' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'API rate limiting is not enforced yet. Adding request throttling can reduce abuse and improve service stability.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/api-rate-limiting-not-enforced?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
