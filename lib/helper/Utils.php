<?php


namespace sitemap\helper;


class Utils
{
    public static function getSitemapFiles($path, $offset = 0, $length = 9, $flag = 'sitemaps_')
    {

        if (!file_exists($path)) {
            return [];
        }
        $handle = dir($path);
        $fileItem = [];
        if ($handle) {
            while (($file = $handle->read())) {
                $newPath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($newPath) && $file != '.' && $file != '..') {


                } else if (is_file($newPath)) {
                    $filename = basename($newPath);
                    if (substr($filename, $offset, $length) == $flag) {

                        $fileItem[] = $filename;

                    }
                }

            }

        }
        $handle->close();
        return $fileItem;

    }
}