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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Route("/upload")
 */
class UploadifiveController extends Controller
{
    private $checkToken = false;
    private $mustBeImages = false;
    private $uploadFolder = '/var/uploads';
    private $subfolderInFormData = false;
    private $filenameInFormData = false;
    private $allowedExtensions = array('jpg', 'jpeg', 'gif', 'png');

    /**
     * @Route("/", name="uploadifive")
     * @Method("POST")
     */
    public function uploadAction(Request $request)
    {
        // This approach assumes that the standard Symfony form token is used in uploadifive callback too
        if ($this->checkToken && !$this->isCsrfTokenValid('token_id', $request->request->get('token')))
        {
            return new Response("Token is invalid.");
        }
        elseif (!$request->files->count())
            return new Response("No files to upload.");

        if ($this->subfolderInFormData)
        {
            $fullUploadFolder = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..' . $this->uploadFolder . DIRECTORY_SEPARATOR . $request->request->get('subfolder');
        }
        else
        {
            $fullUploadFolder = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..' . $this->uploadFolder;
        }

        $count = 1;

        /** @var UploadedFile $file */
        foreach ($request->files->all() as $file)
        {
            if ($this->mustBeImages && !$this->isImage($file->getRealPath()))
            {
                continue;
            }
            elseif (count($this->allowedExtensions) && !in_array(strtolower($file->getExtension()), $this->allowedExtensions))
            {
                continue;
            }

            if ($this->filenameInFormData && $request->request->get('filename'))
            {
                if ($request->files->count() > 1)
                    $filename = $request->request->get('filename') . "-" . $count;
                else
                    $filename = $request->request->get('filename');
            }
            else
            {
                $filename = $file->getClientOriginalName();
            }

            $file->move($fullUploadFolder, $filename);

            $count++;
        }

        return new Response("1");
    }

    /**
     * @Route("/", name="uploadifive_checkexists")
     * @Method("POST")
     */
    public function checkexistsAction(Request $request)
    {
        if ($this->filenameInFormData || $this->subfolderInFormData)
        {
            return new Response("This function cannot be used with file or folder names in form data.");
        }

        $fullPath = $this->get('kernel')->getRootDir() . DIRECTORY_SEPARATOR . '..' . $this->uploadFolder . DIRECTORY_SEPARATOR . $request->request->get('filename');

        if (file_exists($fullPath)) {
            return new Response("1");
        } else {
            return new Response("0");
        }
    }

    private function isImage($tempFile) {

        // Get the size of the image
        $size = getimagesize($tempFile);

        if (isset($size) && $size[0] && $size[1] && $size[0] *  $size[1] > 0) {
            return true;
        } else {
            return false;
        }

    }
}
