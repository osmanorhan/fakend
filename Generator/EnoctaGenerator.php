<?php
namespace Fakend\Generator;
use Faker;
use Stringy\Stringy as S;

class EnoctaGenerator extends DefaultGenerator {
    public function className($options){
        $sentence = $this->faker->sentence(2);
        return (string) S::create(substr($sentence, 0, strlen($sentence) - 1))->toTitleCase();
    }
    public function weightDistribution($options){
        $weightDistribution = array();
        $weights = $this->weights();
        for($i =1;$i<=count($weights);$i++){
            $weight = array();
            $weight['weightID'] = $i;
            $weight['weight'] = $weights[$i-1];
            ($this->faker->boolean(70)) ? $weight['lastScore'] = $this->faker->numberBetween(1,100) : '';
            $weightDistribution[] =  $weight;
        }
        return $weightDistribution;
    }
    public function weights(){
       return $this->faker->randomElement(array(array(15,15,70), array(25,25,50), array(10,60,30),array(30,50,20)));
    }
    public function courseName($options){
        $sentence = $this->faker->sentence(2);
        return (string) S::create(substr($sentence, 0, strlen($sentence) - 1))->toTitleCase();
    }
    public function courseCode(){
        return (string) S::create($this->faker->bothify('???###'))->toUpperCase();
    }
    public function semester(){
        return $this->faker->randomElement(array('12-13','13-14','14-15','15-16')).' '.(string) S::create($this->faker->word)->toTitleCase();
    }
    public function sessionAttendanceDuration(){
        $duration = array();
        $duration['semester'] = $this->semester();
        $duration['durations'][] = array('type' => $this->faker->word, 'duration' => $this->faker->numberBetween(10,1000));
        $duration['durations'][] = array('type' => $this->faker->word, 'duration' => $this->faker->numberBetween(10,1000));
        return $duration;
    }
    public function sessionAttendanceRatio(){
        $ratio = array();
        if($this->faker->boolean(50)){
            $ratio['semester'] = $this->semester();
            $ratio['generalRatio'] = $this->faker->numberBetween(10,99); // %
            $ratio['userRatio'] = $this->faker->numberBetween(20,100); // %
        }
        return $ratio;
    }
    public function averageGrade(){
        $grades = array();
        for($i=1;$i<=$this->faker->numberBetween(1,1);$i++){
            $grades[] = array('semester' => $this->semester(), 'grade' => $this->faker->randomFloat(2, 1, 4));
        }
        return $grades;
    }
    public function icon($options){
        $icon = array('iconClass' => '','iconUrl' => '', 'title' => $this->title());
        $forceToClass = 50;
        $iconClasses = array('sa-settings', 'sa-binoculars');
        if(is_object($options) && isset($options->type)){
            switch ($options->type) {
                case 'course':
                    $iconClasses = array('sa-settings', 'sa-binoculars');
                    $forceToClass = 100;
                break;
                case 'notification':
                    $iconClasses = array('ss-alert','ss-info');
                    $forceToClass = 100;
                break;
                case 'content':
                    $iconClasses = array('sg-file','sg-info');
                    $forceToClass = 100;
                break;
            }
        }
        if($this->boolean($forceToClass)){
            $icon['iconClass'] = $this->faker->randomElement($iconClasses);
        }else{
            $options = array('width' => 50, 'heigth' => 50);
            $icon['iconUrl'] = $this->imageUrl($options);
        }
        return $icon;
    }
    public function currentUserPrivacyStatus($options){
        $status = array('privacyReasonText' => $this->description(),'isPrivate' => $this->boolean());
        return $status;
    }
    public function fileObject($options){
        $file = array('downloadUrl' => $this->faker->url(), 'fileName' => $this->faker->word(), 'mimeType' => $this->faker->mimeType());
        return $file;
    }
    public function educationHistory($options){
        $history[] = array('level' => 'Lisans', 'description' => $this->faker->sentence(10));
        $history[] = array('level' => 'Yüksek Lisans', 'description' => $this->faker->sentence(12));
        if($this->boolean(50)){
            $history[] = array('level' => 'Doktora', 'description' => $this->faker->sentence(7));
        }
        return $history;
    }
    public function recipientUsers($options){
        $recipents = array();
        $limit = $this->faker->numberBetween(1,100);
        for($i=0; $i<=$limit; $i++ ){
            $type = $this->faker->randomElement(array('staff','user'));
            $name = $this->firstName().' '. $this->lastName();
            $recipents[] = array('id' => $this->id(), 'name' => $name, 'type' => $type);
        }
        return $recipents;
    }
    public function mainboxName(){
        $names = ['inbox','sent','draft','trash'];
        return $this->faker->randomElement($names);
    }
}