<?php
namespace Fakend\Presentation;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class FractalProvider
{
    protected $fractal;
    protected $serializer;
    public function __construct($serializer)
    {
        $this->fractal = new Manager();
        $this->serializer = $serializer;
        $this->fractal->setSerializer($this->serializer);
    }

    public function processCollection($data, $resourceKey)
    {
        $dataBySerializer = array();
        if(get_class($this->serializer) === 'League\Fractal\Serializer\JsonApiSerializer')
        {
            $dataBySerializer = $data[0];
        } else {
            $dataBySerializer = $data;
        }

        $this->resource = new Collection( $dataBySerializer, function(array $r) {
                return $r;
        }, $resourceKey);
        return $this;
    }
    public function processItem($data, $resourceKey)
    {
        $this->resource = new Item($data, function(array $r) {
            return $r[0];
        }, $resourceKey);
        return $this;
    }
    public function setMeta(array $meta){
        if($this->resource){
            $this->resource->setMeta($meta);
        }
        return $this;
    }
    public function toJson(){
        return $this->fractal->createData($this->resource)->toJson();
    }
}