<?php
 
class REPL {
    private $stream;
    
    public function __construct($streamPath = 'php://stdin'){
        $this->stream = fopen($streamPath, 'r');
    }
    
    public function begin(){
        $scopeCount = 0;
        $parenCount = 0;
        $inQuotes = 0; // 1 for single, 2 for double
        $lines = '';
        $currentLine;
        $char;
        
        echo <<<INFO
PHP REPL - Written by Dominic Charley-Roy

INFO;
        
        while (true) {
            if ($scopeCount == 0 && $parenCount == 0 && $inQuotes == 0) {
                echo "> ";
            } else {
                echo "  ";
            }
            
            $currentLine = trim(fgets($this->stream));
            
            for ($i = 0; $i < strlen($currentLine); $i++) {
                $char = $currentLine[$i];
                
                if ($inQuotes == 0 && $char == '{') {
                    $scopeCount++;
                } else if ($char == '\'' && 
                        ($i == 0 || $currentLine[$i-1] != '\\') && 
                        ($inQuotes == 1 || $inQuotes == 0)) {
                    $inQuotes = ($inQuotes == 0) ? 1 : 0;
                } else if ($char == '"' && 
                        ($i == 0 || $currentLine[$i-1] != '\\') && 
                        ($inQuotes == 2 || $inQuotes == 0)) {
                    $inQuotes = ($inQuotes == 0) ? 2 : 0;
                } else if ($inQuotes == 0 && $char  == '(') {
                    $parenCount++;
                } else if ($inQuotes == 0 && $char == '}') {
                    if ($scopeCount == 0){
                        echo "\nERROR: Unbalanced scope braces.";
                        $parenCount = $parenCount = $inQuotes = 0;
                        $currentLine = '';
                        break;
                    }
                    
                    $scopeCount--;
                } else if ($inQuotes == 0 && $char == ')'){ 
                    if ($parenCount == 0){
                        echo "\nERROR: Unbalanced parenthesis.";
                        $parenCount = $parenCount = $inQuotes = 0;
                        $currentLine = '';
                        break;
                    }
                    
                    $parenCount--;
                }
            }
            
            $lines .= $currentLine."\n";
            
            if ($scopeCount == 0 && $parenCount == 0 && $inQuotes == 0) {
                if (strpos($lines, ';') === false) {
                    $return = eval('var_dump('.$lines.');');
                } else {
                    $return = eval($lines.';');
                }
                echo $return;
                $lines = '';
            }
        }
    }

    public function __destruct() {
        fclose($this->stream);
    }
}

$repl = new REPL();
$repl->begin();