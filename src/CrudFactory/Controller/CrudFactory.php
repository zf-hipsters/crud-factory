<?php
namespace CrudFactory\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Stdlib\Hydrator\ArraySerializable;
use Zend\View\Model\ViewModel;

use CrudFactory\Service\CrudFactory as CrudService;

/**
 * Class CrudFactory
 * @package CrudFactory\Controller
 */
class CrudFactory extends AbstractActionController
{
    /**
     * Service Singleton
     * @var
     */
    protected $service;

    /**
     * Crud - Create Row
     * @return \Zend\Http\Response|ViewModel
     */
    public function createAction()
    {
        $request = $this->getRequest();
        $form = $this->getService()->buildForm();

        if ($request->isPost()) {
            $postVars = $this->params()->fromPost();
            $form->setData($postVars);

            if ($form->isValid()) {
                $module = strtolower($this->params()->fromRoute('__CONTROLLER__'));

                if ($this->getService()->create($form->getData()))
                {
                    $this->flashMessenger()->setNamespace('success')->addMessage('The row was successfully added.');
                } else {
                    $this->flashMessenger()->setNamespace('error')->addMessage('The row was unable to be added.');
                }

                return $this->redirect()->toRoute($module);

            }

            $this->flashMessenger()->setNamespace('error')->addMessage('Please check the form fields below for errors.');
        }

        $viewModel = new ViewModel(array(
            'module' => $this->params()->fromRoute('__CONTROLLER__'),
            'form' =>$form
        ));

        $viewModel->setTemplate('crud-factory/crud-factory/create');

        return $viewModel;
    }

    /**
     * cRud - Read all rows
     * @return ViewModel
     */
    public function readAction()
    {
        $sort = ($this->params()->fromQuery('sort'))?:'id';
        $dir = ($this->params()->fromQuery('dir'))?:'asc';

        $paginator =$this->getService()->readAll($sort, $dir);

        if (!$paginator instanceof Paginator && !is_array($paginator) ) {
            throw new \Exception('ReadAll must return an instance of Zend\Paginator\Paginator or an array');
        }

        if (is_array($paginator)) {
            $paginatorArray = array();
            foreach ($paginator as $pag) {
                $entity = clone $this->getServiceLocator()->get('CrudFactory\Entity\ArrayEntity');
                $hydrate = new ArraySerializable();
                $hydrate->hydrate($pag, $entity);

                $paginatorArray[] = $entity;

            }

            $paginator = new Paginator(new ArrayAdapter($paginatorArray));
        }

        $paginator->setCurrentPageNumber((int)$this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        $viewModel = new ViewModel(
            array(
                'module' => strtolower($this->params()->fromRoute('__CONTROLLER__')),
                'results' => $paginator,
                'title' => $this->getService()->getConfig('title'),
                'headers' => $this->getService()->getHeaders(),
                'sort' => ($sort)?:'id',
                'direction' => ($dir)?:'asc'
            )
        );

        $viewModel->setTemplate('crud-factory/crud-factory/index');

        return $viewModel;
    }

    /**
     * crUd - Update Row
     * @return \Zend\Http\Response|ViewModel
     * @throws \Exception
     */
    public function updateAction()
    {
        $id = $this->params()->fromRoute('id');

        if (!$id) {
            throw new \Exception('A valid ID is required to complete this action.');
        }

        $entity = $this->getService()->read($id, true);

        $request = $this->getRequest();
        $form = $this->getService()->buildForm(false);
        $form->setData($entity);

        if ($request->isPost()) {
            $postVars = $this->params()->fromPost();
            $form->setData($postVars);

            if ($form->isValid()) {
                $module = strtolower($this->params()->fromRoute('__CONTROLLER__'));

                if ($this->getService()->update($form->getData()))
                {
                    $this->flashMessenger()->setNamespace('success')->addMessage('The row was successfully updated.');
                } else {
                    $this->flashMessenger()->setNamespace('error')->addMessage('The row was unable to be updated.');
                }

                return $this->redirect()->toRoute($module);
            }

            $this->flashMessenger()->setNamespace('error')->addMessage('Please check the form fields below for errors.');
        }

        $viewModel = new ViewModel(array(
            'module' => $this->params()->fromRoute('__CONTROLLER__'),
            'form' =>$form
        ));

        $viewModel->setTemplate('crud-factory/crud-factory/config');

        return $viewModel;
    }

    /**
     * cruD - Delete Row
     * @throws \Exception
     */
    public function deleteAction()
    {
        if (!$this->params()->fromRoute('id')) {
            throw new \Exception('A valid ID is required to complete this action.');
        }

        $id = $this->params()->fromRoute('id');
        $this->getService()->delete($id);

        $module = strtolower($this->params()->fromRoute('__CONTROLLER__'));

        if ($this->getService()->delete($id))
        {
            $this->flashMessenger()->setNamespace('success')->addMessage('The row was successfully deleted.');
        } else {
            $this->flashMessenger()->setNamespace('error')->addMessage('The row was unable to be deleted.');
        }

        return $this->redirect()->toRoute($module);

    }

    public function configAction()
    {
        $request = $this->getRequest();
        $entity = $this->getService()->getEntity();


        $config = new \Zend\Config\Config($this->getServiceLocator()->get('Config'), true);

        var_Dump(strtolower($this->params()->fromRoute('__CONTROLLER__')));
        /** @var \Zend\Config\Config $cfconfig */
        $cfconfig = $config['crud-factory'][strtolower($this->params()->fromRoute('__CONTROLLER__'))];
//        $cfconfig->people = 'dog';

        $ewriter = new \Zend\Config\Writer\PhpArray();
        $ewriter->toString($cfconfig);

        $newEntity = $this->getServiceLocator()->get('CrudFactory\Entity\Config');
        $this->getService()->setEntity($newEntity);

        $form = $this->getService()->buildForm();

        if ($request->isPost()) {
            $postVars = $this->params()->fromPost();
            $form->setData($postVars);

            if ($form->isValid()) {
                $this->getService()->create($form->getData());

                $module = strtolower($this->params()->fromRoute('__CONTROLLER__'));
                $this->flashMessenger()->setNamespace('success')->addMessage('The row was successfully created.');
                return $this->redirect()->toRoute($module);

            }

            $this->flashMessenger()->setNamespace('error')->addMessage('Please check the form fields below for errors.');
        }

        $viewModel = new ViewModel(array(
            'module' => $this->params()->fromRoute('__CONTROLLER__'),
            'form' =>$form
        ));

        $viewModel->setTemplate('crud-factory/crud-factory/config');

        return $viewModel;
    }

    /**
     * Return the crud factory service
     * @return object CrudFactory\Service\CrudFactory
     */
    protected function getService()
    {
        if (!$this->service instanceof \CrudFactory\Service\ServiceInterface) {
            $abstract = $this->getServiceLocator()->get('CrudFactory\Service\CrudFactory');
            $config = $abstract->getConfig('service_class');

            if ($abstract->getConfig('service_class')) {
                $service = $this->getServiceLocator()->get($abstract->getConfig('service_class'));
            } else {
                $service = $this->getServiceLocator()->get('CrudFactory\Service\Strategy\TableGateway');
            }


            if (!$service instanceof \CrudFactory\Service\ServiceInterface) {
                throw new \Exception('The service class must implement CrudFactory\Service\ServiceInterface');
            }

            $this->service = $service;
        }

        return $this->service;
    }
}