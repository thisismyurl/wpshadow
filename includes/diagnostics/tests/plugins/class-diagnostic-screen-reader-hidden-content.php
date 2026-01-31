<?php
/**
 * Screen Reader Hidden Content Diagnostic
 *
 * Screen Reader Hidden Content not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1141.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Screen Reader Hidden Content Diagnostic Class
 *
 * @since 1.1141.0000
 */
class Diagnostic_ScreenReaderHiddenContent extends Diagnostic_Base {

	protected static $slug = 'screen-reader-hidden-content';
	protected static $title = 'Screen Reader Hidden Content';
	protected static $description = 'Screen Reader Hidden Content not compliant';
	protected static $family = 'functionality';

	public static function check() {
		
		$issues = array();
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
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
				'kb_link'     => 'https://wpshadow.com/kb/screen-reader-hidden-content',
			);
		}
		
		return null;
	}
}
