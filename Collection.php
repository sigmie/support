<?php

declare(strict_types=1);

namespace Sigmie\Support\Collection;

use Illuminate\Support\Collection as LaravelCollection;

class Collection extends LaravelCollection
{
    public function __construct($expected, array $data)
    {
        $array = array_map(fn () => (new $expected)->populate($data), $data);

        parent::__construct($array);
    }
}
