<?php
/**
 * Widget Customization Not Implemented Diagnostic
 *
 * Checks if widgets are customized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2320
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget Customization Not Implemented Diagnostic Class
 *
 * Detects generic widget configuration.
 *
 * @since 1.2601.2320
 */
class Diagnostic_Widget_Customization_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'widget-customization-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Widget Customization Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if widgets are customized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2320
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get widget settings
		$widget_option = get_option( 'widget_settings' );

		// Check if sidebar has widgets
		$sidebars_widgets = wp_get_sidebars_widgets();

		if ( empty( $sidebars_widgets ) || empty( array_filter( $sidebars_widgets ) ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No widgets are active in any sidebar. Add widgets to improve content organization and UX.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/widget-customization-not-implemented',
			);
		}

		return null;
	}
}
