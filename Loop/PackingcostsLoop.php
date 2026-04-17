<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\Loop;

use CarriersDelivery\Model\CarriersdeliveryPackingcostsQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;

class PackingcostsLoop extends BaseLoop implements PropelSearchLoopInterface
{
    /**
     * @return \Thelia\Core\Template\Loop\Argument\ArgumentCollection
     */
    protected function getArgDefinitions(): \Thelia\Core\Template\Loop\Argument\ArgumentCollection
    {
        return new ArgumentCollection(
            Argument::createIntListTypeArgument('id'),
            Argument::createEnumListTypeArgument(
                'order',
                ['id', 'id_reverse', 'cost', 'cost_reverse', 'weight_max', 'weight_max_reverse'],
                'id'
            )
        );
    }

    /**
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function buildModelCriteria(): \Propel\Runtime\ActiveQuery\ModelCriteria
    {
        $search = CarriersdeliveryPackingcostsQuery::create();

        $id = $this->getId();

        if (!is_null($id)) {
            $search->filterById($id, Criteria::IN);
        }

        // $weight_max = $this->getWeightMax();
        //
        // if (!is_null($weight_max)) {
        //     $search->filterByWeightMax($weight_max, Criteria::IN);
        // }

        $orders = $this->getOrder();

        foreach ($orders as $order) {
            switch ($order) {
                case 'id':
                    $search->orderById(Criteria::ASC);
                    break;
                case 'id_reverse':
                    $search->orderById(Criteria::DESC);
                    break;
                case 'cost':
                    $search->orderByCost(Criteria::ASC);
                    break;
                case 'cost_reverse':
                    $search->orderByCost(Criteria::DESC);
                    break;
                case 'weight_max':
                    $search->orderByWeightMax(Criteria::ASC);
                    break;
                case 'weight_max_reverse':
                    $search->orderByWeightMax(Criteria::DESC);
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
                ->set('WEIGHT_MAX', $row->getWeightMax())
                ->set('COST', floatval($row->getCost()))
            ;

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }

}