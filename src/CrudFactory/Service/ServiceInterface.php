<?php
namespace CrudFactory\Service;

interface ServiceInterface
{
    public function create($entity);
    public function read($id);
    public function readAll();
    public function update($entity);
    public function delete($id);
}