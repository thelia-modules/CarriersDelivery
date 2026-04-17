<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Loop;

use CarriersDelivery\Model\CarriersdeliveryAreascostskgQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class AreascostskgLoop extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions(): \Thelia\Core\Template\Loop\Argument\ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id'),
            Argument::createIntListTypeArgument('carrierarea_id'),
            Argument::createEnumListTypeArgument(
                'order',
                ['id', 'id_reverse', 'carrierarea_id', 'carrierarea_id_reverse'],
                'id'
            )
        );
    }

    /**
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria(): \Propel\Runtime\ActiveQuery\ModelCriteria
    {
        $search = CarriersdeliveryAreascostskgQuery::create();

        $id = $this->getId();

        if (!is_null($id)) {
            $search->filterById($id, Criteria::IN);
        }

        $carrierarea_id = $this->getCarrierareaId();

        if (!is_null($carrierarea_id)) {
            $search->filterByCarrierareaId($carrierarea_id, Criteria::IN);
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
                case 'carrierarea_id':
                    $search->orderByCarrierareaId(Criteria::ASC);
                    break;
                case 'carrierarea_id_reverse':
                    $search->orderByCarrierareaId(Criteria::DESC);
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
                ->set('CARRIERAREA_ID', $row->getCarrierareaId())
                ->set('WEIGHT_MAX', floatval($row->getWeightMax()))
                ->set('COST', floatval($row->getCost()))
            ;

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

}