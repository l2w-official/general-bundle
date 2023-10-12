<?php

namespace LearnToWin\GeneralBundle\Message;

readonly class EntityMessage
{
    /**
     * @param string $resource This is the resource name (e.g. User, Organization, etc.)
     * @param string $action This is the action that was performed (e.g. persist, remove, update, etc.)
     * @param string $data This is the json serialized data of the entity that was affected
     */
    public function __construct(private string $resource, private string $action, private string $data)
    {
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
