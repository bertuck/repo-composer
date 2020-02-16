<?php

namespace Drupal\kb_error_pages\Form;


use Drupal\Core\Cache\Cache;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

class Configuration404Form extends ConfigFormBase {


    /**
     * @var EntityTypeManagerInterface
     */
    protected $entityTypeManager;

    /**
     * @var ConfigFactoryInterface
     */
    protected $config;

    /**
     * @var StateInterface
     */
    protected $state;

    /**
     * @param EntityTypeManagerInterface $entityTypeManager
     * @param ConfigFactoryInterface $config
     * @param StateInterface $state
     */
    public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $config, StateInterface $state) {
        $this->entityTypeManager = $entityTypeManager;
        $this->config = $config;
        $this->state = $state;
        parent::__construct($this->config);
    }

    /**
     * @param ContainerInterface $container
     * @return static
     */
    public static function create(ContainerInterface $container) {
        return new static(
            $container->get('entity_type.manager'),
            $container->get('config.factory'),
            $container->get('state')
        );

    }

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'kb_error_pages.form_404_configuration';
    }

    /**
     * @return array
     */
    protected function getEditableConfigNames() {
        return array(
            'kb_error_pages.404_configuration'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $form['link'] = array(
            '#title' => $this->t('Voir la page'),
            '#type' => 'link',
            '#url' => Url::fromRoute('kb_error_pages.not_found')
        );

        $form['error_404_title'] = array(
            '#type' => 'textfield',
            '#title' => $this->t('Titre'),
            '#default_value' => $this->state->get('error_404_title'),
            '#required' => TRUE,
        );

        $form['error_404_description'] = array(
            '#type' => 'text_format',
            '#title' => $this->t('Description'),
            '#default_value' => $this->state->get('error_404_description'),
            '#format'=> 'full_html',
            '#required' => FALSE,
        );

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        $errorTitle = $form_state->getValue('error_404_title');
        $errorDescription = $form_state->getValue('error_404_description')['value'];
        $this->config->getEditable('system.site')->set('page.404', '/not-found')->save();
        $this->state->set('error_404_title', $errorTitle);
        $this->state->set('error_404_description', $errorDescription);
        \Drupal::messenger()->addMessage(t("La page 404 à été correctement mise à jour."));

        // INVALIDATING CACHE TAGS
        Cache::invalidateTags(['config:aw.error.pages.404']);
    }
}