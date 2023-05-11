<?php

namespace Drupal\hello_world\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class HelloController.
 *
 * @package Drupal\hello_world\Controller
 */
class HelloController {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a HelloController object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Callback for the glossary content.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return array
   *   A render array representing the glossary content.
   */
  public function content(RouteMatchInterface $route_match) {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree('glossary', 0, 1);
    $termTitles = [];

    // get term titles.
    foreach ($terms as $term) {
      $termTitles[] = $term->name->value;
    }

    // get current page content.
    $node = $route_match->getParameter('node');
    $content = '';

    if ($node && $node instanceof \Drupal\node\NodeInterface) {
      $content = $node->body->value;

      // loop through the glossary terms and underline them in the content.
      foreach ($termTitles as $title) {
        $term = Term::loadByProperties(['name' => $title, 'vid' => 'glossary']);
        if (!empty($term)) {
          $termId = reset($term)->id();
          $termUrl = \Drupal::url('entity.taxonomy_term.canonical', ['taxonomy_term' => $termId]);
          $replacement = '<a href="' . $termUrl . '" style="text-decoration: underline;">' . $title . '</a>';
          $content = preg_replace('/\b' . preg_quote($title, '/') . '\b/i', $replacement, $content);
        }
      }
    }

    return [
      '#type' => 'markup',
      '#markup' => $content,
    ];
  }

}
