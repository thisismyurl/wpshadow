<?php
/**
 * Service Worker Not Implemented Diagnostic
 *
 * Checks service worker implementation.
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
 * Diagnostic_Service_Worker_Not_Implemented Class
 *
 * Performs diagnostic check for Service Worker Not Implemented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Service_Worker_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'service-worker-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Service Worker Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks service worker implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('wp_head',
						'add_service_worker_script' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Service worker not implemented. Implement service workers to enable offline mode,
						'severity'   =>   'low',
						'threat_level'   =>   10,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/service-worker-not-implemented'
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
