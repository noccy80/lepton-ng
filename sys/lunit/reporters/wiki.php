<?php

using('lunit.reporter');

class WikiLunitReporter extends LunitReporter {
	function report(LunitRunner $runner, $filename) {
		$f = fopen($filename,'w');
		$res = $runner->getResults();
		fputs($f,"= Lunit Test Report =\n");
		foreach($res as $casename=>$casedata) {
			fputs($f,sprintf("== %s (%s) ==\n", $casedata['meta']['description'], $casename));
			fputs($f,sprintf("||= Test =||= Result =||= Time =||= Message =||\n"));
			foreach($casedata['tests'] as $testname=>$testdata) {
				fputs($f,sprintf("||%s||%s||%.3fs||%s||\n", $testdata['meta']['description'], ($testdata['passed'])?'Passed':'Failed', $testdata['elapsed'][0], $testdata['message']));
			}
		}
		fclose($f);
	}
}
