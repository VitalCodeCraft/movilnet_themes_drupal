<?php

/**
 * @file
 * Implements().
 */

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @file
 * Market_wave theme file.
 */

/**
 * Implements hook_form_system_theme_settings_alter().
 */
function market_wave_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
  if ($form['#attributes']['class'][0] == 'system-theme-settings') {
    $form['#attached']['library'][] = 'market_wave/theme.setting';
  }

  // Verticle tabs.
  $form['wave_tabs'] = [
    '#type' => 'vertical_tabs',
    '#prefix' => '<h2><small>' . t('Market wave settings') . '</small></h2>',
    '#weight' => -10,
  ];

  // Header settings.
  $form['header'] = [
    '#type' => 'details',
    '#title' => t('Header'),
    '#group' => 'wave_tabs',
  ];
  $form['header']['full_width'] = [
    '#type' => 'checkbox',
    '#title' => t('Full width header'),
    '#default_value' => theme_get_setting('full_width', 'market_wave'),
    '#description'   => t("Check this option to show full width header."),
  ];
  $form['header']['header_class'] = [
    '#title' => t('Header custom class'),
    '#type' => 'textfield',
    '#default_value' => theme_get_setting("header_class", "market_wave"),
  ];

  // Home page slider settings.
  $form['Banner'] = [
    '#type' => 'details',
    '#title' => t('Slider settings'),
    '#group' => 'wave_tabs',
  ];
  // Modificación aquí: forzar la lectura de slide_num desde la configuración
  if (!$form_state->get('num_rows')) {
    $form_state->set('num_rows', \Drupal::configFactory()->get('theme.market_wave')->get('slide_num') ?? 2);
  }
  $form['Banner']['slideshow_display'] = [
    '#type' => 'checkbox',
    '#title' => t('Show slideshow'),
    '#default_value' => theme_get_setting('slideshow_display', 'market_wave'),
    '#description'   => t("Check this option to show Slideshow in front page. Uncheck to hide."),
  ];
  $form['Banner']['slide_num'] = [
    '#type' => 'number',
    '#title' => t('Select Number of Slider Display'),
    '#min' => 1,
    '#required' => TRUE,
    '#default_value' => \Drupal::configFactory()->get('theme.market_wave')->get('slide_num') ?? 2,
    '#description' => t("Enter Number of slider you want to display"),
    '#access' => FALSE,
  ];
  $form['Banner']['slide'] = [
    '#markup' => t('You can change the title, descriptions, url and image of each slide in the following Slide Setting fieldsets.'),
  ];
  $form['Banner']['slidecontent'] = [
    '#type' => 'container',
    '#attributes' => ['id' => 'slide-wrapper'],
  ];
  for ($i = 1; $i <= $form_state->get('num_rows'); $i++) {
    $form['Banner']['slidecontent']['slide' . $i] = [
      '#type' => 'details',
      '#title' => t('Slide @i', ['@i' => $i]),
    ];
    $form['Banner']['slidecontent']['slide' . $i]['slide_title_' . $i] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => theme_get_setting("slide_title_{$i}", "market_wave"),
    ];
    $form['Banner']['slidecontent']['slide' . $i]['slide_desc_' . $i] = [
      '#type' => 'text_format',
      '#title' => t('Descriptions'),
      '#default_value' => theme_get_setting("slide_desc_{$i}", "market_wave")['value'],
    ];
    $form['Banner']['slidecontent']['slide' . $i]['slide_image_' . $i] = [
      '#type' => 'managed_file',
      '#title' => t('Image'),
      '#description' => t('Use same size for all the slideshow images(Recommented size : 1920 X 603).'),
      '#default_value' => theme_get_setting("slide_image_{$i}", "market_wave"),
      '#upload_location' => 'public://',
    ];
    $form['Banner']['slidecontent']['slide' . $i]['slide_url_' . $i] = [
      '#type' => 'textfield',
      '#title' => t('button URL'),
      '#default_value' => theme_get_setting("slide_url_{$i}", "market_wave"),
    ];
    $form['Banner']['slidecontent']['slide' . $i]['slide_url_title_' . $i] = [
      '#type' => 'textfield',
      '#title' => t('Button text'),
      '#default_value' => theme_get_setting("slide_url_title_{$i}", "market_wave"),
    ];
  }
  $form['Banner']['info'] = [
    '#markup' => t('You can add or remove slider content using both button.'),
  ];
  $form['Banner']['actions'] = [
    '#type' => 'actions',
  ];
  $form['Banner']['actions']['add_more'] = [
    '#type' => 'submit',
    '#value' => t('Add one more'),
    '#submit' => ['add_one'],
    '#attributes' => [
      'class' => ['button-addmore'],
    ],
    '#ajax' => [
      'callback' => 'addmore_callback',
      'wrapper' => 'slide-wrapper',
    ],
  ];
  if ($form_state->get('num_rows') > 2) {
    $form['Banner']['actions']['remove_last'] = [
      '#type' => 'submit',
      '#value' => t('remove last one'),
      '#submit' => ['removelast'],
      '#attributes' => [
        'class' => ['button-remove'],
      ],
      '#ajax' => [
        'callback' => 'removelast_callback',
        'wrapper' => 'slide-wrapper',
      ],
    ];
  }
  $form['actions']['submit']['#value'] = t('Save Custom Settings');
  $form['#submit'][] = 'market_wave_custom_submit_callback';
}

/**
 * Callback for both ajax-enabled buttons.
 *
 * Selects and returns the slide content in it.
 */
function addmore_callback(array &$form, FormStateInterface $form_state) {
  return $form['Banner']['slidecontent'];
}

/**
 * Submit handler for the "add-one-more" button.
 *
 * Increments the max counter and causes a rebuild.
 */
function add_one(array &$form, FormStateInterface $form_state) {
  $current_slide_num = \Drupal::configFactory()->get('theme.market_wave')->get('slide_num') ?? 2;
  $new_slide_num = $current_slide_num + 1;

  // Actualizar el estado del formulario
  $form_state->set('num_rows', $new_slide_num);

  // Actualizar inmediatamente la configuración del tema
  \Drupal::configFactory()->getEditable('theme.market_wave')
    ->set('slide_num', $new_slide_num)
    ->save();

  $form_state->setRebuild();
}

/**
 * Callback for both ajax-enabled buttons.
 *
 * Selects and returns the slide content in it.
 */
function removelast_callback(array &$form, FormStateInterface $form_state) {
  return $form['Banner']['slidecontent'];
}

/**
 * Submit handler for the "remove last one" button.
 *
 * Decrements the max counter and causes a rebuild.
 */
function removelast(array &$form, FormStateInterface $form_state) {
  $number_of_slide = $form_state->get('num_rows');
  if ($number_of_slide > 2) {
    $remove_button = $number_of_slide - 1;
    $form_state->set('num_rows', $remove_button);
    $keys = [
      "slide_image_$number_of_slide",
      "slide_url_$number_of_slide",
      "slide_url_title_$number_of_slide",
      "slide_title_$number_of_slide",
      "slide_desc_$number_of_slide",
    ];
    $config_market_wave = \Drupal::configFactory()->getEditable('theme.market_wave');
    foreach ($keys as $slide_index) {
      $config_market_wave->delete($slide_index);
    }
    $config_market_wave->save();
    $form_state->setRebuild();
  }
}

/**
 * Custom submit callback for the theme settings form.
 */
function market_wave_custom_submit_callback(&$form, FormStateInterface $form_state) {
  // Guardar el valor de 'slide_num'
  $slide_num_value = $form_state->get('num_rows');
  \Drupal::configFactory()->getEditable('theme.market_wave')->set('slide_num', $slide_num_value)->save();

  // Guardar el valor de 'slideshow_display'
  $slideshow_display_value = $form_state->getValue('slideshow_display');
  \Drupal::configFactory()->getEditable('theme.market_wave')->set('slideshow_display', $slideshow_display_value)->save();

  // Guardar los valores de cada slide
  for ($i = 1; $i <= $form_state->get('num_rows'); $i++) {
    \Drupal::configFactory()->getEditable('theme.market_wave')
      ->set("slide_title_{$i}", $form_state->getValue("slide_title_{$i}"))
      ->set("slide_desc_{$i}", $form_state->getValue("slide_desc_{$i}")) // Acceder al 'value' del text_format
      ->set("slide_image_{$i}", $form_state->getValue("slide_image_{$i}")[0] ?? '') // Guardar el FID de la imagen
      ->set("slide_url_{$i}", $form_state->getValue("slide_url_{$i}"))
      ->set("slide_url_title_{$i}", $form_state->getValue("slide_url_title_{$i}"))
      ->save();
  }
}

/**
 * Controller for the Market Wave API.
 */
class MarketWaveApiController extends \Drupal\Core\Controller\ControllerBase {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new MarketWaveApiController.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * The config factory service.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * Returns the Market Wave slider data as JSON.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   * The slider data.
   */
  public function getSliderData() {
    $config = $this->configFactory->get('theme.market_wave')->get();
    $sliderData = [];
    $slideNum = $config['slide_num'] ?? 0;

    if ($config['slideshow_display']) {
      for ($i = 1; $i <= $slideNum; $i++) {
        $sliderData[] = [
          'title' => $config["slide_title_{$i}"] ?? '',
          'description' => $config["slide_desc_{$i}"]['value'] ?? '',
          'image' => $this->generateImageUrl($config["slide_image_{$i}"] ?? ''),
          'url' => $config["slide_url_{$i}"] ?? '',
          'url_title' => $config["slide_url_title_{$i}"] ?? '',
        ];
      }
    }

    return new JsonResponse($sliderData);
  }

  /**
   * Generates the absolute URL for a file entity.
   *
   * @param int|string $fid
   * The file ID.
   *
   * @return string
   * The absolute URL of the file, or an empty string if the file cannot be loaded.
   */
  protected function generateImageUrl($fid) {
    if (is_numeric($fid)) {
      $file = \Drupal::entityTypeManager()->getStorage('file')->load((int) $fid);
      if ($file instanceof \Drupal\file\Entity\File) {
        return \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      }
    }
    return '';
  }

}