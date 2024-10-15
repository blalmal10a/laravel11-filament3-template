<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Image $image)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Image $image)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Image $image)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        //
    }

    //


    public function getMedia($id = null)
    {
        if (!$id) {
            $id = 'UlZM1Tz.png';
        }

        $imageHost = rtrim(env('IMAGE_HOST'), '/') . '/';
        $mediaUrl = $imageHost . $id;

        if (!$this->urlExists($mediaUrl)) {
            abort(404, 'Media not found');
        }

        $mimeType = $this->getMimeType($mediaUrl);
        $fileSize = $this->getFileSize($mediaUrl);

        // For small files, we can still use file_get_contents
        if ($fileSize !== null && $fileSize < 5 * 1024 * 1024) {
            $mediaData = file_get_contents($mediaUrl);
            return response($mediaData, 200)->header('Content-Type', $mimeType);
        }

        // For large files or when size is unknown, we'll use streaming
        return $this->streamMedia($mediaUrl, $mimeType);
    }

    private function getMimeType($url)
    {
        $response = Http::head($url);
        return $response->header('Content-Type') ?? 'application/octet-stream';
    }

    private function getFileSize($url)
    {
        $response = Http::head($url);
        return $response->header('Content-Length');
    }

    private function urlExists($url)
    {
        $response = Http::head($url);
        return $response->successful();
    }

    private function streamMedia($url, $mimeType)
    {
        return new StreamedResponse(function () use ($url) {
            $stream = fopen($url, 'rb');
            while (!feof($stream)) {
                echo fread($stream, 8192);
                flush();
            }
            fclose($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'no-cache',
        ]);
    }
}
