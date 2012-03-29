<?php

class AssertionException extends CriticalException {
    static function callback($file,$line,$msg) {
        throw new AssertionException(sprintf("Assertion failed in %s online %d: %s",  $file, $line, $msg));
    }
}
assert_options(ASSERT_CALLBACK, array('AssertionException','callback'));
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
