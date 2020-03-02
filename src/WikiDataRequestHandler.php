<?php


namespace OctavianParalescu\UatSeeder;


use GuzzleHttp\Client;

class WikiDataRequestHandler
{
    const WIKIDATA_URL = 'https://query.wikidata.org/sparql?';

    public function retrieveWikiDataResults(string $sparqlQuery)
    {
        $query = http_build_query(
            [
                'format' => 'json',
                'query' => $sparqlQuery,
            ]
        );

        $url = self::WIKIDATA_URL . $query;

        $client = new Client();

        $body = ($client->request('GET', $url))->getBody();
        $data = json_decode($body, true);

        return $data;
    }
}
