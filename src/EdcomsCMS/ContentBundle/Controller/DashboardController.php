<?php

namespace EdcomsCMS\ContentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;


class DashboardController extends Controller
{
    public function indexAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
//        foreach ($user->getDashboard() as $dashboard) {
//            print_r($dashboard->getDashboardItem()->getController());
//        }
        // Check if spirit is used to display report
        $has_spirit = false;
        if ($this->container->has('SPIRITRegistration')) {
            $has_spirit = true;
        }

        // Check if custom site reports are available
        $has_custom_reports = false;
        $custom_reports_properties = [];
        if ($this->container->has('CustomReporting')) {
            $has_custom_reports = true;
            $custom_reporting = $this->container->get('CustomReporting');
            $custom_reports_properties = $custom_reporting->getReports();
        }
        return $this->render(
            "EdcomsCMSTemplatesBundle::index.html.twig",
            [
                "title" => "Dashboard",
                "person" => $user->getPerson(),
                "dashboard" => $user->getDashboard(),
                "has_spirit" => $has_spirit,
                "has_custom_reports" => $has_custom_reports,
                "custom_reports_properties" => $custom_reports_properties
            ]
        );
    }

    /**
     * @Route("/cms/reporting")
     * @return JsonResponse
     */
    public function reportingAction()
    {
        $reports = [];

        //Check for SPIRIT report
        if ($this->container->has('SPIRITRegistration')) {
            $reports[] = array(
                "name"  => "SPIRIT",
                "url"   => "/cms/users/export/spirit"
            );
        }

        //Check for custom report
        if ($this->container->has('CustomReporting')) {
            $custom_reporting = $this->container->get('CustomReporting');
            $reports = array_merge($reports, $custom_reporting->getReports());
        }

        return new JsonResponse($reports);
    }
}
