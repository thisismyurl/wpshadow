<?php
/**
 * GDPR Data Export Not Implemented Diagnostic
 *
 * Checks GDPR export.
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
 * Diagnostic_GDPR_Data_Export_Not_Implemented Class
 *
 * Performs diagnostic check for Gdpr Data Export Not Implemented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_GDPR_Data_Export_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gdpr-data-export-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'GDPR Data Export Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks GDPR export';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'implement_data_export' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('GDPR data export not implemented. Provide users ability to export personal data in standard formats.',
						'severity'   =>   'high',
						'threat_level'   =>   85,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/gdpr-data-export-not-implemented'
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
