<?php
require_once 'soundcloud-api.php';
require 'config.php';

ini_set('display_errors', DEBUG_MODE);

try {

$dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

$api = new SoundCloudApi();

$artists_uri = [
    'lakeyinspired',
    'aljoshakonstanty',
    'birocratic',
    'dixxy-2',
    'dekobe',
];

function get_artist_id($nick_name, $dbh) {
    $query = 'SELECT `id`,`nick_name` FROM `media_artists` WHERE `nick_name` = ?';

    $stmt = $dbh->prepare($query);
    $res = $stmt->execute([$nick_name]);

    if($res > 0) {
        $row = $stmt->fetch(PDO::FETCH_LAZY);
        return $row->id;
    }
    return 0;
}

foreach($artists_uri as $artist_uri) {

    $params = $api->get_artist_info($artist_uri);

    $query = 'INSERT INTO `media_artists` (`nick_name`,`full_name`,`city`,`description`) 
              VALUES (:nick_name, :full_name, :city, :description)';
    $stmt = $dbh->prepare($query);
    $stmt->execute($params);

    $tracks = $api->get_tracks($artist_uri);

    foreach($tracks as $track_params) {
        $query_add_track = 'INSERT INTO `media_tracks` (`title`,`published`,`genre`, `artist_id`) 
        VALUES (:title, :published, :genre, :artist_id)';
        
        $artist_id = get_artist_id($params[':nick_name'], $dbh);
        $track_params[':artist_id'] = $artist_id;

        $stmt_track = $dbh->prepare($query_add_track);
        $stmt_track->execute($track_params);

        echo $track_params[':title'] . ' done<br>';
    }

    echo $artist_uri . ' done<br>';
}

echo "done.";


} catch (Exception $e) {
    print "Error!: " . $e->getMessage();
    die();
}