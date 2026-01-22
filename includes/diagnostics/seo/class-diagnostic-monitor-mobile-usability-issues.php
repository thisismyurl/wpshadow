<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
class Diagnostic_Monitor_Mobile_Usability_Issues extends Diagnostic_Base { public static function check(): ?array { return ['id' => 'monitor-mobile-issues', 'title' => __('Mobile Usability Issues Detection', 'wpshadow'), 'description' => __('Tracks viewport, clickable element, font size issues. Mobile-first indexing penalties from poor mobile UX.', 'wpshadow'), 'severity' => 'high', 'category' => 'monitoring', 'kb_link' => 'https://wpshadow.com/kb/mobile-optimization/', 'training_link' => 'https://wpshadow.com/training/responsive-design/', 'auto_fixable' => false, 'threat_level' => 8]; } }
