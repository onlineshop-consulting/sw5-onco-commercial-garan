<?php

namespace OncoCommercialGaran\Services;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Plugin\DBALConfigReader;
use Shopware\Models\Shop\Shop;

class LabelService
{
    const BRAND_PLACEHOLDER = '<tspan x="0" y="0">Brand/</tspan><tspan class="cls-19" x="28.34" y="0">T</tspan><tspan class="cls-22" x="33.61" y="0">rademark</tspan>';

    const YEARS_PLACEHOLDER = '<tspan x="0" y="0">XX</tspan>';

    /** @var Connection */
    private $connection;

    /** @var DBALConfigReader */
    private $configReader;

    /** @var MediaServiceInterface */
    private $mediaService;

    /** @var string */
    private $pluginPath;

    /** @var Shop|null */
    private $shop;

    public function __construct(
        Connection            $connection,
        DBALConfigReader      $configReader,
        MediaServiceInterface $mediaService,
        $pluginPath,
        Shop $shop = null
    ) {
        $this->connection = $connection;
        $this->configReader = $configReader;
        $this->mediaService = $mediaService;
        $this->pluginPath = $pluginPath;
        $this->shop = $shop;
    }

    /**
     * @return string|null
     */
    public function generateSvg($number)
    {
        $data = $this->loadProductData($number);

        if (!$data || $data['years'] === null || $data['years'] === '') {
            return null;
        }

        return $this->renderSvg($data);
    }

    /**
     * @return array<string, string>|null ['content', 'mime', 'filename']
     */
    public function getLabelAttachment($number)
    {
        $data = $this->loadProductData($number);

        if (!$data || $data['years'] === null || $data['years'] === '') {
            return null;
        }

        if (!empty($data['mediaId'])) {
            $override = $this->loadOverrideMedia((int) $data['mediaId'], $number);
            if ($override !== null) {
                return $override;
            }
        }

        return [
            'content' => $this->renderSvg($data),
            'mime' => 'image/svg+xml',
            'filename' => 'EU-GARAN-Label-' . $number . '.svg',
        ];
    }

    /** @return array<string, string|null>|null */
    private function loadProductData($number)
    {
        $number = trim((string) $number);
        if ($number === '') {
            return null;
        }

        $row = $this->connection->fetchAssoc(
            'SELECT d.ordernumber, d.suppliernumber, d.ean,
                    attr.onco_commercial_garan_years AS years,
                    attr.onco_commercial_garan_media_id AS mediaId,
                    s.name AS brand
             FROM s_articles_details d
             INNER JOIN s_articles_attributes attr ON attr.articledetailsID = d.id
             INNER JOIN s_articles a ON a.id = d.articleID
             LEFT JOIN s_articles_supplier s ON s.id = a.supplierID
             WHERE d.ordernumber = ?',
            [$number]
        );

        if (!is_array($row)) {
            return null;
        }

        if ($row['brand'] === null) {
            $row['brand'] = '';
        }

        return $row;
    }

    /** @return string */
    private function renderSvg(array $data)
    {
        $svg = file_get_contents($this->pluginPath . '/Resources/label/garan-label-colour.svg');

        $yearsAttributes = strlen($data['years']) > 2
            ? ' textLength="105" lengthAdjust="spacingAndGlyphs"'
            : '';
        $svg = str_replace(
            self::YEARS_PLACEHOLDER,
            '<tspan x="0" y="0"' . $yearsAttributes . '>' . $this->escape($data['years']) . '</tspan>',
            $svg
        );

        $svg = str_replace('>Model identifier<', '>' . $this->escape($this->resolveIdentifier($data)) . '<', $svg);

        return str_replace(
            self::BRAND_PLACEHOLDER,
            '<tspan x="0" y="0">' . $this->escape($data['brand']) . '</tspan>',
            $svg
        );
    }

    /**
     * @return string
     */
    private function resolveIdentifier(array $data)
    {
        $config = $this->configReader->getByPluginName('OncoCommercialGaran', $this->shop);

        $priorities = [
            isset($config['identifierPriority1']) ? $config['identifierPriority1'] : 'ordernumber',
            isset($config['identifierPriority2']) ? $config['identifierPriority2'] : 'suppliernumber',
            isset($config['identifierPriority3']) ? $config['identifierPriority3'] : 'ean',
        ];

        foreach ($priorities as $field) {
            if (isset($data[$field]) && trim((string) $data[$field]) !== '') {
                return trim((string) $data[$field]);
            }
        }

        return '';
    }

    /** @return array<string, string>|null */
    private function loadOverrideMedia($mediaId, $number)
    {
        $media = $this->connection->fetchAssoc(
            'SELECT path, extension FROM s_media WHERE id = ?',
            [$mediaId]
        );

        if (!is_array($media) || empty($media['path'])) {
            return null;
        }

        $content = $this->mediaService->read($media['path']);
        if ($content === false || $content === null || $content === '') {
            return null;
        }

        $extension = strtolower((string) $media['extension']);
        $mimeTypes = [
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'pdf' => 'application/pdf',
        ];

        return [
            'content' => $content,
            'mime' => isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : 'application/octet-stream',
            'filename' => 'EU-GARAN-Label-' . $number . ($extension !== '' ? '.' . $extension : ''),
        ];
    }

    /** @return string */
    private function escape($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
