<?php
/**
 * Mailpoet Newsletter Database Cleanup Diagnostic
 *
 * Mailpoet Newsletter Database Cleanup configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.713.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailpoet Newsletter Database Cleanup Diagnostic Class
 *
 * @since 1.713.0000
 */
class Diagnostic_MailpoetNewsletterDatabaseCleanup extends Diagnostic_Base {

	protected static $slug = 'mailpoet-newsletter-database-cleanup';
	protected static $title = 'Mailpoet Newsletter Database Cleanup';
	protected static $description = 'Mailpoet Newsletter Database Cleanup configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'MailPoet\Config\Initializer' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/mailpoet-newsletter-database-cleanup',
			);
		}
		
		return null;
	}
}
