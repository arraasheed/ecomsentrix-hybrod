# Workspace Rules — eCommerce Go SPA/PWA Project

> These rules are loaded automatically by Antigravity for every conversation in
> this workspace. They apply to **all** models working in this repository.

## 0. Before you do anything

Read these two files first, every session, without being asked:

1. `.ai/PROJECT_CONTEXT.md` — the full ruleset, codebase facts, known problems
2. `.ai/PROGRESS.md` — what is done, what is next, any open blockers

Then open your own task file and work on **one numbered prompt only**:

| You are | Your task file |
|---|---|
| Gemini | `.ai/GEMINI_TASKS.md` |
| Claude | `.ai/CLAUDE_TASKS.md` |
| BULK model (Ling / MiniMax / Nemotron) | `.ai/BULK_TASKS.md` |

## 1. Resuming after a crash, restart, or power loss

In Antigravity, a hook injects a resume brief at the start of every session
(`.agents/scripts/resume-context.cjs`). It reads `.ai/PROGRESS.md`, `git log`,
and the crash journal at `.ai/state/`.

**In any other tool (OpenCode, Cursor, web chat) that hook does not run.** There,
reconstruct the state yourself — it takes four commands:

```
read  .ai/PROJECT_CONTEXT.md
read  .ai/PROGRESS.md
run   git log --oneline -20
run   git status --short
```

**If the user says "continue" or "resume the work", do not ask them what you
were doing.** Find your first `⬜ PENDING` or `🟨 IN PROGRESS` task in the
PROGRESS.md table and pick it up.

If your task is `🟨 IN PROGRESS`, read its report in `.ai/reports/` to see which
files are already done, and continue from the next one. **Never redo a finished
file** — double-wrapping a script breaks the code.

**When `git log` and `PROGRESS.md` disagree, git is correct.** Commits cannot
lie; a markdown table can be stale. Fix the table, do not redo the work.

## 1a. Checkpoint every 5 files on bulk tasks

Cheap models hit daily token limits mid-task and reset with no memory. A commit
only at the end of a 20-file task means losing 13 files of work.

On BULK-1 … BULK-4, after every 5 files:

```
1. append those 5 rows to the task's report in .ai/reports/
2. set the task to 🟨 IN PROGRESS in .ai/PROGRESS.md, note "N/total done"
3. git add -A
   git commit -m "wip(<scope>): <task> files X-Y  [<MODEL>-<TASK#>]"
```

Worst case then loses 4 files, not 20.

## 2. Never touch these

```
app/                 (exception: adding new files where a task explicitly says so)
routes/
database/
vendor/
packages/workdo/     (exception: creating a new module, when a task says so)
```

This project is a licensed script (WorkDo / eCommerce Go). The user must be able
to merge upstream updates later. Every change to core code destroys that path.
Roughly 15 files are approved for modification — they are listed in
`.ai/PROJECT_CONTEXT.md`.

## 3. Every change needs an off switch

Anything you add must be disableable via a `.env` flag or a one-line comment.
If you cannot design a kill-switch for a change, that change is too risky —
write it into Blockers and ask.

## 4. Commit after every task

```
git add -A
git commit -m "<type>(<scope>): <what>  [<MODEL>-<TASK#>]"
```

Example: `perf(vite): bundle 28 admin css into one file  [CLAUDE-2]`

Then update the task's row in `.ai/PROGRESS.md` (status + commit hash).
**A task without a commit and a PROGRESS.md update is a task that will be lost.**

### Never push on your own

```
origin = https://github.com/arraasheed/ecommercego.git   (PUBLIC)
```

Commit freely, locally. But `git push` only when the user explicitly asks.
The repo is public: a secret that is committed can still be fixed, a secret
that is pushed lives in GitHub's history forever.

`git push --force`, `git reset --hard`, and `git clean` are never yours to run.

## 5. When you are stuck

Three attempts maximum. Then:

1. Enable the relevant kill-switch so the app still works
2. Write the failure into the `## 🚧 Blockers` section of `.ai/PROGRESS.md`:
   what you tried, the exact error, what you need
3. Stop and tell the user

Do not improvise a fix on shared code. Do not silently skip the task.

## 6. Do not give the user technical work

The user is not a developer. They copy prompts and paste reports. They cannot
run test suites, read stack traces, or verify CSS. If verification is needed,
the model with browser tools (Claude) does it.

## 7. Scope discipline

Work on your assigned task number and nothing else. Do not "also fix" things you
notice — write them down in your report instead. Unrequested changes are how a
four-day project becomes a three-week debugging session.

## 8. Image Inspection / Vision for all models

When any model (especially non-vision models like DeepSeek, Ling, MiniMax) needs to read, inspect, or analyze an image or screenshot, run:
```bash
node .agents/scripts/gemini-vision.cjs <path/to/image.png> [optional prompt]
```
The script transcribes all text, UI elements, errors, and layout details to stdout.

