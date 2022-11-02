<?php declare(strict_types=1);

namespace ashishrcrm\Aurora;

use Exception;
use Illuminate\Database\Connectors\MySqlConnector;
use PDOException;

final class AuroraConnector extends MySqlConnector
{
    // public function __construct(private PasswordResolver $resolver)
    // {}

    public function createConnection($dsn, array $config, array $options)
    {
        // If the developer explicitly set the `password` attribute on the database
        // configuration, we'll go ahead and establish a regular connection. This
        // is useful for automation tests that bypass the connection process.
        if (! empty($config['password'])) {
            
            $connection = parent::createConnection($dsn, $config, $options);

            // This will check if the request needs to be forwarded to writer in
            // case the host is a reader
            $this->configureReadConsistency($connection, $config);

            return $connection;
        }

    }

    /**
     * Allow Aurora to use read committed from the read replica to reduce history length
     * maintained by the writer replica.
     * @see https://docs.aws.amazon.com/AmazonRDS/latest/AuroraUserGuide/AuroraMySQL.Reference.html#AuroraMySQL.Reference.IsolationLevels
     */
    protected function configureIsolationLevel($connection, array $config)
    {
        if (isset($config['isolation_level']) && $config['isolation_level'] === 'off') {
            return;
        }

        $connection->prepare('SET SESSION aurora_read_replica_read_committed = ON')->execute();

        $connection->prepare('SET SESSION TRANSACTION ISOLATION LEVEL read committed')->execute();
    }

    
    protected function configureReadConsistency($connection, array $config)
    {
        
        if (isset($config['read_consistency']) && $config['read_consistency'] === 'off') {
            return;
        }
        
        $connection->prepare('SET aurora_replica_read_consistency = "session"')->execute();

    }

}