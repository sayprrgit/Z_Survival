---
name: token-economy
description: Minimize token use for any task while retaining the information, checks, and precision needed to complete it reliably.
---

# Token economy

Use this skill when the user asks to conserve tokens, reduce context use, or
work concisely.

- Start with the result; use the shortest complete answer appropriate to the
  request.
- Read only files, logs, and documentation directly relevant to the task; use
  targeted searches and bounded command output.
- Avoid restating the request, instructions, tool output, or unchanged context.
- Prefer a single focused tool call and batched independent checks over
  exploratory repetition.
- Do not trade away required verification, safety checks, or user-requested
  detail solely to save tokens.
- Report changed files and essential validation only; omit routine process
  narration unless it affects a decision or outcome.
