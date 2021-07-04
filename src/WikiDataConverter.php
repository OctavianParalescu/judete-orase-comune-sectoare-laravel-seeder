<?php


namespace OctavianParalescu\UatSeeder;


class WikiDataConverter
{
    const TYPES_OF_TOWNS_QUERY = '{?county wdt:P150 ?town} UNION {?county wdt:P1383 ?town}';
    const COUNTY_SIRUTA_ID_QUERY = 'OPTIONAL { ?county wdt:P843 ?countySirutaId .}';

    public function buildQuery(array $params, $countySirutaFilter = null)
    {
        $select = [
            '?countySirutaId', // we allways need the siruta ids for request chunking purposes
        ];

        $where = [
            // https://www.wikidata.org/wiki/Wikidata:SPARQL_query_service/query_optimization
            'hint:Query  hint:optimizer "None"', // disable the optimiser
            'SERVICE wikibase:label { bd:serviceParam wikibase:language "ro" . }', // using  the labeling service
        ];

        if ($countySirutaFilter !== null) {
            $where = array_merge(
                $where,
                [
                    '?county wdt:P843 "' . $countySirutaFilter . '"', // filter by county siruta id
                    self::TYPES_OF_TOWNS_QUERY, // should contain admin territorial entity or settlement
                    '?town wdt:P1383 ?sate', // towns contain villages
                    self::COUNTY_SIRUTA_ID_QUERY, // always retrieve the siruta id of counties
                ]
            );
        } else {
            $where = array_merge(
                $where,
                [
                    'VALUES ?typesOfCounties { wd:Q1776764 wd:Q10864048 }',// judet or first-level admin country subdiv
                    'VALUES ?typesOfTowns { wd:Q15921300 wd:Q640364 wd:Q659103 wd:Q16858213 } .' . // sector/municipiu/oras/comuna
                    '?county wdt:P131* wd:Q218',// located in the administrative territory of Romania
                    self::TYPES_OF_TOWNS_QUERY,// should contain admin territorial entity or settlement
                    self::COUNTY_SIRUTA_ID_QUERY,// county siruta id
                    '?county wdt:P31 ?typesOfCounties',// instance of judet or first-level admin country subdiv
                    '?town wdt:P31 ?typesOfTowns',// sector/municipiu/oras/comuna
                ]
            );
        }

        foreach ($params as $param) {
            switch ($param) {
                case UatSeeder::FLAG_JUDETE:
                    {
                        $select [] = '?countyLabel';
                    }
                break;
                case UatSeeder::FLAG_JUDETE_TYPE:
                    {
                        $select [] = '?typesOfCountiesLabel';
                    }
                break;
                case UatSeeder::FLAG_UAT:
                    {
                        $select [] = '?townLabel';
                    }
                break;
                case UatSeeder::FLAG_UAT_TYPE:
                    {
                        $select [] = '?typesOfTownsLabel';
                    }
                break;
                case UatSeeder::FLAG_COORDS:
                    {
                        $select [] = '?coords';
                        $where [] = 'OPTIONAL { ?town wdt:P625 ?coords .}';
                    }
                break;
                case UatSeeder::FLAG_WEBSITE:
                    {
                        $select [] = '?website';
                        $where [] = 'OPTIONAL { ?town wdt:P856 ?website .}';
                    }
                break;
                case UatSeeder::FLAG_SIRUTA:
                    {
                        $select [] = '?sirutaId';
                        $where [] = 'OPTIONAL { ?town wdt:P843 ?sirutaId .}';
                    }
                break;
                case UatSeeder::FLAG_SATE:
                    {
                        $select [] = '?sateLabel';
                    }
                break;
                case UatSeeder::FLAG_SATE_COORDS:
                    {
                        $select [] = '?sateCoords';
                        $where [] = 'OPTIONAL { ?sate wdt:P625 ?sateCoords .}';
                    }
                break;
                case UatSeeder::FLAG_SATE_SIRUTA:
                    {
                        $select [] = '?sateSirutaId';
                        $where [] = 'OPTIONAL { ?sate wdt:P843 ?sateSirutaId .}';
                    }
                break;
            }
        }

        $query = 'SELECT DISTINCT ' . implode(' ', $select) . ' WHERE { ' . implode(' .', $where) . ' }';

        return $query;
    }

    public function convertResponseToArray(array $response)
    {
        foreach ($response as $item => $itemValues) {
            foreach ($itemValues as $key => $itemValue) {
                if (isset($itemValue['value'])) {
                    $response[$item][$key] = $itemValue['value'];
                } else {
                    throw new \Exception('Could not find key value when converting rdf ' . json_encode($itemValue));
                }
            }
        }

        return $response;
    }

    public function mapData(array $data, array $mapping)
    {
        foreach ($data as $key => $item) {
            foreach ($item as $sparqlKey => $value) {
                if (!in_array($sparqlKey, array_keys($mapping))) {
                    // Item was not requested
                    unset($data[$key][$sparqlKey]);
                }
            }
            foreach ($mapping as $from => $to) {
                if (isset($item[$from])) {
                    $var = $item[$from];

                    switch ($from) {
                        case UatSeeder::FLAG_JUDETE_SIRUTA:
                        case UatSeeder::FLAG_SIRUTA:
                            $var = intval($var);
                        break;
                    }

                    if (!is_string($to)) {
                        $var = $to['function']($var);
                        $to = $to['name'];
                    }

                    $data[$key][$to] = $var;
                    if ($from !== $to) {
                        unset($data[$key][$from]);
                    }
                } else {
                    throw new \Exception('[MAP] Could not find initial parameter name ' . $from);
                }
            }
        }

        return $data;
    }
}
