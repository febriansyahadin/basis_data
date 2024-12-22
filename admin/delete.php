<?php
require '../news.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
 $db = getDB();
 $collection = $db->posts;
 $id = new MongoDB\BSON\ObjectId($_GET['id']);
 $deleteResult = $collection->deleteOne(['_id' => $id]);
 header("Location: adminpanel.php");
 exit();
}
?>
