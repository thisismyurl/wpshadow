<?php
/**
 * Environmental Variable Exposure Not Prevented Treatment
 *
 * Checks env variable exposure.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Environmental_Variable_Exposure_Not_Prevented Class
 *
 * Performs treatment check for Environmental Variable Exposure Not Prevented.
 *
 * @since 1.6033.2033
 */
class Treatment_Environmental_Variable_Exposure_Not_Prevented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'environmental-variable-exposure-not-prevented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Environmental Variable Exposure Not Prevented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks env variable exposure';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('wp_headers',
						'hide_environment_variables' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Environment variables exposed. Never echo env variables in HTML/logs. Use .gitignore for .env files and set proper permissions on configuration files.',
						'severity'   =>   'high',
						'threat_level'   =>   75,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/environmental-variable-exposure-not-prevented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
