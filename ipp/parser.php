<?php

require('error.php'); 

ini_set('display_errors', 'stderr');

$counter = 0;        // Instruction counter.
$is_header = false;
$delimiter = "#";


//------------------------------------Prints out help information.
$long_params = array("help");
$params = getopt("", $long_params);
if (array_key_exists("help", $params)){
    if($argc == 2 && $argv[1] == "--help"){
        echo("\n\tA filter type script (parse.php in PHP 8.1) reads the source code"."\n".
        "\tin IPPcode23 from the standard input, checks the lexical and syntactic"."\n".
        "\tcorrectness of the code and writes it to standard output XML representation"."\n". 
        "\tof the program."."\n\n");
        echo("\t"."--help       prints out this help information.\n\n");

        exit(success);      // Exit 0.
    }
    else{
        echo("\tWrong input! Use --help parameter for more helpful information.\n");
    }
}
//TODO ERROR
//--------------------------------------------------------------------


echo("<?xml version=\"1.0\" encoding=\"UTF-8\"?>" . "\n");


while($file = fgets(STDIN)){

    $splitted_file = explode(' ', trim($file, "\n"));

    if(!$is_header){
        if($splitted_file[0] == ".IPPcode23"){
            $is_header = true;
            echo("<program language=\"IPPcode23\">"."\n");
            continue;
        }
        else{
            print_error("Error! Wrong or missing file header!" . "\n" );
            exit(missing_or_bad_header);     // Error 21.       
        }
    }

    if (substr(trim($file), 0, strlen($delimiter)) == $delimiter) {
        continue; // ignore the line and move on to the next one
    }



    switch(strtoupper($splitted_file[0])){
//-------------------------- Instructions with no params.
        case 'CREATEFRAME':
        case 'PUSHFRAME':
        case 'POPFRAME':    
        case 'RETURN':
        case 'BREAK':
            $counter ++;
            if(empty($splitted_file[1])){
                echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">"."\n");
                echo("\t</instruction>\n");   
            }
            else{
                print_error("Error! Unexpected instruction parameter!\n");  
                exit(23);  // Error 23.
            }
            break;

                
//--------------------- Instructions with 1 param.: <var>.    
        case 'DEFVAR':  
        case 'POPS':    
            $counter++;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[1])){
                echo("\t\t<arg1 type=\"var\">".$splitted_file[1]."</arg1>\n");
            }
            else{
                print_error("Error! Wrong instruction parameter!\n");
                exit(23);  // Error 23.
            }

            if(empty($splitted_file[2])){
                echo("\t</instruction>\n");
                break;
            }
            else{
                print_error("Error! Unexpected instruction parameters!\n");
                exit(23);  // Error 23.
            }
            break;


//--------------------- Instructions with 1 param.: <symb>; <symb> can be var or const.
        case 'PUSHS':   
        case 'WRITE':   
        case 'EXIT':    
        case 'DPRINT':  
            $counter++;
            // <var>
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[1])){
                echo("\t\t<arg1 type=\"var\">".$splitted_file[1]."</arg1>\n");
            }   
            // <const>       
            elseif(((preg_match("/^int@-?\d+/", $splitted_file[1])))){
                $string = $splitted_file[1];
                $string_name = substr($string, strpos($string, "@") + 1); 
                echo("\t\t<arg1 type=\"int\">".$string_name."</arg1>\n");
            }
            elseif(preg_match("/^nil@nil/", $splitted_file[1])){
                $string = $splitted_file[1];
                $string_name = substr($string, strpos($string, "@") + 1); 
                echo("\t\t<arg1 type=\"nil\">".$string_name."</arg1>\n");
            }
            elseif((preg_match("/^string@/", $splitted_file[1]))){
                $string = $splitted_file[1];
                $string_name = substr($string, strpos($string, "@") + 1); 
                echo("\t\t<arg1 type=\"string\">".$string_name."</arg1>\n");
            }
            elseif(preg_match("/^bool@(true|false)/", $splitted_file[1])){
                $string = $splitted_file[1];
                $string_name = substr($string, strpos($string, "@") + 1); 
                echo("\t\t<arg1 type=\"bool\">".$string_name."</arg1>\n");
            }
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(23);  // Error 23.   
            }
            echo("\t</instruction>\n");
            break;


//---------------------- Instructions with 1 param.: <label>.            
        case 'CALL':   
        case 'LABEL':   
        case 'JUMP':    
            $counter++;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            if(preg_match("/[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[1])){
                $string = $splitted_file[1];
                $string_name = substr($string, strpos($string, "@")); 
                echo("\t\t<arg1 type=\"label\">".$string_name."</arg1>\n");
                echo("\t</instruction>\n");
            }  
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(23);  // Error 23.   
            }
            break;


//--------------------- Instructions with 2 params.: <var> <type>. 
        case 'READ':    
            $counter++;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            // <var> 
            if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[1])){
                echo("\t\t<arg1 type=\"var\">".$splitted_file[1]."</arg1>\n");
                //<type>: type can be int/string/bool.
                if(preg_match("/(int|string|bool)/", $splitted_file[2])){
                    echo("\t\t<arg2 type=\"type\">".$splitted_file[2]."</arg2>\n");
                    echo("\t</instruction>\n");
                }
                else{
                    print_error("Error! Wrong or unexpected instruction parameters!\n");
                    exit(23);  // Error 23.   
                }
            } 
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(23);  // Error 23.   
            }

            break;    


//------------------------ Instructions with 2 params.: <var> <symb>; symb can be var or const. 
        case 'INT2CHAR':    
        case 'STRLEN':
        case 'TYPE':        
        case 'MOVE':    
            $counter++;
            // var
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[1])){
                echo("\t\t<arg1 type=\"var\">".$splitted_file[1]."</arg1>\n");
                // symb | symb is var or const
                // var 
                if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[2])){
                    echo("\t\t<arg2 type=\"var\">".$splitted_file[2]."</arg2>\n");
                }   
                // <const>       
                elseif(((preg_match("/^int@-?\d+/", $splitted_file[2])))){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"int\">".$string_name."</arg2>\n");
                }
                elseif(preg_match("/^nil@nil/", $splitted_file[2])){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"nil\">".$string_name."</arg2>\n");
                }
                elseif((preg_match("/^string@/", $splitted_file[2]))){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"string\">".$string_name."</arg2>\n");
                }
                elseif(preg_match("/^bool@(true|false)/", $splitted_file[2])){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"bool\">".$string_name."</arg2>\n");
                }
                else{
                    print_error("Error! Wrong or unexpected instruction parameters!\n");
                    exit(23);  // Error 23.   
                }
            }
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(23);  // Error 23.   
            }
            
            break;
  
            
//------------------------ Instructions with 3 params.: <var> <symb1> <symb2>.
        case 'CONCAT':     
        case 'GETCHAR':
        case 'SETCHAR':    
        case 'STRI2INT':    
        case 'ADD':
        case 'SUB':     
        case 'MUL':
        case 'IDIV':    
        case 'LT':
        case 'GT':
        case 'EQ':
        case 'AND':
        case 'OR':
        case 'NOT':            
            $counter++;
            $param = false;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            // <label>
            if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[1])){ 
                echo("\t\t<arg1 type=\"label\">".$splitted_file[1]."</arg1>\n");
                // <symb1>------------------------------------------------------------------
                if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[2])){
                    echo("\t\t<arg2 type=\"var\">".$splitted_file[2]."</arg2>\n");
                    $param = true;
                }         
                elseif(((preg_match("/^int@-?\d+/", $splitted_file[2])))){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"int\">".$string_name."</arg2>\n");
                    $param = true;
                }
                elseif(preg_match("/^nil@nil/", $splitted_file[2])){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"nil\">".$string_name."</arg2>\n");
                    $param = true;
                }
                elseif((preg_match("/^string@/", $splitted_file[2]))){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"string\">".$string_name."</arg2>\n");
                    $param = true;
                }
                elseif(preg_match("/^bool@(true|false)/", $splitted_file[2])){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"bool\">".$string_name."</arg2>\n");
                    $param = true;
                }

                // 3rd param. processing.
                if($param){
                    if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[3])){
                        echo("\t\t<arg3 type=\"var\">".$splitted_file[3]."</arg3>\n");
                    }         
                    elseif(((preg_match("/^int@-?\d+/", $splitted_file[3])))){
                        $string = $splitted_file[3];
                        $string_name = substr($string, strpos($string, "@") + 1); 
                        echo("\t\t<arg3 type=\"int\">".$string_name."</arg3>\n");
                        echo("\t</instruction>\n");
                    }
                    elseif(preg_match("/^nil@nil/", $splitted_file[3])){
                        $string = $splitted_file[3];
                        $string_name = substr($string, strpos($string, "@") + 1); 
                        echo("\t\t<arg3 type=\"nil\">".$string_name."</arg3>\n");
                        echo("\t</instruction>\n");
                    }
                    elseif((preg_match("/^string@/", $splitted_file[3]))){
                        $string = $splitted_file[3];
                        $string_name = substr($string, strpos($string, "@") + 1); 
                        echo("\t\t<arg3 type=\"string\">".$string_name."</arg3>\n");
                        echo("\t</instruction>\n");
                    }
                    elseif(preg_match("/^bool@(true|false)/", $splitted_file[2])){
                        $string = $splitted_file[3];
                        $string_name = substr($string, strpos($string, "@") + 1); 
                        echo("\t\t<arg3 type=\"bool\">".$string_name."</arg3>\n");
                        echo("\t</instruction>\n");
                    }
                    else{
                        print_error("Error! Wrong or unexpected instruction parameters!\n");
                        exit(23);  // Error 23.
                    }    
                }
                else {
                    print_error("Error! Wrong or unexpected instruction parameters!\n");
                    exit(23);  // Error 23.   
                }
            }  
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(23);  // Error 23.   
            }
            break;


//------------------------- Instructions with 3 params.: <label> <symb1> <symb2>.        
        case 'JUMPIFEQ':    
        case 'JUMPIFNEQ':
            $counter++;
            $param = false;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            // <label>
            if(preg_match("/[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[1])){
                $string = $splitted_file[1];
                $string_name = substr($string, strpos($string, "@")); 
                echo("\t\t<arg1 type=\"label\">".$string_name."</arg1>\n");
                // <symb1>------------------------------------------------------------------
                if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[2])){
                    echo("\t\t<arg2 type=\"var\">".$splitted_file[2]."</arg2>\n");
                    $param = true;
                }         
                elseif(((preg_match("/^int@-?\d+/", $splitted_file[2])))){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"int\">".$string_name."</arg2>\n");
                    $param = true;
                }
                elseif(preg_match("/^nil@nil/", $splitted_file[2])){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"nil\">".$string_name."</arg2>\n");
                    $param = true;
                }
                elseif((preg_match("/^string@/", $splitted_file[2]))){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"string\">".$string_name."</arg2>\n");
                    $param = true;
                }
                elseif(preg_match("/^bool@(true|false)/", $splitted_file[2])){
                    $string = $splitted_file[2];
                    $string_name = substr($string, strpos($string, "@") + 1); 
                    echo("\t\t<arg2 type=\"bool\">".$string_name."</arg2>\n");
                    $param = true;
                }

                // 3rd param. processing.
                if($param){
                    if(preg_match("/^(GF|LF|TF)@[a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[3])){
                        echo("\t\t<arg3 type=\"var\">".$splitted_file[3]."</arg3>\n");
                    }         
                    elseif(((preg_match("/^int@-?\d+/", $splitted_file[3])))){
                        $string = $splitted_file[3];
                        $string_name = substr($string, strpos($string, "@") + 1); 
                        echo("\t\t<arg3 type=\"int\">".$string_name."</arg3>\n");
                        echo("\t</instruction>\n");
                    }
                    elseif(preg_match("/^nil@nil/", $splitted_file[3])){
                        $string = $splitted_file[3];
                        $string_name = substr($string, strpos($string, "@") + 1); 
                        echo("\t\t<arg3 type=\"nil\">".$string_name."</arg3>\n");
                        echo("\t</instruction>\n");
                    }
                    elseif((preg_match("/^string@/", $splitted_file[3]))){
                        $string = $splitted_file[3];
                        $string_name = substr($string, strpos($string, "@") + 1); 
                        echo("\t\t<arg3 type=\"string\">".$string_name."</arg3>\n");
                        echo("\t</instruction>\n");
                    }
                    elseif(preg_match("/^bool@(true|false)/", $splitted_file[2])){
                        $string = $splitted_file[3];
                        $string_name = substr($string, strpos($string, "@") + 1); 
                        echo("\t\t<arg3 type=\"bool\">".$string_name."</arg3>\n");
                        echo("\t</instruction>\n");
                    }
                    else{
                        print_error("Error! Wrong or unexpected instruction parameters!\n");
                        exit(23);  // Error 23.
                    }    
                }
                else {
                    print_error("Error! Wrong or unexpected instruction parameters!\n");
                    exit(23);  // Error 23.   
                }
            }  
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(23);  // Error 23.   
            }
            break;    


        default: 
            print_error("Lexical error! Current instruction does not exist!\n");
            exit(bad_or_unknown_opcode);
            break;

    }
}

echo("</program>\n");
exit(success);

?>