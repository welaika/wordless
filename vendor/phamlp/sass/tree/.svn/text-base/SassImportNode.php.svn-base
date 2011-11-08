<?php
/* SVN FILE: $Id$ */
/**
 * SassImportNode class file.
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright 	Copyright (c) 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Sass.tree
 */

/**
 * SassImportNode class.
 * Represents a CSS Import.
 * @package			PHamlP
 * @subpackage	Sass.tree
 */
class SassImportNode extends SassNode {
	const IDENTIFIER = '@';
	const MATCH = '/^@import\s+(.+)/i';
	const MATCH_CSS = '/^(.+\.css|url\(.+\)|.+" \w+|"http)/im';
	const FILES = 1;

	/**
	 * @var array files to import
	 */
	private $files = array();

	/**
	 * SassImportNode.
	 * @param object source token
	 * @return SassImportNode
	 */
	public function __construct($token) {
		parent::__construct($token);
		preg_match(self::MATCH, $token->source, $matches);
		foreach (explode(',', $matches[self::FILES]) as $file) {
			$this->files[] = trim($file);
		}		
	}

	/**
	 * Parse this node.
	 * If the node is a CSS import return the CSS import rule.
	 * Else returns the rendered tree for the file.
	 * @param SassContext the context in which this node is parsed
	 * @return array the parsed node
	 */
	public function parse($context) {
		$imported = array();
		foreach ($this->files as $file) {
			if (preg_match(self::MATCH_CSS, $file)) {
				return "@import {$file}";
			}
			else {
				$file = trim($file, '\'"');
				$tree = SassFile::getTree(
					SassFile::getFile($file, $this->parser), $this->parser);
				if (empty($tree)) {
					throw new SassImportNodeException('Unable to create document tree for {file}', array('{file}'=>$file), $this);
				}
				else {
					$imported = array_merge($imported, $tree->parse($context)->children);
				}
			}
		}
		return $imported;
	}
}