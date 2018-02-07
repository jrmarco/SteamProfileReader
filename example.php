<?php
include 'SteamProfileReader.php';

$spr = new SteamProfileReader('<steamID>');

echo "Fav";
print_r($src->getFavGame());
echo "<br><br>Last <br><br>";
print_r($src->getLastPlayed());
echo "<br><br>Medal <br><br>";
print_r($src->getMedals());
echo "<br><br>Achi<br><br>";
print_r($src->getAchivements());
echo "<br><br>User<br><br>";
print_r($src->getUserDetails());

//Store into DB
$spr->saveOnDb();
?>

