<?php


namespace Tests\Feature;


use Illuminate\Foundation\Testing\TestCase;
use OctavianParalescu\UatSeeder\UatSeeder;
use OctavianParalescu\UatSeeder\WikiDataConverter;
use OctavianParalescu\UatSeeder\WikiDataRequestHandler;

class UatSeederTest extends TestCase
{
    /**
     * Setup the test environment.
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        // Create our testing DB tables
        $this->artisan(
            'migrate:fresh',
            [
                '--path' => 'vendor/octavianparalescu/judete-orase-comune-sectoare-laravel-seeder/',
            ]
        );

        $this->beforeApplicationDestroyed(
            function () {
                $this->artisan('migrate:rollback');
            }
        );
    }

    public function createApplication()
    {
        return require __DIR__ . '/../../bootstrap/app.php';
    }

    public function testShouldImportCounties()
    {
        $seeder = new UatSeeder(new WikiDataConverter(), new WikiDataRequestHandler());
        $seeder->table = 'test_counties';
        $seeder->mapping = [
            'countySirutaId' => 'id',
            'countyLabel' => 'name',
            'typesOfCountiesLabel' => 'type',
        ];
        $seeder->run();

        // Make sure the rows imported
        $this->assertDatabaseHas(
            'test_counties',
            [
                'id' => 270,
                'name' => 'Neamț',
                'type' => 'județ',
            ]
        );
    }

    public function testShouldImportCitiesLinkedToCounties()
    {
        $seeder = new UatSeeder(new WikiDataConverter(), new WikiDataRequestHandler());
        $seeder->table = 'test_counties';
        $seeder->mapping = [
            'countySirutaId' => 'id',
            'countyLabel' => 'name',
            'typesOfCountiesLabel' => 'type',
        ];
        $seeder->run();

        $seeder = new UatSeeder(new WikiDataConverter(), new WikiDataRequestHandler());
        $seeder->table = 'test_cities';
        $seeder->mapping = [
            'countySirutaId' => 'county_id',
            'townLabel' => 'name',
            'typesOfTownsLabel' => 'type',
            'sirutaId' => 'id',
        ];
        $seeder->run();

        // Make sure the rows imported
        $this->assertDatabaseHas(
            'test_cities',
            [
                'id' => 120726,
                'name' => 'Piatra Neamț',
                'type' => 'municipiu',
                'county_id' => 270,
            ]
        );
    }
}
