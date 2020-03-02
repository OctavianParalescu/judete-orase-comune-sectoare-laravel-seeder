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
    /**
     * DB table name
     * @var string
     */
    public $table;
    /**
     * An SQL INSERT query will execute every time this number of rows
     * are read from the WikiData result. Without this, large INSERTS will silently
     * fail.
     * @var int
     */
    public $insertChunkSize = 50;
    /**
     * The mapping of WikiData to DB column.
     * @var array
     */
    public $mapping = [];
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
        $this->seed($this->table, $this->mapping, $this->insertChunkSize);
    }

    public function seed(string $table, array $mapping, int $insertChunkSize)
    {
        // Create query
        $flags = array_keys($mapping);
        $query = $this->wikiDataConverter->buildQuery($flags);

        // Get data from cache or WikiData
        $rdfData = $this->wikiDataRequestHandler->retrieveWikiDataResults($query);

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
