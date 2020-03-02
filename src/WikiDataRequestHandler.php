<?php


namespace OctavianParalescu\UatSeeder;


use GuzzleHttp\Client;

class WikiDataRequestHandler
{
    const WIKIDATA_URL = 'https://query.wikidata.org/sparql?';

    public function retrieveWikiDataResults(string $sparqlQuery)
    {
        $fileCache = __DIR__ . DIRECTORY_SEPARATOR . md5($sparqlQuery) . '.cache';
        if (file_exists($fileCache)) {
            return json_decode(file_get_contents($fileCache), true);
        }
        $query = http_build_query(
            [
                'format' => 'json',
                'query' => $sparqlQuery,
            ]
        );

        $url = self::WIKIDATA_URL . $query;

        $client = new Client();

        $body = ($client->request('GET', $url))->getBody();

        file_put_contents($fileCache, $body);

        $data = json_decode($body, true);

        return $data;
    }
}
