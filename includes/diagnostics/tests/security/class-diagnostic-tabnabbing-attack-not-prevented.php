<?php
/**
 * Tabnabbing Attack Not Prevented Diagnostic
 *
 * Checks tabnabbing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tabnabbing_Attack_Not_Prevented Class
 *
 * Performs diagnostic check for Tabnabbing Attack Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Tabnabbing_Attack_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tabnabbing-attack-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tabnabbing Attack Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks tabnabbing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'prevent_tabnabbing' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Tabnabbing attack not prevented. Set rel="noopener noreferrer" on external links to prevent window.opener access.',
						'severity'   =>   'medium',
						'threat_level'   =>   35,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/tabnabbing-attack-not-prevented'
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
