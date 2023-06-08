<?php

namespace App\Managers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileManager
{
    public static function getUrl(string $path): string
    {
        return Storage::url($path);
    }

    public static function convertToUTF8(UploadedFile $file): UploadedFile
    {
        $encodingCheckTypes = ['BIG5', 'UTF-8', 'ASCII'];
        $originalEncoding = mb_detect_encoding($file->getContent(), $encodingCheckTypes);

        if (!empty($originalEncoding)) {
            $content = mb_convert_encoding($file->getContent(), 'utf-8', $originalEncoding);

            return UploadedFile::fake()->createWithContent('utf8.csv', $content);
        }

        return $file;
    }
}
