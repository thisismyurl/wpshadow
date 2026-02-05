<?php
/**
 * Debug Information Exposed Treatment
 *
 * Checks debug exposure.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Debug_Information_Exposed Class
 *
 * Performs treatment check for Debug Information Exposed.
 *
 * @since 1.6033.2033
 */
class Treatment_Debug_Information_Exposed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'debug-information-exposed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Debug Information Exposed';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks debug exposure';

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
		if (   defined('WP_DEBUG' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Debug information exposed in production. Set WP_DEBUG to false and log errors to file,
						'severity'   =>   'high',
						'threat_level'   =>   70,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/debug-information-exposed'
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
