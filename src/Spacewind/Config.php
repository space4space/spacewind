<?php

namespace Spacewind;

/**
 * Класс для управления конфигурационными файлами.
 */
class Config
{
    private $provider;
    private $cached;
    private $memcached;

    public function __construct($provider, $cached = true)
    {
        $this->provider = $provider;
        $this->cached = $cached;
        if (!class_exists('\Memcached', false)) {
            $this->cached = false;
        }
        if ($this->cached) {
            $this->memcached = new \Memcached();
            $this->memcached->addServer('127.0.0.1', 11211);
        }
    }

    public function __destruct()
    {
    }

    public function __get($id)
    {
        // print_r($id);
        if ($this->cached) {
            $item = $this->memcached->get($this->provider->folder.'/'.$id);
            if ($item) {
                return unserialize($item);
            } else {
                $source = json_decode(json_encode($this->provider->findOne(['_id' => $id])));
                $this->memcached->set($this->provider->folder.'/'.$id, serialize($source));

                return $source;
            }
        } else {
            return json_decode(json_encode($this->provider->findOne(['_id' => $id])));
        }
    }

    public function __set($id, $value)
    {
        if ($this->cached) {
            $this->memcached->delete($this->provider->folder.'/'.$id);
        }

        return $this->provider->save($id, $value);
    }
}
