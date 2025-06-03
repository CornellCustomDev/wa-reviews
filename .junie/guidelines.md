# WA Reviews Project Overview

## Introduction
WA Reviews is a web application designed for completing and managing Cornell Web Accessibility reviews. The application helps ensure that web content meets accessibility standards by providing tools for review, issue tracking, and reporting. It is an implementation of [Cornell's WCAG testing guidelines](https://docs.google.com/document/d/16b77RZcTL0bWZXTkMgJNd0rfkrv-x83jktacVB8NY-U/edit?tab=t.0#heading=h.oab37tgoxnuc) and checklist.

## Project Purpose
The primary purpose of this application is to streamline the process of conducting web accessibility reviews at Cornell University. It provides a structured approach to identifying, documenting, and resolving accessibility issues across web properties.

## Key Features
1. **Project Management**
   - Create and manage accessibility review projects
   - Track project status and progress
   - Generate accessibility reports

2. **Team Collaboration**
   - Team-based access control
   - Collaborative review workflows
   - Role-based permissions

3. **Issue Tracking**
   - Document accessibility issues
   - Categorize issues by scope and severity
   - Track issue resolution progress

4. **Accessibility Guidelines**
   - Reference to accessibility standards and guidelines
   - Categorized criteria for evaluation
   - ACT rules, SIA rules, and Siteimprove rules integration

5. **AI Assistance**
   - AI-powered chat for accessibility questions
   - Page analysis tools
   - Automated suggestions
   - AI agents with specialized tools for accessibility analysis

## Technical Stack
- **Framework**: Laravel (PHP)
- **Frontend**: Livewire components using FluxUI and Tailwind CSS
- **Authentication**: Cornell SSO integration via Apache mod_shib
- **Database**: MySQL/MariaDB
- **AI Integration**: Cornell AI API Gateway with LarAgent and OpenAI
- **Permissions**: Laratrust library for role-based access control
- **Data Import/Export**: Laravel Excel for spreadsheet handling

## Project Structure
The application follows a standard Laravel project structure with:
- Livewire components for UI interactions
- Models for data representation
- Controllers and routes for request handling
- Policies for authorization
- Migrations for database schema management

## Workflow Process

The application follows a specific workflow for accessibility reviews:

1. **Create a Project**: Start by creating a new review project from the Projects page or from a Team's Projects page.
   - Projects can be set up like creating a new WA Checklist
   - Data can be imported via the Upload button

2. **Add Scope**: Define the scope of the review on the Project page.
   - Add scope items via the Scope tab
   - Import scopes from a spreadsheet checklist using the standard sheets format

3. **Assign Reviewer**: In the Workflow panel, assign the project to a reviewer and update status to Start Review.

4. **Add Issues**: Document accessibility issues via the Issues tab on the Project page or from a Scope page.
   - Add observations manually
   - Use AI to suggest observations (must be approved to be included in reports)

5. **Complete and Report**: Fill out report fields, update status to Complete Review, and view the report.
   - Add read-only viewers via the Report Viewers panel

## Team Structure

The application uses a team-based approach to manage access:

- **Site Admins**: Create teams and manage system-wide settings
- **Team Admins**: Manage team users and projects
- **Reviewers**: Work on assigned projects
- **Report Viewers**: Have read-only access to specific reports

## Architectural Patterns

The application follows several key architectural patterns:

1. **Model-driven architecture**: Relationships and actions are defined on models rather than directly modifying relationships. Use model actions like `Project→assignTouser()` and `Project→addReportViewer()`.

2. **Event-driven activity logging**: Data-changing actions trigger events that write to the activities table for audit purposes.

3. **AI agent pattern**: AI functionality is implemented using LarAgent with specialized tools and chat history management.

4. **Role-based access control**: Permissions are applied via Policies at the model level using the Laratrust library.

## Contributing
When contributing to this project, please ensure you follow the established coding standards and patterns. Create feature branches for new functionality and submit pull requests for review.
