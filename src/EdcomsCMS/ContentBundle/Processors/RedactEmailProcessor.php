<?php
/**
 * Created by PhpStorm.
 * User: joank
 * Date: 24-Mar-20
 * Time: 5:49 PM
 */
namespace EdcomsCMS\ContentBundle\Processors;

class RedactEmailProcessor extends AbstractProcessor
{
    /**
     * @param string $message
     *
     * @return string
     */
    public function __invoke(string $message): string
    {
        if(!empty($message) ) {
            $message = preg_replace_callback(
                "/([a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`\"\"{|}~-]+)*(@|\sat\s)(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?(\.|\"\"\sdot\s))+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)/",
                function ($matches) {
                    return $this->getHashedValue($matches[0]);
                },
                $message
            );
        }

        return $message;
    }
}
