<?php

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class pixel_admin_home extends Module
{
    /**
     * Module's constructor.
     */
    public function __construct()
    {
        $this->name = 'pixel_admin_home';
        $this->version = '1.0.0';
        $this->author = 'Pixel';
        $this->tab = 'front_office_features';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Pixel Admin Home', [], 'Modules.Pixeladminhome.Admin');
        $this->description = $this->trans('Allow selecting any page as the admin homepage.', [], 'Modules.Pixeladminhome.Admin');

        $this->ps_versions_compliancy = [
            'min' => '8.0.0',
            'max' => _PS_VERSION_,
        ];
    }

    /**
     * Use the new translation system
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * Install module and register hooks to allow grid modification.
     *
     * @return bool
     */
    public function install(): bool
    {
        return parent::install() && $this->registerHook('actionEmployeeFormBuilderModifier');
    }

    /**
     * Employee form
     *
     * @param array $params
     */
    public function hookActionEmployeeFormBuilderModifier(array $params)
    {
        /** @var FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'];

        try {
            $field = $formBuilder->get('default_page');
            $formBuilder->add(
                'default_page',
                ChoiceType::class,
                [
                    'label' => $field->getOption('label'),
                    'help' => $field->getOption('help'),
                    'attr' => $field->getOption('attr'),
                    'choices' => $this->getChoices(),
                ]
            );
        } catch (Exception $e) {}
    }

    /**
     * Retrieve tab choices
     *
     * @return array
     */
    protected function getChoices(): array
    {
        $file = $this->getLocalPath() . 'pixel_admin_home.txt';

        if (!is_file($file) || !is_readable($file)) {
            return $this->getDefault();
        }

        $classNames = array_filter(
            array_map(function ($value) { return preg_replace( "/\r|\n/", "", $value); }, file($file))
        );
        if (empty($classNames)) {
            return $this->getDefault();
        }

        $choices = [];
        foreach ($classNames as $className) {
            $tab = Tab::getInstanceFromClassName($className);
            if (!$tab->id) {
                continue;
            }
            $choices = array_merge($choices, $this->getChoice($tab));
        }

        if (empty($choices)) {
            return $this->getDefault();
        }

        return $choices;
    }

    /**
     * Retrieve default tab
     *
     * @return array
     */
    protected function getDefault(): array
    {
        return $this->getChoice(Tab::getInstanceFromClassName('AdminDashboard'));
    }

    /**
     * Retrieve single tab choice
     *
     * @param Tab $tab
     * @return array
     */
    protected function getChoice(Tab $tab): array
    {
        return [
            $tab->wording ? $tab->wording . ' (' . $tab->class_name . ')' : $tab->class_name => $tab->id,
        ];
    }
}
