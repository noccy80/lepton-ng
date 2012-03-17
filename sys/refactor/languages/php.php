<?

class PhpRefactor {

    private $fileset;
    private $from = array();
    private $to = null;

    public function __construct() {
    }

    public function read(DOMElement $e) {
        $tn = end(explode(':',$e->tagName));
        switch($tn) {
          case 'function':
          
            foreach($e->childNodes as $cnn) {
                if (typeOf($cnn) == 'DOMElement') {
                    $cnt = end(explode(':',$cnn->tagName));
                    if ($cnt == 'from') {
                        $this->from[] = $cnn->nodeValue;
                    } elseif ($cnt == 'to') {
                        $this->to = $cnn->nodeValue;
                    } else {
                        printf("Warning: Didn't expect %s here\n", $cnn->nodeName); 
                    }
                }
            }
            
            printf(__astr("[\b{phprefactor}] Refactoring{%s} --> %s\n"), join(', ',$this->from), $this->to);
            break;
            
          default:
            printf("I don't know what to do with %s!\n", $tn);
            
        }
    }
    
    public function refactor(RefactorFileset $fileset) {
        $tofunc = reset(explode('(',$this->to));
        $toargs = reset(explode(')',end(explode('(',$this->to))));
        $mod = array();
        foreach($fileset->files as $file) {
            if (file_exists($file.'.rf')) {
                $in = file_get_contents($file.'.rf');
            } else {
                $in = file_get_contents($file);
            }
            foreach($this->from as $from) {
                $func = reset(explode('(',$from));
                $args = reset(explode(')',end(explode('(',$from))));
                if ($args == $toargs) {
                    // Just change function names
                    $ref = str_replace($func.'(',$tofunc.'(',$in);
                }
            }
            if ($ref != $in) {
                $mod[] = $file;
                file_put_contents($file.'.rf', $ref);
            }
        }
        return $mod;
    }

}

refactors::registerRefactor('PhpRefactor', 'lepton.refactor.php');
