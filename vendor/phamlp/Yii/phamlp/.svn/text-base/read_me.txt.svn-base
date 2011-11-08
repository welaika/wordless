Requirements
============
Yii 1.0 or above

Installation
============
Extract the release file under `protected/extensions`

Usage
=====
The [PHamlP Wiki](http://code.google.com/p/phamlp/w/list) has detailed
description of options for Haml and Sass.

  Haml
  ====
  Declare PHamlP's Haml as your the viewRender component in your configuration.
  ~~~
  'viewRenderer'=>array(
    'class'=>'ext.phamlp.Haml',
    // delete options below in production
    'ugly' => false,
    'style' => 'nested',
    'debug' => 0,
    'cache' => false,
  ),
  ~~~
  In the demo folder there are Haml templates for a new Yii project. Copy them 
  into the appropriate locations in the views directory.

    View File Types
    ===============
    The extension supports the use of both Haml and PHP view files and layouts 
    in the same application, so you can swap your views to Haml gradually and 
    use extensions and widgets with .php views.

    Yii 1.1.2 and greater support this feature natively.
    For use with earlier versions of Yii the extension contains the 
    AppController file in the Yii directory. This has a new resolveViewFile() 
    method that must override CCcontroller::resolveViewFile(). This is done 
    either by extending your application's controllers from AppController (Yii 
    must be able to find it, so either move it from the extension into 
    application.components or edit your configuration file to import it), or 
    copy AppCcontroller::resolveViewFile() method into your application's own 
    base controller.

    **Note:** Prior to R0014 all views must be Haml

  Sass
  ====

  In order to make the handling of Sass files transparent the extension has an 
  enhanced asset manager. This must be declared as the assetManager component 
  in your configuration file.
  ~~~
  'assetManager' => array(
    'class' => 'PBMAssetManager',
    'parsers' => array(
      'sass' => array( // key == the type of file to parse
        'class' => 'ext.phamlp.Sass', // path alias to the parser
        'output' => 'css', // the file type it is parsed to
        'options' => array(<Parser specific options>)
      ),
    )
  ),
  ~~~

  Sass supports the .sass (indented) and the .scss (CSS style) syntaxes.
  To use both you need to tell the asset manager that both extensions are to be
  parsed with Sass.
  ~~~
  'assetManager' => array(
    'class' => 'PBMAssetManager',
    'parsers' => array(
      'sass' => array( // key == the type of file to parse
        'class' => 'ext.phamlp.Sass', // path alias to the parser
        'output' => 'css', // the file type it is parsed to
        'options' => array(<Parser specific options>)
      ),
      'scss' => array( // key == the type of file to parse
        'class' => 'ext.phamlp.Sass', // path alias to the parser
        'output' => 'css', // the file type it is parsed to
        'options' => array(<Parser specific options>)
      ),
    )
  ),
  ~~~
  
  Publishing a Sass file is the same as publishing any other asset, i.e.:
  ~~~
  $publishedAsset = Yii::app()->getAssetMananger()->publish(
    Yii::getPathOfAlias('allias.to.asset.directory').DIRECTORY_SEPARATOR.'asset.sass'
  );
  ~~~
