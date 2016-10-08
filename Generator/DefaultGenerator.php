<?php
namespace Fakend\Generator;
use Faker;
use Stringy\Stringy as S;

class DefaultGenerator {
    protected $faker;

    function __construct(){
        $this->faker = Faker\Factory::create();
    }
    public function id(){
        return $this->faker->numberBetween(1, $max = 999);
    }
    public function string($options){
        $length =(is_object($options) && isset($options->length)) ? $options->length : 10;
        if($length == 1){
            return $this->faker->word();
        }else{
            return $this->faker->sentence($length);
        }
    }
    public function title($options = null){
         $nullable = (is_object($options) && isset($options->nullable)) ? $this->faker->boolean(100) : false;
        if($nullable){
            return '';
        }
        $sentence = $this->faker->sentence($this->faker->numberBetween(2,5));
        return substr($sentence, 0, strlen($sentence) - 1);
    }
    public function description($options = null){
        $length = (is_object($options) && isset($options->length) &&  $options->length > 0) ? $options->length : 20;
        $hasHtmlOutput = (is_object($options) && isset($options->html) && $options->html) ? true : false;
        if($hasHtmlOutput){
            return $this->html($length);
        }else{
            return $this->faker->sentence($length);
        }

    }
    public function numberBetween($options){
        $max = (is_object($options) && isset($options->max)) ? $options->max : 3;
        $min = (is_object($options) && isset($options->min))  ? $options->min : 1;
        return $this->faker->numberBetween($max,$min);
    }
    public function date($options){
        if(!empty($options->to) || !empty($options->from)){
            $now = 'now';
            $from = !empty($options->from) ? $options->from :  'now' ;
            $to = !empty($options->to) ? $options->to : 'now';
            return $this->faker->dateTimeBetween($from,$to)->format(DATE_ATOM);
        }else{
            return $this->faker->dateTime()->format(DATE_ATOM);
        }
    }
    public function boolean($chanceOfGettingTrue = 50){
        $chanceOfGettingTrue = ($chanceOfGettingTrue) ? $chanceOfGettingTrue : 50;
        return $this->faker->boolean($chanceOfGettingTrue);
    }
    public function random($options){

        $values = (is_array($options)) ? $options['values']: $options->values;
        $returnArray = (is_object($options) && isset($options->array) &&  $options->array) ? true : false;
        if($returnArray){
            return $this->faker->randomElements($values,$this->faker->numberBetween(1,1));
        }else{
            return $this->faker->randomElement($values);
        }
    }
    public function url(){
        return $this->faker->url();
    }
    public function json($jsonObject){
        $return = array();
        foreach($jsonObject as $key => $parameters){
            $objectParameter = (array) $parameters->parameters;
            $return[$key] = $this->{$parameters->type}($objectParameter);
        }
        return $return;
    }
    public function imageUrl($options){
        $url = 'http://lorempicsum.com';
        $type = $this->faker->randomElement(array('up','futurama','nemo','rio','simpsons'));
        $index = $this->faker->numberBetween(1,6);
        $hasImage = (is_object($options) && isset($options->required)) ? $this->boolean(50) : true;
        if($hasImage){
            if(is_object($options) && isset($options->type) && $options->type == 'avatar'){
                $width = 100;
                $heigth = 100;
                 $type = 'nemo';
            }else{
                $width = (!empty($options) && is_array($options) && array_key_exists('width', $options)) ? $options['width'] : 300;
                $heigth = (!empty($options) && is_array($options) && array_key_exists('heigth', $options)) ? $options['heigth'] : 300;
            }
            return $url.'/'.$type.'/'.$width.'/'.$heigth.'/'.$index;
        }else{
            return '';
        }
    }
    public function mimeType($options){
        return $this->faker->mimeType();
    }
    public function firstname(){
        $firstname = $this->faker->firstName();
        $middleName = $this->faker->firstName();
        return ($this->boolean(50) ? $firstname.' '.$middleName : $firstname);
    }
    public function lastName(){
        return $this->faker->lastName();
    }
    public function html($length = 20){
        $hasTable = $this->boolean(50);
        $hasParagraph = $this->boolean(80);
        $hasList = $this->boolean(70);
        $hasImage = $this->boolean(60);
        $html = '';
        if($hasTable){
            $html .= '<table>';
            for($i=0;$i<3;$i++){
                $html .= '<tr><td>'.$this->faker->word().'</td><td>'.$this->faker->word().'</td></tr>';
            }
            $html .= '</table>';
        }
        if($hasParagraph){
            for($i=0;$i<5;$i++){
                $html .= '<p>'.$this->faker->sentence(100).'</p><br />';
            }
        }
        if($hasList){
            $html .= '<ul><li>'.$this->faker->sentence(4).'</li><li>'.$this->faker->sentence(3).'</li><li>'.$this->faker->sentence(4).'</li></ul>';
        }
        if($hasImage){
            $html .= '<img src='.$this->imageUrl(array('width' =>  $this->faker->numberBetween(100,1000), 'heigth' => $this->faker->numberBetween(100,1000))).'>';
        }
        return $html;
    }
}