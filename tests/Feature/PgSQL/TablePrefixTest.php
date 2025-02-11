<?php

namespace KitLoong\MigrationsGenerator\Tests\Feature\PgSQL;

use Illuminate\Support\Facades\DB;

class TablePrefixTest extends PgSQLTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('database.connections.pgsql.prefix', 'kit_');
    }

    public function testTablePrefix()
    {
        $migrateTemplates = function () {
            $this->migrateGeneral('pgsql');

            DB::statement(
                "ALTER TABLE kit_all_columns_pgsql ADD COLUMN status my_status NOT NULL"
            );
        };

        $generateMigrations = function () {
            $this->generateMigrations();
        };

        $this->verify($migrateTemplates, $generateMigrations);
    }

    private function verify(callable $migrateTemplates, callable $generateMigrations)
    {
        $migrateTemplates();

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('expected.sql'));

        $generateMigrations();

        $this->assertMigrations();

        $this->dropAllTables();

        $this->runMigrationsFrom('pgsql', $this->storageMigrations());

        $this->truncateMigration();
        $this->dumpSchemaAs($this->storageSql('actual.sql'));

        $this->assertFileEqualsIgnoringOrder(
            $this->storageSql('expected.sql'),
            $this->storageSql('actual.sql')
        );
    }
}
