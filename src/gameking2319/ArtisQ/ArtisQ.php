<?php

namespace gameking2319\ArtisQ;

use pocketmine\plugin\PluginBase;
use SQLite3;

final class ArtisQ extends PluginBase
{

    /** @var SQLite3 */
    private SQLite3 $database;
    /** @var ArtisQ */
    private static ArtisQ $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
        $this->database = new SQLite3($this->getDataFolder() . "database.db");
    }

    /**
     * @return SQLite3
     */
    public function getDatabase(): SQLite3
    {
        return $this->database;
    }

    /**
     * @return ArtisQ
     */
    public static function getInstance(): ArtisQ
    {
        return self::$instance;
    }

}