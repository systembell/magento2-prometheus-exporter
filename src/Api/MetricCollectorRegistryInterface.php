<?php

namespace RunAsRoot\PrometheusExporter\Api;

use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use RunAsRoot\PrometheusExporter\Aggregator\AbstractCounterMetricAggregator;
use RunAsRoot\PrometheusExporter\Aggregator\AbstractGaugeMetricAggregator;
use RunAsRoot\PrometheusExporter\Aggregator\AbstractHistogramMetricAggregator;

interface MetricCollectorRegistryInterface
{
    /**
     * @return \Prometheus\MetricFamilySamples[]
     */
    public function getMetricFamilySamples() : array;

    public function getOrRegisterGauge(AbstractGaugeMetricAggregator $aggregator) : Gauge;

    public function getOrRegisterCounter(AbstractCounterMetricAggregator $aggregator) : Counter;

    public function getOrRegisterHistogram(AbstractHistogramMetricAggregator $aggregator) : Histogram;
}
