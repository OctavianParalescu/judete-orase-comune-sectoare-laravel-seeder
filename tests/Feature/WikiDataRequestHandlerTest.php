<?php


namespace OctavianParalescu\UatSeeder;


use GuzzleHttp\Client;

class WikiDataRequestHandler
{
    const WIKIDATA_URL = 'https://query.wikidata.org/sparql?format=json';

    public function retrieveWikiDataResults(string $sparqlQuery)
    {
        $fileCache = __DIR__ . DIRECTORY_SEPARATOR . md5($sparqlQuery) . '.cache';
        if (file_exists($fileCache)) {
            return json_decode(file_get_contents($fileCache), true);
        }

        $url = self::WIKIDATA_URL;

        $client = new Client();

        $headers = [
            'accept-encoding' => 'gzip, deflate',
        ];
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
        $body = ($response)->getBody();

        file_put_contents($fileCache, $body);

        $data = json_decode($body, true);

        return $data;
    }
}