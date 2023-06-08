<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Managers\FileManager;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    const UPLOAD_COUNT_LIMIT = 5;
    const ACCEPTABLE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    public function uploadImages(Request $request)
    {
        $fileKey = 'images';
        $countLimit = self::UPLOAD_COUNT_LIMIT;
        // 10 MB
        $sizeLimit = 10 * 1024 * 1024;

        // Check input file exist
        if (!$request->hasFile($fileKey)) {
            abort(422, 'Input images are invalid');
        }

        $files = collect($request->file($fileKey));

        // check the delivery of file is successful
        if (!$files->every(fn ($file) => $file->isValid())) {
            abort(400, 'Upload failed');
        }
        // Check file extension
        if (!$files->every(fn ($file) => collect(self::ACCEPTABLE_EXTENSIONS)->contains($file->extension()))) {
            abort(422, 'Invalid file extension');
        }
        // Check the limit of file size
        if (!$files->every(fn ($file) => $file->getSize() <= $sizeLimit)) {
            abort(422, 'File size is too large');
        }
        // Check the limit of count
        if ($files->count() > $countLimit) {
            abort(422, "Too many files, limit: $countLimit");
        }

        $outputImages = $files->map(function ($inputImage) {
            $path = $inputImage->store('images');

            return FileManager::getUrl($path);
        });

        return response()->json(['images' => $outputImages->toArray()]);
    }
}
