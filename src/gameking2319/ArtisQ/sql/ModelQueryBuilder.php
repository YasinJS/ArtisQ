<?php

namespace gameking2319\ArtisQ\sql;

use gameking2319\ArtisQ\ArtisQ;
use gameking2319\ArtisQ\model\Model;

final class ModelQueryBuilder
{
    /** @var string */
    public const CREATE = "create";
    /** @var string */
    public const DELETE = "delete";
    /** @var string */
    public const UPDATE = "update";
    /** @var string */
    public const GET = "get";

    /** @var Model[] | array  */
    private array | Model $models;

    /**
     * @param Model $model
     * @param string $action
     */
    public function __construct(Model $model, string $action = "get")
    {
        switch ($action){
            case self::CREATE:
                $this->createModel($model);
                break;
            case self::DELETE:
                $this->deleteModel($model);
                break;
            case self::UPDATE:
                $this->updateModel($model);
                break;
            case self::GET:
                $this->getData($model);
                break;
            default:
                ArtisQ::getInstance()->getLogger()->info("Action with name: {$action}, not found.");
        }
    }

    /**
     * @param Model $model
     * @return void
     */
    public function getData(Model $model): void
    {
        $table = $model->getTable();
        $conditions = $model->getConditions();
        $statements = [];
        $values = [];

        foreach ($conditions as $key => $value){
            $statements[] = "{$key} {$value[1]} :{$key}";
            $values[":{$key}"] = $value[0];
        }

        $statements = implode(' AND ', $statements);
        $query = "SELECT * FROM {$table} WHERE {$statements}";

        $instance = ArtisQ::getInstance();
        $stmt = $instance->getDatabase()->prepare($query);
        if($stmt === false)return;
        foreach ($values as $key => $value){
            $stmt->bindValue($key, $value);
        }

        $models = [];

        $res = $stmt->execute();
        if($res === false)return;

        while($info = $res->fetchArray()){
            $class = get_class($model);
            $model = new $class();
            $model->setSaved(true);
            foreach ($info as $key => $value){
                $model->set($key, $value);
            }
            $models[] = $model;
        }

        $model->resetConditions();

        // Look if there is only 1
        if(count($models) === 1)
            $this->models = $models[0];
        else
            $this->models = $models;
    }

    /**
     * @return Model[] | Model
     */
    public function getModels(): array | Model
    {
        return $this->models;
    }

    public function deleteModel(Model $model): void
    {
        if(!$model->isSaved())return;
        $table = $model->getTable();
        $id = $model->getId();

        $query = "DELETE FROM {$table} WHERE id = :id";

        $instance = ArtisQ::getInstance();
        $stmt = $instance->getDatabase()->prepare($query);
        if($stmt === false)return;
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    }

    /**
     * @param Model $model
     * @return void
     */
    public function updateModel(Model $model): void
    {
        $instance = ArtisQ::getInstance();

        $properties = get_object_vars($model);
        $id = $model->getId();
        $table = $model->getTable();

        $keys = array_keys($properties);

        $i = 0;
        foreach ($keys as $key){
            if(gettype($key) !== "string")unset($keys[$i]);
            $i++;
        }
        $update = [];
        foreach ($keys as $value) {
            $update[] = $value.' = ' . ":" . $value;
        }

        $stmt = $instance->getDatabase()->prepare("UPDATE {$table} SET " . implode(', ', $update) . " WHERE id = :id");
        if($stmt === false)return;
        $stmt->bindParam(':id', $id);

        foreach ($keys as $keyName)
        {
            $key = ":" . $keyName;
            /** @phpstan-ignore-next-line */
            $value = $model->{$keyName};
            $stmt->bindValue($key, $value);

        }

        $stmt->execute();
    }

    /**
     * @param Model $model
     * @return void
     */
    public function createModel(Model $model): void{
        $instance = ArtisQ::getInstance();

        $properties = get_object_vars($model);
        $table = $model->getTable();

        $tableList = array_keys($properties);
        $valueList = array_values($properties);

        $tableString = implode(", ", $tableList);
        $questionMarkList = array_fill(0, count($valueList), "?");

        $stmt = $instance->getDatabase()->prepare("INSERT INTO " . $table . " ($tableString) VALUES (" . implode(", ", $questionMarkList).")");
        if($stmt === false)return;

        $i = 1;
        foreach ($valueList as $value){
            $stmt->bindParam($i, $valueList[$i - 1]);
            $i++;
        }

        $model->setSaved(true);
        $stmt->execute();
    }

}