<?php
/**
 * Created by Redi Linxa
 * Date: 05.12.19
 * Time: 16:54
 */

namespace EdcomsCMS\MaintenanceBundle\EventListener;

use EdcomsCMS\SettingsBundle\Service\SettingsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class MaintenanceListener
{
    private $settingsManager = null;
    private $template = null;
    private $twig = null;

    public function __construct(SettingsService $settingsManager, $template, \Twig_Environment $twig)
    {
        $this->settingsManager = $settingsManager;
        $this->template = $template;
        $this->twig = $twig;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $route = $event->getRequest()->attributes->get('_route');
        $uri = $event->getRequest()->getRequestUri();
        if (strpos($route, 'assetic') !== false || strpos($route, '_wdt') !== false || strpos($uri, '/cms/') !== false ||
            !$this->settingsManager->exists('maintenance') || !($this->settingsManager->get('maintenance') === 'on')) {
            return;
        }
        $template = $this->template ?
            $this->template : "EdcomsCMSMaintenanceBundle:Maintenance:deafult.html.twig";
        $event->setResponse(
            new Response(
                $this->twig->render($template),
                Response::HTTP_SERVICE_UNAVAILABLE
            )
        );
        $event->stopPropagation();
    }
}
