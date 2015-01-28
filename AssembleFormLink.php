<?php
/**
 * The AssembleFormLink extension provides a parser function that allows to build simple helper forms on a page (in display mode).
 *
 * For more info see http://mediawiki.org/wiki/Extension:AssembleFormLink
 *
 * @file
 * @ingroup Extensions
 * @package MediaWiki
 *
 * @links https://github.com/Fannon/AssembleFormLink/blob/master/README.md Documentation
 * @links https://www.mediawiki.org/wiki/Extension_talk:AssembleFormLink Support
 * @links https://github.com/Fannon/AssembleFormLink/issues Bug tracker
 * @links https://github.com/Fannon/AssembleFormLink Source code
 *
 * @author Simon Heimler (Fannon), 2015
 * @license http://opensource.org/licenses/mit-license.php The MIT License (MIT)
 */

//////////////////////////////////////////
// VARIABLES                            //
//////////////////////////////////////////

$dir         = dirname( __FILE__ );
$dirbasename = basename( $dir );


//////////////////////////////////////////
// CONFIGURATION                        //
//////////////////////////////////////////

$wgAssembleFormLinkSubmitText = 'NEW';


//////////////////////////////////////////
// CREDITS                              //
//////////////////////////////////////////

$wgExtensionCredits['other'][] = array(
   'path'           => __FILE__,
   'name'           => 'AssembleFormLink',
   'author'         => array('Simon Heimler'),
   'version'        => '0.0.1',
   'url'            => 'https://www.mediawiki.org/wiki/Extension:AssembleFormLink',
   'descriptionmsg' => 'assembleformlink-desc',
);


//////////////////////////////////////////
// RESOURCE LOADER                      //
//////////////////////////////////////////

$wgResourceModules['ext.AssembleFormLink'] = array(
   'scripts' => array(
      'lib/AssembleFormLink.js',
   ),
   'styles' => array(
      'lib/AssembleFormLink.css',
   ),
   'dependencies' => array(
      'ext.semanticforms.select2'
   ),
   'localBasePath' => __DIR__,
   'remoteExtPath' => 'AssembleFormLink',
);


//////////////////////////////////////////
// LOAD FILES                           //
//////////////////////////////////////////

// Register i18n
$wgExtensionMessagesFiles['AssembleFormLinkMagic'] = $dir . '/AssembleFormLink.i18n.magic.php';

// Register files
$wgAutoloadClasses['AssembleFormLinkParserFunction'] = $dir . '/modules/AssembleFormLinkParserFunction.php';

// Register hooks
$wgHooks['BeforePageDisplay'][] = 'assembleFormLinkOnBeforePageDisplay';
$wgHooks['ParserFirstCallInit'][] = 'assembleFormLinkOnParserFirstCallInit';



//////////////////////////////////////////
// HOOK CALLBACKS                       //
//////////////////////////////////////////

/**
* Add plastic.js library to all pages
*/
function assembleFormLinkOnBeforePageDisplay( OutputPage &$out, Skin &$skin ) {

  // Add as ResourceLoader Module
  $out->addModules('ext.AssembleFormLink');

  return true;
}

/**
* Register parser hooks
*
* See also http://www.mediawiki.org/wiki/Manual:Parser_functions
*/
function assembleFormLinkOnParserFirstCallInit( &$parser ) {

  // Register {{#assembleFormLink }} parser function
  $parser->setFunctionHook('assembleFormLink', 'AssembleFormLinkParserFunction::parserFunction');

  return true;
}

