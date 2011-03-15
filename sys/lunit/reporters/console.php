<?php

// Move to reporter base class:
interface ILunitReporter {
	function report(LunitRunner $runner);
}

abstract class LunitReporter implements ILunitReporter {
}

class ConsoleLunitReporter extends LunitReporter {
	function report(LunitRunner $runner) {
	}
}
