<?php

namespace RunAsRoot\PrometheusExporter\Aggregator;

use RunAsRoot\PrometheusExporter\Api\MetricAggregatorInterface;
use RunAsRoot\PrometheusExporter\Api\MetricCollectorRegistryInterface;

abstract class AbstractMetricAggregator implements MetricAggregatorInterface
{
    protected $namespace;
    protected $code;
    protected $help;
    protected $labels;
    protected $metricCollectorRegistry;

    public function __construct(
        string $namespace,
        string $code,
        string $help,
        MetricCollectorRegistryInterface $metricCollectorRegistry,
        array $labels = []
    ) {
        $this->namespace = $namespace;
        $this->code = $code;
        $this->help = $help;
        $this->labels = $labels;
        $this->metricCollectorRegistry = $metricCollectorRegistry;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getHelp(): string
    {
        return $this->help;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }
}
