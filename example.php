<?php
include 'SteamProfileReader.php';

$spr = new SteamProfileReader('<steamID>');

echo "Favourite Games";
echo '<pre>';
print_r($src->getFavGame());
echo '</pre>';
echo "<br><br>Featured Games <br><br>";
echo '<pre>';
print_r($src->getFeaturedGames());
echo '</pre>';
echo "<br><br>Medal <br><br>";
echo '<pre>';
print_r($src->getMedals());
echo '</pre>';
echo "<br><br>Achi<br><br>";
echo '<pre>';
print_r($src->getAchivements());
echo '</pre>';
echo "<br><br>User<br><br>";
echo '<pre>';
print_r($src->getUserDetails());
echo '</pre>';
// Uncomment to store data into DB
//$spr->saveOnDb();
?>

