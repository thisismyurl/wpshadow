<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

class Diagnostic_Monitor_Tag_Manager_Container_Errors extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-gtm-errors', 'title' => __('Tag Manager Container Health', 'wpshadow'), 'description' => __('Monitors GTM container for errors, failed tags, conflict issues. Tag errors = data loss.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/tag-manager/', 'training_link' => 'https://wpshadow.com/training/gtm-setup/', 'auto_fixable' => false, 'threat_level' => 7]; } 
}