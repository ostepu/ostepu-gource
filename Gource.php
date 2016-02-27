<?php
#region Gource
class Gource
{
    private static $initialized=false;
    public static $name = 'gource';
    public static $installed = false;
    public static $page = 8;
    public static $rank = 100;
    public static $enabledShow = true;
    private static $langTemplate='Gource';

    public static $onEvents = array(
                                    'createGourceData'=>array(
                                        'name'=>'createGourceData',
                                        'event'=>array('actionCreateGourceData'),
                                        'procedure'=>'installCreateGourceData',
                                        'enabledInstall'=>true
                                        ),
                                    'listGourceData'=>array(
                                        'name'=>'listGourceData',
                                        'event'=>array('actionListGourceData'),
                                        'procedure'=>'installListGourceData',
                                        'enabledInstall'=>true
                                        ),
                                    'executeGource'=>array(
                                        'name'=>'executeGource',
                                        'event'=>array('actionExecuteGource'),
                                        'procedure'=>'installExecuteGource',
                                        'enabledInstall'=>true
                                        ),
                                    'listGourceResult'=>array(
                                        'name'=>'listGourceResult',
                                        'event'=>array('actionListGourceResult'),
                                        'procedure'=>'installListGourceResult',
                                        'enabledInstall'=>true
                                        ),
                                    'convertGource'=>array(
                                        'name'=>'convertGource',
                                        'event'=>array('actionConvertGource'),
                                        'procedure'=>'installConvertGource',
                                        'enabledInstall'=>true
                                        )
                                    );

    public static function getDefaults($data)
    {
        $res = array(
                     'path' => array('data[GOURCE][path]', '/var/www/gource'),
                     'selectedData' => array('data[GOURCE][selectedData]', NULL),
                     'selectedResult' => array('data[GOURCE][selectedResult]', NULL)
                     );
        $res['repos'] = array();
        $pluginFiles = PlugInsInstallieren::getPluginFiles($data);
        foreach($pluginFiles as $plug){
            $input = PlugInsInstallieren::gibPluginInhalt($data,$plug);
            if ($input !== null){
                $entries = array();
                PlugInsInstallieren::gibPluginEintraegeNachTyp($input, 'git', $entries);
                foreach ($entries as $git){
                    $path = $git['params']['path'];
                    $name=md5($path);
                    $res['repos'][$name] = array('data[GOURCE][REPO]['.$name.']', NULL);                  
                }
            }
        }
        return $res;
    }

    public static function checkExecutability($data)
    {
        Installation::log(array('text'=>Installation::Get('main','functionBegin')));
        $res = array(
                    ['name'=>'gource','exec'=>'gource --help','desc'=>'gource --help'],
                    ['name'=>'ffmpeg','exec'=>'ffmpeg -version','desc'=>'ffmpeg -version']);
        Installation::log(array('text'=>Installation::Get('main','functionEnd')));
        return $res;
    }

    public static function init($console, &$data, &$fail, &$errno, &$error)
    {
        Installation::log(array('text'=>Installation::Get('main','functionBegin')));
        Language::loadLanguageFile('de', self::$langTemplate, 'json', dirname(__FILE__).'/');
        Installation::log(array('text'=>Installation::Get('main','languageInstantiated')));

        $def = self::getDefaults($data);

        $text = '';
        $text .= Design::erstelleVersteckteEingabezeile($console, $data['GOURCE']['path'], 'data[GOURCE][path]', $def['path'][1], true);
        if (isset($data['GOURCE']['selectedData']) && !file_exists($data['GOURCE']['selectedData'])){
            $data['GOURCE']['selectedData'] = NULL;
        }
        $text .= Design::erstelleVersteckteEingabezeile($console, $data['GOURCE']['selectedData'], 'data[GOURCE][selectedData]', $def['selectedData'][1], true);
       
        if (isset($data['GOURCE']['selectedResult']) && !file_exists($data['GOURCE']['selectedResult'])){
            $data['GOURCE']['selectedResult'] = NULL;
        }
        $text .= Design::erstelleVersteckteEingabezeile($console, $data['GOURCE']['selectedResult'], 'data[GOURCE][selectedResult]', $def['selectedResult'][1], true);
        
        foreach($def['repos'] as $defName => $defVar){
            $text .= Design::erstelleVersteckteEingabezeile($console, $data['GOURCE']['REPO'][$defName], $defVar[0], $defVar[1], true);
        }
        
        echo $text;

        self::$initialized = true;
        Installation::log(array('text'=>Installation::Get('main','functionEnd')));
    }

    public static function show($console, $result, $data)
    {
        if (!Einstellungen::$accessAllowed) return;

        Installation::log(array('text'=>Installation::Get('main','functionBegin')));
        $text='';
        $text .= Design::erstelleBeschreibung($console,Installation::Get('main','description',self::$langTemplate));

        $text .= Design::erstelleZeile($console, Installation::Get('createGourceData','path',self::$langTemplate), 'e', Design::erstelleEingabezeile($console, $data['GOURCE']['path'], 'data[GOURCE][path]', $data['GOURCE']['path'], true), 'v');
        if (self::$onEvents['createGourceData']['enabledInstall']){
            $mainPath = $data['PL']['localPath'];
            $mainPath = str_replace(array("\\","/"), array(DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR), $mainPath);
            $pluginFiles = PlugInsInstallieren::getSelectedPluginFiles($data);
            $gitResults = array();
            foreach ($pluginFiles as $plug){
                $input = PlugInsInstallieren::gibPluginInhalt($data,$plug);
                if ($input !== null){
                    $entries = array();
                    PlugInsInstallieren::gibPluginEintraegeNachTyp($input, 'git', $entries);
                    $gitResults = array_merge($entries, $gitResults);
                }
            }
            
            function custom_sort2($a,$b) {
                $displayNameA = (isset($a['params']['name'])?$a['params']['name']:'');
                $displayNameB = (isset($b['params']['name'])?$b['params']['name']:'');
                return strcmp($displayNameA,$displayNameB);
            }
            usort($gitResults, "custom_sort2");
            foreach($gitResults as $git){
                $path = $git['params']['path'];
                $displayName = (isset($git['params']['name'])?$git['params']['name']:'???');
                $name=md5($path);
                $text .= Design::erstelleZeile($console, $displayName, 'e',  Design::erstelleAuswahl($console, $data['GOURCE']['REPO'][$name], 'data[GOURCE][REPO]['.$name.']', $name, null, true), 'h');
            }

            $text .= Design::erstelleZeile($console, Installation::Get('createGourceData','createDesc',self::$langTemplate), 'e',  Design::erstelleSubmitButton(self::$onEvents['createGourceData']['event'][0],Installation::Get('createGourceData','create',self::$langTemplate)), 'h');
        }

        $createBackup=false;
        if (isset($result[self::$onEvents['createGourceData']['name']])){
            $content = $result[self::$onEvents['createGourceData']['name']]['content'];
            if (!isset($content['outputFile'])) $content['outputFile'] = '???';
            if (!isset($content['outputSize'])) $content['outputSize'] = '???';

            $createBackup=true;
            if (!empty($content['output'])){
                $text .= Design::erstelleZeile($console, Installation::Get('createGourceData','databaseMessage',self::$langTemplate) , 'e', $content['databaseOutput'], 'v error_light break');
            }

            $text .= Design::erstelleZeile($console, Installation::Get('createGourceData','filePath',self::$langTemplate) , 'e', $content['outputFile'], 'v');
            $text .= Design::erstelleZeile($console, Installation::Get('createGourceData','fileSize',self::$langTemplate) , 'e', Design::formatBytes($content['outputSize']), 'v');
        }

        if (self::$onEvents['listGourceData']['enabledInstall']){
            $text .= Design::erstelleZeile($console, Installation::Get('listGourceData','listDesc',self::$langTemplate), 'e',  Design::erstelleSubmitButton(self::$onEvents['listGourceData']['event'][0],Installation::Get('listGourceData','list',self::$langTemplate)), 'h');
        }

        if (isset($result[self::$onEvents['listGourceData']['name']])){
            $content = $result[self::$onEvents['listGourceData']['name']]['content'];
            if (!isset($content['gourceData'])){
                $content['gourceData'] = array();
            }
            
            $content['gourceData'] = array_reverse($content['gourceData']);
            foreach($content['gourceData'] as $key => $file){
                if ($key == 0){
                    $text .= Design::erstelleZeile($console, '','','','' );
                }

                $text .= Design::erstelleZeile($console, Installation::Get('listGourceData','filePath',self::$langTemplate) , 'e', $file['file'], 'v');
                if (isset($file['size'])){
                    $text .= Design::erstelleZeile($console, Installation::Get('listGourceData','fileSize',self::$langTemplate) , 'e', Design::formatBytes($file['size']), 'v');
                }
                
                if (self::$onEvents['executeGource']['enabledInstall']){
                    $text .= Design::erstelleZeile($console, Installation::Get('listGourceData','select',self::$langTemplate), 'e',  Design::erstelleGruppenAuswahl($console, $data['GOURCE']['selectedData'], 'data[GOURCE][selectedData]', $file['file'], NULL, true), 'h');
                }
           
                if ($key != count($content['gourceData'])-1){
                    $text .= Design::erstelleZeile($console, '','','','' );
                }
            }

            if (empty($content['gourceData'])){
                $text .= Design::erstelleZeile($console, '','e',Installation::Get('listGourceData','noData',self::$langTemplate),'v_c' );
            } else {
                if (self::$onEvents['executeGource']['enabledInstall']){
                    $text .= Design::erstelleZeile($console, Installation::Get('executeGource','executeDesc',self::$langTemplate), 'e',  Design::erstelleSubmitButton(self::$onEvents['executeGource']['event'][0],Installation::Get('executeGource','execute',self::$langTemplate)), 'h');
                }
            }
        }
        
        if (self::$onEvents['listGourceResult']['enabledInstall']){
            $text .= Design::erstelleZeile($console, Installation::Get('listGourceResult','listDesc',self::$langTemplate), 'e',  Design::erstelleSubmitButton(self::$onEvents['listGourceResult']['event'][0],Installation::Get('listGourceResult','list',self::$langTemplate)), 'h');
        }

        if (isset($result[self::$onEvents['listGourceResult']['name']])){
            $content = $result[self::$onEvents['listGourceResult']['name']]['content'];
            if (!isset($content['gourceResult'])){
                $content['gourceResult'] = array();
            }
            
            $content['gourceResult'] = array_reverse($content['gourceResult']);
            foreach($content['gourceResult'] as $key => $file){
                if ($key == 0){
                    $text .= Design::erstelleZeile($console, '','','','' );
                }

                $text .= Design::erstelleZeile($console, Installation::Get('listGourceResult','filePath',self::$langTemplate) , 'e', $file['file'], 'v');
                if (isset($file['size'])){
                    $text .= Design::erstelleZeile($console, Installation::Get('listGourceResult','fileSize',self::$langTemplate) , 'e', Design::formatBytes($file['size']), 'v');
                }
                
                if (self::$onEvents['convertGource']['enabledInstall']){
                    $text .= Design::erstelleZeile($console, Installation::Get('listGourceResult','select',self::$langTemplate), 'e',  Design::erstelleGruppenAuswahl($console, $data['GOURCE']['selectedResult'], 'data[GOURCE][selectedResult]', $file['file'], NULL, true), 'h');
                }
           
                if ($key != count($content['gourceResult'])-1){
                    $text .= Design::erstelleZeile($console, '','','','' );
                }
            }

            if (empty($content['gourceResult'])){
                $text .= Design::erstelleZeile($console, '','e',Installation::Get('listGourceResult','noData',self::$langTemplate),'v_c' );
            } else {
                if (self::$onEvents['convertGource']['enabledInstall']){
                    $text .= Design::erstelleZeile($console, Installation::Get('convertGource','executeDesc',self::$langTemplate), 'e',  Design::erstelleSubmitButton(self::$onEvents['convertGource']['event'][0],Installation::Get('convertGource','execute',self::$langTemplate)), 'h');
                }
            }
        }

        echo Design::erstelleBlock($console, Installation::Get('main','title',self::$langTemplate), $text);

        Installation::log(array('text'=>Installation::Get('main','functionEnd')));
        return null;
    }
    
    private static function filter($mode, $name){
        return true;
    }

    public static function installCreateGourceData($data, &$fail, &$errno, &$error)
    {
        Installation::log(array('text'=>Installation::Get('main','functionBegin')));
        
        $res = array();
        $location = $data['GOURCE']['path'];
        Einstellungen::generatepath($location);
        
        $mainPath = $data['PL']['localPath'];
        $mainPath = str_replace(array("\\","/"), array(DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR), $mainPath);
        $pluginFiles = PlugInsInstallieren::getSelectedPluginFiles($data);
        $gitResults = array();
        foreach ($pluginFiles as $plug){
            $input = PlugInsInstallieren::gibPluginInhalt($data,$plug);
            if ($input !== null){
                $entries = array();
                PlugInsInstallieren::gibPluginEintraegeNachTyp($input, 'git', $entries);
                $gitResults = array_merge($entries, $gitResults);
            }
        }
        
        $repositories = array();
        foreach($gitResults as $git){
            $path = $git['params']['path'];
            $displayName = $git['params']['name'];
            $name=md5($path);
            if (isset($data['GOURCE']['REPO'][$name]) && $data['GOURCE']['REPO'][$name] === $name){
                // dieses Repository soll einbezogen werden
                $repositories[$displayName] = $mainPath. DIRECTORY_SEPARATOR .$path;
            }
        }
        
        // lade die authorMap
        $authorMap = json_decode(file_get_contents(dirname(__FILE__). DIRECTORY_SEPARATOR . 'authorMap.json'),true);
        
        function get_filename($name){
            /*if (substr($name,0,1) === "\""){
                
            }*/
            return trim(trim($name,"\""));
        }
        
        $allCommits=array();
        foreach($repositories as $repoName=>$repo){
            $res['repos'][$repoName] = array();
            $pathOld = getcwd();
            $out=null;
            @chdir($repo);
            exec('(git log --decorate=full --stat --name-status --date=raw --pretty=format:\'%ad,%ae,%an,%d\' --find-renames --full-history --all --no-merges --no-notes) 2>&1', $out, $return);
            @chdir($pathOld);
            
            $res['repos'][$repoName]['logStatus'] = $return;
            if ($return !== 0){
                continue;
            }
            
            $commit = null;
            $commits = array();
            $authors = array();
            $anz = count($out);
            for($i=0;$i<$anz;$i++){
                $out[$i] = trim($out[$i],"'");
                $commit = array('changes'=>array());
                $line = explode(',',$out[$i]);
                
                $o = explode(' ',$line[0]);
                $commit['date'] = $o[0];
                
                $commit['author']['mail'] = $line[1];
                $commit['author']['name'] = $line[2];
                
                if (isset($authorMap[$commit['author']['mail']])){
                    $a=$authorMap[$commit['author']['mail']];
                    $commit['author']['name'] = $a[0];
                    $commit['author']['mail'] = $a[1];
                }
                $authors[$commit['author']['name'].'_'.$commit['author']['mail']] = $commit['author'];
                
                $b=$i+1;
                for(;$b<$anz;$b++){
                    if (strlen($out[$b])===0){
                        break;
                    }
                    $indikator = substr($out[$b],0,1);
                    if ($indikator !== ' '){
                            $p = substr($out[$b],1);
                        if ($indikator === 'R' || $indikator === 'C'){
                            $p = explode("\t",$p);
                            $p[2] = get_filename($p[2]);
                            $p[1] = get_filename($p[1]);
                            if (self::filter($indikator,$p[2])){
                                $commit['changes'][] = array('type'=>'A','file'=>$p[2]);
                            }
                            if (self::filter($indikator,$p[1])){
                                $commit['changes'][] = array('type'=>'D','file'=>$p[1]);
                            }
                        } else {
                            $p = trim($p);
                            $p = get_filename($p);
                            if (self::filter($indikator,$p)){
                                $commit['changes'][] = array('type'=>$indikator,'file'=>$p);
                            }
                        }
                    }
                }
                $i=$b;
                $commit['changes'] = array_reverse($commit['changes']);
                if (!empty($commit['changes'])){
                    $commit['repo'] = $repoName;
                    $commits[]= $commit;
                }
            }
            unset($out);
            if ($commit !== null && isset($commits['author'])){
                $commits[] = $commit;
            }
            unset($commit);
            //$last = $commits[count($commits)-1];
            //$commits[]=array('date'=>$last['date'],'author'=>array('name'=>''),'repo'=>$repoName,'changes'=>array(array('type'=>'A','file'=>'dummy')));
            $commits = array_reverse($commits);
            ///file_put_contents($location. DIRECTORY_SEPARATOR .$repoName.'_authors.json',json_encode($authors));
            unset($authors);
            $allCommits=array_merge($allCommits,$commits);
        }
        
        function custom_sort($a,$b) {
             return $a['date']>$b['date'];
        }
        usort($allCommits, "custom_sort");
        
        // umwandeln zu gource format
        $result = array();
        foreach($allCommits as $commit){
            foreach($commit['changes'] as $change){
                $dat = array($commit['date'],$commit['author']['name'],$change['type'],$commit['repo'].'/'.$change['file']);
                $result[] = implode('|',$dat);
            }
        }
        
        $filename = $location. DIRECTORY_SEPARATOR .'gource_'.date('Ymd_His').'.dat';
        file_put_contents($filename,implode("\n",$result));
        $res['outputFile'] = $filename;
        $res['outputSize'] = filesize($filename);
        
        Installation::log(array('text'=>Installation::Get('main','functionEnd')));
        return $res;
    }

    public static function installListGourceData($data, &$fail, &$errno, &$error)
    {
        Installation::log(array('text'=>Installation::Get('main','functionBegin')));
        $res = array();
        if (is_dir($data['GOURCE']['path'])){
            $files = Installation::read_all_files($data['GOURCE']['path']);
            $res['gourceData'] = array();
            foreach($files['files'] as $file){
                if (pathinfo($file)['extension'] === 'dat'){
                    $data = array();
                    $data['file'] = $file;
                    $data['size'] = filesize($file);
                    $res['gourceData'][] = $data;
                }
            }
        }

        Installation::log(array('text'=>Installation::Get('main','functionEnd')));
        return $res;
    }
    
    public static function installListGourceResult($data, &$fail, &$errno, &$error)
    {
        Installation::log(array('text'=>Installation::Get('main','functionBegin')));
        $res = array();
        if (is_dir($data['GOURCE']['path'])){
            $files = Installation::read_all_files($data['GOURCE']['path']);
            $res['gourceResult'] = array();
            foreach($files['files'] as $file){
                if (pathinfo($file)['extension'] === 'ppm'){
                    $data = array();
                    $data['file'] = $file;
                    $data['size'] = filesize($file);
                    $res['gourceResult'][] = $data;
                }
            }
        }

        Installation::log(array('text'=>Installation::Get('main','functionEnd')));
        return $res;
    }

    public static function installExecuteGource($data, &$fail, &$errno, &$error)
    {
        Installation::log(array('text'=>Installation::Get('main','functionBegin')));
        $res = array();
        $file = $data['GOURCE']['selectedData'];
        $dir = dirname($file);
        $outputFile = $dir . DIRECTORY_SEPARATOR . pathinfo($file)['filename'] . '.ppm';
        
        $exec = 'gource --path "'.$file.'"--load-config "'.dirname(__FILE__). DIRECTORY_SEPARATOR .'gource.conf" -o "'.$outputFile.'"';
        Installation::execInBackground($exec);
            
        Installation::log(array('text'=>Installation::Get('main','functionEnd')));
        return $res;
    }

    public static function installConvertGource($data, &$fail, &$errno, &$error)
    {
        Installation::log(array('text'=>Installation::Get('main','functionBegin')));
        $res = array();
        $file = $data['GOURCE']['selectedResult'];
        $dir = dirname($file);
        $outputFile = $dir . DIRECTORY_SEPARATOR . pathinfo($file)['filename'] . '.mp4';
        
        $exec = 'ffmpeg -y -r 60 -f image2pipe -vcodec ppm -i "'.$file.'" -vcodec libx264 -preset ultrafast -pix_fmt yuv420p -crf 1 -threads 4 -bf 0 "'.$outputFile.'"';
        Installation::execInBackground($exec);
            
        Installation::log(array('text'=>Installation::Get('main','functionEnd')));
        return $res;
    }
}
#endregion BackupSegment