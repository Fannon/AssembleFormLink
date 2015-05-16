<?php

/**
 * Hooks for the parser Functioons
 *
 * @file
 * @ingroup Extensions
 */
class AssembleFormLinkParserFunction extends SMWQueryProcessor  {


    /**
     * Parser function handler for {{#assembleFormLink: .. | .. }}
     *
     * This generates both visible and invisible form elements that hold the values for generating the correct Form Link
     *
     *
     *
     * @param Parser $parser
     * @param string $arg
     *
     * @return string: HTML to insert in the page.
     */
    public static function parserFunction(Parser &$parser) {


        //////////////////////////////////////////
        // VARIABLES                            //
        //////////////////////////////////////////

        // Imports
        global $wgScriptPath;
        global $wgAssembleFormLinkSubmitText;

        // Get Parameters
        $params = func_get_args();
        array_shift($params); // Remove the $parser.
        $formName = $params[0];
        array_shift($params); // Remove first argument, already stored in $form
        $arguments = extractOptions($params);
        $url = $wgScriptPath . '/index.php/';

        // Variables
        $submitText = $wgAssembleFormLinkSubmitText;
        $namespaceStyle = '';
        $categoryStyle = '';
        $categoryIncludeInUrl = false;
        $additionalClasses = '';
        $autocompleteValues = array();
        $select2 = false;


        //////////////////////////////////////////
        // BUILD FORM (HTML)                    //
        //////////////////////////////////////////

        $formArguments = '';
        // Get category-min-width parameter if given
        if (array_key_exists('check-for-existence', $arguments)) {
            $formArguments .= 'check-for-existence="true" ';
            unset($arguments['check-for-existence']);
        }

        $html = '<form class="afl-form" ' . $formArguments . '>';

        // TODO: Rename category to label
        // TODO: Add option to make the label a link (to a category or general)

        // Calculate the URL that creates a new form of given formtype
        // Those information are included through hidden form input elements
        $html .= '<input class="afl afl-hidden" style="display: none;" value="' . $url . 'Special:FormEdit/' . $formName . '/"></input>';

        // Get category-min-width parameter if given
        if (array_key_exists('category-include-in-url', $arguments)) {
            $categoryIncludeInUrl = true;
            unset($arguments['category-include-in-url']);
        }

        // Get category-min-width parameter if given
        if (array_key_exists('category-min-width', $arguments)) {
            $categoryStyle = ' style="min-width: ' . $arguments['category-min-width'] . '"';
            unset($arguments['category-min-width']);
        }

        // Print Category Link if given
        if (array_key_exists('category', $arguments)) {

            // Pretty print if human readable name is given through an additional '='
            $nameArray = explode('=', $arguments['category']);
            $internalName = $nameArray[0];
            $readableName = $nameArray[0];

            if (isset($nameArray[1])) {
                $readableName = $nameArray[1];
            }

            $html .= '<a href="' . $url . 'Category:' . $internalName . '" class="afl afl-category"' . $categoryStyle . '>' . $readableName . '</a>';

            if ($categoryIncludeInUrl) {
                $html .= '<input class="afl afl-hidden" style="display: none;" value="' . $internalName . '"></input>';
            }
            unset($arguments['category']);
        }

        // If a submit text is given, use it instead of the default
        if (array_key_exists('submit-text', $arguments)) {
            $submitText = $arguments['submit-text'];
            unset($arguments['submit-text']);
        }


        foreach ($arguments as $key => $value) {

            // If the value is "just" true, no value is given. Assume it is a separator
            if ($value === true) {

                $separatorString = $key;

                if (startsWith($key, 'slash')) {
                    $separatorString = '/';
                    $separatorValue = '/';
                } else if (startsWith($key, 'colon')) {
                    $separatorString = ':';
                    $separatorValue = ':';
                } else if (startsWith($key, 'space')) {
                    $separatorString = '&nbsp;';
                    $separatorValue = ' ';
                } else if (startsWith($key, 'commaspace')) {
                    $separatorString = ',&nbsp;';
                    $separatorValue = ', ';
                } else if (startsWith($key, 'comma')) {
                    $separatorString = ',';
                    $separatorValue = ',';
                } else {
                    // Default to space
                    $separatorString = '&nbsp;';
                    $separatorValue = ' ';
                }

                $html .= '<span class="afl afl-separator">' . $separatorString . '</span>';
                $html .= '<input class="afl afl-hidden" style="display: none;" value="' . $separatorValue . '"></input>';


            // If its not a separator, it is a form element
            } else {

                if (startsWith($value, 'textfield')) {

                    // Extract suboptions and add them as HTML attributes to the input element
                    $additionalAttributes = extractSubOptions($value);
                    $additionalAttributes .= ' name="' . $key . '"';

                    // Get the suboptions as associative array, to allow for easier handling
                    $additionalAttributesArray = extractSubOptions($value, true);

                    // If data-widget is a select box, or the select2 library is used, render a <select> element

                    $attr = 'required';

                    if (array_key_exists('optional', $additionalAttributesArray)) {
                        $attr = '';
                    }

                    if (array_key_exists('placeholder', $additionalAttributesArray)) {
                        $attr .= ' title="' . $additionalAttributesArray['placeholder'] . '" data-toggle="tooltip" data-placement="top"';
                    }


                    if (array_key_exists('data-select2', $additionalAttributesArray)) {
                        $html .= '<select ' . $attr . ' class="afl select2"' . $additionalAttributes . '></select>';
                    } else if (array_key_exists('data-select', $additionalAttributesArray)) {
                        $html .= '<select ' . $attr . ' class="afl"' . $additionalAttributes . '></select>';
                    } else {
                        $additionalAttributes .= extractSubOptions($value);
                        $html .= '<input type="text" ' . $attr . ' class="afl' . $additionalClasses . '"' . $additionalAttributes . '>';
                    }

                }
            }
        }

        $html .= '<input type="submit" value="' . $submitText . '" class="afl-submit">';

        $html .= '</form>';

        $debug = array(
            '$formName' => $formName,
            '$arguments' => $arguments,
            '$url' => $url,
            '$html' => $html,
        );


        // jlog($debug);

        return array(
            $html,
            'noparse' => true,
            'isHTML' => true,
            "markerType" => 'nowiki'
        );
    }

}

/**
 * Converts an array of values in form [0] => "name=value" into a real
 * associative array in form [name] => value
 *
 * @param array string $options
 * @return array $results
 */
function extractOptions(array $options, $separator = '=') {

    $results = array();

    foreach ($options as $option) {
        $pair = explode($separator, $option, 2 );
        if (count($pair) == 2) {
            $name           = trim( $pair[0] );
            $value          = trim( $pair[1] );
            $results[$name] = htmlspecialchars($value);
        } else {
            $results[$option] = true;
        }
    }
    return $results;
}

/**
 * Suboptions can be attached to an option value
 * They are provided with brackets, containing key=value
 * Example: [placeholder=Alternative Text][size=16]
 *
 * @param string $optionString
 * @param boolean $returnAsObject
 *
 * @return array $additionalParams
 */
function extractSubOptions($optionString, $returnAsObject = false) {

    $additionalParams = '';

    // Look for text within brackets: []
    // http://stackoverflow.com/a/10104517/776425
    preg_match_all("/\[([^\]]*)\]/", $optionString, $matches);

    $inputParams = extractOptions($matches[1]); // Don't include the brackets

    // Add additional parameters
    $additionalParamsArray = array();
    foreach ($inputParams as $inputParamKey => $inputParamValue) {
        $additionalParams .= ' ' . htmlspecialchars($inputParamKey) . '="' . htmlspecialchars($inputParamValue) . '"';
        $additionalParamsArray[htmlspecialchars($inputParamKey)] = htmlspecialchars($inputParamValue);
    }

    if ($returnAsObject === true) {
        return $additionalParamsArray;
    } else {
        return $additionalParams;
    }


}

/**
 * Check if string starts with a (sub)string
 * http://stackoverflow.com/a/10473026/776425
 *
 * @param  [string] $haystack
 * @param  [string] $needle
 * @return [boolean]
 */
function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

/**
 * Check if string ends with a (sub)string
 * http://stackoverflow.com/a/10473026/776425
 *
 * @param  [string] $haystack
 * @param  [string] $needle
 * @return [boolean]
 */
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || strpos($haystack, $needle, strlen($haystack) - strlen($needle)) !== FALSE;
}


/**
 * Helper Logging Function that outputs an object as pretty JSON and kills the PHP process
 *
 * @param  [type] $object [description]
 * @return [type]         [description]
 */
function jlog($object) {
    header('Content-Type: application/json');
    print(json_encode($object, JSON_PRETTY_PRINT));
    die();
}

/**
 * Helper Logging Function that outputs an object as pretty JSON and kills the PHP process
 *
 * @param  [type] $object [description]
 * @return [type]         [description]
 */
function tlog($object) {
    header('Content-Type: text/plain');
    print($object);
    die();
}
