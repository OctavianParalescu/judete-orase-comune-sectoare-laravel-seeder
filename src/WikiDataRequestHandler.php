<?php


namespace OctavianParalescu\UatSeeder;


use GuzzleHttp\Client;

class WikiDataRequestHandler
{
    const WIKIDATA_URL = 'https://query.wikidata.org/sparql?format=json';

    public function retrieveWikiDataResults(string $sparqlQuery, bool $isCacheEnabled = true)
    {
        if ($isCacheEnabled) {
            $fileCache = __DIR__ . DIRECTORY_SEPARATOR . md5($sparqlQuery) . '.cache';
            if (file_exists($fileCache)) {
                return json_decode(file_get_contents($fileCache), true);
            }
        }

        $url = self::WIKIDATA_URL;

        $client = new Client();
        $headers = [
            'accept-encoding' => 'gzip, deflate',
        ];

        if (strlen($sparqlQuery) > 2000 || !$isCacheEnabled) {
            // Post request for larger queries
            $formParams = [
                'query' => $sparqlQuery,
            ];
            $options = [
                'headers' => $headers,
                'form_params' => $formParams,
            ];
            $response = $client->request(
                'POST',
                $url,
                $options
            );
        } else {
            // Get request
            $queryString = http_build_query(
                [
                    'query' => $sparqlQuery,
                ]
            );
            $url .= "&$queryString";
            $options = [
                'headers' => $headers,
            ];
            $response = $client->request(
                'GET',
                $url,
                $options
            );
        }

        $body = ($response)->getBody();

        if ($isCacheEnabled) {
            file_put_contents($fileCache, $body);
        }

        $data = json_decode($body, true);

        return $data;
    }
}
