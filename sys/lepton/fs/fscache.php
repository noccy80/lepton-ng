<?php

    /**
     * Filesystem cache library. Caches content such as dynamic images in the
     * filesystem and the system database.
     *
     * @todo Add proper garbage collection
     *
     * @author Christopher Vagnetoft <noccy@chillat.net>
     */
    class FsCache {

        /**
         * Checks the cache for the item with the specific hash, if it exists
         * it will be sent to the client using the content type that was
         * attached to the set() call.
         *
         * @param string $name The name of the object
         * @return boolean True if the object was returned from the cache
         */
        function get($name) {

            $hash = md5($name);
            $base = config::get('lepton.fscache.path','cache');

            $db = DBX::getInstance(DBX);
            $db->getSchemaManager()->checkSchema('fscache');
            $rs = $db->getSingleRow("SELECT contenttype,expires FROM fscache WHERE hash='%s'", $hash);
            if ($rs) {
                if ((!file_exists($base.'/'.$hash)) || (time() > $rs['expires'])) {
                    @unlink($base.'/'.$hash);
                    $db->updateRow("DELETE FROM fscache WHERE hash='%s'", $hash);
                    return false;
                } else {
                    response::contentType($rs['contenttype']);
                    response::sendFile($base.'/'.$hash);
                    return true;
                }
            } else {
                return false;
            }

        }

        /**
         * Puts (or updates) the item into the cache. Use duration::toMinutes()
         * to calculate a proper expiry for the item.
         *
         * @param string $name The name of the object
         * @param string $contenttype The content type of the object
         * @param string $data The object contents
         * @param int $expiry The expiry of the content in minutes
         * @return boolean True if it was successful
         */
        function set($name, $contenttype, $data, $expiry=null) {

            $hash = md5($name);
            $base = config::get('lepton.fscache.path','cache');

            if (!$expiry) $expiry = config::get('lepton.fscache.defaultexpiry', '1h');
            $expires = duration::toSeconds($expiry);

            $db = DBX::getInstance(DBX);
            $db->getSchemaManager()->checkSchema('fscache');
            $db->updateRow(
                "REPLACE INTO fscache (hash,contenttype,expires) VALUES ('%s','%s','%d')",
                $hash, $contenttype, $expires
            );

            file_put_contents( $base.'/'.$hash , $data );

        }

    }
