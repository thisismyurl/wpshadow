<?php
/**
 * Poedit Plural Forms Diagnostic
 *
 * Poedit Plural Forms misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1173.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Poedit Plural Forms Diagnostic Class
 *
 * @since 1.1173.0000
 */
class Diagnostic_PoeditPluralForms extends Diagnostic_Base {

	protected static $slug = 'poedit-plural-forms';
	protected static $title = 'Poedit Plural Forms';
	protected static $description = 'Poedit Plural Forms misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Plural forms configured
		$plural = get_option( 'poedit_plural_forms_configured', 0 );
		if ( ! $plural ) {
			$issues[] = 'Plural forms not configured';
		}
		
		// Check 2: Language detection
		$lang = get_option( 'poedit_language_detection_enabled', 0 );
		if ( ! $lang ) {
			$issues[] = 'Language detection not enabled';
		}
		
		// Check 3: Translation strings parsed
		$strings = get_option( 'poedit_translation_strings_parsed', 0 );
		if ( ! $strings ) {
			$issues[] = 'Translation strings not parsed';
		}
		
		// Check 4: Context usage
		$context = get_option( 'poedit_context_usage_enabled', 0 );
		if ( ! $context ) {
			$issues[] = 'Context usage not enabled';
		}
		
		// Check 5: Comments for translators
		$comments = get_option( 'poedit_translator_comments_enabled', 0 );
		if ( ! $comments ) {
			$issues[] = 'Translator comments not enabled';
		}
		
		// Check 6: Validation rules
		$validation = get_option( 'poedit_plural_validation_rules_enabled', 0 );
		if ( ! $validation ) {
			$issues[] = 'Plural validation rules not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 25;
			$threat_multiplier = 6;
			$max_threat = 55;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d plural form issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/poedit-plural-forms',
			);
		}
		
		return null;
	}
}
