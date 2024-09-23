# WA Reviews

A web app for completing and managing Cornell Web Accessibility reviews.

## Local Installation

```bash
git clone https://github.com/CornellCustomDev/wa-reviews.git
cd wa-reviews
# if using lando
lando start 
lando ssh
# set up the db
php artisan migrate:fresh
php artisan db:seed CategorySeeder
php artisan db:seed GuidelineSeeder
php artisan app:split-guidelines
```
