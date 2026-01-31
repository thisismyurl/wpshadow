<?php
/**
 * Wp Mail Smtp Provider Api Limits Diagnostic
 *
 * Wp Mail Smtp Provider Api Limits issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1459.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Mail Smtp Provider Api Limits Diagnostic Class
 *
 * @since 1.1459.0000
 */
class Diagnostic_WpMailSmtpProviderApiLimits extends Diagnostic_Base {

	protected static $slug = 'wp-mail-smtp-provider-api-limits';
	protected static $title = 'Wp Mail Smtp Provider Api Limits';
	protected static $description = 'Wp Mail Smtp Provider Api Limits issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPMS_PLUGIN_VER' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wp-mail-smtp-provider-api-limits',
			);
		}
		
		return null;
	}
}
