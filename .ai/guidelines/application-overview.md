## Application Overview

WA Reviews implements Cornell's WCAG accessibility review workflow. Teams create Projects with defined Scopes, document accessibility Issues/Items, then generate reports.

## Domain Model

`Team` → `Project` → `Scope` → `Issue` → `Item` (review hierarchy)  
`Guideline` → `Criterion` → `Category` (accessibility standard hierarchy)

## Architecture

- **Livewire-first**: 96 components across 18 feature domains in `app/Livewire/[Feature]/`; only 3 HTTP controllers
- **Model-driven actions**: Use model methods, not inline logic — e.g. `Project->assignToUser()`, `Project->addReportViewer()`
- **Event-driven audit**: Data-changing actions trigger events → written to `activities` table automatically
- **AI agents**: Prism + LarAgent with tool-calling; chat history in `chat_histories`; agents in `app/Ai/`
- **RBAC**: Laratrust library; authorization via policies at model level; role hierarchy: Site Admin > Team Admin > Reviewer > Report Viewer

## Livewire Patterns

Complex features follow: `Show[Feature].php` (primary view) + `[Feature]Sidebar`, `[Feature]Analyzer`, `[Feature]Chat` sub-components. Form components in `app/Livewire/Forms/`.
