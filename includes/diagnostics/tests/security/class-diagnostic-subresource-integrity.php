<?php
/**
 * Subresource Integrity Diagnostic
 *
 * Issue #4952: No Subresource Integrity (SRI) for CDN Assets
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if external scripts/styles use SRI hashes.
 * CDN compromise can inject malicious code without SRI.
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
 * Diagnostic_Subresource_Integrity Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Subresource_Integrity extends Diagnostic_Base {

	protected static $slug = 'subresource-integrity';
	protected static $title = 'No Subresource Integrity (SRI) for CDN Assets';
	protected static $description = 'Checks if external resources use integrity hashes';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add integrity attribute to CDN scripts and styles', 'wpshadow' );
		$issues[] = __( 'Generate SHA-384 or SHA-512 hashes for files', 'wpshadow' );
		$issues[] = __( 'Use crossorigin="anonymous" with integrity', 'wpshadow' );
		$issues[] = __( 'Monitor CDN provider security advisories', 'wpshadow' );
		$issues[] = __( 'Fall back to local copy if integrity check fails', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Subresource Integrity ensures CDN files haven\'t been tampered with. If a CDN is compromised, SRI prevents malicious code from running.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/subresource-integrity?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'example'                 => '<script src="https://cdn.example.com/script.js" integrity="sha384-oqVu..." crossorigin="anonymous">',
					'real_breach'             => 'British Airways hack (2018) via compromised CDN',
					'browser_support'         => '96%+ browser support',
				),
			);
		}

		return null;
	}
}
