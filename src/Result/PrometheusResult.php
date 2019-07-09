<?php

declare(strict_types=1);

/**
 * @copyright see PROJECT_LICENSE.txt
 *
 * @see PROJECT_LICENSE.txt
 */

namespace RunAsRoot\PrometheusExporter\Result;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Response\HttpInterface as HttpResponseInterface;
use Magento\Framework\Controller\Result\Raw;
use Prometheus\RenderTextFormat;
use RunAsRoot\PrometheusExporter\Api\Data\MetricInterface;
use RunAsRoot\PrometheusExporter\Api\MetricCollectorRegistryInterface;
use RunAsRoot\PrometheusExporter\Api\MetricRepositoryInterface;
use RunAsRoot\PrometheusExporter\Data\Config;
use RunAsRoot\PrometheusExporter\Metric\MetricAggregatorPool;
use RunAsRoot\PrometheusExporter\Metric\MetricCollectorRegistry;

class PrometheusResult extends Raw
{
    private $metricCollectorRegistry;
    /**
     * @var MetricRepositoryInterface
     */
    private $metricRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var MetricAggregatorPool
     */
    private $metricAggregatorPool;

    /**
     * @var \RunAsRoot\PrometheusExporter\Data\Config
     */
    private $config;

    private $renderTextFormat;

    public function __construct(
        MetricCollectorRegistryInterface $metricCollectorRegistry,
        MetricAggregatorPool $metricAggregatorPool,
        MetricRepositoryInterface $metricRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Config $config,
        RenderTextFormat $renderTextFormat
    ) {
        $this->metricCollectorRegistry = $metricCollectorRegistry;
        $this->metricRepository = $metricRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->metricAggregatorPool = $metricAggregatorPool;
        $this->config = $config;
        $this->renderTextFormat = $renderTextFormat;
    }

    protected function render(HttpResponseInterface $response)
    {
        $metrics = $this->renderTextFormat->render($this->metricCollectorRegistry->getMetricFamilySamples());

        $this->setContents($metrics);
        $this->setHeader('Content-Type', RenderTextFormat::MIME_TYPE);

        return parent::render($response);
    }
}
