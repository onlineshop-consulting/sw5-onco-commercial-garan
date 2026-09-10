<?php

namespace OncoCommercialGaran;

use Enlight_Controller_ActionEventArgs;
use OncoCommercialGaran\Services\LabelService;
use OncoCommercialGaran\Services\LifeCycleService;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OncoCommercialGaran extends Plugin
{
    const ATTR_YEARS = 'onco_commercial_garan_years';

    const ATTR_MEDIA = 'onco_commercial_garan_media_id';

    const PORTAL_URL = 'https://europa.eu/youreurope/commercial-guarantee-durability/index.htm';

    /** @var ContainerInterface */
    protected $container;

    /** @var string[] */
    const CACHE_LIST = [
        ActivateContext::CACHE_TAG_TEMPLATE,
        ActivateContext::CACHE_TAG_CONFIG,
        ActivateContext::CACHE_TAG_PROXY,
        ActivateContext::CACHE_TAG_THEME,
        ActivateContext::CACHE_TAG_HTTP,
    ];

    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Detail' => 'onDetailPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Listing' => 'onListingPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Search' => 'onSearchPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => 'onCheckoutPostDispatch',
            'Enlight_Controller_Dispatcher_ControllerPath_Frontend_OncoCommercialGaran' => 'onGetFrontendController',
            'Shopware_Modules_Order_SendMail_BeforeSend' => 'onOrderMailBeforeSend',
        ];
    }

    /**
     * @return void
     */
    public function onOrderMailBeforeSend(\Enlight_Event_EventArgs $args)
    {
        $mail = $args->get('mail');
        $context = $args->get('context');

        if (!$mail instanceof \Enlight_Components_Mail || !is_array($context)) {
            return;
        }

        $details = isset($context['sOrderDetails']) && is_array($context['sOrderDetails'])
            ? $context['sOrderDetails']
            : [];

        try {
            $service = $this->getLabelService();
            $attached = [];

            foreach ($details as $item) {
                if (!is_array($item) || empty($item['ordernumber'])) {
                    continue;
                }
                if (isset($item['modus']) && (int) $item['modus'] !== 0) {
                    continue;
                }

                $number = (string) $item['ordernumber'];
                if (isset($attached[$number])) {
                    continue;
                }
                $attached[$number] = true;

                $attachment = $service->getLabelAttachment($number);
                if ($attachment === null) {
                    continue;
                }

                $mail->createAttachment(
                    $attachment['content'],
                    $attachment['mime'],
                    \Zend_Mime::DISPOSITION_ATTACHMENT,
                    \Zend_Mime::ENCODING_BASE64,
                    $attachment['filename']
                );
            }
        } catch (\Exception $e) {
            $this->container->get('pluginlogger')->warning(
                'OncoCommercialGaran: could not attach GARAN label to order mail: ' . $e->getMessage()
            );
        }
    }

    /** @return string */
    public function onGetFrontendController()
    {
        return $this->getPath() . '/Controllers/Frontend/OncoCommercialGaran.php';
    }

    /** @return void */
    public function onDetailPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();
        $article = $view->getAssign('sArticle');

        $numbers = [];
        if (is_array($article) && !empty($article['ordernumber'])) {
            $numbers[] = $article['ordernumber'];
        }

        $this->assignLabelData($view, $numbers);
    }

    /** @return void */
    public function onListingPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();

        $this->assignLabelData($view, $this->collectOrderNumbers($view->getAssign('sArticles')));
    }

    /** @return void */
    public function onSearchPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();
        $result = $view->getAssign('sSearchResults');

        $articles = is_array($result) && isset($result['sArticles']) ? $result['sArticles'] : null;

        $this->assignLabelData($view, $this->collectOrderNumbers($articles));
    }

    /** @return void */
    public function onCheckoutPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $controller = $args->getSubject();

        if ($controller->Request()->getActionName() !== 'confirm') {
            return;
        }

        $view = $controller->View();
        $basket = $view->getAssign('sBasket');

        $items = is_array($basket) && isset($basket['content']) ? $basket['content'] : null;

        $this->assignLabelData($view, $this->collectOrderNumbers($items));
    }

    /** @return void */
    public function install(InstallContext $context)
    {
        $this->getLifeCycleService()->install();

        parent::install($context);
    }

    /** @return void */
    public function uninstall(UninstallContext $context)
    {
        $this->getLifeCycleService()->uninstall($context->keepUserData());

        parent::uninstall($context);
    }

    /** @return void */
    public function update(UpdateContext $context)
    {
        $context->scheduleClearCache(self::CACHE_LIST);
    }

    /** @return void */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(self::CACHE_LIST);
    }

    /** @return void */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(self::CACHE_LIST);
    }

    /** @return string[] */
    private function collectOrderNumbers($items)
    {
        $numbers = [];

        if (!is_array($items)) {
            return $numbers;
        }

        foreach ($items as $item) {
            if (is_array($item) && !empty($item['ordernumber'])) {
                $numbers[] = (string) $item['ordernumber'];
            }
        }

        return array_values(array_unique($numbers));
    }

    /** @return void */
    private function assignLabelData(\Enlight_View_Default $view, array $orderNumbers)
    {
        $view->addTemplateDir($this->getPath() . '/Resources/views');

        $view->assign('oncoCommercialGaran', [
            'map' => $this->buildLabelMap($orderNumbers),
            'portalUrl' => self::PORTAL_URL,
        ]);
    }

    /**
     * Ordernumber => ['years' => string, 'labelUrl' => string|null] for all
     * products that carry a maintained guarantee duration. labelUrl is only
     * set when a producer label media is maintained as manual override;
     * otherwise the full label is generated by the frontend controller.
     *
     * @return array<string, array<string, string|null>>
     */
    private function buildLabelMap(array $orderNumbers)
    {
        if (empty($orderNumbers)) {
            return [];
        }

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $this->container->get('dbal_connection');

        $rows = $connection->fetchAll(
            'SELECT d.ordernumber, a.' . self::ATTR_YEARS . ' AS years, a.' . self::ATTR_MEDIA . ' AS mediaId
             FROM s_articles_details d
             INNER JOIN s_articles_attributes a ON a.articledetailsID = d.id
             WHERE d.ordernumber IN (?)
               AND a.' . self::ATTR_YEARS . " IS NOT NULL
               AND a." . self::ATTR_YEARS . " != ''",
            [$orderNumbers],
            [\Doctrine\DBAL\Connection::PARAM_STR_ARRAY]
        );

        if (empty($rows)) {
            return [];
        }

        $mediaIds = [];
        foreach ($rows as $row) {
            if (!empty($row['mediaId'])) {
                $mediaIds[] = (int) $row['mediaId'];
            }
        }

        $urlByMediaId = [];
        if (!empty($mediaIds)) {
            $mediaPaths = $connection->fetchAll(
                'SELECT id, path FROM s_media WHERE id IN (?)',
                [array_values(array_unique($mediaIds))],
                [\Doctrine\DBAL\Connection::PARAM_INT_ARRAY]
            );

            /** @var \Shopware\Bundle\MediaBundle\MediaServiceInterface $mediaService */
            $mediaService = $this->container->get('shopware_media.media_service');

            foreach ($mediaPaths as $media) {
                $urlByMediaId[(int) $media['id']] = $mediaService->getUrl($media['path']);
            }
        }

        $map = [];
        foreach ($rows as $row) {
            $mediaId = (int) $row['mediaId'];

            $map[$row['ordernumber']] = [
                'years' => $row['years'],
                'labelUrl' => isset($urlByMediaId[$mediaId]) ? $urlByMediaId[$mediaId] : null,
            ];
        }

        return $map;
    }

    /** @return LabelService */
    private function getLabelService()
    {
        return new LabelService(
            $this->container->get('dbal_connection'),
            $this->container->get('shopware.plugin.config_reader'),
            $this->container->get('shopware_media.media_service'),
            $this->getPath(),
            $this->container->initialized('shop') ? $this->container->get('shop') : null
        );
    }

    /** @return LifeCycleService */
    private function getLifeCycleService()
    {
        return new LifeCycleService(
            $this->container->get('models'),
            $this->container->get('shopware_attribute.crud_service')
        );
    }
}
