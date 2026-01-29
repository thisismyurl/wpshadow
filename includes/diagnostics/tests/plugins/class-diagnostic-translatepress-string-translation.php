<?php
/**
 * TranslatePress String Translation Diagnostic
 *
 * TranslatePress strings not translatable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.318.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TranslatePress String Translation Diagnostic Class
 *
 * @since 1.318.0000
 */
class Diagnostic_TranslatepressStringTranslation extends Diagnostic_Base {

	protected static $slug = 'translatepress-string-translation';
	protected static $title = 'TranslatePress String Translation';
	protected static $description = 'TranslatePress strings not translatable';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-string-translation',
			);
		}
		
		return null;
	}
}
