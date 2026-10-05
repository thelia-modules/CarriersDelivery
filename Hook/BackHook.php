<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Hook;

use CarriersDelivery\Model\CarriersdeliveryCarrierQuery;
use CarriersDelivery\Model\CarriersdeliveryOrderQuery;
use CarriersDelivery\Model\CarriersdeliveryProductQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class BackHook extends BaseHook
{
    public static function getSubscribedHooks(): array
    {
        return [
            'order-edit.delivery-module-bottom' => [
                ['type' => 'back', 'method' => 'onOrderEditDeliveryModuleBottom'],
            ],
            'product.modification.form-right.bottom' => [
                ['type' => 'back', 'method' => 'onProductModificationFormRightBottom'],
            ],
        ];
    }

    public function onProductModificationFormRightBottom(HookRenderEvent $event): void
    {
        $productId = (int) $event->getArgument('product_id');

        $carriers = CarriersdeliveryCarrierQuery::create()
            ->orderByName()
            ->find()
            ->toKeyValue('Id', 'Name');

        $selectedCarrierIds = array_map(
            'intval',
            CarriersdeliveryProductQuery::create()
                ->filterByProductId($productId)
                ->select(['CarrierId'])
                ->find()
                ->getData()
        );

        $event->add(
            $this->render(
                'CarriersDelivery/carriersdelivery-product.modification.form-right.bottom.html.twig',
                [
                    'product_id' => $productId,
                    'carriers' => $carriers,
                    'selected_carrier_ids' => $selectedCarrierIds,
                ]
            )
        );
    }

    public function onOrderEditDeliveryModuleBottom(HookRenderEvent $event): void
    {
        $orderId = $event->getArgument('order_id');

        $orders = CarriersdeliveryOrderQuery::create()
            ->filterByOrderId($orderId, Criteria::IN)
            ->orderByOrderId(Criteria::ASC)
            ->find();

        $carrierOrders = [];

        foreach ($orders as $order) {
            $carrierOrders[] = [
                'order_id' => $order->getOrderId(),
                'postage_log' => $order->getPostageLog(),
            ];
        }

        $event->add(
            $this->render(
                'CarriersDelivery/carriersdelivery-order-edit.delivery-module-bottom.html.twig',
                [
                    'order_id' => $orderId,
                    'carrier_orders' => $carrierOrders,
                ]
            )
        );
    }
}
