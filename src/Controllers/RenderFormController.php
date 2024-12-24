<?php

namespace doode\FormBuilder\Controllers;

use App\Http\Controllers\Controller;
use doode\FormBuilder\Helper;
use doode\FormBuilder\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Throwable;

class RenderFormController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('public-form-access');
    }

    /**
     * Render the form so a user can fill it
     *
     * @param string $identifier
     * @return Response
     */
    public function render($identifier)
    {
        $form = Form::where('identifier', $identifier)->firstOrFail();

        $pageTitle = "{$form->name}";

        return view('formbuilder::render.index', compact('form', 'pageTitle'));
    }

    /**
     * Process the form submission
     *
     * @param Request $request
     * @param string $identifier
     * @return Response
     */
    public function submit(Request $request, $identifier)
    {
        Log::info('Starting submit function', ['identifier' => $identifier]);
    
        try {
            // Retrieve the form using the identifier
            $form = Form::where('identifier', $identifier)->firstOrFail();
            Log::info('Form retrieved successfully', ['form_name' => $form->name]);
    
            // Generate the table name (PascalCase format)
            $table = ucfirst(rtrim($form->name));
            Log::info('Table name generated', ['table' => $table]);
    
            DB::beginTransaction();
    
            $input = $request->except('_token');
            Log::info('Request input received', $input);
    
            // Dynamically generate the model class
            $modelClass = "App\\" . ucfirst(Str::camel($table));
            Log::info('Model class being used:', ['model_class' => $modelClass]);
    
            if (!class_exists($modelClass)) {
                throw new \Exception("Model class {$modelClass} does not exist.");
            }
    
            $model = new $modelClass();
            Log::info('Model instantiated successfully', ['model_class' => $modelClass]);
    
            // Assign input values to the model
            foreach ($input as $key => $value) {
                $model->$key = $value;
            }
    
            // Save the model
            $model->save();
            DB::commit();
            Log::info('Data saved successfully to table', ['table' => $table]);
    
            // Redirect with success
            return redirect()
                ->route('formbuilder::form.feedback', $identifier)
                ->with('success', 'Form successfully submitted.');
        } catch (\Throwable $e) {
            // Log the error
            Log::error('Error occurred during form submission', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
    
            DB::rollback();
    
            // Redirect back with an error message
            return back()
                ->withInput()
                ->with('error', Helper::wtf());
        }
    }
    

    /**
     * Display a feedback page
     *
     * @param string $identifier
     * @return Response
     */
    public function feedback($identifier)
    {
        $form = Form::where('identifier', $identifier)->firstOrFail();

        $pageTitle = "Form Submitted!";

        return view('formbuilder::render.feedback', compact('form', 'pageTitle'));
    }
}
