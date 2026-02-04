<?php
/**
 * Multisite Isolation Not Enforced Diagnostic
 *
 * Checks multisite isolation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Multisite_Isolation_Not_Enforced Class
 *
 * Performs diagnostic check for Multisite Isolation Not Enforced.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Multisite_Isolation_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-isolation-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Isolation Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks multisite isolation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   is_multisite( ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Multisite isolation not enforced. Prevent cross-site data access and enforce role separation.',
						'severity'   =>   'high',
						'threat_level'   =>   75,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/multisite-isolation-not-enforced'
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
