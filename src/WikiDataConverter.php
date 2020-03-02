<?php


namespace OctavianParalescu\UatSeeder;


class WikiDataConverter
{
    public function buildQuery(array $params)
    {
        $select = [];
        $where = [
            'SERVICE wikibase:label { bd:serviceParam wikibase:language "ro" . }', // using  the labeling service
            'VALUES ?typesOfCounties { wd:Q1776764 wd:Q10864048 }', // judet or first-level admin country subdiv
            'VALUES ?typesOfTowns { wd:Q15921300 wd:Q640364 wd:Q659103 wd:Q16858213 } .' . // sector/municipiu/oras/comuna
            '?county wdt:P131* wd:Q218', // located in the administrative territory of Romania
            '?county wdt:P31 ?typesOfCounties', // instance of judet or first-level admin country subdiv
            '{?county wdt:P150 ?town} UNION {?county wdt:P1383 ?town}', // should contain admin territorial entity or settlement
            '?town wdt:P31 ?typesOfTowns', // sector/municipiu/oras/comuna
        ];

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
                case UatSeeder::FLAG_JUDETE_SIRUTA:
                    {
                        $select [] = '?countySirutaId';
                        $where [] = 'OPTIONAL { ?county wdt:P843 ?countySirutaId .}'; // county should have a sirutaId
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
                case UatSeeder::FLAG_SIRUTA:
                    {
                        $select [] = '?sirutaId';
                        $where [] = 'OPTIONAL { ?town wdt:P843 ?sirutaId .}';
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
            foreach ($mapping as $from => $to) {
                if (isset($item[$from])) {
                    $var = $item[$from];

                    switch ($from) {
                        case UatSeeder::FLAG_JUDETE_SIRUTA:
                        case UatSeeder::FLAG_SIRUTA:
                            $var = intval($var);
                        break;
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
