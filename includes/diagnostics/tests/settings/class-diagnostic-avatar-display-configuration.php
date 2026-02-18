<?php
/**
 * Avatar Display Configuration Diagnostic
 *
 * Verifies avatar settings are configured for optimal user experience and privacy.
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
 * Avatar Display Configuration Diagnostic Class
 *
 * Checks avatar display and Gravatar settings.
 *
 * @since 1.6032.1755
 */
class Diagnostic_Avatar_Display_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'avatar-display-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Avatar Display Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies avatar settings';

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

		// Check if avatars are enabled.
		$show_avatars = get_option( 'show_avatars', 1 );

		if ( ! $show_avatars ) {
			$issues[] = __( 'Avatars are disabled - may reduce user engagement', 'wpshadow' );
		}

		// Check avatar rating.
		$avatar_rating = get_option( 'avatar_rating', 'G' );
		if ( $avatar_rating === 'X' ) {
			$issues[] = __( 'Avatar rating allows adult content (X-rated)', 'wpshadow' );
		}

		// Check default avatar.
		$avatar_default = get_option( 'avatar_default', 'mystery' );
		if ( $avatar_default === 'blank' ) {
			$issues[] = __( 'Default avatar is blank - may look unprofessional', 'wpshadow' );
		}

		// Check if Gravatar service is accessible (privacy consideration).
		if ( $show_avatars ) {
			// Gravatar loads from external service - privacy concern.
			$issues[] = __( 'Gravatars load from external service (privacy consideration)', 'wpshadow' );
		}

		// Check if local avatar plugins are active.
		$local_avatar_plugins = array(
			'simple-local-avatars/simple-local-avatars.php',
			'wp-user-avatar/wp-user-avatar.php',
			'metronet-profile-picture/metronet-profile-picture.php',
		);

		$has_local_avatar = false;
		foreach ( $local_avatar_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_local_avatar = true;
				break;
			}
		}

		if ( $show_avatars && ! $has_local_avatar ) {
			$issues[] = __( 'Using Gravatar without local avatar option for GDPR compliance', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/avatar-display-configuration',
			);
		}

		return null;
	}
}
