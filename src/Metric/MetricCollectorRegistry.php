<?php

namespace RunAsRoot\PrometheusExporter\Metric;

use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use RunAsRoot\PrometheusExporter\Aggregator\AbstractCounterMetricAggregator;
use RunAsRoot\PrometheusExporter\Aggregator\AbstractGaugeMetricAggregator;
use RunAsRoot\PrometheusExporter\Aggregator\AbstractHistogramMetricAggregator;
use RunAsRoot\PrometheusExporter\Api\MetricCollectorRegistryInterface;
use RunAsRoot\PrometheusExporter\Metric\Prometheus\CollectorRegistryFactory;

class MetricCollectorRegistry implements MetricCollectorRegistryInterface
{
    protected $collectorRegistry;

    public function __construct(CollectorRegistryFactory $collectorRegistryFactory)
    {
        $this->collectorRegistry = $collectorRegistryFactory->create();
    }

    public function getMetricFamilySamples(): array
    {
        return $this->collectorRegistry->getMetricFamilySamples();
    }

    public function getOrRegisterGauge(AbstractGaugeMetricAggregator $aggregator): Gauge
    {
        return $this->collectorRegistry->getOrRegisterGauge(
            $aggregator->getNamespace(),
            $aggregator->getCode(),
            $aggregator->getHelp(),
            $aggregator->getLabels()
        );
    }

    public function getOrRegisterCounter(AbstractCounterMetricAggregator $aggregator): Counter
    {
        return $this->collectorRegistry->getOrRegisterCounter(
            $aggregator->getNamespace(),
            $aggregator->getCode(),
            $aggregator->getHelp(),
            $aggregator->getLabels()
        );
    }

    public function getOrRegisterHistogram(AbstractHistogramMetricAggregator $aggregator): Histogram
    {
        return $this->collectorRegistry->getOrRegisterHistogram(
            $aggregator->getNamespace(),
            $aggregator->getCode(),
            $aggregator->getHelp(),
            $aggregator->getLabels()
        );
    }
}
