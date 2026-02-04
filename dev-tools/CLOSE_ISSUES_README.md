# Closing Completed Diagnostic Issues

This guide explains how to close GitHub issues for implemented diagnostics.

## Prerequisites

1. **GitHub Personal Access Token** with `repo` permissions
   - Visit: https://github.com/settings/tokens
   - Click "Generate new token (classic)"
   - Select scope: `repo` (Full control of private repositories)
   - Generate and copy the token

2. **Python 3** with `requests` library
   ```bash
   pip install requests
   ```

## Quick Start

### 1. Set Your GitHub Token
```bash
export GITHUB_TOKEN="ghp_your_token_here"
```

**⚠️ Security Note:** Never commit your token to git. The token gives access to your repositories.

### 2. Close Issues for 137 Implemented Diagnostics

**Dry Run (Check Status First):**
```bash
cd dev-tools
python3 close-completed-diagnostic-issues.py --start 4800 --count 137 --dry-run
```

**Actually Close Issues:**
```bash
python3 close-completed-diagnostic-issues.py --start 4800 --count 137
```

## Usage Examples

### Close Specific Batch
Close only issues from batch 1 (11 diagnostics):
```bash
python3 close-completed-diagnostic-issues.py --start 4800 --count 11
```

### Close with Custom Comment
```bash
python3 close-completed-diagnostic-issues.py \
  --start 4800 \
  --count 137 \
  --comment "✅ Implemented in batch creation sprint (Feb 4, 2026)"
```

### Close from Different Repository
```bash
python3 close-completed-diagnostic-issues.py \
  --repo-owner yourname \
  --repo-name yourrepo \
  --start 4800 \
  --count 137
```

## How It Works

1. **Scans** workspace for implemented diagnostic files
2. **Checks** each issue number from start to start+count
3. **Verifies** issue exists and is open
4. **Adds comment** explaining implementation (optional)
5. **Closes** the issue via GitHub API
6. **Reports** summary of actions taken

## What Gets Closed

Based on your current progress:

| Batch | Issues | Count | Status |
|-------|--------|-------|--------|
| Batch 1 | #4800-#4810 | 11 | ✅ Implemented |
| Batch 2 | #4811-#4819 | 9 | ✅ Implemented |
| Batch 3 | #4820-#4839 | 20 | ✅ Implemented |
| Batch 4 | #4840-#4859 | 20 | ✅ Implemented |
| Batch 5 | #4860-#4877 | 18 | ✅ Implemented |
| Batch 6 | #4878-#4896 | 19 | ✅ Implemented |
| Batch 7 | #4897-#4916 | 20 | ✅ Implemented |
| Batch 8 | #4917-#4936 | 20 | ✅ Implemented |
| **Total** | **#4800-#4936** | **137** | **Ready to close** |

## Expected Output

```
🔍 Checking issues #4800 through #4936...

#4800 - ✅ Closed: No email marketing list diagnostic
#4801 - ✅ Closed: No social media integration
#4802 - ✓  Already closed: No analytics tracking
#4803 - ⚠️  Not found (may not exist)
...

======================================================================
📊 SUMMARY
======================================================================
✅ Closed:          120
✓  Already closed:   15
⚠️  Not found:        2
❌ Failed:            0
📊 Total checked:   137
======================================================================

✨ Done!
```

## Troubleshooting

### "GITHUB_TOKEN environment variable not set"
**Solution:** Export your GitHub token:
```bash
export GITHUB_TOKEN="ghp_your_token_here"
```

### "401 Unauthorized"
**Solution:** Your token is invalid or expired. Generate a new one.

### "404 Not Found" for many issues
**Explanation:** Issues may not exist yet. This happens if:
- Issues weren't created for every diagnostic
- Issue numbers don't match diagnostic count
- Using wrong repository

**Solution:** Use `--dry-run` first to see which issues exist.

### "403 Forbidden"
**Solution:** Your token lacks `repo` permissions. Regenerate with correct scope.

### Rate Limiting
GitHub API has rate limits:
- Authenticated: 5,000 requests/hour
- This script uses 1-2 requests per issue
- 137 issues = ~274 requests (well under limit)

## Alternative: Manual Closing

If you prefer manual closing or script fails:

1. **Get issue list:**
```bash
python3 close-completed-diagnostic-issues.py --start 4800 --count 137 --dry-run > issue_status.txt
```

2. **Close manually** via GitHub web interface:
   - Visit each issue URL
   - Add comment: "✅ Diagnostic implemented"
   - Click "Close issue"

## What Happens When You Close

1. **Issue status** changes from "Open" to "Closed"
2. **Comment added** explaining implementation
3. **Labels preserved** (not removed)
4. **Milestone preserved** (if set)
5. **Can be reopened** if needed later

## Safety Features

- **Dry run mode** to preview actions
- **Already closed check** (won't re-close)
- **Not found handling** (skips missing issues)
- **Detailed logging** of all actions
- **Error handling** for API failures

## Next Steps After Closing

1. ✅ Close GitHub issues (you are here)
2. 🔄 Continue with remaining ~78 diagnostics (4 more batches)
3. 🧪 Create test classes for all 137 diagnostics
4. 📚 Generate KB articles for documentation
5. 🎯 Create additional treatments for auto-fixes

## Questions?

See the script's help:
```bash
python3 close-completed-diagnostic-issues.py --help
```

---

**Script Location:** `dev-tools/close-completed-diagnostic-issues.py`  
**Last Updated:** February 4, 2026  
**Diagnostics Implemented:** 137 / 215 (63.7%)
