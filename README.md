# Enhanced Laravel Form Builder Package

This repository contains an enhanced version of the original Laravel Form Builder Package, designed to create drag-and-drop forms using JQuery Form Builder. It features dynamic generation of models, migrations, and management of form-associated tables.

## Installation

You can install the original package using the following command:
```bash
composer require doode/laravel-formbuilder
```
To install this enhanced version, follow the steps below:

### Requirements
- Laravel 7
- Bootstrap 4+
- MySQL/MariaDB
- Laravel Authentication

Install Laravel UI for authentication:
```bash
composer require laravel/ui
php artisan ui bootstrap --auth
```

### Step 1: Install the Package
```bash
composer require navidanjum/enhanced_laravel_form_builder:dev-main
```

#### Add the Repository to composer.json (Optional)
If users face issues installing directly, they can manually add your GitHub repository to their composer.json file:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/NavidAnjum/Enhanced_Laravel_Form_Builder.git"
    }
]
```
Then run:
```bash
composer require navidanjum/enhanced_laravel_form_builder:dev-main
```

### Step 2: Run Migrations
```bash
php artisan migrate
```

### Step 3: Publish Assets and Configurations
```bash
php artisan vendor:publish --provider="NavidAnjum\EnhancedFormBuilder\EnhancedFormBuilderServiceProvider" --tag=formbuilder-views
php artisan vendor:publish --tag=formbuilder-public
php artisan vendor:publish --tag=formbuilder-config
```

### Step 4: Configure Blade Templates
Update your Blade layout to include the following:
```php
@stack('styles')  <!-- Include styles -->
@stack('scripts') <!-- Include scripts -->
```

## Key Features

### Dynamic Model and Migration Creation
- Automatically generates an Eloquent model and database table when a user creates a new form.
- Models include `protected $fillable` fields based on the form structure.

### Full Data Management
- Provides full CRUD functionality for dynamically created tables.
- Users can add, update, and delete records in these tables.

### Form Updates
- Synchronizes updates to forms with associated database tables, including column renaming and schema updates.
- Ensures data integrity during schema changes.

### Enhanced Compatibility
- Seamless integration with Laravel's MVC architecture for scalable and dynamic data management.

## Example Workflow

### Form Creation
- Users design a new form using the drag-and-drop builder.
- Example JSON representation of a form:
  ```json
  [
    {"type":"text","label":"Name","className":"form-control","name":"name"}
  ]
  ```
- The system generates:
  - A new table: `forms`
  - A model: `Form`

### Form Update
- Updating a form reflects changes in the database table schema.
- Data is preserved during schema updates.

### Form Submission
- Submissions are saved to dynamically created tables.
- Example table structure:
  ```sql
  CREATE TABLE forms (
      id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(255) NULL,
      created_at TIMESTAMP NULL,
      updated_at TIMESTAMP NULL
  );
  ```

## Managing Data
- View, edit, and delete records stored in dynamically created tables.

## Dependencies
- **jQuery UI**: For user interface actions.
- **jQuery Formbuilder**: Drag-and-drop form editing.
- **clipboard.js**: Copy text to clipboard.
- **parsley.js**: Form validation.
- **moment.js**: Date and time handling.
- **footable**: Responsive table plugin.
- **sweetalert**: Enhanced alert messages.

## Roadmap
- Add advanced field types (e.g., file uploads).
- Improve UI/UX for managing forms and submissions.
- Expand form permissions for finer control.

## Precautions
- Ensure the database contains a `users` table with a column `id` (`BIGINT UNSIGNED`).
- Avoid editing forms with existing submissions unless necessary, as it may affect older submissions.

## License
This package is open-sourced software licensed under the [MIT license](LICENSE).
