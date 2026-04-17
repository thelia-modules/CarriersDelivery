<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Loop;

use CarriersDelivery\Model\CarriersdeliveryAreasQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class AreasLoop extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions(): \Thelia\Core\Template\Loop\Argument\ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id'),
            Argument::createIntListTypeArgument('carrier_id'),
            Argument::createEnumListTypeArgument(
                'order',
                ['id', 'id_reverse', 'carrier_id', 'carrier_id_reverse', 'name', 'name_reverse'],
                'id'
            )
        );
    }

    /**
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria(): \Propel\Runtime\ActiveQuery\ModelCriteria
    {
        $search = CarriersdeliveryAreasQuery::create();

        $id = $this->getId();

        if (!is_null($id)) {
            $search->filterById($id, Criteria::IN);
        }

        $carrier_id = $this->getCarrierId();

        if (!is_null($carrier_id)) {
            $search->filterByCarrierId($carrier_id, Criteria::IN);
        }

        $orders = $this->getOrder();

        foreach ($orders as $order) {
            switch ($order) {
                case 'id':
                    $search->orderById(Criteria::ASC);
                    break;
                case 'id_reverse':
                    $search->orderById(Criteria::DESC);
                    break;
                case 'carrier_id':
                    $search->orderByCarrierId(Criteria::ASC);
                    break;
                case 'carrier_id_reverse':
                    $search->orderByCarrierId(Criteria::DESC);
                    break;
                case 'name':
                    $search->orderByName(Criteria::ASC);
                    break;
                case 'name_reverse':
                    $search->orderByName(Criteria::DESC);
                    break;
            }
        }

        return $search;
    }

    /**
     * @param LoopResult $loopResult
     *
     * @return LoopResult
     */
    public function parseResults(LoopResult $loopResult): LoopResult
    {
        foreach ($loopResult->getResultDataCollection() as $row) {
            $loopResultRow = new LoopResultRow($row);

            $loopResultRow
                ->set('ID', $row->getId())
                ->set('NAME', $row->getName())
                ->set('CARRIER_ID', $row->getCarrierId())
                ->set('DEPARTMENTS', $row->getDepartments())
            ;

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

}