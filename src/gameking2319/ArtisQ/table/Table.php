<?php

namespace gameking2319\ArtisQ\table;

use Closure;
use gameking2319\ArtisQ\sql\TableQueryBuilder;

final class Table
{

    private string $name = "";
    /** @var array<string, string[]> */
    private array $columns = [];

    public function __construct(string $name, ?Closure $closure = null)
    {
        $this->name = $name;
        if($closure === null)return;

        $closure->__invoke($this);
        (new TableQueryBuilder($this, "build"));
    }

    public function string(string $name, bool $nullable = true): self
    {
        $this->columns[$name] = ["type"=> "TEXT", "nullable"=> $nullable];
        return $this;
    }

    public function int(string $name, bool $nullable = false): self
    {
        $this->columns[$name] = ["type"=> "INTEGER", "nullable"=> $nullable];
        return $this;
    }

    public function float(string $name, bool $nullable = false): self
    {
        $this->columns[$name] = ["type"=> "FLOAT", "nullable"=> $nullable];
        return $this;
    }

    public static function create(string $name, Closure $closure){
        return new self($name, $closure);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array<string, string[]>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public static function drop(string $name): void{
        (new TableQueryBuilder(new self($name), "drop"));
    }

}