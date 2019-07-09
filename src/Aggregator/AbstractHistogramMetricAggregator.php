<?php

namespace RunAsRoot\PrometheusExporter\Aggregator;

use Prometheus\Histogram;

abstract class AbstractHistogramMetricAggregator extends AbstractMetricAggregator
{
    const METRIC_TYPE = 'histogram';

    public function getType(): string
    {
        return self::METRIC_TYPE;
    }

    protected function getCollector() : Histogram
    {
        return $this->metricCollectorRegistry->getOrRegisterHistogram($this);
    }
}
