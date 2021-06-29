<?php

declare(strict_types=1);

namespace Sigmie\Base\Analysis\TokenFilter;


use function Sigmie\Helpers\name_configs;

class Stopwords extends TokenFilter
{
    public function type(): string
    {
        return 'stop';
    }

    public static function fromRaw(array $raw): static
    {
        [$name, $configs] = name_configs($raw);

        $instance = new static($name, $configs['stopwords']);

        return $instance;
    }

    protected function getValues(): array
    {
        return [
            'stopwords' => $this->settings,
        ];
    }
}
