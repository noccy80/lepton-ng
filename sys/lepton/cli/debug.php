<?php

class CliDebugProvider implements IDebugProvider {

    function inspect($data,$table=false) {

        var_dump($data);

    }

}

debug::setDebugProvider(new CliDebugProvider());
