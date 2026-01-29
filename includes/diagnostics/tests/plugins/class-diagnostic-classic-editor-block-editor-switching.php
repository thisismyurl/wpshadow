<?php
/**
 * Classic Editor Block Editor Switching Diagnostic
 *
 * Classic Editor Block Editor Switching issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1434.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic Editor Block Editor Switching Diagnostic Class
 *
 * @since 1.1434.0000
 */
class Diagnostic_ClassicEditorBlockEditorSwitching extends Diagnostic_Base {

	protected static $slug = 'classic-editor-block-editor-switching';
	protected static $title = 'Classic Editor Block Editor Switching';
	protected static $description = 'Classic Editor Block Editor Switching issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'classic_editor_init_actions' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/classic-editor-block-editor-switching',
			);
		}
		
		return null;
	}
}
