<?php


namespace Tests\Unit;


use OctavianParalescu\UatSeeder\UatSeeder;
use OctavianParalescu\UatSeeder\WikiDataConverter;
use PHPUnit\Framework\TestCase;

class WikiDataConverterTest extends TestCase
{
    public function testShouldOnlyReturnJudeteQuery()
    {
        $params = [UatSeeder::FLAG_JUDETE, UatSeeder::FLAG_JUDETE_SIRUTA];

        $object = new WikiDataConverter();
        $query = $object->buildQuery($params);

        $expected = 'SELECT DISTINCT ?countyLabel ?countySirutaId WHERE { SERVICE wikibase:label { bd:serviceParam wikibase:language "ro" . } .VALUES ?typesOfCounties { wd:Q1776764 wd:Q10864048 } .VALUES ?typesOfTowns { wd:Q15921300 wd:Q640364 wd:Q659103 wd:Q16858213 } .?county wdt:P131* wd:Q218 .?county wdt:P31 ?typesOfCounties .{?county wdt:P150 ?town} UNION {?county wdt:P1383 ?town} .?town wdt:P31 ?typesOfTowns .OPTIONAL { ?county wdt:P843 ?countySirutaId .} }';

        $this->assertEquals($expected, $query);
    }

    public function testShouldOnlyReturnJudeteAndOraseComune()
    {
        $params = [UatSeeder::FLAG_JUDETE, UatSeeder::FLAG_JUDETE_SIRUTA, UatSeeder::FLAG_UAT];

        $object = new WikiDataConverter();
        $query = $object->buildQuery($params);

        $expected = 'SELECT DISTINCT ?countyLabel ?countySirutaId ?townLabel WHERE { SERVICE wikibase:label { bd:serviceParam wikibase:language "ro" . } .VALUES ?typesOfCounties { wd:Q1776764 wd:Q10864048 } .VALUES ?typesOfTowns { wd:Q15921300 wd:Q640364 wd:Q659103 wd:Q16858213 } .?county wdt:P131* wd:Q218 .?county wdt:P31 ?typesOfCounties .{?county wdt:P150 ?town} UNION {?county wdt:P1383 ?town} .?town wdt:P31 ?typesOfTowns .OPTIONAL { ?county wdt:P843 ?countySirutaId .} }';

        $this->assertEquals($expected, $query);
    }

    public function testShouldConvertToSimpleArray()
    {
        $initialData = json_decode(
            "[ {
      \"countySirutaId\" : {
        \"type\" : \"literal\",
        \"value\" : \"305\"
      },
      \"countyLabel\" : {
        \"xml:lang\" : \"ro\",
        \"type\" : \"literal\",
        \"value\" : \"Satu Mare\"
      }
    }, {
      \"countySirutaId\" : {
        \"type\" : \"literal\",
        \"value\" : \"10\"
      },
      \"countyLabel\" : {
        \"xml:lang\" : \"ro\",
        \"type\" : \"literal\",
        \"value\" : \"Alba\"
      }
    }]",
            true
        );

        $object = new WikiDataConverter();
        $result = $object->convertResponseToArray($initialData);

        $expected = [
            0 => ['countySirutaId' => '305', 'countyLabel' => 'Satu Mare'],
            1 => ['countySirutaId' => '10', 'countyLabel' => 'Alba'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testShouldMapData()
    {
        $mapping = ['countyLabel' => 'judet', 'countySirutaId' => 'id'];
        $data = [
            [
                'countyLabel' => 'Neamț',
                'countySirutaId' => 21
            ],
            [
                'countyLabel' => 'Bacău',
                'countySirutaId' => 21
            ],
        ];

        $object = new WikiDataConverter();
        $result = $object->mapData($data, $mapping);

        $expected = [
            [
                'judet' => 'Neamț',
                'id' => 21
            ],
            [
                'judet' => 'Bacău',
                'id' => 21
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    public function testShouldThrowExceptionWhenUnsupportedParameterIsUsed()
    {
        $mapping = ['countyLabeltypo' => 'judet', 'countySirutaId' => 'id'];
        $data = [
            [
                'countyLabel' => 'Neamț',
                'countySirutaId' => 21
            ],
            [
                'countyLabel' => 'Bacău',
                'countySirutaId' => 21
            ],
        ];

        $this->expectExceptionMessageMatches('/Could not find initial parameter name/');

        $object = new WikiDataConverter();
        $object->mapData($data, $mapping);
    }
}
