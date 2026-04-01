<?php
/**
 * Guest Author Management Diagnostic
 *
 * Checks guest author policies and configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guest Author Management Diagnostic
 *
 * Validates guest author policies and permissions.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Guest_Author_Management extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'guest-author-management';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Guest Author Management';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks guest author policies and configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );
		$issues = array();
		$details = array();

		$guest_plugins = array(
			'co-authors-plus/co-authors-plus.php' => 'Co-Authors Plus',
			'guest-author/guest-author.php' => 'Guest Author',
			'molongui-authorship/molongui-authorship.php' => 'Molongui Authorship',
		);

		$guest_enabled = false;
		foreach ( $guest_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$guest_enabled = true;
				$details['guest_plugin'] = $name;
				break;
			}
		}

		$policy_url = get_option( 'wpshadow_guest_author_policy_url', '' );
		if ( $guest_enabled && empty( $policy_url ) ) {
			$issues[] = __( 'Guest author policy URL not configured', 'wpshadow' );
		}

		if ( $guest_enabled && empty( $issues ) ) {
			return null;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Guest author management policy is missing', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/guest-author-management?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
