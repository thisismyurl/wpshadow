<?php
/**
 * Wpml Automatic Translation Api Diagnostic
 *
 * Wpml Automatic Translation Api misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1143.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wpml Automatic Translation Api Diagnostic Class
 *
 * @since 1.1143.0000
 */
class Diagnostic_WpmlAutomaticTranslationApi extends Diagnostic_Base {

	protected static $slug = 'wpml-automatic-translation-api';
	protected static $title = 'Wpml Automatic Translation Api';
	protected static $description = 'Wpml Automatic Translation Api misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-automatic-translation-api',
			);
		}
		
		return null;
	}
}
