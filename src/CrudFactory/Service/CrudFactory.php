<?php
namespace CrudFactory\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\Validator;


/**
 * Class CrudFactory
 * @package CrudFactory\Service
 */
class CrudFactory implements ServiceLocatorAwareInterface
{
    /**
     * @param bool $create
     * @return Form
     */
    public function buildForm($create = true)
    {
        $attributes = $this->getHeaders();

        $form = new Form();
        $filter = new InputFilter();

        $validators = array();

        foreach ($attributes as $key => $attribute) {
            $label = (isset($attribute['title'])?$attribute['title']:ucwords($key));
            $type = (isset($attribute['type'])?$attribute['type']:'text');

            switch ($type) {
                case 'text':
                    $element = new Element\Text($key);
                    break;
                case 'int':
                    $element = new Element\Text($key);
                    $validators[] = 'Zend\Validator\Digits';
                    $type = 'text';
                    break;
                case 'float':
                    $element = new Element\Text($key);
                    $validators[] = 'Zend\I18n\Validator\Float';
                    $type = 'text';
                    break;
                case 'password':
                    $element = new Element\Password($key);
                    break;
                case 'select':
                    $element = new Element\Select($key);
                    break;
                case 'radio':
                    $element = new Element\Radio($key);
                    break;
                case 'hidden':
                    $element = new Element\Hidden($key);
                    break;
                case 'checkbox':
                    $element = new Element\Checkbox($key);
                    break;
                case 'textarea':
                    $element = new Element\Textarea($key);
                    break;
                case 'wysiwyg':
                    $element = new Element\Textarea($key);
                    break;
                default:
                    $element = new Element($key);
            }

            if ($create === true) {
                if (isset($attribute['create']) && $attribute['create'] === false) {
                    continue;
                }
            } else {
                if (isset($attribute['update']) && $attribute['update'] === false) {
                    continue;
                }
            }

            $element->setLabel($label);
            $element->setAttributes(array(
                'type'  => $type
            ));

            if (isset($attribute['options'])) {
                $element->setOptions(array(
                    'value_options' => $attribute['options']
                ));
            }

            if (isset($attribute['required']) && $attribute['required'] === true) {
                $validators[] =  'Zend\Validator\NotEmpty';
            }

            if (!empty($validators)) {
                foreach ($validators as $validator) {
                    $input = new Input($key);
                    $input->getValidatorChain()
                        ->attach(new $validator);
                    $filter->add($input);
                }
            }

            $form->setInputFilter($filter);
            $form->add($element);
        }


        $element = new Element($key);
        $element->setAttributes(array(
            'type'  => 'button',
            'value' => '<span class="glyphicon glyphicon-chevron-left"></span> Back',
            'name' => 'btnBack',
        ));
        $form->add($element);

        $element = new Element($key);
        $element->setAttributes(array(
            'type'  => 'submit',
            'value' => 'Submit',
            'name' => 'btnSubmit',
        ));
        $form->add($element);

        $form->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'csrf'
        ));

        return $form;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->getConfig('table_configuration');
    }

    /**
     * @param null $key
     * @return array|object
     */
    public function getConfig($key = null) {
        $config = $this->getServiceLocator()->get('CrudFactory\Service\Factory\Config');

        if (!is_null($key)) {
            if (isset($config[$key])) {
                return $config[$key];
            }

            return false;
        }

        return $config;
    }

    /**
     * Set serviceManager instance
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return void
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Retrieve serviceManager instance
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

}