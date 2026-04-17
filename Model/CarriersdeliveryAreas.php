<?php

namespace CarriersDelivery\Model;

use CarriersDelivery\Model\Base\CarriersdeliveryAreas as BaseCarriersdeliveryAreas;
use Propel\Runtime\Connection\ConnectionInterface;

class CarriersdeliveryAreas extends BaseCarriersdeliveryAreas
{
    /**
     * @param ConnectionInterface|null $con
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function postInsert(?ConnectionInterface $con = null): void
    {
        $costsByWeight = CarriersdeliveryAreascostsQuery::getCostsByWeightForCarrier($this->getCarrierId());

        foreach ($costsByWeight['distinctWeights'] as $weight) {
            $areacost = new CarriersdeliveryAreascosts();

            $areacost
                ->setCarrierareaId($this->getId())
                ->setWeightMax($weight)
                ->setCost(0)
                ->save();
        }

        $costskgByWeight = CarriersdeliveryAreascostskgQuery::getCostskgByWeightForCarrier($this->getCarrierId());

        foreach ($costskgByWeight['distinctWeights'] as $weight) {
            $areacostkg = new CarriersdeliveryAreascostskg();

            $areacostkg
                ->setCarrierareaId($this->getId())
                ->setWeightMax($weight)
                ->setCost(0)
                ->save();
        }
    }

}
