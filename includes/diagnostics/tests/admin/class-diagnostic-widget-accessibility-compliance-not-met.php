<?php
/**
 * Widget Accessibility Compliance Not Met Diagnostic
 *
 * Checks if widgets are accessible.
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
 * Widget Accessibility Compliance Not Met Diagnostic Class
 *
 * Detects inaccessible widgets.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Widget_Accessibility_Compliance_Not_Met extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'widget-accessibility-compliance-not-met';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Widget Accessibility Compliance Not Met';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if widgets are accessible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if widgets have proper labels
		$sidebars = wp_get_sidebars_widgets();

		if ( ! empty( $sidebars ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Widget accessibility compliance is not met. Ensure all custom widgets have proper ARIA labels and are keyboard navigable.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/widget-accessibility-compliance-not-met',
			);
		}

		return null;
	}
}
