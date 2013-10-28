<?php
namespace CrudFactory\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

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
                $this->getService()->update($form->getData());

                $module = strtolower($this->params()->fromRoute('__CONTROLLER__'));
                $this->flashMessenger()->setNamespace('success')->addMessage('The row was successfully updated.');
                return $this->redirect()->toRoute($module);

            }

            $this->flashMessenger()->setNamespace('error')->addMessage('Please check the form fields below for errors.');
        }

        $viewModel = new ViewModel(array(
            'module' => $this->params()->fromRoute('__CONTROLLER__'),
            'form' =>$form
        ));

        $viewModel->setTemplate('crud-factory/crud-factory/update');

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
        $this->flashMessenger()->setNamespace('success')->addMessage('The row was successfully deleted.');
        $this->redirect()->toRoute($module);

    }

    /**
     * Return the crud factory service
     * @return object CrudFactory\Service\CrudFactory
     */
    protected function getService()
    {
        if (!$this->service instanceof \CrudFactory\Service\CrudFactory) {
            $this->service = $this->getServiceLocator()->get('CrudFactory\Service\CrudFactory');
        }

        return $this->service;
    }
}