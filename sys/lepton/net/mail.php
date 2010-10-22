<?php __fileinfo("Mail and Message functionality", array(
    'author' => 'noccy@chillat.net',
    'version' => '1.0.0'
));

using('lepton.mvc.model');

class MailException extends BaseException { }

class Mailbox {

    const MBX_ALL = 0;
    const MBX_UNREAD = 1;
    const MBX_READ = 2;
    const MBX_REPLIED = 3;
    const MBX_FORWARDED = 4;
    const MBX_DELETED = 5;

    private $handler;
    private $identity;
    private $mailbox;

    static $_handlers = array();

    static function registerBackend($scheme,$handler) {
        self::$_handlers[$scheme] = $handler;
        console::debugEx(LOG_DEBUG, __CLASS__, "Registered backend: %s (%s)", $scheme, $handler);
    }

    function __construct($identity=null) {

        if ($identity) {
            $this->openMailbox($identity);
        }

    }

    function openMailbox($identity) {

        $box = parse_url($identity);
        if (isset(self::$_handlers[$box['scheme']])) {
            $handler = self::$_handlers[$box['scheme']];
            $this->identity = $identity;
            // Activate the selected backend
            console::debugEx(LOG_DEBUG,__CLASS__,"Opening identity: %s", $identity);
            $this->mailbox = new $handler($identity);
            
        } else {
            throw new MailException("Bad backend: ". $box['scheme']." (from ".$identity.")");
        }

    }

    function saveMessage(MailMessage $message) {
        if ($this->mailbox) {
            return $this->mailbox->addMessage($message);
        } else {
            throw new MailException("Mailbox not opened!");
        }
    }

    function getMessageList() {
        if ($this->mailbox) {
            return $this->mailbox->getMessageList();
        } else {
            throw new MailException("Mailbox not opened!");
        }
    }

    function getMessage($msgid) {
        if ($this->mailbox) {
            return $this->mailbox->getMessage($msgid);
        } else {
            throw new MailException("Mailbox not opened!");
        }
    }

    function getUnreadCount() {
        if ($this->mailbox) {
            return $this->mailbox->getMessageCount(Mailbox::MBX_UNREAD);
        } else {
            throw new MailException("Mailbox not opened!");
        }
    }

}


class MailMessage extends AbstractModel {
    var $model = 'MailMessage';
    var $fields = array(
        'msgid' => 'string',
        'from' => 'int',
        'to' => 'int',
        'read' => 'int between 0 and 1 default 0',
        'subject' => 'string',
        'body' => 'string'
    );
}

interface IMailStorage {
    function open($identity);
}

abstract class MailStorage implements IMailStorage {

    function __construct($identity) {
        $this->open($identity);
    }

}

// Load the backends
foreach((array)config::get('lepton.mail.backends') as $backend) using($backend);

///////////////////////////////////////////////////////////////////////////////
//
// To use this test-application, use the lepton command line utility:
//
//    $ bin/lepton -llepton.net.mail -- run mailtest
//
// You should then get the mail prompt and have access to the various commands
// and utilities. Remember to run it from the base application path. Otherwise
// you have to define the BASE_PATH environment variable.
//

class MailTest extends ConsoleApplication {
    private $sess;
    function main($argc,$argv) {
        do {
            $cmd = readline::read("mail> ");
            $c = explode(' ',$cmd);
            switch($c[0]) {
                case 'compose':
                    $msg = new MailMessage();
                    $msg->subject = readline::read("Subject   : ");
                    $msg->from    = readline::read("From      : ");
                    $msg->to      = readline::read("To        : ");
                    console::writeLn("Compose your message; when you are done, just enter a dot on its own line(.)");
                    $lines = array();
                    do {
                        $line = readline::read("");
                        if ($line == null) $line = '';
                        if ($line == '.') break;
                        $lines[] = $line;
                    } while (true);
                    $msg->body = join("\n", $lines);
                    $msg->read = 0;
                    $this->sess->saveMessage($msg);
                    break;
                case 'open':
                    $this->sess = new Mailbox($c[1]);
                    console::writeLn("Unread messages: %d", $this->sess->getUnreadCount());
                    break;
                case 'list':
                    $this->msgs = $this->sess->getMessageList();
                    foreach($this->msgs as $index=>$msg) {
                        console::writeLn("%d: %s (%s) %db", $index, $msg['subject'], $msg['from'], $msg['size']);
                    }
                    break;
                case 'read':
                    if (!isset($this->msgs)) $this->msgs = $this->sess->getMessageList();
                    $msg = $this->sess->getMessage($this->msgs[$c[1]]['msgid']);
                    console::writeLn($msg->body);
                    break;
                case 'help':
                    console::writeLn("Commands: open close read compose delete list");
                    break;
                case 'exit': // handled by default:
                default:
                    break;
            }
        } while($cmd != 'exit');
    }
}