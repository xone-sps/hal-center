<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Psr\Cache\InvalidArgumentException;
use Spatie\Browsershot\Browsershot;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class FCMPrintServiceController extends Controller
{
    private $factory;


    public $image_path = "/socket-template-image/";

    /**
     * FCMPrintServiceController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        try {
            $cache = new FilesystemAdapter();
            $serviceJsonFile = $cache->getItem('service_json_file');
            $this->factory = (new Factory)
                ->withServiceAccount(storage_path(config('services.firebase-account.service-account')))
                ->withVerifierCache($serviceJsonFile);
        } catch (InvalidArgumentException $e) {
            dd($e);
        }
    }

    public function sendNotification($data)
    {
        $topic = "printing-service";
        $messaging = $this->factory->createMessaging();
        $message = CloudMessage::withTarget('topic', $topic)
            ->withData($data);

        try {
            $messaging->send($message);
        } catch (MessagingException $e) {
        } catch (FirebaseException $e) {
        }
    }

    public function storeImageTemplate(Request $request)
    {
        $user = $request->user('web');
        $filename = $this->createFileName($user);
        $image_path = public_path($this->image_path . $filename);
        if ($request->exists('html')) {
            $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $request->get('html'));
            $html = view('socket-service.invoice-print-template', compact('html'));
            $html = $html->render();
        } else {
            $html = $this->defaultHtmlTemplate();
        }

        try {

            Browsershot::html($html)
                ->userAgent('Mozilla/5.0 (Linux; Android 9; Redmi Note 8 Pro) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.99 Mobile Safari/537.36')
                ->windowSize(375, 812)
                ->deviceScaleFactor(3)
                ->touch()
                ->mobile()
                ->landscape(false)
                ->fullPage()
                ->disableJavascript()
                ->save($image_path);

            $data = [
                'id' => $this->createRandomId($user),
                'filename' => $filename,
                'url' => url($this->image_path . $filename),
                'content' => base64_encode(file_get_contents($image_path)),
            ];

            // send notification to client
            $this->sendNotification($data);

            unset($data["content"]);
            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (CouldNotTakeBrowsershot $ignored) {

        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to make a snapshot',
            'data' => ''
        ]);
    }

    public function notifyForTesting(Request $request)
    {
        $user = $request->user('web');
        $filename = "test-image.png";
        $image_path = public_path($this->image_path . $filename);
        $data = [
            'id' => $this->createRandomId($user),
            'filename' => $filename,
            'url' => url($this->image_path . $filename),
            'content' => base64_encode(file_get_contents($image_path)),
        ];

        // send notification to client
        $this->sendNotification($data);

        unset($data["content"]);
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function defaultHtmlTemplate()
    {
        return "<!doctype html>
                <html lang=\"en\">
                <head>
                    <meta charset=\"utf-8\">
                    <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
                    <title>Invoice Print</title>
                    <style type=\"text/css\">
                        body {
                            margin: 0;
                            padding: 0;
                            font-family: Roboto, BoonHome, serif, sans-serif !important;
                        }
                    </style>
                </head>
                <body>
                <div>
                </div>
            </body>
        </html>";
    }

    public function createFileName($user)
    {
        $random = uniqid('template-print') . Str::random();
        return "{$user->id}_template_" . $random . ".png";
    }

    public function createRandomId($user)
    {
        $random = uniqid('invoice-print-id') . Str::random();
        return "{$user->id}" . $random;
    }
}
