<?php

namespace OncoCommercialGaran\Services;

use OncoCommercialGaran\OncoCommercialGaran;
use Shopware\Bundle\AttributeBundle\Service\CrudService;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Media;

class LifeCycleService
{
    /** @var ModelManager */
    private $modelManager;

    /** @var CrudService */
    private $attributeService;

    public function __construct(ModelManager $modelManager, CrudService $attributeService)
    {
        $this->modelManager = $modelManager;
        $this->attributeService = $attributeService;
    }

    /** @return void */
    public function install()
    {
        $this->attributeService->update(
            's_articles_attributes',
            OncoCommercialGaran::ATTR_YEARS,
            'string',
            [
                'label' => 'EU GARAN Garantiedauer in Jahren',
                'supportText' => 'z.B. 5 oder 2,5 - leer lassen, wenn keine Herstellergarantie',
                'helpText' => 'Dauer der Haltbarkeitsgarantie des Herstellers (mehr als 2 Jahre, halbe Jahre mit Komma). Sobald gepflegt, wird das EU-GARAN-Label automatisch erzeugt und angezeigt.',
                'displayInBackend' => true,
                'custom' => false,
            ],
            null,
            true
        );

        $this->attributeService->update(
            's_articles_attributes',
            OncoCommercialGaran::ATTR_MEDIA,
            'single_selection',
            [
                'entity' => Media::class,
                'label' => 'EU GARAN Eigenes Label (optional)',
                'helpText' => 'Optional: Überschreibt das automatisch erzeugte EU-GARAN-Label, z.B. mit dem vom Hersteller gelieferten Original.',
                'displayInBackend' => true,
                'custom' => false,
            ],
            null,
            true
        );

        $this->modelManager->generateAttributeModels(['s_articles_attributes']);
    }

    /** @return void */
    public function uninstall($keepUserData)
    {
        if ($keepUserData) {
            return;
        }

        $this->attributeService->delete('s_articles_attributes', OncoCommercialGaran::ATTR_YEARS, true);
        $this->attributeService->delete('s_articles_attributes', OncoCommercialGaran::ATTR_MEDIA, true);

        $this->modelManager->generateAttributeModels(['s_articles_attributes']);
    }
}
