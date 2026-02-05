<?php
/**
 * Theme Active & Valid Treatment
 *
 * Ensures the active theme exists and is properly loaded.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Theme_Active_Valid Class
 *
 * Checks that the active theme exists and is not broken.
 *
 * @since 1.6035.1400
 */
class Treatment_Theme_Active_Valid extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-active-valid';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Active & Valid';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures the active theme exists and is valid';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();

		if ( ! $theme->exists() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The active theme is missing or invalid. This can break site rendering.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-active-valid',
			);
		}

		if ( ! $theme->is_allowed() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The active theme is not allowed for this site. Verify theme permissions.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-active-valid',
				'meta'         => array(
					'theme' => $theme->get_stylesheet(),
				),
			);
		}

		return null;
	}
}