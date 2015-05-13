<?php

namespace Tracks\Storage;


use Tracks\TrackInterface;

interface StorageInterface {

    public function send($key = 'tracks', TrackInterface $object);
}