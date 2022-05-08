<?php
/**
 * Created by PhpStorm.
 * User: joank
 * Date: 25-Mar-20
 * Time: 9:59 PM
 */
namespace EdcomsCMS\ContentBundle\Service\Logger\Slack;

use Monolog\Logger;
use Monolog\Utils;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Formatter\FormatterInterface;
use EdcomsCMS\ContentBundle\Processors\RedactEmailProcessor;
use EdcomsCMS\ContentBundle\Processors\RedactIpProcessor;

/**
 * Slack record utility helping to log to Slack webhooks or API.
 *
 * @see    https://api.slack.com/incoming-webhooks
 * @see    https://api.slack.com/docs/message-attachments
 */
class SlackRecord
{
    const COLOR_DANGER = 'danger';
    const COLOR_WARNING = 'warning';
    const COLOR_GOOD = 'good';
    const COLOR_DEFAULT = '#e3e4e6';

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var NormalizerFormatter
     */
    private $normalizerFormatter;

    /**
     * Slack Parameters
     * @var array
     */
    protected $slackParams;

    /**
     * Processors that will process all log records
     *
     * To process records of a single handler instead, add the processor on that specific handler
     *
     * @var callable[]
     */
    protected $processors;

    public function __construct(
        $slackParams,
        FormatterInterface $formatter = null,
        $normalizerFormatter = null,
        $processors = array()
    )
    {
        $this->slackParams = $slackParams;
        $this->formatter = $formatter;
        $this->processors = $processors;

        if (isset($this->slackParams['includeContextAndExtra'])
            && !empty($this->slackParams['includeContextAndExtra'])) {
            $this->normalizerFormatter = new NormalizerFormatter();
        }
    }

    public function getSlackData(array $record)
    {
        $dataArray = array();

        $dataArray['username'] = $this->slackParams['bot_name'];
        $dataArray['channel'] = $this->slackParams['channel'];

        // Add the processors
        if (isset($this->slackParams['processors']) && is_array($this->slackParams['processors'])) {
            foreach($this->slackParams['processors'] as $processor) {
                switch ($processor) {
                    case 'email':
                        $emailProcessor = new RedactEmailProcessor();
                        $emailProcessor->setSalt($this->slackParams['saltKey']);
                        $this->addProcessor($emailProcessor);
                        break;
                    case 'ipv4':
                        $ipProcessor = new RedactIpProcessor();
                        $ipProcessor->setSalt($this->slackParams['saltKey']);
                        $this->addProcessor($ipProcessor);
                        break;
                }
            }

            // Execute processors for the message
            $record['message'] = $this->executeProcessors($record['message']);
        }

        if ($this->formatter && !$this->useAttachment) {
            $message = $this->formatter->format($record);
        } else {
            $message = $record['message'];
        }

        if ($this->slackParams['useAttachment']) {
            $attachment = array(
                'fallback'  => $message,
                'text'      => $message,
                'color'     => $this->getAttachmentColor($record['level']),
                'fields'    => array(),
                'mrkdwn_in' => array('fields'),
                'ts'        => $record['datetime']->getTimestamp()
            );

            if ($this->slackParams['useShortAttachment']) {
                $attachment['title'] = $record['level_name'];
            } else {
                $attachment['title'] = 'Message';
                $attachment['fields'][] = $this->generateAttachmentField('Level', $record['level_name']);
            }

            if ($this->slackParams['includeContextAndExtra']) {
                foreach (array('extra', 'context') as $key) {
                    if (empty($record[$key])) {
                        continue;
                    }

                    if ($this->slackParams['useShortAttachment']) {
                        $attachment['fields'][] = $this->generateAttachmentField(
                            $key,
                            $record[$key]
                        );
                    } else {
                        // Add all extra fields as individual fields in attachment
                        $attachment['fields'] = array_merge(
                            $attachment['fields'],
                            $this->generateAttachmentFields($record[$key])
                        );
                    }
                }
            }

            $dataArray['attachments'] = array($attachment);
        } else {
            $dataArray['text'] = $message;
        }

        if ($this->slackParams['icon_emoji']) {
            if (filter_var($this->slackParams['icon_emoji'], FILTER_VALIDATE_URL)) {
                $dataArray['icon_url'] = $this->slackParams['icon_emoji'];
            } else {
                $dataArray['icon_emoji'] = $this->slackParams['icon_emoji'];
            }
        }

        return $dataArray;
    }

    /**
     * Returned a Slack message attachment color associated with
     * provided level.
     *
     * @param  int    $level
     * @return string
     */
    public function getAttachmentColor($level)
    {
        switch (true) {
            case $level >= Logger::ERROR:
                return self::COLOR_DANGER;
            case $level >= Logger::WARNING:
                return self::COLOR_WARNING;
            case $level >= Logger::INFO:
                return self::COLOR_GOOD;
            default:
                return self::COLOR_DEFAULT;
        }
    }

    /**
     * Stringifies an array of key/value pairs to be used in attachment fields
     *
     * @param array $fields
     *
     * @return string
     */
    public function stringify($fields)
    {
        $normalized = $this->normalizerFormatter->format($fields);
        $prettyPrintFlag = defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 128;
        $flags = 0;
        if (PHP_VERSION_ID >= 50400) {
            $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        }

        $hasSecondDimension = count(array_filter($normalized, 'is_array'));
        $hasNonNumericKeys = !count(array_filter(array_keys($normalized), 'is_numeric'));

        return $hasSecondDimension || $hasNonNumericKeys
            ? Utils::jsonEncode($normalized, $prettyPrintFlag | $flags)
            : Utils::jsonEncode($normalized, $flags);
    }

    /**
     * Generates attachment field
     *
     * @param string       $title
     * @param string|array $value
     *
     * @return array
     */
    private function generateAttachmentField($title, $value)
    {
        // execute the processors also for the context
        if (isset($this->slackParams['processors']) && is_array($this->slackParams['processors'])) {
            $serialisedValue = json_encode($value);
            $serialisedValue = $this->executeProcessors($serialisedValue);
            $value = json_decode($serialisedValue, true);
        }

        $value = is_array($value)
            ? sprintf('```%s```', $this->stringify($value))
            : $value;

        return array(
            'title' => ucfirst($title),
            'value' => $value,
            'short' => false
        );
    }

    /**
     * Generates a collection of attachment fields from array
     *
     * @param array $data
     *
     * @return array
     */
    private function generateAttachmentFields(array $data)
    {
        $fields = array();
        foreach ($this->normalizerFormatter->format($data) as $key => $value) {
            $fields[] = $this->generateAttachmentField($key, $value);
        }

        return $fields;
    }

    /**
     * Add the processor
     *
     * @param object $processor
     *
     * @return void
     */
    public function addProcessor($processor)
    {
        if (!is_callable($processor)) {
            throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), '.var_export($processor, true).' given');
        }

        try {
            array_unshift($this->processors, $processor);
        } catch (Exception $e) {
        }
    }

    /**
     * Execute the processors on the $message
     *
     * @param string $message
     *
     * @return string
     */
    public function executeProcessors(string $message)
    {
        try {
            foreach ($this->processors as $processor) {
                $message = call_user_func($processor, $message);
            }
        } catch (Exception $e) {
        }

        return $message;
    }
}
