<?php
/**
 * Referrer Policy Not Set Diagnostic
 *
 * Checks if referrer policy is set.
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
 * Referrer Policy Not Set Diagnostic Class
 *
 * Detects missing referrer policy.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Referrer_Policy_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'referrer-policy-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Referrer Policy Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if referrer policy is set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for referrer policy header
		if ( ! has_filter( 'wp_headers', 'add_referrer_policy' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Referrer policy is not set. Set Referrer-Policy: strict-origin-when-cross-origin to control what referrer information is sent with external links.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/referrer-policy-not-set?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
