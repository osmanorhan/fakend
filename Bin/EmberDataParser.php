<?php
namespace Fakend\Bin;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Memio\Memio\Config\Build;
use Memio\Model\File;
use Memio\Model\Object;
use Memio\Model\Property;
use Memio\Model\Method;
use Memio\Model\Argument;
use Memio\Model\Contract;
use Memio\Model\FullyQualifiedName;
use Stringy\Stringy as S;

class EmberDataParser extends Command
{
    protected $path = '';
    protected function configure()
    {
        $this
            ->setName('generate')
            ->setDescription('Generate Ember Data')
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Ember models path'
            )
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Ember model name'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->path = $input->getArgument('path');
        if($input->getArgument('name'))
        {
             $this->createClass($input->getArgument('name'));
        } else {
            if ($handle = opendir($input->getArgument('path')))
            {
                while (false !== ($entry = readdir($handle))) {
                    $this->createClass($entry);
                }
                closedir($handle);
            }
        }
    $output->writeln('done');
    }
    protected function createClass($file)
    {
        $model = array();
        $attrBody = $hasManyRelations = $belongsToRelations = '';
        $model = $this->parseModelSchema($file);
        $fileName = explode('.',$file);
        $className = S::create($fileName[0])->upperCamelize();
        $attrBody = 'return [';
            if(array_key_exists('attrs',$model))
            {
                foreach ($model['attrs'] as  $attr)
                {
                    $attrBody .= "'".$attr['fieldName']."'"."=>"." array('type' => '".$attr['attributeType']."', 'parameters' => '".$attr['parameters']."'),";
                }
                $attrBody = (substr($attrBody,-1) == ',') ? substr($attrBody, 0, -1) : $attrBody;
            }
            $attrBody .= '];';
            $hasManyRelations .= 'return [';
            if(array_key_exists('hasMany',$model))
            {
                foreach ($model['hasMany'] as  $attr)
                {
                    $hasManyRelations .= "'".$attr['fieldName']."'"."=>"." array('type' => '".$attr['attributeType']."', 'parameters' => '".$attr['parameters']."'),";
                }
                $hasManyRelations = (substr($hasManyRelations,-1) == ',') ? substr($hasManyRelations, 0, -1) : $hasManyRelations;
            }
            $hasManyRelations .= '];';
            $belongsToRelations .= 'return [';
            if(array_key_exists('belongsTo',$model))
            {
                foreach ($model['belongsTo'] as  $attr)
                {
                    $belongsToRelations .= "'".$attr['fieldName']."'"."=>"." array('type' => '".$attr['attributeType']."', 'parameters' => '".$attr['parameters']."'),";
                }
                $belongsToRelations = (substr($belongsToRelations,-1) == ',') ? substr($belongsToRelations, 0, -1) : $belongsToRelations;
            }
            $belongsToRelations .= '];';
            $file = File::make('src/Fakend/Schemas/'.$className.'.php')
                        ->addFullyQualifiedName(FullyQualifiedName::make('Fakend\SchemaProviders\EmberDataSchemaProvider'))
                        ->setStructure(
                            Object::make('Fakend\Schemas\\'.$className)
                                ->extend(new Object('Fakend\SchemaProviders\EmberDataSchemaProvider'))
                                ->addProperty(new Property('data'))
                                ->addMethod(Method::make('getAttributes')->setBody($attrBody))
                                ->addMethod(Method::make('getHasManyRelations')->setBody($hasManyRelations))
                                ->addMethod(Method::make('getBelongsToRelations')->setBody($belongsToRelations))
                    );
            $prettyPrinter = Build::prettyPrinter();
            $generatedCode = $prettyPrinter->generateCode($file);
            file_put_contents('api/Schemas/'.$className.'.php', $generatedCode);
            $loadclass = fopen("api/Schemas/loadclasses.php", "a");
            fwrite($loadclass, "require_once __DIR__ . '/".$className.".php';\n");
            fclose($loadclass);
            $this->addMethods($className);  
    }
    protected function parseModelSchema($modelFile)
    {
        $filePath = $this->path.$modelFile;
        $result = array();
        $model = file_get_contents($this->path.$modelFile);
        $json = json_decode($model, true);
        return $json;
    }
    protected function addMethods($className){
    $methods = <<<'EOT'
    
    $app->match('/%classname%', function(Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $class = FakendFactory::create('%classname%');
    $class->setSerializer(new JsonApiSerializer());
    $return = $class->setMeta(array('totalCount' => rand(10,100)))->getMany(rand(5,10));
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
    })->method('GET|OPTIONS');
    $app->match('/%classname%/{id}', function($id, Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $class = FakendFactory::create('%classname%');
    $class->setSerializer(new JsonApiSerializer());
    $return = $class->get($id);
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
    })->method('GET|OPTIONS');
    $app->match('/%classname%/{id}', function($id, Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $return = json_encode(array());
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
    })->method('DELETE|OPTIONS');
    $app->match('/%classname%', function(Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $class = FakendFactory::create('%classname%');
    $class->setSerializer(new JsonApiSerializer());
    $return = $class->get(rand(1,10000));
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
    })->method('POST|OPTIONS');
    $app->match('/%classname%/{id}', function($id, Request $request) use ($app) {
    if($request->getMethod() == 'OPTIONS') {
        return new Response('', 200);
    }
    $class = FakendFactory::create('%classname%');
    $class->setSerializer(new JsonApiSerializer());
    $return = $class->get($id);
    return new Response($return, 200, array(
        'Content-Type' => 'application/json',
    ));
    })->method('POST|PUT|OPTIONS');
    $app->run();
EOT;
    ob_start();
    passthru("wc -l < api/index.php | awk '{s=$1-1} END {print s}' | xargs -I s head -n s api/index.php");
    $index = trim(ob_get_clean());
    $indexFile = fopen("api/index.php", "w");
    fwrite($indexFile, $index.str_replace("%classname%", $className, $methods) );
    fclose($indexFile);
    }
}