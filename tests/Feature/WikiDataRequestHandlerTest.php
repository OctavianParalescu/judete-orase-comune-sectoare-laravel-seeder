<?php


namespace Tests\Feature;


use OctavianParalescu\UatSeeder\UatSeeder;
use OctavianParalescu\UatSeeder\WikiDataRequestHandler;
use PHPUnit\Framework\TestCase;

class WikiDataRequestHandlerTest extends TestCase
{

    public function testShouldOnlyRetrieveJudeteQuery()
    {
        $params = [UatSeeder::FLAG_JUDETE, UatSeeder::FLAG_JUDETE_SIRUTA];

        $object = new WikiDataRequestHandler();

        $query = 'SELECT DISTINCT ?countyLabel ?countySirutaId WHERE { SERVICE wikibase:label { bd:serviceParam wikibase:language "ro" . } .VALUES ?typesOfAdministrations { wd:Q1776764 wd:Q10864048 } .?county wdt:P131* wd:Q218 .?county wdt:P31 ?typesOfAdministrations .{?county wdt:P150 ?town} UNION {?county wdt:P1383 ?town} .OPTIONAL { ?county wdt:P843 ?countySirutaId .} }';

        $result = $object->retrieveWikiDataResults($query);

        $this->assertArrayHasKey('head', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('bindings', $result['results']);
        $this->assertCount(42, $result['results']['bindings']); // 42 total counties
    }

    public function testShouldOnlyRetrieveJudeteAndOraseComune()
    {
        $params = [UatSeeder::FLAG_JUDETE, UatSeeder::FLAG_JUDETE_SIRUTA, UatSeeder::FLAG_UAT];

        $object = new WikiDataRequestHandler();

        $query = 'SELECT DISTINCT ?countyLabel ?countySirutaId ?townLabel WHERE { SERVICE wikibase:label { bd:serviceParam wikibase:language "ro" . } .VALUES ?typesOfAdministrations { wd:Q1776764 wd:Q10864048 } .?county wdt:P131* wd:Q218 .?county wdt:P31 ?typesOfAdministrations .{?county wdt:P150 ?town} UNION {?county wdt:P1383 ?town} .OPTIONAL { ?county wdt:P843 ?countySirutaId .} }';

        $result = $object->retrieveWikiDataResults($query);

        $this->assertArrayHasKey('head', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('bindings', $result['results']);
        $this->assertEquals(3186, count($result['results']['bindings'])); // 3186 uats without judete and bucharest
    }
}
