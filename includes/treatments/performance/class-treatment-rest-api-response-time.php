<?php
/**
 * REST API Response Time Treatment
 *
 * Measures WordPress REST API performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Response Time Treatment Class
 *
 * Tests REST API endpoint performance. Slow REST API impacts
 * Gutenberg editor, mobile apps, and API integrations.
 *
 * @since 1.6093.1200
 */
class Treatment_REST_API_Response_Time extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-response-time';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Response Time';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures REST API endpoint performance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests REST API by timing internal request.
	 * Threshold: <500ms good, >1000ms slow
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_REST_API_Response_Time' );
	}
}
