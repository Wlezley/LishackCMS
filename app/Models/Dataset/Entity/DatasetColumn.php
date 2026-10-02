<?php

declare(strict_types=1);

namespace App\Models\Dataset\Entity;

final class DatasetColumn
{
//    public function getDatabaseColumnName(): string
//    {
//        return \App\Models\Dataset\Repository\DataRepository::DATA_COLUMN_PREFIX . $this->columnId;
//    }

//    /**
//     * Returns the SQL column definition for CREATE/ALTER TABLE or ADD/MODIFY COLUMN
//     */
//    public function getColumnSqlDefinition(mixed $default = null, bool $isNullable = true): string
//    {
//        if (!$this->columnId) {
//            throw new DatasetException('Cannot get SQL definition without column ID.');
//        }
//
//        $columnName = $this->getDatabaseColumnName();
//        $sqlType = $this->getSqlType();
//        $nullableClause = $isNullable ? 'NULL' : 'NOT NULL';
//        $defaultClause = SqlHelper::formatDefaultValue($default);
//
//        return "`{$columnName}` {$sqlType} {$nullableClause} DEFAULT {$defaultClause}";
//    }
}
