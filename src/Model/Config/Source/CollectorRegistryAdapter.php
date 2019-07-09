<?php

namespace RunAsRoot\PrometheusExporter\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Prometheus\Storage\Adapter;

class CollectorRegistryAdapter implements OptionSourceInterface
{
    protected $adapters;

    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters;
    }

    public function toOptionArray()
    {
        return array_map(function ($key) {
            $adapter = $this->adapters[$key];
            return ['label' => $adapter['label'], 'value' => $key];
        }, array_keys($this->adapters));
    }

    public function getAdapters() : array
    {
        return $this->adapters;
    }

    public function getAdapter($key) : Adapter
    {
        $adapter = $this->adapters[$key] ?? null;
        return ($adapter ? $adapter['adapter'] : null);
    }
}
