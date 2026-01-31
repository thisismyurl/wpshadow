<?php
/**
 * Restrict Content Pro Membership Levels Diagnostic
 *
 * RCP membership levels poorly structured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.327.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Restrict Content Pro Membership Levels Diagnostic Class
 *
 * @since 1.327.0000
 */
class Diagnostic_RestrictContentProMembershipLevels extends Diagnostic_Base {

	protected static $slug = 'restrict-content-pro-membership-levels';
	protected static $title = 'Restrict Content Pro Membership Levels';
	protected static $description = 'RCP membership levels poorly structured';
	protected static $family = 'functionality';

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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/restrict-content-pro-membership-levels',
			);
		}
		
		return null;
	}
}
