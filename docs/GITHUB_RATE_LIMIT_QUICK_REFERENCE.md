# GitHub Rate Limit Quick Reference Card

**Print this. Use this. Live by this.**

---

## 🎯 The Hierarchy (Use in This Order)

### 1️⃣ LOCAL FIRST (No Cost - 90%+ of tasks)
```bash
grep_search "pattern"                # Search code
git log --oneline -20                # Recent commits
git log --grep="keyword"             # Search commits
git diff main                        # Compare changes
git status                           # File status
read_file "path/to/file.php"         # Read file
list_dir "path/to/dir"               # List directory
```
**Cost:** $0 | **Speed:** Instant | **Limit:** Unlimited

---

### 2️⃣ CACHED GITHUB CLI (5 min cache)
```bash
./scripts/gh-cached.sh api repos/thisismyurl/wpshadow/issues
./scripts/gh-cached.sh api repos/thisismyurl/wpshadow/pulls
```
**Cost:** 1 call per 5 min | **Speed:** Cached instant or ~1s | **Limit:** 50,000/hour

---

### 3️⃣ BATCHED GRAPHQL (Single efficient call)
```bash
gh api graphql -f query='
query {
  repository(owner:"thisismyurl", name:"wpshadow") {
    issues(first:10) { nodes { number title } }
    pullRequests(first:10) { nodes { number title } }
    labels(first:20) { nodes { name } }
  }
}'
```
**Cost:** 1 call for 3 resources | **Speed:** ~1-2s | **Limit:** 50,000/hour

---

### 4️⃣ REST API (Single resource)
```bash
gh api repos/thisismyurl/wpshadow/issues/123
gh pr view 456
```
**Cost:** 1 call per resource | **Speed:** ~1s | **Limit:** 50,000/hour

---

### 5️⃣ SEARCH API (ONLY IF ABSOLUTELY NECESSARY)
```bash
gh search issues "label:bug repo:thisismyurl/wpshadow"
```
**Cost:** 1 call | **Speed:** ~2-3s | **Limit:** ⚠️ 30/minute ONLY

---

## 🚨 What NOT to Do

| ❌ BAD | ✅ GOOD | Cost Saved |
|--------|---------|-----------|
| GitHub code search | `grep_search` | 1 quota |
| GitHub issue API | `git log --grep` | 1 quota |
| API fetch file | `read_file` | 1 quota |
| Loop over API calls | GraphQL batch | N-1 quotas |
| Poll API repeatedly | One-time check | 99% quota |
| Search API for code | `grep_search` | 1 quota |

---

## 📊 Rate Limits (Authenticated)

| API | Limit | Current | % Used | Resets |
|-----|-------|---------|--------|--------|
| Core (REST) | 50,000/hour | 15 used | 0.03% | Every hour |
| GraphQL | 50,000/hour | 57 used | 0.11% | Every hour |
| Search | 30/minute | 0 used | 0% | Every minute |
| Code Search | 10/minute | 0 used | 0% | Every minute |

**Status:** ✅ EXCELLENT - All quotas healthy

---

## 🔥 Emergency Protocol

**If rate limit drops below 25%:**
```bash
./scripts/check-rate-limits.sh        # Check status
export GITHUB_DISABLE_API=1            # Disable API usage
# Switch to 100% local operations only
```

**If rate limit drops below 10%:**
```bash
# STOP all GitHub API calls immediately
# Wait for reset (check: ./scripts/check-rate-limits.sh)
# Use ONLY local git/grep operations
```

---

## 💡 Decision Tree

```
Need information?
  ├─ Is it in local code?
  │  └─ YES: Use grep_search or read_file ✅ (no cost)
  │
  ├─ Is it a recent commit?
  │  └─ YES: Use git log ✅ (no cost)
  │
  ├─ Will I need this again in next 5 minutes?
  │  └─ YES: Use gh-cached.sh ✅ (cached, cheap)
  │
  ├─ Do I need multiple GitHub resources?
  │  └─ YES: Use GraphQL batch ✅ (1 call for many)
  │
  ├─ Do I need just one GitHub resource?
  │  └─ YES: Use REST API ⚠️ (1 call)
  │
  └─ Do I need to search GitHub code?
     └─ LAST RESORT: Use search API 🚨 (30/min limit!)
```

---

## 🛠️ Useful Commands

### Check Rate Limits
```bash
./scripts/check-rate-limits.sh          # Quick check
./scripts/check-rate-limits.sh --verbose # Detailed
./scripts/daily-rate-limit-report.sh    # Full report
```

### Monitor Cache
```bash
ls -la .cache/github/                   # See cache
du -sh .cache/github/                   # Cache size
find .cache/github -mmin -5             # Fresh cache
```

### Clear Cache
```bash
rm -rf .cache/github/*                  # Clear all
find .cache/github -mmin +5 -delete     # Clear old (>5min)
```

### Manual API Call
```bash
gh api rate_limit | jq '.rate'          # Check limits
gh api repos/thisismyurl/wpshadow       # Single resource
gh api repos/thisismyurl/wpshadow/issues --paginate  # All issues
```

---

## 📋 Agent Rules

### ALWAYS:
- ✅ Use `grep_search` first
- ✅ Use `git log` for commits
- ✅ Cache API responses
- ✅ Batch with GraphQL
- ✅ Check limits before batch work

### NEVER:
- ❌ Poll APIs
- ❌ Use search API (unless absolutely necessary)
- ❌ Sequential API calls (batch instead)
- ❌ GitHub code search (use grep_search)
- ❌ Repeated same API call (use cache)

---

## 🎯 TL;DR

| Question | Answer | Cost |
|----------|--------|------|
| How to search code? | `grep_search` | $0 |
| How to find commits? | `git log --grep` | $0 |
| How to check file? | `read_file` | $0 |
| How to batch queries? | GraphQL | 1 call |
| How to avoid repeats? | `gh-cached.sh` | 1/5min |
| How to check limits? | `./scripts/check-rate-limits.sh` | $0 |

**Philosophy:** Local first. Cache second. API last.

---

**Keep this handy. Reference often. Question every GitHub API call.**

*Current Status: ✅ HEALTHY (49,985/50,000 remaining)*
