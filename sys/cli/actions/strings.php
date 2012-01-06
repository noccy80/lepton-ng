<?php

class StringsAction extends Action {

    private $db = null;

    public static $commands = array(
        'initialize' => array(
            'arguments' => '\g{lang}',
            'info' => 'Initialize the database with language'
        ),
        'update' => array(
            'arguments' => '[\g{path}|all]',
            'info' => 'Update the strings from source files'
        ),
        'compile' => array(
            'arguments' => '[\g{lang}[,\g{lang},..]|all]',
            'info' => 'Recompile the translations from database'
        ),
        'purge' => array(
            'arguments' => '[\g{lang}[,\g{lang},..]|all]',
            'info' => 'Purge strings from database'
        ),
        'translate' => array(
            'arguments' => '\g{tolang} \g{service}',
            'info' => 'Translate using service'
        ),
        'spell' => array(
            'arguments' => '[\g{lang}]',
            'info' => 'Spell check language'
        ),
        'display' => array(
            'arguments' => '[\g{lang}]',
            'info' => 'Display a language'
        ),
        'languages' => array(
            'arguments' => '',
            'info' => 'List languages'
        )
    );

    private function openDatabase() {
        $dbpath = base::appPath().'languages/strings.dat';
        $this->db = unserialize(file_get_contents($dbpath));
    }

    private function saveDatabase() {
        $dbpath = base::appPath().'languages/strings.dat';
        file_put_contents($dbpath, serialize($this->db));
    }


    public function initialize($lang=null) { 
        if (!$lang) {
            console::writeLn("Missing language code for strings initialize");
        } else {
            console::writeLn("Initializing database...");
            $this->db = array();
            $this->db['db:defaultlang'] = $lang;
            $this->saveDatabase();
            $this->update();
        }
    }

    public function update($path=null) {

        $this->openDatabase();

        $lang = $this->db['db:defaultlang'];

        console::writeLn("Updating default language %s", $lang);
        console::write("Finding files to parse: ");
        $files = array();
        if (!$path) $path = base::appPath();
        $iter = new RecursiveDirectoryIterator($path);
        foreach(new RecursiveIteratorIterator($iter) as $p) {
            $rfp = $p->getRealpath();
            if (strpos($rfp,'/.')) $rfp = null;
            if (fnmatch('*.php',$rfp)) {
                $files[] = $rfp;
            }
        }
        console::writeLn("%d files", count($files));

        console::write("Extracting strings: ");
        $strings = array();
        foreach($files as $file) {
            $fc = file_get_contents($file);
            if (preg_match_all('/intl\:\:str\("(.+?)"/',$fc,$matches)) {
                $strings = array_merge($strings,$matches[1]);
            }
            if (preg_match_all('/intl\:\:str\(\'(.+?)\'/',$fc,$matches)) {
                $strings = array_merge($strings,$matches[1]);
            }
        }
        $ustrings = array_unique($strings);
        console::writeLn("%d strings, %d unique", count($strings), count($ustrings));

        $kstrings = array();
        foreach($ustrings as $string) {
            $kstrings[$string] = $string;
        }

        $this->db['lang:'.$lang] = $kstrings;

        $this->saveDatabase();

    }

    public function display($lang=null) {
        $this->openDatabase();
        if (!$lang) $lang = $this->db['db:defaultlang'];
        foreach($this->db['lang:'.$lang] as $key=>$str) {
             console::writeLn(__astr("\g{%s}: %s"), $key, $str);
        }
    }

    public function languages() {
        $this->openDatabase();
        foreach($this->db as $key=>$val) {
            if (string::like('lang:*',$key)) {
                $keyparts = explode(':',$key);
                console::writeLn('%s: %d strings',$keyparts[1], count($val));
            }
        }
    }

    public function translate($tolang=null) {

        if (!$tolang) throw new BadArgumentException("Translate requires a language!");
        using('lepton.google.translate');
        using('app.manualtranslate');

        $this->openDatabase();

        $fromlang = $this->db['db:defaultlang'];

        // $to = new GoogleTranslate($fromlang,$tolang);
        $to = new ManualTranslate($fromlang,$tolang);
        $tstra = array();

    $err = 0;
    $tstra = $this->db['lang:'.$tolang];
        foreach($this->db['lang:'.$fromlang] as $key=>$str) {
            if (!arr::hasKey($tstra,$key)) {
            $tstr = $to->translate($str);
            if ($tstr) {
                    $tstra[$key] = $tstr;
            } else {
                $err++;
            }
                    console::writeLn(__astr("\g{%s}: %s"), $key, $tstr);
            usleep(1000000);
        }
        }

    if ($err > 0) { console::writeLn(__astr("\c{red %d strings could not be translated.}\nYou should run the script again."), $err); }

        $this->db['lang:'.$tolang] = $tstra;
        $this->saveDatabase();

    }

    public function spell($lang=null) {

        $this->openDatabase();

        if (!$lang) $lang = $this->db['db:defaultlang'];

        if (!function_exists('pspell_new')) {
            console::fatal("strings spell requires the pspell extension");
            exit(1);
        }
        $sp = pspell_new($lang);
        if (!$sp) {
            console::fatal("Couldn't load dictionary for %s", $lang);
            exit(1);
        }

        foreach($this->db['lang:'.$lang] as $key=>$str) {
            $strn = explode(' ',$str);
            foreach($strn as $word) {
                if (!pspell_check($sp, $word)) {
                    console::write("%s: ", $word);
                    $suggestions = pspell_suggest($sp, $word);
                    foreach ($suggestions as $suggestion) {
                        console::write('%s ,',$suggestion);
                    }
                    console::writeLn();
                }
            }
        }

    }
    
    function compile() {
        $this->openDatabase();
        foreach($this->db as $key=>$val) {
            if (string::like('lang:*',$key)) {
                $out = '';
                $keyparts = explode(':',$key);
                $lang = $keyparts[1];
                $out = sprintf("<?php\n// Do not edit! Automatically generated by 'lepton strings'\n\n");
                $out.= sprintf("intl::registerLanguage('%s',array(\n", $lang);
                foreach($val as $src=>$str) {
                    $out.= sprintf("   '%s' => '%s'", str_replace('\'','\\\'',$src), str_replace('\'','\\\'',$str));
                    if ($str != end($val)) $out.=',';
                    $out.="\n";
                }
                $out.= sprintf("));");
                console::writeLn(__astr("\g{Generated:} %s"),$lang);
                file_put_contents(base::appPath().'languages/'.$lang.'.php',$out);
            }
        }
        
    }

}

actions::register(
    new StringsAction(),
    'strings',
    'Manages internationalization and translation of strings',
    StringsAction::$commands
);
