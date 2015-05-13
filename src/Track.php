<?php

namespace Tracks;


use Rhumsaa\Uuid\Uuid;
use Tracks\Storage\StorageInterface;

class Track implements TrackInterface {

    public $id;

    public $ts;

    public $te;

    public $ms;

    private $msTs;

    private $msTe;

    public $bucket;

    public $host;

    public $memory;

    public $tags = [];

    public $payload = [];

    public $pid = null;

    /** @var StorageInterface */
    private static $client;

    /** @var string key */
    private static $key = 'tracks';

    /**
     * @param string $bucket
     * @param array $payload
     * @param null|string $pid
     */
    public function __construct($bucket, $payload = [], $unique_pid = NULL)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->bucket = $bucket;
        $this->host = (string) (\gethostname() . '_' . \getmypid());

        if($unique_pid) {
            $this->pid = $unique_pid;
        }
    }

    /**
     * @param StorageInterface $client
     */
    public static function setClient(StorageInterface $client)
    {
        self::$client = $client;
    }

    /**
     * @param $bucket
     * @param array $payload
     * @param null $pid
     * @return Track
     */
    public static function create($bucket, $payload = [], $pid = NULL) {
        return new Track($bucket, $payload, $pid);
    }

    /**
     * @return $this
     */
    public function start()
    {
        list($this->ts, $this->msTs) = $this->getTime();
        return $this;
    }

    /**
     * @return $this
     */
    public function stop()
    {
        list($this->te, $this->msTe) = $this->getTime();
        $this->memory = \memory_get_usage(true);
        $this->ms = intval(($this->msTe - $this->msTs) * 1000000);
        return $this;
    }

    public function addPayload($payload)
    {
        $this->payload[] = $payload;
        return $this;
    }

    private function getTime()
    {
        $d = new \DateTime();
        return [(string)  $d->format('Y-m-d\TH:i:s.u\0\0O'), microtime(true)];
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTags(array $tags = [])
    {
        $this->tags = $tags;
    }

    public function addTag($string = '')
    {
        $this->tags[] = $string;
    }

    public function send()
    {
        try {
            self::$client->send(self::$key, $this);
        } catch(\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }

        return TRUE;
    }

}