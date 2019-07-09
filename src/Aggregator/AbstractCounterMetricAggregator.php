<?php

namespace RunAsRoot\PrometheusExporter\Aggregator;

use Prometheus\Counter;

abstract class AbstractCounterMetricAggregator extends AbstractMetricAggregator
{
    const METRIC_TYPE = 'counter';

    public function getType(): string
    {
        return self::METRIC_TYPE;
    }

    protected function getCollector() : Counter
    {
        return $this->metricCollectorRegistry->getOrRegisterCounter($this);
    }
}
