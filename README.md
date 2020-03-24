# judete-orase-comune-sectoare-laravel-seeder
Seeder for administrative territories in Romania that uses wikidata

## Installing

Use the composer cmd:
``
composer require octavianparalescu/judete-orase-comune-sectoare-laravel-seeder:dev-master
``
As you are probably using the seeder for development purposes, use the `--dev` option to save the package to require-dev:
``
composer require octavianparalescu/judete-orase-comune-sectoare-laravel-seeder:dev-master --dev
``
## Using

Create a seeder that extends the `UatSeeder` class, define the table that needs seeding and the mappings from WikiData to your table's columns:

```php
<?php

class CountiesSeeder extends OctavianParalescu\UatSeeder\UatSeeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $table = 'counties';
        $mapping = [
            'countySirutaId' => 'id',
            'countyLabel' => 'name',
            'typesOfCountiesLabel' => 'type',
        ];
        $insertChunkSize = 200;

        $this->seed($table, $mapping, $insertChunkSize);
    }
}
```

You can also use the `seed()` method multiple times in a single seeder (for example to seed both the counties and the cities):
```php
<?php

class CountiesSeeder extends OctavianParalescu\UatSeeder\UatSeeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $table = 'counties';
        $mapping = [
            'countySirutaId' => 'id',
            'countyLabel' => 'name',
            'typesOfCountiesLabel' => 'type',
        ];

        $this->seed($table, $mapping);

        $table = 'cities';
        $mapping = [
            'countySirutaId' => 'county_id',
            'townLabel' => 'name',
            'typesOfTownsLabel' => 'type',
            'sirutaId' => 'id',
        ];
        $insertChunkSize = 500;

        $this->seed($table, $mapping, $insertChunkSize);

        $this->seed(
            'sate',
            [
                'countySirutaId' => 'county_id',
                'sirutaId' => 'city_id',
                'sateLabel' => 'name',
                'sateCoords' => 'coords',
            ],
            500
        );

    }
}
```

As you can see the mappings are an array with the keys equal to flags defined by the seeder and the values being table columns defined in your migrations. The list of the flags is:
```
countyLabel - string
typesOfCountiesLabel - enum{diviziune administrativă de rangul întâi, județ}
countySirutaId - int
townLabel - string
typesOfTownsLabel - enum{comună, municipiu, oraș, sector al Bucureștiului}
sirutaId - int (town siruta id)
coords - string (format: Point(lat, long))
website - string
sateLabel - string
sateCoords - string (format: Point(lat, long))
sateSirutaId - int (village siruta id)
```
## ToDo:
- split coords to Lat/Long

