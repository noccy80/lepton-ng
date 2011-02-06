<?php

    /**
     * Comment feed class. Manages comments for items organized in namespaces
     * and identified by URIs. This allows for a virtually unlimited number
     * of comment feeds.
     *
     * @since 0.2
     * @author Christopher Vagnetoft <noccy@chillat.net>
     */
    class Comments extends Library {

        function initialize() {
            $sm = DBX::getInstance(DBX)->getSchemaManager();
            $sm->checkSchema('comments');
        }

        /**
         * Retrieves a comment feed.
         *
         * @param string $nsuri The namespace and URI to query
         * @param int $items The number of items to show
         * @param int $pagenumber The page to fetch, 1-based
         * @return CommentFeed The comments
         */
        function getFeed($nsuri,$items=10,$pagenumber=1) {

            $start = (int)(($pagenumber - 1) * $items);
            $db = DBX::getInstance(DBX);

            list($ns,$uri) = StringUtil::getNamespaceURI(
                config::get('lepton.comments.defaultnamespace','comments'),
                $nsuri
            );

            $rs = $db->getRows("SELECT * FROM comments WHERE ns='%s' AND uri='%s' ORDER BY postdate DESC LIMIT %d,%d", $ns, $uri, $start, $items);
            // TODO: Sanitizing needs a bit of work.
            foreach($rs as $idx => $row) {
                $str = $rs[$idx]['message'];
                $str = str_replace('<','&lt',$str);
                $str = str_replace('>','&gt',$str);
                $rs[$idx]['message'] = $str;
                $rs[$idx] = array_merge($rs[$idx], (array)unserialize($row['ambient']));
            }
            return $rs;

        }

        /**
         * Return the number of comments contained in the feed.
         *
         * @param string $nsuri The namespace and URI to query
         * @return int The number of comments
         */
        function getCount($nsuri) {

            $db = DBX::getInstance(DBX);
            list($ns,$uri) = StringUtil::getNamespaceURI(
                config::get('lepton.comments.defaultnamespace','comments'),
                $nsuri
            );
            $rs = $db->getSingleRow("SELECT COUNT(*) FROM comments WHERE ns='%s' AND uri='%s'", $ns, $uri);
            return (int)$rs[0];

        }

        /**
         * Post a comment to a feed.
         *
         * @param string $nsuri The namespace and URI to post to
         * @param array $item The item to post
         */
        function post($nsuri, $item) {

            $db = DBX::getInstance(DBX);

            // Extract namespace and URI
            list($ns,$uri) = StringUtil::getNamespaceURI(
                config::get('lepton.comments.defaultnamespace','comments'),
                $nsuri
            );

            // TODO: Implement spam filtering here
            if (Lepton::has('spamfilter')) {
                if (Spamfilter::checkContent($item)) {
                    $status = 'QUARANTINED';
                } else {
                    $status = 'PUBLISHED';
                }
            } else {
                $status = 'PUBLISHED';
            }

            // Check to make sure the required parameters are present
            if (!$item['message']) {
                throw new BaseException('Missing required key message for comment item');
            }
            if (((!$item['name']) || (!$item['email'])) && (!$item['userid'])) {
                throw new BaseException('Missing required key for comment item (name,email or userid)');
            }
            if (!$item['ip']) $item['ip'] = request::getRemoteHost();

            $ambient = array();
            foreach($item as $k=>$i) {
                switch($k) {
                    case 'ip':
                    case 'subject':
                    case 'message':
                    case 'userid':
                    case 'name':
                    case 'email':
                    case 'website':
                    case 'status':
                        break;
                    default:
                        $ambient[$k] = $i;
                }
            }

            // Save the comment
            $rs = $db->updateRow(
                "INSERT INTO comments (ns,uri,postdate,userid,name,email,website,ip,message,status,ambient) ".
                "VALUES ('%s','%s',NOW(),'%d','%s','%s','%s','%s','%s','%s','%s')",
                $ns, $uri,
                $item['userid'],
                $item['name'], $item['email'], $item['website'],
                $item['ip'], $item['message'], $status, serialize($ambient));

        }

    }

    Library::register('comments',array(
        'baseclass' =>   'Comments',
        'alias' =>       'comments',
        'onload' =>      new Callback(Comments, 'initialize')
    ));

?>
