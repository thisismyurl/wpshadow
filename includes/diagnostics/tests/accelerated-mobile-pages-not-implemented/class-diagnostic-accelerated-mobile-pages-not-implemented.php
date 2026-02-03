<?php
/**
 * Accelerated Mobile Pages Not Implemented Diagnostic
 *
 * Checks AMP implementation.
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
 * Diagnostic_Accelerated_Mobile_Pages_Not_Implemented Class
 *
 * Performs diagnostic check for Accelerated Mobile Pages Not Implemented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Accelerated_Mobile_Pages_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accelerated-mobile-pages-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accelerated Mobile Pages Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks AMP implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !is_plugin_active('amp/amp.php' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('AMP not implemented. Consider implementing Google AMP pages for mobile traffic to achieve lightning-fast load times and improved SEO.',
						'severity'   =>   'low',
						'threat_level'   =>   15,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/accelerated-mobile-pages-not-implemented'
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
