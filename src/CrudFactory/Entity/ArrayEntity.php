<?php
namespace CrudFactory\Entity;

/**
 * Class ArrayEntity
 * @package CrudFactory\Entity
 */
class ArrayEntity
{
    /**
     * Data store
     * @var
     */
    protected $data;

    /**
     * Used by Hydrate method
     * @param $data
     */
    public function exchangeArray($data)
    {
        foreach ($data as $key=>$value) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Used by Extract method
     * @return mixed
     */
    public function getArrayCopy() {
        return $this->data;
    }

    /**
     * Used to remove redundant fields after initial build
     * @param $data
     * @return mixed
     */
    public function cleanData($data)
    {
        $vars = $this->getArrayCopy();

        foreach ($data as $key=>$value)
        {
            if (!isset($vars[$key]))
            {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Magic Method for method calls
     * @param $name
     * @param $params
     * @return $this
     */
    function __call($name, $params) {
        $type = substr($name, 0, 3);
        $value = strtolower(str_replace($type, '', $name));

        if ($type == 'get') {
            if (isset($this->data[$value])) {
                return $this->data[$value];
            }
        }

        if ($type == 'set') {
            if (isset($this->data[$value])) {
                $this->data[$value] = $params[0];
            }
            return $this;
        }
    }

    /**
     * Magic Method for getters
     * @param $name
     * @return mixed
     */
    function __get($name) {
        echo $name;
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }

    /**
     * Magic Method for setters
     * @param $name
     * @param $value
     * @return $this
     */
    function __set($name, $value) {
        if (isset($this->data[$name])) {
            $this->data[$name] = $value;
        }
        return $this;
    }

}