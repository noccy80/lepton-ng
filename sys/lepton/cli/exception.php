<?php

/**
 * @class ConsoleExceptionHandler
 *
 *
 */
class ConsoleExceptionHandler extends ExceptionHandler {

    function exception(Exception $e) {

        logger::emerg("Unhandled exception: (%s) %s in %s:%d", get_class($e), $e->getMessage(), str_replace(BASE_PATH,'',$e->getFile()), $e->getLine());
        Console::debugEx(0, get_class($e), "Unhandled exception: (%s) %s in %s:%d", get_class($e), $e->getMessage(), str_replace(BASE_PATH,'',$e->getFile()), $e->getLine());
        $f = file($e->getFile());
        foreach($f as $i=>$line) {
            $mark = (($i+1) == $e->getLine())?'=> ':'   ';
            $f[$i] = sprintf('  %05d. %s',$i+1,$mark).$f[$i];
            $f[$i] = str_replace("\n","",$f[$i]);
        }
        $first = $e->getLine() - 4; if ($first < 0) $first = 0;
        $last = $e->getLine() + 3; if ($last >= count($f)) $last = count($f)-1;
        $source = join("\n",array_slice($f,$first,$last-$first));
        Console::debugEx(0, get_class($e), Console::backtrace(0,$e->getTrace(),true));
        Console::debugEx(LOG_LOG,"Exception","Source dump of %s:\n%s", str_replace(BASE_PATH,'',$e->getFile()), $source);
        $rv = 1;
        logger::emerg("Exiting with return code %d after exception.", $rv);
        Console::debugEx(LOG_BASIC,__CLASS__,"Exiting with return code %d after exception.", $rv);

    }

}

