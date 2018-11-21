<?php

/*
 * Nexxus Stock Keeping (online voorraad beheer software)
 * Copyright (C) 2018 Copiatek Scan & Computer Solution BV
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see licenses.
 *
 * Copiatek – info@copiatek.nl – Postbus 547 2501 CM Den Haag
 */

namespace AppBundle\Controller;

trait UploadControllerTrait
{
    private $uploadFolder = '/var/uploads';

    private function isImage($tempFile) {

        // Get the size of the image
        $size = getimagesize($tempFile);

        if (isset($size) && $size[0] && $size[1] && $size[0] *  $size[1] > 0) {
            return true;
        } else {
            return false;
        }

    }

    private function getFullUploadFolder()
    {
        return
            $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR .
            '..' . DIRECTORY_SEPARATOR .
            $this->uploadFolder . DIRECTORY_SEPARATOR;
    }
}