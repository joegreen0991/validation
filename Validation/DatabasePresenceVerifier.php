<?php

namespace Validation;

class DatabasePresenceVerifier implements PresenceVerifierInterface {

    /**
     * The database connection instance.
     *
     * @var  \PDO
     */
    protected $db;

    /**
     * Create a new database presence verifier.
     *
     * @param  \PDO  $db
     * @return void
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Count the number of objects in a collection having the given value.
     *
     * @param  string  $collection
     * @param  string  $column
     * @param  string  $value
     * @param  int     $excludeId
     * @param  string  $idColumn
     * @param  array   $extra
     * @return int
     */
    public function getCount($collection, $column, $value, $excludeId = null, $idColumn = null, array $extra = array())
    {
        $query = "SELECT count(*) FROM $collection WHERE $column = ?";

        $bind = array($value);

        if (!is_null($excludeId) && $excludeId != 'NULL')
        {
            $bind[] = $excludeId;
            $query .= ' AND ' . ($idColumn ? : 'id') . ' <> ?';
        }

        foreach ($extra as $key => $extraValue) {
            $query .= ' AND ' . $key . ' <> ?';
            $bind[] = $extraValue;
        }

        $stmt = $query->prepare();

        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Count the number of objects in a collection with the given values.
     *
     * @param  string  $collection
     * @param  string  $column
     * @param  array   $values
     * @param  array   $extra
     * @return int
     */
    public function getMultiCount($collection, $column, array $values, array $extra = array())
    {

        $qs = trim(str_repeat('?,', count($values)), ',');

        $query = "SELECT count(*) FROM $collection WHERE $column IN $qs";

        $bind = array_values($values);

        foreach ($extra as $key => $extraValue) {
            $query .= ' AND ' . $key . ' <> ?';
            $bind[] = $extraValue;
        }

        $stmt = $query->prepare();

        $stmt->execute($bind);

        return (int) $stmt->fetchColumn();
    }

    /**
     * Add a "where" clause to the given query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $key
     * @param  string  $extraValue
     * @return void
     */
    protected function addWhere($query, $key, $extraValue)
    {
        if ($extraValue == 'NULL')
        {
            $query->whereNull($key);
        } elseif ($extraValue == 'NOT_NULL')
        {
            $query->whereNotNull($key);
        } else
        {
            $query->where($key, $extraValue);
        }
    }

    /**
     * Set the connection to be used.
     *
     * @param  string  $connection
     * @return void
     */
    public function setConnection(\PDO $connection)
    {
        $this->connection = $connection;
    }

}
