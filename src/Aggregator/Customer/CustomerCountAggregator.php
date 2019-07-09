<?php
declare(strict_types=1);
/**
 * @copyright see PROJECT_LICENSE.txt
 *
 * @see PROJECT_LICENSE.txt
 */

namespace RunAsRoot\PrometheusExporter\Aggregator\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreRepositoryInterface;
use RunAsRoot\PrometheusExporter\Aggregator\AbstractGaugeMetricAggregator;
use RunAsRoot\PrometheusExporter\Api\MetricCollectorRegistryInterface;
use RunAsRoot\PrometheusExporter\Service\UpdateMetricService;

class CustomerCountAggregator extends AbstractGaugeMetricAggregator
{
    /**
     * @var UpdateMetricService
     */
    private $updateMetricService;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        string $namespace,
        string $code,
        string $help,
        MetricCollectorRegistryInterface $metricCollectorRegistry,
        UpdateMetricService $updateMetricService,
        CustomerRepositoryInterface $customerRepository,
        StoreRepositoryInterface $storeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $labels = []
    ) {
        parent::__construct($namespace, $code, $help, $metricCollectorRegistry, $labels);

        $this->updateMetricService = $updateMetricService;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeRepository = $storeRepository;
    }

    /**
     * @throws LocalizedException
     */
    public function aggregate()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $searchResult = $this->customerRepository->getList($searchCriteria);

        if ($searchResult->getTotalCount() === 0) {
            return true;
        }

        $customers = $searchResult->getItems();

        $countByStore = [];
        foreach ($customers as $customer) {
            $storeId = $customer->getStoreId();

            try {
                $store = $this->storeRepository->getById($storeId);
                $storeCode = $store->getCode();
            } catch (NoSuchEntityException $e) {
                $storeCode = $storeId;
            }

            if (!array_key_exists($storeCode, $countByStore)) {
                $countByStore[$storeCode] = 0;
            }

            $countByStore[$storeCode]++;
        }

        foreach ($countByStore as $storeCode => $count) {
            $this->getCollector()->set($count, [$storeCode]);
        }

        return true;
    }
}
