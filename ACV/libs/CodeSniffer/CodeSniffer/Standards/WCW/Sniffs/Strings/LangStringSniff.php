<?php
/**
 * Sniff for detecting strings that should probably use lang strings and aren't.
 *
 * @package    local_codechecker
 * @copyright  2011 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class WCW_Sniffs_Strings_LangStringSniff implements PHP_CodeSniffer_Sniff {
    public function __construct() {
        $this->supportedTokenizers = array('PHP');
    }
    
    public function register() {
        return PHP_CodeSniffer_Tokens::$stringTokens;
    }
    
    public function process(PHP_CodeSniffer_File $file, $stackptr) {
        $tokens = $file->getTokens();
        
        for ($i = $stackptr; $i < $file->numTokens; $i++) {
            if ($tokens[$stackptr]['code'] !== $tokens[$i]['code']) {
                break;
            }
            
            $content = trim($tokens[$i]['content']);
        }
        
        $content = trim($content);
        
        //check content for SQL very simple check to stop suggetions for most SQL
        if (preg_match("/SELECT|INSERT|UPDATE|DELETE|CREATE|DROP|ALTER|WHERE|LIMIT|GROUP|ORDER|FROM|JOIN|ASC|DESC/" , $content)) return;
        
        //check content for HTML
        if(strlen(strip_tags($content))+6 < strlen($content)) return;
        
        //check content for URI vars / XML attribs / etc.
        if (preg_match("/=\s*[\"']/" , $content)) return;
        
        //check content for lack of spaces
        if(strpos($content, ' ') === false) return;
        
        //check content for length
        if(strlen($content) < 12) return;
        
        $error = 'Consider putting this string in a language file.';
        $file->addWarning($error, $stackptr, 'Found');
    }
}
