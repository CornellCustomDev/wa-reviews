# Contributing to the Laravel Starter Kit

## Getting Started
There are several places for information on what needs to be worked on:
- See the #cd-laravel-starter-kit CIT Slack channel for current activity.
- See the [GitHub project discussions](https://github.com/CU-CommunityApps/CD-LaravelStarterKit/discussions) for general notes and discussion.
- See the [GitHub issues list](https://github.com/CU-CommunityApps/CD-LaravelStarterKit/issues) and [pull requests](https://github.com/CU-CommunityApps/CD-LaravelStarterKit/pulls) for work that needs to be done or reviewed.

## Starter Kits, Projects, and Packages
Contributing code to the Laravel Starter Kit will generally happen in this repository, but doing so requires understanding the different layers by which the Starter Kit is implemented and delivered.

- **Projects** are client sites built with the Starter Kit. The process for using the Stater Kit when starting a project is documented in the [README](./README.md).
- The **Laravel Starter Kit** is a package that is delivered with composer and has its own composer dependencies on Laravel and Custom Dev packages (see the [composer.json file](./composer.json)).
- **Other packages**, such as `cubear/cwd_framework_lite` are developed separately but used by the Starter Kit.
- The `orchestra/testbench` package is a dev-only requirement of the Starter Kit that places a Laravel project directory in `./vendor/orchestra/testbench-core/laravel` that is used for running automated tests.
- There are settings in `.gitattributes` file and `composer.json` which make sure that resources needed to develop the Starter Kit do not get included when installing the Starter Kit in a project.

## Local Development Setup
Since the Laravel Starter Kit is a package, the development of it is not the same as a Laravel project. It may be of use to read [Laravel Package Development](https://laravelpackage.com/) and the [Laravel Package Development documentation](https://laravel.com/docs/9.x/packages) and make sure that you understand the distinctions outlined above in [Starter Kits, Projects, and Packages](#starter-kits-projects-and-packages).

This repository has a `.lando.yml` that can be used to run the development environment. It is not a requirement. Once you run `lando start`, you should be able to open a terminal with:

```shell
lando ssh
```

### Installing dependencies
To fully set up your local environment, you will need to install the composer-managed dependencies (if you are not using lando, please make sure to use an up-to-date instance of composer version 2):

```shell
composer install
```

You should then confirm that everything is set up properly by running the tests and confirming they pass ([see Testing](#Testing) below).

```shell
php ./vendor/bin/phpunit
```

> **NOTE**<br>
> Since the `vendor` directory is not committed to the repository, it is important to run `composer install` whenever there are dependency updates. This is also a good first step if tests are failing and you have not made any local changes.

### Functional testing
Since the Starter Kit is a package, functionally testing it requires creating a Laravel project and then composer requiring the Starter Kit. 

- Follow the [README - Usage](./README.md#usage) steps to set up a test Laravel project
- Once you have done this you should have a working Laravel site with the cwd_framework theming

While this is successful for seeing how someone will use the Starter Kit, it is not a fast way to develop and test changes, since you will need to push a commit to GitHub and then composer require an update to the Starter Kit to get the changes into your test project. Read the next section for an alternative local approach.

#### Local composer repository 
For testing, you can composer require the starter kit using a path reference, which is much faster than committing and
pushing to GitHub and then running composer update. Good instructions for how to do this can be found on
the [Laravel Package Development](https://laravelpackage.com/02-development-environment.html#importing-the-package-locally)
site.

If you are using Lando, you will also need a project `.lando.local.yml` to map a directory for the path. An example is
provided in [`/project/.lando.local.dev.html`](./project/.lando.local.dev.yml).

Your `composer.json` needs a repository definition that installs your local Starter Kit code as a symbolic link from the
mapped directory:
```
    "repositories": [
        {
            "type": "path",
            "url": "/laravel-starter-kit"
        }
    ],
```

The `url` value should be the full path to the directory.

## Development Standards
Meeting the goals of the Starter Kit includes developing in ways that make it easy to collaborate, maintain, and support our work.

### Design principles
Here are some principles that can apply to all development (based on [drupal.org CSS architecture goals](https://www.drupal.org/docs/develop/standards/css/css-architecture-for-drupal-9#goals)):
- Predictable: Code is consistent and understandable, doing what is expected without side effects
- Reusable: Code is abstract and decoupled enough that you can build new components quickly from existing parts without having to recode patterns and problems you’ve already solved
- Maintainable: as new work is needed, it should be easy to add or refactor existing code
- Scalable: code should be easy to manage for a single developer and distributed teams

### Code style and linting
Laravel coding conventions and style can be followed in part by using automated linting. All pull requests must pass code style linting. Code linting in this starter kit is set up to use [Laravel Pint](https://laravel.com/docs/9.x/pint), which can be run with
  ```shell
  lando pint
  ```
or
  ```shell
  php ./vendor/bin/pint
  ```

Configuration for Pint is in `pint.json`. Use `--test` option to have errors reported without having fixes applied.

### Testing
Automated test coverage is valuable for supporting collaboration on the starter kit. All pull requests must pass unit testing. Tests written for [PHPUnit](https://phpunit.readthedocs.io/en/9.5/writing-tests-for-phpunit.html) can be run with
  ```shell
  lando phpunit
  ```
or
  ```shell
  php ./vendor/bin/phpunit
  ```

Testing utilizes the [Orchestral Testbench](https://github.com/orchestral/testbench), which creates a basic Laravel install at `vendor/orchestra/testbench-core/laravel` and runs tests in that environment.

Configuration for PHPUnit is in `.phpunit.xml`.


## Development Workflow
Work should be reviewed and approved by at least one other person via a GitHub pull request before being merged to the main branch.

### Pull request creation
There is a [pull request template](.github/pull_request_template.md) to help prompt for useful content. In addition to what is in the template, please link to any relevant content (like GitHub issues or Slack conversations) or provide context that may help inform the review process.

Pull requests are constrained in GitHub with the following automations:
- Work is only merged to the main branch via a PR
- Unit test and linting checks are run on PR code and must pass 
- PRs need to be approved by at least one other person

### Pull request review
Review feedback can generally be oriented as:

Must Fix Issues
- Bugs
- Potential maintenance issues
- Coding standard issues
- Performance issues
- Not meeting the understood need that the PR is addressing

Recommendations
- Optimization issues
- Architectural changes
- Style issues

When adding a recommendation (rather than a "must fix"), make sure the changes are worth the time and support development goals ([see README](README.md)).

When acting upon recommendations, presume that the review comment should be implemented unless there is a reason not to.

### Release management
Since the Laravel Starter Kit is a composer-delivered package, it should have tagged releases that use semantic versioning to identify the nature of releases. The Laravel Package Development site has a good description of [how to publish a release with GitHub](https://laravelpackage.com/15-publishing.html#releasing-v1-0-0) and release version numbering.

Guidelines for releases of the Laravel Starter Kit:
- Major releases are reserved for fundamental changes to the architecture, including backwards-incompatible changes. There should be consensus on a major release being required.
- Minor releases are bundled sets of non-breaking changes and there should be a consensus approval for the set of changes being release-ready.
- Patch-level releases address bugs, minor dependency updates, or trivial changes and a pull-request review process is sufficient for approving as a release.
- When [publishing a release in GitHub](https://docs.github.com/en/repositories/releasing-projects-on-github/about-releases), provide a good summary of the work that is bundled in the release. Note: GitHub provides a mechanism for [automatically summarizing the content of a release](https://docs.github.com/en/repositories/releasing-projects-on-github/automatically-generated-release-notes).
