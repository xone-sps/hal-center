<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Session;
use RachidLaasri\LaravelInstaller\Helpers\EnvironmentManager;
use Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * @var EnvironmentManager
     */
    protected $EnvironmentManager;

    /**
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->EnvironmentManager = $environmentManager;
    }

    /**
     * Display the Environment menu page.
     *
     * @return \Illuminate\View\View
     */
    public function userMenu()
    {
        return view('vendor.installer.user');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function userSaveWizard(Request $request, Redirector $redirect)
    {
        $this->validate($request,[
            "first_name" => "required",
            "last_name" => "required",
            "email" => "required",
            "password" => "required",
        ]);


        $first_name = $request->first_name;
        $last_name = $request->last_name;
        $email = $request->email;
        $password = $request->password;

        session()->put('first_name', $first_name);
        session()->put('last_name', $last_name);
        session()->put('email', $email);
        session()->put('password', $password);
        //$data=$request->input();

        //return $data;

        return $redirect->route('LaravelInstaller::environmentWizard');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentClassic()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('vendor.installer.environment-classic', compact('envConfig'));
    }

    /**
     * Processes the newly saved environment configuration (Classic).
     *
     * @param Request $input
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveClassic(Request $input, Redirector $redirect)
    {
        $message = $this->EnvironmentManager->saveFileClassic($input);

        return $redirect->route('LaravelInstaller::environmentClassic')
                        ->with(['message' => $message]);
    }

    /**
     * Processes the newly saved environment configuration (Form Wizard).
     *
     * @param Request $request
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveWizard(Request $request, Redirector $redirect)
    {
        $rules = config('installer.environment.form.rules');
        $messages = [
            'environment_custom.required_if' => trans('installer_messages.environment.wizard.form.name_required'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return view('vendor.installer.environment-wizard', compact('errors', 'envConfig'));
        }

        $results = $this->EnvironmentManager->saveFileWizard($request);

        return $redirect->route('LaravelInstaller::database')
                        ->with(['results' => $results]);
    }
}
