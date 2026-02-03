<?php
/**
 * Zero Trust Architecture Not Implemented Diagnostic
 *
 * Checks zero trust.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Zero_Trust_Architecture_Not_Implemented Class
 *
 * Performs diagnostic check for Zero Trust Architecture Not Implemented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Zero_Trust_Architecture_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'zero-trust-architecture-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Zero Trust Architecture Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks zero trust';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'implement_zero_trust' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Zero trust architecture not implemented. Verify every request,
						'severity'   =>   'high',
						'threat_level'   =>   75,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/zero-trust-architecture-not-implemented'
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
