# WA Reviews

A web application designed for completing and managing Cornell Web Accessibility reviews. The application helps ensure that web content meets accessibility standards by providing tools for review, issue tracking, and reporting.

Please refer to the Confluence page for detailed documentation: [WA Review App](https://confluence.cornell.edu/display/customdev/WA+Review+App)

## Table of Contents
- [Project Overview](#project-overview)
- [Key Features](#key-features)
- [Technical Stack](#technical-stack)
- [Project Structure](#project-structure)
- [Contributing](#contributing)

## Project Overview

WA Reviews streamlines the process of conducting web accessibility reviews at Cornell University. It provides a structured approach to identifying, documenting, and resolving accessibility issues across web properties.

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

## Technical Stack


- The application is build off of the Custom Dev [Laravel Starter Kit](https://github.com/CornellCustomDev/CD-LaravelStarterKit), extensively using [Livewire 3 full page components](https://livewire.laravel.com/docs/components#full-page-components) with FluxUI 2 and TailwindCSS 4
- Authentication is using Apache mod_shib, managed via [CornellCustomDev\LaravelStarterKit\CUAuth](https://github.com/CornellCustomDev/CD-LaravelStarterKit/blob/onelogin-saml/src/CUAuth/README.md)
- User permissions are managed using [Laratrust](https://laratrust.santigarcor.me/docs/8.x/) library (similar to Spatie's Laravel permissions, but with more robust team support)
- AI agents, tool usage, and chats are managed with [LarAgent](https://docs.laragent.ai/introduction) and [openai-php/client](https://github.com/openai-php/client) library
- [livewire/flux-pro](https://fluxui.dev/docs) is authenticated in the `auth.json`
- [Laravel Excel](https://docs.laravel-excel.com/) is utilized for spreadsheet import/export
- [Telescope](https://laravel.com/docs/12.x/telescope) is used for monitoring the system (note the the extensive use of Livewire means that a lot of data only gets recorded if you modify the default config to not ignore livewire)


## Project Structure

The application follows a standard Laravel project structure:

- **Livewire components**: UI interactions in `app/Livewire`
- **Models**: Data representation in `app/Models`
- **Controllers**: Request handling in `app/Http/Controllers`
- **Routes**: Defined in `routes/web.php`
- **Policies**: Authorization in `app/Policies`
- **Migrations**: Database schema management in `database/migrations`

## Contributing

When contributing to this project:

1. Create a feature branch for your work
2. Follow established coding standards and patterns
3. Submit pull requests for review

