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
use RunAsRoot\PrometheusExporter\Api\MetricAggregatorInterface;
use RunAsRoot\PrometheusExporter\Service\UpdateMetricService;

class OrderItemAmountAggregator implements MetricAggregatorInterface
{
    private const METRIC_CODE = 'magento2_orders_items_amount_total';

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
        UpdateMetricService $updateMetricService,
        OrderRepositoryInterface $orderRepository,
        StoreRepositoryInterface $storeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->updateMetricService = $updateMetricService;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeRepository = $storeRepository;
    }

    public function getCode(): string
    {
        return self::METRIC_CODE;
    }

    public function getHelp(): string
    {
        return 'Magento2 Order Items Amount by state';
    }

    public function getType(): string
    {
        return 'gauge';
    }

    /**
     * @return bool
     * @throws CouldNotSaveException
     *
     */
    public function aggregate(): bool
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $orderSearchResult = $this->orderRepository->getList($searchCriteria);

        if ($orderSearchResult->getTotalCount() === 0) {
            return true;
        }

        $orders = $orderSearchResult->getItems();

        $grandTotalsByStore = [];
        foreach ($orders as $order) {
            $storeId = $order->getStoreId();
            try {
                $store = $this->storeRepository->getById($storeId);
                $storeCode = $store->getCode();
            } catch (NoSuchEntityException $e) {
                $storeCode = $storeId;
            }

            if (!array_key_exists($storeCode, $grandTotalsByStore)) {
                $grandTotalsByStore[$storeCode] = [];
            }

            foreach ($order->getItems() as $orderItem) {
                /** @var $orderItem OrderItem */
                $status = (string)$orderItem->getStatus();

                if (!array_key_exists($status, $grandTotalsByStore[$storeCode])) {
                    $grandTotalsByStore[$storeCode][$status] = 0.0;
                }
                $grandTotalsByStore[$storeCode][$status] += $orderItem->getRowTotalInclTax();
            }
        }

        foreach ($grandTotalsByStore as $storeCode => $grandTotals) {
            foreach ($grandTotals as $status => $grandTotal) {
                $labels = ['status' => $status, 'store_code' => $storeCode];

                $this->updateMetricService->update(self::METRIC_CODE, (string)$grandTotal, $labels);
            }
        }

        return true;
    }
}
