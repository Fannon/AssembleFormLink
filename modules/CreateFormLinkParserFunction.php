<?php

/**
 * Hooks for PlasticMW extension
 *
 * @file
 * @ingroup Extensions
 */

class CreateFormLinkParserFunction extends SMWQueryProcessor  {


    /**
     * Parser function handler for {{#create-form-link: .. | .. }}
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

        // Imports
        global $wgScriptPath;

        // Get Parameters
        $params = func_get_args();
        array_shift($params); // Remove the $parser.
        $formName = $params[0];
        array_shift($params); // Remove first argument, already stored in $form
        $arguments = extractOptions($params);
        $url = $wgScriptPath;
        $submitText = "Create";

        $html = '<form class="cfl-form">';

        // Calculate the URL that creates a new form of given formtype
        $html .= '<input class="cfl cfl-hidden" style="display: none;" value="' . $url . '/index.php/Special:FormEdit/' . $formName . '/"></input>';

        // If a namespace is given, always use it first
        if (array_key_exists('namespace', $arguments)) {
            $html .= '<span class="cfl cfl-namespace">' . $arguments['namespace'] . '</span>';
            $html .= '<span class="cfl cfl-separator">:</span>';
            $html .= '<input class="cfl cfl-hidden" style="display: none;" value="' . $arguments['namespace'] . ':"></input>';
            unset($arguments['namespace']);
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

                if ($key === 'slash') {
                    $separatorString = '/';
                } else if ($key === 'colon') {
                    $separatorString = ':';
                } else if ($key === 'space') {
                    $separatorString = ' ';
                }

                $html .= '<span class="cfl cfl-separator">' . $separatorString . '</span>';
                $html .= '<input class="cfl cfl-hidden" style="display: none;" value="' . $separatorString . '"></input>';

            // If its not a separator, it is a form element

            } else {
                if (startsWith($value, 'textfield')) {

                    // Look for text within brackets: []
                    // http://stackoverflow.com/a/10104517/776425
                    preg_match_all("/\[([^\]]*)\]/", $value, $matches);

                    $inputParams = extractOptions($matches[1]); // Don't include the brackets

                    // Add additional parameters
                    $additionalParams = '';
                    foreach ($inputParams as $inputParamKey => $inputParamValue) {
                        $additionalParams .= ' ' . htmlspecialchars($inputParamKey) . '="' . htmlspecialchars($inputParamValue) . '"';
                    }

                    $html .= '<input type="text" required class="cfl" name="' . $key . '"' . $additionalParams . '>';
                }
            }
        }

        $html .= '<input type="submit" value="' . $submitText . '" class="cfl-submit">';


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
 *
 *
 * @param string $optionString
 * @return array $results
 */
function extractInputOptions($optionString) {

    $options = explode(",", $optionString);

    $results = extractOptions($options, ':');

    return $results;
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
