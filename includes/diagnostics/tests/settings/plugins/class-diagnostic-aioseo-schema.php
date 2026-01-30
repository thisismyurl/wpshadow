<?php
/**
 * AIOSEO Schema Markup Diagnostic
 *
 * Validates structured data configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1805
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AIOSEO Schema Class
 *
 * Checks schema markup implementation.
 *
 * @since 1.5029.1805
 */
class Diagnostic_AIOSEO_Schema extends Diagnostic_Base {

	protected static $slug        = 'aioseo-schema';
	protected static $title       = 'AIOSEO Schema Markup';
	protected static $description = 'Validates structured data';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_aioseo_schema';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();
		$options = get_option( 'aioseo_options', array() );

		// Check if schema is enabled.
		$schema_enabled = isset( $options['searchAppearance']['advanced']['useSchema'] ) 
			? $options['searchAppearance']['advanced']['useSchema'] 
			: true;

		if ( ! $schema_enabled ) {
			$issues[] = 'Schema markup is disabled';
		}

		// Check organization info.
		$org_name = isset( $options['searchAppearance']['global']['schema']['organizationName'] ) 
			? $options['searchAppearance']['global']['schema']['organizationName'] 
			: '';

		if ( empty( $org_name ) ) {
			$issues[] = 'Organization name not configured for schema';
		}

		$org_logo = isset( $options['searchAppearance']['global']['schema']['organizationLogo'] ) 
			? $options['searchAppearance']['global']['schema']['organizationLogo'] 
			: '';

		if ( empty( $org_logo ) ) {
			$issues[] = 'Organization logo not configured for schema';
		}

		// Check social profiles.
		$social_profiles = isset( $options['searchAppearance']['global']['schema']['social'] ) 
			? $options['searchAppearance']['global']['schema']['social'] 
			: array();

		$has_social = ! empty( $social_profiles['facebook'] ) || 
			! empty( $social_profiles['twitter'] ) || 
			! empty( $social_profiles['instagram'] ) ||
			! empty( $social_profiles['linkedin'] );

		if ( ! $has_social ) {
			$issues[] = 'No social profiles configured for schema';
		}

		// Check if knowledge graph is configured.
		$knowledge_graph_type = isset( $options['searchAppearance']['global']['schema']['siteRepresents'] ) 
			? $options['searchAppearance']['global']['schema']['siteRepresents'] 
			: '';

		if ( empty( $knowledge_graph_type ) ) {
			$issues[] = 'Knowledge Graph type not selected';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d schema markup issues. Improve structured data for better search visibility.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-aioseo-schema',
				'data'         => array(
					'schema_issues' => $issues,
					'total_issues' => count( $issues ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
