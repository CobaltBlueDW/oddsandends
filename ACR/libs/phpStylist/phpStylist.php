<?php
/*****************************************************************************
 * The contents of this file are subject to the RECIPROCAL PUBLIC LICENSE
 * Version 1.1 ("License"); You may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 * http://opensource.org/licenses/rpl.php. Software distributed under the
 * License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND,
 * either express or implied.
 *
 * @author:  Mr. Milk (aka Marcelo Leite)
 * @email:   mrmilk@anysoft.com.br
 * @version: 0.9 beta
 * @date:    2007-07-07
 *
 *****************************************************************************/

processBatch ();


function parseFile ($download = false, $config) {
    
    $code = "";
    $stylist = new phpStylist ();
    if (!isset ($config->file) || $config->file == "") {
        return false;
    }
    
    $code = loadFile ($config->file);
    if (isset ($config->iso8859)) {
        $code = utf8_encode ($code);
    }
    
    if (!empty ($code)) {
        $stylist->options = $config;
        
        if (isset ($config->indent_with_tabs) && $config->indent_with_tabs) {
            $stylist->indent_char = "\t";
        }
        if (!empty ($config->indent_size)) {
            $stylist->indent_size = $config->indent_size;
        }
        if (strpos ($code, '<?') === false) {
            $code = '<?php '.$code.' ?>';
        }
        $formatted = $stylist->formatCode ($code);
    }
    
    return $formatted;
}

function processBatch () {
    global $argv;
    
    if (is_array ($argv)) {
        $options = $argv;
    } elseif (is_array ($_SERVER['argv'])) {
        $options = $_SERVER['argv'];
    } elseif (is_array ($GLOBALS['HTTP_SERVER_VARS']['argv'])) {
        $options = $GLOBALS['HTTP_SERVER_VARS']['argv'];
    }
    
    //get default config settings
    $config = json_decode (file_get_contents (__DIR__.'/working_temp.json'));

    /*foreach($config as $key=>$value){
        $_REQUEST[$key] = $value;
    }*/
    
    //supress notices (because I'm lazy)
    error_reporting (E_ERROR| E_WARNING| E_PARSE);
    
    foreach ($options as $index => $option) {
        if ($option == "--help") {
            echo "phpStylist v0.8 by Mr. Milk\n";
            echo "usage: phpStylist source_file options\n\n";
            echo "Indentation and General Formatting:\n";
            echo "--indent_size n\n";
            echo "--indent_with_tabs\n";
            echo "--keep_redundant_lines\n";
            echo "--space_inside_parentheses\n";
            echo "--space_outside_parentheses\n";
            echo "--space_after_comma\n\n";
            echo "Operators:\n";
            echo "--space_around_assignment\n";
            echo "--align_var_assignment\n";
            echo "--space_around_comparison\n";
            echo "--space_around_arithmetic\n";
            echo "--space_around_logical\n";
            echo "--space_around_colon_question\n\n";
            echo "Functions, Classes and Objects:\n";
            echo "--line_before_function\n";
            echo "--line_before_curly_function\n";
            echo "--line_after_curly_function\n";
            echo "--space_around_obj_operator\n";
            echo "--space_around_double_colon\n\n";
            echo "Control Structures:\n";
            echo "--space_after_if\n";
            echo "--else_along_curly\n";
            echo "--line_before_curly\n";
            echo "--add_missing_braces\n";
            echo "--line_after_break\n";
            echo "--space_inside_for\n";
            echo "--indent_case\n\n";
            echo "Arrays and Concatenation:\n";
            echo "--line_before_array\n";
            echo "--vertical_array\n";
            echo "--align_array_assignment\n";
            echo "--space_around_double_arrow\n";
            echo "--vertical_concat\n";
            echo "--space_around_concat\n\n";
            echo "Comments:\n";
            echo "--line_before_comment_multi\n";
            echo "--line_after_comment_multi\n";
            echo "--line_before_comment\n";
            echo "--line_after_comment\n";
            exit;
        }
        if ($index == 1) {
            $config->file = $option;
        } elseif ($index == 2) {
            $config->outFile = $option;
        } elseif ($option == "--indent_size") {
            $config->indent_size = $options[$index + 1];
        } elseif ($index > 0 && $options[$index - 1] != "indent_size") {
            $config-> {
                substr ($option, 2)
            } = true;
        }
    }
    
    $config->download = 2;
    $str = parseFile (true, $config);
    
    if (isset ($config->outFile)) {
        file_put_contents ($config->outFile, $str);
    } else {
        echo $str;
    }
    
    exit;
}

function loadFile ($filename) {
    $code = "";
    if (filesize ($filename) > 0) {
        $f = fopen ("$filename", "rb");
        $code = fread ($f, filesize ($filename));
        fclose ($f);
    }
    return $code;
}

class phpStylist {
    var $indent_size = 2;
    var $indent_char = " ";
    var $block_size = 3;
    var $_new_line = "\n";
    var $_indent = 0;
    var $_for_idx = 0;
    var $_code = "";
    var $_log = false;
    var $_pointer = 0;
    var $_tokens = 0;
    var $options;
    
    function phpStylist () {
        define ("S_OPEN_CURLY", "{");
        define ("S_CLOSE_CURLY", "}");
        define ("S_OPEN_BRACKET", "[");
        define ("S_CLOSE_BRACKET", "]");
        define ("S_OPEN_PARENTH", "(");
        define ("S_CLOSE_PARENTH", ")");
        define ("S_SEMI_COLON", ";");
        define ("S_COMMA", ",");
        define ("S_CONCAT", ".");
        define ("S_COLON", ":");
        define ("S_QUESTION", "?");
        define ("S_EQUAL", "=");
        define ("S_EXCLAMATION", "!");
        define ("S_IS_GREATER", ">");
        define ("S_IS_SMALLER", "<");
        define ("S_MINUS", "-");
        define ("S_PLUS", "+");
        define ("S_TIMES", "*");
        define ("S_DIVIDE", "/");
        define ("S_MODULUS", "%");
        define ("S_REFERENCE", "&");
        define ("S_QUOTE", '"');
        define ("S_AT", "@");
        define ("S_DOLLAR", "$");
        define ("S_ABSTRACT", "abstract");
        define ("S_INTERFACE", "interface");
        define ("S_FINAL", "final");
        define ("S_PUBLIC", "public");
        define ("S_PRIVATE", "private");
        define ("S_PROTECTED", "protected");
        if (defined ("T_ML_COMMENT")) {
            define ("T_DOC_COMMENT", T_ML_COMMENT);
        } elseif (defined ("T_DOC_COMMENT")) {
            define ("T_ML_COMMENT", T_DOC_COMMENT);
        }
        $this->options = new stdClass ();
    }
    
    function formatCode ($source = '') {
        $in_for = false;
        $in_break = false;
        $in_function = false;
        $in_concat = false;
        $space_after = false;
        $curly_open = false;
        $array_level = 0;
        $arr_parenth = array ();
        $switch_level = 0;
        $if_level = 0;
        $if_pending = 0;
        $else_pending = false;
        $if_parenth = array ();
        $switch_arr = array ();
        $halt_parser = false;
        $after = false;
        $this->_tokens = token_get_all ($source);
        foreach ($this->_tokens as $index => $token) {
            list ($id, $text) = $this->_get_token ($token);
            $this->_pointer = $index;
            if ($halt_parser && $id != S_QUOTE) {
                $this->_append_code ($text, false);
                continue;
            }
            if (substr (phpversion (), 0, 1) == "4" && $id == T_STRING) {
                switch (strtolower (trim ($text))) {
                    case S_ABSTRACT:
                    case S_INTERFACE:
                    case S_FINAL:
                    case S_PUBLIC:
                    case S_PRIVATE:
                    case S_PROTECTED:
                        $id = T_PUBLIC;
                    default:
                }
            }
            switch ($id) {
                case S_OPEN_CURLY:
                    $condition = $in_function ? $this->options->line_before_curly_function : $this->options->line_before_curly;
                    $this->_set_indent ( + 1);
                    $this->_append_code ( (!$condition ? ' ' : $this->_get_crlf_indent (false, - 1)).$text.$this->_get_crlf ($this->options->line_after_curly_function && $in_function && !$this->_is_token_lf ()).$this->_get_crlf_indent ());
                    $in_function = false;
                    break;

                

                case S_CLOSE_CURLY:
                    if ($curly_open) {
                        $curly_open = false;
                        $this->_append_code (trim ($text));
                    } else {
                        if ( ($in_break || $this->_is_token (S_CLOSE_CURLY)) && $switch_level > 0 && $switch_arr["l".$switch_level] > 0 && $switch_arr["s".$switch_level] == $this->_indent - 2) {
                            if ($this->options->indent_case) {
                                $this->_set_indent ( - 1);
                            }
                            $switch_arr["l".$switch_level]--;
                            $switch_arr["c".$switch_level]--;
                        }
                        while ($switch_level > 0 && $switch_arr["l".$switch_level] == 0 && $this->options->indent_case) {
                            unset ($switch_arr["s".$switch_level]);
                            unset ($switch_arr["c".$switch_level]);
                            unset ($switch_arr["l".$switch_level]);
                            $switch_level--;
                            if ($switch_level > 0) {
                                $switch_arr["l".$switch_level]--;
                            }
                            $this->_set_indent ( - 1);
                            $this->_append_code ($this->_get_crlf_indent ().$text.$this->_get_crlf_indent ());
                            $text = '';
                        }
                        if ($text != '') {
                            $this->_set_indent ( - 1);
                            $this->_append_code ($this->_get_crlf_indent ().$text.$this->_get_crlf_indent ());
                        }
                    }
                    break;

                

                case S_SEMI_COLON:
                    if ( ($in_break || $this->_is_token (S_CLOSE_CURLY)) && $switch_level > 0 && $switch_arr["l".$switch_level] > 0 && $switch_arr["s".$switch_level] == $this->_indent - 2) {
                        if ($this->options->indent_case) {
                            $this->_set_indent ( - 1);
                        }
                        $switch_arr["l".$switch_level]--;
                        $switch_arr["c".$switch_level]--;
                    }
                    if ($in_concat) {
                        $this->_set_indent ( - 1);
                        $in_concat = false;
                    }
                    if ($this->options->allow_inline_comments && 
                            $this->_is_token(array(T_COMMENT, T_ML_COMMENT, T_DOC_COMMENT), false, $index, true) &&
                            !$this->_is_token_lf(false, $index)) {
                        $this->_append_code ($text.$this->_get_indent());
                        break;
                    }
                    $this->_append_code ($text.$this->_get_crlf ($this->options->line_after_break && $in_break).$this->_get_crlf_indent ($in_for));
                    while ($if_pending > 0) {
                        $text = $this->options->add_missing_braces ? "}" : "";
                        $this->_set_indent ( - 1);
                        if ($text != "") {
                            $this->_append_code ($this->_get_crlf_indent ().$text.$this->_get_crlf_indent ());
                        } else {
                            $this->_append_code ($this->_get_crlf_indent ());
                        }
                        $if_pending--;
                        if ($this->_is_token (array (T_ELSE, T_ELSEIF))) {
                            break;
                        }
                    }
                    if ($this->_for_idx == 0) {
                        $in_for = false;
                    }
                    $in_break = false;
                    $in_function = false;
                    break;

                

                case S_OPEN_BRACKET:
                case S_CLOSE_BRACKET:
                    $this->_append_code ($text);
                    break;

                

                case S_OPEN_PARENTH:
                    if ($if_level > 0) {
                        $if_parenth["i".$if_level]++;
                    }
                    if ($array_level > 0) {
                        $arr_parenth["i".$array_level]++;
                        if ($this->_is_token (array (T_ARRAY), true) && !$this->_is_token (S_CLOSE_PARENTH)) {
                            $this->_set_indent ( + 1);
                            $this->_append_code ( (!$this->options->line_before_array ? '' : $this->_get_crlf_indent (false, - 1)).$text.$this->_get_crlf_indent ());
                            break;
                        }
                    }
                    $this->_append_code ($this->_get_space ($this->options->space_outside_parentheses || $space_after).$text.$this->_get_space ($this->options->space_inside_parentheses));
                    $space_after = false;
                    break;

                

                case S_CLOSE_PARENTH:
                    if ($array_level > 0) {
                        $arr_parenth["i".$array_level]--;
                        if ($arr_parenth["i".$array_level] == 0) {
                            $comma = substr (trim ($this->_code), - 1) != "," && $this->options->vertical_array ? "," : "";
                            $this->_set_indent ( - 1);
                            $this->_append_code ($comma.$this->_get_crlf_indent ().$text.$this->_get_crlf_indent ());
                            unset ($arr_parenth["i".$array_level]);
                            $array_level--;
                            break;
                        }
                    }
                    $this->_append_code ($this->_get_space ($this->options->space_inside_parentheses).$text.$this->_get_space ($this->options->space_outside_parentheses));
                    if ($if_level > 0) {
                        $if_parenth["i".$if_level]--;
                        if ($if_parenth["i".$if_level] == 0) {
                            if (!$this->_is_token (S_OPEN_CURLY) && !$this->_is_token (S_SEMI_COLON)) {
                                $text = $this->options->add_missing_braces ? "{" : "";
                                $this->_set_indent ( + 1);
                                $this->_append_code ( (!$this->options->line_before_curly || $text == "" ? ' ' : $this->_get_crlf_indent (false, - 1)).$text.$this->_get_crlf_indent ());
                                $if_pending++;
                            }
                            unset ($if_parenth["i".$if_level]);
                            $if_level--;
                        }
                    }
                    break;

                

                case S_COMMA:
                    if ($array_level > 0) {
                        $this->_append_code ($text.$this->_get_crlf_indent ($in_for));
                    } else {
                        $this->_append_code ($text.$this->_get_space ($this->options->space_after_comma));
                        if ($this->_is_token (S_OPEN_PARENTH)) {
                            $space_after = $this->options->space_after_comma;
                        }
                    }
                    break;

                

                case S_CONCAT:
                    $condition = $this->options->space_around_concat;
                    if ($this->_is_token (S_OPEN_PARENTH)) {
                        $space_after = $condition;
                    }
                    if ($this->options->vertical_concat) {
                        if (!$in_concat) {
                            $in_concat = true;
                            $this->_set_indent ( + 1);
                        }
                        $this->_append_code ($this->_get_space ($condition).$text.$this->_get_crlf_indent ());
                    } else {
                        $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    }
                    break;

                

                case T_CONCAT_EQUAL:
                case T_DIV_EQUAL:
                case T_MINUS_EQUAL:
                case T_PLUS_EQUAL:
                case T_MOD_EQUAL:
                case T_MUL_EQUAL:
                case T_AND_EQUAL:
                case T_OR_EQUAL:
                case T_XOR_EQUAL:
                case T_SL_EQUAL:
                case T_SR_EQUAL:
                case S_EQUAL:
                    $condition = $this->options->space_around_assignment;
                    if ($this->_is_token (S_OPEN_PARENTH)) {
                        $space_after = $condition;
                    }
                    $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    break;

                

                case T_IS_EQUAL:
                case S_IS_GREATER:
                case T_IS_GREATER_OR_EQUAL:
                case T_IS_SMALLER_OR_EQUAL:
                case S_IS_SMALLER:
                case T_IS_IDENTICAL:
                case T_IS_NOT_EQUAL:
                case T_IS_NOT_IDENTICAL:
                    $condition = $this->options->space_around_comparison;
                    if ($this->_is_token (S_OPEN_PARENTH)) {
                        $space_after = $condition;
                    }
                    $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    break;

                

                case T_BOOLEAN_AND:
                case T_BOOLEAN_OR:
                case T_LOGICAL_AND:
                case T_LOGICAL_OR:
                case T_LOGICAL_XOR:
                case T_SL:
                case T_SR:
                    $condition = $this->options->space_around_logical;
                    if ($this->_is_token (S_OPEN_PARENTH)) {
                        $space_after = $condition;
                    }
                    $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    break;

                

                case T_DOUBLE_COLON:
                    $condition = $this->options->space_around_double_colon;
                    $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    break;

                

                case S_COLON:
                    if ($switch_level > 0 && $switch_arr["l".$switch_level] > 0 && $switch_arr["c".$switch_level] < $switch_arr["l".$switch_level]) {
                        $switch_arr["c".$switch_level]++;
                        if ($this->options->indent_case) {
                            $this->_set_indent ( + 1);
                        }
                        $this->_append_code ($text.$this->_get_crlf_indent ());
                    } else {
                        $condition = $this->options->space_around_colon_question;
                        if ($this->_is_token (S_OPEN_PARENTH)) {
                            $space_after = $condition;
                        }
                        $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    }
                    if ( ($in_break || $this->_is_token (S_CLOSE_CURLY)) && $switch_level > 0 && $switch_arr["l".$switch_level] > 0) {
                        if ($this->options->indent_case) {
                            $this->_set_indent ( - 1);
                        }
                        $switch_arr["l".$switch_level]--;
                        $switch_arr["c".$switch_level]--;
                    }
                    break;

                

                case S_QUESTION:
                    $condition = $this->options->space_around_colon_question;
                    if ($this->_is_token (S_OPEN_PARENTH)) {
                        $space_after = $condition;
                    }
                    $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    break;

                

                case T_DOUBLE_ARROW:
                    $condition = $this->options->space_around_double_arrow;
                    if ($this->_is_token (S_OPEN_PARENTH)) {
                        $space_after = $condition;
                    }
                    $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    break;

                

                case S_MINUS:
                case S_PLUS:
                case S_TIMES:
                case S_DIVIDE:
                case S_MODULUS:
                    $condition = $this->options->space_around_arithmetic;
                    if ($this->_is_token (S_OPEN_PARENTH)) {
                        $space_after = $condition;
                    }
                    $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    break;

                

                case T_OBJECT_OPERATOR:
                    $condition = $this->options->space_around_obj_operator;
                    $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($condition));
                    break;

                

                case T_FOR:
                    $in_for = true;
                case T_FOREACH:
                case T_WHILE:
                case T_DO:
                case T_IF:
                case T_SWITCH:
                    $space_after = $this->options->space_after_if;
                    $this->_append_code ($text.$this->_get_space ($space_after), false);
                    if ($id == T_SWITCH) {
                        $switch_level++;
                        $switch_arr["s".$switch_level] = $this->_indent;
                        $switch_arr["l".$switch_level] = 0;
                        $switch_arr["c".$switch_level] = 0;
                    }
                    $if_level++;
                    $if_parenth["i".$if_level] = 0;
                    break;

                

                case T_FUNCTION:
                case T_CLASS:
                case T_INTERFACE:
                case T_FINAL:
                case T_ABSTRACT:
                case T_PUBLIC:
                case T_PROTECTED:
                case T_PRIVATE:
                    if (!$in_function) {
                        if ($this->options->line_before_function) {
                            $this->_append_code ($this->_get_crlf ($after || !$this->_is_token (array (T_COMMENT, T_ML_COMMENT, T_DOC_COMMENT), true)).$this->_get_crlf_indent ().$text.$this->_get_space ());
                            $after = false;
                        } else {
                            $this->_append_code ($text.$this->_get_space (), false);
                        }
                        $in_function = true;
                    } else {
                        $this->_append_code ($this->_get_space ().$text.$this->_get_space ());
                    }
                    break;

                

                case T_START_HEREDOC:
                    $this->_append_code ($this->_get_space ($this->options->space_around_assignment).$text);
                    break;

                

                case T_END_HEREDOC:
                    $this->_append_code ($this->_get_crlf ().$text.$this->_get_crlf_indent ());
                    break;

                

                case T_COMMENT:
                case T_ML_COMMENT:
                case T_DOC_COMMENT:
                    if (is_array ($this->_tokens[$index - 1])) {
                        $pad = $this->_tokens[$index - 1][1];
                        $i = strlen ($pad) - 1;
                        $k = "";
                        while (substr ($pad, $i, 1) != "\n" && substr ($pad, $i, 1) != "\r" && $i >= 0) {
                            $k .= substr ($pad, $i--, 1);
                        }
                        $text = preg_replace ("/\r?\n$k/", $this->_get_crlf_indent (), $text);
                    }
                    $after = $id == (T_COMMENT && preg_match ("/^\/\//", $text)) ? $this->options->line_after_comment : $this->options->line_after_comment_multi;
                    $before = $id == (T_COMMENT && preg_match ("/^\/\//", $text)) ? $this->options->line_before_comment : $this->options->line_before_comment_multi;
                    if ($prev = $this->_is_token (S_OPEN_CURLY, true, $index, true)) {
                        $before = $before && !$this->_is_token_lf (true, $prev);
                    }
                    $after = $after && (!$this->_is_token_lf () || !$this->options->keep_redundant_lines);
                    if ($before) {
                        $this->_append_code ($this->_get_crlf (!$this->_is_token (array (T_COMMENT), true)).$this->_get_crlf_indent ().trim ($text).$this->_get_crlf ($after).$this->_get_crlf_indent ());
                    } else {
                        $this->_append_code (trim ($text).$this->_get_crlf ($after).$this->_get_crlf_indent (), false);
                    }
                    break;

                

                case T_DOLLAR_OPEN_CURLY_BRACES:
                case T_CURLY_OPEN:
                    $curly_open = true;
                case T_NUM_STRING:
                case T_BAD_CHARACTER:
                    $this->_append_code (trim ($text));
                    break;

                

                case T_EXTENDS:
                case T_IMPLEMENTS:
                case T_INSTANCEOF:
                case T_AS:
                    $this->_append_code ($this->_get_space ().$text.$this->_get_space ());
                    break;

                

                case S_DOLLAR:
                case S_REFERENCE:
                case T_INC:
                case T_DEC:
                    $this->_append_code (trim ($text), false);
                    break;

                

                case T_WHITESPACE:
                    $redundant = "";
                    if (isset ($this->options->keep_redundant_lines) && $this->options->keep_redundant_lines) {
                        $lines = preg_match_all ("/\r?\n/", $text, $matches);
                        $lines = $lines > 0 ? $lines - 1 : 0;
                        $redundant = $lines > 0 ? str_repeat ($this->_new_line, $lines) : "";
                        $current_indent = $this->_get_indent ();
                        if (substr ($this->_code, strlen ($current_indent) * - 1) == $current_indent && $lines > 0) {
                            $redundant .= $current_indent;
                        }
                    }
                    if ($this->_is_token (array (T_OPEN_TAG), true)) {
                        $this->_append_code ($text, false);
                    } else {
                        $this->_append_code ($redundant.trim ($text), false);
                    }
                    break;

                

                case S_QUOTE:
                    $this->_append_code ($text, false);
                    $halt_parser = !$halt_parser;
                    break;

                

                case T_ARRAY:
                    if ($this->options->vertical_array) {
                        $next = $this->_is_token (array (T_DOUBLE_ARROW), true);
                        $next |= $this->_is_token (S_EQUAL, true);
                        $next |= $array_level > 0;
                        if ($next) {
                            $next = $this->_is_token (S_OPEN_PARENTH, false, $index, true);
                            if ($next) {
                                $next = !$this->_is_token (S_CLOSE_PARENTH, false, $next);
                            }
                        }
                        if ($next) {
                            $array_level++;
                            $arr_parenth["i".$array_level] = 0;
                        }
                    }
                case T_STRING:
                case T_CONSTANT_ENCAPSED_STRING:
                case T_ENCAPSED_AND_WHITESPACE:
                case T_VARIABLE:
                case T_CHARACTER:
                case T_STRING_VARNAME:
                case S_AT:
                case S_EXCLAMATION:
                case T_OPEN_TAG:
                case T_OPEN_TAG_WITH_ECHO:
                    $this->_append_code ($text, false);
                    break;

                

                case T_CLOSE_TAG:
                    $this->_append_code ($text, !$this->_is_token_lf (true));
                    break;

                

                case T_CASE:
                case T_DEFAULT:
                    if ($switch_arr["l".$switch_level] > 0 && $this->options->indent_case) {
                        $switch_arr["c".$switch_level]--;
                        $this->_set_indent ( - 1);
                        $this->_append_code ($this->_get_crlf_indent ().$text.$this->_get_space ());
                    } else {
                        $switch_arr["l".$switch_level]++;
                        $this->_append_code ($text.$this->_get_space (), false);
                    }
                    break;

                

                case T_INLINE_HTML:
                    $this->_append_code ($text, false);
                    break;

                

                case T_BREAK:
                case T_CONTINUE:
                    $in_break = true;
                case T_VAR:
                case T_GLOBAL:
                case T_STATIC:
                case T_CONST:
                case T_ECHO:
                case T_PRINT:
                case T_INCLUDE:
                case T_INCLUDE_ONCE:
                case T_REQUIRE:
                case T_REQUIRE_ONCE:
                case T_DECLARE:
                case T_EMPTY:
                case T_ISSET:
                case T_UNSET:
                case T_DNUMBER:
                case T_LNUMBER:
                case T_RETURN:
                case T_EVAL:
                case T_EXIT:
                case T_LIST:
                case T_CLONE:
                case T_NEW:
                case T_FUNC_C:
                case T_CLASS_C:
                case T_FILE:
                case T_LINE:
                    $this->_append_code ($text.$this->_get_space (), false);
                    break;

                

                case T_ELSEIF:
                    $space_after = $this->options->space_after_if;
                    $added_braces = $this->_is_token (S_SEMI_COLON, true) && $this->options->add_missing_braces;
                    $condition = $this->options->else_along_curly && ($this->_is_token (S_CLOSE_CURLY, true) || $added_braces);
                    $this->_append_code ($this->_get_space ($condition).$text.$this->_get_space ($space_after), $condition);
                    $if_level++;
                    $if_parenth["i".$if_level] = 0;
                    break;

                

                case T_ELSE:
                    $added_braces = $this->_is_token (S_SEMI_COLON, true) && $this->options->add_missing_braces;
                    $condition = $this->options->else_along_curly && ($this->_is_token (S_CLOSE_CURLY, true) || $added_braces);
                    $this->_append_code ($this->_get_space ($condition).$text, $condition);
                    if (!$this->_is_token (S_OPEN_CURLY) && !$this->_is_token (array (T_IF))) {
                        $text = $this->options->add_missing_braces ? "{" : "";
                        $this->_set_indent ( + 1);
                        $this->_append_code ( (!$this->options->line_before_curly || $text == "" ? ' ' : $this->_get_crlf_indent (false, - 1)).$text.$this->_get_crlf_indent ());
                        $if_pending++;
                    }
                    break;

                

                default:
                    $this->_append_code ($text.' ', false);
                    break;
            }
        }
        return $this->_align_operators ();
    }
    
    function _get_token ($token) {
        if (is_string ($token)) {
            return array ($token, $token);
        } else {
            return $token;
        }
    }
    
    function _append_code ($code = "", $trim = true) {
        if ($trim) {
            $this->_code = rtrim ($this->_code).$code;
        } else {
            $this->_code .= $code;
        }
    }
    
    function _get_crlf_indent ($in_for = false, $increment = 0) {
        if ($in_for) {
            $this->_for_idx++;
            if ($this->_for_idx > 2) {
                $this->_for_idx = 0;
            }
        }
        if ($this->_for_idx == 0 || !$in_for) {
            return $this->_get_crlf ().$this->_get_indent ($increment);
        } else {
            return $this->_get_space ($this->options->space_inside_for);
        }
    }
    
    function _get_crlf ($true = true) {
        return $true ? $this->_new_line : "";
    }
    
    function _get_space ($true = true) {
        return $true ? " " : "";
    }
    
    function _get_indent ($increment = 0) {
        return str_repeat ($this->indent_char, ($this->_indent + $increment) * $this->indent_size);
    }
    
    function _set_indent ($increment) {
        $this->_indent += $increment;
        if ($this->_indent < 0) {
            $this->_indent = 0;
        }
    }
    
    function _is_token ($token, $prev = false, $i = 99999, $idx = false) {
        if ($i == 99999) {
            $i = $this->_pointer;
        }
        if ($prev) {
            while (--$i >= 0 && is_array ($this->_tokens[$i]) && $this->_tokens[$i][0] == T_WHITESPACE);
        } else {
            while (++$i < count ($this->_tokens) - 1 && is_array ($this->_tokens[$i]) && $this->_tokens[$i][0] == T_WHITESPACE);
        }
        if (is_string ($this->_tokens[$i]) && $this->_tokens[$i] == $token) {
            return $idx ? $i : true;
        } elseif (is_array ($token) && is_array ($this->_tokens[$i])) {
            if (in_array ($this->_tokens[$i][0], $token)) {
                return $idx ? $i : true;
            } elseif ($prev && $this->_tokens[$i][0] == T_OPEN_TAG) {
                return $idx ? $i : true;
            }
        }
        return false;
    }
    
    function _is_token_lf ($prev = false, $i = 99999) {
        if ($i == 99999) {
            $i = $this->_pointer;
        }
        if ($prev) {
            $count = 0;
            while (--$i >= 0 && is_array ($this->_tokens[$i]) && $this->_tokens[$i][0] == T_WHITESPACE && strpos ($this->_tokens[$i][1], "\n") === false);
        } else {
            $count = 1;
            while (++$i < count ($this->_tokens) && is_array ($this->_tokens[$i]) && $this->_tokens[$i][0] == T_WHITESPACE && strpos ($this->_tokens[$i][1], "\n") === false);
        }
        if (is_array ($this->_tokens[$i]) && preg_match_all ("/\r?\n/", $this->_tokens[$i][1], $matches) > $count) {
            return true;
        }
        return false;
    }
    
    function _pad_operators ($found) {
        global $quotes;
        $pad_size = 0;
        $result = "";
        $source = explode ($this->_new_line, $found[0]);
        $position = array ();
        array_pop ($source);
        foreach ($source as $k => $line) {
            if (preg_match ("/'quote[0-9]+'/", $line)) {
                preg_match_all ("/'quote([0-9]+)'/", $line, $holders);
                for ($i = 0; $i < count ($holders[1]); $i++) {
                    $line = preg_replace ("/".$holders[0][$i]."/", str_repeat (" ", strlen ($quotes[0][$holders[1][$i]])), $line);
                }
            }
            if (strpos ($line, "=") > $pad_size) {
                $pad_size = strpos ($line, "=");
            }
            $position[$k] = strpos ($line, "=");
        }
        foreach ($source as $k => $line) {
            $padding = str_repeat (" ", $pad_size - $position[$k]);
            $padded = preg_replace ("/^([^=]+?)([\.\+\*\/\-\%]?=)(.*)$/", "\\1{$padding}\\2\\3".$this->_new_line, $line);
            $result .= $padded;
        }
        return $result;
    }
    
    function _parse_block ($blocks) {
        global $quotes;
        $pad_chars = "";
        $holders = array ();
        if ($this->options->align_array_assignment) {
            $pad_chars .= ",";
        }
        if ($this->options->align_var_assignment) {
            $pad_chars .= ";";
        }
        $php_code = $blocks[0];
        preg_match_all ("/\/\*.*?\*\/|\/\/[^\n]*|#[^\n]|([\"'])[^\\\\]*?(?:\\\\.[^\\\\]*?)*?\\1/s", $php_code, $quotes);
        $quotes[0] = array_values (array_unique ($quotes[0]));
        for ($i = 0; $i < count ($quotes[0]); $i++) {
            $patterns[] = "/".preg_quote ($quotes[0][$i], '/')."/";
            $holders[] = "'quote$i'";
            $quotes[0][$i] = str_replace ('\\\\', '\\\\\\\\', $quotes[0][$i]);
        }
        if (count ($holders) > 0) {
            $php_code = preg_replace ($patterns, $holders, $php_code);
        }
        $php_code = preg_replace_callback ("/(?:.+=.+[".$pad_chars."]\r?\n){".$this->block_size.",}/", array ($this, "_pad_operators"), $php_code);
        for ($i = count ($holders) - 1; $i >= 0; $i--) {
            $holders[$i] = "/".$holders[$i]."/";
        }
        if (count ($holders) > 0) {
            $php_code = preg_replace ($holders, $quotes[0], $php_code);
        }
        return $php_code;
    }
    
    function _align_operators () {
        if ($this->options->align_array_assignment || $this->options->align_var_assignment) {
            return preg_replace_callback ("/<\?.*?\?".">/s", array ($this, "_parse_block"), $this->_code);
        } else {
            return $this->_code;
        }
    }
}
?>
