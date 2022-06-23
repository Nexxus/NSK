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

use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class IgnoreTablesListener
{
    private $ignoredTables = [
        'stock',
    ];

    public function postGenerateSchema(GenerateSchemaEventArgs $args)
    {
        $schema = $args->getSchema();
        $schemaName = $schema->getName();
        $tableNames = $schema->getTableNames();
       
        $this->ignoredTables = array_map(function ($ignoredTable) use ($schemaName) {
            return $ignoredTable = $schemaName.'.'.$ignoredTable;
        }, $this->ignoredTables);

        foreach ($tableNames as $tableName) {

            if (in_array($tableName, $this->ignoredTables)) {
                // remove table from schema
                $schema->dropTable($tableName);
            }

        }
    }

}