<?php __fileinfo("Unit testing framework");

	interface ITestCase {
		function getName();
	}

	abstract class TestCase implements ITestCase {
	
		private $testname;
		function getName() {
			return $this->testname;
		}
		function assert($cond) {
			if (!$cond) throw new TestException("Assertion Failed: ".$cond." ".$this->testname);
		}
	
	}
	
	interface ITestReporter {
		function beginGroup($title);
		function endGroup();
		function addItem($index,$name,$status);
	}
	
	abstract class TestReporter implements ITestReporter {
	
	}
	
	abstract class TestRunner {
	
		static $testcases = array();
	
		static function run(TestReporter $reporter) {
			foreach(self::$testcases as $test) {
				$testctl = new $test();
				$reporter->beginGroup($testctl->getName());
				$reflect = new ReflectionClass($testctl);
				$methods = $reflect->getMethods();
				foreach($methods as $method) {
					if ((substr($method->getName(),0,1) == '_') &&
						(substr($method->getName(),1,1) != '_')) {
						try {
							call_user_func_array(array($testctl,$method->getName()));
							$reporter->addItem($index++,$method->getDocComment(),"Passed");
						} catch (TestException $e) {
							$reporter->addItem($index++,$method->getDocComment,"Failed");
						}
					}
				}
				$reporter->endGroup();
				unset($testctl);

			
			}
		}
		
		static function registerTest($testcase) {
			
			self::$testcases[] = $testcase;	
			
		}
	
	}
