<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Defuse\Crypto\File;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Config;
use RachidLaasri\LaravelInstaller\Helpers\DatabaseManager;

class DatabaseController extends Controller
{
    /**
     * @var DatabaseManager
     */
    private $databaseManager;

    /**
     * @param DatabaseManager $databaseManager
     */
    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    /**
     * Migrate and seed the database.
     *
     * @return \Illuminate\View\View
     */
    public function database()
    {
        $response = $this->databaseManager->migrateAndSeed();

        $success = \File::move(storage_path('index.php'),base_path('index.php'));
        $success = \File::move(storage_path('gain.php'),base_path('config/gain.php'));
        //unlink($success);

        return redirect()->route('LaravelInstaller::final')
                         ->with(['message' => $response]);
    }
}
