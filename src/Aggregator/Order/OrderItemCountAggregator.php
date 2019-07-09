<?php

declare(strict_types=1);
/**
 * @copyright see PROJECT_LICENSE.txt
 *
 * @see PROJECT_LICENSE.txt
 */

namespace RunAsRoot\PrometheusExporter\Aggregator\Order;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Api\StoreRepositoryInterface;
use RunAsRoot\PrometheusExporter\Aggregator\AbstractGaugeMetricAggregator;
use RunAsRoot\PrometheusExporter\Api\MetricCollectorRegistryInterface;
use RunAsRoot\PrometheusExporter\Service\UpdateMetricService;

class OrderItemCountAggregator extends AbstractGaugeMetricAggregator
{
    /**
     * @var UpdateMetricService
     */
    private $updateMetricService;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    public function __construct(
        string $namespace,
        string $code,
        string $help,
        MetricCollectorRegistryInterface $metricCollectorRegistry,
        UpdateMetricService $updateMetricService,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $labels = []
    ) {
        parent::__construct($namespace, $code, $help, $metricCollectorRegistry, $labels);

        $this->updateMetricService = $updateMetricService;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeRepository = $storeRepository;
    }

    public function aggregate()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $orderSearchResult = $this->orderRepository->getList($searchCriteria);

        if ($orderSearchResult->getTotalCount() === 0) {
            return true;
        }

        $orders = $orderSearchResult->getItems();

        $countByStore = [];
        foreach ($orders as $order) {
            $storeId = $order->getStoreId();

            try {
                $store = $this->storeRepository->getById($storeId);
                $storeCode = $store->getCode();
            } catch (NoSuchEntityException $e) {
                $storeCode = $storeId;
            }

            if (!array_key_exists($storeCode, $countByStore)) {
                $countByStore[$storeCode] = [];
            }

            foreach ($order->getItems() as $orderItem) {
                /** @var $orderItem OrderItem */
                $status = (string)$orderItem->getStatus();

                if (!array_key_exists($status, $countByStore[$storeCode])) {
                    $countByStore[$storeCode][$status] = 0;
                }
                $countByStore[$storeCode][$status]++;
            }
        }

        foreach ($countByStore as $storeCode => $countByState) {
            foreach ($countByState as $status => $count) {
                $this->getCollector()->set($count, [$status, $storeCode]);
            }
        }

        return true;
    }
}
