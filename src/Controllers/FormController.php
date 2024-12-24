<?php

namespace doode\FormBuilder\Controllers;

use App\Http\Controllers\Controller;
use doode\FormBuilder\Events\Form\FormCreated;
use doode\FormBuilder\Events\Form\FormDeleted;
use doode\FormBuilder\Events\Form\FormUpdated;
use doode\FormBuilder\Helper;
use doode\FormBuilder\Models\Form;
use doode\FormBuilder\Requests\SaveFormRequest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Driver\PDOMySql\Driver;

use Throwable;

class FormController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = "Forms";

        $forms = Form::getForUser(auth()->user());

        return view('formbuilder::forms.index', compact('pageTitle', 'forms'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = "Create New Form";

        $saveURL = route('formbuilder::forms.store');

        // get the roles to use to populate the make the 'Access' section of the form builder work
        $form_roles = Helper::getConfiguredRoles();

        return view('formbuilder::forms.create', compact('pageTitle', 'saveURL', 'form_roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  doode\FormBuilder\Requests\SaveFormRequest $request
     * @return \Illuminate\Http\Response
     */

    public function store(SaveFormRequest $request)
    {
        $user = $request->user();
        $input = $request->merge(['user_id' => $user->id])->except('_token');

        Log::info('Incoming Request Data:', $request->all());

       
        DB::beginTransaction();
        $tableName = Str::snake($request->get('name'))."s";
        Log::info('Name of table:', [$tableName]);

        // Generate a random identifier
        $input['identifier'] = $tableName;
        $created = Form::create($input);

        try {
            // Dispatch the event
            event(new FormCreated($created));
            // Get field names from the form definition
            $fields = json_decode($created->form_builder_json, true);
            $fieldNames = $this->extractFieldNames($fields);

            // Create migration for the table
            $this->createMigration($tableName, $fieldNames);

            // Create model for the table
            $this->createModel($tableName, $fieldNames);

            // Run the migration
            Artisan::call('migrate');

            DB::commit();

            return response()->json([
                'success' => true,
                'details' => 'Form and table successfully created!',
                'dest' => route('formbuilder::forms.index'),
            ]);
        }
        catch (Throwable $e) {
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            DB::rollback();
        
            return response()->json(['success' => false, 'details' => 'Failed to create the form and table.']);
        }
    }

    /**
     * Extract field names from form JSON.
     */
    private function extractFieldNames($fields)
    {
        $fieldNames = [];
        foreach ($fields as $field) {
            if (isset($field['name'])) {
                $fieldNames[] = $field['name'];
            }
        }
        return $fieldNames;
    }

    private function createModel($tableName, $fieldNames)
    {
        // Generate the model name (singular, PascalCase)
        $modelName = ucfirst(Str::singular(Str::camel($tableName)));
        $modelPath = app_path("{$modelName}.php");
        Log::info('Name of table:', [$modelPath]);

        if (!File::exists($modelPath)) {
            // Convert field names into a PHP array format for the $fillable property
            $fillableArray = implode(", ", array_map(fn($field) => "'{$field}'", $fieldNames));
    
            // Create the model template with dynamic fillable fields
            $modelTemplate = <<<EOD
    <?php
    
    namespace App;
    
    use Illuminate\Database\Eloquent\Model;
    
    class {$modelName} extends Model
    {
        protected \$table = '{$tableName}';
    
        protected \$fillable = [{$fillableArray}];
    }
    EOD;
    
            // Write the model file
            File::put($modelPath, $modelTemplate);
        }
    }

    private function createMigration($tableName, $fieldNames)
{
    // Generate the class name and file name for the migration
    $className = 'Create' . ucfirst(Str::camel($tableName)) . 'Table';
    $migrationName = 'create_' . $tableName . '_table';
    $filePath = database_path('migrations/' . date('Y_m_d_His') . "_{$migrationName}.php");

    // Prepare fields for the migration
    $fieldsCode = '';
    foreach ($fieldNames as $field) {
        $fieldsCode .= "\$table->string('$field')->nullable();\n";
    }

    // Create the migration template
    $migrationTemplate = <<<EOD
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class {$className} extends Migration
{
    public function up()
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();
            {$fieldsCode}
            \$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('{$tableName}');
    }
}
EOD;

    // Write the migration file
    if (!File::put($filePath, $migrationTemplate)) {
        throw new \Exception("Failed to create migration file: {$filePath}");
    }
}




    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = auth()->user();
        $form = Form::where(['user_id' => $user->id, 'id' => $id])
                    ->with('user')
                    ->withCount('submissions')
                    ->firstOrFail();
        Log::info('Data:', $form->toArray()); // If $form is an Eloquent model

        $pageTitle = "Preview Form";

        return view('formbuilder::forms.show', compact('pageTitle', 'form'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = auth()->user();

        $form = Form::where(['user_id' => $user->id, 'id' => $id])->firstOrFail();

        $pageTitle = 'Edit Form';

        $saveURL = route('formbuilder::forms.update', $form);

        // get the roles to use to populate the make the 'Access' section of the form builder work
        $form_roles = Helper::getConfiguredRoles();

        return view('formbuilder::forms.edit', compact('form', 'pageTitle', 'saveURL', 'form_roles'));
    }

      /**
     * Extract field names from form JSON.
     */
    private function extractUpdatedFieldNames($fields)
    {
        $fieldNames = [];
        foreach ($fields as $field) {
            if (isset($field['name'])) {
                $fieldNames[] = $field['name'];
            }
        }
        
        return $fieldNames;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  doode\FormBuilder\Requests\SaveFormRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    private function updateModel($tableName, $fieldNames)
    {
        // Generate the model name (singular, PascalCase)
        $modelName = ucfirst(Str::singular(Str::camel($tableName)));
        $modelPath = app_path("{$modelName}.php");

        // Convert field names into a PHP array format for the $fillable property
        $fillableArray = implode(", ", array_map(fn($field) => "'{$field}'", $fieldNames));

        // Update or overwrite the model template with the updated fillable fields
        $modelTemplate = <<<EOD
        <?php

        namespace App;

        use Illuminate\Database\Eloquent\Model;

        class {$modelName} extends Model
        {
            protected \$table = '{$tableName}';

            protected \$fillable = [{$fillableArray}];
        }
        EOD;

            // Write the model file
            File::put($modelPath, $modelTemplate);
        }

    public function update(SaveFormRequest $request, $id)
        {

            $form = Form::findOrFail($id);

            $user = $request->user();

            $input = $request->merge(['user_id' => $user->id])->except('_token');

            $oldFields = json_decode($form->form_builder_json, true); // Decode old JSON
            $newFields = json_decode($request->get('form_builder_json'), true); // Decode new JSON

            // Log the JSON data
            Log::info('Old JSON:', $oldFields);
            Log::info('New JSON:', $newFields);

            // For debugging: Return the user object

            DB::beginTransaction();

            $tableName = $form->identifier; // Use the existing table name from the 'identifier' column

            try {
                // Update the form data in the forms table
            $form->update($input);

                // Dispatch the event
            //   event(new FormUpdated($form));

                // Compare old and new form_builder_json
        
                // Update the database schema for the table matching the form identifier
                $this->updateMigration($tableName, $oldFields, $newFields);

                // Update the model file
                $this->updateModel($tableName, array_column($newFields, 'name'));

                // Run migrations to ensure schema is updated
                Artisan::call('migrate');

                DB::commit();

                return response()->json([
                    'success' => true,
                    'details' => 'Form and table successfully updated!',
                    'dest' => route('formbuilder::forms.edit', $id),
                ]);
            } catch (Throwable $e) {
                Log::error($e->getMessage());
                Log::error($e->getTraceAsString());
                DB::rollback();

                return response()->json(['success' => false, 'details' => 'Failed to update the form and table.']);
            }
        }
        private function updateMigration($tableName, $oldFields, $newFields)
        {
            // Parse old and new field names
            $oldFieldNames = array_column($oldFields, 'name'); // Old column names
            $newFieldNames = array_column($newFields, 'name'); // New column names

            // Exclude columns that should not be modified
            $excludedColumns = ['id', 'created_at', 'updated_at'];

            // Filter out excluded columns from old and new fields
            $oldFieldNames = array_diff($oldFieldNames, $excludedColumns);
            $newFieldNames = array_diff($newFieldNames, $excludedColumns);

            Schema::table($tableName, function (Blueprint $table) use ($oldFieldNames, $newFieldNames) {
                // Rename columns sequentially
                foreach ($oldFieldNames as $index => $oldName) {
                    if (isset($newFieldNames[$index]) && $oldName !== $newFieldNames[$index]) {
                        $table->renameColumn($oldName, $newFieldNames[$index]);
                        Log::info("Renamed column '{$oldName}' to '{$newFieldNames[$index]}' in table '{$table->getTable()}'");
                    }
                }

                // Add any new columns that are in the new fields but not in the old
                foreach (array_slice($newFieldNames, count($oldFieldNames)) as $newColumn) {
                    $table->string($newColumn)->nullable();
                    Log::info("Added new column '{$newColumn}' in table '{$table->getTable()}'");
                }
            });
        }

    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = auth()->user();
        $form = Form::where(['user_id' => $user->id, 'id' => $id])->firstOrFail();
        $form->delete();

        // dispatch the event
        event(new FormDeleted($form));

        return back()->with('success', "'{$form->name}' deleted.");
    }
}
