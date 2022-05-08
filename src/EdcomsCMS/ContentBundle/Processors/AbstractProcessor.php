<?php
/**
 * Created by PhpStorm.
 * User: joank
 * Date: 24-Mar-20
 * Time: 5:48 PM
 */
namespace EdcomsCMS\ContentBundle\Processors;

abstract class AbstractProcessor
{
    /**
     * @var null|string
     */
    private $salt;

    /**
     * @param string $salt
     */
    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    abstract public function __invoke(string $record): string;

    /**
     * @param string $value
     *
     * @return string
     */
    protected function getHashedValue(string $value)
    {
        return sha1($value . $this->salt);
    }
}