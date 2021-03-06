--TEST--
Check if closure can safely use variable names also present in outside scope
--FILE--
<?php
// variable present in outside scope
$variable = 1;

class Test {
    public function m(){
        echo "METHOD" . PHP_EOL;
    }
}

function setup($variable){
    dd_trace("Test", "m", function() use ($variable){
        $this->m();
        echo "HOOK " . $variable . PHP_EOL;
    });
}

(new Test())->m();
setup(1);
(new Test())->m();
setup(3);
(new Test())->m();

?>
--EXPECT--
METHOD
METHOD
HOOK 1
METHOD
HOOK 3
