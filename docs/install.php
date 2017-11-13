<?php

initFakend(is_array($args) ? $args : array());

function initFakend($argv)
{

    exec('composer require brewinteractive/fakend:dev-update', $composerExecOut, $composerExecReturn); 
    if ($composerExecReturn === 0){
        echo "Composer packages initiated...  \n";
        if(!is_dir('api')){
            exec('mkdir api', $apiDirOut, $apiDirReturn);
        }
        if(!is_dir('api/Schemas')){
            exec('mkdir api/Schemas', $schemasDirOut, $schemasDirReturn);
        }
        if(!is_dir('api/Models')){
            exec('mkdir api/Models', $modelDirOut, $modelDirReturn);
        }
        exec('ln -s vendor/brewinteractive/fakend/Bin/parser.php parser');
        exec('cp vendor/brewinteractive/fakend/samples/index.php api/index.php', $copyIndexOut, $copyIndexReturn);
        $loadclasses = fopen("api/Schemas/loadclasses.php", "w");
        fwrite($loadclasses, "<?php\n");
        fclose($loadclasses);
        if($copyIndexReturn === 0 && $apiDirReturn === 0 && $schemasDirReturn === 0){
            echo "Files copied... \n";
        }
        exit(1);    
    }else{  
        echo "Composer can not be initiated. Please check Composer.";
        exit(1);    
    }
    

}




