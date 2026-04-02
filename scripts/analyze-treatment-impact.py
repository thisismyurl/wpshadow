#!/usr/bin/env python3
"""
Analyze treatments to identify high-impact treatments for average WordPress users.

Strategy:
- Keep treatments that fix common, tangible problems
- Remove cloud/pro-dependent treatments
- Remove enterprise-only features
- Focus on 75% of value proposition in 35% of treatments
"""

import os
import re
from pathlib import Path
from collections import defaultdict

# High-impact keywords that indicate valuable treatments for average users
HIGH_IMPACT_KEYWORDS = {
    'security': ['ssl', 'https', 'password', 'login', 'authentication', 'file-permission', 
                 'security-header', 'xss', 'sql-injection', 'csrf', 'directory-listing',
                 'two-factor', 'weak-password', 'session', 'encryption-at-rest',
                 'input-validation', 'output-escaped', 'sanitized', 'nonce'],
    'performance': ['cache', 'image-optimization', 'lazy-load', 'minif', 'compress',
                    'database-cleanup', 'transient', 'render-blocking', 'memory-limit',
                    'query-performance', 'dns-prefetch', 'preload', 'critical-css'],
    'seo': ['meta', 'sitemap', 'robots', 'canonical', 'schema', 'title', 'description',
            'permalink', 'redirect', 'noindex', 'nofollow', 'open-graph', 'twitter-card'],
    'settings': ['memory-limit', 'debug', 'error', 'timezone', 'permalink', 'comment',
                 'discussion', 'reading', 'writing', 'media', 'privacy-policy'],
    'accessibility': ['alt-text', 'color-contrast', 'keyboard', 'screen-reader', 'aria',
                      'focus', 'skip-link', 'heading', 'label', 'form-label', 'wcag'],
    'design': ['mobile', 'responsive', 'tap-target', 'form', 'cta', 'button', 'navigation',
               'menu', 'touch-target', 'font-size', 'dark-mode'],
}

# Keywords that indicate cloud/pro/enterprise-only features
EXCLUDE_KEYWORDS = [
    'monitoring', 'alerting', 'sla', 'uptime', 'log-aggregation', 'infrastructure-as-code',
    'container', 'orchestration', 'kubernetes', 'docker-swarm', 'high-availability',
    'load-balancer', 'multi-region', 'replication', 'failover', 'disaster-recovery',
    'ldap', 'active-directory', 'oauth2-sso', 'saml', 'vpn-secure-access',
    'waf-rules', 'ddos', 'intrusion-detection', 'malware-scanning', 'vulnerability-scan',
    'penetration-test', 'security-audit', 'compliance-checklist', 'change-management',
    'git-version-control', 'ci-cd', 'api-documentation', 'translation-ready',
    'business-continuity', 'data-loss-prevention', 'dlp-rules'
]

def calculate_impact_score(filepath):
    """Calculate impact score based on filename and category."""
    filename = filepath.name.lower()
    category = filepath.parent.name.lower()
    
    # Exclude cloud/pro/enterprise features
    for exclude in EXCLUDE_KEYWORDS:
        if exclude in filename or exclude in str(filepath):
            return 0
    
    score = 0
    
    # Add points for high-impact keywords
    for cat, keywords in HIGH_IMPACT_KEYWORDS.items():
        for keyword in keywords:
            if keyword in filename:
                score += 10
                
    # Boost score for critical categories
    if category == 'security':
        score += 5
    elif category == 'performance':
        score += 5
    elif category == 'seo':
        score += 4
    elif category == 'settings':
        score += 4
    elif category == 'accessibility':
        score += 3
    elif category == 'design':
        score += 3
    elif category == 'wordpress-health':
        score += 10  # Keep all WordPress health checks
    
    # Penalize complex/niche features
    if 'enterprise' in str(filepath):
        score = max(0, score - 20)
    if 'advanced' in filename:
        score = max(0, score - 5)
    
    return score

def main():
    treatments_dir = Path('/workspaces/wpshadow/includes/treatments')
    
    # Collect all treatment files with scores
    treatments = []
    category_counts = defaultdict(int)
    
    for php_file in treatments_dir.rglob('*.php'):
        if php_file.name.startswith('class-treatment-'):
            score = calculate_impact_score(php_file)
            category = php_file.parent.name
            treatments.append((score, php_file, category))
            category_counts[category] += 1
    
    # Sort by impact score (descending)
    treatments.sort(reverse=True, key=lambda x: x[0])
    
    # Calculate 35% threshold
    total_count = len(treatments)
    keep_count = int(total_count * 0.35)
    
    print(f"Total treatments: {total_count}")
    print(f"Target to keep (35%): {keep_count}")
    print()
    
    # Split into keep/remove
    keep_treatments = treatments[:keep_count]
    remove_treatments = treatments[keep_count:]
    
    # Analyze what we're keeping by category
    keep_by_category = defaultdict(int)
    for score, filepath, category in keep_treatments:
        keep_by_category[category] += 1
    
    print("KEEPING by category:")
    for category in sorted(keep_by_category.keys()):
        total = category_counts[category]
        keeping = keep_by_category[category]
        pct = (keeping / total * 100) if total > 0 else 0
        print(f"  {category}: {keeping}/{total} ({pct:.1f}%)")
    print()
    
    # Show top 50 treatments we're keeping
    print("TOP 50 HIGH-IMPACT TREATMENTS:")
    for i, (score, filepath, category) in enumerate(keep_treatments[:50], 1):
        filename = filepath.name.replace('class-treatment-', '').replace('.php', '')
        print(f"  {i:2d}. [{score:3d}] {category:20s} {filename}")
    print()
    
    # Show treatments we're removing (sample)
    print("SAMPLE OF TREATMENTS TO REMOVE (lowest impact):")
    for i, (score, filepath, category) in enumerate(remove_treatments[-30:], 1):
        filename = filepath.name.replace('class-treatment-', '').replace('.php', '')
        print(f"  {i:2d}. [{score:3d}] {category:20s} {filename}")
    print()
    
    # Write lists to files for processing
    with open('/tmp/treatments-to-keep.txt', 'w') as f:
        for score, filepath, category in keep_treatments:
            f.write(f"{filepath}\n")
    
    with open('/tmp/treatments-to-remove.txt', 'w') as f:
        for score, filepath, category in remove_treatments:
            f.write(f"{filepath}\n")
    
    print(f"\nWrote lists to:")
    print(f"  /tmp/treatments-to-keep.txt ({len(keep_treatments)} files)")
    print(f"  /tmp/treatments-to-remove.txt ({len(remove_treatments)} files)")

if __name__ == '__main__':
    main()
