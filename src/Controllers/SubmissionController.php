<?php

namespace doode\FormBuilder\Controllers;

use App\Http\Controllers\Controller;
use doode\FormBuilder\Helper;
use doode\FormBuilder\Models\Form;
use doode\FormBuilder\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubmissionController extends Controller
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
     * @param integer $form_id
     * @return \Illuminate\Http\Response
     */
    public function index($form_id)
    {
        $user = auth()->user();

        $form = Form::where(['user_id' => $user->id, 'id' => $form_id])
                    ->with(['user'])
                    ->firstOrFail();
              $table = $form->name;
            
            // Dynamically generate the model class
            $modelClass = "App\\" . ucfirst(Str::camel($table));
            Log::info('Model class being used:', ['model_class' => $modelClass]);
    
            if (!class_exists($modelClass)) {
                throw new \Exception("Model class {$modelClass} does not exist.");
            }
    
            $model = new $modelClass();
            Log::info('Model instantiated successfully', ['model_class' => $modelClass]);
    //
        $submissions = $model->latest()->paginate(100);
     
        $form_headers = $form->getEntriesHeader();

        $pageTitle = "Submitted Entries for '{$table}'";

        return view(
            'formbuilder::submissions.index',
            compact('form', 'submissions', 'pageTitle', 'form_headers')
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $form_id
     * @param integer $submission_id
     * @return \Illuminate\Http\Response
     */
    public function show($form_id, $submission_id)
    {

        $user = auth()->user();

        $form = Form::where(['user_id' => $user->id, 'id' => $form_id])
                    ->with(['user'])
                    ->firstOrFail();
              $table = $form->name;
            
            // Dynamically generate the model class
            $modelClass = "App\\" . ucfirst(Str::camel($table));
            Log::info('Model class being used:', ['model_class' => $modelClass]);
    
            if (!class_exists($modelClass)) {
                throw new \Exception("Model class {$modelClass} does not exist.");
            }
    
            $model = new $modelClass();
            Log::info('Model instantiated successfully', ['model_class' => $modelClass]);
            
        $submission = $model::where([
                                'id' => $submission_id,
                            ])
                            ->firstOrFail();

        $form_headers = $form->getEntriesHeader();
        $form = [
            'id' => $form_id,
            'name' => $table,
        ];
        $pageTitle = "View Submission";

        return view('formbuilder::submissions.show', compact('form', 'submission', 'form_headers', 'pageTitle'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $form_id
     * @param int $submission_id
     * @return \Illuminate\Http\Response
     */
    public function destroy($form_id, $submission_id)
    {
        $user = auth()->user();

        $form = Form::where(['user_id' => $user->id, 'id' => $form_id])
        ->with(['user'])
        ->firstOrFail();
        $table = $form->name;

        // Dynamically generate the model class
        $modelClass = "App\\" . ucfirst(Str::camel($table));
        Log::info('Model class being used:', ['model_class' => $modelClass]);

        if (!class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} does not exist.");
        }

        $model = new $modelClass();
        Log::info('Model instantiated successfully', ['model_class' => $modelClass]);
        //

        $submission = $model::where(['id' => $submission_id])->firstOrFail();
        $submission->delete();

        return redirect()
                    ->route('formbuilder::forms.submissions.index', $form_id)
                    ->with('success', 'Submission successfully deleted.');
    }
}
