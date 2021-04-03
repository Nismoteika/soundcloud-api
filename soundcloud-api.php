<?php
require 'vendor/autoload.php';
require 'config.php';

class SoundCloudApi
{
    public $client;

    public function __construct()
    {
        $this->client = new GuzzleHttp\Client(['base_uri' => BASE_URL]);
    }

    public function get_artist_info($artist_uri)
    {
        $response = $this->client->request('GET', '/' . $artist_uri);
        
        $body = $response->getBody();
        $dom = new DOMDocument;
        $dom->loadHTML($body);
        $xpath = new DomXPath( $dom );
        
        $nick_name = $xpath->query('//*[@id="app"]/noscript[2]/article/header/h1/a');
        $full_name = $xpath->query('//*[@id="app"]/noscript[2]/article/header/p[1]');
        $city = $xpath->query('//*[@id="app"]/noscript[2]/article/header/p[2]');
        $description = $xpath->query('//*[@id="app"]/noscript[2]/article/p');
        
        $params = [
            ':nick_name' => $nick_name[0]->nodeValue,
            ':full_name' => $full_name[0]->nodeValue,
            ':city' => $city[0]->nodeValue,
            ':description' => $description[0]->nodeValue,
        ];

        return $params;
    }

    public function get_tracks($artist_uri)
    {
        $response = $this->client->request('GET', '/' . $artist_uri . '/tracks');
        
        $body = $response->getBody();
        
        $dom = new DOMDocument;
        $dom->loadHTML($body);
        $xpath = new DomXPath( $dom );
    
        $arr_tracks_src = $xpath->query('//*[@id="app"]/noscript[2]/article/section/article');
        $arr_tracks_out = [];
    
        foreach($arr_tracks_src as $track) {
            $datetimeSrc = $track->getElementsByTagName('time')[0]->nodeValue;
            $date = date_create_from_format('Y-m-d\TH:i:s\Z', $datetimeSrc);

            $genre = '';
            if($track->getElementsByTagName('meta')[1] != null) {
                $genre = $track->getElementsByTagName('meta')[1]->getAttribute('content');
            }

            $params = [
                ':title' => $track->getElementsByTagName('a')[0]->nodeValue,
                ':published' => $date->format('Y-m-j H:i:s'),
                ':genre' => $genre,
            ];

            array_push($arr_tracks_out, $params);
        }

        return $arr_tracks_out;
    }
}