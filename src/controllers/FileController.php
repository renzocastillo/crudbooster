<?php namespace crocodicstudio\crudbooster\controllers;

use File;
use Image;
use Request;
use Response;
use Storage;

class FileController extends Controller
{
    public function getPreview($one, $two = null, $three = null, $four = null, $five = null)
    {

        if ($two) {
            $fullFilePath = 'uploads/'.$one.'/'.$two;
            $filename = $two;
            if ($three) {
                $fullFilePath = 'uploads/'.$one.'/'.$two.'/'.$three;
                $filename = $three;
                if ($four) {
                    $fullFilePath = 'uploads/'.$one.'/'.$two.'/'.$three.'/'.$four;
                    $filename = $four;
                    if ($five) {
                        $fullFilePath = 'uploads/'.$one.'/'.$two.'/'.$three.'/'.$four.'/'.$five;
                        $filename = $five;
                    }
                }
            }
        } else {
            $fullFilePath = 'uploads/'.$one;
            $filename = $one;
        }

        $fullStoragePath = storage_path('app/'.$fullFilePath);
        $lifetime = 31556926; // One year in seconds

        // Validate that the file exists and is not a directory
        if (! Storage::exists($fullFilePath) || ! is_file($fullStoragePath)) {
            abort(404);
        }

        $handler = new \Symfony\Component\HttpFoundation\File\File($fullStoragePath);

        $extension = strtolower(File::extension($fullStoragePath));
        $images_ext = config('crudbooster.IMAGE_EXTENSIONS', 'jpg,png,gif,bmp');
        $images_ext = explode(',', $images_ext);
        $imageFileSize = 0;
        $imgRaw = null;

        if (in_array($extension, $images_ext)) {
            $defaultThumbnail = config('crudbooster.DEFAULT_THUMBNAIL_WIDTH');
            if ($defaultThumbnail != 0) {
                $w = Request::get('w') ?: $defaultThumbnail;
                $h = Request::get('h') ?: $w;
            } else {
                $w = Request::get('w');
                $h = Request::get('h') ?: $w;
            }

            $img = Image::make($fullStoragePath);
            if ($w) {
                if (! $h) {
                    $img->fit($w);
                } else {
                    $img->fit($w, $h);
                }
            }
            $imgRaw = (string) $img->encode();

            $imageFileSize = mb_strlen($imgRaw, '8bit') ?: 0;
        }

        /**
         * Prepare some header variables
         */
        $file_time = $handler->getMTime(); // Get the last modified time for the file (Unix timestamp)

        $header_content_type = $handler->getMimeType();
        $header_content_length = ($imageFileSize) ?: $handler->getSize();
        $header_etag = md5($file_time.$fullFilePath);
        $header_last_modified = gmdate('r', $file_time);
        $header_expires = gmdate('r', $file_time + $lifetime);

        $headers = [
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Last-Modified' => $header_last_modified,
            'Cache-Control' => 'must-revalidate',
            'Expires' => $header_expires,
            'Pragma' => 'public',
            'Etag' => $header_etag,
        ];

        /**
         * Is the resource cached?
         */
        $h1 = isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified;
        $h2 = isset($_SERVER['HTTP_IF_NONE_MATCH']) && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) == $header_etag;

        $headers = array_merge($headers, [
            'Content-Type' => $header_content_type,
            'Content-Length' => $header_content_length,
        ]);

        if (in_array($extension, $images_ext)) {
            if ($h1 || $h2) {
                return Response::make('', 304, $headers); // File (image) is cached by the browser, so we don't have to send it again
            } else {
                return Response::make($imgRaw, 200, $headers);
            }
        } else {
            if (Request::get('download')) {
                return Response::download($fullStoragePath, $filename, $headers);
            } else {
                return Response::file($fullStoragePath, $headers);
            }
        }
    }
}
