<?php
/**
 * @author Informatique Prog (contact@informatiqueprog.net)
 * @copyright (c) 2008 - 2018 Informatique Prog
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CarriersDelivery\EventListeners;

use CarriersDelivery\CarriersDelivery;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Api\Bridge\Propel\Event\DeliveryModuleOptionEvent;
use Thelia\Api\Resource\DeliveryModuleOption;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Module\Exception\DeliveryException;

/**
 * Exposes the computed postage as a delivery option, which is what the checkout lists.
 */
class DeliveryOptionListener implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'module.CarriersDelivery')]
        private readonly CarriersDelivery $module,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            TheliaEvents::MODULE_DELIVERY_GET_OPTIONS => ['addDeliveryOption', 128],
        ];
    }

    public function addDeliveryOption(DeliveryModuleOptionEvent $event): void
    {
        $module = $event->getModule();
        $country = $event->getCountry();

        if ($module->getCode() !== CarriersDelivery::getModuleCode() || null === $country) {
            return;
        }

        try {
            if (!$this->module->isValidDelivery($country)) {
                return;
            }

            $orderPostage = $this->module->getPostage($country);
        } catch (DeliveryException) {
            return;
        }

        $postage = (float) $orderPostage->getAmount();
        $postageTax = (float) $orderPostage->getAmountTax();

        $event->appendDeliveryModuleOptions(
            (new DeliveryModuleOption())
                ->setCode(CarriersDelivery::getModuleCode())
                ->setValid(true)
                ->setTitle((string) ($module->getTitle() ?: $module->getCode()))
                ->setDescription('')
                ->setImage('')
                ->setPostage($postage)
                ->setPostageTax($postageTax)
                ->setPostageUntaxed($postage - $postageTax)
        );
    }
}
