<?php
/**
 * CPT UI REST API Exposure Diagnostic
 *
 * CPT UI exposing posts via REST.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.448.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT UI REST API Exposure Diagnostic Class
 *
 * @since 1.448.0000
 */
class Diagnostic_CptuiRestApiExposure extends Diagnostic_Base {

	protected static $slug = 'cptui-rest-api-exposure';
	protected static $title = 'CPT UI REST API Exposure';
	protected static $description = 'CPT UI exposing posts via REST';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'CPT_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Get registered post types
		$post_types = get_post_types( array( '_builtin' => false ), 'objects' );
		
		if ( empty( $post_types ) ) {
			return null;
		}
		
		foreach ( $post_types as $post_type ) {
			// Check 2: REST API enabled
			if ( ! $post_type->show_in_rest ) {
				continue; // Not exposed to REST
			}
			
			// Check 3: Public visibility
			if ( $post_type->public ) {
				$issues[] = sprintf( __( '%s publicly accessible via REST', 'wpshadow' ), $post_type->name );
			}
			
			// Check 4: Authentication
			if ( ! isset( $post_type->rest_controller_class ) || 'WP_REST_Posts_Controller' === $post_type->rest_controller_class ) {
				$issues[] = sprintf( __( '%s using default REST controller (limited security)', 'wpshadow' ), $post_type->name );
			}
			
			// Check 5: Capability checks
			if ( ! isset( $post_type->cap->read_private_posts ) ) {
				$issues[] = sprintf( __( '%s no private read capability', 'wpshadow' ), $post_type->name );
			}
		}
		
		// Check 6: REST authentication
		$rest_auth = get_option( 'cptui_rest_authentication', 'no' );
		if ( 'no' === $rest_auth ) {
			$issues[] = __( 'No REST authentication (unauthenticated access)', 'wpshadow' );
		}
		
		// Check 7: Permission callbacks
		$permission_callbacks = get_option( 'cptui_rest_permission_callbacks', array() );
		if ( empty( $permission_callbacks ) ) {
			$issues[] = __( 'No permission callbacks (unrestricted access)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 65;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 80;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 73;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of REST API exposure issues */
				__( 'CPT UI has %d REST API security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cptui-rest-api-exposure',
		);
	}
}
