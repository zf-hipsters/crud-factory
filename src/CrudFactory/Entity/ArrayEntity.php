<?php
namespace CrudFactory\Entity;

class ArrayEntity
{
    protected $data;

    public function exchangeArray($data)
    {
        foreach ($data as $key=>$value) {
            $this->data[$key] = $value;
        }
    }

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
                $this->data[$value] = $params;
            }
            return $this;
        }
    }

    function __get($name) {
        echo $name;
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
    }

    function __set($name, $value) {
        if (isset($this->data[$name])) {
            $this->data[$name] = $value;
        }
        return $this;
    }

}