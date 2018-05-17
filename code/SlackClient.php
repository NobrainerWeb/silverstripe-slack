<?php

namespace NobrainerWeb\Slack;

use Maknz\Slack\Client;

class SlackClient extends \Object
{
    /**
     * Allowed channels and their specific settings such as webhooks
     *
     * Example:
     *
     * channels:
     *   {channelname}:
     *     username: "My bot name"
     *     webhook: "https://hooks.slack.com/...."
     *
     *
     * @config string
     */
    private static $channels;

    /**
     * @object Client
     */
    protected $client;

    /**
     * Webhook (endpoint) for the current channel
     *
     * @var
     */
    protected $endpoint;

    /**
     * @var
     */
    protected $channel;

    public function __construct($channel)
    {
        $this->setChannel($channel);

        $endpoint = $this->getChannelEndpoint();

        $this->client = new Client($endpoint);
    }

    /**
     * @return string
     */
    public function getChannelEndpoint()
    {
        $endpoint = $this->endpoint ? $this->endpoint : $this->getChannelSetting("webhook");

        return $endpoint;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->validateChannel($channel);

        $this->channel = $channel;

        return $this;
    }

    /**
     * @param $channel
     * @throws \Exception
     */
    protected function validateChannel($channel)
    {
        if (!isset(self::config()->get("channels")[$channel])) {
            throw new \Exception("This channel is not allowed!");
        }
    }

    /**
     * @param $setting
     * @return mixed
     */
    public function getChannelSetting($setting)
    {
        return self::config()->get("channels")[$this->getChannel()][$setting];
    }

    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param $payload
     * @return mixed
     */
    public function notifyChannel($payload)
    {
        $from = $this->getChannelSetting("username");
        $to = $this->getSlackChannelName();

        return $this->getClient()->from($from)->to($to)->send($payload);
    }

    /**
     * @return string
     */
    public function getSlackChannelName()
    {
        $name = '#' . $this->getChannel();

        $this->extend("updateSlackChannelName", $name);

        return $name;
    }
}