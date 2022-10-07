<?php

declare(strict_types=1);

namespace Cycle\SymfonyBundle;

use Cycle\Database\Config;
use Cycle\Database\Config\{ConnectionConfig, DriverConfig};
use Cycle\SymfonyBundle\Exception\AbstractException;

class ConnectionFactory
{
    /** @var AbstractException */
    public static function createConnectionConfig(array $parameters): ConnectionConfig
    {
        return match ($parameters['type']) {
            'mysql' => self::createMysql($parameters),
            'pgsql' => self::createPgsql($parameters),
            'sqlite' => self::createSqlite($parameters),
            'sqlsrv' => self::createSqlsrv($parameters),

            default => throw new AbstractException('Invalid parameter `type` in `connections` section in cycle.yaml'),
        };
    }

    public static function createDriverConfig(ConnectionConfig $connectionConfig): DriverConfig
    {
        return match (true) {
            $connectionConfig instanceof Config\MySQL\ConnectionConfig => new Config\MySQLDriverConfig($connectionConfig),
            $connectionConfig instanceof Config\Postgres\ConnectionConfig => new Config\PostgresDriverConfig($connectionConfig),
            $connectionConfig instanceof Config\SQLite\ConnectionConfig => new Config\SQLiteDriverConfig($connectionConfig),
            $connectionConfig instanceof Config\SQLServer\ConnectionConfig => new Config\SQLServerDriverConfig($connectionConfig),
        };
    }

    /** @var AbstractException */
    private static function createMysql(array $parameters): ConnectionConfig
    {
        // connect by TCP
        if (isset($parameters['database'], $parameters['host'], $parameters['port'])) {
            return new Config\MySQL\TcpConnectionConfig(
                database: $parameters['database'],
                host: $parameters['host'],
                port: $parameters['port'],
                charset: $parameters['charset'],
                user: $parameters['user'],
                password: $parameters['password'],
                options: $parameters['options'] ?? []
            );
        }

        // connect by DSN
        if (isset($parameters['dsn'])) {
            return new Config\MySQL\DsnConnectionConfig(
                dsn: $parameters['dsn'],
                user: $parameters['user'],
                password: $parameters['password'],
                options: $parameters['options'] ?? []
            );
        }

        // connect by SOCKET
        if (isset($parameters['socket'])) {
            return new Config\MySQL\SocketConnectionConfig(
                database: $parameters['database'],
                socket: $parameters['socket'],
                charset: $parameters['charset'],
                user: $parameters['user'],
                password: $parameters['password'],
                options: $parameters['options'] ?? []
            );
        }

        throw new AbstractException('Can not create MySQL connection based on `connections` section in cycle.yaml');
    }

    /** @var AbstractException */
    private static function createPgsql(array $parameters): ConnectionConfig
    {
        // connect by TCP
        if (isset($parameters['database'], $parameters['host'], $parameters['port'])) {
            return new Config\Postgres\TcpConnectionConfig(
                database: $parameters['database'],
                host: $parameters['host'],
                port: $parameters['port'],
                user: $parameters['user'],
                password: $parameters['password'],
                options: $parameters['options'] ?? []
            );
        }

        // connect by DSN
        if (isset($parameters['dsn'])) {
            return new Config\Postgres\DsnConnectionConfig(
                dsn: $parameters['dsn'],
                user: $parameters['user'],
                password: $parameters['password'],
                options: $parameters['options'] ?? []
            );
        }

        throw new AbstractException('Can not create Postgres connection based on `connections` section in cycle.yaml');
    }

    /** @var AbstractException */
    private static function createSqlite(array $parameters): ConnectionConfig
    {
        // connect by DSN
        if (isset($parameters['dsn'])) {
            return new Config\SQLite\DsnConnectionConfig(
                dsn: $parameters['dsn'],
                options: $parameters['options'] ?? []
            );
        }

        // connect by DSN
        if (isset($parameters['database'])) {
            return new Config\SQLite\FileConnectionConfig(
                database: $parameters['database'],
                options: $parameters['options'] ?? []
            );
        }

        throw new AbstractException('Can not create SQLite connection based on `connections` section in cycle.yaml');
    }

    /** @var AbstractException */
    private static function createSqlsrv(array $parameters): ConnectionConfig
    {
        // connect by DSN
        if (isset($parameters['dsn'])) {
            return new Config\SQLServer\DsnConnectionConfig(
                dsn: $parameters['dsn'],
                user: $parameters['user'],
                password: $parameters['password'],
                options: $parameters['options'] ?? []
            );
        }

        // connect by DSN
        if (isset($parameters['database'])) {
            return new Config\SQLServer\TcpConnectionConfig(
                database: $parameters['database'],
                host: $parameters['host'],
                port: $parameters['port'],
                user: $parameters['user'],
                password: $parameters['password'],
                options: $parameters['options'] ?? []
            );
        }

        throw new AbstractException('Can not create SQLServer connection based on `connections` section in cycle.yaml');
    }
}
