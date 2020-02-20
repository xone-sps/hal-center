<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use RachidLaasri\LaravelInstaller\Helpers\EnvironmentManager;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use RachidLaasri\LaravelInstaller\Events\EnvironmentSaved;


class EnvironmentController extends Controller
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
    public function environmentMenu()
    {
        return view('vendor.installer.environment');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentWizard()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('vendor.installer.environment-wizard', compact('envConfig'));
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
        $this->EnvironmentManager->saveFileClassic($input);

        return $redirect->route('LaravelInstaller::InstallNow');
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
        //$rules = config('installer.environment.form.rules');
        $request->flash();

        $messages = [
            'environment_custom.required_if' => trans('installer_messages.environment.wizard.form.name_required'),
        ];

        $validator = $this->validate($request,[
//            'database_connection' => 'required',
            'database_hostname' => 'required',
            'database_port' => 'required',
            'database_name' => 'required',
            'database_username' => 'required',
            'database_password' => 'required',
        ]);



        if ($this->checkDatabaseConnection($request)===true) {
            if (\Schema::hasTable('settings'))
            {
                return $redirect->back()->with('error', 'Your selected database is not empty. Please use a new database.');
            }

            $results = $this->EnvironmentManager->saveFileWizard($request);
            return $redirect->route('LaravelInstaller::database')->with(['results' => $results]);

        }

        return $redirect->back()->with('error', 'Database connection failed');

    }

    private function checkDatabaseConnection($request)
    {
//        $connection = $request->input('database_connection');
        $connection = "mysql";
        $settings = config("database.connections.$connection");

        config([
            'database' => [
                'default' => $connection,
                'connections' => [
                    $connection => array_merge($settings, [
                        'driver' => $connection,
                        'host' => $request->input('database_hostname'),
                        'port' => $request->input('database_port'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);

        $val = '';

        try {
            DB::connection()->getPdo();
            $val =  true;
        } catch (\Exception $e) {
            $val = false;
        }

        return $val;
    }

    public function InstallNow()
    {
        return view('vendor.installer.install');
    }
}

