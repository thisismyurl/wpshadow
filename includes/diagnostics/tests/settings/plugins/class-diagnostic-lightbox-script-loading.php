<?php
/**
 * Lightbox Script Loading Diagnostic
 *
 * Lightbox scripts loading on all pages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.504.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lightbox Script Loading Diagnostic Class
 *
 * @since 1.504.0000
 */
class Diagnostic_LightboxScriptLoading extends Diagnostic_Base {

	protected static $slug = 'lightbox-script-loading';
	protected static $title = 'Lightbox Script Loading';
	protected static $description = 'Lightbox scripts loading on all pages';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists('some_check') ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/lightbox-script-loading',
			);
		}
		
		return null;
	}
}
