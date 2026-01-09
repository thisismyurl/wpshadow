# AGENT GITHUB LOGGING CHECKLIST

**Owner:** thisismyurl  
**Repository:** core-support-thisismyurl (primary) + image/license/vault/media support plugins  
**Purpose:** Enforce comprehensive GitHub issue tracking for all development

---

## FOR EVERY DEVELOPMENT TASK

Use this checklist to ensure GitHub logging is complete:

### Before Starting
- [ ] Search GitHub for existing issue (don't duplicate)
- [ ] Check if similar work is already in progress
- [ ] Identify correct milestone (M01-M05 or M99)
- [ ] Note the owner: `thisismyurl`

### When Opening New Work
- [ ] Create issue with title: `[Type] Description`
- [ ] Add detailed description (why, what, how)
- [ ] Set acceptance criteria
- [ ] Apply relevant labels (feature, bug, refactor, enhancement, etc.)
- [ ] Assign to milestone (M01, M02, M03, M04, M05, M99)
- [ ] Comment: "Opening for work - starting now"

### During Development
- [ ] Update issue with progress every major step
- [ ] Document any blockers or decisions
- [ ] Reference commit hashes: `Commit: abc1234`
- [ ] Link to related issues: `Related to #123`
- [ ] Keep status label current

### When Creating Pull Request
- [ ] Use format: `feat/fix/refactor: Description`
- [ ] Reference issue: `Fixes #123` in PR description
- [ ] Link PR in GitHub issue comment
- [ ] Update issue: "PR #456 opened - under review"

### When Work Completes
- [ ] Add final summary comment to issue
- [ ] Reference the PR that fixed it
- [ ] Update any related issues
- [ ] Close issue with: `Closed via PR #456`
- [ ] Verify milestone status

### On Investigation Tasks
- [ ] Log findings as issue comments
- [ ] Update title if scope changes
- [ ] Document decision/outcome
- [ ] Link to related decisions

---

## GITHUB USERNAME REMINDER

🔑 **Your GitHub username is:** `thisismyurl`

Use this in:
- Issue assignments
- PR mentions
- Milestone tracking
- Repository references

---

## REPOSITORIES TO TRACK

| Repo | Type | Status |
|------|------|--------|
| `core-support-thisismyurl` | HUB | Primary tracking repo |
| `image-support-thisismyurl` | SPOKE | Image processing |
| `license-support-thisismyurl` | SPOKE | License management |
| `vault-support-thisismyurl` | SPOKE | Vault operations |
| `media-support-thisismyurl` | SPOKE | Media library |

**All repos owned by:** `thisismyurl`

---

## MILESTONE MAPPING

When creating/updating issues, use correct milestone:

- **M01** = Foundations (Jan 31)
- **M02** = Vault Core (Feb 29)
- **M03** = Diagnostics (Mar 31)
- **M04** = Multisite & UX (Apr 15)
- **M05** = Templates & Dependencies (Apr 30)
- **M99** = UX Polish & Future

---

## RED FLAGS (Don't Do These)

❌ Skip creating an issue  
❌ Work without GitHub tracking  
❌ Forget to reference issue in PR  
❌ Close issues without linking PRs  
❌ Work on old/stale issues without updating  
❌ Create duplicate issues  
❌ Use wrong milestone  
❌ Forget to assign labels  

---

## QUICK START FOR AGENTS

```bash
# 1. Check for existing issue
mcp_io_github_git_search_issues "owner:thisismyurl repo:core-support-thisismyurl [search term]"

# 2. Create new issue if needed
mcp_io_github_git_issue_write method:create owner:thisismyurl repo:core-support-thisismyurl

# 3. Update issue with progress
mcp_io_github_git_add_issue_comment owner:thisismyurl repo:core-support-thisismyurl issue_number:123

# 4. Create PR when ready
mcp_io_github_git_create_pull_request owner:thisismyurl repo:core-support-thisismyurl

# 5. Close issue when done
mcp_io_github_git_issue_write method:update owner:thisismyurl repo:core-support-thisismyurl state:closed
```

---

## NON-NEGOTIABLE

✅ **MUST HAPPEN:**
- Every task gets a GitHub issue
- Every issue is in correct milestone
- Every PR references its issue
- Every completion is logged

✅ **AGENT AUTONOMY:**
- No asking permission for GitHub operations
- No asking to create issues
- No asking to update tracking
- Just do it and report

---

**Last reminder:** Your GitHub username is `thisismyurl`. Use it everywhere. Log everything. Move fast.
