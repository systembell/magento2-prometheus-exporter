<?php

namespace RunAsRoot\PrometheusExporter\Aggregator;

use Prometheus\Gauge;

abstract class AbstractGaugeMetricAggregator extends AbstractMetricAggregator
{
    const METRIC_TYPE = 'gauge';

    public function getType(): string
    {
        return self::METRIC_TYPE;
    }

    protected function getCollector() : Gauge
    {
        return $this->metricCollectorRegistry->getOrRegisterGauge($this);
    }
}
