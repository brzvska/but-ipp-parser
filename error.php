<?php
/**
 * @author Anastasiia Berezovska, xberez04@stud.fit.vutbr.cz
 */

const success = 0;
const missing_or_bad_param = 10;
const open_file_error = 11;
const open_output_file_error = 12;
const missing_or_bad_header = 21;
const bad_or_unknown_opcode = 22;
const lexical_or_syntax_error = 23;
const internal_error = 99;


function print_error($message){
    fwrite(STDERR, $message);
}

?>