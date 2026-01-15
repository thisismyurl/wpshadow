# Site Health Quick Reference

## How It Works

```
Feature Reports → WPS Calculates → WordPress Displays
```

## Score Formula

```
Overall Health = (Security × 60%) + (Performance × 40%)
```

## Sub-Feature Point Values

### Head Cleanup (100 max)
- Emoji Scripts: **20 points** ⭐ (High impact)
- DNS Prefetch: **20 points** ⭐ (High impact)
- oEmbed Links: **15 points** (High impact)
- WP Generator: **10 points** (Security)
- Feed Links: **10 points** (Medium)
- REST API Link: **10 points** (Medium)
- RSD Link: **5 points** (Minimal)
- WLWManifest: **5 points** (Minimal)
- Shortlink: **5 points** (Minimal)

**Example**: Enabling just Emoji Scripts (20) + DNS Prefetch (20) = 40% of max head cleanup score

### Database Cleanup (100 max)
- Revisions: **25 points** ⭐ (Biggest impact)
- Auto-Drafts: **20 points** (High volume)
- Trashed Posts: **15 points**
- Spam Comments: **15 points**
- Transients: **15 points**
- Optimize Tables: **10 points**

## Weight Categories

| Weight | Multiplier | When to Use |
|--------|-----------|-------------|
| **Critical** | 100% | Security core, essential caching |
| **High** | 75% | Performance boosters |
| **Medium** | 50% | Cleanup, optimization |
| **Low** | 25% | Minor tweaks |

## Quick Wins

### To Boost Security Score Fast
1. Enable **Hardening** (+20 points)
2. Enable **Firewall** (+20 points)
3. Enable **Malware Scanner** (+15 points)

**Result**: 55+ security score

### To Boost Performance Score Fast
1. Enable **Page Cache** (+15 points)
2. Enable **CDN Integration** (+12 points)
3. Enable **Image Optimizer** (+10 points)

**Result**: 37+ performance score

## WordPress Site Health Access

Navigate to: **Tools → Site Health**

Look for these badges:
- 🔵 **Security** (WPS Security Score)
- 🟠 **Performance** (WPS Performance Score)
- 🟢 **WP Support** (Overall Health)
- 🟣 **Features** (Feature Status)

## Dashboard Widget Access

- **Location**: WordPress Dashboard
- **Position**: Normal column (high priority)
- **Updates**: Auto-refreshes every 5 minutes
- **Actions**: 
  - "View Site Health" → WordPress Site Health
  - "WP Support Dashboard" → WPS main dashboard

## API Endpoint

**AJAX Call**:
```javascript
jQuery.post(ajaxurl, {
    action: 'wps_get_health_score',
    nonce: wpsHealth.nonce
}, function(response) {
    console.log(response.data.overall); // 85
    console.log(response.data.security); // 90
    console.log(response.data.performance); // 78
});
```

## Color Indicators

- 🟢 **Green (80-100)**: Good
- 🟡 **Yellow (60-79)**: Needs Improvement
- 🔴 **Red (0-59)**: Critical

## Typical Scores

| Configuration | Security | Performance | Overall |
|--------------|----------|-------------|---------|
| Minimal (2-3 features) | 35 | 25 | 29 (🔴) |
| Basic (5-7 features) | 55 | 45 | 51 (🔴) |
| Standard (10-12 features) | 70 | 65 | 68 (🟡) |
| Optimal (15+ features) | 90 | 85 | 88 (🟢) |

## Troubleshooting

**Score lower than expected?**
1. Check feature is actually **active** (not just enabled)
2. Verify sub-features configured correctly
3. Clear cache (`wp cache flush`)
4. Check PHP error logs

**WordPress Site Health not showing WPS tests?**
1. Deactivate/reactivate WP Support
2. Clear transients: `wp transient delete --all`
3. Check for filter conflicts

**Dashboard widget not appearing?**
1. Check "Screen Options" (top right)
2. Verify user has `manage_options` capability
3. Check browser console for JavaScript errors

---

**Quick Start**: Enable Page Cache + Firewall for instant 50+ overall health score!
