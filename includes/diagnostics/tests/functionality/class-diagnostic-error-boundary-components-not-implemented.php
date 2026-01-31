<?php
/**
 * Error Boundary Components Not Implemented Diagnostic
 *
 * Checks if error boundary components are implemented.
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
 * Error Boundary Components Not Implemented Diagnostic Class
 *
 * Detects missing error boundary implementation.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Error_Boundary_Components_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'error-boundary-components-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Error Boundary Components Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if error boundary components are implemented';

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
		// Check for error boundary implementation
		if ( ! has_filter( 'wp_footer', 'add_error_boundary_script' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Error boundary components are not implemented. Catch JavaScript errors in component trees to prevent full page crashes and show graceful fallback UIs to users.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/error-boundary-components-not-implemented',
			);
		}

		return null;
	}
}
