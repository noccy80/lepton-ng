<?php

class XsnpUtils {

    static function parseIdentity($identity) {
        return array($host, $user, $meta);
    }

    static function resolve($identity) {
        // Find endpoint
    }

}

abstract class XsnpNetworkProtocols {
    const XNP_PROFILE = "xsnp::ns::profile";
    const XNP_PHOTOS = "xsnp::ns::photos";
    const XNP_UPDATES = "xsnp::ns::updates";
}


interface IXsnpServerExtension {
    /**
     * @brief What is handled by this extension
     * @return Array Array of XsnpServerExtension::XNP_* constants or namespaces
     */
    function handles();
}

interface IXsnpClientExtension {
    function handles();
}

abstract class XsnpServerExtension implements IXsnpServerExtension { }

abstract class XsnpClientExtension implements IXsnpClientExtension { }
