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
 * Copiatek - info@copiatek.nl - Postbus 547 2501 CM Den Haag
 */

/*
 * This file is taken from the Symfony WebpackEncoreBundle package.
 * by Fabien Potencier <fabien@symfony.com>
 */

namespace AppBundle\Helper;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpKernel\KernelInterface;

class EncoreTwigExtension extends AbstractExtension
{
    private $relativeBuildPath = "web/js/build/";

    public function getName()
    {
        return "app.encoretwig";
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('encore_entry_js_files', [$this, 'getWebpackJsFiles']),
            new TwigFunction('encore_entry_css_files', [$this, 'getWebpackCssFiles']),
            new TwigFunction('encore_entry_script_tags', [$this, 'renderWebpackScriptTags'], ['is_safe' => ['html']]),
            new TwigFunction('encore_entry_link_tags', [$this, 'renderWebpackLinkTags'], ['is_safe' => ['html']]),
        ];
    }

    public function getWebpackJsFiles(string $entryName): array
    {
        return $this->getWebpackFiles($entryName, "js");
    }

    public function getWebpackCssFiles(string $entryName): array
    {
        return $this->getWebpackFiles($entryName, "css");
    }

    private function getWebpackFiles(string $entryName, string $fileType): array
    {
        $fullpath = dirname(__FILE__) . "/../../../" . $this->relativeBuildPath . "entrypoints.json";

        // TODO: this needs improvement
        if (!file_exists($fullpath))
        {
            $this->relativeBuildPath = "../public_html/nsk/js/build/";
            $fullpath = dirname(__FILE__) . "/../../../" . $this->relativeBuildPath . "entrypoints.json";
        }

        $entrypoints = json_decode(file_get_contents($fullpath), true);
        return $entrypoints['entrypoints'][$entryName][$fileType];
    }

    public function renderWebpackScriptTags(string $entryName): string
    {
        $tags = array();

        foreach ($this->getWebpackJsFiles($entryName) as $file)
        {
            $tags[] = '<script src="'.$file.'"></script>';
        }

        return implode(PHP_EOL, $tags);
    }

    public function renderWebpackLinkTags(string $entryName): string
    {
        $tags = array();

        foreach ($this->getWebpackCssFiles($entryName) as $file)
        {
            $tags[] = '<link rel="stylesheet" href="'.$file.'">';
        }

        return implode(PHP_EOL, $tags);
    }
}
