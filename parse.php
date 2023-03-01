<?php
/**
 * @author Anastasiia Berezovska, xberez04@stud.fit.vutbr.cz
 * Project 1 from IPP university class - parser in php.
 */

require('error.php'); 

ini_set('display_errors', 'stderr');

$counter = 0;        // Instruction counter.
$is_header = false;

//------------------------------ replaces sequence of whitespaces with one whitespace.
function lessSpaces($file){
    return preg_replace('/\s+/', ' ', $file);
}

//------------------------------- replaces special symbols in string name in XML code. (<, >, &).
function replaceSymbols($substring, $i){
    for ($j = 0; $j < strlen($substring); $j++) {
        $letter = $substring[$j];
        if(preg_match("/&/", $substring)){
            $new_string = str_replace("&", "&amp;", $substring);
        }
        elseif(preg_match("/</", $substring)){
            $new_string = str_replace("<", "&lt;", $substring);
        }
        elseif(preg_match("/>/", $substring)){
            $new_string = str_replace(">", "&gt;", $substring);
        }
        else {
            $new_string = $substring;
        }
    }

    echo("\t\t<arg".$i." type=\"string\">".$new_string."</arg".$i.">\n");
    return true;
}

//--------------------------------------- CHECKS <var>.
// @params - $splitted_file - current line, int $i - index (order of <var>, eg. INSTRUCTION <var> -> i = 1).
function checkVar($splitted_file, int $i){
    if(preg_match("/^(GF|LF|TF)@[_a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[$i])){
        if(preg_match("/&/", $splitted_file[$i])){
            $new_string = str_replace("&", "&amp;", $splitted_file[$i]);
            echo("\t\t<arg".$i." type=\"var\">".$new_string."</arg".$i.">\n");
        }
        elseif(preg_match("/</", $splitted_file[$i])){
            $new_string = str_replace("<", "&lt;", $splitted_file[$i]);
            echo("\t\t<arg".$i." type=\"var\">".$new_string."</arg".$i.">\n");
        }
        elseif(preg_match("/>/", $splitted_file[$i])){
            $new_string = str_replace(">", "&gt;", $splitted_file[$i]);
            echo("\t\t<arg".$i." type=\"var\">".$new_string."</arg".$i.">\n");
        }
        else{
            echo("\t\t<arg".$i." type=\"var\">".$splitted_file[$i]."</arg1>\n");
        }
    }
    else{
        print_error("Error! Wrong instruction parameter!\n");
        exit(lexical_or_syntax_error);  // Error 23.
    }
   return true;
}

//--------------------------------------- ------CHECKS <type>; <type> can be int|bool|string.
// @params - $splitted_file - current line, int $i - index (order of <type>, eg. INSTRUCTION <type> -> i = 1).
function checkType($splitted_file, $i){
    if(preg_match("/(int|string|bool)$/", $splitted_file[$i])){
        echo("\t\t<arg".$i." type=\"type\">".$splitted_file[$i]."</arg".$i.">\n");
    }
    else{
        print_error("Error! Wrong or unexpected instruction parameters!\n");
        exit(lexical_or_syntax_error);  // Error 23.   
    }
    return true;
}

//--------------------------------------- CHECKS <label>.
// @params - $splitted_file - current line, int $i - index (order of <label>, eg. INSTRUCTION <label> -> i = 1).
function checkLabel($splitted_file, $i){
    if(preg_match("/^[_a-zA-Z-$&%*!?][a-zA-Z0-9-$&%!?]*$/", $splitted_file[$i])){
        $string = $splitted_file[$i];
        $string_name = substr($string, strpos($string, "@")); 
        echo("\t\t<arg".$i." type=\"label\">".$string_name."</arg".$i.">\n");
    }  
    else{
        print_error("Error! Wrong or unexpected instruction parameters!\n");
        exit(lexical_or_syntax_error);  // Error 23.   
    }
    return true;
}

//--------------------------------------- CHECKS <symb>; <symb> can be const or var.
// @params - $splitted_file - current line, int $i - index (order of <symb>, eg. INSTRUCTION <symb> -> i = 1).
function checkSymb($splitted_file, int $i){
    // <var>
    if(preg_match("/^(GF|LF|TF)@[_a-zA-Z-$&%*!?][a-zA-Z-$&%*!?]*/", $splitted_file[$i])){
        if(preg_match("/&/", $splitted_file[$i])){
            $new_string = str_replace("&", "&amp;", $splitted_file[$i]);
            echo("\t\t<arg".$i." type=\"var\">".$new_string."</arg".$i.">\n");
        }
        elseif(preg_match("/</", $splitted_file[$i])){
            $new_string = str_replace("<", "&lt;", $splitted_file[$i]);
            echo("\t\t<arg".$i." type=\"var\">".$new_string."</arg".$i.">\n");
        }
        elseif(preg_match("/>/", $splitted_file[$i])){
            $new_string = str_replace(">", "&gt;", $splitted_file[$i]);
            echo("\t\t<arg".$i." type=\"var\">".$new_string."</arg".$i.">\n");
        }
        else{
            echo("\t\t<arg".$i." type=\"var\">".$splitted_file[$i]."</arg".$i.">\n");
        }
    }   
    //<const>       
    elseif(((preg_match("/^int@[+|-]?\d+/", $splitted_file[$i])))){
        $string = $splitted_file[$i];
        $string_name = substr($string, strpos($string, "@") + 1); 
        echo("\t\t<arg".$i." type=\"int\">".$string_name."</arg".$i.">\n");
    }
    elseif(preg_match("/\bnil@nil\b/", $splitted_file[$i])){
        $string = $splitted_file[$i];
        $string_name = substr($string, strpos($string, "@") + 1); 
        echo("\t\t<arg".$i." type=\"nil\">".$string_name."</arg".$i.">\n");
    }
    elseif((preg_match("/^string@/", $splitted_file[$i]))){
        $pos = strpos($splitted_file[$i], "#");
        if($pos ==! false){
            $substring = substr($splitted_file[$i], 0, $pos);
            $string = $substring;
            $substring = substr($string, strpos($string, "@") + 1);

            if(preg_match("/^(?:\\\\[0-9]{3}|[^\\\\])*$/", $substring)){
                if(!replaceSymbols($substring, $i)){
                    print_error("Error! Wrong or unexpected instruction parameters!\n");
                    exit(lexical_or_syntax_error);  // Error 23. 
                }
            }
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(lexical_or_syntax_error);  // Error 23.  
                }
            }
        
        else{
            $string = $splitted_file[$i];
            $string_name = substr($string, strpos($string, "@") + 1); 
        if(preg_match("/^(?:\\\\[0-9]{3}|[^\\\\])*$/", $string_name)){
                // echo("\t\t<arg".$i." type=\"string\">".$string_name."</arg".$i.">\n");
               if(!replaceSymbols($string_name, $i)){
                    print_error("Error! Wrong or unexpected instruction parameters!\n");
                    exit(lexical_or_syntax_error);  // Error 23. 
               }
               
            }
        else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(lexical_or_syntax_error);  // Error 23.   
                }
            }
        }
    elseif(preg_match("/^bool@(true|false)/", $splitted_file[$i])){
        $string = $splitted_file[$i];
        $string_name = substr($string, strpos($string, "@") + 1); 
        echo("\t\t<arg".$i." type=\"bool\">".$string_name."</arg".$i.">\n");
    }
    else{
        print_error("Error! Wrong or unexpected instruction parameters!\n");
        exit(lexical_or_syntax_error);  // Error 23.   
    }
    return true;
}

//--------------------------------------- CHECKS an empty argument (used to check number of args).
// @params - $splitted_file - current line, int $i - index (order of arg, eg. INSTRUCTION arg -> i = 1).
function checkEmptyArg($splitted_file, int $i){
    if(!(empty($splitted_file[$i]))){
        print_error("Error! Wrong or unexpected instruction parameters!\n");
        exit(lexical_or_syntax_error);  // Error 23.
    }

    return true;
}

//------------------------------------processing help information.
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

//------------------------------------------------------------ beginning of the program.
echo("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
while(true){
    if(($file = fgets(STDIN)) == false){        // if eof -> end.
        break;
    }
    if(preg_match("/^\s*$/", $file) or (preg_match("/^#/", $file))){       // skips an empty line and comments in the beginning of the line.
        continue;
    }

    $file = lessSpaces($file);
    $split_comment = explode('#', trim($file, "\n")); //odriznem si comentare
    $splitted_file = explode(' ', trim($split_comment[0], " "));

    $without_whitespaces =  trim($file, " ");
    //$splitted_file = explode(' ', lessSpaces($file));   // exploded line into words. to access them use index ($splitted_file[i]).

    //---------------------------------------------- header processing.
    if(!$is_header){

        if(preg_match("/^\.IPPcode23(?:\s*#.+)?$/", $without_whitespaces)){
            $is_header = true;
            echo("<program language=\"IPPcode23\">"."\n");
            continue;
           }
        else{
            print_error("Error! Wrong or missing file header!" . "\n" );
            exit(missing_or_bad_header);     // Error 21.       
        }
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
                exit(lexical_or_syntax_error);  // Error 23.
            }

            break;

                
        //------------------------------ Instructions with 1 param.: <var>.    
        case 'DEFVAR':  
        case 'POPS':    
            $counter++;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            checkVar($splitted_file, 1);

            checkEmptyArg($splitted_file, 2);

            echo("\t</instruction>\n");
            break;


        //----------------------------- Instructions with 1 param.: <symb>; <symb> can be var or const.
        case 'PUSHS':   
        case 'WRITE':   
        case 'EXIT':    
        case 'DPRINT':  
            $counter++;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            if(checkSymb($splitted_file, 1)){
                echo("\t</instruction>\n");
            }
            checkEmptyArg($splitted_file, 2);
            break;
           

        //---------------------------------------- Instructions with 1 param.: <label>.            
        case 'CALL':   
        case 'LABEL':   
        case 'JUMP':    
            $counter++;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            checkLabel($splitted_file, 1);

            checkEmptyArg($splitted_file, 2);

            echo("\t</instruction>\n");
            break;


        //------------------------------------------- Instructions with 2 params.: <var> <type>. 
        case 'READ':    
            $counter++;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");

            if(checkVar($splitted_file, 1)){
                checkType($splitted_file, 2);
            }
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(lexical_or_syntax_error);  // Error 23.   
            }

            checkEmptyArg($splitted_file, 3);

            echo("\t</instruction>\n");
            break;    


        //------------------------------- Instructions with 2 params.: <var> <symb>; symb can be var or const. 
        case 'INT2CHAR':    
        case 'STRLEN':
        case 'TYPE':        
        case 'MOVE':    
            $counter++;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            if(checkVar($splitted_file, 1)){
                checkSymb($splitted_file, 2);
            }
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(lexical_or_syntax_error);  // Error 23.   
            }
            
            checkEmptyArg($splitted_file, 3);

            echo("\t</instruction>\n");
            break;
  

        //---------------------------------------------------------------------------------------------------            
        case 'NOT':   
            $counter++;
            $param = false;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            // <var>
            if(checkVar($splitted_file, 1)){
                checkSymb($splitted_file, 2);
            }    
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(lexical_or_syntax_error);  // Error 23.   
            }

            checkEmptyArg($splitted_file, 3);

            echo("\t</instruction>\n");
            break;
            
        //--------------------------------------- Instructions with 3 params.: <var> <symb1> <symb2>.
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
            $counter++;
            $param = false;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            if(checkVar($splitted_file, 1)){    
                if(checkSymb($splitted_file, 2)){
                    checkSymb($splitted_file, 3);
                }          
            }  
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(lexical_or_syntax_error);  // Error 23.   
            }

            checkEmptyArg($splitted_file, 4);

            echo("\t</instruction>\n");
            break;


        //----------------------------------- Instructions with 3 params.: <label> <symb1> <symb2>.        
        case 'JUMPIFEQ':    
        case 'JUMPIFNEQ':
            $counter++;
            $param = false;
            echo("\t<instruction order=\"$counter\" opcode=\"".strtoupper($splitted_file[0])."\">\n");
            // <label>
            if(checkLabel($splitted_file, 1)){
                if(checkSymb($splitted_file, 2)){
                    checkSymb($splitted_file, 3);
                }
            }  
            else{
                print_error("Error! Wrong or unexpected instruction parameters!\n");
                exit(lexical_or_syntax_error);  // Error 23.   
            }

            checkEmptyArg($splitted_file, 4);

            echo("\t</instruction>\n");
            break;    
        

        default: 
            print_error("Lexical error! Current instruction does not exist!\n");
            exit(bad_or_unknown_opcode);    // Error 22.
            break;

    }
}

echo("</program>\n");
exit(success);

?>