<?php
require 'vendor/autoload.php';
function getDB() {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    return $client->Berita; // Mengembalikan koleksi 'comments'
}

?>
