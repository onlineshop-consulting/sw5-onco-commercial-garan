<?php

use OncoCommercialGaran\Services\LabelService;

class Shopware_Controllers_Frontend_OncoCommercialGaran extends Enlight_Controller_Action
{
    /** @return void */
    public function labelAction()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();

        $service = new LabelService(
            $this->get('dbal_connection'),
            $this->get('shopware.plugin.config_reader'),
            $this->get('shopware_media.media_service'),
            $this->container->getParameter('onco_commercial_garan.plugin_dir'),
            $this->container->initialized('shop') ? $this->get('shop') : null
        );

        $svg = $service->generateSvg($this->Request()->getParam('number'));

        if ($svg === null) {
            throw new Enlight_Controller_Exception('Label not found', 404);
        }

        $response = $this->Response();
        $response->setHeader('Content-Type', 'image/svg+xml; charset=utf-8', true);
        $response->setBody($svg);
    }
}
