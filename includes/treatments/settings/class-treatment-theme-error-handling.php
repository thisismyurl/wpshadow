<?php
/**
 * Theme Error Handling Treatment
 *
 * Checks theme error handling and fallback templates.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Error Handling Treatment
 *
 * Ensures theme provides basic error and 404 handling.
 *
 * @since 1.6030.2240
 */
class Treatment_Theme_Error_Handling extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-error-handling';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Error Handling';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme error handling and fallback templates';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();

		$issues = array();
		$templates = array(
			'404.php' => __( '404 template', 'wpshadow' ),
			'search.php' => __( 'Search template', 'wpshadow' ),
			'index.php' => __( 'Index template', 'wpshadow' ),
		);

		foreach ( $templates as $file => $label ) {
			if ( ! file_exists( $theme_dir . '/' . $file ) ) {
				$issues[] = sprintf(
					/* translators: %s: template name */
					__( 'Missing %s in active theme', 'wpshadow' ),
					$label
				);
			}
		}

		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$issues[] = __( 'WP_DEBUG_DISPLAY is enabled - errors may show to visitors', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme error handling and fallback templates need attention', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-error-handling',
				'details'      => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
