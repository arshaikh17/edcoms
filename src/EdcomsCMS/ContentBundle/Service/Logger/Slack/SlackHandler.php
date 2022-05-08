<?php
/**
 * Created by PhpStorm.
 * User: joank
 * Date: 24-Mar-20
 * Time: 7:30 PM
 */
namespace EdcomsCMS\ContentBundle\Service\Logger\Slack;

use Monolog\Handler\MissingExtensionException;
use Monolog\Handler\SocketHandler;
use Monolog\Logger;
use Monolog\Utils;

class SlackHandler extends SocketHandler
{
    const BOT_NAME                      = 'Edcoms DEV';
    const USE_ATTACHMENT                = true;
    const ICON_EMOJI                    = ':ghost:';
    const LEVEL                         = Logger::CRITICAL;
    const BUBBLE                        = true;
    const USE_SHORT_ATTACHMENT          = false;
    const INCLUDE_CONTEXT_AND_EXTRA     = true;
    const SALT_KEY                      = 'h@tsefl@ts';
    const PROCESSORS                    = '';

    /**
     * @var $slackParams
     */
    private $slackParams;

    /**
     * Instance of the SlackRecord util class preparing data for Slack API.
     * @var SlackRecord
     */
    private $slackRecord;

    /**
     * @param  array    $slackParams    slack parameters
     * @throws MissingExtensionException If no OpenSSL PHP extension configured
     */
    public function __construct($slackParams)
    {
        if (!extension_loaded('openssl')) {
            throw new MissingExtensionException('The OpenSSL PHP extension is required to use the SlackHandler');
        }

        if(!isset($slackParams['token']) || !isset($slackParams['channel'])) {
            throw new \Exception('Slack API Token and Slack Channel are required!');
        }

        $token = $slackParams['token'];
        $channel = $slackParams['channel'];

        if(!isset($slackParams['bot_name'])) $slackParams['bot_name'] = self::BOT_NAME;
        if(!isset($slackParams['useAttachment'])) $slackParams['useAttachment'] = self::USE_ATTACHMENT;
        if(!isset($slackParams['icon_emoji'])) $slackParams['icon_emoji'] = self::ICON_EMOJI;
        if(!isset($slackParams['level'])) $slackParams['level'] = self::LEVEL;
        if(!isset($slackParams['bubble'])) $slackParams['bubble'] = self::BUBBLE;
        if(!isset($slackParams['useShortAttachment'])) $slackParams['useShortAttachment'] = self::USE_SHORT_ATTACHMENT;
        if(!isset($slackParams['includeContextAndExtra'])) $slackParams['includeContextAndExtra'] = self::INCLUDE_CONTEXT_AND_EXTRA;
        if(!isset($slackParams['saltKey'])) $slackParams['saltKey'] = self::SALT_KEY;
        if(!isset($slackParams['processors'])) $slackParams['processors'] = self::PROCESSORS;

        parent::__construct('ssl://slack.com:443', $slackParams['level'], $slackParams['bubble']);

        $this->slackParams = $slackParams;

        $this->slackRecord = new SlackRecord(
            $this->slackParams,
            $this->formatter
        );
    }

    /**
     * {@inheritdoc}
     *
     * @param  array  $record
     * @return string
     */
    protected function generateDataStream($record)
    {
        $content = $this->buildContent($record);

        return $this->buildHeader($content) . $content;
    }

    /**
     * Builds the body of API call
     *
     * @param  array  $record
     * @return string
     */
    private function buildContent($record)
    {
        $dataArray = $this->prepareContentData($record);

        return http_build_query($dataArray);
    }

    /**
     * Prepares content data
     *
     * @param  array $record
     * @return array
     */
    protected function prepareContentData($record)
    {
        $dataArray = $this->slackRecord->getSlackData($record);
        $dataArray['token'] = $this->slackParams['token'];

        if (!empty($dataArray['attachments'])) {
            $dataArray['attachments'] = Utils::jsonEncode($dataArray['attachments']);
        }

        return $dataArray;
    }

    /**
     * Builds the header of the API Call
     *
     * @param  string $content
     * @return string
     */
    private function buildHeader($content)
    {
        $header = "POST /api/chat.postMessage HTTP/1.1\r\n";
        $header .= "Host: slack.com\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($content) . "\r\n";
        $header .= "\r\n";

        return $header;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $record
     */
    protected function write(array $record)
    {
        parent::write($record);
        $this->finalizeWrite();
    }

    /**
     * Finalizes the request by reading some bytes and then closing the socket
     *
     * If we do not read some but close the socket too early, slack sometimes
     * drops the request entirely.
     */
    protected function finalizeWrite()
    {
        $res = $this->getResource();
        if (is_resource($res)) {
            @fread($res, 2048);
        }
        $this->closeSocket();
    }
}
