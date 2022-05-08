<?php
/**
 * Created by PhpStorm.
 * User: joank
 * Date: 24-Mar-20
 * Time: 5:57 PM
 */
namespace EdcomsCMS\ContentBundle\Processors;

class RedactIpProcessor extends AbstractProcessor
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
                "/(\b\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3}\b)/",
                function ($matches) {
                    return $this->getHashedValue($matches[0]);
                },
                $message
            );
        }

        return $message;
    }
}
