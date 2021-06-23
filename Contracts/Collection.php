<?php

declare(strict_types=1);

namespace Sigmie\Support\Contracts;

use ArrayAccess;
use Closure;
use Countable;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use IteratorAggregate;

interface Collection extends Countable, IteratorAggregate, ArrayAccess
{
    public function deepen(int $depth = INF): static;

    public function flatten(int $depth = INF): static;

    public function flattenWithKeys(int $depth = 1): static;

    public function mapWithKeys(callable $callback): static;

    public function mapToDictionary(callable $callback): static;

    public function sortByKeys(): static;

    public function merge(Collection|array $values): static;

    public function slice(int $offset, int|null $length = null): static;

    public function clear(): static;

    public function each(Closure $p): static;

    public function filter(Closure $p): static;

    public function map(Closure $func): static;

    public function isEmpty(): bool;

    public function add(mixed $element): static;

    public function count(): int;

    public function set($key, $value): static;

    public function values(): array;

    public function keys(): array;

    public function get($key): mixed;

    public function indexOf($element): int|string;

    public function exists(Closure $p): bool;

    public function contains(mixed $element): bool;

    public function containsKey(string|int $key): bool;

    public function removeElement(mixed $element): static;

    public function current(): mixed;

    public function next(): mixed;

    public function key(): mixed;

    public function last(): mixed;

    public function first(): mixed;
}
