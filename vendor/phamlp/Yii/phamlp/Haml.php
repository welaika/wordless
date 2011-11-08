<?php
/* SVN FILE: $Id: Haml.php 93 2010-05-20 17:43:41Z chris.l.yates $ */
/**
 * HamlViewRenderer class file.
 * Renders {@link HAML http://haml-lang.com/} view files.
 * Please see the {@link HAML documentation http://haml-lang.com/docs/yardoc/file.HAML_REFERENCE.html#plain_text} for the syntax.
 *
 * To use HamlViewRenderer, configure it as an application component named
 * "viewRenderer" in the application configuration:
 * <pre>
 * array(
 *   'components'=>array(
 *     ......
 *     'viewRenderer'=>array(
 *       'class'=>'HamlViewRenderer',
 *       .... options ....
 *     ),
 *   ),
 * )
 * </pre>
 *
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright &copy; 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Yii
 */

/**
 * HamlViewRenderer allows you to write view files in
 * {@link Haml http://haml-lang.com/}.
 * @package			PHamlP
 * @subpackage	Yii
 */
class Haml extends CViewRenderer {
	/**#@+
	 * Configurable Options - Renderer
	 */
	/**
	 * @var string the extension name of the source file. Defaults to '.haml'.
	 */
	public $fileExtension = '.haml';
	/**
	 * @var string the extension name of the view file. Defaults to '.php'.
	 */
	public $viewFileExtension = '.php';
	/**
	 * @var boolean whether to cache parsed files. If false files will be parsed
	 * every time.
	 */
	public $cache = true;
	/**#@-*/
	/**#@+
	 * Configurable Options - Haml
	 */
	/**
	 * @var string DOCTYPE format
	 * @see doctypes
	 */
	public $format = 'xhtml';
	/**
	 * @var string custom doctype. If null (default) {@link format} must be
	 * a key in {@link doctypes}
	 */
	 public $doctype;
	/**
	 * @var boolean whether or not to escape X(HT)ML-sensitive characters in script.
	 * If this is true, = behaves like &=; otherwise, it behaves like !=.
	 * Note that if this is set, != should be used for yielding to subtemplates
	 * and rendering partials. Defaults to false.
	 */
	public $escapeHtml;
  /**
   * @var boolean Whether or not attribute hashes and scripts designated by
   * = or ~ should be evaluated. If true, the scripts are rendered as empty strings.
   * Defaults to false.
   */
	public $suppressEval;
  /**
	 * @var string The character that should wrap element attributes. Characters
	 * of this type within attributes will be escaped (e.g. by replacing them with
	 * &apos;) if the character is an apostrophe or a quotation mark.
	 * Defaults to " (an quotation mark).
	 */
	 public $attrWrapper;
  /**
	 * @var string style of output. Can be:
	 * nested: output is nested according to the indent level in the source
	 * expanded: block tags have their own lines as does content which is indented
	 * compact: block tags and their content go on one line
	 * compressed: all unneccessary whitepaces is removed. If ugly is true this style is used.
	 */
	public $style;
  /**
	 * @var boolean if true no attempt is made to properly indent or format
	 * the output. Reduces size of output file but is not very readable.
	 * Defaults to true.
	 */
	public $ugly;
	/**
	 * @var boolean if true comments are preserved in ugly mode. If not in
	 * ugly mode comments are always output. Defaults to false.
	 */
	public $preserveComments;
	/**
	 * @var integer Initial debug setting:
	 * no debug, show source, show output, or show all.
	 * Debug settings can be controlled in the template
	 * Defaults to DEBUG_NONE.
	 */
	public $debug;
	/**
	 * @var string Path alias to filters. If specified this will be searched
	 * first followed by 'haml.filters'. This allows the default filters to be
	 * overridden.
	 */
	public $filterPathAlias;
	/**
	 * @var array supported doctypes
	 * @see format
	 */
	public $doctypes;
	/**
	 * @var array A list of tag names that should be automatically self-closed
	 * if they have no content.
	 */
	public $emptyTags;
	/**
	 * @var array A list of inline tags for which whitespace is not collapsed
	 * fully when in ugly mode or stripping outer whitespace.
	 */
	public $inlineTags;
	/**
	 * @var array attributes that are minimised
	 */
	 public $minimizedAttributes;
	/**
	 * @var array A list of tag names that should automatically have their newlines preserved.
	 */
	public $preserve;
	/**
	 * @var string Path to a file containing user defined Haml helpers.
	 */
	public $helperFile;
	/**#@-*/

	/**
	* @var HamlParser the Haml parser
	*/
	private $haml;
	/**
	 * @var array Haml parser option names. These are passed to the parser if set.
	 */
	private $hamlOptions = array('format', 'doctype', 'escapeHtml', 'suppressEval', 'attrWrapper', 'style', 'ugly', 'preserveComments', 'debug', 'filterDir', 'doctypes', 'emptyTags', 'inlineTags', 'minimizedAttributes', 'preserve', 'helperFile');
	/**
	 * @var string Path to filters. Derived from filterPathAlias.
	 */
	private $filterDir;

	/**
	 * Do a sanity check on the options and setup alias to filters
	 */
	private function _init() {
		if (isset($this->filterPathAlias)) {
			$this->filterDir = Yii::getPathOfAlias($this->filterPathAlias);
		}
		if (isset($this->helperFile)) {
			$this->helperFile = Yii::getPathOfAlias($this->helperFile).'.php';
		}

		$options = array();
		foreach ($this->hamlOptions as $option) {
			if (isset($this->$option)) {
				$options[$option] = $this->$option;
			}
		} // foreach

		Yii::import('ext.phamlp.vendors.phamlp.haml.HamlParser');
	  $this->haml = new HamlParser($options);
	}

	/**
	 * Parses the source view file and saves the results as another file.
	 * This method is required by the parent class.
	 * @param string the source view file path
	 * @param string the resulting view file path
	 */
	protected function generateViewFile($sourceFile, $viewFile) {
		if (substr($sourceFile, strlen($this->fileExtension) * -1) === $this->fileExtension) {
			if (empty($this->haml)) $this->_init();
 			$data = $this->haml->parse($sourceFile);
		}
		else {
			$data = file_get_contents($sourceFile);
		}
		file_put_contents($viewFile, $data);
	}

	/**
	 * Renders a view file.
	 * This method is required by {@link IViewRenderer}.
	 * @param CBaseController the controller or widget who is rendering the view file.
	 * @param string the view file path
	 * @param mixed the data to be passed to the view
	 * @param boolean whether the rendering result should be returned
	 * @return mixed the rendering result, or null if the rendering result is not needed.
	 */
	public function renderFile($context,$sourceFile,$data,$return) {
		$hamlSourceFile = substr($sourceFile, 0, strrpos($sourceFile, '.')).$this->fileExtension;

		if(!is_file($hamlSourceFile) || ($file=realpath($hamlSourceFile))===false) {
			return parent::renderFile($context, $sourceFile, $data, $return);
		}
		$viewFile=$this->getViewFile($sourceFile);
		
		$viewFile = str_replace($this->fileExtension.($this->useRuntimePath?'':'c'), $this->viewFileExtension, $viewFile);
		
		if(!$this->cache||@filemtime($sourceFile)>@filemtime($viewFile)) {
			$this->generateViewFile($sourceFile,$viewFile);
			@chmod($viewFile,$this->filePermission);
		}
		return $context->renderInternal($viewFile,$data,$return);
	}
}