<?php
/* SVN FILE: $Id$ */
/**
 * PBMAssetManager class file.
 * @author		Chris Yates <chris.l.yates@gmail.com>
 * @copyright	Copyright &copy; 2010 PBM Web Development
 * @license		http://phamlp.googlecode.com/files/license.txt
 * @package		PBM
 */
/**
 * PBMAssetManager class.
 * PBMAssetManager overrides CAssetManager::publish to provide parsing of assets
 * when required.
 *
 * Configuration
 * -------------
 * Import the component.
 * Yii::import('path.to.component.PBMAssetManager');
 *
 * Declare the use of this component as the asset manager component. This
 * example declares a Sass {@link } parser; multiple parsers may be declared.
 * <pre>
 * // application components
 * 'components'=>array(
 *   'assetManager' => array(
 *     'class' => 'PBMAssetManager',
 *     'parsers' => array(
 *       'sass' => array( // key == the type of file to parse
 *         'class' => 'ext.haml.Sass', // path alias to the parser
 *         'output' => 'css', // the file type it is parsed to
 *         'options' => array(<Parser specific options>)
 *       ),
 *     )
 *   )
 * )
 * </pre>
 *
 * You can also declare the "force" parameter to be true. This forces assets to
 * be published whether newer than the published asset or not; this is for
 * development so that changes to deep files get published without having to
 * flush the asset directory. Make sure this parameter is removed or declared
 * false in production.
 *
 * Usage
 * -----
 * Usage is exactly the same as publishing an asset with CAssetManager, i.e.
 *
 * $publishedAsset = Yii::app()->getAssetMananger()->publish(Yii::getPathOfAlias('allias.to.asset.directory'). DIRECTORY_SEPARATOR . 'asset.sass');
 *
 * The only difference is that parsing of files will take place during the
 * publish. Files that do not require parsing are handled exactly as before.
 * 
 * @package PBM
 */
class PBMAssetManager extends CAssetManager {
	/**
	 * @var array asset parsers
	 */
	public $parsers;
	/**
	 * @var boolean if true the asset will always be published
	 */
	public $force = false;
	/**
	 * @var string base web accessible path for storing private files
	 */
	private $_basePath;
	/**
	 * @var string base URL for accessing the publishing directory.
	 */
	private $_baseUrl;
	/**
	 * @var array published assets
	 */
	private $_published=array();

	/**
	 * Publishes a file or a directory.
	 * This method will copy the specified asset to a web accessible directory
	 * and return the URL for accessing the published asset.
	 * <ul>
	 * <li>If the asset is a file, its file modification time will be checked
	 * to avoid unnecessary file copying;</li>
	 * <li>If the asset is a directory, all files and subdirectories under it will
	 * be published recursively. Note, in this case the method only checks the
	 * existence of the target directory to avoid repetitive copying.</li>
	 * </ul>
	 * @param string the asset (file or directory) to be published
	 * @param boolean whether the published directory should be named as the hashed basename.
	 * If false, the name will be the hashed dirname of the path being published.
	 * Defaults to false. Set true if the path being published is shared among
	 * different suffixs.
	 * @param integer level of recursive copying when the asset is a directory.
	 * Level -1 means publishing all subdirectories and files;
	 * Level 0 means publishing only the files DIRECTLY under the directory;
	 * level N means copying those directories that are within N levels.
	 * @return string an absolute URL to the published asset
	 * @throws CException if the asset to be published does not exist.
	 */
	public function publish($path,$hashByName=false,$level=-1) {
		if(isset($this->_published[$path])) {
			return $this->_published[$path];
		}
		else if(($src=realpath($path))!==false) {
			if(is_file($src)) {
				$dir=$this->hash($hashByName ? basename($src) : dirname($src));
				$fileName=basename($src);
				$suffix=substr(strrchr($fileName, '.'), 1);
				$dstDir=$this->getBasePath().DIRECTORY_SEPARATOR.$dir;

				if (array_key_exists($suffix, $this->parsers)) {
					$fileName=basename($src, $suffix);
					$fileName=basename($src, $suffix).$this->parsers[$suffix]['output'];
				}
				$dstFile=$dstDir.DIRECTORY_SEPARATOR.$fileName;

				if($this->force || @filemtime($dstFile)<@filemtime($src)) {
					if(!is_dir($dstDir)) {
						mkdir($dstDir);
						@chmod($dstDir,0777);
					}

					if (array_key_exists($suffix, $this->parsers)) {
						$parserClass = Yii::import($this->parsers[$suffix]['class']);
						$parser = new $parserClass($this->parsers[$suffix]['options']);
						file_put_contents($dstFile, $parser->parse($src));
					}
					else {
						copy($src,$dstFile);
					}
				}

				return $this->_published[$path]=$this->getBaseUrl()."/$dir/$fileName";
			}
			else if(is_dir($src)) {
				$dir=$this->hash($hashByName ? basename($src) : $src);
				$dstDir=$this->getBasePath().DIRECTORY_SEPARATOR.$dir;

				if(!is_dir($dstDir)) {
					CFileHelper::copyDirectory($src,$dstDir,array('exclude'=>array('.svn'),'level'=>$level));
				}

				return $this->_published[$path]=$this->getBaseUrl().'/'.$dir;
			}
		}
		throw new CException(Yii::t('yii','The asset "{asset}" to be published does not exist.',
			array('{asset}'=>$path)));
	}
}
