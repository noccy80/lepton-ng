<?php

ModuleManager::load('lepton.mvc.router');
ModuleManager::load('lepton.mvc.controller');

class DefaultRouter extends Router {
    function routeRequest() {

        $controller = $this->getSegment(0);
        $method = $this->getSegment(1);
        $arguments = $this->getSegmentSlice(2);

        Controller::invoke($controller, $method, $arguments);

    }
}

