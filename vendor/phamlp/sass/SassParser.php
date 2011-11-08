<?php
/* SVN FILE: $Id: SassParser.php 118 2010-09-21 09:45:11Z chris.l.yates@gmail.com $ */
/**
 * SassParser class file.
 * See the {@link http://sass-lang.com/docs Sass documentation}
 * for details of Sass.
 * 
 * Credits:
 * This is a port of Sass to PHP. All the genius comes from the people that
 * invented and develop Sass; in particular:
 * + {@link http://hamptoncatlin.com/ Hampton Catlin},
 * + {@link http://nex-3.com/ Nathan Weizenbaum},
 * + {@link http://chriseppstein.github.com/ Chris Eppstein}
 * 
 * The bugs are mine. Please report any found at {@link http://code.google.com/p/phamlp/issues/list}
 * 
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass
 */

require_once('SassFile.php');
require_once('SassException.php');
require_once('tree/SassNode.php');

/**
 * SassParser class.
 * Parses {@link http://sass-lang.com/ .sass and .sccs} files.
 * @package			PHamlP
 * @subpackage	Sass
 */
class SassParser {
	/**#@+
	 * Default option values
	 */
	const CACHE							= true;
	const CACHE_LOCATION		= './sass-cache';
	const CSS_LOCATION			= './css';
	const TEMPLATE_LOCATION = './sass-templates';
	const BEGIN_COMMENT			= '/';
	const BEGIN_CSS_COMMENT	= '/*';
	const END_CSS_COMMENT		= '*/';
	const BEGIN_SASS_COMMENT= '//';
	const BEGIN_INTERPOLATION = '#';
	const BEGIN_INTERPOLATION_BLOCK = '#{';
	const BEGIN_BLOCK				= '{';
	const END_BLOCK					= '}';
	const END_STATEMENT			= ';';
	const DOUBLE_QUOTE			= '"';
	const SINGLE_QUOTE			= "'";

	/**
	 * @var string the character used for indenting
	 * @see indentChars
	 * @see indentSpaces
	 */
	private $indentChar;
	/**
	 * @var array allowable characters for indenting
	 */
	private $indentChars = array(' ', "\t");
	/**
	 * @var integer number of spaces for indentation.
	 * Used to calculate {@link Level} if {@link indentChar} is space.
	 */
	private $indentSpaces = 2;
	
	/**
	 * @var string source
	 */
	private $source;
	 
	/**#@+
	 * Option
	 */
	/**
	 * cache: 
	 * @var boolean Whether parsed Sass files should be cached, allowing greater
	 * speed.
	 * 
	 * Defaults to true.
	 */
	private $cache;
	
	/**
	 * cache_location:
	 * @var string The path where the cached sassc files should be written to.
	 * 
	 * Defaults to './sass-cache'.
	 */
	private $cache_location;
	
	/**
	 * css_location:
	 * @var string The path where CSS output should be written to.
	 * 
	 * Defaults to './css'.
	 */
	private $css_location;
	
	/**
	 * debug_info:
	 * @var boolean When true the line number and file where a selector is defined
	 * is emitted into the compiled CSS in a format that can be understood by the
	 * {@link https://addons.mozilla.org/en-US/firefox/addon/103988/
	 * FireSass Firebug extension}.
	 * Disabled when using the compressed output style.
	 * 
	 * Defaults to false.
	 * @see style
	 */
	private $debug_info;
	
	/**
	 * extensions:
	 * @var array Sass extensions, e.g. Compass. An associative array of the form
	 * $name => $options where $name is the name of the extension and $options
	 * is an array of name=>value options pairs.
	 */
	protected $extensions;
	
	/**
	 * filename:
	 * @var string The filename of the file being rendered. 
	 * This is used solely for reporting errors.
	 */
	protected $filename;
	
	/**
	 * function_paths:
	 * @var array An array of filesystem paths which should be searched for
	 * SassScript functions.
	 */
	private $function_paths;
	
	/**
	 * line:
	 * @var integer The number of the first line of the Sass template. Used for
	 * reporting line numbers for errors. This is useful to set if the Sass
	 * template is embedded.
	 * 
	 * Defaults to 1. 
	 */
	private $line;
	
	/**
	 * line_numbers:
	 * @var boolean When true the line number and filename where a selector is
	 * defined is emitted into the compiled CSS as a comment. Useful for debugging
	 * especially when using imports and mixins.
	 * Disabled when using the compressed output style or the debug_info option.
	 * 
	 * Defaults to false.
	 * @see debug_info
	 * @see style
	 */
	 private $line_numbers;
	
	/**
	 * load_paths:
	 * @var array An array of filesystem paths which should be searched for
	 * Sass templates imported with the @import directive.
	 * 
	 * Defaults to './sass-templates'.
	 */
	private $load_paths;
	
	/**
	 * property_syntax: 
	 * @var string Forces the document to use one syntax for
	 * properties. If the correct syntax isn't used, an error is thrown. 
	 * Value can be:
	 * + new - forces the use of a colon or equals sign after the property name.
	 * For example	 color: #0f3 or width: $main_width.
	 * + old -  forces the use of a colon before the property name.
	 * For example: :color #0f3 or :width = $main_width.
	 * 
	 * By default, either syntax is valid.
	 * 
	 * Ignored for SCSS files which alaways use the new style.
	 */
	private $property_syntax;
	
	/**
	 * quiet:
	 * @var boolean When set to true, causes warnings to be disabled.
	 * Defaults to false.
	 */
	private $quiet;
	
	/**
	 * style:
	 * @var string the style of the CSS output.
	 * Value can be:
	 * + nested - Nested is the default Sass style, because it reflects the
	 * structure of the document in much the same way Sass does. Each selector
	 * and rule has its own line with indentation is based on how deeply the rule
	 * is nested. Nested style is very useful when looking at large CSS files as
	 * it allows you to very easily grasp the structure of the file without
	 * actually reading anything.
	 * + expanded - Expanded is the typical human-made CSS style, with each selector
	 * and property taking up one line. Selectors are not indented; properties are
	 * indented within the rules.
	 * + compact - Each CSS rule takes up only one line, with every property defined
	 * on that line. Nested rules are placed with each other while groups of rules
	 * are separated by a blank line.
	 * + compressed - Compressed has no whitespace except that necessary to separate
	 * selectors and properties. It's not meant to be human-readable.
	 * 
	 * Defaults to 'nested'.
	 */
	private $style;
	
	/**
	 * syntax:
	 * @var string The syntax of the input file.
	 * 'sass' for the indented syntax and 'scss' for the CSS-extension syntax.
	 * 
	 * This is set automatically when parsing a file, else defaults to 'sass'.
	 */
	private $syntax;

	/**
	 * template_location:
	 * @var string Path to the root sass template directory for your
	 * application.
	 */
	private $template_location;

	/**
	 * vendor_properties:
	 * If enabled a property need only be written in the standard form and vendor
	 * specific versions will be added to the style sheet.
	 * @var mixed array: vendor properties, merged with the built-in vendor
	 * properties, to automatically apply.
	 * Boolean true: use built in vendor properties.
	 * 
	 * Defaults to vendor_properties disabled.
	 * @see _vendorProperties
	 */
	private $vendor_properties = array();
	
	/**#@-*/
	/**
	 * Defines the build-in vendor properties
	 * @var array built-in vendor properties
	 * @see vendor_properties
	 */
	private $_vendorProperties = array(
		'border-radius' => array(
			'-moz-border-radius',
			'-webkit-border-radius',
			'-khtml-border-radius'
		),
		'border-top-right-radius' => array(
			'-moz-border-radius-topright',
			'-webkit-border-top-right-radius',
			'-khtml-border-top-right-radius'
		),
		'border-bottom-right-radius' => array(
			'-moz-border-radius-bottomright', 
			'-webkit-border-bottom-right-radius',
			'-khtml-border-bottom-right-radius'
		),
		'border-bottom-left-radius' => array(
			'-moz-border-radius-bottomleft',
			'-webkit-border-bottom-left-radius',
			'-khtml-border-bottom-left-radius'
		),
		'border-top-left-radius' => array(
			'-moz-border-radius-topleft',
			'-webkit-border-top-left-radius',
			'-khtml-border-top-left-radius'
		),
		'box-shadow' => array('-moz-box-shadow', '-webkit-box-shadow'),
		'box-sizing' => array('-moz-box-sizing', '-webkit-box-sizing'),
		'opacity' => array('-moz-opacity', '-webkit-opacity', '-khtml-opacity'),
	);

	/**
	 * Constructor.
	 * Sets parser options
	 * @param array $options
	 * @return SassParser
	 */
	public function __construct($options = array()) {
		if (!is_array($options)) {
			throw new SassException('{what} must be a {type}', array('{what}'=>'options', '{type}'=>'array'));
		}
		if (!empty($options['language'])) {
			Phamlp::$language = $options['language'];
		}
		
		if (!empty($options['extensions'])) {
			foreach ($options['extensions'] as $extension=>$extOptions) {
				include dirname(__FILE__).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$extension.DIRECTORY_SEPARATOR.'config.php';
				$configClass = 'SassExtentions'.$extension.'Config';
				$config = new $configClass;
				$config->config($extOptions);
				
				$lp = dirname(__FILE__).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$extension.DIRECTORY_SEPARATOR.'frameworks';
				$fp = dirname(__FILE__).DIRECTORY_SEPARATOR.'extensions'.DIRECTORY_SEPARATOR.$extension.DIRECTORY_SEPARATOR.'functions';
				$options['load_paths'] = (empty($options['load_paths']) ?
					array($lp) : array_merge($options['load_paths'], $lp));
				$options['function_paths'] = (empty($options['function_paths']) ?
					array($fp) : array_merge($options['function_paths'], $fp));			
			}
		}
		
		if (!empty($options['vendor_properties'])) {
			if ($options['vendor_properties'] === true) {
				$this->vendor_properties = $this->_vendorProperties;
			}
			elseif (is_array($options['vendor_properties'])) {
				$this->vendor_properties = array_merge($this->vendor_properties, $this->_vendorProperties);
			}
		}
		unset($options['language'], $options['vendor_properties']);
		
		$defaultOptions = array(
			'cache' 				 => self::CACHE,
			'cache_location' => dirname(__FILE__) . DIRECTORY_SEPARATOR . self::CACHE_LOCATION,
			'css_location'	 => dirname(__FILE__) . DIRECTORY_SEPARATOR . self::CSS_LOCATION,
			'debug_info'		 => false,
			'filename'			 => array('dirname' => '', 'basename' => ''),
			'function_paths' => array(),
			'load_paths' 		 => array(dirname(__FILE__) . DIRECTORY_SEPARATOR . self::TEMPLATE_LOCATION),
			'line'					 => 1,
			'line_numbers'	 => false,
			'style' 				 => SassRenderer::STYLE_NESTED,
			'syntax'				 => SassFile::SASS
		);
		
		foreach (array_merge($defaultOptions, $options) as $name=>$value) {
			if (property_exists($this, $name)) {
				$this->$name = $value;
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
		throw new SassException('No getter function for {what}', array('{what}'=>$name));
	}
	
	public function getCache() {
		return $this->cache; 
	}
	
	public function getCache_location() {
		return $this->cache_location; 
	}
	
	public function getCss_location() {
		return $this->css_location; 
	}
	
	public function getDebug_info() {
		return $this->debug_info; 
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
	
	public function getLine_numbers() {
		return $this->line_numbers; 
	}
	
	public function getFunction_paths() {
		return $this->function_paths; 
	}
	
	public function getLoad_paths() {
		return $this->load_paths; 
	}
	
	public function getProperty_syntax() {
		return $this->property_syntax; 
	}
	
	public function getQuiet() {
		return $this->quiet; 
	}
	
	public function getStyle() {
		return $this->style; 
	}
	
	public function getSyntax() {
		return $this->syntax; 
	}
	
	public function getTemplate_location() {
		return $this->template_location; 
	}
	
	public function getVendor_properties() {
		return $this->vendor_properties;
	}
	
	public function getOptions() {
		return array(
			'cache' => $this->cache,
			'cache_location' => $this->cache_location,
			'css_location' => $this->css_location,
			'filename' => $this->filename,
			'function_paths' => $this->function_paths,
			'line' => $this->line,
			'line_numbers' => $this->line_numbers,
			'load_paths' => $this->load_paths,
			'property_syntax' => $this->property_syntax,
			'quiet' => $this->quiet,
			'style' => $this->style,
			'syntax' => $this->syntax,
			'template_location' => $this->template_location,
			'vendor_properties' => $this->vendor_properties
		);
	}

	/**
	 * Parse a sass file or Sass source code and returns the CSS.
	 * @param string name of source file or Sass source
	 * @return string CSS
	 */
	public function toCss($source, $isFile = true) {
		return $this->parse($source, $isFile)->render();
	}

	/**
	 * Parse a sass file or Sass source code and
	 * returns the document tree that can then be rendered.
	 * The file will be searched for in the directories specified by the
	 * load_paths option.
	 * If caching is enabled a cached version will be used if possible or the
	 * compiled version cached if not.
	 * @param string name of source file or Sass source
	 * @return SassRootNode Root node of document tree
	 */
	public function parse($source, $isFile = true) {
		if ($isFile) {
			$this->filename = SassFile::getFile($source, $this);
			
			if ($isFile) {
				$this->syntax = substr($this->filename, -4);
			}
			elseif ($this->syntax !== SassFile::SASS && $this->syntax !== SassFile::SCSS) {
				throw new SassException('Invalid {what}', array('{what}'=>'syntax option'));
			}

			if ($this->cache) {
				$cached = SassFile::getCachedFile($this->filename, $this->cache_location);
				if ($cached !== false) {
					return $cached;
				}
			}
			
			$tree = $this->toTree(file_get_contents($this->filename));

			if ($this->cache) {
				SassFile::setCachedFile($tree, $this->filename, $this->cache_location);
			}

			return $tree;
		}
		else {
			return $this->toTree($source);
		}
	}

	/**
	 * Parse Sass source into a document tree.
	 * If the tree is already created return that.
	 * @param string Sass source
	 * @return SassRootNode the root of this document tree
	 */
	private function toTree($source) {
		if ($this->syntax === SassFile::SASS) {
			$this->source = explode("\n", $source);
			$this->setIndentChar();
		}
		else {
			$this->source = $source;
		}
		unset($source);
		$root = new SassRootNode($this);
		$this->buildTree($root);
		return $root;
	}

	/**
	 * Builds a parse tree under the parent node.
	 * Called recursivly until the source is parsed.
	 * @param SassNode the node
	 */
	private function buildTree($parent) {
		$node = $this->getNode($parent);
		while (is_object($node) && $node->isChildOf($parent)) {
			$parent->addChild($node);
			$node = $this->buildTree($node);
		}
		return $node;
	}

	/**
	 * Creates and returns the next SassNode.
	 * The tpye of SassNode depends on the content of the SassToken.
	 * @return SassNode a SassNode of the appropriate type. Null when no more
	 * source to parse.
	 */
	private function getNode($node) {
		$token = $this->getToken();
		if (empty($token)) return null;
		switch (true) {
			case SassDirectiveNode::isa($token):
				return $this->parseDirective($token, $node);
				break;
			case SassCommentNode::isa($token):
				return new SassCommentNode($token);
				break;
			case SassVariableNode::isa($token):
				return new SassVariableNode($token);
				break;
			case SassPropertyNode::isa($token, $this->property_syntax):
				return new SassPropertyNode($token, $this->property_syntax);
				break;
			case SassMixinDefinitionNode::isa($token):
				if ($this->syntax === SassFile::SCSS) {
					throw new SassException('Mixin {which} shortcut not allowed in SCSS', array('{which}'=>'definition'), $this);
				}
				return new SassMixinDefinitionNode($token);
				break;
			case SassMixinNode::isa($token):
				if ($this->syntax === SassFile::SCSS) {
					throw new SassException('Mixin {which} shortcut not allowed in SCSS', array('{which}'=>'include'), $this);
				}
				return new SassMixinNode($token);
				break;
			default:
				return new SassRuleNode($token);
				break;
		} // switch
	}
	
	/**
	 * Returns a token object that contains the next source statement and
	 * meta data about it.
	 * @return object
	 */
	private function getToken() {
		return ($this->syntax === SassFile::SASS ? $this->sass2Token() : $this->scss2Token());
	}
	
	/**
	 * Returns an object that contains the next source statement and meta data
	 * about it from SASS source.
	 * Sass statements are passed over. Statements spanning multiple lines, e.g.
	 * CSS comments and selectors, are assembled into a single statement.
	 * @return object Statement token. Null if end of source. 
	 */
	private function sass2Token() {
		$statement = ''; // source line being tokenised
		$token = null;
		
		while (is_null($token) && !empty($this->source)) {
			while (empty($statement) && !empty($this->source)) {
				$source = array_shift($this->source);
				$statement = trim($source);
				$this->line++;
			}
			
			if (empty($statement)) {
				break;
			}
			
			$level = $this->getLevel($source);
			
			// Comment statements can span multiple lines
			if ($statement[0] === self::BEGIN_COMMENT) {
				// Consume Sass comments
				if (substr($statement, 0, strlen(self::BEGIN_SASS_COMMENT))
						=== self::BEGIN_SASS_COMMENT) {
					unset($statement);
					while($this->getLevel($this->source[0]) > $level) {
						array_shift($this->source);
						$this->line++;
					}
					continue;
				}
				// Build CSS comments
				elseif (substr($statement, 0, strlen(self::BEGIN_CSS_COMMENT))
						=== self::BEGIN_CSS_COMMENT) {
					while($this->getLevel($this->source[0]) > $level) {
						$statement .= "\n" . ltrim(array_shift($this->source));
						$this->line++;
					}
				}
				else {
					$this->source = $statement;
					throw new SassException('Illegal comment type', array(), $this);
				}
			}
			// Selector statements can span multiple lines
			elseif (substr($statement, -1) === SassRuleNode::CONTINUED) {
				// Build the selector statement
				while($this->getLevel($this->source[0]) === $level) {
					$statement .= ltrim(array_shift($this->source));
					$this->line++;
				}
			}
			
			$token = (object) array(
				'source' => $statement,
				'level' => $level,
				'filename' => $this->filename,
				'line' => $this->line - 1,
			);
		}
		return $token;		 
	}

	/**
	 * Returns the level of the line.
	 * Used for .sass source
	 * @param string the source
	 * @return integer the level of the source
	 * @throws Exception if the source indentation is invalid
	 */
	private function getLevel($source) {
		$indent = strlen($source) - strlen(ltrim($source));
		$level = $indent/$this->indentSpaces;
		if (!is_int($level) ||
				preg_match("/[^{$this->indentChar}]/", substr($source, 0, $indent))) {
			$this->source = $source;
			throw new SassException('Invalid indentation', array(), $this);
		}
		return $level;
	}
	
	/**
	 * Returns an object that contains the next source statement and meta data
	 * about it from SCSS source.
	 * @return object Statement token. Null if end of source. 
	 */
	private function scss2Token() {
		static $srcpos = 0; // current position in the source stream
		static $srclen; // the length of the source stream
		
		$statement = '';
		$token = null;
		if (empty($srclen)) {
			$srclen = strlen($this->source);
		}
		while (is_null($token) && $srcpos < $srclen) {
			$c = $this->source[$srcpos++];
			switch ($c) {
				case self::BEGIN_COMMENT:	
					if (substr($this->source, $srcpos-1, strlen(self::BEGIN_SASS_COMMENT))
							=== self::BEGIN_SASS_COMMENT) {
						while ($this->source[$srcpos++] !== "\n");
						$statement .= "\n";
					}
					elseif (substr($this->source, $srcpos-1, strlen(self::BEGIN_CSS_COMMENT))
							=== self::BEGIN_CSS_COMMENT) {
						if (ltrim($statement)) {
							throw new SassException('Invalid {what}', array('{what}'=>'comment'), (object) array(
								'source' => $statement,
								'filename' => $this->filename,
								'line' => $this->line,
							));
						}
						$statement .= $c.$this->source[$srcpos++];
						while (substr($this->source, $srcpos, strlen(self::END_CSS_COMMENT))
								!== self::END_CSS_COMMENT) {
							$statement .= $this->source[$srcpos++];
						}
						$srcpos += strlen(self::END_CSS_COMMENT);
						$token = $this->createToken($statement.self::END_CSS_COMMENT);
					}
					else {
						$statement .= $c;
					}
					break;
				case self::DOUBLE_QUOTE:
				case self::SINGLE_QUOTE:
					$statement .= $c;
					while ($this->source[$srcpos] !== $c) {
						$statement .= $this->source[$srcpos++];
					}
					$statement .= $this->source[$srcpos++];
					break;
				case self::BEGIN_INTERPOLATION:
					$statement .= $c;
					if (substr($this->source, $srcpos-1, strlen(self::BEGIN_INTERPOLATION_BLOCK))
							=== self::BEGIN_INTERPOLATION_BLOCK) {
						while ($this->source[$srcpos] !== self::END_BLOCK) {
							$statement .= $this->source[$srcpos++];
						}
						$statement .= $this->source[$srcpos++];
					}
					break;
				case self::BEGIN_BLOCK:				
				case self::END_BLOCK:
				case self::END_STATEMENT:
					$token = $this->createToken($statement . $c);
					if (is_null($token)) $statement = '';
					break;	
				default:
					$statement .= $c;
					break;
			}	
		}
		
		if (is_null($token))
			$srclen = $srcpos = 0;

		return $token; 
	}
	
	/**
	 * Returns an object that contains the source statement and meta data about
	 * it.
	 * If the statement is just and end block we update the meta data and return null.
	 * @param string source statement
	 * @return SassToken
	 */
	private function createToken($statement) {
		static $level = 0;
		
		$this->line += substr_count($statement, "\n");
		$statement = trim($statement);
		if (substr($statement, 0, strlen(self::BEGIN_CSS_COMMENT)) !== self::BEGIN_CSS_COMMENT) { 
			$statement = str_replace(array("\n","\r"), '', $statement);
		}
		$last = substr($statement, -1);
		// Trim the statement removing whitespace, end statement (;), begin block ({), and (unless the statement ends in an interpolation block) end block (})
		$statement = rtrim($statement, ' '.self::BEGIN_BLOCK.self::END_STATEMENT);
		$statement = (preg_match('/#\{.+?\}$/i', $statement) ? $statement : rtrim($statement, self::END_BLOCK));
		$token = ($statement ? (object) array(
			'source' => $statement,
			'level' => $level,
			'filename' => $this->filename,
			'line' => $this->line,
		) : null);
		$level += ($last === self::BEGIN_BLOCK ? 1 : ($last === self::END_BLOCK ? -1 : 0));
		return $token;
	}

	/**
	 * Parses a directive
	 * @param SassToken token to parse
	 * @param SassNode parent node
	 * @return SassNode a Sass directive node
	 */
	private function parseDirective($token, $parent) {
		switch (SassDirectiveNode::extractDirective($token)) {
			case '@extend':
				return new SassExtendNode($token);
				break;
			case '@mixin':
				return new SassMixinDefinitionNode($token);
				break;
			case '@include':
				return new SassMixinNode($token);
				break;
			case '@import':
				if ($this->syntax == SassFile::SASS) {
					$i = 0;
					$source = '';
					while (!empty($this->source) && empty($source)) {
						$source = $this->source[$i++];
					}
					if (!empty($source) && $this->getLevel($source) > $token->level) {
						throw new SassException('Nesting not allowed beneath {what}', array('{what}'=>'@import directive'), $token);
					}
				}
				return new SassImportNode($token);
				break;
			case '@for':
				return new SassForNode($token);
				break;
			case '@if':
				return new SassIfNode($token);
				break;
			case '@else': // handles else and else if directives
				return new SassElseNode($token);
				break;
			case '@do':
			case '@while':
				return new SassWhileNode($token);
				break;
			case '@debug':
				return new SassDebugNode($token);
				break;
			case '@warn':
				return new SassDebugNode($token, true);
				break;
			default:
				return new SassDirectiveNode($token);
				break;
		}
	}

	/**
	 * Determine the indent character and indent spaces.
	 * The first character of the first indented line determines the character.
	 * If this is a space the number of spaces determines the indentSpaces; this
	 * is always 1 if the indent character is a tab.
	 * Only used for .sass files.
	 * @throws SassException if the indent is mixed or
	 * the indent character can not be determined
	 */
	private function setIndentChar() {
		foreach ($this->source as $l=>$source) {
			if (!empty($source) && in_array($source[0], $this->indentChars)) {
				$this->indentChar = $source[0];
				for	($i = 0, $len = strlen($source); $i < $len && $source[$i] == $this->indentChar; $i++);
				if ($i < $len && in_array($source[$i], $this->indentChars)) {
					$this->line = ++$l;
					$this->source = $source;
					throw new SassException('Mixed indentation not allowed', array(), $this);
				}
				$this->indentSpaces = ($this->indentChar == ' ' ? $i : 1);
				return;
			}
		} // foreach
		$this->indentChar = ' ';
		$this->indentSpaces = 2;
	}
}
