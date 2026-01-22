<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Business_Impact_Score extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-business-impact', 'title' => __('Estimated Business Impact Score', 'wpshadow'), 'description' => __('Estimates $ revenue impact of issues (downtime = lost sales, slow site = abandonment). Prioritizes urgency.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/roi-tracking/', 'training_link' => 'https://wpshadow.com/training/impact-analysis/', 'auto_fixable' => false, 'threat_level' => 9]; } }
