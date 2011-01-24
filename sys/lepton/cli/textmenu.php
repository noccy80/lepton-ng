<?php

class TextMenu {
	private $title;
	private $options = array();
	private $columns = null;
	private $width = null;
	function __construct($title,array $options=null) {
		$this->title = $title;
		if ($this->options) $this->options = $options;
	}
	function addOption($key,$value,$default) {
		$this->options[$key] = array(
			'value' => $value,
			'default' => $default,
			'state' => $default
		);
	}
	function getOption($key) {
		return $this->options[$key]['state'];
	}
	function setLayout($columns=null,$width=null) {
		$this->columns = $columns;
		$this->width = $width;
	}
	function runMenu() {
		while (true) {
			$col = 0;
			console::writeLn($this->title);
			$keys = array();
			foreach($this->options as $key=>$option) {
				if ($this->width) {
					console::write(' [%s] %-'.$this->width.'s', ($option['state'])?__astr('\b{x}'):' ', $key);
				} else {
					console::writeLn('   %s. [%s] %s', $key, ($option['state'])?__astr('\b{x}'):' ', $option['value']);
				}
				if ($this->columns) {
					$col++;
					if ($col > $this->columns) {
						$col = 0;
						console::writeLn();
					}
				}
				$keys[] = $key;
			}
			if (($this->columns) && ($col > 0)) {
				console::writeLn();
			}
			if (count($keys) > 10) {
				$keys = array_merge(array_slice($keys,0,9),array('...'));
			}
			$keystr = join('/',$keys);
			$prompt = '[Y/n/help/all/none/invert/reset/'.$keystr.']: ';
			$rl = readline::read($prompt);
			foreach(explode(' ',$rl) as $r) {
				foreach($this->options as $key=>$option) {
					if ($key == $r) {
						$this->options[$key]['state'] = !$this->options[$key]['state'];
					}
				}
				if (strtolower($r) == 'help') {
					console::writeLn("Type any of the alternatives listed above. They will be evaluated in the order they are provided.");
					console::writeLn("When you are done, hit enter on an empty line or enter 'y'. To cancel, enter 'n'.");
				}
				if ((strtolower($r) == 'y') || ($r == '')) {
					return true;
				}
				if (strtolower($r) == 'n') {
					return false;
				}
				if (strtolower($r) == 'all') {
					foreach($this->options as $key=>$option) {
						$this->options[$key]['state'] = true;
					}
				}
				if (strtolower($r) == 'none') {
					foreach($this->options as $key=>$option) {
						$this->options[$key]['state'] = false;
					}
				}
				if (strtolower($r) == 'reset') {
					foreach($this->options as $key=>$option) {
						$this->options[$key]['state'] = $this->options[$key]['default'];
					}
				}
				if (strtolower($r) == 'invert') {
					foreach($this->options as $key=>$option) {
						$this->options[$key]['state'] = !$this->options[$key]['state'];
					}
				}
			}
		}
	}
}
