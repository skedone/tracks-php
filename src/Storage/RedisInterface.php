<?php

namespace Tracks\Storage;


use Tracks\TrackInterface;

class RedisStorage implements StorageInterface {

    /** @var \Redis  */
    private $client;

    public function __construct($host)
    {
        $this->client = new \Redis();
        $this->client->connect($host);
    }

    public function send($key = 'tracks', TrackInterface $object)
    {
        $this->client->rpush($key, \json_encode($object));
    }
}