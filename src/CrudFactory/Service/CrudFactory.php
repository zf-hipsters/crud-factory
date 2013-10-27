<?php
namespace CrudFactory\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbTableGateway;
use Zend\Db\Sql\Select;
use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;
use Zend\Validator;


class CrudFactory implements ServiceLocatorAwareInterface
{
    public function create($entity)
    {
        $tableGateway = $this->getServiceLocator()->get('CrudFactory\Service\Factory\TableGateway');

        $hydrator = $this->getServiceLocator()->get($this->getconfig('hydrator_class'));

        $update = $hydrator->extract($entity);
        unset($update['attributes']);

        $attributes = $entity->getAttributes();
        foreach ($attributes as $key=>$attrib) {
            if (isset($attrib['create']) && $attrib['create'] == false) {
                unset($update[$key]);
            }
        }

        $tableGateway->insert($update);

        return true;
    }

    public function read($id, $returnArray = false)
    {

        /** @var \Zend\Db\TableGateway\AbstractTableGateway $tableGateway */
        $tableGateway = $this->getServiceLocator()->get('CrudFactory\Service\Factory\TableGateway');
        $results = $tableGateway->select(array('id' => $id));

        if ($returnArray) {
            $record = $results->current();
            $hydrator = $this->getServiceLocator()->get($this->getconfig('hydrator_class'));
            $update = $hydrator->extract($record);
            unset($update['attributes']);

            return $update;
        }

        return $results->current();
    }

    public function readAll($sort = 'id', $dir = 'asc')
    {
        /** @var \Zend\Db\TableGateway\AbstractTableGateway $tableGateway */
        $tableGateway = $this->getServiceLocator()->get('CrudFactory\Service\Factory\TableGateway');
        $tableGateway->select();

        $sortBy = $sort . ' ' . $dir;

        $dbTableGatewayAdapter = new DbTableGateway($tableGateway, null, $sortBy);

        return new Paginator($dbTableGatewayAdapter);
    }

    public function update($entity)
    {
        $id = $entity->getId();

        $tableGateway = $this->getServiceLocator()->get('CrudFactory\Service\Factory\TableGateway');

        $originalEntity = $this->read($id);

        $hydrator = $this->getServiceLocator()->get($this->getconfig('hydrator_class'));

        /** @var \Zend\Stdlib\Hydrator\ClassMethods $hydrator */
        $hydrator->hydrate($hydrator->extract($entity), $originalEntity);

        $update = $hydrator->extract($entity);
        unset($update['attributes']);

        $attributes = $entity->getAttributes();
        foreach ($attributes as $key=>$attrib) {
            if (isset($attrib['update']) && $attrib['update'] == false) {
                unset($update[$key]);
            }
        }

        $tableGateway->update($update, array('id' => $id));

        return true;
    }

    public function delete($id)
    {
        $tableGateway = $this->getServiceLocator()->get('CrudFactory\Service\Factory\TableGateway');
        if ($this->getConfig('soft_delete') === true) {
            $results = $tableGateway->select(array('id', $id));
            $record = $results->current();

            if (!method_exists($record, 'setDelete')) {
                throw new \Exception('Soft deletable is selected, but entity does not have a delete property/methods');
            }

            $hydrator = $this->getServiceLocator()->get($this->getconfig('hydrator_class'));
            $record->setDelete(1);

            $update = $hydrator->extract($record);
            unset($update['attributes'], $update['id']);

            $tableGateway->update($update, array('id' => $id));

            return true;
        }

        $tableGateway->delete(array('id' => $id));
        return true;
    }

    public function buildForm($create = true)
    {
        $entity = $this->getServiceLocator()->get($this->getConfig('entity_prototype'));
        $hydrator = $this->getServiceLocator()->get($this->getconfig('hydrator_class'));

        $attributes = $entity->getAttributes();

        $form = new Form();
        $form->setHydrator($hydrator);
        $form->bind($entity);

        $filter = new InputFilter();

        $validators = array(
            'Zend\Validator\NotEmpty'
        );

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
                    case 'passowrd':
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

    public function getHeaders()
    {
        $entity = $this->getServiceLocator()->get($this->getConfig('entity_prototype'));
        return $entity->getAttributes();
     }

    public function getConfig($key = null) {
        $config = $this->getServiceLocator()->get('CrudFactory\Service\Factory\Config');

        if (!is_null($key)) {
            return $config[$key];
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