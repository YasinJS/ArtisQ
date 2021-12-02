<?php

namespace gameking2319\ArtisQ\sql;

use gameking2319\ArtisQ\ArtisQ;
use gameking2319\ArtisQ\table\Table;

final class TableQueryBuilder
{

    /** @var string */
    public const BUILD = "build";
    /** @var string */
    public const DROP = "drop";

    /**
     * @param Table $table
     * @param string $action
     */
    public function __construct(Table $table, string $action = "build")
    {
        switch($action){
            case self::BUILD:
                $this->createTable($table);
                break;
            case self::DROP:
                $this->dropTable($table);
                break;
            default:
                ArtisQ::getInstance()->getLogger()->info("Action with name: {$action}, not found.");
        }
    }

    /**
     * @param Table $table
     * @return void
     */
    public function createTable(Table $table): void
    {
        $name = $table->getName();
        $columns = $table->getColumns();

        $SQLColumns = [];
        $SQLColumns[] = "id INTEGER PRIMARY KEY AUTOINCREMENT";
        foreach ($columns as $key => $value){
            $column = "{$key} {$value['type']}";
            if(!$value['nullable']) $column .= " NOT NULL";
            $SQLColumns[] = $column;
        }

        $SQLColumns = implode(',', $SQLColumns);

        $instance = ArtisQ::getInstance();
        $instance->getDatabase()->query("CREATE TABLE IF NOT EXISTS {$name} ( {$SQLColumns} )");
    }

    /**
     * @param Table $table
     * @return void
     */
    public function dropTable(Table $table): void
    {
        $name = $table->getName();
        $instance = ArtisQ::getInstance();
        $instance->getDatabase()->query("DROP TABLE {$name}");
    }


}