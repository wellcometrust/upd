<?php

namespace Drupal\simple_sitemap\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\simple_sitemap\Simplesitemap;
use Symfony\Component\HttpFoundation\Request;
use Drupal\simple_sitemap\SimplesitemapManager;

/**
 * Class SimplesitemapController
 * @package Drupal\simple_sitemap\Controller
 */
class SimplesitemapController extends ControllerBase {

  /**
   * @var \Drupal\simple_sitemap\Simplesitemap
   */
  protected $generator;

  /**
   * SimplesitemapController constructor.
   * @param \Drupal\simple_sitemap\Simplesitemap $generator
   */
  public function __construct(Simplesitemap $generator) {
    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('simple_sitemap.generator')
    );
  }

  /**
   * Returns the whole sitemap variant, its requested chunk,
   * or its sitemap index file.
   * Caches the response in case of expected output, prevents caching otherwise.
   *
   * @param string $variant
   *  Optional name of sitemap variant.
   *  @see \hook_simple_sitemap_variants_alter()
   *  @see SimplesitemapManager::getSitemapVariants()
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *  The request object.
   *
   * @throws NotFoundHttpException
   *
   * @return object
   *  Returns an XML response.
   */
  public function getSitemap(Request $request, $variant = NULL) {
    $output = $this->generator->setVariants($variant)->getSitemap($request->query->getInt('page'));
    if (!$output) {
      throw new NotFoundHttpException();
    }

    return new Response($output, Response::HTTP_OK, [
      'content-type' => 'application/xml',
      'X-Robots-Tag' => 'noindex', // Tell search engines not to index the sitemap itself.
    ]);
  }
}
