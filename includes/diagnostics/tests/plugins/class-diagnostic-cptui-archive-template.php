<?php
/**
 * CPT UI Archive Template Diagnostic
 *
 * CPT UI archive templates missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.447.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT UI Archive Template Diagnostic Class
 *
 * @since 1.447.0000
 */
class Diagnostic_CptuiArchiveTemplate extends Diagnostic_Base {

	protected static $slug = 'cptui-archive-template';
	protected static $title = 'CPT UI Archive Template';
	protected static $description = 'CPT UI archive templates missing';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'CPT_VERSION' ) ) {
			return null;
		}
		
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/cptui-archive-template',
			);
		}
		
		return null;
	}
}
