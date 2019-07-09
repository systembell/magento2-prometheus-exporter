<?php

declare(strict_types=1);
/**
 * @copyright see PROJECT_LICENSE.txt
 *
 * @see PROJECT_LICENSE.txt
 */

namespace RunAsRoot\PrometheusExporter\Aggregator\Cms;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use RunAsRoot\PrometheusExporter\Aggregator\AbstractGaugeMetricAggregator;
use RunAsRoot\PrometheusExporter\Api\MetricCollectorRegistryInterface;
use RunAsRoot\PrometheusExporter\Service\UpdateMetricService;

class CmsBlockCountAggregator extends AbstractGaugeMetricAggregator
{
    /**
     * @var UpdateMetricService
     */
    private $updateMetricService;

    /**
     * @var BlockRepositoryInterface
     */
    private $cmsRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(
        string $namespace,
        string $code,
        string $help,
        MetricCollectorRegistryInterface $metricCollectorRegistry,
        UpdateMetricService $updateMetricService,
        BlockRepositoryInterface $cmsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $labels = []
    ) {
        parent::__construct($namespace, $code, $help, $metricCollectorRegistry, $labels);

        $this->updateMetricService = $updateMetricService;
        $this->cmsRepository = $cmsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aggregate()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $cmsSearchResult = $this->cmsRepository->getList($searchCriteria);

        $this->getCollector()->set($cmsSearchResult->getTotalCount());
    }
}
