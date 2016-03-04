
<?php

$dbName = isset($argv[1]) ? $argv[1] : "test-fixtures" ;

$client = new MongoClient(
    'mongodb//localhost:27017/' . $dbName
);
$client->connect();

$fixtureDir = __DIR__ . "/Fixtures";

$fixtures = array();

foreach(scandir($fixtureDir) as $fileName) {
    if (!in_array($fileName, array('.', '..'))) {

        include_once $fixtureDir . "/" . $fileName;
    }
}


foreach($fixtures as $collectionName => $documents) {
    $client->{$dbName}->$collectionName->remove();
    echo sprintf("Loading %s fixtures for collection %s on db\n", count($documents), $collectionName, $dbName);
    foreach($documents as $document) {
        $client->{$dbName}->$collectionName->insert($document);
    }
}


