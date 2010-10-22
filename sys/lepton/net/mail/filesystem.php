<?php __fileinfo("Mail store: Filesystem");

using('lepton.crypto.uuid');

/**
 * @brief Filesystem-based XML backend for the Mailbox class.
 *
 * Stores the messages in the folder specified with the configuration key
 * "lepton.mail.filesystem.storage". This class uses a very basic locking
 * mechanism using lockfiles. This is just fine for a smaller website, but
 * if you will need to access the mailboxes a lot, the DatabaseMailStorage
 * is recommended.
 *
 * @see DatabaseMailStorage
 * @author Christopher Vagnetoft <noccy@chillat.net>
 * @package lepton.net.mail
 *
 */
class FilesystemMailStorage extends MailStorage {

    const KEY_LOCK_TIMEOUT = 'lepton.mail.filesystem.locktimeout';
    const KEY_STORAGE_PATH = 'lepton.mail.filesystem.storage';
    const DEF_LOCK_TIMEOUT = 10;

    private $mbpath;
    private $lockpath;
    private $mbox;
    private $lock;
    private $xpath;

    /**
     * Open a message store.
     *
     * @param string $identity The identity to open as an URI
     */
    function open($identity) {

        $file = parse_url($identity, PHP_URL_HOST);

        // Set up the paths and objects we need
        $this->mbpath = config::get(FilesystemMailStorage::KEY_STORAGE_PATH,
            BASE_PATH.'cache').'/'.$file.'.xml';
        $this->lockpath = config::get(FilesystemMailStorage::KEY_STORAGE_PATH,
            BASE_PATH.'cache').'/'.$file.'.lock';
        $this->mbox = new DOMDocument('1.0');

        // Primitive locking for the win :) This should be more than enough
        // for whoever decides to use this. Will probably not be too good to
        // the disk under heavy load.
        $t = new Timer(true);
        $to = config::get(FilesystemMailStorage::KEY_LOCK_TIMEOUT,
            FilesystemMailStorage::DEF_LOCK_TIMEOUT);
        console::debugEx(LOG_DEBUG,__CLASS__,"Acquiring lock...");
        while(file_exists($this->lockpath)) {
            usleep(100000);
            if ($t->getElapsed() > $to) {
                throw new MailException("Timed out waiting for lock.");
            }
        }
        unset($t);
        $this->lock = fopen($this->lockpath,'w');

        // Check if the mailbox exists
        if (file_exists($this->mbpath)) {
            $this->mbox->load($this->mbpath);
            console::debugEx(LOG_DEBUG,__CLASS__,"Loaded mailbox %s", $this->mbpath);
        } else {
            console::debugEx(LOG_DEBUG,__CLASS__,"Created new mailbox %s", $this->mbpath);
            $this->mbox->appendChild(
                $this->mbox->createElement('mailbox')
            );
        }

    }

    /**
     * @brief Close a previously opened handle to a mailbox.
     * Saves the mailbox and releases all locks.
     */
    function close() {

        $this->mbox->save($this->mbpath);

        // Free the lock if it exists
        if (($this->lock) || (file_exists($this->lockpath))) {
            console::debugEx(LOG_DEBUG,__CLASS__,"Freeing locks...");
            fclose($this->lock);
            unlink($this->lockpath);
        }

    }

    /**
     * Add a message to the message store.
     *
     * @param MailMessage $message The message
     */
    function addMessage($message) {

        $msgel = $this->mbox->createElement('message');

        $message->msgid = uuid::v4();

        $msgel->setAttribute('read', $message->read);
        $msgel->setAttribute('subject', $message->subject);
        $msgel->setAttribute('msgid', $message->msgid);

        $msgbody = $this->mbox->createTextNode($message->body);
        $msgel->appendChild($msgbody);

        $this->mbox->documentElement->appendChild($msgel);

        return true;

    }

    /**
     * @brief returns the number of messages unread, total, etc.
     *
     * Type should be one of the Mailbox::MBX_* constants.
     *
     * @param int $type The message type to query
     * @param string $folder The folder to query
     * @return int The number of read messages
     */
    function getMessageCount($type = Mailbox::MBX_ALL, $folder = null) {

        $this->xp = new DomXPath($this->mbox);
        
        switch($type) {
            case Mailbox::MBX_ALL:
                $nodes = $this->xp->query("message");
                break;
            case Mailbox::MBX_UNREAD:
                $nodes = $this->xp->query("message[@read='0']");
                break;
        }

        return $nodes->length;
        
    }

    /**
     * @brief Retrieve a list of matching messages.
     *
     * Returns an array containing the matching messages and their msgid,
     * unread status, from, date, size and subject properties. Use the
     * getMessage method to retrieve the actual message.
     *
     * @see FilesystemMessageStorage::getMessage
     * @param int $type The type of message to query
     * @param string $folder The folder to query
     * @return array The message list
     */
    function getMessageList($type = Mailbox::MBX_ALL, $folder = null) {

        $messages = array();
        $this->xp = new DomXPath($this->mbox);

        if ($type == Mailbox::MBX_ALL) {
            $nodes = $this->xp->query("message");
        } else {
            $nodes = $this->xp->query("message[@type='". $type ."']");
        }

        for($n = 0; $n < $nodes->length; $n++) {
            $messages[] = array(
                'msgid' =>    $nodes->item($n)->getAttribute('msgid'),
                'unread' =>   ($nodes->item($n)->getAttribute('read') != '1'),
                'from' =>     $nodes->item($n)->getAttribute('from'),
                'date' =>     $nodes->item($n)->getAttribute('date'),
                'size' =>     strlen($nodes->item($n)->nodeValue),
                'subject' =>  $nodes->item($n)->getAttribute('subject')
            );
        }

        return $messages;

    }

    /**
     * @brief Return the message with the matching id
     * 
     * @param string $msgid The message id
     * @return MailMessage The message
     */
    function getMessage($msgid) {

        console::debugEx(LOG_DEBUG,__CLASS__,"Fetching message %s...", $msgid);
        $messages = $this->xp->query("message[@msgid='". $msgid ."']");

        $mailmsg = new MailMessage();
        $mailmsg->subject = $messages->item(0)->getAttribute('subject');
        $mailmsg->body = $messages->item(0)->nodeValue;

        return $mailmsg;
    }

}

Mailbox::registerBackend('fs', 'FilesystemMailStorage');