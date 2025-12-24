<?php
/**
 * Img - Image manipulation utilities
 * Summary: Provides image resizing and format conversion using Imagick and GD
 *
 * Author:  KaisarCode
 * Website: https://kaisarcode.com
 * License: GNU GPL v3.0
 * License URL: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License.
 */

/**
 * Image manipulation utilities
 */
class Img
{

    /**
     * Resize and optionally extend an image while preserving aspect ratio.
     *
     * @param string $url Image source URL or path.
     * @param int $w Target width.
     * @param int|null $h Target height; if null, maintain aspect ratio.
     * @param string $fmt Preferred output format (default: 'png').
     * @return string Binary image blob.
     */
    static function proc(string $url, int $w, ?int $h = null, string $fmt = 'png'): string
    {
        $data = file_get_contents($url);
        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));

        if ($ext === 'svg') {
            $h = $h ?? $w;
            $tmpd = sys_get_temp_dir();
            $tmpIn = "$tmpd/in_" . uniqid() . ".svg";
            $tmpOut = "$tmpd/out_" . uniqid() . ".$fmt";
            file_put_contents($tmpIn, $data);
            $cmd = "rsvg-convert -w $w -h $h $tmpIn -o $tmpOut";
            exec($cmd);
            if (file_exists($tmpOut)) {
                $blob = file_get_contents($tmpOut);
                unlink($tmpOut);
            } else {
                $blob = '';
            }
            if (file_exists($tmpIn))
                unlink($tmpIn);
            return $blob;
        }

        if (strtolower($fmt) === 'webp') {
            $tmp = new Imagick();
            $formats = array_map('strtolower', $tmp->queryFormats());
            if (!in_array('webp', $formats)) {
                $src = imagecreatefromstring($data);
                $iw = imagesx($src);
                $ih = imagesy($src);
                if ($h === null) {
                    $ratio = $ih / $iw;
                    $h = (int) round($w * $ratio);
                }
                $dst = imagecreatetruecolor($w, $h);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, $iw, $ih);
                ob_start();
                imagewebp($dst, null, 90);
                $blob = ob_get_clean();
                imagedestroy($src);
                imagedestroy($dst);
                return $blob;
            }
        }

        $img = new Imagick();
        $img->setBackgroundColor(new ImagickPixel('transparent'));
        $img->setResolution($w * 2, $h ? $h * 2 : $w * 2);
        $img->readImageBlob($data);
        $img->setImageFormat($fmt);
        $img->setImageCompressionQuality(95);

        $filter = Imagick::FILTER_LANCZOS;
        $blur = 1;

        if ($h === null) {
            $img->resizeImage($w, 0, $filter, $blur);
        } else {
            $img->resizeImage($w, $h, $filter, $blur, true);
            $b = new ImagickPixel('transparent');
            $img->setImageBackgroundColor($b);
            $imw = $img->getImageWidth();
            $imh = $img->getImageHeight();
            $exw = (int) (-($w / 2) + ($imw / 2));
            $exh = (int) (-($h / 2) + ($imh / 2));
            $img->extentImage($w, $h, $exw, $exh);
            $img->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
            $img->mergeImageLayers(Imagick::LAYERMETHOD_MERGE);
        }

        $blob = $img->getImagesBlob();
        $img->clear();
        $img->destroy();

        return $blob;
    }
}
