<?php
/* SVN FILE: $Id: HamlParser.php 117 2010-09-21 09:41:58Z chris.l.yates@gmail.com $ */
/**
 * HamlParser class file.
 * HamlParser allows you to write view files in
 * {@link http://haml-lang.com/ Haml}.
 * 
 * Please see the {@link http://haml-lang.com/docs/yardoc/file.Haml_REFERENCE.html#plain_text Haml documentation} for the syntax.
 * 
 * Credits:
 * This is a port of Haml to PHP. All the genius comes from the people that
 * invented and develop Haml; in particular:
 * + {@link http://hamptoncatlin.com/ Hampton Catlin},
 * + {@link http://nex-3.com/ Nathan Weizenbaum},
 * + {@link http://chriseppstein.github.com/ Chris Eppstein}
 * 
 * The bugs are mine. Please report any found at {@link http://code.google.com/p/phamlp/issues/list}
 * 
 * Notes
 * <ul>
 * <li>Debug (addition)<ul>
 * <li>Source debug - adds comments to the output showing each source line above
 * the result - ?#s+ to turn on, ?#s- to turn off, ?#s! to toggle</li>
 * <li>Output debug - shows the output directly in the browser - ?#o+ to turn on, ?#o- to turn off, ?#o! to toggle</li>
 * <li>Control both at once - ?#so+ to turn on, ?#so- to turn off, ?#so! to toggle</li>
 * <li>Ugly mode can be controlled by the template</li>
 * <liUugly mode strips comments in the output by default</li>
 * <li>Ugly mode is turned off when in debug</li></ul></li>
 * <li>"-" command (notes)<ul>
 * <li>PHP does not require ending ";"</li>
 * <li>PHP control blocks are automatically bracketed</li>
 * <li>Switch Case statements do not end with ":"
 * <li>do-while control blocks are written as "do (expression)"</li></ul></li>
 * </ul>
 * Comes with filters that run "out of the box":
 * + <b>plain</b>: useful for large chunks of text to ensure Haml doesn't do anything.
 * + <b>escaped</b>: like plain but the output is (x)html escaped.
 * + <b>preserve</b>: like plain but preserves the whitespace.
 * + <b>cdata</b>: wraps the content in CDATA tags.
 * + <b>javascript</b>: wraps the content in <script> and CDATA tags. Useful for adding inline JavaScript.
 * + <b>css</b>: wraps the content in <style> and CDATA tags. Useful for adding inline CSS.
 * + <b>php</b>: wraps the content in <?php tags. The content is PHP code.
 * There are two filters that require external classes to work. See {@link http://code.google.com/p/phamlp/wiki/PredefinedFilters PredefinedFilters on the PHamlP wiki} for details of how to use them.
 * + <b>markdown</b>: Parses the filtered text with Markdown.
 * + <b>textile</b>: Parses the filtered text with Textile.
 * PHP can be used in all the filters (except php) by wrapping expressions in #().
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Haml
 */

require_once('tree/HamlNode.php');
require_once('HamlHelpers.php');
require_once('HamlException.php');

/**
 * HamlParser class.
 * Parses {@link http://haml-lang.com/ Haml} view files.
 * @package			PHamlP
 * @subpackage	Haml
 */
class HamlParser {
	/**#@+
	 * Debug modes
	 */
	const DEBUG_NONE = 0;
	const DEBUG_SHOW_SOURCE = 1;
	const DEBUG_SHOW_OUTPUT = 2;
	const DEBUG_SHOW_ALL = 3;
	/**#@-*/

	/**#@+
	 * Regexes used to parse the document
	 */
	const REGEX_HAML = '/(?m)^([ \x09]*)((?::(\w*))?(?:%([\w:-]*))?(?:\.((?:(?:[-_:a-zA-Z]|#\{.+?\})+(?:[-:\w]|#\{.+?\})*(?:\.?))*))?(?:#((?:[_:a-zA-Z]|#\{.+?\})+(?:[-:\w]|#\{.+?\})*))?(?:\[(.+)\])?(?:(\()((?:(?:html_attrs\(.*?\)|data[\t ]*=[\t ]*\{.+?\}|(?:[_:a-zA-Z]+[-:\w]*)[\t ]*=[\t ]*.+)[\t ]*)+\)))?(?:(\{)((?::(?:html_attrs\(.*?\)|data[\t ]*=>[\t ]*\{.+?\}|(?:[_:a-zA-Z]+[-:\w]*)[\t ]*=>?[\t ]*.+)(?:,?[\t ]*)?)+\}))?(\|?>?\|?<?) *((?:\?#)|!!!|\/\/|\/|-#|!=|&=|!|&|=|-|~|\\\\\\\\)? *(.*?)(?:\s(\|)?)?)$/'; // Haml line
	const REGEX_ATTRIBUTES = '/:?(?:(data)\s*=>?\s*([({].*?[})]))|(\w+(?:[-:]\w*)*)\s*=>?\s*(?(?=\[)(?:\[(.+?)\])|(?(?=([\'"]))(?:[\'"](.*?)\5)|([^\s,]+)))/';
	const REGEX_ATTRIBUTE_FUNCTION = '/^\$?[_a-zA-Z]\w*(?(?=->)(->[_a-zA-Z]\w*)+|(::[_a-zA-Z]\w*)?)\(.+\)$/'; // Matches functions and instantiated and static object methods
	const REGEX_WHITESPACE_REMOVAL = '/(.*?)\s+$/s';
	const REGEX_WHITESPACE_REMOVAL_DEBUG = '%(.*?)(?:<br />\s)$%s'; // whitespace control when showing output
	//const REGEX_CODE_INTERPOLATION = '/(?:(?<!\\\\)#{(.+?(?:\(.*?\).*?)*)})/';
	/**#@-*/
	const MATCH_INTERPOLATION = '/(?<!\\\\)#\{(.*?)\}/';
	const INTERPOLATE = '<?php echo \1; ?>';
	const HTML_ATTRS = '/html_attrs\(\s*((?(?=\')(?:.*?)\'|(?:.*?)"))(?:\s*,\s*(.*?))?\)/';


	/**#@+
	 * Haml regex match positions
	 */
	const HAML_HAML									=  0;
	const HAML_INDENT								=  1;
	const HAML_SOURCE								=  2;
	const HAML_FILTER								=  3;
	const HAML_TAG									=  4;
	const HAML_CLASS								=  5;
	const HAML_ID										=  6;
	const HAML_OBJECT_REFERENCE			=  7;
	const HAML_OPEN_XML_ATTRIBUTES	=  8;
	const HAML_XML_ATTRIBUTES 			=  9;
	const HAML_OPEN_RUBY_ATTRIBUTES = 10;
	const HAML_RUBY_ATTRIBUTES			= 11;
	const HAML_WHITESPACE_REMOVAL		= 12;
	const HAML_TOKEN								= 13;
	const HAML_CONTENT							= 14;
	const HAML_MULTILINE						= 15;
	/**#@-*/

	/**#@+
	 * Haml tokens
	 */
	const DOCTYPE = '!!!';
	const HAML_COMMENT = '!(-#|//)!';
	const XML_COMMENT = '/';
	const SELF_CLOSE_TAG = '/';
	const ESCAPE_XML = '&=';
	const UNESCAPE_XML = '!=';
	const INSERT_CODE = '=';
	const INSERT_CODE_PRESERVE_WHITESPACE = '~';
	const RUN_CODE = '-';
	const INNER_WHITESPACE_REMOVAL = '<';
	const OUTER_WHITESPACE_REMOVAL = '>';
	const BLOCK_LEFT_OUTER_WHITESPACE_REMOVAL = '|>';
	const BLOCK_RIGHT_OUTER_WHITESPACE_REMOVAL = '>|';
	/**#@-*/
	
	const MULTILINE= ' |';

	/**#@+
	 * Attribute tokens
	 */
	const OPEN_XML_ATTRIBUTES = '(';
	const CLOSE_XML_ATTRIBUTES = ')';
	const OPEN_RUBY_ATTRIBUTES = '{';
	const CLOSE_RUBY_ATTRIBUTES = '}';
	/**#@-*/

	/**#@+
	 * Directives
	 */
	const DIRECTIVE = '?#';
	const SOURCE_DEBUG = 's';
	const OUTPUT_DEBUG = 'o';
	/**#@-*/

	const IS_XML_PROLOG = 'XML';
	const XML_PROLOG = "<?php echo \"<?xml version='1.0' encoding='{encoding}' ?>\n\"; ?>";
	const DEFAULT_XML_ENCODING = 'utf-8';
	const XML_ENCODING = '{encoding}';

	/**
	 * @var string Doctype format. Determines how the Haml Doctype declaration is 
	 * rendered.
	 * @see doctypes
	 */
	private $format = 'xhtml';
	/**
	 * @var string Custom Doctype. If not null and the Doctype declaration in the
	 * Haml Document is not a built in Doctype this will be used as the Doctype.
	 * This allows Haml to be used for non-(X)HTML documents that are XML compliant.
	 * @see doctypes
	 * @see emptyTags
	 * @see inlineTags
	 * @see minimizedAttributes
	 */
	 private $doctype;
	/**
	 * @var boolean whether or not to escape X(HT)ML-sensitive characters in script.
	 * If this is true, = behaves like &=; otherwise, it behaves like !=.
	 * Note that if this is set, != should be used for yielding to subtemplates
	 * and rendering partials. Defaults to false.
	 */
	private $escapeHtml = false;
  /**
   * @var boolean Whether or not attribute hashes and scripts designated by
   * = or ~ should be evaluated. If true, the scripts are rendered as empty strings.
   * Defaults to false.
   */
	private $suppressEval = false;
  /**
	 * @var string The character that should wrap element attributes. Characters
	 * of this type within attributes will be escaped (e.g. by replacing them with
	 * &apos;) if the character is an apostrophe or a quotation mark.
	 * Defaults to " (an quotation mark).
	 */
	private $attrWrapper = '"';
	/**
	 * @var array available output styles:
	 * nested: output is nested according to the indent level in the source
	 * expanded: block tags have their own lines as does content which is indented
	 * compact: block tags and their content go on one line
	 * compressed: all unneccessary whitepaces is removed. If ugly is true this style is used.
	 */
	private $styles = array('nested', 'expanded', 'compact', 'compressed');
	/**
	 * @var string output style. Note: ugly must be false to allow style.
	 */
	private $style = 'nested';
  /**
	 * @var boolean if true no attempt is made to properly indent or format
	 * the output. Reduces size of output file but is not very readable;
	 * equivalent of style == compressed. Note: ugly must be false to allow style.
	 * Defaults to true.
	 * @see style
	 */
	private $ugly = true;
	/**
	 * @var boolean if true comments are preserved in ugly mode. If not in
	 * ugly mode comments are always output. Defaults to false.
	 */
	private $preserveComments = false;
	/**
	 * @var integer Initial debug setting:
	 * no debug, show source, show output, or show all.
	 * Debug settings can be controlled in the template
	 * Defaults to DEBUG_NONE.
	 */
	private $debug = self::DEBUG_NONE;
	/**
	 * @var string Path to the directory containing user defined filters. If 
	 * specified this dirctory will be searched before PHamlP looks for the filter
	 * in it's collection. This allows the default filters to be overridden and
	 * new filters to be installed. Note: No trailing directory separator.
	 */
	private $filterDir;
	/**
	 * @var string Path to the file containing user defined Haml helpers.
	 */
	private $helperFile;
	/**
	 * @var string Haml helper class. This must be an instance of HamlHelpers.
	 */
	private $helperClass = 'HamlHelpers';

	/**
	 * @var array built in Doctypes
	 * @see format
	 * @see doctype
	 */
	private $doctypes = array (
		'html4' => array (
			'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">', //HTML 4.01 Transitional
			'Strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD 4.01 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">', //HTML 4.01 Strict
			'Frameset' => '<!DOCTYPE html PUBLIC "-//W3C//DTD 4.01 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">', //HTML 4.01 Frameset
		),
		'html5' => array (
			'<!DOCTYPE html>', // XHTML 5
		),
		'xhtml' => array (
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">', //XHTML 1.0 Transitional
			'Strict' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">', //XHTML 1.0 Strict
			'Frameset' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">', //XHTML 1.0 Frameset
			'5' => '<!DOCTYPE html>', // XHTML 5
			'1.1' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">', // XHTML 1.1
			'Basic' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic11.dtd">', //XHTML Basic 1.1
			'Mobile' => '<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN" "http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">', //XHTML Mobile 1.2
		)
	);
	/**
	 * @var array A list of tag names that should be automatically self-closed
	 * if they have no content.
	 */
	private $emptyTags = array('meta', 'img', 'link', 'br', 'hr', 'input', 'area', 'param', 'col', 'base');
	/**
	 * @var array A list of inline tags.
	 */
	private $inlineTags = array('a', 'abbr', 'accronym', 'b', 'big', 'cite', 'code', 'dfn', 'em', 'i', 'kbd', 'q', 'samp', 'small', 'span', 'strike', 'strong', 'tt', 'u', 'var');
	/**
	 * @var array attributes that are minimised
	 */
	 private $minimizedAttributes = array('compact', 'checked', 'declare', 'readonly', 'disabled', 'selected', 'defer', 'ismap', 'nohref', 'noshade', 'nowrap', 'multiple', 'noresize');
	/**
	 * @var array A list of tag names that should automatically have their newlines preserved.
	 */
	private $preserve = array('pre', 'textarea');
	/**#@-*/

	/**
	 * @var string the character used for indenting. Space or tab.
	 * @see indentSpaces
	 */
	private $indentChar;
	/**
	 * @var array allowable characters for indenting
	 */
	private $indentChars = array(' ', "\t");
	/**
	 * @var integer number of spaces for indentation.
	 * Used on source if {@link indentChar} is space.
	 * Used on output if {@link ugly} is false.
	 */
	private $indentSpaces;
	/**
	 * @var array loaded filters
	 */
	private $filters = array();
	/**
	 * @var boolean whether line is in a filter
	 */
	private $inFilter = false;
	/**
	 * @var boolean whether to show the output in the browser for debug
	 */
	private $showOutput;
	/**
	 * @var boolean whether to show the source in the browser for debug
	 */
	private $showSource;
	/**
	 * @var integer line number of source being parsed
	 */
	private $line;
	/**
	 * @var string name of file being parsed
	 */
	private $filename;
	/**
	 * @var mixed source
	 */
	private $source;

	/**
	 * HamlParser constructor.
	 * @param array options
	 * @return HamlParser
	 */
	public function __construct($options = array()) {
		if (isset($options['language'])) {
			Phamlp::$language = $options['language'];
			unset($options['language']);
		}
		foreach ($options as $name => $value) {
			$this->$name = $value;
		} // foreach
		
		if ($this->ugly) {
			$this->style = 'compressed';
		}

		$this->format = strtolower($this->format);
		if (is_null($this->doctype) &&
				!array_key_exists($this->format, $this->doctypes)) {
			throw new HamlException('Invalid {what} ({value}). Must be one of "{options}"', array('{what}'=>'format', '{value}'=>$this->format, '{options}'=>join(', ', array_keys($this->doctypes))), $this);
		}

		$this->showSource = $this->debug & HamlParser::DEBUG_SHOW_SOURCE;
		$this->showOutput = $this->debug & HamlParser::DEBUG_SHOW_OUTPUT;
		
		require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'HamlHelpers.php';
		if (isset($this->helperFile)) {
			require_once $this->helperFile;
			$this->helperClass = basename($this->helperFile, ".php"); 
			if (!is_subclass_of($this->helperClass, 'HamlHelpers')) {
				throw new HamlException('{what} must extend {base} class', array('{what}'=>$this->helperClass, '{base}'=>'HamlHelpers'), $this);
			}
		} 
	}
	
	/**
	 * Getter.
	 * @param string name of property to get
	 * @return mixed return value of getter function
	 */
	public function __get($name) {
		$getter = 'get' . ucfirst($name);
		if (method_exists($this, $getter)) {
			return $this->$getter();
		}
		throw new HamlException('No getter function for {what}', array('{what}'=>$name));
	}
	
	public function getFilename() {
		return $this->filename; 
	}
	
	public function getLine() {
		return $this->line; 
	}
	
	public function getSource() {
		return $this->source; 
	}

	/**
	 * Parses a Haml file.
	 * If an output directory is given the resulting PHP is cached.
	 * @param string path to file to parse
	 * @param mixed boolean: true to use the default cache directory, false to use
	 * the source file directory. string: path to the cache directory.
	 * null: disable caching
	 * @param string output file extension
	 * @param integer permission for the output directory and file
	 * @return mixed string: the resulting PHP if no output directory is specified
	 * or the output filename if the output directory is specified.
	 * boolean: false if the output file could not be written.
	 */
	public function parse($sourceFile, $cacheDir=null, $permission=0755, $sourceExtension='.haml', $outputExtension='.php') {
		if (is_string($cacheDir) || is_bool($cacheDir)) {
			if (is_bool($cacheDir)) {
				$cacheDir =
						($cacheDir ? dirname(__FILE__).DIRECTORY_SEPARATOR.'haml-cache' :
						dirname($sourceFile));
			}
			$outputFile = $cacheDir.DIRECTORY_SEPARATOR.
					basename($sourceFile, $sourceExtension).$outputExtension;
			if (@filemtime($sourceFile) > @filemtime($outputFile)) {
				if (!is_dir($cacheDir)) {
					@mkdir($cacheDir, $permission);
				}
				$return = (file_put_contents($outputFile, $this->haml2PHP($sourceFile))
						=== false ? false : $outputFile);
				if ($return !== false) {
					@chmod($outputFile, $permission);
				}
			}
			else {
				$return = $outputFile;
			}
		}
		else {
			$return = $this->haml2PHP($sourceFile);
		}
		return $return;
	}

	/**
	 * Parses a Haml file into PHP.  
	 * @param string path to file to parse
	 * @return string the resulting PHP
	 */
	public function haml2PHP($sourceFile) {
		$this->line = 0;
		$this->filename = $sourceFile;
		$helpers = "<?php\nrequire_once '".dirname(__FILE__).DIRECTORY_SEPARATOR."HamlHelpers.php';\n";
		if (isset($this->helperFile)) {
			$helpers .= "require_once '{$this->helperFile}';\n";
		}
		$helpers .= "?>";
		return $helpers . $this->toTree(file_get_contents($sourceFile))->render();
	}

	/**
	 * Parse Haml source into a document tree.
	 * @param string Haml source
	 * @return HamlRootNode the root of this document tree
	 */
	private function toTree($source) {
		$this->source = explode("\n", $source);
		$this->setIndentChar();

		preg_match_all(self::REGEX_HAML, $source, $this->source, PREG_SET_ORDER);
		unset($source);
		$root = new HamlRootNode(array(
			'format' => $this->format,
			'style' => $this->style,
			'attrWrapper' => $this->attrWrapper,
			'minimizedAttributes' => $this->minimizedAttributes
		));
		$this->buildTree($root);
		return $root;
	}

	/**
	 * Builds a parse tree under the parent node.
	 * @param HamlNode the parent node
	 */
	private function buildTree($parent) {
		while (!empty($this->source) && $this->isChildOf($parent, $this->source[0])) {
			$line = $this->getNextLine();
			if (!empty($line)) {
				$node = ($this->inFilter ?
					new HamlNode($line[self::HAML_SOURCE], $parent) :
					$this->parseLine($line, $parent));

				if (!empty($node)) {
					$node->token = $line;
					$node->showOutput = $this->showOutput;
					$node->showSource = $this->showSource;
					$this->addChildren($node, $line);
				}
			}
		}
	}

	/**
	 * Adds children to a node if the current line has children.
	 * @param HamlNode the node to add children to
	 * @param array line to test
	 */
	private function addChildren($node, $line) {
		if ($node instanceof HamlFilterNode) {
			$this->inFilter = true;
		}
		if ($this->hasChild($line, $this->inFilter)) {
			$this->buildTree($node);
			if ($node instanceof HamlFilterNode) {
				$this->inFilter = false;
			}
		}
	}

	/**
	 * Returns a value indicating if the next line is a child of the parent line
	 * @param array parent line
	 * @param boolean whether to all greater than the current indent
	 * Used if the source line is a comment or a filter.
	 * If true all indented lines are regarded as children; if not the child line
	 * must only be indented by 1 or blank. Defaults to false.
	 * @return boolean true if the next line is a child of the parent line
	 * @throws Exception if the indent is invalid
	 */
	private function hasChild($line, $allowGreater = false) {
		if (!empty($this->source)) {
			$i = 0;
			$c = count($this->source);
			while (empty($nextLine[self::HAML_SOURCE]) && $i <= $c) {
				$nextLine = $this->source[$i++];
			}

			$level = $this->getLevel($nextLine, $line['line'] + $i);

			if (($level == $line['level'] + 1) ||
					($allowGreater && $level > $line['level'])) {
				return true;
			}
			elseif ($level <= $line['level']) {
				return false;
			}
			else {
				throw new HamlException('Illegal indentation level ({level}); indentation level can only increase by one', array('{level}'=>$level), $this);
			}
		}
		else {
			return false;
		}
	}

	/**
	 * Returns a value indicating if $line is a child of a node.
	 * A blank line is a child of a node.
	 * @param HamlNode the node
	 * @param array the line to check
	 * @return boolean true if the line is a child of the node, false if not
	 */
	private function isChildOf($node, $line) {
		$haml = trim($line[self::HAML_HAML]);
		return empty($haml) || $this->getLevel($line, $this->line) >
			$node->level;
	}

	/**
	 * Determine the indent character and indent spaces.
	 * The first character of the first indented line determines the character.
	 * If this is a space the number of spaces determines the indentSpaces; this
	 * is always 1 if the indent character is a tab.
	 * @throws HamlException if the indent is mixed
	 */
	private function setIndentChar() {
		foreach ($this->source as $l=>$source) {
			if (!empty($source) && in_array($source[0], $this->indentChars)) {
				$this->indentChar = $source[0];
				for	($i = 0, $len = strlen($source); $i < $len && $source[$i] == $this->indentChar; $i++);
				if ($i < $len && in_array($source[$i], $this->indentChars)) {
					$this->line = ++$l;
					$this->source = $source;
					throw new HamlException('Mixed indentation not allowed', array(), $this);
				}
				$this->indentSpaces = ($this->indentChar == ' ' ? $i : 1);
				return;
			}
		} // foreach
		$this->indentChar = ' ';
		$this->indentSpaces = 2;
	}

	/**
	 * Gets the next line.
	 * @param array remaining source lines
	 * @return array the next line
	 */
	private function getNextLine() {
		$line = array_shift($this->source);
		// Blank lines ore OK
		$haml =  trim($line[self::HAML_HAML]);
		if (empty($haml)) {
			$this->line++;
			return null;
		}
		// The regex will strip off a '<' at the start of a line
		if ($line[self::HAML_WHITESPACE_REMOVAL] ===
				self::INNER_WHITESPACE_REMOVAL && empty($line[self::HAML_TAG])) {
			$line[self::HAML_CONTENT] =
				$line[self::HAML_WHITESPACE_REMOVAL].$line[self::HAML_TOKEN].$line[self::HAML_CONTENT];
		}
		// The regex treats lines starting with [.+] as an object reference; they are just content
		if (!empty($line[self::HAML_OBJECT_REFERENCE]) && empty($line[self::HAML_TAG])) {
			unset($line[self::HAML_OBJECT_REFERENCE]);
			$line[self::HAML_CONTENT] = $line[self::HAML_SOURCE];
		}
		$line['line'] = $this->line++;
		$line['level'] = $this->getLevel($line, $this->line);
		$line['filename'] = $this->filename;
		if ($this->isMultiline($line)) {
			$line = $this->getMultiline($line);
		}
		return $line;
	}

	/**
	 * Returns the indent level of the line.
	 * @param array the line
	 * @param integer line number
	 * @return integer the indent level of the line
	 * @throws Exception if the indent level is invalid
	 */
	private function getLevel($line, $n) {
		if ($line[self::HAML_INDENT] && $this->indentChar === ' ') {
			$indent = strlen($line[self::HAML_INDENT]) / $this->indentSpaces;
		}
		else {
			$indent = strlen($line[self::HAML_INDENT]);
		}

		if (!is_integer($indent) ||
				preg_match("/[^{$this->indentChar}]/", $line[self::HAML_INDENT])) {
			throw new HamlException('Invalid indentation', array(), $this);
		}
		return $indent;
	}

	/**
	 * Parse a line of Haml into a HamlNode for the document tree
	 * @param array line to parse
	 * @param HamlNode parent node
	 * @return HamlNode
	 */
	private function parseLine($line, $parent) {
		if ($this->isHamlComment($line)) {
			return $this->parseHamlComment($line);
		}
		elseif ($this->isXmlComment($line)) {
			return $this->parseXmlComment($line, $parent);
		}
		elseif ($this->isElement($line)) {
			return $this->parseElement($line, $parent);
		}
		elseif ($this->isHelper($line)) {
			return $this->parseHelper($line, $parent);
		}
		elseif ($this->isCode($line)) {
			return $this->parseCode($line, $parent);
		}
		elseif ($this->isDirective($line)) {
			return $this->parseDirective($line, $parent);
		}
		elseif ($this->isFilter($line)) {
			return $this->parseFilter($line, $parent);
		}
		elseif ($this->isDoctype($line)) {
			return $this->parseDoctype($line, $parent);
		}
		else {
			return $this->parseContent($line, $parent);
		}
	}

	/**
	 * Return a value indicating if the line has content.
	 * @param array line
	 * @return boolean true if the line has a content, false if not
	 */
	private function hasContent($line) {
	  return !empty($line[self::HAML_CONTENT]);
	}

	/**
	 * Return a value indicating if the line is code to be run.
	 * @param array line
	 * @return boolean true if the line is code to be run, false if not
	 */
	private function isCode($line) {
		return $line[self::HAML_TOKEN] === self::RUN_CODE;
	}

	/**
	 * Return a value indicating if the line is a directive.
	 * @param array line
	 * @return boolean true if the line is a directive, false if not
	 */
	private function isDirective($line) {
		return $line[self::HAML_TOKEN] === self::DIRECTIVE;
	}

	/**
	 * Return a value indicating if the line is a doctype.
	 * @param array line
	 * @return boolean true if the line is a doctype, false if not
	 */
	private function isDoctype($line) {
		return $line[self::HAML_TOKEN] === self::DOCTYPE;
	}

	/**
	 * Return a value indicating if the line is an element.
	 * Will set the tag to div if it is an implied div.
	 * @param array line
	 * @return boolean true if the line is an element, false if not
	 */
	private function isElement(&$line) {
		if (empty($line[self::HAML_TAG]) && (
				!empty($line[self::HAML_CLASS]) ||
				!empty($line[self::HAML_ID]) ||
				!empty($line[self::HAML_XML_ATTRIBUTES]) ||
				!empty($line[self::HAML_RUBY_ATTRIBUTES]) ||
				!empty($line[self::HAML_OBJECT_REFERENCE])
		)) {
			$line[self::HAML_TAG] = 'div';
		}

	  return !empty($line[self::HAML_TAG]);
	}

	/**
	 * Return a value indicating if the line starts a filter.
	 * @param array line to test
	 * @return boolean true if the line starts a filter, false if not
	 */
	private function isFilter($line) {
	  return !empty($line[self::HAML_FILTER]);
	}

	/**
	 * Return a value indicating if the line is a Haml comment.
	 * @param array line to test
	 * @return boolean true if the line is a Haml comment, false if not
	 */
	private function isHamlComment($line) {
		return preg_match(self::HAML_COMMENT, $line[self::HAML_TOKEN]) > 0;
	}

	/**
	 * Return a value indicating if the line is a HamlHelper.
	 * @param array line to test
	 * @return boolean true if the line is a HamlHelper, false if not
	 */
	private function isHelper($line) {
		return (preg_match(HamlHelperNode::MATCH, $line[self::HAML_CONTENT], $matches)
			? method_exists($this->helperClass, $matches[HamlHelperNode::NAME]) : false);
	}

	/**
	 * Return a value indicating if the line is an XML comment.
	 * @param array line to test
	 * @return boolean true if theline is an XML comment, false if not
	 */
	private function isXmlComment($line) {
	  return $line[self::HAML_SOURCE][0] === self::XML_COMMENT;
	}

	/**
	 * Returns a value indicating whether the line is part of a multilne group
	 * @param array the line to test
	 * @return boolean true if the line os part of a multiline group, false if not
	 */
	private function isMultiline($line) {
	  return substr($line[self::HAML_SOURCE], -2) === self::MULTILINE;
	}

	/**
	 * Return a value indicating if the line's tag is a block level tag.
	 * @param array line
	 * @return boolean true if the line's tag is is a block level tag, false if not
	 */
	private function isBlock($line) {
	  return (!in_array($line[self::HAML_TAG], $this->inlineTags));
	}

	/**
	 * Return a value indicating if the line's tag is self-closing.
	 * @param array line
	 * @return boolean true if the line's tag is self-closing, false if not
	 */
	private function isSelfClosing($line) {
	  return (in_array($line[self::HAML_TAG], $this->emptyTags) ||
	  	$line[self::HAML_TOKEN] == self::SELF_CLOSE_TAG);
	}

	/**
	 * Gets a filter.
	 * Filters are loaded on first use.
	 * @param string filter name
	 * @throws HamlException if the filter does not exist or does not extend HamlBaseFilter
	 */
	private function getFilter($filter) {
		static $firstRun = true;
		$imported = false;
		
		if (empty($this->filters[$filter])) {
			if ($firstRun) {
				require_once('filters/HamlBaseFilter.php');
				$firstRun = false;
			}

			$filterclass = 'Haml' . ucfirst($filter) . 'Filter';
			if (isset($this->filterDir)) {
				$this->filterDir = (substr($this->filterDir, -1) == DIRECTORY_SEPARATOR?
						substr($this->filterDir, 0, -1):$this->filterDir);
				if (file_exists($this->filterDir.DIRECTORY_SEPARATOR."$filterclass.php")) {
					require_once($this->filterDir.DIRECTORY_SEPARATOR."$filterclass.php");
					$imported = true; 
				}
			}

			if (!$imported && file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'filters'.DIRECTORY_SEPARATOR."$filterclass.php")) {
				require_once("filters/$filterclass.php");
				$imported = true; 
			}
			
			if (!$imported) {
				throw new HamlException('Unable to find {what}: {filename}', array('{what}'=>$filter.' filter', '{filename}'=>$filterclass.'.php'), $this);
			}
			
			$this->filters[$filter] = new $filterclass();

			if (!($this->filters[$filter] instanceof HamlBaseFilter)) {
				throw new HamlException('{what} must extend {base} class', array('{what}'=>$filter, '{base}'=>'HamlBaseFilter'), $this);
			}

			$this->filters[$filter]->init();
		}
		return $this->filters[$filter];
	}

	/**
	 * Gets the next line.
	 * @param array first line
	 * @return array the next line
	 */
	private function getMultiline($line) {
		do {
			$multiLine = array_shift($this->source);
			$line[self::HAML_CONTENT] .= substr($multiLine[self::HAML_SOURCE], 0, -2);
		} while(!empty($this->source) && $this->isMultiline($this->source[0]));
	  return $line;
	}

	/**
	 * Parse attributes.
	 * @param array line to parse
	 * @return array attributes in name=>value pairs
	 */
	private function parseAttributes($line) {
		$attributes = array();
		if (!empty($line[self::HAML_OPEN_XML_ATTRIBUTES])) {
			if (empty($line[self::HAML_XML_ATTRIBUTES])) {
				$line[self::HAML_XML_ATTRIBUTES] = $line[self::HAML_CONTENT];
				unset($line[self::HAML_CONTENT]);
				do {
					$multiLine = array_shift($this->source);
					$line[self::HAML_XML_ATTRIBUTES] .= $multiLine[self::HAML_CONTENT];
				}	while (substr($line[self::HAML_XML_ATTRIBUTES], -1) !==
						self::CLOSE_XML_ATTRIBUTES);		
			}
			if (preg_match(self::HTML_ATTRS, $line[self::HAML_XML_ATTRIBUTES], $htmlAttrs)) {
				$line[self::HAML_XML_ATTRIBUTES] = preg_replace(self::HTML_ATTRS, '', $line[self::HAML_XML_ATTRIBUTES]);
				$attributes = array_merge($attributes, $this->htmlAttrs($htmlAttrs));			
			}
			$attributes = array_merge(
					$attributes,
					$this->parseAttributeHash($line[self::HAML_XML_ATTRIBUTES])
			);
		}
		if (!empty($line[self::HAML_OPEN_RUBY_ATTRIBUTES])) {
			if (empty($line[self::HAML_RUBY_ATTRIBUTES])) {
				$line[self::HAML_RUBY_ATTRIBUTES] = $line[self::HAML_CONTENT];
				unset($line[self::HAML_CONTENT]);
				do {
					$multiLine = array_shift($this->source);
					$line[self::HAML_RUBY_ATTRIBUTES] .= $multiLine[self::HAML_CONTENT];
				}	while (substr($line[self::HAML_RUBY_ATTRIBUTES], -1) !==
						self::CLOSE_RUBY_ATTRIBUTES);		
			}
			if (preg_match(self::HTML_ATTRS, $line[self::HAML_RUBY_ATTRIBUTES], $htmlAttrs)) {
				$line[self::HAML_RUBY_ATTRIBUTES] = preg_replace(self::HTML_ATTRS, '', $line[self::HAML_RUBY_ATTRIBUTES]);
				$attributes = array_merge($attributes, $this->htmlAttrs($htmlAttrs));			
			}
			$attributes = array_merge(
					$attributes,
					$this->parseAttributeHash($line[self::HAML_RUBY_ATTRIBUTES])
			);
		}
		if (!empty($line[self::HAML_OBJECT_REFERENCE])) {
			$objectRef = explode(',', preg_replace('/,\s*/', ',', $line[self::HAML_OBJECT_REFERENCE]));
			$prefix = (isset($objectRef[1]) ? $objectRef[1] . '_' : '');
			$class = "strtolower(str_replace(' ',	'_', preg_replace('/(?<=\w)([ A-Z])/', '_\1', get_class(" . $objectRef[0] . '))))';
			$attributes['class'] = "<?php echo '$prefix' . $class; ?>";
			$attributes['id'] = "<?php echo '$prefix' . $class . '_' . {$objectRef[0]}->id; ?>";
		}
		else {
			if (!empty($line[self::HAML_CLASS])) {
				$classes = explode('.', $line[self::HAML_CLASS]);
				foreach ($classes as &$class) {
					if (preg_match(self::MATCH_INTERPOLATION, $class)) {
						$class = $this->interpolate($class);
					}
				} // foreach
				$attributes['class'] = join(' ', $classes) .
						(isset($attributes['class']) ? " {$attributes['class']}" : '');
			}
			if (!empty($line[self::HAML_ID])) {
				$attributes['id'] =
						(preg_match(self::MATCH_INTERPOLATION, $line[self::HAML_ID]) ?
						$this->interpolate($line[self::HAML_ID]) : $line[self::HAML_ID]) .
						(isset($attributes['id']) ? "_{$attributes['id']}" : '');
			}
		}

		ksort($attributes, SORT_STRING);
	  return $attributes;
	}

	/**
	 * Parse attributes.
	 * @param string the attributes
	 * @return array attributes in name=>value pairs
	 */
	private function parseAttributeHash($subject) {
		$subject = substr($subject, 0, -1);
 		$attributes = array();
		if (preg_match(self::REGEX_ATTRIBUTE_FUNCTION, $subject)) {
			$attributes[0] = "<?php echo $subject; ?>";
			return $attributes;
		}
		
		preg_match_all(self::REGEX_ATTRIBUTES, $subject, $attrs, PREG_SET_ORDER);
		foreach ($attrs as $attr) {
			if (!empty($attr[1])) { // HTML5 Custom Data Attributes
				$dataAttributes = $this->parseAttributeHash(substr($attr[2], 1));
				foreach ($dataAttributes as $key=>$value) {
					$attributes["data-$key"] = $value;				
				} // foreach
			}
			elseif (!empty($attr[4])) {
				$values = array_map('trim', explode(',', $attr[4]));
				if ($attr[3] !== 'class' && $attr[3] !== 'id') {
					throw new HamlException('Attribute must be "class" or "id" with array value', array(), $this);
				}
				$attributes[$attr[3]] = '<?php echo ' . join(($attr[3] === 'id' ? ".'_'." : ".' '."), $values) . '; ?>';
			}
			elseif (!empty($attr[6])) {
				$attributes[$attr[3]] = $this->interpolate($attr[6]);
			}
			elseif ($attr[6] === '') {
				$attributes[$attr[3]] = $attr[6];
			}
			else {
				switch ($attr[7]) {
					case 'true':
						$attributes[$attr[3]] = $attr[3];
						break;
					case 'false':
						break;
					default:
						$attributes[$attr[3]] = "<?php echo {$attr[7]}; ?>";
						break;
				}
			}
		} // foreach
		return $attributes;
	}
	
	/**
	 * Returns an array of attributes for the html element.
	 * @param array arguments for HamlHelpers::html_attrs 
	 * @return array attributes for the html element
	 */
	private function htmlAttrs($htmlAttrs) {
		if (empty($htmlAttrs[1]) && empty($htmlAttrs[2])) {
			return HamlHelpers::html_attrs();
		}
		else {
			$htmlAttrs[1] = substr($htmlAttrs[1], 1, -1);
			if (substr($htmlAttrs[1], -1) == ';') {
				$htmlAttrs[1] = eval("return {$htmlAttrs[1]}");
			}
			if (isset($htmlAttrs[2])) {
				return HamlHelpers::html_attrs($htmlAttrs[1], eval($htmlAttrs[2] . ';'));
			}
			else {
				return HamlHelpers::html_attrs($htmlAttrs[1]);
			}
		}
	}
	
	/**
	 * Parse code
	 * @param array line to parse
	 * @param HamlNode parent node
	 * @return HamlCodeBlockNode
	 */
	private function parseCode($line, $parent) {
		if (preg_match('/^(if|foreach|for|switch|do|while)\b(.*)$/',
				$line[self::HAML_CONTENT], $block)) {
			if ($block[1] === 'do') {
				$node = new HamlCodeBlockNode('<?php do { ?>', $parent);
				$node->doWhile = 'while' . $block[2] . ';';
			}
			elseif ($block[1] === 'switch') {
				$node = new HamlCodeBlockNode("<?php {$line[self::HAML_CONTENT]} {", $parent);
			}
			else {
				$node = new HamlCodeBlockNode("<?php {$line[self::HAML_CONTENT]} { ?>", $parent);
			}
		}
		elseif (strpos($line[self::HAML_CONTENT], 'else') === 0) {
			$node = new HamlCodeBlockNode("<?php } {$line[self::HAML_CONTENT]} { ?>", null);
			$node->token = $line;
			$node->showOutput = $this->showOutput;
			$node->showSource = $this->showSource;
			$parent->getLastChild()->addElse($node);
			$this->addChildren($node, $line);
			$node = null;
		}
		elseif (strpos($line[self::HAML_CONTENT], 'case') === 0) {
			$node = new HamlNode(($parent->hasChildren() ? '<?php ' : '') .
					"{$line[self::HAML_CONTENT]}: ?>", $parent);
		}
		else {
			$node = new HamlNode("<?php {$line[self::HAML_CONTENT]}; ?>", $parent);
		}
		return $node;
	}

	/**
	 * Parse content
	 * @param array line to parse
	 * @param HamlNode parent node
	 * @return HamlNode
	 */
	private function parseContent($line, $parent) {
		switch ($line[self::HAML_TOKEN]) {
		  case self::INSERT_CODE:
		  	$content = ($this->suppressEval ? '' :
						'<?php echo ' . ($this->escapeHtml ?
						'htmlentities(' . $line[self::HAML_CONTENT] . ')' :
						$line[self::HAML_CONTENT]) .
						"; ?>" .
						($this->style == HamlRenderer::STYLE_EXPANDED ||
							$this->style == HamlRenderer::STYLE_NESTED ? "\n" : ''));
		    break;
		  case self::INSERT_CODE_PRESERVE_WHITESPACE:
				$content = ($this->suppressEval ? '' :
						'<?php echo str_replace("\n", \'&#x000a\', ' . ($this->escapeHtml ?
						'htmlentities(' . $line[self::HAML_CONTENT] . ')' :
						$line[self::HAML_CONTENT]) .
						"; ?>" .
						($this->style == HamlRenderer::STYLE_EXPANDED ||
							$this->style == HamlRenderer::STYLE_NESTED ? "\n" : ''));
		    break;
		  default:
		  	$content = $line[self::HAML_CONTENT];
		    break;
		} // switch

	  return new HamlNode($this->interpolate($content), $parent);
	}

	/**
	 * Parse a directive.
	 * Various options are set according to the directive
	 * @param array line to parse
	 * @return null
	 */
	private function parseDirective($line) {
		preg_match('/(\w+)(\+|-)?/', $line[self::HAML_CONTENT], $matches);
		switch ($matches[1]) {
		  case 's':
		  	$this->showSource = ($matches[2] == '+' ? true :
		  		($matches[2] == '-' ? false : $this->showSource));
		    break;
		  case 'o':
		  	$this->showOutput = ($matches[2] == '+' ? true :
		  		($matches[2] == '-' ? false : $this->showOutput));
		    break;
		  case 'os':
		  case 'so':
		  	$this->showSource = ($matches[2] == '+' ? true :
		  		($matches[2] == '-' ? false : $this->showSource));
		  	$this->showOutput = ($matches[2] == '+' ? true :
		  		($matches[2] == '-' ? false : $this->showOutput));
		    break;
		  default:
		  	if (!in_array($matches[1], $this->styles)) {
					throw new HamlException('Invalid {what} ({value})', array('{what}'=>'directive', '{value}'=>self::DIRECTIVE.$matches[0]), $this);
		  	}
		  	$this->style = $matches[1];
		    break;
		} // switch
	}

	/**
	 * Parse a doctype declaration
	 * @param array line to parse
	 * @param HamlNode parent node
	 * @return HamlDoctypeNode
	 */
	private function parseDoctype($line, $parent) {
		$content = explode(' ', $line[self::HAML_CONTENT]);
		if (!empty($content)) {
			if ($content[0] === self::IS_XML_PROLOG) {
				$encoding = isset($content[1]) ? $content[1] : self::DEFAULT_XML_ENCODING;
				$output = str_replace(self::XML_ENCODING, $encoding, self::XML_PROLOG);
			}
			elseif (empty($content[0])) {
				$output = $this->doctypes[$this->format][0];
			}
			elseif (array_key_exists($content[0],
					$this->doctypes[$this->format])) {
				$output = $this->doctypes[$this->format][$content[0]];
			}
			elseif (!empty($this->doctype)) {
				$output = $this->doctype;				
			}
			else {
				$_doctypes = array_keys($this->doctypes[$this->format]);
				array_shift($_doctypes);
				throw new HamlException('Invalid {what} ({value}); must be one of "{options}"', array('{what}'=>'doctype', '{value}'=>$content[0], '{options}'=>join(', ', $_doctypes).' or empty'), $this);
			}
		}
		return new HamlDoctypeNode($output, $parent);
	}

	/**
	 * Parse a Haml comment.
	 * If the comment is an empty comment eat all child lines.
	 * @param array line to parse
	 */
	private function parseHamlComment($line) {
		if (!$this->hasContent($line)) {
			while ($this->hasChild($line, true)) {
				array_shift($this->source);
				$this->line++;
			}
		}
	}

	/**
	 * Parse a HamlHelper.
	 * @param array line to parse
	 * @param HamlNode parent node
	 * @return HamlHelperNode
	 */
	private function parseHelper($line, $parent) {
		preg_match(HamlHelperNode::MATCH, $line[self::HAML_CONTENT], $matches);
		$node = new HamlHelperNode($this->helperClass, $matches[HamlHelperNode::PRE], $matches[HamlHelperNode::NAME], $matches[HamlHelperNode::ARGS], $parent);
		if (isset($matches[HamlHelperNode::BLOCK])) {
			new HamlNode($matches[HamlHelperNode::BLOCK], $node);
		}
		return $node;
	}

	/**
	 * Parse an element.
	 * @param array line to parse
	 * @param HamlNode parent node
	 * @return HamlElementNode tag node and children
	 */
	private function parseElement($line, $parent) {
		$node = new HamlElementNode($line[self::HAML_TAG], $parent);
		$node->isSelfClosing = $this->isSelfClosing($line);
		$node->isBlock = $this->isBlock($line);
		$node->attributes = $this->parseAttributes($line);
		if ($this->hasContent($line)) {
			$child = $this->parseContent($line, $node);
			$child->showOutput = $this->showOutput;
			$child->showSource = $this->showSource;
			$child->token = array(
				self::HAML_SOURCE => $line[self::HAML_SOURCE],
				'filename' => $line['filename'],
				'line' => $line['line'],
				'level' => ($line['level'] + 1)
			);
		}
		$node->whitespaceControl = $this->parseWhitespaceControl($line);
	  return $node;
	}

	/**
	 * Parse a filter.
	 * @param array line to parse
	 * @param HamlNode parent node
	 * @return HamlNode filter node
	 */
	private function parseFilter($line, $parent) {
		$node = new HamlFilterNode($this->getFilter($line[self::HAML_FILTER]), $parent);
		if ($this->hasContent($line)) {
			$child = $this->parseContent($line);
			$child->showOutput = $this->showOutput;
			$child->showSource = $this->showSource;
			$child->token = array(
				'level' => ($line['level'] + 1),
				'line' => $line['line']
			);
		}
	  return $node;
	}

	/**
	 * Parse an Xml comment.
	 * @param array line to parse
	 * @param HamlNode parent node
	 * @return HamlCommentNode
	 */
	private function parseXmlComment($line, $parent) {
		return new HamlCommentNode($line[self::HAML_CONTENT], $parent);
	}

	private function parseWhitespaceControl($line) {
		$whitespaceControl = array('inner' => false, 'outer' => array('left' => false, 'right' => false));

		if (!empty($line[self::HAML_WHITESPACE_REMOVAL])) {
			$whitespaceControl['inner'] =
					(strpos($line[self::HAML_WHITESPACE_REMOVAL],
					self::INNER_WHITESPACE_REMOVAL) !== false);

			if (strpos($line[self::HAML_WHITESPACE_REMOVAL],
					self::OUTER_WHITESPACE_REMOVAL) !== false) {
				$whitespaceControl['outer']['left'] =
						(strpos($line[self::HAML_WHITESPACE_REMOVAL],
						self::BLOCK_LEFT_OUTER_WHITESPACE_REMOVAL) === false);
				$whitespaceControl['outer']['right'] =
						(strpos($line[self::HAML_WHITESPACE_REMOVAL],
						self::BLOCK_RIGHT_OUTER_WHITESPACE_REMOVAL) === false);
			}
		}
	  return $whitespaceControl;
	}

	/**
	 * Replace interpolated PHP contained in '#{}'.
	 * @param string the text to interpolate
	 * @return string the interpolated text
	 */
	protected function interpolate($string) {
	  return preg_replace(self::MATCH_INTERPOLATION, self::INTERPOLATE, $string);
	}
}
