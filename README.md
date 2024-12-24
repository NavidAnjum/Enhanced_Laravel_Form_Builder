Enhanced Laravel Form Builder Package
This is a modified version of the original Laravel Form Builder Package for creating drag-and-drop forms using the JQuery Form Builder. This enhanced version includes features for dynamically generating models, migrations, and managing form-associated tables.
You can install the original one from here : composer require doode/laravel-formbuilder

Key Features
Dynamic Model and Migration Creation:

When a user creates a new form, a corresponding Eloquent model and database table are dynamically generated.
Models include protected $fillable fields based on the form's structure.
Full Data Management:

Users can add, update, and delete records in the dynamically created tables.
Data is stored in the associated database table with full CRUD functionality.
Form Updates:

Updating a form synchronizes changes with the associated database table, including column renaming and schema updates, while preserving data integrity.
Enhanced Compatibility:

Improved integration with Laravel's MVC architecture for scalable and dynamic data management.
Example Workflow
Form Creation:

A user creates a new form using the drag-and-drop builder.
Example JSON representation:
json
Copy code
[{"type":"text","label":"Name","className":"form-control","name":"name"}]
The system generates:
A new table: forms
A model: Form
Form Update:

When a user updates a form:
The database table schema is updated to reflect changes (e.g., renaming or adding columns).
Data is preserved during schema changes.
Form Submission:

Form submissions are saved to the dynamically created tables.
Example database table for the form:
sql
Copy code
CREATE TABLE forms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
Requirements
Laravel 7+
Bootstrap 4+
MySQL/MariaDB
Laravel Authentication
bash
Copy code
composer require laravel/ui
php artisan ui bootstrap --auth
Installation
Step 1: Install the Package
bash
Copy code
composer require doode/enhanced-laravel-form-builder
Step 2: Run Migrations
bash
Copy code
php artisan migrate
Step 3: Publish Assets and Configurations
bash
Copy code
php artisan vendor:publish --provider="doode\FormBuilder\FormBuilderServiceProvider" --tag=formbuilder-views
php artisan vendor:publish --tag=formbuilder-public
php artisan vendor:publish --tag=formbuilder-config
Step 4: Configure Blade Templates
Update your blade layout to include:

php
Copy code
@stack('styles')  <!-- Include styles -->
@stack('scripts') <!-- Include scripts -->
Using the Package
Creating a New Form
Navigate to the form builder.
Design a new form using the drag-and-drop interface.
The system creates:
A database table to store submissions.
An Eloquent model for interacting with the table.
Updating a Form
Update form fields (e.g., rename columns or add new ones).
Changes are synchronized with the database table while preserving existing data.
Managing Data
View, edit, and delete records stored in the dynamically created tables.
Precautions
Ensure the database contains a users table with a column id (BIGINT UNSIGNED).
Avoid editing forms with existing submissions unless necessary, as it may impact older submissions.
Dependencies
jQuery UI: For user interface actions.
jQuery Formbuilder: Drag-and-drop form editing.
clipboard.js: For copying text to clipboard.
parsley.js: Form validation.
moment.js: Date and time handling.
footable: Responsive table plugin.
sweetalert: Enhanced alert messages.
Roadmap
Add advanced field types (e.g., file uploads).
Improve UI/UX for managing forms and submissions.
Expand form permissions for finer control.
License
This package is open-sourced software licensed under the MIT license.

