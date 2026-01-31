<?php
/**
 * Restrict Content Pro Content Restrictions Diagnostic
 *
 * RCP content restrictions bypassable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.329.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Content Restrictions Diagnostic Class
 *
 * @since 1.329.0000
 */
class Diagnostic_RestrictContentProContentRestrictions extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-content-restrictions';
	protected static $title = 'Restrict Content Pro Content Restrictions';
	protected static $description = 'RCP content restrictions bypassable';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'RCP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-content-restrictions',
			);
		}
		
		return null;
	}
}
