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
                '--path' => 'vendor/octavianparalescu/judete-orase-comune-sectoare-laravel-seeder/tests/migrations',
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
        return require __DIR__ . '/../../../../../bootstrap/app.php';
    }

    public function testShouldImportCounties()
    {
        $seeder = new UatSeeder(new WikiDataConverter(), new WikiDataRequestHandler());
        $seeder->seed(
            'test_counties',
            [
                'countySirutaId' => 'id',
                'countyLabel' => 'name',
                'typesOfCountiesLabel' => 'type',
            ]
        );

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
        $seeder->seed(
            'test_counties',
            [
                'countySirutaId' => 'id',
                'countyLabel' => 'name',
                'typesOfCountiesLabel' => 'type',
            ]
        );

        $seeder = new UatSeeder(new WikiDataConverter(), new WikiDataRequestHandler());
        $seeder->seed(
            'test_cities',
            [
                'countySirutaId' => 'county_id',
                'townLabel' => 'name',
                'typesOfTownsLabel' => 'type',
                'sirutaId' => 'id',
            ]
        );

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

    public function testShouldImportSateLinkedToCities()
    {
        $seeder = new UatSeeder(new WikiDataConverter(), new WikiDataRequestHandler());
        $seeder->seed(
            'test_counties',
            [
                'countySirutaId' => 'id',
                'countyLabel' => 'name',
                'typesOfCountiesLabel' => 'type',
            ]
        );

        $seeder = new UatSeeder(new WikiDataConverter(), new WikiDataRequestHandler());
        $seeder->seed(
            'test_cities',
            [
                'countySirutaId' => 'county_id',
                'townLabel' => 'name',
                'typesOfTownsLabel' => 'type',
                'sirutaId' => 'id',
            ]
        );

        $seeder = new UatSeeder(new WikiDataConverter(), new WikiDataRequestHandler());
        $seeder->seed(
            'test_sate',
            [
                'countySirutaId' => 'county_id',
                'sirutaId' => 'city_id',
                'sateLabel' => 'name',
                'sateCoords' => 'coords',
            ]
        );

        // Make sure the rows imported
        $this->assertDatabaseHas(
            'test_sate',
            [
                'name' => 'Grigoreni',
            ]
        );
    }
}
