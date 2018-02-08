# SteamProfileReader

This PHP class provides a tool to fetch static data from the Steam User profile page based on a provided Steam Custom ID

## Requirements

- PHP 5.6 or greater
- MySql database ( optional )

## Installation

 1. Copy or clone the reporitory
 2. Place the SteamProfileReader.php, PersistenceDB.php and config.php in the same folder
 3. Include the SteamProfileReader.php inside a dedicated page or in any place of your code
 4. Change the setting into the config.php to match your installation

## Steam Custom ID 

The Steam Custom ID differs from the SteamID . The Steam Custom ID refers to the ID used by Steam to identify an user profile page :
```http://steamcommunity.com/id/<steamCustomId>```
This ID could be totally different from the Steam ID and it's choosen by the user itself from the profile editor page

![Profile Editor](http://bigm.it/assets/media/steameditor.png)

## Run the tool

 1. Create a new instance of the SteamProfileReader : 
 ```$spr = new SteamProfileReader('<Steam Custom ID>');```
 2. Fetch content live or save it into your database

## Live fetching

 When using the live fetch, the tool read the Steam page profile based on the Steam Custom ID provided into the constructor, and retrive the content from the page. Using different methods it's possible to obtain specific part of the user profile :

 1. $steamProfileReader->getFavGame() - retrive an object that containts the favorite game selected displayed on the user profile
    	Structure of the FavGame object :
```
            $game->img  	   : Favourite game image url to Steam page
            $game->link 	   : Favourite game url to the Steam page
            $game->name 	   : Favourite game name
            $game->time 	   : Favourite hours played
            $game->achivNumber : Favourite number of achievements
```
 2. $steamProfileReader->getLastPlayed() - retrive an array of objects containing the last games played displayed on the user profile
    	Structure of the LastPlayed object :
```
            $game->image      : Game image url
            $game->totTime    : Game total hours played
            $game->lastPlayed : Game last 'day' played
            $game->name       : Game name
            $game->page       : Game url to Steam page
```
 3. $steamProfileReader->getMedals() - retrive an array of objects containing the displayed medals on the user profile
    	Structure of the Medal object :
```
            $medal->name  : Medal name 
            $medal->link  : Medal url to Steam page
            $medal->img   : Medal image url to Steam page
```
 4. $steamProfileReader->getAchivements() - retrive an array of objects containing the displayed achievements on the user profile
    	Structure of the Achievement object :
```
            $achievement->game        : Game name
            $achievement->name        : Achievement name
            $achievement->description : Achievement description
            $achievement->page        : Achievement url to Steam page
            $achievement->img         : Achievement image url to Steam page
```
 5. $steamProfileReader->getUserDetails() - retrive an objects containing the user profile details
 		Structure of the User object :
```
            $user->nickname  : User steam nickname
            $user->imgFull   : User image url to Steam page ( large format )
            $user->imgMid    : User image url to Steam page ( medium format )
            $user->imgSmall  : User image url to Steam page ( small format )
            $user->level     : User Steam level
            $user->badgeName : User profile badge name
            $user->badgePage : Badge profile url to Steam page
            $user->badgeImg  : Badge profile image url to Steam page
```

## Persistence on DB

 Is it possible to directly store all the previous information ( array and object ) into a predefined set of tables. To realize it :
```
$steamProfileReader = new SteamProfileReader('<Steam Custom ID>');
$steamProfileReader->saveOnDb();
```
 
 The function takes care of all the necessary steps to be able to store all the structure and the related information. Prior this, you have to define your system setting to be able to communicate with the database inside the config.php file : 
 1. Choose your prefix name for the tables : define(TABLEPREFIX,<prefix_name>)
 2. Six tables are created using the prefix name string : PREFIX_achievements, PREFIX_conf, PREFIX_favgames, PREFIX_medals, PREFIX_playedGames, PREFIX_users
 3. Running the function will fetch the data from the Steam profile page and store it in the related table. Each element of the table it's stored using as ID an MD5 values of the full set of information to avoid storing the same type of object more than once. 

 	 Tables follows this structure :

```
		//Conf table
		`id` varchar(255) NOT NULL,
		`Name` varchar(255) CHARACTER SET utf8 NOT NULL,
		`Value` varchar(255) CHARACTER SET utf8 NOT NULL,
		PRIMARY KEY , UNIQUE (id)

		//Users table
		`id` varchar(255) NOT NULL,
		`steamID` varchar(255) CHARACTER SET utf8 NOT NULL,
		`nickname` varchar(255) CHARACTER SET utf8 NOT NULL,
		`imgFull` varchar(255) CHARACTER SET utf8 NOT NULL,
		`imgMid` varchar(255) CHARACTER SET utf8 NOT NULL,
		`imgSmall` varchar(255) CHARACTER SET utf8 NOT NULL,
		`level` varchar(255) CHARACTER SET utf8 NOT NULL,
		`badgeName` varchar(255) CHARACTER SET utf8 NOT NULL,
		`badgePage` varchar(255) CHARACTER SET utf8 NOT NULL,
		`badgeImg` varchar(255) CHARACTER SET utf8 NOT NULL,                
		PRIMARY KEY UNIQUE (id)              

		//Favgames table
		`id` varchar(255) NOT NULL,
		`steamID` varchar(255) CHARACTER SET utf8 NOT NULL,
		`img` varchar(255) CHARACTER SET utf8 NOT NULL,
		`link` varchar(255) CHARACTER SET utf8 NOT NULL,
		`name` varchar(255) CHARACTER SET utf8 NOT NULL,
		`time` varchar(255) CHARACTER SET utf8 NOT NULL,
		`achivNumber` varchar(255) CHARACTER SET utf8 NOT NULL,
		PRIMARY KEY UNIQUE (id)              

		//PlayedGames table
		`id` varchar(255) NOT NULL,
		`steamID` varchar(255) CHARACTER SET utf8 NOT NULL,
		`image` varchar(255) CHARACTER SET utf8 NOT NULL,
		`totTime`     varchar(255) CHARACTER SET utf8 NOT NULL,
		`lastPlayed` varchar(255) CHARACTER SET utf8 NULL,
		`name` varchar(255) CHARACTER SET utf8 NULL,
		`page` varchar(255) CHARACTER SET utf8 NULL,                                
		PRIMARY KEY UNIQUE (id)              

		// Achievement table
		`id` varchar(255) NOT NULL,
		`steamID` varchar(255) CHARACTER SET utf8 NOT NULL,               
		`game` varchar(255) CHARACTER SET utf8 NOT NULL,
		`name` varchar(255) CHARACTER SET utf8 NOT NULL,
		`description` varchar(255) CHARACTER SET utf8 NOT NULL,
		`page` varchar(255) CHARACTER SET utf8 NOT NULL,
		`img` varchar(255) CHARACTER SET utf8 NOT NULL,
		PRIMARY KEY UNIQUE (id)              

		// Medals table
		`id` varchar(255) NOT NULL,
		`steamID` varchar(255) CHARACTER SET utf8 NOT NULL,
		`name` varchar(255) CHARACTER SET utf8 NOT NULL,              
		`link` varchar(255) CHARACTER SET utf8 NOT NULL,
		`img` varchar(255) CHARACTER SET utf8 NOT NULL,                            
		PRIMARY KEY UNIQUE (id)              
```
## Check User status
```   SteamProfileReader::checkUserStatus(<Steam Custom ID>); ```
 It is possible to receive the online status for a user, using the Steam Custom ID, calling the static method checkUserStatus. This will return his online status (Currently Online/Currently Offline) always based on the information provided by the Steam user page

# DISCLAIMER

 All contents fetched,loaded,read from Steam are protected by copyright and trademarks by Steam, the software owner and/or third party license . Please check [Legal](http://store.steampowered.com/legal/), [Privacy Policy](http://store.steampowered.com/privacy_agreement/), [User Agreement](http://store.steampowered.com/subscriber_agreement/) for further information
