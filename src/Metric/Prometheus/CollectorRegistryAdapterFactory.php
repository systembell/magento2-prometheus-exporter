<?php

namespace RunAsRoot\PrometheusExporter\Metric\Prometheus;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Prometheus\Storage\Adapter;
use Prometheus\Storage\APC as APCuAdapter;
use Prometheus\Storage\InMemory as InMemoryAdapter;
use Prometheus\Storage\Redis as RedisAdapter;
use RunAsRoot\PrometheusExporter\Model\Config\Source\CollectorRegistryAdapter as CollectorRegistryAdapterSource;

class CollectorRegistryAdapterFactory
{
    const PROMETHEUS_ADAPTER_XML_PATH = 'metric_configuration/adapter/adapter';
    const PROMETHEUS_REDIS_ADAPTER_HOST_XML_PATH = 'metric_configuration/adapter/redis_host';
    const PROMETHEUS_REDIS_ADAPTER_PORT_XML_PATH = 'metric_configuration/adapter/redis_port';
    const PROMETHEUS_REDIS_ADAPTER_PASSWORD_XML_PATH = 'metric_configuration/adapter/redis_password';
    const PROMETHEUS_REDIS_ADAPTER_TIMEOUT_XML_PATH = 'metric_configuration/adapter/redis_timeout';
    const PROMETHEUS_REDIS_ADAPTER_READ_TIMEOUT_XML_PATH = 'metric_configuration/adapter/redis_read_timeout';
    const PROMETHEUS_REDIS_ADAPTER_PERSISTENT_CONNECTIONS_XML_PATH = 'metric_configuration/adapter/redis_persistent_connections';
    const PROMETHEUS_REDIS_ADAPTER_PREFIX_XML_PATH = 'metric_configuration/adapter/redis_prefix';

    protected $scopeConfig;
    protected $adapterSource;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        CollectorRegistryAdapterSource $adapterSource
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->adapterSource = $adapterSource;
    }

    public function create() : Adapter
    {
        $adapter = null;
        $adapterKey = $this->scopeConfig->getValue(self::PROMETHEUS_ADAPTER_XML_PATH);

        if ($adapterKey !== null) {
            $adapter = $this->adapterSource->getAdapter($adapterKey);

            if ($adapter instanceof RedisAdapter) {
                $this->configureRedisAdapter();
            }
        }

        if ($adapter === null) {
            $adapter = $this->getDefaultAdapter();
        }

        return $adapter;
    }

    protected function getDefaultAdapter()
    {
        return new InMemoryAdapter();
    }

    protected function configureRedisAdapter()
    {
        $prefix = $this->scopeConfig->getValue(self::PROMETHEUS_REDIS_ADAPTER_PREFIX_XML_PATH);
        $options = array_filter([
            'host' => $this->scopeConfig->getValue(self::PROMETHEUS_REDIS_ADAPTER_HOST_XML_PATH),
            'port' => $this->scopeConfig->getValue(self::PROMETHEUS_REDIS_ADAPTER_PORT_XML_PATH),
            'password' => $this->scopeConfig->getValue(self::PROMETHEUS_REDIS_ADAPTER_PASSWORD_XML_PATH),
            'timeout' => $this->scopeConfig->getValue(self::PROMETHEUS_REDIS_ADAPTER_TIMEOUT_XML_PATH),
            'read_timeout' => $this->scopeConfig->getValue(self::PROMETHEUS_REDIS_ADAPTER_READ_TIMEOUT_XML_PATH),
            'persistent_connections' => $this->scopeConfig->getValue(self::PROMETHEUS_REDIS_ADAPTER_PERSISTENT_CONNECTIONS_XML_PATH)
        ]);

        if ($prefix !== null) {
            RedisAdapter::setPrefix($prefix);
        }

        RedisAdapter::setDefaultOptions($options);
    }
}
