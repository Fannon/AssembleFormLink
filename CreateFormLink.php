<?php
/**
 * plastic.js MediaWiki Wrapper
 *
 * For more info see http://mediawiki.org/wiki/Extension:CreateFormLink
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

$wgCreateFormLinkSubmitText = 'CREATE';


//////////////////////////////////////////
// CREDITS                              //
//////////////////////////////////////////

$wgExtensionCredits['other'][] = array(
   'path'           => __FILE__,
   'name'           => 'CreateFormLink',
   'author'         => array('Simon Heimler'),
   'version'        => '0.0.1',
   'url'            => 'https://www.mediawiki.org/wiki/Extension:CreateFormLink',
   'descriptionmsg' => 'CreateFormLink-desc',
);


//////////////////////////////////////////
// RESOURCE LOADER                      //
//////////////////////////////////////////

$wgResourceModules['ext.CreateFormLink'] = array(
   'scripts' => array(
      'lib/CreateFormLink.js',
   ),
   'styles' => array(
      'lib/CreateFormLink.css',
   )
   ,'messages' => array(
   ),
   'dependencies' => array(
   ),
   'localBasePath' => __DIR__,
   'remoteExtPath' => 'CreateFormLink',
);


//////////////////////////////////////////
// LOAD FILES                           //
//////////////////////////////////////////

// Register i18n
$wgExtensionMessagesFiles['CreateFormLinkMagic'] = $dir . '/CreateFormLink.i18n.magic.php';

// Register files
$wgAutoloadClasses['CreateFormLinkParserFunction'] = $dir . '/modules/CreateFormLinkParserFunction.php';

// Register hooks
$wgHooks['BeforePageDisplay'][] = 'createFormLinkOnBeforePageDisplay';
$wgHooks['ParserFirstCallInit'][] = 'createFormLinkOnParserFirstCallInit';



//////////////////////////////////////////
// HOOK CALLBACKS                       //
//////////////////////////////////////////

/**
* Add plastic.js library to all pages
*/
function createFormLinkOnBeforePageDisplay( OutputPage &$out, Skin &$skin ) {

  // Add as ResourceLoader Module
  $out->addModules('ext.CreateFormLink');

  return true;
}

/**
* Register parser hooks
*
* See also http://www.mediawiki.org/wiki/Manual:Parser_functions
*/
function createFormLinkOnParserFirstCallInit( &$parser ) {

  // Register {{#create-form-link }} parser function
  $parser->setFunctionHook('create-form-link', 'CreateFormLinkParserFunction::parserFunction');

  return true;
}

