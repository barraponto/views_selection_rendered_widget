<?php

namespace Drupal\views_selection_rendered_widget\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\views\Plugin\EntityReferenceSelection\ViewsSelection;
use Drupal\views\Render\ViewsRenderPipelineMarkup;


/**
 * Plugin implementation of the 'selection' entity_reference.
 *
 * @EntityReferenceSelection(
 *   id = "views_rendered",
 *   label = @Translation("Views: Filter by an entity reference view and display rendered fields"),
 *   group = "views_rendered",
 *   weight = 1
 * )
 */
class ViewsRenderedSelection extends ViewsSelection {

  /**
  * The renderer.
  *
  * @var \Drupal\Core\Render\RendererInterface
  */
  protected $renderer;

  /**
   * Constructs a new ViewsSelection object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $module_handler, $current_user);
    $this->renderer = \Drupal::service('renderer');
  }


  /**
   * Fetches the results of executing the display.
   *
   * @param string|null $match
   *   (Optional) Text to match the label against. Defaults to NULL.
   * @param string $match_operator
   *   (Optional) The operation the matching should be done with. Defaults
   *   to "CONTAINS".
   * @param int $limit
   *   Limit the query to a given number of items. Defaults to 0, which
   *   indicates no limiting.
   * @param array|null $ids
   *   Array of entity IDs. Defaults to NULL.
   *
   * @return array
   *   The results.
   */
  protected function getDisplayExecutionResults($match = NULL, $match_operator = 'CONTAINS', $limit = 0, $ids = NULL) {
    $display_name = $this->getConfiguration()['view']['display_name'];
    $arguments = $this->getConfiguration()['view']['arguments'];
    $results = [];
    if ($this->initializeView($match, $match_operator, $limit, $ids)) {
      $results = $this->view->executeDisplay($display_name, $arguments);
    }
    return $results;
  }

  /**
   * Strips all admin and anchor tags from a result list.
   *
   * These results are usually displayed in an autocomplete field, which is
   * surrounded by anchor tags. Most tags are allowed inside anchor tags, except
   * for other anchor tags.
   *
   * @param array $results
   *   The result list.
   * @return array
   *   The provided result list with anchor tags removed.
   */
  protected function stripAdminAndAnchorTagsFromResults($results) {
    $allowed_tags = Xss::getAdminTagList();
    if (($key = array_search('a', $allowed_tags)) !== FALSE) {
      unset($allowed_tags[$key]);
    }

    $stripped_results = [];
    foreach ($results as $id => $row) {
      $entity = $row['#row']->_entity;
      $stripped_results[$entity->bundle()][$id] = ViewsRenderPipelineMarkup::create(
        Xss::filter($this->renderer->renderPlain($row), $allowed_tags)
      );
    }

    return $stripped_results;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $entities = [];
    if ($display_execution_results = $this->getDisplayExecutionResults($match, $match_operator, $limit)) {
      $entities = $this->stripAdminAndAnchorTagsFromResults($display_execution_results);
    }
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableEntities(array $ids) {
    return array_keys($this->getDisplayExecutionResults(NULL, 'CONTAINS', 0, $ids));
  }
}
