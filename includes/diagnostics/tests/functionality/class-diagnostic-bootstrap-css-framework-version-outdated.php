<?php
/**
 * Bootstrap CSS Framework Version Outdated Diagnostic
 *
 * Checks if Bootstrap is outdated.
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
 * Bootstrap CSS Framework Version Outdated Diagnostic Class
 *
 * Detects outdated Bootstrap.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Bootstrap_CSS_Framework_Version_Outdated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'bootstrap-css-framework-version-outdated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Bootstrap CSS Framework Version Outdated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Bootstrap is outdated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Bootstrap is loaded
		if ( has_filter( 'wp_enqueue_scripts', 'enqueue_bootstrap' ) ) {
			// Check version
			if ( ! get_option( 'bootstrap_version_check_date' ) ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => __( 'Bootstrap CSS framework may be outdated. Update to the latest stable version for security patches and new features.', 'wpshadow' ),
					'severity'      => 'low',
					'threat_level'  => 15,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/bootstrap-css-framework-version-outdated',
				);
			}
		}

		return null;
	}
}
