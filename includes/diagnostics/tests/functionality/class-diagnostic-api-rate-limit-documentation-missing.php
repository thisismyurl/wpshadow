<?php
/**
 * API Rate Limit Documentation Missing Diagnostic
 *
 * Checks if API rate limits are documented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Rate Limit Documentation Missing Diagnostic Class
 *
 * Detects missing rate limit documentation.
 *
 * @since 1.2601.2340
 */
class Diagnostic_API_Rate_Limit_Documentation_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'api-rate-limit-documentation-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'API Rate Limit Documentation Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if API rate limits are documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if custom endpoints exist
		if ( ! rest_get_url_prefix() ) {
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => __( 'API rate limits are not documented. Document rate limits for API consumers to prevent abuse.', 'wpshadow' ),
			'severity'      => 'low',
			'threat_level'  => 15,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/api-rate-limit-documentation-missing',
		);
	}
}
