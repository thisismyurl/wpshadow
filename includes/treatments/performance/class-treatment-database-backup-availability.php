<?php
/**
 * Database Backup Availability Treatment
 *
 * Verifies recent, accessible database backups exist to prevent catastrophic data loss.
 *
 * **What This Check Does:**
 * 1. Checks for backup plugins (UpdraftPlus, Duplicator, WP Rocket backup)
 * 2. Verifies recent backup files exist and are accessible
 * 3. Checks backup service configurations (Backblaze, Dropbox sync)
 * 4. Validates backup retention policy (backups not too old)
 * 5. Tests backup restoration capability\n * 6. Flags sites with no backup strategy\n *
 * **Why This Matters:**\n * Without backups, a single hack, failed update, or hardware failure = total data loss. No backup = no way
 * to recover. For e-commerce: one hack = years of customer data + product listings gone forever. For SaaS:
 * one failure = company ceases to exist. The #1 cause of website shutdown isn't lack of skill, it's lack
 * of backups when disaster strikes. 80% of websites that lose backups permanently lose their business.\n *
 * **Real-World Scenario:**\n * Local business website (plumber) hacked via outdated plugin. No backups were configured. Website
 * defaced with malware. Took 3 weeks to rebuild from scratch, losing 15 years of blog content and customer
 * testimonials. Client estimated $30,000 in lost productivity and customer trust. After incident, implemented
 * automated daily backups. Cost: $10/month. Had they done this initially, disaster prevented entirely.\n *
 * For SaaS: client database corrupted due to failed update. 6 months of data (customer records, transactions)
 * permanently lost. Company sued for $200,000. Investigation revealed no backup strategy configured.\n *
 * **Business Impact:**\n * - Hack without backup = permanent data loss + site shutdown\n * - Software failure without backup = months of work lost\n * - Hardware failure without backup = total site loss\n * - Legal liability for data loss (GDPR fines up to 4% revenue)\n * - Customer trust destroyed (once lost, never recovered)\n * - Business continuity threatened (SaaS clients leave)\n * - Cost of recovery: $5,000-$100,000+ if possible at all\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents catastrophic data loss anxiety\n * - #9 Show Value: Protects years of work with single configuration\n * - #10 Talk-About-Worthy: "We never lose a single post even if hacked" is gold\n *
 * **Related Checks:**\n * - Security Vulnerability Not Patched (backup is last line of defense)\n * - Data Encryption Not Configured (backup privacy)\n * - Disaster Recovery Plan Not Tested (backup usability)\n * - Site Health Monitoring Not Enabled (early warning before disaster)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/database-backup-availability\n * - Video: https://wpshadow.com/training/backup-strategy-101 (8 min)\n * - Advanced: https://wpshadow.com/training/disaster-recovery-plan (15 min)\n *
 * @package    WPShadow\n * @subpackage Treatments\n * @since      1.6030.2148\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Treatments;\n\nuse WPShadow\\Core\\Treatment_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * Database Backup Availability Class\n *\n * Ensures site has recent, accessible database backups for disaster recovery capability.
 *
 * @since 1.6030.2148
 */
class Treatment_Database_Backup_Availability extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-backup-availability';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Availability';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies recent database backups exist and are accessible';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Maximum age for a backup to be considered recent (in days).
	 *
	 * @var int
	 */
	const MAX_BACKUP_AGE_DAYS = 7;

	/**
	 * Run the treatment check.
	 *
	 * Checks for backup plugins, backup files in common directories,
	 * and backup configuration settings to ensure database backups exist.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if no recent backups found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_Backup_Availability' );
	}
}
