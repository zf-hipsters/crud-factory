<?php
namespace CrudFactory\Service\Strategy;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbTableGateway;

use CrudFactory\Service\CrudFactory as CrudService;
use CrudFactory\Service\ServiceInterface as CrudInterface;

/**
 * Class CrudFactory
 * @package CrudFactory\Service
 */
class TableGateway extends CrudService implements CrudInterface
{
    protected $tableGateway = null;
    protected $entity = null;

    /**
     * Used to create new rows
     * @param $entity
     * @return bool
     */
    public function create($postData)
    {
        $this->getHydrator()->hydrate($postData, $this->getEntity());

        $data = $this->getHydrator()->extract($this->getEntity());
        $attributes = $this->getHeaders();
        foreach ($attributes as $key=>$attrib) {
            if (isset($attrib['create']) && $attrib['create'] == false) {
                unset($data[$key]);
            }
        }

        $this->getTableGateway()->insert($data);

        return true;
    }

    /**
     * Fetch a single row from the default database adapter
     *
     * @param $id
     * @param bool $returnArray
     * @return array|\ArrayObject|null
     */
    public function read($id, $returnArray = false)
    {
        $results = $this->getTableGateway()->select(array('id' => $id));

        if ($returnArray) {
            $record = $results->current();
            $update = $this->getHydrator()->extract($record);

            return $update;
        }

        return $results->current();
    }

    /**
     * Read all rows from the database adapter
     * @param string $sort
     * @param string $dir
     * @return Paginator
     */
    public function readAll($sort = 'id', $dir = 'asc')
    {
        $tableGateway = $this->getTableGateway();
        $tableGateway->select();

        $sortBy = $sort . ' ' . $dir;

        $dbTableGatewayAdapter = new DbTableGateway($tableGateway, null, $sortBy);

        return new Paginator($dbTableGatewayAdapter);
    }

    /**
     * @param $entity
     * @return bool
     */
    public function update($postData)
    {
        $entity = $this->getEntity();
        $this->getHydrator()->hydrate($postData, $this->getEntity());

        $id = $entity->getId();
        $originalEntity = $this->read($id);

        /** @var \Zend\Stdlib\Hydrator\ClassMethods $hydrator */
        $this->getHydrator()->hydrate($this->getHydrator()->extract($entity), $originalEntity);

        $update = $this->getHydrator()->extract($entity);

        $attributes = $this->getHeaders();
        foreach ($attributes as $key=>$attrib) {
            if (isset($attrib['update']) && $attrib['update'] == false) {
                unset($update[$key]);
            }
        }

        $this->getTableGateway()->update($update, array('id' => $id));

        return true;
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function delete($id)
    {
        $tableGateway = $this->getTableGateway();

        if ($this->getConfig('soft_delete') === true) {
            $results = $tableGateway->select(array('id', $id));
            $record = $results->current();

            if (!method_exists($record, 'setDelete')) {
                throw new \Exception('Soft deletable is selected, but entity does not have a delete property/methods');
            }

            $record->setDelete(1);

            $update = $this->getHydrator()->extract($record);
            $tableGateway->update($update, array('id' => $id));

            return true;
        }

        $tableGateway->delete(array('id' => $id));
        return true;
    }



    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    public function setHydrator($hydrator)
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        if (is_null($this->entity)) {
            $this->entity = $this->getServiceLocator()->get($this->getConfig('entity_prototype'));
        }

        return $this->entity;
    }

    public function getTableGateway() {
        if (is_null($this->tableGateway)) {
            $this->tableGateway = $this->getServiceLocator()->get('CrudFactory\Service\Factory\TableGateway');
        }

        return $this->tableGateway;
    }

    public function getHydrator()
    {
        if (is_null($this->hydrator)) {
            $this->hydrator = $this->getServiceLocator()->get($this->getconfig('hydrator_class'));
        }

        return $this->hydrator;
    }

}