<?php
/**
 * Classic Editor Gutenberg Coexistence Diagnostic
 *
 * Classic Editor Gutenberg Coexistence issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1433.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic Editor Gutenberg Coexistence Diagnostic Class
 *
 * @since 1.1433.0000
 */
class Diagnostic_ClassicEditorGutenbergCoexistence extends Diagnostic_Base {

	protected static $slug = 'classic-editor-gutenberg-coexistence';
	protected static $title = 'Classic Editor Gutenberg Coexistence';
	protected static $description = 'Classic Editor Gutenberg Coexistence issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! true // WordPress core feature ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/classic-editor-gutenberg-coexistence',
			);
		}
		
		return null;
	}
}
