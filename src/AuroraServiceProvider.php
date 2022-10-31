<?php declare(strict_types=1);

namespace Ashishrcrm\Aurora;

use Illuminate\Database\Connection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\ServiceProvider;

final class AuroraServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->defaultKeyLength();

        $this->registerAuroraDriver();
    }



    private function registerAuroraDriver(): void
    {
        // We register an `aurora` driver which extends the MySQL drivers from Laravel.
        // The only thing we want to change is how the Transaction Isolation Level
        // are handled. We need to enable `aurora_read_replica_read_committed`
        // before we can enable read committed and skip the history length.
        $factory = function ($connection, $database, $prefix, $config) {
            return new MySqlConnection($connection, $database, $prefix, $config);
        };

        Connection::resolverFor('aurora', $factory);

        $this->app->bind('db.connector.aurora', AuroraConnector::class);
    }

    private function defaultKeyLength(): void
    {
        Builder::defaultStringLength(191);
    }
}
