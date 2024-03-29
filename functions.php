<?php

declare(strict_types=1);

namespace Sigmie\Helpers {

    use Carbon\Carbon;
    use Exception;
    use Sigmie\Base\Contracts\DocumentCollection as DocumentCollectionInterface;
    use Sigmie\Base\Contracts\Name;
    use Sigmie\Base\Documents\Collection as DocumentsCollection;
    use Sigmie\Support\Collection;
    use Sigmie\Support\Contracts\Collection as CollectionInterface;

    function refresh_value(): string|null
    {
        $value = getenv('ES_REFRESH');

        if (function_exists('env')) {
            $value = env('ES_REFRESH');
        }

        return $value;
    }

    function index_name(string $prefix): string
    {
        $timestamp = Carbon::now()->format('YmdHisu');

        return "{$prefix}_{$timestamp}";
    }

    function random_letters(int $count = 3): string
    {
        $result = [];

        for ($x = 1; $x <= $count; $x++) {
            $result[] = chr(mt_rand(97, 122));
        }

        return implode('', $result);
    }

    function name_configs(array $values): array
    {
        if (count($values) > 1) {
            throw new Exception('Too many values in name configs');
        }

        [$name] = array_keys($values);
        [$configs] = array_values($values);

        return [$name, $configs];
    }

    function named_collection(array|CollectionInterface $values): CollectionInterface
    {
        $collection = ensure_collection($values);

        return $collection->mapToDictionary(fn (Name $item) => [$item->name() => $item]);
    }

    function ensure_collection(array|CollectionInterface $values): CollectionInterface
    {
        if ($values instanceof Collection) {
            return $values;
        }

        return new Collection($values);
    }

    function collection(array $values): CollectionInterface
    {
        return new Collection($values);
    }

    function mustache_var(string $variable, string $default = ''): string
    {
        if ($default !== '') {
            return "{{{$variable}}}{{^{$variable}}}{$default}{{/{$variable}}}";
        }

        return "{{{$variable}}}";
    }

    function auto_fuzziness(int $oneTypoChars = 3, int $twoTypoChars = 6): string
    {
        return "AUTO:{$oneTypoChars},{$twoTypoChars}";
    }

    function ensure_doc_collection(array|CollectionInterface|DocumentCollectionInterface $values): DocumentCollectionInterface
    {
        if ($values instanceof DocumentCollectionInterface) {
            return $values;
        }

        if ($values instanceof CollectionInterface) {
            return new DocumentsCollection($values->toArray());
        }

        return new DocumentsCollection($values);
    }

    function is_text_field(string $string): bool
    {
        return in_array($string, ['search_as_you_type', 'text', 'completion']);
    }
}
