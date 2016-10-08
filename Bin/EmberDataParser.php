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
            if(array_key_exists('attr',$model))
            {
                foreach ($model['attr'] as  $attr)
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
            file_put_contents('../Schemas/'.$className.'.php', $generatedCode);
    }
    protected function parseModelSchema($modelFile)
    {
        $filePath = $this->path.$modelFile;
        $result = array();
            if (($handle = fopen($filePath, "r")) !== FALSE)
            {
                while (($data = fgetcsv($handle, 1000, "\n")) !== FALSE) {
                    if(array_key_exists(0,$data))
                    {
                        $line = explode(',',trim($data[0]));
                        $commentSettings = explode('/*',trim($data[0]));
                        $settings = json_decode(trim(str_replace('*/','',$commentSettings[1])));
                        $field = explode(':',$line[0]);
                        $fieldName = $field[0];
                        $attribute = explode('.',$field[1]);
                        $attribute = explode('(',$attribute[1]);
                        $attributeName = $attribute[0];
                        $attributeType = str_replace(array(')','\''),'',$attribute[1]);
                        if(!empty($settings->type))
                        {
                            $attributeType = $settings->type;
                        } else {
                            $attributeType = str_replace(array(')','\''),'',$attribute[1]);
                        }
                        $result[$attributeName][] = array('fieldName' =>  $fieldName, 'attributeType' => $attributeType, 'parameters' => json_encode($settings->parameters));
                    }
                }
                fclose($handle);
            }
        return $result;
        }
    }