<?php

using('lunit.reporter');

class HtmlLunitReporter extends LunitReporter {
    function report(LunitRunner $runner, $filename) {
        $f = fopen($filename,'w');
        $res = $runner->getResults();
        fputs($f,"<h1>Lunit Test Report</h1>");
        foreach($res as $casename=>$casedata) {
            fputs($f,sprintf("<h2>%s</h2><p>%s</p>", $casedata['meta']['description'], $casename));
            fputs($f,sprintf("<table><tr><th>Test</th><th>Result</th><th>Time</th><th>Message</th></tr>"));
            foreach($casedata['tests'] as $testname=>$testdata) {
                fputs($f,sprintf("<tr><td>%s</td><td>%s</td><td>%.3fs</td><td>%s</td></tr>", $testdata['meta']['description'], ($testdata['passed'])?'Passed':'Failed', $testdata['elapsed'][0], $testdata['message']));
            }
            fputs($f,sprintf("</table>"));
        }
        fclose($f);
    }
}
