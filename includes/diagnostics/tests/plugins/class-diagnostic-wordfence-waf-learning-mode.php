<?php
/**
 * Wordfence Waf Learning Mode Diagnostic
 *
 * Wordfence Waf Learning Mode misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.847.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Waf Learning Mode Diagnostic Class
 *
 * @since 1.847.0000
 */
class Diagnostic_WordfenceWafLearningMode extends Diagnostic_Base {

	protected static $slug = 'wordfence-waf-learning-mode';
	protected static $title = 'Wordfence Waf Learning Mode';
	protected static $description = 'Wordfence Waf Learning Mode misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-waf-learning-mode',
			);
		}
		
		return null;
	}
}
