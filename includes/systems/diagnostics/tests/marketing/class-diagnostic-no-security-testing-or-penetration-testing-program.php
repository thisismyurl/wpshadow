<?php
/**
 * No Security Testing or Penetration Testing Program Diagnostic
 *
 * Checks if security testing program exists.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Testing Diagnostic
 *
 * You can't assume your system is secure.
 * Test for vulnerabilities before attackers find them.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Security_Testing_Or_Penetration_Testing_Program extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-security-testing-penetration-testing-program';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Security/Penetration Testing Program';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if security testing program exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'business-performance';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_security_testing() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No security testing program detected. You can\'t assume your system is secure. Test for vulnerabilities before attackers find them. Implement: 1) Automated scanning (weekly code scans for known vulnerabilities), 2) Penetration testing (annual by security firm, simulate real attack), 3) Internal testing (team does basic security review), 4) Vulnerability disclosure (let security researchers report issues safely). Typical costs: Automated scanning free-$500/month, pen test $10k-$25k annually. Find: SQL injection, XSS, CSRF, insecure auth, data exposure. Fix before releasing. Data breach costs $4M+ (fines, remediation, lost customers).', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/security-testing-penetration-program',
				'details'     => array(
					'issue'          => __( 'No security testing program detected', 'wpshadow' ),
					'recommendation' => __( 'Implement comprehensive security testing program', 'wpshadow' ),
					'business_impact' => __( 'Risk of data breach ($4M+ cost: fines, remediation, lost customers)', 'wpshadow' ),
					'testing_types'  => self::get_testing_types(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if security testing exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if testing detected, false otherwise.
	 */
	private static function has_security_testing() {
		$security_posts = self::count_posts_by_keywords(
			array(
				'security',
				'penetration test',
				'vulnerability',
				'SSL',
				'encryption',
			)
		);

		return $security_posts > 0;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since  1.6035.0000
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get testing types.
	 *
	 * @since  1.6035.0000
	 * @return array Security testing types.
	 */
	private static function get_testing_types() {
		return array(
			'static'  => array(
				'type'        => __( 'Static Analysis (Code Review)', 'wpshadow' ),
				'frequency'   => __( 'On every code commit', 'wpshadow' ),
				'tools'       => __( 'SAST tools: SonarQube, Checkmarx, Veracode', 'wpshadow' ),
				'finds'       => __( 'Common patterns: SQL injection, XSS, weak crypto', 'wpshadow' ),
			),
			'dynamic' => array(
				'type'        => __( 'Dynamic Testing (Running App)', 'wpshadow' ),
				'frequency'   => __( 'Monthly or quarterly', 'wpshadow' ),
				'tools'       => __( 'DAST tools: Burp Suite, Acunetix, ZAP', 'wpshadow' ),
				'finds'       => __( 'Runtime issues: insecure auth, CSRF, broken encryption', 'wpshadow' ),
			),
			'penetration' => array(
				'type'        => __( 'Penetration Testing (Simulated Attack)', 'wpshadow' ),
				'frequency'   => __( 'Annually', 'wpshadow' ),
				'who'         => __( 'Security firm (external, unbiased perspective)', 'wpshadow' ),
				'cost'        => __( '$10k-$25k typical', 'wpshadow' ),
				'finds'       => __( 'Real-world attack scenarios, chain of vulnerabilities', 'wpshadow' ),
			),
			'dependency' => array(
				'type'        => __( 'Dependency Scanning (Third-party Code)', 'wpshadow' ),
				'frequency'   => __( 'Continuous', 'wpshadow' ),
				'tools'       => __( 'Snyk, Dependabot, npm audit, pip audit', 'wpshadow' ),
				'finds'       => __( 'Vulnerable third-party libraries', 'wpshadow' ),
			),
		);
	}
}
