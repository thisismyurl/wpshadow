<?php
/**
 * Treatment Toggle Policy
 *
 * Controls default enabled/disabled state for treatments based on curated
 * metadata safety and maturity rules.
 *
 * @package ThisIsMyURL\Shadow
 * @since 0.6094
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Core;

use ThisIsMyURL\Shadow\Treatments\Treatment_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Applies default treatment toggle policy and exposes helpers for UI.
 */
final class Treatment_Toggle_Policy {

	/**
	 * Disabled treatment classes option key.
	 */
	private const DISABLED_OPTION = 'thisismyurl_shadow_disabled_treatment_classes';

	/**
	 * Processed class map option key.
	 */
	private const PROCESSED_OPTION = 'thisismyurl_shadow_treatment_toggle_policy_processed_classes';

	/**
	 * Current policy version.
	 */
	private const POLICY_VERSION = 'safe-defaults-v1';

	/**
	 * Policy version option key.
	 */
	private const POLICY_VERSION_OPTION = 'thisismyurl_shadow_treatment_toggle_policy_version';

	/**
	 * Apply defaults for treatment toggles.
	 *
	 * @param bool $force_full_resync Whether to force recalculation for all treatments.
	 * @return void
	 */
	public static function sync_defaults( bool $force_full_resync = false ): void {
		if ( ! class_exists( Treatment_Registry::class ) || ! class_exists( Treatment_Metadata::class ) ) {
			return;
		}

		$disabled = get_option( self::DISABLED_OPTION, array() );
		if ( ! is_array( $disabled ) ) {
			$disabled = array();
		}

		$disabled = array_values( array_filter( array_map( 'strval', $disabled ) ) );

		$processed = get_option( self::PROCESSED_OPTION, array() );
		if ( ! is_array( $processed ) ) {
			$processed = array();
		}

		$changed = false;
		$all     = array();
		try {
			$all = Treatment_Registry::get_all();
		} catch ( \Throwable $exception ) {
			return;
		}

		foreach ( $all as $class_name ) {
			if ( ! is_string( $class_name ) || '' === $class_name ) {
				continue;
			}

			if ( ! $force_full_resync && isset( $processed[ $class_name ] ) ) {
				continue;
			}

			$default_enabled = self::is_default_enabled_for_class( $class_name );
			$currently_disabled = in_array( $class_name, $disabled, true );

			if ( $default_enabled && $currently_disabled ) {
				$disabled = array_values( array_diff( $disabled, array( $class_name ) ) );
				$changed  = true;
			} elseif ( ! $default_enabled && ! $currently_disabled ) {
				$disabled[] = $class_name;
				$changed    = true;
			}

			$processed[ $class_name ] = 1;
		}

		if ( $changed ) {
			update_option( self::DISABLED_OPTION, array_values( array_unique( $disabled ) ), false );
		}

		update_option( self::PROCESSED_OPTION, $processed, false );
		update_option( self::POLICY_VERSION_OPTION, self::POLICY_VERSION, false );
	}

	/**
	 * Ensure policy is applied after upgrades and for newly discovered treatments.
	 *
	 * @return void
	 */
	public static function maybe_sync_defaults(): void {
		$stored_version = (string) get_option( self::POLICY_VERSION_OPTION, '' );
		if ( self::POLICY_VERSION !== $stored_version ) {
			self::sync_defaults( true );
			return;
		}

		self::sync_defaults( false );
	}

	/**
	 * Determine whether a treatment should be enabled by default.
	 *
	 * Rule: only shipped + safe treatments are default-on.
	 *
	 * @param string $class_name Fully-qualified treatment class name.
	 * @return bool
	 */
	public static function is_default_enabled_for_class( string $class_name ): bool {
		if ( '' === $class_name || ! class_exists( $class_name ) || ! method_exists( $class_name, 'get_finding_id' ) ) {
			return false;
		}

		$finding_id = '';
		try {
			$finding_id = sanitize_key( (string) $class_name::get_finding_id() );
		} catch ( \Throwable $exception ) {
			return false;
		}

		if ( '' === $finding_id ) {
			return false;
		}

		$meta = Treatment_Metadata::get( $finding_id );
		if ( ! is_array( $meta ) ) {
			return false;
		}

		return ( 'shipped' === (string) ( $meta['maturity'] ?? '' ) )
			&& ( 'safe' === (string) ( $meta['risk_level'] ?? '' ) );
	}
}
