<?php
namespace CrudFactory\Entity;

class Config {

    protected $title;
    protected $table;
    protected $softDelete;
    protected $entityPrototype;
    protected $hydratorClass;
    protected $route;

    protected $attributes = array(
        'title' => array(
            'title' => 'Module Title',
            'type' => 'text',
        ),
        'table' => array(
            'title' => 'Database Table',
            'type' => 'text',
        ),
        'route' => array(
            'title' => 'Route Url',
        ),
        'soft_delete' => array(
            'title' => 'Soft Delete',
            'type' => 'radio',
            'required' => true,
            'options' => array('true' => 'True', 'false' => 'False'),
        ),
        'static' => array(
            'title' => '<h4>Advanced users only</h4>',
            'type' => 'static',
        ),
        'entity_prototype' => array(
            'title' => 'Entity Prototype',
            'type' => 'text',
        ),
        'hydrator_class' => array(
            'title' => 'Hydrator Class',
            'type' => 'text',
        ),
    );

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param mixed $entityPrototype
     */
    public function setEntityPrototype($entityPrototype)
    {
        $this->entityPrototype = $entityPrototype;
    }

    /**
     * @return mixed
     */
    public function getEntityPrototype()
    {
        return $this->entityPrototype;
    }

    /**
     * @param mixed $hydratorClass
     */
    public function setHydratorClass($hydratorClass)
    {
        $this->hydratorClass = $hydratorClass;
    }

    /**
     * @return mixed
     */
    public function getHydratorClass()
    {
        return $this->hydratorClass;
    }

    /**
     * @param mixed $softDelete
     */
    public function setSoftDelete($softDelete)
    {
        $this->softDelete = $softDelete;
    }

    /**
     * @return mixed
     */
    public function getSoftDelete()
    {
        return $this->softDelete;
    }

    /**
     * @param mixed $table
     */
    public function setTable($table)
    {
        $this->table = $table;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $route
     */
    public function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->route;
    }


}