<?php

	interface ITemplate {
		function loadTemplate($template);
		function render();
	}

	abstract class Template implements ITemplate {
		protected $_tplfile = null;
		function __construct($template=null) {
			if ($template) $this->loadTemplate($template);
		}
		function __toString() {
			return $this->render();
		}
	}

	class LiteTemplate extends Template {
		const NS_LITE = 'lepton:template:lite';
		const NS_LITEMARKUP = 'lepton:template:lite:markup';
		private $_domdoc = null;
		function loadTemplate($template) {
			$this->_tplfile = $template;
			$fn = APP_PATH.'views/'.$template;
			$this->_domdoc = DomDocument::load($fn);
		}
		function render() {
			$doc = $this->_domdoc;
			$base = $doc->getElementsByTagNameNs(self::NS_LITE,'template');
			if ($base->length > 0) {
				$nod = $base->item(0);			
				$buf = $this->renderRecursive($nod);
				return $buf;
			} else {
				throw new TemplateException("Bad template");
			}
		}
		private function renderRecursive($nod) {
			$ret = '';
			for($n = 0; $n < $nod->childNodes->length; $n++) {
				$cn = $nod->childNodes->item($n);
				if ($cn->namespaceURI == self::NS_LITEMARKUP) {
					$t = explode(':',$cn->nodeName);
					switch($t[1]) {
						case 'debug':
							$ret .= "THIS IS THE DEBUGGING INFO";
							break;
						default:
							Console::warn("Unknown LiteMarkup tag: %s", $cn->nodeName);
					}			
				} else {
					switch($cn->nodeName) {
						case '#text':
							$ret .= $cn->nodeValue;
							break;
						default:
							$ret .= '<'.$cn->nodeName.'>'.$this->renderRecursive($cn).'</'.$cn->nodeName.'>';
							break;
					}
				}
			}
			return $ret;
		}
	}
	
	class TemplateException extends BaseException { }
	
?>
