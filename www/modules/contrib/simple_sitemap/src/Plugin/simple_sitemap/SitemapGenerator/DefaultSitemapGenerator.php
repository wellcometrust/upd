<?php

namespace Drupal\simple_sitemap\Plugin\simple_sitemap\SitemapGenerator;

/**
 * Provides the default sitemap generator.
 *
 * @SitemapGenerator(
 *   id = "default",
 *   label = @Translation("Default sitemap generator"),
 *   description = @Translation("Generates a standard conform hreflang sitemap of your content."),
 * )
 */
class DefaultSitemapGenerator extends SitemapGeneratorBase {

  protected const XMLNS_XHTML = 'http://www.w3.org/1999/xhtml';
  protected const XMLNS_IMAGE = 'http://www.google.com/schemas/sitemap-image/1.1';

  /**
   * An array of attributes.
   *
   * @var array
   */
  protected const ATTRIBUTES = [
    'xmlns' => self::XMLNS,
    'xmlns:xhtml' => self::XMLNS_XHTML,
    'xmlns:image' => self::XMLNS_IMAGE,
  ];

  /**
   * {@inheritdoc}
   */
  public function getChunkContent(array $links): string {
    $this->writer->openMemory();
    $this->writer->setIndent(TRUE);
    $this->writer->startSitemapDocument();

    $this->addXslUrl();
    $this->writer->writeGeneratedBy();
    $this->writer->startElement('urlset');
    $this->addSitemapAttributes();
    $this->addLinks($links);
    $this->writer->endElement();
    $this->writer->endDocument();

    return $this->writer->outputMemory();
  }

  /**
   * Adds attributes to the sitemap.
   */
  protected function addSitemapAttributes(): void {
    $attributes = self::ATTRIBUTES;
    if (!$this->sitemap->isMultilingual()) {
      unset($attributes['xmlns:xhtml']);
    }
    $this->moduleHandler->alter('simple_sitemap_attributes', $attributes, $this->sitemap);
    foreach ($attributes as $name => $value) {
      $this->writer->writeAttribute($name, $value);
    }
  }

  /**
   * Adds URL elements to the sitemap.
   *
   * @param array $links
   *   An array of URL elements.
   */
  protected function addLinks(array $links): void {
    foreach ($links as $url_data) {
      $this->writer->startElement('url');
      $this->addUrl($url_data);
      $this->writer->endElement();
    }
  }

  /**
   * Adds a URL element to the sitemap.
   *
   * @param array $url_data
   *   The array of properties for this URL.
   */
  protected function addUrl(array $url_data): void {
    $this->writer->writeElement('loc', $url_data['url']);

    // If more than one language is enabled, add all translation variant URLs
    // as alternate links to this link turning the sitemap into a hreflang
    // sitemap.
    if (isset($url_data['alternate_urls']) && $this->sitemap->isMultilingual()) {
      $this->addAlternateUrls($url_data['alternate_urls']);
    }

    // Add lastmod if any.
    if (isset($url_data['lastmod'])) {
      $this->writer->writeElement('lastmod', $url_data['lastmod']);
    }

    // Add changefreq if any.
    if (isset($url_data['changefreq'])) {
      $this->writer->writeElement('changefreq', $url_data['changefreq']);
    }

    // Add priority if any.
    if (isset($url_data['priority'])) {
      $this->writer->writeElement('priority', $url_data['priority']);
    }

    // Add images if any.
    if (!empty($url_data['images'])) {
      foreach ($url_data['images'] as $image) {
        $this->writer->startElement('image:image');
        $this->writer->writeElement('image:loc', $image['path']);
        if (strlen($image['title']) > 0) {
          $this->writer->writeElement('image:title', $image['title']);
        }
        if (strlen($image['alt']) > 0) {
          $this->writer->writeElement('image:caption', $image['alt']);
        }
        $this->writer->endElement();
      }
    }
  }

  /**
   * Adds all translation variant URLs as alternate URLs to a URL.
   *
   * @param array $alternate_urls
   *   An array of alternate URLs.
   */
  protected function addAlternateUrls(array $alternate_urls): void {
    foreach ($alternate_urls as $language_id => $alternate_url) {
      $this->writer->startElement('xhtml:link');
      $this->addAlternateUrl($language_id, $alternate_url);
      $this->writer->endElement();
    }
  }

  /**
   * Adds a translation variant URL as alternate URL to a URL.
   *
   * @param string $language_id
   *   The language ID.
   * @param string $alternate_url
   *   The alternate URL.
   */
  protected function addAlternateUrl(string $language_id, string $alternate_url): void {
    $this->writer->writeAttribute('rel', 'alternate');
    $this->writer->writeAttribute('hreflang', $language_id);
    $this->writer->writeAttribute('href', $alternate_url);
  }

  /**
   * {@inheritdoc}
   */
  public function getXslContent(): ?string {
    $module_path = $this->moduleList->getPath('simple_sitemap');
    $xsl_content = file_get_contents($module_path . '/xsl/simple_sitemap.xsl');
    $replacements = [
      '[title]' => $this->t('Sitemap file'),
      '[generated-by]' => $this->t('Generated by the <a href="@link">@module_name</a> Drupal module.', [
        '@link' => 'https://www.drupal.org/project/simple_sitemap',
        '@module_name' => 'Simple XML Sitemap',
      ]),
      '[number-of-sitemaps]' => $this->t('Number of sitemaps in this index'),
      '[sitemap-url]' => $this->t('Sitemap URL'),
      '[number-of-urls]' => $this->t('Number of URLs in this sitemap'),
      '[url-location]' => $this->t('URL location'),
      '[lastmod]' => $this->t('Last modification date'),
      '[changefreq]' => $this->t('Change frequency'),
      '[priority]' => $this->t('Priority'),
      '[translation-set]' => $this->t('Translation set'),
      '[images]' => $this->t('Images'),
      '[image-title]' => $this->t('Title'),
      '[image-caption]' => $this->t('Caption'),
      '[jquery]' => base_path() . 'core/assets/vendor/jquery/jquery.min.js',
      '[jquery-tablesorter]' => base_path() . $module_path . '/xsl/jquery.tablesorter.min.js',
      '[parser-date-iso8601]' => base_path() . $module_path . '/xsl/parser-date-iso8601.min.js',
      '[xsl-js]' => base_path() . $module_path . '/xsl/simple_sitemap.xsl.js',
      '[xsl-css]' => base_path() . $module_path . '/xsl/simple_sitemap.xsl.css',
    ];

    return strtr($xsl_content, $replacements);
  }

}
