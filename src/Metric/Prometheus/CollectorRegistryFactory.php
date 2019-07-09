<?php

namespace RunAsRoot\PrometheusExporter\Metric\Prometheus;

use Prometheus\CollectorRegistry;

class CollectorRegistryFactory
{
    protected $adapterFactory;

    public function __construct(CollectorRegistryAdapterFactory $adapterFactory)
    {
        $this->adapterFactory = $adapterFactory;
    }

    public function create()
    {
        $adapter = $this->adapterFactory->create();
        return new CollectorRegistry($adapter);
    }
}
