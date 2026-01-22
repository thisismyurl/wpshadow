<?php
declare(strict_types=1);
/**
 * Malicious File Upload Detection Diagnostic
 *
 * @package WPShadow
 * @subpackage DiagnosticsFuture
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Malicious File Upload Detection
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Guardian module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "WPShadow found 3 PHP backdoors in your uploads folder"
 * 
 * @priority 1
 */
class Diagnostic_Malicious_File_Uploads extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'malicious-file-uploads';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Malicious File Upload Detection';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Scans uploads folder for PHP backdoors and malicious scripts.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        $upload_dir = wp_upload_dir();
        $suspicious_files = array();
        
        // Check uploads directory for PHP files (should never be there)
        if (!is_dir($upload_dir['basedir'])) {
            return null;
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($upload_dir['basedir']),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        $scan_count = 0;
        foreach ($files as $file) {
            if ($file->isFile()) {
                // Limit scan to avoid timeouts
                if (++$scan_count > 5000) {
                    break;
                }
                
                $ext = strtolower($file->getExtension());
                
                // PHP files in uploads = suspicious
                if (in_array($ext, array('php', 'phtml', 'php3', 'php4', 'php5', 'phar'))) {
                    $suspicious_files[] = str_replace($upload_dir['basedir'] . '/', '', $file->getPathname());
                }
            }
        }
        
        if (empty($suspicious_files)) {
            return null;
        }
        
        return array(
            'id'           => static::$slug,
            'title'        => static::$title,
            'description'  => sprintf(
                'Found %d suspicious file(s) in uploads directory: %s',
                count($suspicious_files),
                implode(', ', array_slice($suspicious_files, 0, 5))
            ),
            'severity'     => 'critical',
            'category'     => 'security',
            'kb_link'      => 'https://wpshadow.com/kb/malicious-uploads/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=malicious-uploads',
            'training_link' => 'https://wpshadow.com/training/malicious-uploads/',
            'auto_fixable' => false,
            'threat_level' => 100,
            'module'       => 'Guardian',
            'priority'     => 1,
        );
    }
    
    /**
     * IMPLEMENTATION PLAN
     * 
     * "Holy Shit" Moment: "WPShadow found 3 PHP backdoors in your uploads folder"
     * Revenue Path: Guardian
     * KB Article: https://wpshadow.com/kb/malicious-uploads/
     * Training Video: https://wpshadow.com/training/malicious-uploads/
     * 
     * Implementation Steps:
     * Scan wp-content/uploads/ recursively
     * Find PHP files (shouldn't exist in uploads)
     * Pattern matching: eval(), base64_decode(), system(), shell_exec(), exec()
     * YARA rule matching for known malware
     * Check file upload timestamps (recent = active threat)
     * Show exact file paths with preview
     * One-click quarantine (move to /quarantine/)
     * Optional delete with backup
     * 
     * KPI Tracking:
     * - Time saved: [Calculate based on severity]
     * - Issues found: [Count of findings]
     * - Value delivered: [Show $ impact if applicable]
     * 
     * Treatment Options (Future):
     * - Free: Basic remediation steps (KB link)
     * - Guardian: Advanced automation + monitoring
     * 
     * Philosophy Compliance:
     * - Free detection: ✅ Always accessible
     * - Paid fixes: ✅ Module-gated advanced features
     * - Education: ✅ KB + Training links
     * - KPI: ✅ Track measurable value
     * - Talk-worthy: ✅ Creates "holy shit" moments
     */
}
