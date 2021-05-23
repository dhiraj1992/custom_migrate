<?php

namespace Drupal\custom_migrate\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\custom_migrate\Entity\LocationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LocationController.
 *
 *  Returns responses for Location routes.
 */
class LocationController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Location revision.
   *
   * @param int $location_revision
   *   The Location revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($location_revision) {
    $location = $this->entityTypeManager()->getStorage('location')
      ->loadRevision($location_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('location');

    return $view_builder->view($location);
  }

  /**
   * Page title callback for a Location revision.
   *
   * @param int $location_revision
   *   The Location revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($location_revision) {
    $location = $this->entityTypeManager()->getStorage('location')
      ->loadRevision($location_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $location->label(),
      '%date' => $this->dateFormatter->format($location->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Location.
   *
   * @param \Drupal\custom_migrate\Entity\LocationInterface $location
   *   A Location object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(LocationInterface $location) {
    $account = $this->currentUser();
    $location_storage = $this->entityTypeManager()->getStorage('location');

    $langcode = $location->language()->getId();
    $langname = $location->language()->getName();
    $languages = $location->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $location->label()]) : $this->t('Revisions for %title', ['%title' => $location->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all location revisions") || $account->hasPermission('administer location entities')));
    $delete_permission = (($account->hasPermission("delete all location revisions") || $account->hasPermission('administer location entities')));

    $rows = [];

    $vids = $location_storage->revisionIds($location);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\custom_migrate\LocationInterface $revision */
      $revision = $location_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $location->getRevisionId()) {
          $link = $this->l($date, new Url('entity.location.revision', [
            'location' => $location->id(),
            'location_revision' => $vid,
          ]));
        }
        else {
          $link = $location->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.location.translation_revert', [
                'location' => $location->id(),
                'location_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.location.revision_revert', [
                'location' => $location->id(),
                'location_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.location.revision_delete', [
                'location' => $location->id(),
                'location_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['location_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
