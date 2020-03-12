<?php

namespace App\Http\Controllers\API;

use App\Events\PrintServiceMessagePushed;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use  Spatie\Browsershot\Browsershot;

class SocketPrintServiceController extends Controller
{
    public $image_path = "/socket-template-image/";

    public function storeImageTemplate(Request $request)
    {
        $user = $request->user('web');
        $filename = $this->createFileName($user);
        if ($request->exists('html')) {
            $html = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $request->get('html'));
            $html = view('invoice-print-template', compact('html'));
            $html = $html->render();
        } else {
            $html = $this->defaultHtmlTemplate();
        }

        Browsershot::html($html)
            ->userAgent('Mozilla/5.0 (Linux; Android 9; Redmi Note 8 Pro) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.99 Mobile Safari/537.36')
            ->windowSize(375, 812)
            ->deviceScaleFactor(3)
            ->touch()
            ->mobile()
            ->landscape(false)
            ->fullPage()
            ->hideBackground()
            ->save(public_path($this->image_path . $filename));

        event(new PrintServiceMessagePushed($user, $filename));

        return response()->json([
            'success' => true,
            'data' => [
                'filename' => $filename,
                'url' => url($this->image_path . $filename),
                'content' => $html
            ]
        ]);
    }

    public function deleteTemplateImage(Request $request)
    {
        try {
            unlink(public_path($this->image_path . $request->filename));
            return response()->json([
                'success' => true,
                'message' => 'The image was deleted.'
            ]);
        } catch (\Exception $exception) {

        }
        return response()->json([
            'success' => false,
            'message' => 'The image cannot delete.'
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
}
