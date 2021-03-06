<?php
/**
 * @copyright see PROJECT_LICENSE.txt
 *
 * @see PROJECT_LICENSE.txt
 */

namespace RunAsRoot\PrometheusExporter\Test\Unit\Aggregator\Order;

use Magento\Framework\Api\Search\SearchResult;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RunAsRoot\PrometheusExporter\Aggregator\Order\OrderAmountAggregator;
use RunAsRoot\PrometheusExporter\Service\UpdateMetricService;

class OrderAmountAggregatorUnitTest extends TestCase
{
    /**
     * @var OrderAmountAggregator
     */
    private $sut;

    /** @var UpdateMetricService|MockObject */
    private $updateMetricService;

    /** @var OrderRepositoryInterface|MockObject */
    private $orderRepository;

    /** @var SearchCriteriaBuilder|MockObject */
    private $searchCriteriaBuilder;

    /** @var StoreRepositoryInterface|MockObject */
    private $storeRepository;

    protected function setUp()
    {
        parent::setUp();

        $this->updateMetricService = $this->createMock(UpdateMetricService::class);
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->storeRepository = $this->createMock(StoreRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);

        $this->sut = new OrderAmountAggregator(
            $this->updateMetricService,
            $this->orderRepository,
            $this->storeRepository,
            $this->searchCriteriaBuilder
        );
    }

    public function testItShouldUpdateExistingMetric(): void
    {
        $storeId = 1;
        $storeCode = 'default';
        $totalCount = 1;
        $grandTotal = 47.11;
        $state = 'processing';
        $labels = ['state' => $state, 'store_code' => $storeCode];

        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $order = $this->createMock(Order::class);
        $order->expects($this->once())->method('getState')->willReturn($state);
        $order->expects($this->once())->method('getGrandTotal')->willReturn($grandTotal);
        $order->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $searchResult = $this->createMock(SearchResult::class);
        $searchResult->expects($this->once())->method('getTotalCount')->willReturn($totalCount);
        $searchResult->expects($this->once())->method('getItems')->willReturn([$order]);

        $this->orderRepository->expects($this->once())->method('getList')->with($searchCriteria)
                              ->willReturn($searchResult);

        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getCode')->willReturn($storeCode);

        $this->storeRepository->expects($this->once())->method('getById')->with($storeId)->willReturn($store);

        $this->updateMetricService->expects($this->once())->method('update')
                                  ->with($this->sut->getCode(), $grandTotal, $labels);

        $result = $this->sut->aggregate();

        $this->assertTrue($result);
    }

    public function testItShouldUpdateExistingMetricByState(): void
    {
        $storeId = 1;
        $storeCode = 'default';
        $totalCount = 2;
        $stateOne = 'processing';
        $stateTwo = 'pending';
        $grandTotalOne = 47.11;
        $grandTotalTwo = 88.11;
        $labelsOne = ['state' => $stateOne, 'store_code' => $storeCode];
        $labelsTwo = ['state' => $stateTwo, 'store_code' => $storeCode];

        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $order = $this->createMock(Order::class);
        $order->expects($this->once())->method('getState')->willReturn($stateOne);
        $order->expects($this->once())->method('getGrandTotal')->willReturn($grandTotalOne);
        $order->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $orderTwo = $this->createMock(Order::class);
        $orderTwo->expects($this->once())->method('getState')->willReturn($stateTwo);
        $orderTwo->expects($this->once())->method('getGrandTotal')->willReturn($grandTotalTwo);
        $orderTwo->expects($this->once())->method('getStoreId')->willReturn($storeId);

        $orders = [$order, $orderTwo];

        $searchResult = $this->createMock(SearchResult::class);
        $searchResult->expects($this->once())->method('getTotalCount')->willReturn($totalCount);
        $searchResult->expects($this->once())->method('getItems')->willReturn($orders);

        $this->orderRepository->expects($this->once())->method('getList')->with($searchCriteria)
                              ->willReturn($searchResult);

        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->exactly(2))->method('getCode')->willReturn($storeCode);

        $this->storeRepository->expects($this->exactly(2))->method('getById')->with($storeId)->willReturn($store);

        $this->updateMetricService->expects($this->exactly(2))->method('update')
                                  ->withConsecutive(
                                      [$this->sut->getCode(), $grandTotalOne, $labelsOne],
                                      [$this->sut->getCode(), $grandTotalTwo, $labelsTwo]
                                  );

        $result = $this->sut->aggregate();

        $this->assertTrue($result);
    }

    public function testItShouldStopIfThereAreNoOrders(): void
    {
        $totalCount = 0;

        $searchCriteria = $this->createMock(SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects($this->once())->method('create')->willReturn($searchCriteria);

        $searchResult = $this->createMock(SearchResult::class);
        $searchResult->expects($this->once())->method('getTotalCount')->willReturn($totalCount);
        $searchResult->expects($this->never())->method('getItems');

        $this->orderRepository->expects($this->once())->method('getList')->with($searchCriteria)
                              ->willReturn($searchResult);

        $this->updateMetricService->expects($this->never())->method('update');

        $result = $this->sut->aggregate();

        $this->assertTrue($result);
    }
}
