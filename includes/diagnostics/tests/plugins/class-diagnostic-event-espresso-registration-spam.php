<?php
/**
 * Event Espresso Registration Spam Diagnostic
 *
 * Event Espresso spam registrations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.588.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event Espresso Registration Spam Diagnostic Class
 *
 * @since 1.588.0000
 */
class Diagnostic_EventEspressoRegistrationSpam extends Diagnostic_Base {

	protected static $slug = 'event-espresso-registration-spam';
	protected static $title = 'Event Espresso Registration Spam';
	protected static $description = 'Event Espresso spam registrations';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'EVENT_ESPRESSO_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/event-espresso-registration-spam',
			);
		}
		
		return null;
	}
}
