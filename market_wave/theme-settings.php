<?php

/**
 * @file
 * Implements().
 */

use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Bytes; // <-- Esta línea es CRUCIAL.

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
    '#access' => FALSE, // Keep this as false if you manage num_rows via add/remove buttons.
  ];
  $form['Banner']['slide'] = [
    '#markup' => t('You can change the title, descriptions, url and image of each slide in the following Slide Setting fieldsets.'),
  ];
  $form['Banner']['slidecontent'] = [
    '#type' => 'container',
    '#attributes' => ['id' => 'slide-wrapper'],
  ];

  // Get the current number of rows to display based on form_state or config.
  $num_rows_to_display = $form_state->get('num_rows') ?? \Drupal::configFactory()->get('theme.market_wave')->get('slide_num') ?? 2;

  // OBTENER EL TAMAÑO MÁXIMO DE SUBIDA DE ARCHIVOS DE PHP.
  // Esta es la implementación más directa y robusta, usando Bytes::toInt().
  //$upload_max_filesize_php = Bytes::toInt(ini_get('upload_max_filesize'));
  //$post_max_size_php = Bytes::toInt(ini_get('post_max_size'));
  // El tamaño máximo efectivo para las subidas es el mínimo de estas dos configuraciones de PHP.
  //$max_filesize_bytes = min($upload_max_filesize_php, $post_max_size_php);

  // VALOR POR DEFECTO TEMPORAL: 20MB (20 * 1024 * 1024 bytes)
  $max_filesize_bytes = 20971520;

  for ($i = 1; $i <= $num_rows_to_display; $i++) {
    $form['Banner']['slidecontent']['slide' . $i] = [
      '#type' => 'details',
      '#title' => t('Slide @i', ['@i' => $i]),
      '#open' => FALSE, // Keep them collapsed by default for better UX
    ];
    $form['Banner']['slidecontent']['slide' . $i]['slide_title_' . $i] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#default_value' => theme_get_setting("slide_title_{$i}", "market_wave"),
    ];

    // NEW: Text color for title.
    $form['Banner']['slidecontent']['slide' . $i]['slide_title_color_' . $i] = [
      '#type' => 'color',
      '#title' => t('Color del Título'),
      '#default_value' => theme_get_setting("slide_title_color_{$i}", "market_wave") ?: '#ffffff', // Default white
      '#description' => t('Elija el color del texto para el título.'),
    ];

    $form['Banner']['slidecontent']['slide' . $i]['slide_desc_' . $i] = [
      '#type' => 'text_format',
      '#title' => t('Descriptions'),
      // Ensure 'value' and 'format' are correctly handled for text_format.
      '#default_value' => theme_get_setting("slide_desc_{$i}", "market_wave")['value'] ?? '',
      '#format' => theme_get_setting("slide_desc_{$i}", "market_wave")['format'] ?? 'basic_html', // Ensure a default format
    ];

    // NEW: Text color for description.
    $form['Banner']['slidecontent']['slide' . $i]['slide_desc_color_' . $i] = [
      '#type' => 'color',
      '#title' => t('Color de la Descripción'),
      '#default_value' => theme_get_setting("slide_desc_color_{$i}", "market_wave") ?: '#ffffff', // Default white
      '#description' => t('Elija el color del texto para la descripción.'),
    ];

    // ¡NUEVO CAMPO AQUÍ! (moved it slightly for logical grouping with other text fields)
    $form['Banner']['slidecontent']['slide' . $i]['slide_alignment_text_' . $i] = [
      '#type' => 'select',
      '#title' => t('Text Alignment'),
      '#options' => [
        'left' => t('Left'),
        'center' => t('Center'),
        'right' => t('Right'),
      ],
      // Obtener el valor guardado o 'left' como valor por defecto
      '#default_value' => theme_get_setting("slide_alignment_text_{$i}", "market_wave") ?? 'left',
      '#description' => t('Select the alignment for the slide text.'),
    ];

    $form['Banner']['slidecontent']['slide' . $i]['slide_image_' . $i] = [
      '#type' => 'managed_file',
      '#title' => t('Image'),
      '#description' => t('Use same size for all the slideshow images (Recommended size : 1920 X 603).'),
      '#default_value' => theme_get_setting("slide_image_{$i}", "market_wave"),
      '#upload_location' => 'public://',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg gif'],
        'file_validate_size' => [$max_filesize_bytes],
      ],
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
    '#submit' => ['market_wave_add_one_submit'],
    '#attributes' => [
      'class' => ['button-addmore'],
    ],
    '#ajax' => [
      'callback' => 'market_wave_addmore_callback',
      'wrapper' => 'slide-wrapper',
    ],
  ];
  if ($num_rows_to_display > 1) {
    $form['Banner']['actions']['remove_last'] = [
      '#type' => 'submit',
      '#value' => t('Remove last one'),
      '#submit' => ['market_wave_removelast_submit'],
      '#attributes' => [
        'class' => ['button-remove'],
      ],
      '#ajax' => [
        'callback' => 'market_wave_removelast_callback',
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
function market_wave_addmore_callback(array &$form, FormStateInterface $form_state) {
  return $form['Banner']['slidecontent'];
}

/**
 * Submit handler for the "add-one-more" button.
 *
 * Increments the max counter and causes a rebuild.
 */
function market_wave_add_one_submit(array &$form, FormStateInterface $form_state) {
  $num_rows = $form_state->get('num_rows');
  $form_state->set('num_rows', $num_rows + 1);
  $form_state->setRebuild();
}

/**
 * Callback for both ajax-enabled buttons.
 *
 * Selects and returns the slide content in it.
 */
function market_wave_removelast_callback(array &$form, FormStateInterface $form_state) {
  return $form['Banner']['slidecontent'];
}

/**
 * Submit handler for the "remove last one" button.
 *
 * Decrements the max counter and causes a rebuild.
 */
function market_wave_removelast_submit(array &$form, FormStateInterface $form_state) {
  $num_rows = $form_state->get('num_rows');
  if ($num_rows > 1) {
    $form_state->set('num_rows', $num_rows - 1);
    // When removing, also clear the settings for the removed slide.
    $config_market_wave = \Drupal::configFactory()->getEditable('theme.market_wave');
    $keys_to_clear = [
      "slide_title_{$num_rows}",
      "slide_desc_{$num_rows}",
      "slide_title_color_{$num_rows}",
      "slide_desc_color_{$num_rows}",
      "slide_alignment_text_{$num_rows}",
      "slide_image_{$num_rows}",
      "slide_url_{$num_rows}",
      "slide_url_title_{$num_rows}",
    ];
    foreach ($keys_to_clear as $key) {
      $config_market_wave->clear($key);
    }
    $config_market_wave->save();
  }
  $form_state->setRebuild();
}

/**
 * Custom submit callback for the theme settings form.
 * This is crucial to save the dynamic fields when the main form is submitted.
 */
function market_wave_custom_submit_callback(&$form, FormStateInterface $form_state) {
  $config_market_wave = \Drupal::configFactory()->getEditable('theme.market_wave');

  // Save the total number of slides currently displayed/configured.
  // This value is used to determine how many slides to process on subsequent loads.
  $num_rows_submitted = $form_state->get('num_rows');
  $config_market_wave->set('slide_num', $num_rows_submitted);

  // Save the slideshow display setting.
  $config_market_wave->set('slideshow_display', $form_state->getValue('slideshow_display'));

  // Loop through all possible slides (up to the current num_rows_submitted) and save their values.
  for ($i = 1; $i <= $num_rows_submitted; $i++) {
    // Save text_format field with both 'value' and 'format'.
    $slide_desc = $form_state->getValue("slide_desc_{$i}");
    $config_market_wave
      ->set("slide_title_{$i}", $form_state->getValue("slide_title_{$i}"))
      ->set("slide_title_color_{$i}", $form_state->getValue("slide_title_color_{$i}"))
      ->set("slide_desc_{$i}", $slide_desc)
      ->set("slide_desc_color_{$i}", $form_state->getValue("slide_desc_color_{$i}"))
      ->set("slide_alignment_text_{$i}", $form_state->getValue("slide_alignment_text_{$i}"));

    // For managed_file, the value is an array of FIDs. We want to store the first one.
    $fid_array = $form_state->getValue("slide_image_{$i}");
    if (!empty($fid_array)) {
      $config_market_wave->set("slide_image_{$i}", reset($fid_array));
    } else {
      $config_market_wave->clear("slide_image_{$i}"); // Clear if no file selected
    }

    $config_market_wave
      ->set("slide_url_{$i}", $form_state->getValue("slide_url_{$i}"))
      ->set("slide_url_title_{$i}", $form_state->getValue("slide_url_title_{$i}"));
  }

  // Save the configuration.
  $config_market_wave->save();
}

/**
 * Controller for the Market Wave API.
 * This should ideally be in a separate module's src/Controller directory.
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
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * The service container.
   */
  public function __construct(ContainerInterface $container) {
    $this->configFactory = $container->get('config.factory');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container);
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
        // Get the description and remove HTML tags.
        $description_html = $config["slide_desc_{$i}"]['value'] ?? '';
        $description_plain = strip_tags($description_html);

        $sliderData[] = [
          'title' => $config["slide_title_{$i}"] ?? '',
          'title_color' => $config["slide_title_color_{$i}"] ?? '#ffffff',
          'description' => $description_plain,
          'description_color' => $config["slide_desc_color_{$i}"] ?? '#ffffff',
          'image' => $this->generateImageUrl($config["slide_image_{$i}"] ?? ''),
          'url' => $config["slide_url_{$i}"] ?? '',
          'url_title' => $config["slide_url_title_{$i}"] ?? '',
          'text_alignment' => $config["slide_alignment_text_{$i}"] ?? 'left',
        ];
      }
    }

    return new JsonResponse($sliderData);
  }

  /**
   * Generates the absolute URL for a file entity.
   *
   * @param int|string|array $fid
   * The file ID, or an array containing it.
   *
   * @return string
   * The absolute URL of the file, or an empty string if the file cannot be loaded.
   */
  protected function generateImageUrl($fid) {
    // Ensure $fid is treated as an integer.
    if (is_array($fid) && !empty($fid)) {
      $fid = reset($fid);
    }

    if (is_numeric($fid)) {
      $file = \Drupal::entityTypeManager()->getStorage('file')->load((int) $fid);
      if ($file instanceof \Drupal\file\Entity\File) {
        return \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
      }
    }
    return '';
  }
}

/**
 * Submit handler for the "add-one-more" button.
 */
function market_wave_add_one(array &$form, FormStateInterface $form_state) {
  $num_rows = $form_state->get('num_rows');
  $form_state->set('num_rows', $num_rows + 1);
  $form_state->setRebuild();
}

/**
 * Submit handler for the "remove last one" button.
 */
function market_wave_removelast(array &$form, FormStateInterface $form_state) {
  $num_rows = $form_state->get('num_rows');
  if ($num_rows > 1) {
    $form_state->set('num_rows', $num_rows - 1);
    $config_market_wave = \Drupal::configFactory()->getEditable('theme.market_wave');
    $keys_to_clear = [
      "slide_title_{$num_rows}",
      "slide_desc_{$num_rows}",
      "slide_title_color_{$num_rows}",
      "slide_desc_color_{$num_rows}",
      "slide_alignment_text_{$num_rows}",
      "slide_image_{$num_rows}",
      "slide_url_{$num_rows}",
      "slide_url_title_{$num_rows}",
    ];
    foreach ($keys_to_clear as $key) {
      $config_market_wave->clear($key);
    }
    $config_market_wave->save();
  }
  $form_state->setRebuild();
}