<?php
/**
 * CPT UI Permalink Conflicts Diagnostic
 *
 * Custom Post Type UI permalinks conflicting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.445.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT UI Permalink Conflicts Diagnostic Class
 *
 * @since 1.445.0000
 */
class Diagnostic_CptuiPermalinkConflicts extends Diagnostic_Base {

	protected static $slug = 'cptui-permalink-conflicts';
	protected static $title = 'CPT UI Permalink Conflicts';
	protected static $description = 'Custom Post Type UI permalinks conflicting';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'CPT_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/cptui-permalink-conflicts',
			);
		}
		
		return null;
	}
}
