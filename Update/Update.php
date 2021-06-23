<?php


declare(strict_types=1);

namespace Sigmie\Support\Update;

use Sigmie\Base\Analysis\Analyzer as AnalysisAnalyzer;
use Sigmie\Base\Analysis\CharFilter\HTMLFilter;
use Sigmie\Base\Analysis\CharFilter\MappingFilter;
use Sigmie\Base\Analysis\CharFilter\PatternFilter;
use Sigmie\Base\Analysis\DefaultAnalyzer;
use Sigmie\Base\Analysis\DefaultCharFilters;
use Sigmie\Base\Analysis\DefaultFilters;
use Sigmie\Base\Analysis\TokenizeOn;
use Sigmie\Base\Contracts\Analyzer;
use Sigmie\Base\Contracts\Mappings as ContractsMappings;
use Sigmie\Base\Contracts\Tokenizer;
use function Sigmie\Helpers\ensure_collection;
use function Sigmie\Helpers\named_collection;
use Sigmie\Support\Analysis\AnalyzerUpdate;
use Sigmie\Support\Analysis\Tokenizer\Builder as TokenizerBuilder;

use Sigmie\Support\Contracts\Collection;
use Sigmie\Support\Shared\Mappings;

class Update
{
    use Mappings, DefaultFilters, TokenizeOn, DefaultCharFilters;

    protected int $replicas = 2;

    protected int $shards = 1;

    protected ?Tokenizer $tokenizer = null;

    protected array $charFilter = [];

    protected array $settingsConf = [];

    protected Collection $analyzers;

    protected array $analyzerUpdates = [];

    protected DefaultAnalyzer $defaultAnalyzer;

    public function __construct(Collection|array $analyzers)
    {
        $this->analyzers = ensure_collection($analyzers);
    }

    public function analyzer(string $name): AnalyzerUpdate
    {
        if (!isset($this->analyzers[$name])) {
            $this->analyzers[$name] = new AnalysisAnalyzer($name);
        }

        $analyzer = $this->analyzers[$name];

        $this->analyzersUpdates[$name] = new AnalyzerUpdate($analyzer, $name);

        return  $this->analyzersUpdates[$name];
    }

    public function analyzers(): Collection
    {
        return ensure_collection($this->analyzerUpdates)->map(function (AnalyzerUpdate $analyzerUpdate) {
            return $analyzerUpdate->analyzer();
        })->mapToDictionary(function (Analyzer $analyzer) {
            return [$analyzer->name() => $analyzer];
        });

        return $this->analyzers;
    }

    public function charFilters(): array
    {
        return $this->defaultCharFilters();
    }

    private function getAnalyzer(): Analyzer
    {
        return $this->analyzers['default'];
    }

    public function tokenizer(Tokenizer $tokenizer)
    {
        $this->tokenizer = $tokenizer;

        return $this;
    }

    public function shards(int $shards)
    {
        $this->shards = $shards;

        return $this;
    }

    public function replicas(int $replicas)
    {
        $this->replicas = $replicas;

        return $this;
    }

    public function defaultAnalyzer(): Analyzer
    {
        return $this->defaultAnalyzer;
    }

    public function tokenizerValue(): ?Tokenizer
    {
        return $this->tokenizer;
    }

    public function mappingsValue(): ContractsMappings
    {
        return $this->createMappings($this->analyzers['default']);
    }

    public function toRaw()
    {
        $mappings = $this->mappingsValue();

        return [
            'settings' => [
                'number_of_shards' => $this->shards,
                'number_of_replicas' => $this->replicas,
            ],
            'mappings' => $mappings->toRaw()
        ];
    }
}
