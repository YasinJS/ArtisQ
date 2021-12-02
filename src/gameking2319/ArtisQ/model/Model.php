<?php

namespace gameking2319\ArtisQ\model;

use gameking2319\ArtisQ\sql\ModelQueryBuilder;

abstract class Model
{
    /** @var int */
    private int $id = 0;

    /** @var bool */
    private bool $saved = false;

    /** @var array<string, mixed> */
    private array $conditions = [];

    private function where(string $key, string $condition, mixed $value): static {
        $this->conditions[$key] = [$value, $condition];
        return $this;
    }

    /**
     * @param string $name
     * @param string[] $arguments
     * @return static
     */
    public static function __callStatic(string $name, array $arguments)
    {
        /** @phpstan-ignore-next-line */
        return (new static())->$name(...$arguments);
    }

    /**
     * @param string $name
     * @param string[] $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        /** @phpstan-ignore-next-line */
        return $this->$name(...$arguments);
    }

    public function save(): void {
        if($this->saved) (new ModelQueryBuilder($this, "update"));
        else (new ModelQueryBuilder($this, "create"));
    }

    /**
     * @return Model[] | Model
     */
    public function fetch(): array | Model {
        $this->setSaved(true);
        return (new ModelQueryBuilder($this, "get"))->getModels();
    }

    public function delete(): void {
        if(!$this->saved) return;
        (new ModelQueryBuilder($this, "delete"));
        $this->setSaved(false);
    }

    abstract public function getTable(): string;

    /**
     * @return array<string, mixed>
     */
    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function setSaved(bool $status = true): void
    {
        $this->saved = $status;
    }

    public function resetConditions(): void
    {
        $this->conditions = [];
    }

    public function isSaved(): bool
    {
        return $this->saved;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function set(string $key, mixed $value): void{
        /** @phpstan-ignore-next-line */
        $this->{$key} = $value;
    }

}