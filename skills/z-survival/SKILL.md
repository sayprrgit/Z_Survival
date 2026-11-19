---
name: z-survival
description: Develop the Z_Survival Symfony and Canvas roguelite while preserving its backend/frontend boundary and MVP scope.
---

# Z_Survival

Use this skill for changes to the Z_Survival game. Read `AGENTS.md` first.

- Treat Symfony as the authoritative owner of accounts, catalogue, permanent
  progression, run creation and reward validation.
- Treat `assets/game/` as an offline, ephemeral Canvas simulation. Do not put
  Doctrine calls, HTTP polling or persistent account rules in the game loop.
- Add new catalogue content through entities/fixtures and domain definitions;
  do not hard-code balance values in controllers or Twig templates.
- Keep controllers thin and route changes behind security, CSRF and tests.
- Prefer the smallest playable MVP increment; excluded features stay excluded
  unless the user explicitly expands the scope.

For data-model work, read `docs/ARCHITECTURE.md` and add the corresponding
migration, fixture and test with the behavior.
