<?php

namespace PanGuKTD\LaravelDoctrineORM\Providers;

use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;

class DoctrineORMProvider extends ServiceProvider
{

    protected $defer = true;
    protected $config;

    function boot()
    {
        $publishes = [__DIR__ . '/config/doctrine.php' => config_path('doctrine.php')];
        $this->publishes($publishes, 'config');
    }

    function register()
    {
        $this->app->singleton('doctrine_orm', function($app) {

            $mode = $app['config']['app']['debug'];
            $path = [app_path('Entity') . '/'];

            $cache = $this->getCache();
            $evm = $this->getEvent();
            $params = $this->getConn();

            $config = Setup::createAnnotationMetadataConfiguration($path, $mode, null, $cache);
            $config->getSecondLevelCacheConfiguration();

            return EntityManager::create($params, $config, $evm);
        });
    }

    protected function getConn()
    {
        $this->config = config('database')['connections']['mysql'];
        $params = array(
            'driver' => 'pdo_mysql',
            'host' => $this->config['host'],
            'user' => $this->config['username'],
            'password' => $this->config['password'],
            'dbname' => $this->config['database'],
            'charset' => $this->config['charset'],
            'prefix' => $this->config['prefix'],
        );
        return $params;
    }

    /**
     * 
     * @return \Doctrine\Common\EventManager
     */
    protected function getEvent()
    {
        $options = config('database')['connections']['mysql'];
        $evm = new EventManager();
        $tablePrefix = new \PanGuKTD\LaravelDoctrineORM\Listeners\TablePrefixListener($options['prefix']);
        $evm->addEventListener(\Doctrine\ORM\Events::loadClassMetadata, $tablePrefix);
        return $evm;
    }

    /**
     * 
     * 获取缓存信息
     * @return \Doctrine\Common\Cache\MongoDBCache
     */
    protected function getCache()
    {
        $namespace = 'PanGuKTD_ORM_';
        $cacheConfig = config('doctrine');
        $cacheName = $cacheConfig['name'];
        $cache = null;

        if ($cacheName == 'array') {
            $cache = new \Doctrine\Common\Cache\ArrayCache();
        } elseif ($cacheName == 'xcache') {
            $cache = new \Doctrine\Common\Cache\XcacheCache();
        } elseif ($cacheName == 'memcached') {
            $memcached = new \Memcached();
            $memcached->addServers($cacheConfig['memcached']);
            $cache = new \Doctrine\Common\Cache\MemcachedCache();
            $cache->setMemcached($memcached);
            $cache->setNamespace($namespace);
        } elseif ($cacheName == 'memcache') {
            $memcache = new \Memcache();
            foreach ($cacheConfig['memcache'] as $key => $value) {
                $memcache->addServer($value['host'], $value['port'], $value['persistent'], $value['weight']);
            }
            $cache = new \Doctrine\Common\Cache\MemcacheCache();
            $cache->setMemcache($memcache);
            $cache->setNamespace($namespace);
        } elseif ($cacheName == 'apc') {
            $cache = new \Doctrine\Common\Cache\ApcCache();
            $cache->setNamespace($namespace);
        } elseif ($cacheName == 'mongo') {
            $host   = $cacheConfig['mongo']['host'];
            $port   = $cacheConfig['mongo']['port'];
            $opt    = $cacheConfig['mongo']['options'];
            $mongo = new \MongoClient("mongodb://{$host}:$port", $opt);
            $mongo = new \MongoDB($mongo, 'doctrine_orm_cache');
            $conn = new \MongoCollection($mongo, $namespace);
            $cache = new \Doctrine\Common\Cache\MongoDBCache($conn);
            $cache->setNamespace($namespace);
        } elseif ($cacheName == 'redis') {
            $host   = $cacheConfig['redis']['host'];
            $port   = $cacheConfig['redis']['port'];
            $redis  = new \Redis();
            $redis->connect($host, $port);
            $cache = new \Doctrine\Common\Cache\RedisCache();
            $cache->setRedis($redis);
            
        }

        return $cache;
    }

    public function provides()
    {
        return ['doctrine_orm'];
    }

}
