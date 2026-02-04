<?php
/**
 * SQL Truncation Attack Not Prevented Diagnostic
 *
 * Checks SQL truncation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_SQL_Truncation_Attack_Not_Prevented Class
 *
 * Performs diagnostic check for Sql Truncation Attack Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_SQL_Truncation_Attack_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sql-truncation-attack-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SQL Truncation Attack Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks SQL truncation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'prevent_sql_truncation' ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SQL truncation attack not prevented. Use parameterized queries and implement strict field length validation.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/sql-truncation-attack-not-prevented',
				'context'      => array(
					'why'            => __( 'SQL truncation attacks exploit the gap between application validation and database storage rules. If your app accepts user input and the database silently truncates it (for example, a long username or token), an attacker can craft values that collide after truncation, bypass uniqueness checks, or manipulate comparisons. This can lead to account takeover, password reset abuse, or authorization bypass when the truncated value matches a privileged account. The risk is amplified when input is concatenated into queries or when validation occurs before truncation, because the application believes the value is safe but the database stores something different. OWASP Top 10 2021 ranks Injection #3 and Broken Access Control #1, and truncation issues can enable both by altering query semantics or identity checks. Verizon’s 2024 DBIR reports that roughly three‑quarters of breaches involve the human element and that web application attacks remain a leading pattern against public‑facing systems; attackers routinely chain subtle input handling flaws with credential abuse to gain persistence. From a business perspective, truncation can corrupt orders, user records, and audit trails, creating compliance exposure and costly remediation. It is also hard to detect because logs may show the full input while the database stores the truncated version, leading to confusing investigations and delayed response. Enforcing strict SQL modes, validating length at the application layer, and using prepared statements close this class of bug and provide clear, measurable controls for auditors and cyber insurers.', 'wpshadow' ),
					'recommendation' => __( '1. Use prepared statements for all queries (wpdb->prepare or query builders).
2. Enforce strict SQL modes (STRICT_TRANS_TABLES, STRICT_ALL_TABLES) to reject truncation.
3. Validate maximum length of every input before database write.
4. Normalize and trim input consistently before validation and storage.
5. Use unique constraints with explicit length limits that match validation.
6. Log and alert on database warnings (Data truncated, Truncated incorrect) in error logs.
7. Reject multibyte overflows by validating byte length, not just character count.
8. Add unit tests for boundary lengths (max‑1, max, max+1).
9. Review authentication flows for truncation collisions (usernames, emails, tokens).
10. Audit legacy tables for silent truncation by checking column types and lengths.', 'wpshadow' ),
				),
			);

			return Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'injection', self::$slug );
		}

		return null;
	}
}
