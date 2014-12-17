<?php
/**
 * plastic.js MediaWiki Wrapper
 *
 * For more info see http://mediawiki.org/wiki/Extension:AssembleFormLink
 *
 * @file
 * @ingroup Extensions
 * @author Simon Heimler, 2014
 * @license GNU General Public Licence 2.0 or later
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
   'descriptionmsg' => 'AssembleFormLink-desc',
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
   )
   ,'messages' => array(
   ),
   'dependencies' => array(
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

