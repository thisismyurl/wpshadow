<?php
/**
 * Default Avatar Selection Diagnostic
 *
 * Verifies the default avatar choice provides good user experience.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6032.1755
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Avatar Selection Diagnostic Class
 *
 * Checks default avatar configuration for user experience.
 *
 * @since 1.6032.1755
 */
class Diagnostic_Default_Avatar_Selection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-avatar-selection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Default Avatar Selection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies default avatar choice';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6032.1755
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if avatars are enabled first.
		$show_avatars = get_option( 'show_avatars', 1 );
		if ( ! $show_avatars ) {
			return null; // Avatars disabled, default doesn't matter.
		}

		// Get default avatar.
		$avatar_default = get_option( 'avatar_default', 'mystery' );

		// Check for potentially problematic defaults.
		if ( $avatar_default === 'blank' ) {
			$issues[] = __( 'Blank default avatar may make comments look incomplete', 'wpshadow' );
		}

		// Available default avatars.
		$available_defaults = array(
			'mystery'    => __( 'Mystery Person', 'wpshadow' ),
			'blank'      => __( 'Blank', 'wpshadow' ),
			'gravatar_default' => __( 'Gravatar Logo', 'wpshadow' ),
			'identicon'  => __( 'Identicon (Generated)', 'wpshadow' ),
			'wavatar'    => __( 'Wavatar (Generated)', 'wpshadow' ),
			'monsterid'  => __( 'MonsterID (Generated)', 'wpshadow' ),
			'retro'      => __( 'Retro (Generated)', 'wpshadow' ),
			'robohash'   => __( 'RoboHash (Generated)', 'wpshadow' ),
		);

		if ( ! isset( $available_defaults[ $avatar_default ] ) && ! filter_var( $avatar_default, FILTER_VALIDATE_URL ) ) {
			$issues[] = sprintf(
				/* translators: %s: avatar type */
				__( 'Unknown default avatar type: %s', 'wpshadow' ),
				$avatar_default
			);
		}

		// Check if using custom avatar URL.
		if ( filter_var( $avatar_default, FILTER_VALIDATE_URL ) ) {
			// Verify URL is accessible.
			$response = wp_safe_remote_head( $avatar_default );
			if ( is_wp_error( $response ) ) {
				$issues[] = __( 'Custom default avatar URL is not accessible', 'wpshadow' );
			}
		}

		// Recommend generated avatars for uniqueness.
		if ( in_array( $avatar_default, array( 'mystery', 'blank', 'gravatar_default' ), true ) ) {
			$issues[] = __( 'Consider using generated avatars (identicon, wavatar) for unique visual identity', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/default-avatar-selection',
			);
		}

		return null;
	}
}
