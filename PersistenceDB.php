<?php

require 'config.php';

class PersistenceDB{

    private $pdo;
    private $steamID;

    /**
     * PersistenceDB constructor.
     *
     * Initialize the PDO connection and store the Steam user ID
     *
     * @param $steamID
     */
    public function __construct($steamID) {
        $this->createPDO();
        $this->steamID = $steamID;
    }

    /**
     * Create the PDO object
     *
     * @return void
     */
    private function createPDO() {
        try {
            $this->pdo = new PDO (''.DBDEAMON.':host='.DBHOST.';dbname='.DBNAME, DBUSER, DBPSW);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die('PDO Error');
        }
    }

    /**
     * Explode different array containing user related from the rest
     *
     * @param $arrDataset
     */
    public function persistentSave($arrDataset) {
        $this->verifyDBInit();

        $singleSet = ["_users"=>$arrDataset['_users'],"_favgames"=>$arrDataset['_favgames']];
        $this->saveUserRelated($singleSet);

        $arrSet = ["_playedGames"=>$arrDataset['_playedGames'],
                   "_achievements"=>$arrDataset['_achievements'],
                   "_medals"=>$arrDataset['_medals']];

        foreach ($arrSet as $table => $dataset ) {
            $this->saveArrObject($table,$dataset);
        }
    }

    /**
     * Fetch and insert the information about _users and _favgames table
     *
     * @param $arrData
     */
    private function saveUserRelated($arrData) {
        foreach ($arrData as $table => $arrMix) {
            $tableName = TABLEPREFIX . $table;

            $strField = '';
            $queryStr = '"'.$this->steamID.'",';
            if(!empty($arrMix)) {
                foreach ($arrMix as $key => $data) {
                    $strField .= "`$key`,";
                    $queryStr .= '"'.addslashes($data) . '",';
                }

                $strField = substr($strField,0,-1);
                $queryStr = substr($queryStr, 0, -1);

                $id = md5($queryStr);
                $queryStr = '"'.$id.'",'.$queryStr;

                $sql = "INSERT INTO $tableName (`id`,`steamID`,".$strField.") VALUES (" . $queryStr . ");";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                if(DEBUG) {
                    $time = date("Y-m-d h:i:s");
                    error_log("[".$time."]:".$sql."\n\n",3,'dbQueryVerbose.log');
                }
            }
        }
        $stmt = null;
    }

    /**
     * Fetch and insert the information about _playedGames , _achievements and _medals table
     *
     * @param $table
     * @param $arrObjects
     */
    private function saveArrObject($table,$arrObjects) {
        $tableName = TABLEPREFIX . $table;
        foreach ($arrObjects as $arrMix) {
            $strField = '';
            $queryStr = '"'.$this->steamID.'",';
            if(!empty($arrMix)) {
                foreach ($arrMix as $key => $data) {
                    $strField .= "`$key`,";
                    $queryStr .= '"'.addslashes($data) . '",';
                }

                $strField = substr($strField,0,-1);
                $queryStr = substr($queryStr,0,-1);

                $id = md5($queryStr);
                $queryStr = '"'.$id.'",'.$queryStr;

                $sql = "INSERT INTO $tableName (`id`,`steamID`,".$strField.") VALUES (" . $queryStr . ");";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                if(DEBUG) {
                    $time = date("Y-m-d h:i:s");
                    error_log("[".$time."]:".$sql."\n\n",3,__DIR__.'/dbQueryVerbose.log');
                }
            }
        }
        $stmt = null;
    }

    /**
     * Verify the existence of the necessary tables structure into the database
     *
     * @return void
     */
    private function verifyDBInit() {
        $sql  = "SHOW TABLES LIKE '".TABLEPREFIX."_conf'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        if($stmt->rowCount()==0) {
            $this->dbInit();
        }
    }

    /**
     * Create the structure of tables necessary to store all the fetched information
     *
     * @return void
     */
    private function dbInit() {
        $sql = 'CREATE TABLE `'.TABLEPREFIX.'_conf` (
              `id` varchar(255) NOT NULL,
              `Name` varchar(255) CHARACTER SET utf8 NOT NULL,
              `Value` varchar(255) CHARACTER SET utf8 NOT NULL,
              PRIMARY KEY (id)
            );';
        $sql .= 'ALTER TABLE `'.TABLEPREFIX.'_conf` ADD UNIQUE (`id`);';

        // UserData
        $sql .= "CREATE TABLE `".TABLEPREFIX."_users` (
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
              PRIMARY KEY (id)              
            );";
        $sql .= 'ALTER TABLE `'.TABLEPREFIX.'_users` ADD UNIQUE (`id`);';

        // Fav Games
        $sql .= "CREATE TABLE `".TABLEPREFIX."_favgames` (
                `id` varchar(255) NOT NULL,
                `steamID` varchar(255) CHARACTER SET utf8 NOT NULL,
                `img` varchar(255) CHARACTER SET utf8 NOT NULL,
                `link` varchar(255) CHARACTER SET utf8 NOT NULL,
                `name` varchar(255) CHARACTER SET utf8 NOT NULL,
                `time` varchar(255) CHARACTER SET utf8 NOT NULL,
                `achivNumber` varchar(255) CHARACTER SET utf8 NOT NULL,
              PRIMARY KEY (id)              
            );";
        $sql .= 'ALTER TABLE `'.TABLEPREFIX.'_favgames` ADD UNIQUE (`id`);';

        // LastPlayed table
        $sql .= 'CREATE TABLE `'.TABLEPREFIX.'_playedGames` (
              `id` varchar(255) NOT NULL,
              `steamID` varchar(255) CHARACTER SET utf8 NOT NULL,
              `image` varchar(255) CHARACTER SET utf8 NOT NULL,
              `totTime`     varchar(255) CHARACTER SET utf8 NOT NULL,
              `lastPlayed` varchar(255) CHARACTER SET utf8 NULL,
              `name` varchar(255) CHARACTER SET utf8 NULL,
              `page` varchar(255) CHARACTER SET utf8 NULL,                                
              PRIMARY KEY (id)              
            );';
        $sql .= 'ALTER TABLE `'.TABLEPREFIX.'_playedGames` ADD UNIQUE (`id`);';

        // Achievement table
        $sql .= 'CREATE TABLE `'.TABLEPREFIX.'_achievements` (
                  `id` varchar(255) NOT NULL,
                  `steamID` varchar(255) CHARACTER SET utf8 NOT NULL,               
                  `game` varchar(255) CHARACTER SET utf8 NOT NULL,
                  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
                  `description` varchar(255) CHARACTER SET utf8 NOT NULL,
                  `page` varchar(255) CHARACTER SET utf8 NOT NULL,
                  `img` varchar(255) CHARACTER SET utf8 NOT NULL,
                  PRIMARY KEY (id)              
                 );';
        $sql .= 'ALTER TABLE `'.TABLEPREFIX.'_achievements` ADD UNIQUE (`id`);';

        // Medals table
        $sql .= "CREATE TABLE `".TABLEPREFIX."_medals` (
              `id` varchar(255) NOT NULL,
              `steamID` varchar(255) CHARACTER SET utf8 NOT NULL,
              `name` varchar(255) CHARACTER SET utf8 NOT NULL,              
              `link` varchar(255) CHARACTER SET utf8 NOT NULL,
              `img` varchar(255) CHARACTER SET utf8 NOT NULL,                            
              PRIMARY KEY (id)              
            );";
        $sql .= 'ALTER TABLE `'.TABLEPREFIX.'_medals` ADD UNIQUE (`id`);';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stmt = null;
    }
}