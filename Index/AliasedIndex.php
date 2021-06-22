<?php

declare(strict_types=1);

namespace Sigmie\Support\Index;

use Exception;
use Sigmie\Base\Analysis\Analyzer;
use Sigmie\Base\Analysis\DefaultAnalyzer;
use Sigmie\Base\APIs\Index as IndexAPI;
use Sigmie\Base\APIs\Reindex;
use Sigmie\Base\Index\Index;
use Sigmie\Base\Mappings\Properties;
use function Sigmie\Helpers\index_name;
use Sigmie\Support\Update\Update;
use Sigmie\Base\Index\Settings;
use Sigmie\Base\Index\Mappings;

class AliasedIndex extends Index
{
    use Reindex, IndexAPI;

    public function __construct(
        string $identifier,
        protected string $alias,
        array $aliases,
        ?Settings $settings = null,
        ?Mappings $mappings = null,
    ) {
        parent::__construct($identifier, $aliases, $settings, $mappings);
    }

    public function update(callable $update): AliasedIndex
    {
        /** @var  Update $update */
        $update = $update(new Update($this->settings->analysis->analyzers()));

        if (is_null($update)) {
            throw new Exception('Did you forget to return ?');
        }

        $this->settings->analysis->addAnalyzers($update->analyzers());

        $oldDocsCount = count($this);

        $charFilters = $update->charFilters();

        $this->defaultAnalyzer()->addCharFilters($charFilters);

        // $oldTokenizers = $this->settings->analysis->tokenizers()->toArray();
        // $newTokenizer = $update->tokenizerValue();
        // $tokenizers = array_merge($oldTokenizers, [$newTokenizer->name() => $newTokenizer]);

        // $this->settings->analysis->updateTokenizers($tokenizers);

        $defaultAnalyzer =  $this->settings->analysis->defaultAnalyzer();
        $this->settings->analysis->setDefaultAnalyzer($defaultAnalyzer);

        $newProps = $update->mappingsValue()->properties()->toArray();
        $oldProps = $this->getMappings()->properties()->toArray();

        $props = array_merge($oldProps, $newProps);

        $newFilters = $update->defaultFilters();

        $this->settings->analysis->updateFilters($newFilters);

        $this->mappings = new Mappings(
            $this->settings->analysis->defaultAnalyzer(),
            new Properties($props)
        );

        $newName = index_name($this->alias) . 'new';
        $oldName = $this->identifier;

        $updateArray = $update->toRaw();

        $this->settings->primaryShards = $updateArray['settings']['number_of_shards'];

        $this->settings->replicaShards = 0;
        $this->settings->config('refresh_interval', '-1');

        $this->disableWrite($oldName);

        $this->identifier = $newName;
        $this->createIndex($this);

        $this->reindexAPICall($oldName, $newName);

        $this->indexAPICall("/{$newName}/_settings", 'PUT', [
            'number_of_replicas' => $updateArray['settings']['number_of_replicas'],
            'refresh_interval' => null
        ]);

        $this->switchAlias($this->alias, $oldName, $newName);
        $this->settings->replicaShards = $updateArray['settings']['number_of_replicas'];

        $this->deleteIndex($oldName);

        return $this->getIndex($this->alias);
    }

    public function disableWrite(): void
    {
        $this->indexAPICall("/{$this->name()}/_settings", 'PUT', [
            'index' => ['blocks.write' => true]
        ]);
    }

    public function enableWrite(): void
    {
        $this->indexAPICall("/{$this->name()}/_settings", 'PUT', [
            'index' => ['blocks.write' => false]
        ]);
    }

    protected function defaultAnalyzer(): Analyzer
    {
        return $this->settings->analysis->defaultAnalyzer();
    }
}
