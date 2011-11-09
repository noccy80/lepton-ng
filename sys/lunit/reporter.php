<?php

interface ILunitReporter {
    function report(LunitRunner $runner, $filename);
}

abstract class LunitReporter implements ILunitReporter {
}

