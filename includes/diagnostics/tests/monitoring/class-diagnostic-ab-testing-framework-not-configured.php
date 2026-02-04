<?php
/**
 * A/B Testing Framework Not Configured Diagnostic
 *
 * Checks if A/B testing framework is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A/B Testing Framework Not Configured Diagnostic Class
 *
 * Detects missing A/B testing framework.
 *
 * @since 1.6030.2352
 */
class Diagnostic_AB_Testing_Framework_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ab-testing-framework-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'A/B Testing Framework Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if A/B testing framework is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for A/B testing capability
		if ( ! has_filter( 'init', 'initialize_ab_testing' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'A/B testing framework is not configured. Implement testing framework for headlines, CTAs, and layouts to optimize conversion rates through data-driven decisions.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/ab-testing-framework-not-configured',
			);
		}

		return null;
	}
}
