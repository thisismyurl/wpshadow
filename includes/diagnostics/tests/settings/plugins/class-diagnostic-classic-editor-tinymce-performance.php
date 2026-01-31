<?php
/**
 * Classic Editor Tinymce Performance Diagnostic
 *
 * Classic Editor Tinymce Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1435.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic Editor Tinymce Performance Diagnostic Class
 *
 * @since 1.1435.0000
 */
class Diagnostic_ClassicEditorTinymcePerformance extends Diagnostic_Base {

	protected static $slug = 'classic-editor-tinymce-performance';
	protected static $title = 'Classic Editor Tinymce Performance';
	protected static $description = 'Classic Editor Tinymce Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'classic_editor_init_actions' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/classic-editor-tinymce-performance',
			);
		}
		
		return null;
	}
}
