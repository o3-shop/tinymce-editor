<?php
/*
  RoxyFileman - web based file manager. Ready to use with CKEditor, TinyMCE.
  Can be easily integrated with any other WYSIWYG editor or CMS.

  Copyright (C) 2013, RoxyFileman.com - Lyubomir Arsov. All rights reserved.
  For licensing, see LICENSE.txt or http://RoxyFileman.com/license

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

  Contact: Lyubomir Arsov, liubo (at) web-lobby.com
*/
include_once 'security.inc.php';

function t(string $key): string
{
    global $LANG;

    if (empty($LANG)) {
        $file = 'en.json';
        $langPath = '../lang/';
        if (defined('LANG')) {
            if (LANG == 'auto') {
                $lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
                if (is_file($langPath . $lang . '.json')) {
                    $file = $lang . '.json';
                }
            } elseif (is_file($langPath . LANG . '.json')) {
                $file = LANG . '.json';
            }
        }
        $file = $langPath . $file;
        $LANG = json_decode((string) file_get_contents($file), true);
    }

    if (!$LANG[$key]) {
        $LANG[$key] = $key;
    }

    return $LANG[$key];
}

function checkPath(string $path): bool
{
    return mb_strpos($path . '/', getFilesPath()) === 0;
}

function verifyAction(string $action): void
{
    if (!defined($action) || !constant($action)) {
        exit;
    }

    $confUrl = constant($action);
    if (!is_string($confUrl)) {
        die('Error parsing configuration');
    }
    $qStr = mb_strpos($confUrl, '?');
    if ($qStr !== false) {
        $confUrl = mb_substr($confUrl, 0, $qStr);
    }
    $confUrl = BASE_PATH . '/' . $confUrl;
    $confUrl = RoxyFile::FixPath($confUrl);
    $thisUrl = dirname(__FILE__) . '/' . basename($_SERVER['PHP_SELF']);
    $thisUrl = RoxyFile::FixPath($thisUrl);
    if ($thisUrl != $confUrl) {
        echo "$confUrl $thisUrl";
        exit;
    }
}

function verifyPath(string $path): void
{
    if (!checkPath($path)) {
        echo getErrorRes("Access to $path is denied") . ' ' . $path;
        exit;
    }
}

function fixPath(string $path): string
{
    $path = dirname($_SERVER['SCRIPT_FILENAME']) . '/../../../../../../' . $path;
    $path = str_replace('\\', '/', $path);
    $path = RoxyFile::FixPath($path);
    return $path;
}

function getResultStr(string $type, string $str = ''): string
{
    return '{"res":"' . addslashes($type) . '","msg":"' . addslashes($str) . '"}';
}

function getSuccessRes(string $str = ''): string
{
    return getResultStr('ok', $str);
}

function getErrorRes(string $str = ''): string
{
    return getResultStr('error', $str);
}

function getFilesPath(): string
{
    $ret = (isset($_SESSION[SESSION_PATH_KEY]) && $_SESSION[SESSION_PATH_KEY] != '' ? $_SESSION[SESSION_PATH_KEY] : FILES_ROOT);

    if (!$ret) {
        $ret = RoxyFile::FixPath(BASE_PATH . '/Uploads');
        $tmp = $_SERVER['DOCUMENT_ROOT'];
        if (mb_substr($tmp, -1) == '/' || mb_substr($tmp, -1) == '\\') {
            $tmp = mb_substr($tmp, 0, -1);
        }
        $ret = str_replace(RoxyFile::FixPath($tmp), '', $ret);
    }
    return $ret;
}

/**
 * @param string $path
 * @return string[]
 */
function listDirectory(string $path): array
{
    $ret = @scandir($path);
    if ($ret === false) {
        $ret = [];
        $d = opendir($path);
        if ($d) {
            while (($f = readdir($d)) !== false) {
                $ret[] = $f;
            }
            closedir($d);
        }
    }

    return $ret;
}

class RoxyFile
{
    public static function CheckWritable(string $dir): bool
    {
        $ret = false;
        if (self::CreatePath($dir)) {
            $dir = self::FixPath($dir . '/');
            $testFile = 'writetest.txt';
            $f = @fopen($dir . $testFile, 'w', false);
            if ($f) {
                fclose($f);
                $ret = true;
                @unlink($dir . $testFile);
            }
        }

        return $ret;
    }

    /**
     * @param $path
     * @return bool
     */
    public static function CreatePath(string $path): bool
    {
        if (is_dir($path)) {
            return true;
        }
        $prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);
        $return = self::createPath($prev_path);
        return $return && is_writable($prev_path) && mkdir($path);
    }

    public static function CanUploadFile(string $filename): bool
    {
        $forbidden = array_filter((array) preg_split('/[^\d\w]+/', strtolower(FORBIDDEN_UPLOADS)));
        $allowed = array_filter((array) preg_split('/[^\d\w]+/', strtolower(ALLOWED_UPLOADS)));
        $ext = RoxyFile::GetExtension($filename);

        if ((empty($forbidden) || !in_array($ext, $forbidden)) && (empty($allowed) || in_array($ext, $allowed))) {
            return true;
        }

        return false;
    }

    public static function ZipAddDir(string $path, ZipArchive $zip, string $zipPath): void
    {
        $d = opendir($path);
        $zipPath = str_replace('//', '/', $zipPath);
        if ($zipPath && $zipPath != '/') {
            $zip->addEmptyDir($zipPath);
        }
        if (is_resource($d)) {
            while (($f = readdir($d)) !== false) {
                if ($f == '.' || $f == '..') {
                    continue;
                }
                $filePath = $path . '/' . $f;
                if (is_file($filePath)) {
                    $zip->addFile($filePath, ($zipPath ? $zipPath . '/' : '') . $f);
                } elseif (is_dir($filePath)) {
                    self::ZipAddDir($filePath, $zip, ($zipPath ? $zipPath . '/' : '') . $f);
                }
            }
        }
        if (is_resource($d)) {
            closedir($d);
        }
    }

    public static function ZipDir(string $path, string $zipFile, string $zipPath = ''): void
    {
        $zip = new ZipArchive();
        $zip->open($zipFile, ZIPARCHIVE::CREATE);
        self::ZipAddDir($path, $zip, $zipPath);
        $zip->close();
    }

    public static function IsImage(string $fileName): bool
    {
        $ext = strtolower(self::GetExtension($fileName));

        $imageExtensions = ['jpg', 'jpeg', 'jpe', 'png', 'gif', 'ico', 'webp'];

        return in_array($ext, $imageExtensions);
    }

    public static function IsFlash(string $fileName): bool
    {
        $ext = strtolower(self::GetExtension($fileName));

        $flashExtensions = ['swf', 'flv', 'swc', 'swt'];

        return in_array($ext, $flashExtensions);
    }

    /**
     * Returns human formated file size
     *
     * @param int $filesize
     * @return string
     */
    public static function FormatFileSize(int $filesize): string
    {
        $unit = 'B';
        if ($filesize > 1024) {
            $unit = 'KB';
            $filesize = $filesize / 1024;
        }
        if ($filesize > 1024) {
            $unit = 'MB';
            $filesize = $filesize / 1024;
        }
        if ($filesize > 1024) {
            $unit = 'GB';
            $filesize = $filesize / 1024;
        }

        $ret = round($filesize, 2) . ' ' . $unit;
        return $ret;
    }

    /**
     * Returns MIME type of $filename
     *
     * @param string $filename
     * @return string
     */
    public static function GetMIMEType(string $filename): string
    {
        $ext = self::GetExtension($filename);

        switch (strtolower($ext)) {
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'gif':
                return 'image/gif';
            case 'png':
                return 'image/png';
            case 'bmp':
                return 'image/bmp';
            case 'webp':
                return 'image/webp';
            case 'tiff':
            case 'tif':
                return 'image/tiff';
            case 'pdf':
                return 'application/pdf';
            case 'rtf':
            case 'doc':
                return 'application/msword';
            case 'xls':
                return 'application/vnd.ms-excel';
            case 'zip':
                return 'application/zip';
            case 'swf':
                return 'application/x-shockwave-flash';
            default:
                return 'application/octet-stream';
        }
    }

    /**
     * Replaces any character that is not letter, digit or underscore from $filename with $sep
     *
     * @param string $filename
     * @param string $sep
     * @return string
     */
    public static function CleanupFilename(string $filename, string $sep = '_'): string
    {
        $str = '';
        if (strpos($filename, '.')) {
            $ext = self::GetExtension($filename);
            $name = self::GetName($filename);
        } else {
            $ext = '';
            $name = $filename;
        }
        if (mb_strlen($name) > 32) {
            $name = mb_substr($name, 0, 32);
        }
        $str = str_replace('.php', '', $str);
        $str = (string) mb_ereg_replace("[^\\w]", $sep, $name);

        $str = (string) mb_ereg_replace("$sep+", $sep, $str) . ($ext ? '.' . $ext : '');

        return $str;
    }

    /**
     * Returns file extension without dot
     *
     * @param string $filename
     * @return string
     */
    public static function GetExtension(string $filename): string
    {
        $ext = '';

        if (mb_strrpos($filename, '.') !== false) {
            $ext = mb_substr($filename, mb_strrpos($filename, '.') + 1);
        }

        return strtolower($ext);
    }

    /**
     * Returns file name without extension
     *
     * @param string $filename
     * @return string
     */
    public static function GetName(string $filename): string
    {
        $tmp = mb_strpos($filename, '?');
        if ($tmp !== false) {
            $filename = mb_substr($filename, 0, $tmp);
        }
        $dotPos = mb_strrpos($filename, '.');
        if ($dotPos !== false) {
            $name = mb_substr($filename, 0, $dotPos);
        } else {
            $name = $filename;
        }

        return $name;
    }

    public static function GetFullName(string $filename): string
    {
        $tmp = mb_strpos($filename, '?');
        if ($tmp !== false) {
            $filename = mb_substr($filename, 0, $tmp);
        }
        return basename($filename);
    }

    public static function FixPath(string $path): string
    {
        $path = (string) mb_ereg_replace('[\\\/]+', '/', $path);
        //$path = (string) mb_ereg_replace('\.\.\/', '', $path);

        return $path;
    }

    /**
     * creates unique file name using $filename( " - Copy " and number is added if file already exists) in directory $dir
     *
     * @param string $dir
     * @param string $filename
     * @return string
     */
    public static function MakeUniqueFilename(string $dir, string $filename): string
    {
        ;
        $dir .= '/';
        $dir = self::FixPath($dir . '/');
        $ext = self::GetExtension($filename);
        $name = self::GetName($filename);
        $name = self::CleanupFilename($name);
        $name = mb_ereg_replace(' \\- Copy \\d+$', '', $name);
        if ($ext) {
            $ext = '.' . $ext;
        }
        if (!$name) {
            $name = 'file';
        }

        $i = 0;
        do {
            $temp = ($i > 0 ? $name . " - Copy $i" : $name) . $ext;
            $i++;
        } while (file_exists($dir . $temp));

        return $temp;
    }

    /**
     * creates unique directory name using $name( " - Copy " and number is added if directory already exists) in directory $dir
     *
     * @param string $dir
     * @param string $name
     * @return string
     */
    public static function MakeUniqueDirname(string $dir, string $name): string
    {
        $dir = self::FixPath($dir . '/');
        $name = mb_ereg_replace(' - Copy \\d+$', '', $name);
        if (!$name) {
            $name = 'directory';
        }

        $i = 0;
        do {
            $temp = ($i ? $name . " - Copy $i" : $name);
            $i++;
        } while (is_dir($dir . $temp));

        return $temp;
    }
}
class RoxyImage
{
    public static function GetImage(string $path)
    {
        $ext = RoxyFile::GetExtension(basename($path));
        switch ($ext) {
            case 'png':
                return imagecreatefrompng($path);
            case 'gif':
                return imagecreatefromgif($path);
            default:
                return imagecreatefromjpeg($path);
        }
    }

    public static function OutputImage($img, string $type, ?string $destination = '', int $quality = 90)
    {
        if (is_string($img)) {
            $img = self::GetImage($img);
        }

        switch (strtolower($type)) {
            case 'png':
                imagepng($img, $destination);
                break;
            case 'gif':
                imagegif($img, $destination);
                break;
            default:
                imagejpeg($img, $destination, $quality);
        }
    }

    public static function SetAlpha($img, string $path)
    {
        $ext = RoxyFile::GetExtension(basename($path));
        if ($ext == "gif" || $ext == "png") {
            imagecolortransparent($img, (int) imagecolorallocatealpha($img, 0, 0, 0, 127));
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }

        return $img;
    }

    public static function Resize(
        string $source,
        ?string $destination,
        int $width = 150,
        int $height = 0,
        int $quality = 90
    ): void {
        $tmp = (array) getimagesize($source);
        $w = $tmp[0];
        $h = $tmp[1];
        $r = $w / $h;

        if ($w <= ($width + 1) && (($h <= ($height + 1)) || (!$height && !$width))) {
            if ($source != $destination) {
                self::OutputImage($source, RoxyFile::GetExtension(basename($source)), $destination, $quality);
            }
            return;
        }

        $newWidth = $width;
        $newHeight = floor($newWidth / $r);
        if (($height > 0 && $newHeight > $height) || !$width) {
            $newHeight = $height;
            $newWidth = intval($newHeight * $r);
        }

        $thumbImg = imagecreatetruecolor((int) $newWidth, (int) $newHeight);
        $img = self::GetImage($source);

        $thumbImg = self::SetAlpha($thumbImg, $source);

        imagecopyresampled($thumbImg, $img, 0, 0, 0, 0, (int) $newWidth, (int) $newHeight, $w, $h);

        self::OutputImage($thumbImg, RoxyFile::GetExtension(basename($source)), $destination, $quality);
    }

    public static function CropCenter(
        string $source,
        ?string $destination,
        int $width,
        int $height,
        int $quality = 90
    ): void {
        $tmp = (array) getimagesize($source);
        $w = $tmp[0];
        $h = $tmp[1];
        if (($w <= $width) && (!$height || ($h <= $height))) {
            self::OutputImage(self::GetImage($source), RoxyFile::GetExtension(basename($source)), $destination, $quality);
        }
        $ratio = $width / $height;
        $top = $left = 0;

        $cropWidth = floor($h * $ratio);
        $cropHeight = floor($cropWidth / $ratio);
        if ($cropWidth > $w) {
            $cropWidth = $w;
            $cropHeight = $w / $ratio;
        }
        if ($cropHeight > $h) {
            $cropHeight = $h;
            $cropWidth = $h * $ratio;
        }

        if ($cropWidth < $w) {
            $left = floor(($w - $cropWidth) / 2);
        }
        if ($cropHeight < $h) {
            $top = floor(($h - $cropHeight) / 2);
        }

        self::Crop($source, $destination, (int) $left, (int) $top, $cropWidth, $cropHeight, $width, $height, $quality);
    }

    public static function Crop(
        string $source,
        ?string $destination,
        int $x,
        int $y,
        int $cropWidth,
        int $cropHeight,
        int $width,
        int $height,
        int $quality = 90
    ): void {
        $thumbImg = imagecreatetruecolor($width, $height);
        $img = self::GetImage($source);

        $thumbImg = self::SetAlpha($thumbImg, $source);

        imagecopyresampled($thumbImg, $img, 0, 0, $x, $y, $width, $height, $cropWidth, $cropHeight);

        self::OutputImage($thumbImg, RoxyFile::GetExtension(basename($source)), $destination, $quality);
    }
}

$tmp = json_decode((string) file_get_contents(BASE_PATH . '/conf.json'), true);

if (!$tmp || !is_array($tmp)) {
    die('Error parsing configuration');
}

foreach ($tmp as $k => $v) {
    define((string) $k, $v);
}

$FilesRoot = fixPath(getFilesPath());
if (!is_dir($FilesRoot)) {
    @mkdir($FilesRoot, (int) octdec(DIRPERMISSIONS));
}
