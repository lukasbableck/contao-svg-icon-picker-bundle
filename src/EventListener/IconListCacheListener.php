<?php
namespace Lukasbableck\ContaoSVGIconPickerBundle\EventListener;

use Lukasbableck\ContaoSVGIconPickerBundle\Controller\IconListController;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE, priority: -1)]
class IconListCacheListener {
    public function __invoke(ResponseEvent $event): void {
        if (!$event->isMainRequest()) {
            return;
        }

        if (IconListController::class !== $event->getRequest()->attributes->get('_controller')) {
            return;
        }

        $event->getResponse()->setPrivate();
        $event->getResponse()->setMaxAge(3600);
    }
}
