<?php

namespace OctavianParalescu\UatSeeder;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UatSeeder extends Seeder
{
    const FLAG_JUDETE = 'countyLabel';
    const FLAG_JUDETE_TYPE = 'typesOfCountiesLabel';
    const FLAG_JUDETE_SIRUTA = 'countySirutaId';
    const FLAG_UAT = 'townLabel';
    const FLAG_UAT_TYPE = 'typesOfTownsLabel';
    const FLAG_SIRUTA = 'sirutaId';
    const FLAG_COORDS = 'coords';
    const FLAG_WEBSITE = 'website';
    const FLAG_SATE = 'sateLabel';
    const FLAG_SATE_COORDS = 'sateCoords';
    const FLAG_SATE_SIRUTA = 'sateSirutaId';
    /**
     * @var WikiDataConverter
     */
    private $wikiDataConverter;
    /**
     * @var WikiDataRequestHandler
     */
    private $wikiDataRequestHandler;

    public function __construct(
        WikiDataConverter $wikiDataConverter,
        WikiDataRequestHandler $wikiDataRequestHandler
    ) {
        $this->wikiDataConverter = $wikiDataConverter;
        $this->wikiDataRequestHandler = $wikiDataRequestHandler;
    }

    /**
     * Run DB seed
     */
    public function run()
    {
        throw new \Exception(
            'Cannot run seeder ' . self::class . ' with default properties, please use the seed method'
        );
    }

    public function seed(string $table, array $mapping, int $insertChunkSize = 200, $useCache = true)
    {
        // If "sate" are required, first retrieve the cities so
        // we do the WikiData retrieval in batches per each city
        if (in_array(self::FLAG_SATE, array_keys($mapping))
            || in_array(self::FLAG_SATE_COORDS, array_keys($mapping))
            || in_array(self::FLAG_SATE_SIRUTA, array_keys($mapping))
        ) {
            $citiesQuery = $this->wikiDataConverter->buildQuery([self::FLAG_JUDETE_SIRUTA]);
            $citiesRdfData = $this->wikiDataRequestHandler->retrieveWikiDataResults($citiesQuery, $useCache);
            $cities = $this->wikiDataConverter->convertResponseToArray($citiesRdfData['results']['bindings']);

            foreach ($cities as $city) {
                $this->seedChunk(
                    $table,
                    $mapping,
                    $insertChunkSize,
                    $useCache,
                    $city[self::FLAG_JUDETE_SIRUTA]
                );
            }
        } else {
            $this->seedChunk($table, $mapping, $insertChunkSize, $useCache, null);
        }
    }

    /**
     * @param string $table
     * @param array  $mapping
     * @param int    $insertChunkSize
     * @param        $countiesDatum
     *
     * @throws \Exception
     */
    private function seedChunk(string $table, array $mapping, int $insertChunkSize, bool $useCache, $countySiruta): void
    {
        // Create query
        $flags = array_keys($mapping);
        $query = $this->wikiDataConverter->buildQuery($flags, $countySiruta);

        // Get data from cache or WikiData
        $rdfData = $this->wikiDataRequestHandler->retrieveWikiDataResults($query, $useCache);

        // Convert rdf data to array
        $data = $this->wikiDataConverter->convertResponseToArray($rdfData['results']['bindings']);

        // Do the mappings
        $mappedData = $this->wikiDataConverter->mapData($data, $mapping);

        // Insert into the table in batches
        foreach (array_chunk($mappedData, $insertChunkSize) as $dataChunk) {
            DB::table($table)->insert($dataChunk);
        }
    }
}
