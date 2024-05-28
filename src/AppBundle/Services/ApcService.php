<?php

namespace AppBundle\Services;

/**
 * Class ApcService
 * @package AppBundle\Services
 */
class ApcService
{
    /**
     * @param $key
     * @param $value
     * @return array|bool
     */
    public function apc_store($key, $value)
    {
        return apc_store(sha1(__FILE__) . '_' . $key, $value);
    }

    /**
     * @param $key
     * @return bool|\string[]
     */
    public function apc_delete($key)
    {
        return apc_delete(sha1(__FILE__) . '_' . $key);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function apc_fetch($key)
    {
        return apc_fetch(sha1(__FILE__) . '_' . $key);
    }
}