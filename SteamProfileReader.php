<?php
/**
 * SteamProfileReader v1.0
 */
require 'PersistenceDB.php';

class SteamProfileReader
{
    private $steamID;
    private $strLang;

    private $pageElement;

    private $lastPlayed;
    private $achievements;
    private $medals;
    private $favGame;
    private $userData;

    private $closingTags = array('" >', '">', '/>', '</a>', '<br>', '</div>');

    /**
     * SteamProfileReader constructor.
     *
     * Fetch the Steam user ID and set the default language to english then load all the content from Steam user page
     *
     * @param $strSteamID
     */
    public function __construct($strSteamID)
    {
        $this->steamID = $strSteamID;
        $this->strLang = 'english';
        $this->loadContent();
    }

    /**
     * Static function - fetch the Steam user ID online status
     *
     * @param $steamID
     * @return string
     */
    public static function checkUserStatus($steamID) {
        $status = 'null';
        $file = file('http://steamcommunity.com/id/' . $steamID);
        foreach ($file as $key => $string) {
            if (preg_match("/responsive_status_info/", $string)) {
                $status = trim(strip_tags($file[$key+2]));
                break;
            }
        }
        return $status;
    }

    /**
     * Function to collect all the different data from the Steam user profile based on the Steam ID and the selected language
     *
     * @return void
     */
    private function loadContent() {
        $this->pageElement = file('http://steamcommunity.com/id/' . $this->steamID . '?l=' . $this->strLang);
        $this->fetchUserData();
        $this->fetchPlayedGames();
        $this->fetchAchievements();
        $this->fetchMedals();
        $this->fetchFavouriteGame();
    }

    /**
     * Fetch user data from the Steam user page
     *
     * @return void
     */
    private function fetchUserData() {
        $user = new stdClass();
        $background = null;
        foreach ($this->pageElement as $key => $string) {
            if (preg_match("/profile_header_content/", $string)) {
                $user->nickname = trim(strip_tags($this->pageElement[$key + 4]));

                $img = explode('<img src="',$this->pageElement[$key + 26]);
                $user->imgFull = trim(str_replace($this->closingTags,'',$img[1]));
                $user->imgMid  = str_replace('_full','_medium',$user->imgFull);
                $user->imgSmall= str_replace('_medium','',$user->imgMid);

                $user->level = trim(strip_tags($this->pageElement[$key + 31]));
                $user->badgeName = trim(str_replace($this->closingTags,'',$this->pageElement[$key + 36]));

                $link = explode('href="',$this->pageElement[$key + 37]);
                $user->badgePage = trim(str_replace($this->closingTags,'',$link[1]));
                $img = explode('src="',$this->pageElement[$key + 38]);
                $user->badgeImg  = trim(str_replace(array_merge($this->closingTags,array('" class="badge_icon small')),'',$img[1]));
            }
        }

        $this->userData = $user;
    }

    /**
     * Fetch played games from the Steam user page
     *
     * @return void
     */
    private function fetchPlayedGames()
    {
        foreach ($this->pageElement as $key => $string) {
            if (preg_match("/recent_game_content/", $string)) {
                $game = new stdClass();

                $img = explode('<img src="', $this->pageElement[$key + 2]);
                $img = str_replace($this->closingTags, '', trim($img[1]));
                $game->image = $img;

                $rawTiming = str_replace($this->closingTags, '', $this->pageElement[$key + 4]);
                $game->totTime    = trim($rawTiming);
                $game->lastPlayed = strip_tags($this->pageElement[$key + 5]);
                $game->lastPlayed = trim(preg_replace('/\s\s+/', ' ', $game->lastPlayed));

                $name = explode('<div class="game_name"><a class="whiteLink" href="', $this->pageElement[$key + 6]);
                $name = explode('">', $name[1]);
                $game->name = trim(str_replace($this->closingTags, '', $name[1]));
                $game->page = $name[0];

                $this->lastPlayed[] = $game;
            }
        }
    }

    /**
     * Fetch user achievements from the Steam user page
     *
     * @return void
     */
    private function fetchAchievements()
    {
        foreach ($this->pageElement as $key => $string) {
            if (preg_match("/showcase_achievement/", $string)) {
                $achievement = new stdClass();

                $game = explode('data-community-tooltip="', $this->pageElement[$key].$this->pageElement[$key+1]);
                if (!isset($game[1])) {
                    continue;
                }
                $game = explode('<br>', $game[1]);
                $achievement->game = trim($game[0]);
                $achievement->name = trim($game[1]);

                if(preg_match("/href\=/",$this->pageElement[$key + 3])) {
                    $achievement->description = trim(str_replace($this->closingTags,'',$this->pageElement[$key + 2]));

                    $link = explode('<a href="', $this->pageElement[$key + 3]);
                    $achievement->page = trim(str_replace($this->closingTags,'',$link[1]));

                    $img = explode('<img src="', $this->pageElement[$key + 4]);
                    $achievement->img = trim(str_replace($this->closingTags, '', $img[1]));
                } else {
                    $link = explode('<a href="', $this->pageElement[$key + 1]);
                    $achievement->page = trim(str_replace($this->closingTags, '', $link[1]));

                    $img = explode('<img src="', $this->pageElement[$key + 2]);
                    $achievement->img = trim(str_replace($this->closingTags, '', $img[1]));

                    $percent = explode('">',$game[2]);
                    $achievement->description = $percent[0];
                }

                if (!empty($achievement->game)) {
                    $this->achievements[] = $achievement;
                }
            }
        }
    }

    /**
     * Fetch user medals from the Steam user page
     *
     * @return void
     */
    private function fetchMedals()
    {
        foreach ($this->pageElement as $key => $string) {
            if (preg_match("/showcase_slot showcase_badge/", $string)) {
                $medal = new stdClass();

                $medal->name = preg_replace("/[^A-Za-z0-9 ]/",'',$this->pageElement[$key + 1]);
                $medal->link = trim(str_replace($this->closingTags,'',str_replace('<a href="','',$this->pageElement[$key + 2])));
                $medal->img = trim(str_replace($this->closingTags,'',str_replace(array('<img src="','" class="badge_icon'),'',$this->pageElement[$key + 3])));

                if (!empty($medal)) {
                    $this->medals[] = $medal;
                }
            }
        }
    }

    /**
     * Fetch user favourite game from the Steam user page
     *
     * @return void
     */
    private function fetchFavouriteGame()
    {
        foreach ($this->pageElement as $key => $string) {
            if (preg_match("/favoritegame_showcase_game/", $string)) {
                $game = new stdClass();
                $game->img  = trim(str_replace($this->closingTags,'',str_replace('<img src="','',$this->pageElement[$key+3])));

                $link = explode('href="',$this->pageElement[$key+7]);
                $game->link = trim(str_replace($this->closingTags,'',$link[1]));

                $game->name = trim(str_replace($this->closingTags,'',$this->pageElement[$key+8]));

                $totTime = preg_replace("/[^0-9]/",'',trim(strip_tags($this->pageElement[$key+14])));
                $game->time = trim($totTime.' '.trim(strip_tags($this->pageElement[$key+15])));

                $game->achivNumber = preg_replace("/[^0-9]/",'',strip_tags($this->pageElement[$key+18]));

                $this->favGame = $game;
                break;
            }
        }
    }

    /**
     * Initialize the PersistenceDB object to perform persistence of data into the database
     *
     * @return void
     */
    public function saveOnDb() {
        $dataset = array(
            '_users' =>$this->userData,
            '_favgames'=>$this->favGame,
            '_playedGames'=>$this->lastPlayed,
            '_achievements'=>$this->achievements,
            '_medals'=>$this->medals
        );

        $dbLink = new PersistenceDB($this->steamID);
        $dbLink->persistentSave($dataset);
    }

    public function getLastPlayed() { return $this->lastPlayed; }
    public function getAchievements() { return $this->achievements; }
    public function getMedals() { return $this->medals; }
    public function getFavGame() { return $this->favGame; }

    /**
     * Return the array of all the available Steam languages
     *
     * @return array
     */
    public function getLanguages()
    {
        return array('danish', 'dutch', 'english', 'finnish', 'french', 'german', 'greek', 'hungarian', 'italian', 'japanese', 'koreana', 'norwegian', 'polish', 'portuguese',
            'brazilian', 'romanian', 'russian', 'schinese', 'spanish', 'swedish', 'tchinese', 'thai', 'turkish', 'ukrainian', 'czech');
    }

    /**
     * Set the language for the Steam user page
     *
     * @param $strLang
     */
    public function setLang($strLang)
    {
        $this->strLang = $strLang;
        $this->loadContent();
    }
}

?>