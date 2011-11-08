<?php
/* SVN FILE: $Id: AppController.php 61 2010-04-16 10:19:59Z chris.l.yates $ */
/**
 * AppController class file.
 * 
 * **** NOTE ****
 * This is only required for Yii 1.1.1, 1.1.0, and 1.1.12 and lower.
 * Yii >= 1.1.2 and >= 1.0.13 natively support the functionality provided by
 * this file and so is not required.
 * 
 * Overrides CCcontroller::resolveViewFile() to provide a fallback to PHP view
 * files when using a viewRenderer component.
 *
 * Other controllers should extend this class, or the resolveViewFile method
 * copied into the class the other controllers do extend from.
 *
 * @author			Chris Yates <chris.l.yates@gmail.com>
 * @copyright		Copyright &copy; 2010 PBM Web Development
 * @license			http://phamlp.googlecode.com/files/license.txt
 * @package			PHamlP
 * @subpackage	Yii
 */
/**
 * AppController class.
 * @package			PHamlP
 * @subpackage	Yii
 */
class AppController extends CController
{
	/**
	 * Finds a view file based on its name.
	 * If the application is using a viewRenderer component a view file for the
	 * viewRenderer will be looked for. If not found or or a viewRenderer
	 * component is not being used a .php view file will be looked for.
	 * The view name can be in one of the following formats:
	 * <ul>
	 * <li>absolute view: the view name starts with a slash '/'.</li>
	 * <li>aliased view: the view name contains dots and refers to a path alias.
	 * The view file is determined by calling {@link YiiBase::getPathOfAlias()}.</li>
	 * <li>relative view: otherwise.</li>
	 * </ul>
	 * For absolute view and relative view, the corresponding view file is a PHP file
	 * whose name is the same as the view name. The file is located under a specified directory.
	 * This method will call {@link CApplication::findLocalizedFile} to search for a localized file, if any.
	 * @param string the view name
	 * @param string the directory that is used to search for a relative view name
	 * @param string the directory that is used to search for an absolute view name
	 * @return mixed the view file path. False if the view file does not exist.
	 */
	public function resolveViewFile($viewName,$viewPath,$basePath)
	{
		if(empty($viewName))
			return false;
		if(($renderer=Yii::app()->getViewRenderer())!==null)
			$extension=$renderer->fileExtension;
		do
		{
			if ($renderer===null)
				$extension='.php';
			if($viewName[0]==='/')
				$viewFile=$basePath.$viewName.$extension;
			else if(strpos($viewName,'.'))
				$viewFile=Yii::getPathOfAlias($viewName).$extension;
			else
				$viewFile=$viewPath.DIRECTORY_SEPARATOR.$viewName.$extension;
			if (is_file($viewFile))
				return Yii::app()->findLocalizedFile($viewFile);
			$renderer=null;
		} while ($extension!=='.php');
		return false;
	}
}