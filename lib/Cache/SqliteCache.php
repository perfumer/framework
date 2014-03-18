<?php

namespace Perfumer\Cache;

use Perfumer\Cache\Exception\CacheException;

class SqliteCache extends AbstractCache
{
    protected $pdo;

    public function __construct($database, $schema, $lifetime)
    {
        parent::__construct($lifetime);

        $this->pdo = new \PDO('sqlite:' . $database);

        $result = $this->pdo->query("SELECT * FROM sqlite_master WHERE name = 'caches' AND type = 'table'")->fetchAll();

        if (count($result) == 0)
        {
            try
            {
                $this->pdo->query($schema);
            }
            catch (\PDOException $e)
            {
                throw new CacheException('Failed to create new SQLite caches table with the following error: ' . $e->getMessage());
            }
        }
    }

    public function get($name, $default = null)
    {
        $statement = $this->pdo->prepare('SELECT id, expiration, cache FROM caches WHERE id = :id LIMIT 0, 1');

        try
        {
            $statement->execute([':id' => $this->sanitize($name)]);
        }
        catch (\PDOException $e)
        {
            throw new CacheException('There was a problem querying the local SQLite3 cache. ' . $e->getMessage());
        }

        if (!$result = $statement->fetch(\PDO::FETCH_OBJ))
            return $default;

        if ($result->expiration != 0 and $result->expiration <= time())
        {
            $this->delete($name);
            return $default;
        }
        else
        {
            $ER = error_reporting(~E_NOTICE);

            $data = unserialize($result->cache);

            error_reporting($ER);

            return $data;
        }
    }

    public function set($name, $value, $lifetime = null)
    {
        $value = serialize($value);

        if ($lifetime === null)
            $lifetime = $this->lifetime;

        if ($lifetime !== 0)
            $lifetime += time();

        if ($this->has($name))
        {
            $statement = $this->pdo->prepare('UPDATE caches SET expiration = :expiration, cache = :cache WHERE id = :id');
        }
        else
        {
            $statement = $this->pdo->prepare('INSERT INTO caches (id, cache, expiration) VALUES (:id, :cache, :expiration)');
        }

        try
        {
            $statement->execute([':id' => $this->sanitize($name), ':cache' => $value, ':expiration' => $lifetime]);
        }
        catch (\PDOException $e)
        {
            throw new CacheException('There was a problem querying the local SQLite3 cache. ' . $e->getMessage());
        }

        return (bool) $statement->rowCount();
    }

    public function has($name)
    {
        $statement = $this->pdo->prepare('SELECT id FROM caches WHERE id = :id');
        try
        {
            $statement->execute([':id' => $this->sanitize($name)]);
        }
        catch (\PDOException $e)
        {
            throw new CacheException('There was a problem querying the local SQLite3 cache. ' . $e->getMessage());
        }

        return (bool) $statement->fetchAll();
    }

    public function delete($name)
    {
        $statement = $this->pdo->prepare('DELETE FROM caches WHERE id = :id');

        try
        {
            $statement->execute([':id' => $this->sanitize($name)]);
        }
        catch (\PDOException $e)
        {
            throw new CacheException('There was a problem querying the local SQLite3 cache. ' . $e->getMessage());
        }

        return (bool) $statement->rowCount();
    }
}