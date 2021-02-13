<?php

namespace Alif;

use Exception;

class Query
{
    public string $table = '';
    public array $select = [];
    public array $order = [];
    public int $offset = 0;
    public int $limit = 0;
    public array $where = [];
    public array $orWhere = [];
    public array $values = [];
    public array $group = [];
    public array $insert = [];
    public array $upsert = [];
    public array $update = [];
    public array $innerJoin = [];
    public array $leftJoin = [];
    public array $rightJoin = [];
    public array $fullJoin = [];

    function table(string $table)
    {
        $this->table = $table;

        return $this;
    }

    function innerJoin(string $table, string $joinColumn, string $sourceColumn)
    {
        $this->innerJoin[] = (object) [
            'table' => $table,
            'joinColumn' => $joinColumn,
            'sourceColumn' => $sourceColumn
        ];
    }

    function leftJoin(string $table, string $joinColumn, string $sourceColumn)
    {
        $this->leftJoin[] = (object) [
            'table' => $table,
            'joinColumn' => $joinColumn,
            'sourceColumn' => $sourceColumn
        ];
    }

    function rightJoin(string $table, string $joinColumn, string $sourceColumn)
    {
        $this->rightJoin[] = (object) [
            'table' => $table,
            'joinColumn' => $joinColumn,
            'sourceColumn' => $sourceColumn
        ];
    }

    function fullJoin(string $table, string $joinColumn, string $sourceColumn)
    {
        $this->fullJoin[] = (object) [
            'table' => $table,
            'joinColumn' => $joinColumn,
            'sourceColumn' => $sourceColumn
        ];
    }

    function select(...$columns)
    {
        $this->select = $columns;

        return $this;
    }

    function where(string $column, string $sign, $value)
    {
        $prepared = md5($column);

        $this->where[] = (object) [
            'column' => $column,
            'prepared' => $prepared,
            'sign' => $sign,
            'value' => $value
        ];

        $this->values[$prepared] = $value;

        return $this;
    }

    function orWhere(string $column, string $sign, $value)
    {
        $prepared = md5($column);

        $this->orWhere[] = (object) [
            'column' => $column,
            'prepared' => $prepared,
            'sign' => $sign,
            'value' => $value
        ];

        $this->values[$prepared] = $value;

        return $this;
    }

    function offset($offset)
    {
        $this->offset = (int) $offset;

        return $this;
    }

    function group(string $column)
    {
        $this->group[] = $column;

        return $this;
    }

    function order(string $column, string $order = 'ASC')
    {
        $this->order[] = (object) [
            'column' => $column,
            'order' => strtoupper($order)
        ];

        return $this;
    }

    function limit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    function get(): array
    {
        $result = [];
        $rows = App::$db->select($this->getSelectQuery(), $this->values);

        foreach ($rows as $row) {
            $result[] = (object) $row;
        }

        return $result;
    }

    function first()
    {
        $this->offset(0);
        $this->limit(1);

        $result = App::$db->select($this->getSelectQuery(), $this->values)[0] ?? false;

        return $result ? (object) $result : null;
    }

    function count(): int
    {
        try {
            $statement = App::$db->pdo->prepare($this->getCountQuery());
            $statement->execute($this->values);
            return (int) $statement->fetchColumn();
        } catch (Exception $e) {
            http_response_code(400);
            die("SQL error: [{$e->getCode()}] -> {$e->getMessage()}");
        }
    }

    function pluck(string $column): array
    {
        $this->select = [$column];

        $array = [];

        foreach ($this->get() as $key => $row) {
            $array[$key] = $row->{$column};
        }

        return $array;
    }

    function delete()
    {
        return App::$db->execute($this->getDeleteQuery(), $this->values);
    }

    function insert(array $insert)
    {
        $this->insert = $insert;

        if (!$this->insert) {
            return false;
        }

        try {
            $statement = App::$db->pdo->prepare($this->getInsertQuery());
            $statement->execute($this->values);
            return App::$db->pdo->lastInsertId();
        } catch (Exception $e) {
            http_response_code(400);
            die("SQL error: [{$e->getCode()}] -> {$e->getMessage()}");
        }
    }

    function upsert(array $upsert)
    {

        $this->upsert = $upsert;

        if (!$this->upsert) {
            return false;
        }

        return App::$db->execute($this->getUpsertQuery());
    }

    function update(array $update)
    {
        $this->update = $update;

        if (!$this->insert) {
            return false;
        }

        return App::$db->execute($this->getUpdateQuery(), $this->values);
    }

    protected function getInnerJoinSql(): string
    {
        $sql = "";

        foreach ($this->innerJoin as $join) {
            $sql .= " INNER JOIN {$join->table} ON {$join->joinColumn} = {$join->sourceColumn} ";
        }

        return $sql;
    }

    protected function getLeftJoinSql(): string
    {
        $sql = "";

        foreach ($this->leftJoin as $join) {
            $sql .= " LEFT JOIN {$join->table} ON {$join->joinColumn} = {$join->sourceColumn} ";
        }

        return $sql;
    }

    protected function getRightJoinSql(): string
    {
        $sql = "";

        foreach ($this->rightJoin as $join) {
            $sql .= " RIGHT JOIN {$join->table} ON {$join->joinColumn} = {$join->sourceColumn} ";
        }

        return $sql;
    }

    protected function getFullJoinSql(): string
    {
        $sql = "";

        foreach ($this->innerJoin as $join) {
            $sql .= " FULL OUTER JOIN {$join->table} ON {$join->joinColumn} = {$join->sourceColumn} ";
        }

        return $sql;
    }

    protected function getWhereSql(): string
    {
        $sql = "";

        if ($this->where) {
            $sql .= " WHERE ";

            $array = [];

            foreach ($this->where as $where) {
                $array[] = "{$where->column} {$where->sign} :{$where->prepared}";
            }

            $sql .= implode(' && ', $array);
        }

        if ($this->orWhere) {
            $array = [];

            $sql .= $sql ? ' || ( ' : ' WHERE (';


            foreach ($this->orWhere as $where) {
                $array[] = "{$where->column} {$where->sign} :{$where->prepared}";
            }

            $sql .= implode(' ) || ( ', $array) . ' ) ';
        }

        return $sql;
    }

    protected function getGroupSql(): string
    {
        $sql = "";

        if ($this->group) {
            $sql .= " GROUP BY " . implode(', ', $this->group);
        }

        return $sql;
    }

    protected function getOrderSql(): string
    {
        $sql = '';

        if ($this->order) {
            $sql .= " ORDER BY ";
            $array = [];

            foreach ($this->order as $column => $direction) {
                $array[] = "{$column} {$direction}";
            }

            $sql .= implode(', ', $array);
        }

        return $sql;
    }

    protected function getLimitSql(): string
    {
        $sql = '';

        if ($this->limit) {
            if ($this->offset) {
                $sql .= " LIMIT {$this->offset}, {$this->limit} ";
            } else {
                $sql .= " LIMIT {$this->limit} ";
            }
        }

        return $sql;
    }

    protected function getSelectSql(): string
    {
        return $this->select ? implode(', ', $this->select) : " * ";
    }

    protected function getInsertSql(): string
    {
        $sql = '';

        if ($this->insert) {
            $array = [];

            foreach ($this->insert as $column => $value) {
                $prepared = md5("query_insert_{$column}");
                $array[] = "{$column} = :{$prepared}";
                $this->values[$prepared] = $value;
            }

            $sql .= implode(', ', $array);
        }

        return $sql;
    }

    protected function getUpsertSql(): string
    {
        $sql = '';

        if (count($this->upsert) == 2) {
            if ($this->upsert[0] && $this->upsert[1]) {
                $array = [];

                foreach ($this->upsert[0] as $column => $value) {
                    $prepared = md5("query_upsert_{$column}");
                    $array[] = ":{$prepared}";
                    $this->values[$prepared] = $value;
                }

                $upsertKey = array_keys($this->upsert[1])[0];
                $upsertPrepared = md5("query_upsert_" . $upsertKey);
                $this->values[$upsertPrepared] = $this->upsert[1][$upsertKey];

                $sql .= " (" . implode(', ', array_keys($array)) . ") VALUES (" . implode(', ', $array) . ") ON DUPLICATE KEY UPDATE {$upsertKey} = :{$upsertPrepared};";
            }
        }

        return $sql;
    }

    protected function getUpdateSql(): string
    {
        $sql = '';

        if ($this->update) {
            $array = [];

            foreach ($this->update as $column => $value) {
                $prepared = md5("query_update_{$column}");
                $array[] = "{$column} = :{$prepared}";
                $this->values[$prepared] = $value;
            }

            $sql .= implode(', ', $array);
        }

        return $sql;
    }

    protected function getCountQuery(): string
    {
        return "SELECT COUNT(*) FROM {$this->table} 
        {$this->getInnerJoinSql()}
        {$this->getLeftJoinSql()}
        {$this->getRightJoinSql()}
        {$this->getFullJoinSql()}
        {$this->getWhereSql()}
        {$this->getGroupSql()}
        {$this->getOrderSql()}
        {$this->getLimitSql()};";
    }

    protected function getSelectQuery(): string
    {
        return "SELECT {$this->getSelectSql()} FROM {$this->table}
        {$this->getInnerJoinSql()}
        {$this->getLeftJoinSql()}
        {$this->getRightJoinSql()}
        {$this->getFullJoinSql()}
        {$this->getWhereSql()}
        {$this->getGroupSql()}
        {$this->getOrderSql()}
        {$this->getLimitSql()};";
    }

    protected function getDeleteQuery(): string
    {
        return "DELETE FROM {$this->table} {$this->getWhereSql()} {$this->getLimitSql()};";
    }

    protected function getInsertQuery(): string
    {
        return "INSERT INTO {$this->table} SET {$this->getInsertSql()};";
    }

    protected function getUpsertQuery(): string
    {
        return "INSERT INTO {$this->table} {$this->getUpsertSql()};";
    }

    protected function getUpdateQuery(): string
    {
        return "UPDATE {$this->table} SET {$this->getUpdateSql()} {$this->getLimitSql()};";
    }
}
