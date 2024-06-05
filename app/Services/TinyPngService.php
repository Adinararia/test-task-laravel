<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TinyPngService
{
    private ?string $apiKey;
    private ?string $domain;
    private ?string $username;


    public function __construct()
    {
        $this->apiKey   = config('tiny_png.api_key');
        $this->domain   = config('tiny_png.domain');
        $this->username = config('tiny_png.username');
    }


    /**
     * @param $file
     * @param int $width
     * @param int $height
     * @return string|null
     */
    public function optimizeImage($file, int $width = 70, int $height = 70): ?string
    {
        $return              = null;
        $uploadFileToTinyPng = $this->shrinkFile($file);
        if ($uploadFileToTinyPng) {
            $partsUrl = explode('/', $uploadFileToTinyPng->output->url);
            $filename = last($partsUrl);
            $return   = $this->coverImage($uploadFileToTinyPng->output->url, $filename, $width, $height);
        }
        //        $url = 'https://api.tinify.com/output/hf25ze5b45e4xmd41k7kd1qt8xq9zanm';
        //        $return = $this->coverImage($url, $width, $height);

        return $return;
    }

    /**
     * @param string $fileUrl
     * @param string $filename
     * @param int $width
     * @param int $height
     * @return string|null
     */
    private function coverImage(string $fileUrl, string $filename, int $width = 70, int $height = 70): ?string
    {
        $result   = false;
        $response = $this->prepareHttpRequest()
            ->post($fileUrl, [
                "resize" => [
                    "method" => 'fit',
                    "width"  => 70,
                    "height" => 70,
                ],
            ]);
        if ($response->successful()) {
            $filePath = 'images/' . $filename . '.jpg';
            $result   = Storage::disk('public')->put($filePath, $response->body());
        }
        return $result ? $filePath : null;
    }

    /**
     * @param $file
     * @return object|null
     */
    private function shrinkFile($file): ?object
    {
        $response = $this->prepareHttpRequest()
            ->withBody(file_get_contents($file), $file->getMimeType())
            ->post('{+domain}/shrink');
        if ($response->successful()) {
            $return = $response->object();
        } else {
            $return = null;
        }
        return $return;
    }

    /**
     * @return PendingRequest
     */
    private function prepareHttpRequest(): PendingRequest
    {
        return Http::withUrlParameters([
            'domain' => $this->domain,
        ])->withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($this->username . ':' . $this->apiKey),
        ]);
    }
}
