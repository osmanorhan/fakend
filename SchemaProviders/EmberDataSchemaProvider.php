<?php
namespace Fakend\SchemaProviders;
use Fakend;
use Fakend\FakendFactory;
use Fakend\Generator\DefaultGenerator;
use Fakend\Presentation\FractalProvider;

class EmberDataSchemaProvider {

    protected $generator;
    protected $presentation;
    protected $meta = array();
    protected $belongsTo = array();
    public $parent;
    public $id;
    public $count = 0;

    public function __construct()
    {
        $this->generator = new DefaultGenerator;
    }
    public function setSerializer($serializer){
        $this->presentation = new FractalProvider($serializer);
    }
    public function get($id = null, $include = true, $fromMany = false)
    {
        $return = array();
        $this->id = ($id) ? $id  : $this->generator->id();
        $return['id'] = $this->id;
        $this->count++;
        foreach ($this->getAttributes() as $attrName => $attr)
        {
            $parameters = json_decode($attr['parameters']);
            $return[$attrName] = $this->generator->{$attr['type']}($parameters);
        }
        if($include)
        {
            foreach ($this->getHasManyRelations() as $attrName => $attr)
            {
                ${$attr['type']} = FakendFactory::create($attr['type']);
                ${$attr['type']}->setParentObject($this);
                 $parameters = json_decode($attr['parameters']);
                 if(isset($parameters->belongsTo))
                 {
                    ${$attr['type']}->setBelongsTo((array)$parameters->belongsTo);
                 }
                 if((isset($parameters->required) && $parameters->required) || $this->generator->boolean(80))
                 {
                    $include = (get_class($this->getParentObject()) !== get_class(${$attr['type']})) ? true : false;
                    if( $include  || get_class($this->getParentObject()) === 'Fakend\SchemaProviders\EmberDataSchemaProvider')
                    {
                        $length = (isset($parameters->length)) ? $parameters->length : rand(1, 3);
                        $return[$attrName] = ${$attr['type']}->getMany($length,$include,false);
                    }
                 }
            }
            foreach ($this->getBelongsToRelations() as $attrName => $attr)
            {
                ${$attr['type']} = FakendFactory::create($attr['type']);
                ${$attr['type']}->setParentObject($this);
                $parameters = json_decode($attr['parameters']);
                if(isset($this->belongsTo) && array_key_exists($attrName,$this->belongsTo))
                {
                    $return[$attrName] = ($this->belongsTo[$attrName] === "parent") ? ${$attr['type']}->getParentObject()->id : $this->belongsTo[$attrName];
                } else {
                    if((isset($parameters->required) && $parameters->required) || $this->generator->boolean(80))
                    {
                        $include = (get_class(${$attr['type']}) !== get_class($this->getParentObject())) ? true :false;
                        if($include || get_class($this->getParentObject()) === 'Fakend\SchemaProviders\EmberDataSchemaProvider')
                        {
                            $return[$attrName] = ${$attr['type']}->get(null,$include,true);
                        }
                    }
                }
            }
        }
        if($fromMany)
        {
            return $return;
        } else {
            $className = explode('\\', get_class($this));
            return $this->presentation->processItem(array($return), $className[count($className)-1])->toJson();
        }
    }

    public function getMany($limit = 3, $include = true, $returnJson = true)
    {
        $return = array();
        for($i=0; $i<$limit; $i++)
        {
            $return[] = $this->get(null, $include, true);
        }
        if($returnJson)
        {
            $className = explode('\\', get_class($this));
            return $this->presentation->processCollection(array($return), $className[count($className)-1])->setMeta($this->meta)->toJson();
        } else {
            return $return;
        }
    }
    public function setMeta(array $meta)
    {
        $this->meta = $meta;
        return $this;
    }
    public function setBelongsTo(array $belongsTo)
    {
        $this->belongsTo = $belongsTo;
        return $this;
    }
    public function setBelongsToByName($name, $belongsTo)
    {
        $this->belongsTo[$name] = $belongsTo;
        return $this;
    }
    public function setParentObject($parent)
    {
        $this->parent = $parent;
        return $this;
    }
    public function getParentObject()
    {
        return $this->parent;
    }
    public function post()
    {
        /* Not Implemented */
    }
    public function put()
    {
        /* Not Implemented */
    }
    public function delete()
    {
        /* Not Implemented */
    }



}